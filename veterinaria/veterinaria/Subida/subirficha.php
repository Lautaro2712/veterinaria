<?php
// Carga e interpreta el código de conexion.php
require_once __DIR__ . "/../!PHPchecks/conexion.php";
require_once __DIR__ . "/../!PHPchecks/auth.php";
requireRole(ROL_EMPLEADO);

// Zona horaria de Uruguay, para que la fecha automática de registro
// coincida con la fecha real del país, no con la del servidor si este
// estuviera configurado con otro huso horario.
date_default_timezone_set('America/Montevideo');

// Variables para controlar la visualización de mensajes en el HTML
$exito = false;
$error = null;
$vacunacionFichaClinica  = "";
$pesoFichaClinica  = '';
$fechaRegistroFichaClinica = '';
$antecedentesFichaClinica  = "";

$sql = "SELECT idMascota, nombreMascota FROM mascota ORDER BY nombreMascota ASC";
$stmt = $conexion->prepare($sql);
$stmt->execute();
$mascotas = $stmt->fetchAll();

// Evalúa la variable global $_SERVER para verificar si el usuario envió el formulario por POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // 1. RECEPCIÓN Y VALIDACIÓN DE DATOS
    $vacunacionFichaClinica = $_POST["vacunacionFichaClinica"] ?? "";
    $pesoFichaClinica  = $_POST['pesoFichaClinica']  ?? '';
    // La fecha de registro ya no se recibe del formulario: se asigna
    // automáticamente con la fecha del día en que se sube la ficha,
    // así el empleado no tiene que completarla a mano ni puede
    // equivocarla.
    $fechaRegistroFichaClinica = date('Y-m-d');
    $antecedentesFichaClinica = $_POST["antecedentesFichaClinica"]  ?? "";
    $idMascota = $_POST['idMascota']  ?? '';

    // Comprobamos que los campos requeridos no estén vacíos
    if (empty($vacunacionFichaClinica) || empty($pesoFichaClinica) || empty($antecedentesFichaClinica) || empty($idMascota)) {
        $error = "Por favor, complete todos los campos solicitados.";
    } else {
       // -----------------------------------------------------------------------------------------
    // 2. VERIFICACIÓN DE DISPONIBILIDAD E INSERCIÓN
    // -----------------------------------------------------------------------------------------

    // Paso A: Comprobar si ya existe esa ficha clinica
    $sqlCheck = "SELECT idFichaClinica FROM ficha_clinica WHERE vacunacionFichaClinica = ? AND pesoFichaClinica = ? AND fechaRegistroFichaClinica = ? AND antecedentesFichaClinica = ? AND idMascota = ?";
    try {
        $stmtCheck = $conexion->prepare($sqlCheck);
        $stmtCheck->execute([$vacunacionFichaClinica, $pesoFichaClinica, $fechaRegistroFichaClinica, $antecedentesFichaClinica, $idMascota]);

        // Si la consulta devuelve 1 o más filas, la ficha clinica existe
        if ($stmtCheck->fetchColumn() !== false) {
            $error = "Esa ficha clinica ya existe.";
        } else {
            // Paso B: Si no existe, proceder con la inserción
            $sqlInsert = "INSERT INTO ficha_clinica (vacunacionFichaClinica, pesoFichaClinica, fechaRegistroFichaClinica, antecedentesFichaClinica, idMascota) VALUES (?, ?, ?, ?, ?)";
            $stmtInsert = $conexion->prepare($sqlInsert);
            $stmtInsert->execute([$vacunacionFichaClinica, $pesoFichaClinica, $fechaRegistroFichaClinica, $antecedentesFichaClinica, $idMascota]);
            $exito = true;
        }
    } catch (PDOException $e) {
        $error = "Error al subir el producto al sistema: " . $e->getMessage();
    }
    }
}
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
    <div class="container-fluid mt-5 pt-4">
        <div class="row justify-content-center">

            <!-- CARTEL DE INSTRUCCIONES -->
            <div class="col-lg-4 col-md-5 mb-4">
                <div class="h-80 p-4 bg-light border rounded-3">
                    <h2 class="h5 fw-normal text-secondary mb-3">Cómo completar este formulario</h2>
                    <p class="text-secondary">
                        Por favor recuerde que a la hora de registrar las vacunas realizadas y los antecedentes del animal,
                        se debe de escribir en minuscula, sin tilde y separados por coma.
                    </p>
                    <p class="text-secondary mb-1"><strong>El formato se vería así:</strong></p>
                    <p class="text-secondary mb-3">
                        Vacunas realizadas: [vacuna], [vacuna], [vacuna], etc.<br>
                        Antecedentes del animal: [enfermedad], [enfermedad], [enfermedad], etc.
                    </p>
                    <p class="text-secondary mb-1"><strong>De forma aplicada, se vería así:</strong></p>
                    <p class="text-secondary mb-3">
                        Vacunas realizadas: trivalente felina, rabia, leucemia felina, etc.<br>
                        Antecedentes del animal: neumonia, cancer, cataratas, etc.
                    </p>
                    <p class="text-secondary mb-0">
                        Si tiene algún problema entendiendo las instrucciones, solicite ayuda de un empleado.
                    </p>
                </div>
            </div>

            <!-- FORMULARIO -->
            <div class="col-lg-6 col-md-7">
                <main class="form-signin m-5 mt-0">
                    <?php if (!empty($exito)): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            ✔ Ficha clinica subida con exito al sistema!
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
                        <h1 class="h3 mb-3 fw-normal text-secondary">Subir ficha clinica</h1>
                        <div class="form-floating">
                            <select name="idMascota" id="idMascota" class="form-select" required>
                                <option value="" disabled selected>Seleccione una mascota</option>
                                <?php foreach ($mascotas as $mascota): ?>
                                    <option value="<?php echo htmlspecialchars($mascota['idMascota']); ?>">
                                        <?php echo htmlspecialchars($mascota['nombreMascota']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <label for="idMascota">Mascota</label>
                        </div><br>
                        <div class="form-floating">
                            <input type="text" class="form-control" id="vacunacionFichaClinica" name="vacunacionFichaClinica" placeholder="trivalente felina, rabia, leucemia felina" required>
                            <label for="vacunacionFichaClinica">Vacunaciones realizadas</label>
                        </div><br>
                        <div class="form-floating">
                            <input type="number" class="form-control" id="precioProducto" name="precioProducto" min="0" placeholder="2kg" required>
                            <label for="pesoFichaClinica">Peso del animal en kilogramos</label>
                        </div><br>
                        <div class="form-floating">
                            <input type="text" class="form-control" id="antecedentesFichaClinica" name="antecedentesFichaClinica" placeholder="neumonia, cancer, cataratas" required>
                            <label for="antecedentesFichaClinica">Antecedentes del animal</label>
                        </div><br>
                        <button class="btn bg-info-subtle me-2 border border-info-subtle rounded-5 w-100" type="submit">Subir al sistema</button><br><br>
                    </form>
                <div class="d-flex justify-content-center">
                <a href="/Subida/manejarfichas.php" class="text-secondary nohighlight"><button class="btn bg-light-subtle border border-secondary-subtle rounded-5">Ir al menu de manejo</button></a><br><br>
                </div>
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