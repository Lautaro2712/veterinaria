<?php
// Carga e interpreta el código de conexion.php
require_once __DIR__ . "/../!PHPchecks/conexion.php";
require_once __DIR__ . "/../!PHPchecks/auth.php";
requireRole(ROL_EMPLEADO);

date_default_timezone_set('America/Montevideo');

// Variables para controlar la visualización de mensajes en el HTML
$exito = false;
$error = null;
$numeroFactura  = "";
$fechaEmisionFactura = '';
$montoTotalFactura  = "";

$sql = "SELECT idCliente, nombreCliente FROM cliente ORDER BY nombreCliente ASC";
$stmt = $conexion->prepare($sql);
$stmt->execute();
$clientes = $stmt->fetchAll();

// Evalúa la variable global $_SERVER para verificar si el usuario envió el formulario por POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // 1. RECEPCIÓN Y VALIDACIÓN DE DATOS
    $numeroFactura = $_POST["numeroFactura"] ?? "";
    $fechaEmisionFactura = date('Y-m-d');
    $montoTotalFactura = $_POST["montoTotalFactura"]  ?? "";
    $idCliente = $_POST['idCliente']  ?? "";

    // Comprobamos que los campos requeridos no estén vacíos
    if (empty($numeroFactura) || empty($montoTotalFactura) || empty($idCliente)) {
        $error = "Por favor, complete todos los campos solicitados.";
    } else {
       // -----------------------------------------------------------------------------------------
    // 2. VERIFICACIÓN DE DISPONIBILIDAD E INSERCIÓN
    // -----------------------------------------------------------------------------------------

    // Paso A: Comprobar si ya existe esa ficha clinica
    $sqlCheck = "SELECT idFactura FROM factura WHERE numeroFactura = ? AND fechaEmisionFactura = ? AND montoTotalFactura = ? AND idCliente = ?";
    try {
        $stmtCheck = $conexion->prepare($sqlCheck);
        $stmtCheck->execute([$numeroFactura, $fechaEmisionFactura, $montoTotalFactura, $idCliente]);

        // Si la consulta devuelve 1 o más filas, la ficha clinica existe
        if ($stmtCheck->fetchColumn() !== false) {
            $error = "Esa factura ya existe.";
        } else {
            // Paso B: Si no existe, proceder con la inserción
            $sqlInsert = "INSERT INTO factura (numeroFactura, fechaEmisionFactura, montoTotalFactura, idCliente) VALUES (?, ?, ?, ?, ?)";
            $stmtInsert = $conexion->prepare($sqlInsert);
            $stmtInsert->execute([$numeroFactura, $fechaEmisionFactura, $montoTotalFactura, $idCliente]);
            $exito = true;
        }
    } catch (PDOException $e) {
        $error = "Error al subir la factura al sistema: " . $e->getMessage();
    }
    }
}
// No se puede elegir un dia mas adelante al de hoy a la hora de elegir la fecha de emisión de la factura
$fechaMaxima = date('Y-m-d');
?>

<!DOCTYPE html>
<html lang="en"></html>
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

    <!-- REGISTER FORM -->
    <div class="container-fluid margins mt-5 pt-4">
        <div class="row justify-content-center">

            <!-- FORMULARIO -->
            <div class="col-lg-12 col-md-7">
                <main class="form-signin m-5 mt-0">
                    <?php if (!empty($exito)): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            ✔ Factura subida con exito al sistema!
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
                        <h1 class="h3 mb-3 fw-normal text-secondary">Subir factura</h1>
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
                            <input type="number" class="form-control" id="numeroFactura" name="numeroFactura" placeholder="201547846" required>
                            <label for="numeroFactura">Numero de la factura</label>
                        </div><br>
                        <div class="form-floating">
                            <input type="number" class="form-control" id="montoTotalFactura" name="montoTotalFactura" min="1" placeholder="150" required>
                            <label for="montoTotalFactura">Monto total en pesos</label>
                        </div><br>
                        <button class="btn bg-info-subtle me-2 border border-info-subtle rounded-5 w-100" type="submit">Subir al sistema</button><br><br>
                    </form>
                </main>
            </div>
                                  
        </div>
    </div><br>
    <!-- END: REGISTER FORM -->

    <!-- ★★★★★★★★★★★★★★★★★★★★★★★★★ -->

    <!-- JS MANAGERS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
</body>
</html>