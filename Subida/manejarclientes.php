<?php
require_once __DIR__ . "/../!PHPchecks/conexion.php";
require_once __DIR__ . "/../!PHPchecks/auth.php";
requireRole(ROL_EMPLEADO);

// Trae únicamente a los clientes activos de la base de datos
$sql = "SELECT idCliente, nombreCliente, apellidoCliente, correoCliente, telefonoCliente
        FROM cliente
        WHERE activo = TRUE
        ORDER BY apellidoCliente ASC, nombreCliente ASC";
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

    <!-- MANEJAR CLIENTE (READ ONLY) -->

    <!-- LISTA -->
    <div class="container mt-5 pt-5"><div class="h-100 p-5 bg-light border rounded-3">  
        <h1 class="h3 mb-3 fw-normal text-secondary">Datos de clientes</h1><br>
        <ul class="list-group list-group-horizontal pb-1">
            <li class="list-group-item list-group-item-action list-group-item-light">ID</li>
            <li class="list-group-item list-group-item-action list-group-item-light">Nombre</li>
            <li class="list-group-item list-group-item-action list-group-item-light">Apellido</li>
            <li class="list-group-item list-group-item-action list-group-item-light">Email</li>
            <li class="list-group-item list-group-item-action list-group-item-light">Teléfono</li>
        </ul>

        <?php if (empty($items)): ?>
            <p class="text-secondary mt-3">No hay clientes registrados.</p>
        <?php else: ?>
           <?php foreach ($items as $item): ?>

    <ul class="list-group list-group-horizontal">

        <!-- ID: no modificable -->
        <li class="list-group-item list-group-item-action list-group-item-light cell-break">
            <?php echo htmlspecialchars($item["idCliente"]); ?>
        </li>

        <li class="list-group-item list-group-item-action list-group-item-light cell-break editable-cell"
            data-tabla="cliente"
            data-id="<?php echo htmlspecialchars($item["idCliente"]); ?>"
            data-campo="nombreCliente"
            data-tipo="texto">
            <?php echo htmlspecialchars($item["nombreCliente"]); ?>
        </li>

        <li class="list-group-item list-group-item-action list-group-item-light cell-break editable-cell"
            data-tabla="cliente"
            data-id="<?php echo htmlspecialchars($item["idCliente"]); ?>"
            data-campo="apellidoCliente"
            data-tipo="texto">
            <?php echo htmlspecialchars($item["apellidoCliente"]); ?>
        </li>

        <!-- Correo: modificable -->
        <li class="list-group-item list-group-item-action list-group-item-light cell-break editable-cell"
            data-tabla="cliente"
            data-id="<?php echo htmlspecialchars($item["idCliente"]); ?>"
            data-campo="correoCliente"
            data-tipo="texto">
            <?php echo htmlspecialchars($item["correoCliente"]); ?>
        </li>

        <!-- Teléfono: modificable -->
        <li class="list-group-item list-group-item-action list-group-item-light cell-break editable-cell"
            data-tabla="cliente"
            data-id="<?php echo htmlspecialchars($item["idCliente"]); ?>"
            data-campo="telefonoCliente"
            data-tipo="numero">
            <?php echo htmlspecialchars($item["telefonoCliente"]); ?>
        </li>

    </ul>

<?php endforeach; ?>
        <?php endif; ?>
    </div></div></div>

    <!-- END: MANEJAR CLIENTE (READ ONLY) -->

    <!-- ★★★★★★★★★★★★★★★★★★★★★★★★★ -->


    <!-- JS MANAGERS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
    <script src="/../!JS/busquedainline.js"></script>
    <script src="/../!JS/edicionInline.js"></script>
</body>
</html>