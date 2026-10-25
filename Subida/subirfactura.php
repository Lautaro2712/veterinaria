<?php
require_once __DIR__ . "/../!PHPchecks/conexion.php";
require_once __DIR__ . "/../!PHPchecks/auth.php";
require_once __DIR__ . "/../!PHPchecks/registro_acciones.php";
requireExactRole([ROL_ADMINISTRADOR]);

date_default_timezone_set('America/Montevideo');

const MONTO_MAXIMO_FACTURA = 2147483647; // límite de la columna INT(11) montoTotalFactura
const CANTIDAD_MAXIMA_POR_PRODUCTO = 100000;

$exito = false;
$error = null;
$numeroAsignado = null;
$tipoFactura = 'compra'; // 'compra' (productos) o 'servicio'

/**
 * Calcula el próximo número de factura con formato AAAANNNNNN
 * (año + secuencia de 6 dígitos que reinicia cada año). Ej: 2026000001...
 * Con $bloquear = true se usa dentro de una transacción para evitar que dos
 * facturas simultáneas reciban el mismo número.
 */
function generarNumeroFactura(PDO $conexion, bool $bloquear = false): int {
    $base = (int) date('Y') * 1000000;

    $sql = "SELECT MAX(numeroFactura) FROM factura WHERE numeroFactura > ? AND numeroFactura <= ?";
    if ($bloquear) {
        $sql .= " FOR UPDATE";
    }

    $stmt = $conexion->prepare($sql);
    $stmt->execute([$base, $base + 999999]);
    $ultimo = $stmt->fetchColumn();

    return $ultimo ? ((int) $ultimo + 1) : $base + 1;
}

/**
 * Convierte el JSON enviado por calculoFactura.js en [idProducto => cantidad],
 * juntando repetidos. Devuelve null si el formato es inválido.
 */
function parsearItems(string $json): ?array {
    $datos = json_decode($json, true);
    if (!is_array($datos) || count($datos) === 0) {
        return null;
    }

    $items = [];
    foreach ($datos as $fila) {
        if (!is_array($fila) || !isset($fila['idProducto'], $fila['cantidad'])) {
            return null;
        }
        $id = filter_var($fila['idProducto'], FILTER_VALIDATE_INT);
        $cant = filter_var($fila['cantidad'], FILTER_VALIDATE_INT);
        if ($id === false || $cant === false || $id <= 0 || $cant <= 0 || $cant > CANTIDAD_MAXIMA_POR_PRODUCTO) {
            return null;
        }
        $items[$id] = ($items[$id] ?? 0) + $cant;
        if ($items[$id] > CANTIDAD_MAXIMA_POR_PRODUCTO) {
            return null;
        }
    }

    ksort($items); // orden fijo de bloqueo: evita deadlocks entre facturas simultáneas
    return $items;
}

