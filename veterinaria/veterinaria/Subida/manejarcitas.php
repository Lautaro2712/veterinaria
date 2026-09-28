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

    <!-- LISTA -->
    <div class="container mt-5 pt-5"><div class="h-100 p-5 bg-light border rounded-3" data-orden-nombre="nombreCliente">  
        <h1 class="h3 mb-3 fw-normal text-secondary">Datos de citas</h1><br>
        <a href="/Subida/subircita.php" class="text-secondary nohighlight"><button class="btn btn-outline-secondary me-2 border border-secondary-subtle rounded-5">Crear cita</button></a>
        <button class="btn btn-outline-secondary me-2 border border-secondary-subtle rounded-5 btn-orden-manejo" title="Ordenar alfabéticamente" aria-pressed="false"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-filter-circle" viewBox="0 0 16 16"><path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14m0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16"/><path d="M7 11.5a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 0 1h-1a.5.5 0 0 1-.5-.5m-2-3a.5.5 0 0 1 .5-.5h5a.5.5 0 0 1 0 1h-5a.5.5 0 0 1-.5-.5m-2-3a.5.5 0 0 1 .5-.5h9a.5.5 0 0 1 0 1h-9a.5.5 0 0 1-.5-.5"/></svg></button>
        <button class="btn btn-outline-secondary me-2 border border-secondary-subtle rounded-5" onClick="window.print()"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-printer" viewBox="0 0 16 16"><path d="M2.5 8a.5.5 0 1 0 0-1 .5.5 0 0 0 0 1"/><path d="M5 1a2 2 0 0 0-2 2v2H2a2 2 0 0 0-2 2v3a2 2 0 0 0 2 2h1v1a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2v-1h1a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-1V3a2 2 0 0 0-2-2zM4 3a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1v2H4zm1 5a2 2 0 0 0-2 2v1H2a1 1 0 0 1-1-1V7a1 1 0 0 1 1-1h12a1 1 0 0 1 1 1v3a1 1 0 0 1-1 1h-1v-1a2 2 0 0 0-2-2zm7 2v3a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1v-3a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1"/></svg></button><br><br>
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
                    <li class="list-group-item list-group-item-action list-group-item-light cell-break"
                        data-celda-orden>
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
    <script src="/../!JS/ordeninline.js"></script>
</body>
</html>