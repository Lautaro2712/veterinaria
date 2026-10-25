<?php
// Carga e interpreta el código de conexion.php
require_once __DIR__ . "/../!PHPchecks/conexion.php";
require_once __DIR__ . "/../!PHPchecks/auth.php";
require_once __DIR__ . "/../!PHPchecks/registro_acciones.php";
requireRole(ROL_EMPLEADO);

date_default_timezone_set('America/Montevideo');

// Variables para controlar la visualización de mensajes en el HTML
$exito = false;
$error = null;
$idMascotaRegistroHotel  = "";
$fechaIngresoRegistroHotel  = "";
$fechaPartidaRegistroHotel  = "";

$sql = "SELECT idMascota, nombreMascota FROM mascota ORDER BY nombreMascota ASC";
$stmt = $conexion->prepare($sql);
$stmt->execute();
$mascotas = $stmt->fetchAll();

// Evalúa la variable global $_SERVER para verificar si el usuario envió el formulario por POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // 1. RECEPCIÓN Y VALIDACIÓN DE DATOS
    $idMascotaRegistroHotel = $_POST["idMascotaRegistroHotel"] ?? "";
    $fechaIngresoRegistroHotel  = $_POST["fechaIngresoRegistroHotel"]  ?? "";
    $fechaPartidaRegistroHotel = $_POST["fechaPartidaRegistroHotel"] ?? "";

    // Comprobamos que los campos requeridos no estén vacíos
    if (empty($idMascotaRegistroHotel) || empty($fechaIngresoRegistroHotel)|| empty($fechaPartidaRegistroHotel)) {
        $error = "Por favor, complete todos los campos solicitados.";
    } else {
       // -----------------------------------------------------------------------------------------
    // 2. VERIFICACIÓN DE DISPONIBILIDAD E INSERCIÓN
    // -----------------------------------------------------------------------------------------

    try {

$stmtVencidos = $conexion->prepare(
            "UPDATE registro_hotel
             SET activo = FALSE
             WHERE activo = TRUE
               AND fechaPartidaRegistroHotel < ?"
        );
        $stmtVencidos->execute([date('Y-m-d')]);

        // Paso A: El hotel tiene 6 cuchetas. Si ya hay 6 registros activos,
        // no se puede ingresar una mascota más hasta que un empleado dé de
        // baja a alguna desde el menú de manejo.
        $sqlContarActivas = "SELECT COUNT(*) FROM registro_hotel WHERE activo = TRUE";
        $stmtContarActivas = $conexion->prepare($sqlContarActivas);
        $stmtContarActivas->execute();
        $cantidadActivas = (int) $stmtContarActivas->fetchColumn();

        if ($cantidadActivas >= 6) {

            $error = "El hotel está completo (6/6 cuchetas ocupadas). Debe dar de baja una mascota desde el menú de manejo antes de poder registrar una nueva.";

        } else {

            // Paso B: Comprobar que esa mascota no esté ya alojada en el
            // hotel en este momento (no importa con qué fechas: mientras
            // su registro siga activo, no puede volver a ingresar).
            $sqlYaEnHotel = "SELECT idRegistroHotel FROM registro_hotel WHERE idMascotaRegistroHotel = ? AND activo = TRUE";
            $stmtYaEnHotel = $conexion->prepare($sqlYaEnHotel);
            $stmtYaEnHotel->execute([$idMascotaRegistroHotel]);

            if ($stmtYaEnHotel->fetchColumn() !== false) {

                $error = "Esa mascota ya está alojada en el hotel.";

            } else {

                // Paso C: Asignar la primera cucheta libre (1 a 6). Se
                // consultan los números que están en uso ahora mismo y se
                // toma el menor que no aparezca en esa lista.
                $sqlCuchetasOcupadas = "SELECT numeroCuchetaRegistroHotel FROM registro_hotel WHERE activo = TRUE";
                $stmtCuchetasOcupadas = $conexion->prepare($sqlCuchetasOcupadas);
                $stmtCuchetasOcupadas->execute();
                $cuchetasOcupadas = array_map('intval', $stmtCuchetasOcupadas->fetchAll(PDO::FETCH_COLUMN));

                $numeroCucheta = null;
                for ($n = 1; $n <= 6; $n++) {
                    if (!in_array($n, $cuchetasOcupadas, true)) {
                        $numeroCucheta = $n;
                        break;
                    }
                }

                if ($numeroCucheta === null) {
                    // No debería pasar nunca (ya se comprobó que hay menos
                    // de 6 activas), pero se cubre por las dudas.
                    $error = "No se encontró ninguna cucheta libre. Intente nuevamente.";
                } else {

                    // Paso D: Insertar el nuevo registro, ya con su cucheta
                    // asignada y activo = TRUE.
                    $sqlInsert = "INSERT INTO registro_hotel
                                  (idMascotaRegistroHotel, fechaIngresoRegistroHotel, fechaPartidaRegistroHotel, numeroCuchetaRegistroHotel, activo)
                                  VALUES (?, ?, ?, ?, TRUE)";
                    $stmtInsert = $conexion->prepare($sqlInsert);
                    $stmtInsert->execute([
                        $idMascotaRegistroHotel,
                        $fechaIngresoRegistroHotel,
                        $fechaPartidaRegistroHotel,
                        $numeroCucheta
                    ]);

                    // Bitácora de acciones (ver registro_acciones.php)
                    registrarAccion($conexion, "Alta de registro de hotel (id " . $conexion->lastInsertId() . "): mascota id $idMascotaRegistroHotel, cucheta $numeroCucheta, ingreso $fechaIngresoRegistroHotel, partida $fechaPartidaRegistroHotel");
                    $exito = true;
                }
            }
        }

    } catch (PDOException $e) {
        $error = "Error al subir la mascota al hotel: " . $e->getMessage();
    }
    }
}