$stmt = $conexion->prepare("SELECT idCliente, nombreCliente FROM cliente ORDER BY nombreCliente ASC");
$stmt->execute();
$clientes = $stmt->fetchAll();

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $fechaEmisionFactura = date('Y-m-d');
    $idCliente = $_POST['idCliente'] ?? "";

    // Cualquier valor que no sea 'servicio' se trata como 'compra'; en ese
    // caso la validación de productos de más abajo rechaza lo inválido.
    $tipoFactura = (($_POST['tipoFactura'] ?? 'compra') === 'servicio') ? 'servicio' : 'compra';

    // Los productos solo se leen en modo compra (en modo servicio el campo
    // oculto de productos viaja vacío y no se usa).
    $items = ($tipoFactura === 'compra') ? parsearItems($_POST['itemsFactura'] ?? "") : null;

    if (empty($idCliente)) {
        $error = "Por favor, complete todos los campos solicitados.";
    } elseif ($tipoFactura === 'servicio') {

        // -----------------------------------------------------------------
        // FACTURA DE SERVICIO
        // El navegador solo manda QUÉ servicio se facturó y, si corresponde,
        // a qué tipo de animal. El precio se lee SIEMPRE de la base de
        // datos: nunca se acepta un monto que venga del navegador.
        // -----------------------------------------------------------------
        $idTipoServicio = filter_var($_POST['idTipoServicio'] ?? '', FILTER_VALIDATE_INT);
        $idTipoAnimal = filter_var($_POST['idTipoAnimal'] ?? '', FILTER_VALIDATE_INT);

        try {
            $conexion->beginTransaction();

            // 1. El servicio tiene que existir y ser de los que se piden en una cita
            $servicio = null;
            if ($idTipoServicio !== false && $idTipoServicio > 0) {
                $stmtServicio = $conexion->prepare("SELECT id, precioFijo FROM tipo_servicio WHERE id = ? AND esCita = 1");
                $stmtServicio->execute([$idTipoServicio]);
                $servicio = $stmtServicio->fetch() ?: null;
            }
            if ($servicio === null) {
                throw new RuntimeException("Seleccione un servicio válido.");
            }

            // 2. Precio: fijo (sin animal) o según el tipo de animal elegido
            if ($servicio['precioFijo'] !== null) {
                // Servicio de precio fijo (por ejemplo, la consulta): no
                // admite animal. Si el navegador mandó uno igual, se ignora.
                $idTipoAnimal = null;
                $precio = (int) $servicio['precioFijo'];
            } else {
                if ($idTipoAnimal === false || $idTipoAnimal <= 0) {
                    throw new RuntimeException("Seleccione el tipo de animal que recibió el servicio.");
                }

                $stmtPrecio = $conexion->prepare(
                    "SELECT ps.precio
                     FROM precio_servicio ps
                     INNER JOIN tipo_animal ta ON ta.id = ps.idTipoAnimal
                     WHERE ps.idTipoServicio = ? AND ps.idTipoAnimal = ? AND ta.activo = TRUE"
                );
                $stmtPrecio->execute([$idTipoServicio, $idTipoAnimal]);
                $precioBase = $stmtPrecio->fetchColumn();

                if ($precioBase === false) {
                    throw new RuntimeException("No hay un precio configurado para ese servicio y ese tipo de animal.");
                }
                $precio = (int) $precioBase;
            }

            if ($precio <= 0) {
                throw new RuntimeException("El monto total debe ser mayor a cero.");
            }
            if ($precio > MONTO_MAXIMO_FACTURA) {
                throw new RuntimeException("El monto total de la factura supera el máximo permitido.");
            }

            // 3. Insertar factura
            $numeroAsignado = generarNumeroFactura($conexion, true);
            $stmtInsert = $conexion->prepare("INSERT INTO factura (numeroFactura, fechaEmisionFactura, montoTotalFactura, idCliente) VALUES (?, ?, ?, ?)");
            $stmtInsert->execute([$numeroAsignado, $fechaEmisionFactura, $precio, $idCliente]);
            $idFactura = (int) $conexion->lastInsertId();

            // 4. Guardar el detalle (qué servicio, qué animal y a qué precio),
            //    igual que factura_producto guarda el detalle de una compra
            $stmtDetalle = $conexion->prepare("INSERT INTO factura_servicio (idFactura, idTipoServicio, idTipoAnimal, precio) VALUES (?, ?, ?, ?)");
            $stmtDetalle->execute([$idFactura, $idTipoServicio, $idTipoAnimal, $precio]);

            $conexion->commit();

            // Bitácora de acciones (ver registro_acciones.php)
            registrarAccion(
                $conexion,
                "Alta de factura de servicio N° $numeroAsignado (idFactura $idFactura): "
                . "servicio id $idTipoServicio"
                . ($idTipoAnimal !== null ? ", tipo de animal id $idTipoAnimal" : '')
                . ", cliente id $idCliente, total $precio"
            );

            $exito = true;
        } catch (RuntimeException $e) {
            if ($conexion->inTransaction()) {
                $conexion->rollBack();
            }
            $numeroAsignado = null;
            $error = $e->getMessage();
        } catch (PDOException $e) {
            if ($conexion->inTransaction()) {
                $conexion->rollBack();
            }
            $numeroAsignado = null;
            $error = "Error al subir la factura al sistema: " . $e->getMessage();
        }

    } elseif ($items === null) {
        $error = "Agregue al menos un producto válido a la factura.";
    } else {
        try {
            $conexion->beginTransaction();

            // 1. Bloquear las filas de los productos involucrados y leer stock/precio REALES
            $ids = array_keys($items);
            $marcadores = implode(',', array_fill(0, count($ids), '?'));
            $stmtProd = $conexion->prepare(
                "SELECT idProducto, nombreProducto, precioProducto, stockProducto, activo
                 FROM producto WHERE idProducto IN ($marcadores) ORDER BY idProducto FOR UPDATE"
            );
            $stmtProd->execute($ids);

            $productos = [];
            foreach ($stmtProd->fetchAll() as $p) {
                $productos[(int) $p['idProducto']] = $p;
            }

            // 2. Validar existencia, estado y stock; calcular el total con precios de la BD
            $total = 0;
            foreach ($items as $idProducto => $cantidad) {
                if (!isset($productos[$idProducto]) || !$productos[$idProducto]['activo']) {
                    throw new RuntimeException("Uno de los productos seleccionados ya no está disponible.");
                }
                $p = $productos[$idProducto];
                $stock = (int) $p['stockProducto'];
                if ($stock < $cantidad) {
                    throw new RuntimeException(
                        "Stock insuficiente de \"{$p['nombreProducto']}\": se pidieron $cantidad y hay $stock disponibles."
                    );
                }
                $total += (int) $p['precioProducto'] * $cantidad;
            }

            if ($total <= 0) {
                throw new RuntimeException("El monto total debe ser mayor a cero.");
            }
            if ($total > MONTO_MAXIMO_FACTURA) {
                throw new RuntimeException("El monto total de la factura supera el máximo permitido.");
            }

            // 3. Insertar factura
            $numeroAsignado = generarNumeroFactura($conexion, true);
            $stmtInsert = $conexion->prepare("INSERT INTO factura (numeroFactura, fechaEmisionFactura, montoTotalFactura, idCliente) VALUES (?, ?, ?, ?)");
            $stmtInsert->execute([$numeroAsignado, $fechaEmisionFactura, $total, $idCliente]);
            $idFactura = (int) $conexion->lastInsertId();

            // 4. Guardar detalle y descontar stock
            $stmtDetalle = $conexion->prepare("INSERT INTO factura_producto (idFactura, idProducto, cantidad, precioUnitario) VALUES (?, ?, ?, ?)");
            $stmtStock = $conexion->prepare("UPDATE producto SET stockProducto = stockProducto - ? WHERE idProducto = ? AND stockProducto >= ?");

            foreach ($items as $idProducto => $cantidad) {
                $stmtDetalle->execute([$idFactura, $idProducto, $cantidad, (int) $productos[$idProducto]['precioProducto']]);
                $stmtStock->execute([$cantidad, $idProducto, $cantidad]);
                if ($stmtStock->rowCount() !== 1) {
                    throw new RuntimeException("No se pudo descontar el stock de un producto. Intente nuevamente.");
                }
            }

            $conexion->commit();

            // Bitácora de acciones (ver registro_acciones.php)
            registrarAccion(
                $conexion,
                "Alta de factura de compra N° $numeroAsignado (idFactura $idFactura): "
                . count($items) . " producto(s) distinto(s), cliente id $idCliente, total $total"
            );

            $exito = true;
        } catch (RuntimeException $e) {
            if ($conexion->inTransaction()) {
                $conexion->rollBack();
            }
            $numeroAsignado = null;
            $error = $e->getMessage();
        } catch (PDOException $e) {
            if ($conexion->inTransaction()) {
                $conexion->rollBack();
            }
            $numeroAsignado = null;
            $error = "Error al subir la factura al sistema: " . $e->getMessage();
        }
    }
}

