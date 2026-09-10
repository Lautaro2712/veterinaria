<?php
require_once __DIR__ . "/../!PHPchecks/conexion.php";
require_once __DIR__ . "/../!PHPchecks/auth.php";
requireRole(ROL_EMPLEADO);

// Trae a todos los proveedores de la base de datos
$sql = "SELECT rutProveedor, idProveedor, nombreProveedor, telefonoProveedor, correoProveedor
        FROM proveedor
        ORDER BY nombreProveedor ASC";
$stmt = $conexion->prepare($sql);
$stmt->execute();
$items = $stmt->fetchAll();
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

<!-- MENU CONTEXTUAL -->
    <div id="menuContextual">
        <button type="button" id="btnModificar">Modificar</button>
        <button type="button" id="btnDarBaja">Dar de baja</button>
    </div>

    <!-- ★★★★★★★★★★★★★★★★★★★★★★★★★ -->

    <!-- MANEJAR PROVEEDORES (READ ONLY) -->

    <!-- MENU CONTEXTUAL -->
    <div id="menuContextual">
        <button type="button" id="btnModificar">Modificar</button>
        <button type="button" id="btnDarBaja">Dar de baja</button>
    </div>

    <!-- LISTA -->
    <div class="container mt-5 pt-5"><div class="h-100 p-5 bg-light border rounded-3">  
        <h1 class="h3 mb-3 fw-normal text-secondary">Datos de proveedores</h1><br>
        <ul class="list-group list-group-horizontal pb-1">
            <li class="list-group-item list-group-item-action list-group-item-light">ID</li>
            <li class="list-group-item list-group-item-action list-group-item-light">RUT</li>
            <li class="list-group-item list-group-item-action list-group-item-light">Nombre</li>
            <li class="list-group-item list-group-item-action list-group-item-light">Teléfono</li>
            <li class="list-group-item list-group-item-action list-group-item-light">Email</li>
        </ul>

        <?php if (empty($items)): ?>
            <p class="text-secondary mt-3">No hay proveedores registrados.</p>
        <?php else: ?>
            <?php foreach ($items as $item): ?>
                <ul class="list-group list-group-horizontal">
                    <li class="list-group-item list-group-item-action list-group-item-light cell-break">
                        <?php echo htmlspecialchars($item["idProveedor"]); ?>
                    </li>
                    <li class="list-group-item list-group-item-action list-group-item-light cell-break editable-cell"
                        data-tabla="proveedor"
                        data-id="<?php echo htmlspecialchars($item["idProveedor"]); ?>"
                        data-campo="rutProveedor"
                        data-tipo="numero">
                        <?php echo htmlspecialchars($item["rutProveedor"]); ?>
                    </li>
                    <li class="list-group-item list-group-item-action list-group-item-light cell-break editable-cell"
                        data-tabla="proveedor"
                        data-id="<?php echo htmlspecialchars($item["idProveedor"]); ?>"
                        data-campo="nombreProveedor"
                        data-tipo="texto">
                        <?php echo htmlspecialchars($item["nombreProveedor"]); ?>
                    </li>
                    <li class="list-group-item list-group-item-action list-group-item-light cell-break editable-cell"
                        data-tabla="proveedor"
                        data-id="<?php echo htmlspecialchars($item["idProveedor"]); ?>"
                        data-campo="telefonoProveedor"
                        data-tipo="numero">
                        <?php echo htmlspecialchars($item["telefonoProveedor"]); ?>
                    </li>
                    <li class="list-group-item list-group-item-action list-group-item-light cell-break editable-cell"
                        data-tabla="proveedor"
                        data-id="<?php echo htmlspecialchars($item["idProveedor"]); ?>"
                        data-campo="correoProveedor"
                        data-tipo="texto">
                        <?php echo htmlspecialchars($item["correoProveedor"]); ?>
                    </li>
                </ul>
            <?php endforeach; ?>
        <?php endif; ?>
    </div></div></div>

    <!-- END: MANEJAR PROVEEDORES (READ ONLY) -->

    <!-- ★★★★★★★★★★★★★★★★★★★★★★★★★ -->


    <!-- JS MANAGERS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
    <script src="/!JS/edicionInline.js" defer></script>
    <script src="/!JS/busquedainline.js"></script>
</body>
</html>