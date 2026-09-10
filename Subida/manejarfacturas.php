<?php
require_once __DIR__ . "/../!PHPchecks/conexion.php";
require_once __DIR__ . "/../!PHPchecks/auth.php";
requireRole(ROL_EMPLEADO);

// Trae a todas las facturas activas de la base de datos, junto con el
// nombre del cliente al que corresponden. Se excluyen las dadas de baja
// (activo = FALSE) para que no reaparezcan al recargar la página
// después de eliminarlas con el menú contextual.
$sql = "SELECT f.idFactura, f.numeroFactura, f.solicitaReciboFactura,
               f.fechaEmisionFactura, f.montoTotalFactura,
               f.idCliente, c.nombreCliente
        FROM factura f
        LEFT JOIN cliente c ON f.idCliente = c.idCliente
        WHERE f.activo = TRUE
        ORDER BY f.fechaEmisionFactura DESC, f.idFactura DESC";
$stmt = $conexion->prepare($sql);
$stmt->execute();
$items = $stmt->fetchAll();

// Lista de clientes activos, para armar el <select> de la celda de
// Cliente en edición inline (evita que el empleado tenga que escribir
// el ID a mano).
$sqlClientes = "SELECT idCliente, nombreCliente FROM cliente WHERE activo = TRUE ORDER BY nombreCliente ASC";
$stmtClientes = $conexion->prepare($sqlClientes);
$stmtClientes->execute();
$clientesDisponibles = $stmtClientes->fetchAll();

$opcionesCliente = array_map(function ($c) {
    return ['valor' => (string) $c['idCliente'], 'texto' => $c['nombreCliente']];
}, $clientesDisponibles);

$opcionesClienteJson = json_encode($opcionesCliente, JSON_UNESCAPED_UNICODE);
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
    <link rel="icon" type="image/png" href="/!Imagenes/favicon.png">
    <title>Veterinaria del Mar</title>
</head>

<body>
    <!-- DETAIL: HIDE OVERFLOW-X -->
    <div style="overflow-x: hidden;">

    <!-- TOP BAR -->
    <?php include '../topbar.php'; ?>


    <!-- ★★★★★★★★★★★★★★★★★★★★★★★★★ -->

    <!-- MANEJAR FACTURAS (READ ONLY) -->

    <!-- MENU CONTEXTUAL (compartido por todas las celdas editables) -->
    <div id="menuContextual">
        <button type="button" id="btnModificar">Modificar</button>
        <button type="button" id="btnDarBaja">Dar de baja</button>
    </div>

    <!-- LISTA -->
    <div class="container mt-5 pt-5"><div class="h-100 p-5 bg-light border rounded-3">  
        <h1 class="h3 mb-3 fw-normal text-secondary">Datos de facturas</h1><br>
        <ul class="list-group list-group-horizontal pb-1">
            <li class="list-group-item list-group-item-action list-group-item-light">ID</li>
            <li class="list-group-item list-group-item-action list-group-item-light">Cliente</li>
            <li class="list-group-item list-group-item-action list-group-item-light">Número</li>
            <li class="list-group-item list-group-item-action list-group-item-light">¿Recibo?</li>
            <li class="list-group-item list-group-item-action list-group-item-light">Fecha de emisión</li>
            <li class="list-group-item list-group-item-action list-group-item-light">Monto total</li>
        </ul>

        <?php if (empty($items)): ?>
            <p class="text-secondary mt-3">No hay facturas registradas.</p>
        <?php else: ?>
            <?php foreach ($items as $item): ?>
                <ul class="list-group list-group-horizontal">
                    <li class="list-group-item list-group-item-action list-group-item-light cell-break">
                        <?php echo htmlspecialchars($item["idFactura"]); ?>
                    </li>
                    <li class="list-group-item list-group-item-action list-group-item-light cell-break editable-cell"
                        data-tabla="factura"
                        data-id="<?php echo htmlspecialchars($item["idFactura"]); ?>"
                        data-campo="idCliente"
                        data-tipo="seleccion"
                        data-opciones="<?php echo htmlspecialchars($opcionesClienteJson, ENT_QUOTES, 'UTF-8'); ?>"
                        data-valor-crudo="<?php echo htmlspecialchars((string) $item["idCliente"]); ?>">
                        <?php echo $item["nombreCliente"] !== null ? htmlspecialchars($item["nombreCliente"]) : "Sin cliente"; ?>
                    </li>
                    <li class="list-group-item list-group-item-action list-group-item-light cell-break">
                        <?php echo htmlspecialchars($item["numeroFactura"]); ?>
                    </li>
                    <li class="list-group-item list-group-item-action list-group-item-light cell-break editable-cell"
                        data-tabla="factura"
                        data-id="<?php echo htmlspecialchars($item["idFactura"]); ?>"
                        data-campo="solicitaReciboFactura"
                        data-tipo="booleano">
                        <?php echo ((int) $item["solicitaReciboFactura"] === 1) ? "Sí" : "No"; ?>
                    </li>
                    <li class="list-group-item list-group-item-action list-group-item-light cell-break editable-cell"
                        data-tabla="factura"
                        data-id="<?php echo htmlspecialchars($item["idFactura"]); ?>"
                        data-campo="fechaEmisionFactura"
                        data-tipo="fecha">
                        <?php echo htmlspecialchars($item["fechaEmisionFactura"]); ?>
                    </li>
                    <li class="list-group-item list-group-item-action list-group-item-light cell-break editable-cell"
                        data-tabla="factura"
                        data-id="<?php echo htmlspecialchars($item["idFactura"]); ?>"
                        data-campo="montoTotalFactura"
                        data-tipo="numero">
                        <?php echo htmlspecialchars($item["montoTotalFactura"]); ?>
                    </li>
                </ul>
            <?php endforeach; ?>
        <?php endif; ?>
    </div></div></div>

    <!-- END: MANEJAR FACTURAS (READ ONLY) -->

    <!-- ★★★★★★★★★★★★★★★★★★★★★★★★★ -->


    <!-- JS MANAGERS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
    <script src="/../!JS/edicionInline.js?v=5" defer></script>
    <script src="/../!JS/busquedainline.js"></script>
</body>
</html>