// Se consulta DESPUÉS del POST para que el stock mostrado ya refleje la venta recién hecha
$stmtProductos = $conexion->prepare("SELECT idProducto, nombreProducto, precioProducto, stockProducto FROM producto WHERE activo = TRUE ORDER BY nombreProducto ASC");
$stmtProductos->execute();
$productosDisponibles = $stmtProductos->fetchAll();

// Servicios facturables (los que se piden en una cita) con sus precios, para
// armar el menú de "facturar un servicio". Cada servicio trae:
//   - precioFijo: si no es null, el servicio cuesta siempre lo mismo y no pide
//     tipo de animal (el caso de la consulta);
//   - animales: los tipos de animal con precio cargado para ESE servicio.
// Todo sale de la base, así que agregar servicios, animales o precios no
// requiere tocar este archivo.
$serviciosFacturables = [];
foreach ($conexion->query("SELECT id, servicio, precioFijo FROM tipo_servicio WHERE esCita = 1 ORDER BY id")->fetchAll() as $s) {
    $serviciosFacturables[(int) $s['id']] = [
        'id'         => (int) $s['id'],
        'nombre'     => ucfirst((string) $s['servicio']),
        'precioFijo' => ($s['precioFijo'] !== null) ? (int) $s['precioFijo'] : null,
        'animales'   => []
    ];
}

