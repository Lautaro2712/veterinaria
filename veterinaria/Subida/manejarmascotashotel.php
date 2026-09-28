<?php
require_once __DIR__ . "/../!PHPchecks/conexion.php";
require_once __DIR__ . "/../!PHPchecks/auth.php";
requireRole(ROL_EMPLEADO);

// Trae a todas las mascotas activas de la base de datos, junto con el
// nombre de su cliente. Se excluyen las dadas de baja (activo = FALSE)
// para que no reaparezcan al recargar la página después de eliminarlas
// con el menú contextual.
$sql = "SELECT rh.idRegistroHotel, rh.fechaIngresoRegistroHotel, rh.fechaPartidaRegistroHotel,
        rh.idMascotaRegistroHotel, rh.numeroCuchetaRegistroHotel,
        m.nombreMascota
        FROM registro_hotel rh
        LEFT JOIN mascota m ON rh.idMascotaRegistroHotel = m.idMascota
        WHERE rh.activo = TRUE
        ORDER BY rh.fechaPartidaRegistroHotel DESC, rh.idRegistroHotel DESC";
$stmt = $conexion->prepare($sql);
$stmt->execute();
$items = $stmt->fetchAll();

// Arma el estado de las 6 cuchetas del hotel para el dibujo: un array de
// 1 a 6, cada posición con el nombre de la mascota que la ocupa (o null
// si está libre). Como el sistema nunca deja pasar de 6 registros
// activos, ni repetir un mismo número de cucheta entre dos activos
// (ver subirmascotahotel.php), alcanza con recorrer $items una vez.
$cuchetas = array_fill(1, 6, null);
foreach ($items as $item) {
    $numero = (int) $item['numeroCuchetaRegistroHotel'];
    if ($numero >= 1 && $numero <= 6) {
        $cuchetas[$numero] = $item['nombreMascota'] ?? 'Sin mascota';
    }
}

// Lista de mascotas activas, para armar el <select> de la celda de
// Mascota en edición inline (evita que el empleado tenga que escribir
// el ID a mano).
$sqlMascotas = "SELECT idMascota, nombreMascota FROM mascota WHERE activo = TRUE ORDER BY nombreMascota ASC";
$sqlMascotas = $conexion->prepare($sqlMascotas);
$sqlMascotas->execute();
$mascotasDisponibles = $sqlMascotas->fetchAll();

$opcionesMascota = array_map(function ($rh) {
    return ['valor' => (string) $rh['idMascota'], 'texto' => $rh['nombreMascota']];
}, $mascotasDisponibles);

