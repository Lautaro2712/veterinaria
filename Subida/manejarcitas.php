<?php
require_once __DIR__ . "/../!PHPchecks/conexion.php";
require_once __DIR__ . "/../!PHPchecks/auth.php";
requireRole(ROL_EMPLEADO);

// Da de baja automáticamente las citas cuya fecha y hora ya pasaron
$sqlBajaCitas = "UPDATE servicio
                 SET activo = FALSE
                 WHERE tipoServicio = 'Cita'
                 AND activo = TRUE
                 AND TIMESTAMP(fechaServicio, horaServicio) < NOW()";

$stmtBajaCitas = $conexion->prepare($sqlBajaCitas);
$stmtBajaCitas->execute();

// Trae a todas las citas activas, junto con el nombre y apellido del
// cliente que la solicitó (LEFT JOIN por si el cliente fue dado de baja
// después de pedir la cita, para que la fila no desaparezca ni rompa).
$sql = "SELECT s.idServicio, s.tipoServicio, s.fechaServicio, s.horaServicio,
               s.idEmpleado, s.idCliente, s.idMascota,
               c.nombreCliente, c.apellidoCliente
        FROM servicio s
        LEFT JOIN cliente c ON s.idCliente = c.idCliente
        WHERE s.tipoServicio = 'Cita' AND s.activo = TRUE
        ORDER BY s.idServicio ASC, s.idMascota ASC;";
$stmt = $conexion->prepare($sql);
$stmt->execute();
$items = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
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

    <!-- MANEJAR CITAS (READ ONLY) -->

    <!-- MENU CONTEXTUAL (compartido por todas las celdas editables) -->
    <div id="menuContextual">
        <button type="button" id="btnModificar">Modificar</button>
        <button type="button" id="btnDarBaja">Dar de baja</button>
    </div>

    <!-- LISTA -->
    <div class="container mt-5 pt-5"><div class="h-100 p-5 bg-light border rounded-3">  
        <h1 class="h3 mb-3 fw-normal text-secondary">Datos de citas</h1><br>
        <ul class="list-group list-group-horizontal pb-1">
            <li class="list-group-item list-group-item-action list-group-item-light">ID</li>
            <li class="list-group-item list-group-item-action list-group-item-light">Cliente</li>
            <li class="list-group-item list-group-item-action list-group-item-light">Fecha</li>
            <li class="list-group-item list-group-item-action list-group-item-light">Hora</li>
        </ul>

        <?php if (empty($items)): ?>
            <p class="text-secondary mt-3">No hay citas registradas.</p>
        <?php else: ?>
            <?php foreach ($items as $item): ?>
                <ul class="list-group list-group-horizontal">
                    <li class="list-group-item list-group-item-action list-group-item-light cell-break">
                        <?php echo htmlspecialchars($item["idServicio"]); ?>
                    </li>
                    <li class="list-group-item list-group-item-action list-group-item-light cell-break">
                        <?php
                        echo $item["idCliente"] !== null
                            ? htmlspecialchars(trim($item["nombreCliente"] . ' ' . $item["apellidoCliente"]))
                            : "Sin cliente";
                        ?>
                    </li>
                    <li class="list-group-item list-group-item-action list-group-item-light cell-break editable-cell"
                        data-tabla="servicio"
                        data-id="<?php echo htmlspecialchars($item["idServicio"]); ?>"
                        data-campo="fechaServicio"
                        data-tipo="fecha">
                        <?php echo htmlspecialchars($item["fechaServicio"]); ?>
                    </li>
                    <li class="list-group-item list-group-item-action list-group-item-light cell-break editable-cell"
                        data-tabla="servicio"
                        data-id="<?php echo htmlspecialchars($item["idServicio"]); ?>"
                        data-campo="horaServicio"
                        data-tipo="hora">
                        <?php echo htmlspecialchars($item["horaServicio"]); ?>
                    </li>
                </ul>
            <?php endforeach; ?>
        <?php endif; ?>
    </div></div></div>

    <!-- END: MANEJAR CITAS (READ ONLY) -->

    <!-- ★★★★★★★★★★★★★★★★★★★★★★★★★ -->


    <!-- JS MANAGERS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
    <script src="/../!JS/edicionInline.js?v=2" defer></script>
    <script src="/../!JS/busquedainline.js?v=2" defer></script>
</body>
</html>