$fechaMinima = date('Y-m-d');
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
    <link rel="icon" type="image/png" href="/!Imagenes/favicon.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <title>Veterinaria del Mar</title>
</head>

<body>
    <!-- DETAIL: HIDE OVERFLOW-X -->
    <div style="overflow-x: hidden;">

    <!-- TOP BAR -->
    <?php include '../topbar.php'; ?>

    <!-- ★★★★★★★★★★★★★★★★★★★★★★★★★ -->

    <!-- REGISTER FORM -->
    <div class="container-fluid margins"><main class="form-signin m-5"> 
        <?php if (!empty($exito)): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                ✔ Subida al hotel correctamente realizada!
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
            <h1 class="h3 mb-3 fw-normal text-secondary">Registrar mascota al hotel</h1> 
            <div class="form-floating"> 
                <select name="idMascotaRegistroHotel" id="idMascotaRegistroHotel" class="form-select" required>
                    <option value="" disabled selected>Seleccione una mascota</option>
                    <?php foreach ($mascotas as $mascota): ?>
                        <option value="<?php echo htmlspecialchars($mascota['idMascota']); ?>">
                            <?php echo htmlspecialchars($mascota['nombreMascota']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <label for="idMascotaRegistroHotel">Id de la mascota</label> 
            </div><br>
            <div class="form-floating">
                <input type="date" class="form-control" id="fechaIngresoRegistroHotel" name="fechaIngresoRegistroHotel" max="<?php echo $fechaMaxima; ?>" required>
                <label for="fechaIngresoRegistroHotel">Fecha de ingreso al hotel</label>
            </div><br>
            <div class="form-floating">
                <input type="date" class="form-control" id="fechaPartidaRegistroHotel" name="fechaPartidaRegistroHotel" required>
                <label for="fechaPartidaRegistroHotel">Fecha de egreso del hotel</label>
            </div><br>
            <button class="btn bg-info-subtle me-2 border border-info-subtle rounded-5 w-100" type="submit">Registrar</button><br><br>
        </form>
        <div class="d-flex justify-content-center">
        <a href="/Subida/manejarmascotashotel.php" class="text-secondary nohighlight"><button class="btn bg-light-subtle border border-secondary-subtle rounded-5">Ir al menu de manejo</button></a><br><br>
        </div>
    </main></div><br>
    
    <!-- END: REGISTER FORM -->

    <!-- ★★★★★★★★★★★★★★★★★★★★★★★★★ -->

    <!-- JS MANAGERS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
</body>
</html>