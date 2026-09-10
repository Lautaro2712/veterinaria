<?php
require_once __DIR__ . "/../!PHPchecks/conexion.php";
require_once __DIR__ . "/../!PHPchecks/auth.php";
requireRole(ROL_EMPLEADO);

// Trae a todos los productos activos de la base de datos, junto con el
// nombre de su proveedor. Se excluyen los dados de baja (activo = FALSE)
// para que no reaparezcan al recargar la página después de eliminarlos
// con el menú contextual.
$sql = "SELECT p.idProducto, p.nombreProducto, p.tipoProducto, p.precioProducto, p.stockProducto,
               p.idProveedor, pr.nombreProveedor
        FROM producto p
        LEFT JOIN proveedor pr ON p.idProveedor = pr.idProveedor
        WHERE p.activo = TRUE
        ORDER BY p.nombreProducto ASC";
$stmt = $conexion->prepare($sql);
$stmt->execute();
$items = $stmt->fetchAll();

// Lista de proveedores activos, para armar el <select> de la celda de
// Proveedor en edición inline (evita que el empleado tenga que escribir
// el ID a mano).
$sqlProveedores = "SELECT idProveedor, nombreProveedor FROM proveedor WHERE activo = TRUE ORDER BY nombreProveedor ASC";
$stmtProveedores = $conexion->prepare($sqlProveedores);
$stmtProveedores->execute();
$proveedoresDisponibles = $stmtProveedores->fetchAll();

$opcionesProveedor = array_map(function ($p) {
    return ['valor' => (string) $p['idProveedor'], 'texto' => $p['nombreProveedor']];
}, $proveedoresDisponibles);

$opcionesProveedorJson = json_encode($opcionesProveedor, JSON_UNESCAPED_UNICODE);
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

    <!-- MANEJAR PRODUCTOS (READ ONLY) -->

    <!-- MENU CONTEXTUAL (compartido por todas las celdas editables) -->
    <div id="menuContextual">
        <button type="button" id="btnModificar">Modificar</button>
        <button type="button" id="btnDarBaja">Dar de baja</button>
    </div>
    
    <!-- LISTA -->
    <div class="container mt-5 pt-5"><div class="h-100 p-5 bg-light border rounded-3">  
        <h1 class="h3 mb-3 fw-normal text-secondary">Datos de productos</h1><br>
        <ul class="list-group list-group-horizontal pb-1">
            <li class="list-group-item list-group-item-action list-group-item-light">ID</li>
            <li class="list-group-item list-group-item-action list-group-item-light">Nombre</li>
            <li class="list-group-item list-group-item-action list-group-item-light">Tipo</li>
            <li class="list-group-item list-group-item-action list-group-item-light">Precio</li>
            <li class="list-group-item list-group-item-action list-group-item-light">Stock</li>
            <li class="list-group-item list-group-item-action list-group-item-light">Proveedor</li>
        </ul>

        <?php if (empty($items)): ?>
            <p class="text-secondary mt-3">No hay productos registrados.</p>
        <?php else: ?>
            <?php foreach ($items as $item): ?>
                <ul class="list-group list-group-horizontal">
                    <li class="list-group-item list-group-item-action list-group-item-light cell-break">
                        <?php echo htmlspecialchars($item["idProducto"]); ?>
                    </li>
                    <li class="list-group-item list-group-item-action list-group-item-light cell-break editable-cell"
                        data-tabla="producto"
                        data-id="<?php echo htmlspecialchars($item["idProducto"]); ?>"
                        data-campo="nombreProducto"
                        data-tipo="texto">
                        <?php echo htmlspecialchars($item["nombreProducto"]); ?>
                    </li>
                    <li class="list-group-item list-group-item-action list-group-item-light cell-break editable-cell"
                        data-tabla="producto"
                        data-id="<?php echo htmlspecialchars($item["idProducto"]); ?>"
                        data-campo="tipoProducto"
                        data-tipo="texto">
                        <?php echo htmlspecialchars($item["tipoProducto"]); ?>
                    </li>
                    <li class="list-group-item list-group-item-action list-group-item-light cell-break editable-cell"
                        data-tabla="producto"
                        data-id="<?php echo htmlspecialchars($item["idProducto"]); ?>"
                        data-campo="precioProducto"
                        data-tipo="numero">
                        <?php echo htmlspecialchars($item["precioProducto"]); ?>
                    </li>
                    <li class="list-group-item list-group-item-action list-group-item-light cell-break editable-cell"
                        data-tabla="producto"
                        data-id="<?php echo htmlspecialchars($item["idProducto"]); ?>"
                        data-campo="stockProducto"
                        data-tipo="numero">
                        <?php echo htmlspecialchars($item["stockProducto"]); ?>
                    </li>
                    <li class="list-group-item list-group-item-action list-group-item-light cell-break editable-cell"
                        data-tabla="producto"
                        data-id="<?php echo htmlspecialchars($item["idProducto"]); ?>"
                        data-campo="idProveedor"
                        data-tipo="seleccion"
                        data-opciones="<?php echo htmlspecialchars($opcionesProveedorJson, ENT_QUOTES, 'UTF-8'); ?>"
                        data-valor-crudo="<?php echo htmlspecialchars((string) $item["idProveedor"]); ?>">
                        <?php echo $item["nombreProveedor"] !== null ? htmlspecialchars($item["nombreProveedor"]) : "Sin proveedor"; ?>
                    </li>
                </ul>
            <?php endforeach; ?>
        <?php endif; ?>
    </div></div></div>

    <!-- END: MANEJAR PRODUCTOS (READ ONLY) -->

    <!-- ★★★★★★★★★★★★★★★★★★★★★★★★★ -->


    <!-- JS MANAGERS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
    <script src="/../!JS/edicionInline.js?v=5" defer></script>
    <script src="/../!JS/busquedainline.js"></script>
</body>
</html>