$opcionesMascotaJson = json_encode($opcionesMascota, JSON_UNESCAPED_UNICODE);
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

    <!-- ESTILOS DEL DIBUJO DE CUCHETAS DEL HOTEL -->
    <style>
        .cuchetas-hotel {
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
            margin: 1rem 0 1.5rem 0;
        }

        .cucheta {
            display: flex;
            flex-direction: column;
            align-items: center;
            width: 5.5rem;
            text-align: center;
        }

        .cucheta-circulo {
            width: 3.5rem;
            height: 3.5rem;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            border: 2px solid;
        }

        /* Libre: mismos tonos grises que ya usa el resto del sitio para
           estados neutros (bg-light / border-secondary-subtle). */
        .cucheta-libre .cucheta-circulo {
            background-color: var(--bs-light);
            border-color: var(--bs-secondary-border-subtle, #dee2e6);
            color: var(--bs-secondary);
        }

        /* Ocupada: mismo tono "info" que ya se usa en los botones de
           acción del resto del sitio (bg-info-subtle). */
        .cucheta-ocupada .cucheta-circulo {
            background-color: var(--bs-info-bg-subtle, #cff4fc);
            border-color: var(--bs-info-border-subtle, #9eeaf9);
            color: var(--bs-info-text-emphasis, #055160);
        }

        .cucheta-etiqueta {
            font-size: 0.75rem;
            color: var(--bs-secondary);
            margin-top: 0.35rem;
            overflow-wrap: anywhere;
        }
    </style>
</head>

<body>
    <!-- DETAIL: HIDE OVERFLOW-X -->
    <div style="overflow-x: hidden;">

    <!-- TOP BAR -->
    <?php include '../topbar.php'; ?>


    <!-- ★★★★★★★★★★★★★★★★★★★★★★★★★ -->

    <!-- MANEJAR MASCOTAS -->

    <!-- MENU CONTEXTUAL (compartido por todas las celdas editables) -->
    <div id="menuContextual">
        <button type="button" id="btnModificar">Modificar</button>
        <button type="button" id="btnDarBaja">Dar de baja</button>
    </div>

    <!-- LISTA -->
    <div class="container mt-5 pt-5"><div class="h-100 p-5 bg-light border rounded-3" data-orden-nombre="nombreMascota">
        <h1 class="h3 mb-3 fw-normal text-secondary">Datos de mascotas en el hotel</h1><br>
        <a href="/Subida/subirmascotahotel.php" class="text-secondary nohighlight"><button class="btn btn-outline-secondary me-2 border border-secondary-subtle rounded-5">Registrar mascota en el hotel</button></a>
        <button class="btn btn-outline-secondary me-2 border border-secondary-subtle rounded-5 btn-orden-manejo" title="Ordenar alfabéticamente" aria-pressed="false"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-filter-circle" viewBox="0 0 16 16"><path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14m0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16"/><path d="M7 11.5a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 0 1h-1a.5.5 0 0 1-.5-.5m-2-3a.5.5 0 0 1 .5-.5h5a.5.5 0 0 1 0 1h-5a.5.5 0 0 1-.5-.5m-2-3a.5.5 0 0 1 .5-.5h9a.5.5 0 0 1 0 1h-9a.5.5 0 0 1-.5-.5"/></svg></button>
        <button class="btn btn-outline-secondary me-2 border border-secondary-subtle rounded-5" onClick="window.print()"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-printer" viewBox="0 0 16 16"><path d="M2.5 8a.5.5 0 1 0 0-1 .5.5 0 0 0 0 1"/><path d="M5 1a2 2 0 0 0-2 2v2H2a2 2 0 0 0-2 2v3a2 2 0 0 0 2 2h1v1a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2v-1h1a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-1V3a2 2 0 0 0-2-2zM4 3a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1v2H4zm1 5a2 2 0 0 0-2 2v1H2a1 1 0 0 1-1-1V7a1 1 0 0 1 1-1h12a1 1 0 0 1 1 1v3a1 1 0 0 1-1 1h-1v-1a2 2 0 0 0-2-2zm7 2v3a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1v-3a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1"/></svg></button><br><br>

        <!-- DIBUJO DE LAS 6 CUCHETAS: se arma enteramente en el servidor a
             partir de $cuchetas (definido más arriba), no necesita JS. -->
        <div class="cuchetas-hotel">
            <?php for ($n = 1; $n <= 6; $n++): ?>
                <?php $ocupante = $cuchetas[$n]; ?>
                <div class="cucheta <?php echo $ocupante !== null ? 'cucheta-ocupada' : 'cucheta-libre'; ?>">
                    <div
                        class="cucheta-circulo"
                        title="Cucheta <?php echo $n; ?>: <?php echo $ocupante !== null ? htmlspecialchars($ocupante) : 'Libre'; ?>"
                    >
                        <?php echo $n; ?>
                    </div>
                    <div class="cucheta-etiqueta">
                        <?php echo $ocupante !== null ? htmlspecialchars($ocupante) : 'Libre'; ?>
                    </div>
                </div>
            <?php endfor; ?>
        </div>

        <ul class="list-group list-group-horizontal pb-1">
            <li class="list-group-item list-group-item-action list-group-item-light">ID</li>
            <li class="list-group-item list-group-item-action list-group-item-light">Mascota</li>
            <li class="list-group-item list-group-item-action list-group-item-light">Cucheta</li>
            <li class="list-group-item list-group-item-action list-group-item-light">Fecha de ingreso</li>
            <li class="list-group-item list-group-item-action list-group-item-light">Fecha de egreso</li>
        </ul>

        <?php if (empty($items)): ?>
            <p class="text-secondary mt-3">No hay mascotas registradas.</p>
        <?php else: ?>
            <?php foreach ($items as $item): ?>
                <ul class="list-group list-group-horizontal">
                    <li class="list-group-item list-group-item-action list-group-item-light cell-break">
                        <?php echo htmlspecialchars($item["idRegistroHotel"]); ?>
                    </li>
                    <li class="list-group-item list-group-item-action list-group-item-light cell-break editable-cell"
                        data-celda-orden
                        data-tabla="registro_hotel"
                        data-id="<?php echo htmlspecialchars($item["idRegistroHotel"]); ?>"
                        data-campo="idMascotaRegistroHotel"
                        data-tipo="seleccion"
                        data-opciones="<?php echo htmlspecialchars($opcionesMascotaJson, ENT_QUOTES, 'UTF-8'); ?>"
                        data-valor-crudo="<?php echo htmlspecialchars((string) $item["idMascotaRegistroHotel"]); ?>">
                        <?php echo $item["nombreMascota"] !== null ? htmlspecialchars($item["nombreMascota"]) : "Sin mascota"; ?>
                    </li>
                    <!-- Cucheta: no editable. Se asigna sola al ingresar la
                         mascota (ver subirmascotahotel.php) y no debería
                         poder cambiarse a mano, para no generar dos
                         mascotas activas en la misma cucheta. -->
                    <li class="list-group-item list-group-item-action list-group-item-light cell-break">
                        <?php echo htmlspecialchars($item["numeroCuchetaRegistroHotel"]); ?>
                    </li>
                    <li class="list-group-item list-group-item-action list-group-item-light cell-break editable-cell"
                        data-tabla="registro_hotel"
                        data-id="<?php echo htmlspecialchars($item["idRegistroHotel"]); ?>"
                        data-campo="fechaIngresoRegistroHotel"
                        data-tipo="fecha">
                        <?php echo htmlspecialchars($item["fechaIngresoRegistroHotel"]); ?>
                    </li>
                    <li class="list-group-item list-group-item-action list-group-item-light cell-break editable-cell"
                        data-tabla="registro_hotel"
                        data-id="<?php echo htmlspecialchars($item["idRegistroHotel"]); ?>"
                        data-campo="fechaPartidaRegistroHotel"
                        data-tipo="fecha">
                        <?php echo htmlspecialchars($item["fechaPartidaRegistroHotel"]); ?>
                    </li>
                </ul>
            <?php endforeach; ?>
        <?php endif; ?>
    </div></div></div>

    <!-- END: MANEJAR MASCOTAS -->

    <!-- ★★★★★★★★★★★★★★★★★★★★★★★★★ -->


    <!-- JS MANAGERS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
    <script src="/../!JS/edicionInline.js?v=5" defer></script>
    <script src="/../!JS/busquedainline.js"></script>
    <script src="/../!JS/ordeninline.js"></script>
</body>
</html>