$sqlPreciosServicio = "SELECT ps.idTipoServicio, ps.idTipoAnimal, ta.nombre, ps.precio
                       FROM precio_servicio ps
                       INNER JOIN tipo_animal ta ON ta.id = ps.idTipoAnimal
                       WHERE ta.activo = TRUE
                       ORDER BY ta.nombre ASC";
foreach ($conexion->query($sqlPreciosServicio)->fetchAll() as $p) {
    $idServ = (int) $p['idTipoServicio'];
    if (isset($serviciosFacturables[$idServ])) {
        $serviciosFacturables[$idServ]['animales'][] = [
            'id'     => (int) $p['idTipoAnimal'],
            'nombre' => ucfirst((string) $p['nombre']),
            'precio' => (int) $p['precio']
        ];
    }
}

$serviciosFacturablesJson = json_encode(array_values($serviciosFacturables), JSON_UNESCAPED_UNICODE);

$numeroSugerido = generarNumeroFactura($conexion);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/../!CSS/basecss.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <script src="https://unpkg.com/scrollreveal"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <link rel="icon" type="image/png" href="/../!Imagenes/favicon.png">
    <title>Veterinaria del Mar</title>
</head>

<body>
    <!-- DETAIL: HIDE OVERFLOW-X -->
    <div style="overflow-x: hidden;">

    <!-- TOP BAR -->
    <?php include '../topbar.php'; ?>

    <!-- ★★★★★★★★★★★★★★★★★★★★★★★★★ -->

    <!-- FORMULARIO SUBIR FACTURA -->
    <div class="container-fluid margins mt-5 pt-4">
        <div class="row justify-content-center">
            <div class="col-lg-12 col-md-7">
                <main class="form-signin m-5 mt-0">

                    <?php if (!empty($exito)): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            ✔ Factura N° <?php echo htmlspecialchars((string) $numeroAsignado); ?> subida con exito al sistema!<?php echo ($tipoFactura === 'compra') ? ' El stock fue actualizado.' : ''; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($error)): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            ✘ <?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>

                    <br><form method="POST">
                        <h1 class="h3 mb-3 fw-normal text-secondary">Registrar factura</h1>

                        <div class="form-floating">
                            <select name="idCliente" id="idCliente" class="form-select" required>
                                <option value="" disabled selected>Seleccione un cliente</option>
                                <?php foreach ($clientes as $cliente): ?>
                                    <option value="<?php echo htmlspecialchars($cliente['idCliente']); ?>">
                                        <?php echo htmlspecialchars($cliente['nombreCliente']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <label for="idCliente">Cliente</label>
                        </div><br>

                        <div class="form-floating">
                            <select name="tipoFactura" id="tipoFactura" class="form-select">
                                <option value="compra" <?php echo ($tipoFactura === 'compra') ? 'selected' : ''; ?>>Facturar una compra</option>
                                <option value="servicio" <?php echo ($tipoFactura === 'servicio') ? 'selected' : ''; ?>>Facturar un servicio</option>
                            </select>
                            <label for="tipoFactura">Tipo de factura</label>
                        </div><br>

                        <!-- COMPRA: el menú de productos de siempre (lo maneja calculoFactura.js) -->
                        <div id="seccionCompra">
                        <div class="mb-3">
                            <label for="selectProductoFactura" class="form-label">Productos de la factura</label>

                            <div class="input-group mb-2">
                                <select id="selectProductoFactura" class="form-select">
                                    <option value="" disabled selected>Seleccione un producto</option>
                                    <?php foreach ($productosDisponibles as $producto):
                                        $sinStock = ((int) $producto['stockProducto']) <= 0; ?>
                                        <option
                                            value="<?php echo htmlspecialchars($producto['idProducto']); ?>"
                                            data-nombre="<?php echo htmlspecialchars($producto['nombreProducto'], ENT_QUOTES, 'UTF-8'); ?>"
                                            data-precio="<?php echo htmlspecialchars($producto['precioProducto']); ?>"
                                            data-stock="<?php echo (int) $producto['stockProducto']; ?>"
                                            <?php echo $sinStock ? 'disabled' : ''; ?>
                                        >
                                            <?php echo htmlspecialchars($producto['nombreProducto']); ?>
                                            — $<?php echo htmlspecialchars($producto['precioProducto']); ?>
                                            <?php echo $sinStock ? '(sin stock)' : '(stock: ' . (int) $producto['stockProducto'] . ')'; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <button type="button" id="btnAgregarProductoFactura" class="btn bg-info-subtle border border-info-subtle">Agregar</button>
                            </div>

                            <!-- Se completa dinámicamente con calculoFactura.js -->
                            <ul id="listaProductosFactura" class="list-group mb-2"></ul>

                            <div class="d-flex justify-content-between align-items-center text-secondary">
                                <strong>Total:</strong>
                                <strong id="totalFacturaTexto">$0</strong>
                            </div>

                            <!-- JSON [{idProducto, cantidad}]; el servidor recalcula el monto con los precios de la BD -->
                            <input type="hidden" id="itemsFactura" name="itemsFactura" value="[]">
                        </div><br>
                        </div>

                        <!-- SERVICIO: lo maneja facturaServicio.js. Los servicios, los animales
                             y sus precios llegan desde la base en data-servicios; el precio
                             que se muestra es solo informativo, el servidor lo vuelve a leer. -->
                        <div id="seccionServicio" class="d-none" data-servicios="<?php echo htmlspecialchars($serviciosFacturablesJson, ENT_QUOTES, 'UTF-8'); ?>">
                            <div class="form-floating">
                                <select name="idTipoServicio" id="idTipoServicio" class="form-select">
                                    <option value="" disabled selected>Seleccione un servicio</option>
                                    <?php foreach ($serviciosFacturables as $s): ?>
                                        <option value="<?php echo $s['id']; ?>"><?php echo htmlspecialchars($s['nombre'], ENT_QUOTES, 'UTF-8'); ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <label for="idTipoServicio">Servicio</label>
                            </div><br>

                            <!-- Se muestra solo si el servicio elegido depende del tipo de animal
                                 (los de precio fijo, como la consulta, no lo piden) -->
                            <div id="bloqueTipoAnimal" class="form-floating d-none">
                                <select name="idTipoAnimal" id="idTipoAnimal" class="form-select"></select>
                                <label for="idTipoAnimal">Tipo de animal</label>
                            </div>

                            <div class="d-flex justify-content-between align-items-center text-secondary mt-3">
                                <strong>Total:</strong>
                                <strong id="totalServicioTexto">$0</strong>
                            </div>
                            <br>
                        </div>

                        <button class="btn bg-info-subtle me-2 border border-info-subtle rounded-5 w-100" type="submit">Registrar</button><br><br>
                    </form>

                    <div class="d-flex justify-content-center">
                        <a href="/Subida/manejarfacturas.php" class="text-secondary nohighlight"><button class="btn bg-light-subtle border border-secondary-subtle rounded-5">Ir al menu de manejo</button></a><br><br>
                    </div>

                </main>
            </div>
        </div>
    </div><br>
    <!-- END: FORMULARIO SUBIR FACTURA -->

    <!-- ★★★★★★★★★★★★★★★★★★★★★★★★★ -->

    <!-- JS MANAGERS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
    <script src="/../!JS/calculoFactura.js"></script>
    <script src="/../!JS/facturaServicio.js"></script>
    </div>
</body>
</html>