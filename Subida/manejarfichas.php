<?php
require_once __DIR__ . "/../!PHPchecks/conexion.php";
require_once __DIR__ . "/../!PHPchecks/auth.php";
requireRole(ROL_EMPLEADO);

// Trae a todas las fichas clínicas activas de la base de datos, junto
// con el nombre de la mascota a la que corresponden. Se excluyen las
// dadas de baja (activo = FALSE) para que no reaparezcan al recargar
// la página después de eliminarlas con el menú contextual.
$sql = "SELECT fc.idFichaClinica, fc.vacunacionFichaClinica, fc.pesoFichaClinica,
               fc.fechaRegistroFichaClinica, fc.antecedentesFichaClinica,
               fc.idMascota, m.nombreMascota
        FROM ficha_clinica fc
        LEFT JOIN mascota m ON fc.idMascota = m.idMascota
        WHERE fc.activo = TRUE
        ORDER BY fc.fechaRegistroFichaClinica DESC, fc.idFichaClinica DESC";
$stmt = $conexion->prepare($sql);
$stmt->execute();
$items = $stmt->fetchAll();

// Lista de mascotas activas, para armar el <select> de la celda de
// Mascota en edición inline (evita que el empleado tenga que escribir
// el ID a mano).
$sqlMascotas = "SELECT idMascota, nombreMascota FROM mascota WHERE activo = TRUE ORDER BY nombreMascota ASC";
$stmtMascotas = $conexion->prepare($sqlMascotas);
$stmtMascotas->execute();
$mascotasDisponibles = $stmtMascotas->fetchAll();

$opcionesMascota = array_map(function ($m) {
    return ['valor' => (string) $m['idMascota'], 'texto' => $m['nombreMascota']];
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
    <link rel="icon" type="image/png" href="/!Imagenes/favicon.png">
    <title>Veterinaria del Mar</title>
</head>

<body>
    <!-- DETAIL: HIDE OVERFLOW-X -->
    <div style="overflow-x: hidden;">

    <!-- TOP BAR -->
    <?php include '../topbar.php'; ?>


    <!-- ★★★★★★★★★★★★★★★★★★★★★★★★★ -->

    <!-- MANEJAR FICHAS CLINICAS (READ ONLY) -->

    <!-- MENU CONTEXTUAL (compartido por todas las celdas editables) -->
    <div id="menuContextual">
        <button type="button" id="btnModificar">Modificar</button>
        <button type="button" id="btnDarBaja">Dar de baja</button>
    </div>

    <!-- LISTA -->
    <div class="container mt-5 pt-5"><div class="h-100 p-5 bg-light border rounded-3">  
        <h1 class="h3 mb-3 fw-normal text-secondary">Datos de fichas clínicas</h1><br>
        <ul class="list-group list-group-horizontal pb-1">
            <li class="list-group-item list-group-item-action list-group-item-light">ID</li>
            <li class="list-group-item list-group-item-action list-group-item-light">Mascota</li>
            <li class="list-group-item list-group-item-action list-group-item-light">Vacunación</li>
            <li class="list-group-item list-group-item-action list-group-item-light">Antecedentes</li>
            <li class="list-group-item list-group-item-action list-group-item-light">Peso (kg)</li>
            <li class="list-group-item list-group-item-action list-group-item-light">Fecha de registro</li>
        </ul>

        <?php if (empty($items)): ?>
            <p class="text-secondary mt-3">No hay fichas clínicas registradas.</p>
        <?php else: ?>
            <?php foreach ($items as $item): ?>
                <ul class="list-group list-group-horizontal">
                    <li class="list-group-item list-group-item-action list-group-item-light cell-break">
                        <?php echo htmlspecialchars($item["idFichaClinica"]); ?>
                    </li>
                    <li class="list-group-item list-group-item-action list-group-item-light cell-break editable-cell"
                        data-tabla="ficha_clinica"
                        data-id="<?php echo htmlspecialchars($item["idFichaClinica"]); ?>"
                        data-campo="idMascota"
                        data-tipo="seleccion"
                        data-opciones="<?php echo htmlspecialchars($opcionesMascotaJson, ENT_QUOTES, 'UTF-8'); ?>"
                        data-valor-crudo="<?php echo htmlspecialchars((string) $item["idMascota"]); ?>">
                        <?php echo $item["nombreMascota"] !== null ? htmlspecialchars($item["nombreMascota"]) : "Sin mascota"; ?>
                    </li>
                    <li class="list-group-item list-group-item-action list-group-item-light cell-break editable-cell"
                        data-tabla="ficha_clinica"
                        data-id="<?php echo htmlspecialchars($item["idFichaClinica"]); ?>"
                        data-campo="vacunacionFichaClinica"
                        data-tipo="texto">
                        <?php echo htmlspecialchars($item["vacunacionFichaClinica"]); ?>
                    </li>
                    <li class="list-group-item list-group-item-action list-group-item-light cell-break editable-cell"
                        data-tabla="ficha_clinica"
                        data-id="<?php echo htmlspecialchars($item["idFichaClinica"]); ?>"
                        data-campo="antecedentesFichaClinica"
                        data-tipo="texto">
                        <?php echo htmlspecialchars($item["antecedentesFichaClinica"]); ?>
                    </li>
                    <li class="list-group-item list-group-item-action list-group-item-light cell-break editable-cell"
                        data-tabla="ficha_clinica"
                        data-id="<?php echo htmlspecialchars($item["idFichaClinica"]); ?>"
                        data-campo="pesoFichaClinica"
                        data-tipo="numero">
                        <?php echo htmlspecialchars($item["pesoFichaClinica"]); ?>
                    </li>
                    <li class="list-group-item list-group-item-action list-group-item-light cell-break">
                        <?php echo htmlspecialchars($item["fechaRegistroFichaClinica"]); ?>
                    </li>
                </ul>
            <?php endforeach; ?>
        <?php endif; ?>
    </div></div></div>

    <!-- END: MANEJAR FICHAS CLINICAS (READ ONLY) -->

    <!-- ★★★★★★★★★★★★★★★★★★★★★★★★★ -->


    <!-- JS MANAGERS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
    <script src="/../!JS/edicionInline.js?v=5" defer></script>
    <script src="/../!JS/busquedainline.js"></script>
</body>
</html>