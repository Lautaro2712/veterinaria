<?php
// Carga e interpreta el código de conexion.php
require_once __DIR__ . "/../!PHPchecks/conexion.php";
require_once __DIR__ . "/../!PHPchecks/auth.php";
require_once __DIR__ . "/../!PHPchecks/registro_acciones.php";
requireRole(ROL_EMPLEADO);

// Variables para controlar la visualización de mensajes en el HTML
$exito = false;
$error = null;
$nombreMascota  = "";
$especieMascota  = "";
$razaMascota  = "";
$sexoMascota  = "";

$sql = "SELECT idCliente, nombreCliente FROM cliente WHERE activo = TRUE
        ORDER BY apellidoCliente ASC, nombreCliente ASC";
$stmt = $conexion->prepare($sql);
$stmt->execute();
$clientes = $stmt->fetchAll();

// Evalúa la variable global $_SERVER para verificar si el usuario envió el formulario por POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // 1. RECEPCIÓN Y VALIDACIÓN DE DATOS
    $nombreMascota = $_POST["nombreMascota"] ?? "";
    $especieMascota  = $_POST["especieMascota"]  ?? "";
    $razaMascota = $_POST['razaMascota'] ?? '';
    $sexoMascota = $_POST['sexoMascota']  ?? '';
    $idCliente = $_POST['idCliente']  ?? '';

    // Comprobamos que los campos requeridos no estén vacíos
    if (empty($nombreMascota) || empty($especieMascota)|| empty($razaMascota) || empty($sexoMascota) || empty($idCliente)) {
        $error = "Por favor, complete todos los campos solicitados.";
    } else {
       // -----------------------------------------------------------------------------------------
    // 2. VERIFICACIÓN DE DISPONIBILIDAD E INSERCIÓN
    // -----------------------------------------------------------------------------------------

    // Paso A: Comprobar si ya existe esa mascota
    $sqlCheck = "SELECT idMascota FROM mascota WHERE nombreMascota = ? AND especieMascota = ? AND razaMascota = ? AND sexoMascota = ? AND idCliente = ?";
    try {
        $stmtCheck = $conexion->prepare($sqlCheck);
        $stmtCheck->execute([$nombreMascota, $especieMascota, $razaMascota, $sexoMascota, $idCliente]);

        // Si la consulta devuelve 1 o más filas, la mascota existe
        if ($stmtCheck->fetchColumn() !== false) {
            $error = "Esa mascota ya existe.";
        } else {
            // Paso B: Si no existe, proceder con la inserción
            $sqlInsert = "INSERT INTO mascota (nombreMascota, especieMascota, razaMascota, sexoMascota, idCliente) VALUES (?, ?, ?, ?, ?)";
            $stmtInsert = $conexion->prepare($sqlInsert);
            $stmtInsert->execute([$nombreMascota, $especieMascota, $razaMascota, $sexoMascota, $idCliente]);
            // Bitácora de acciones (ver registro_acciones.php)
            registrarAccion($conexion, "Alta de mascota (id " . $conexion->lastInsertId() . "): $nombreMascota, cliente id $idCliente");
            $exito = true;
        }
    } catch (PDOException $e) {
        $error = "Error al subir la mascota al sistema: " . $e->getMessage();
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
                ✔ Mascota subida con exito al sistema!
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
            <h1 class="h3 mb-3 fw-normal text-secondary">Registrar mascota</h1> 
            <div class="form-floating"> 
                <input type="text" class="form-control" id="nombreMascota" name="nombreMascota" maxlength="20" placeholder="Pedro" required> 
                <label for="nombreMascota">Nombre de la mascota</label> 
            </div><br>
            <div class="form-floating"> 
                <input type="text" class="form-control" id="especieMascota" name="especieMascota" maxlength="20" placeholder="Perro" required> 
                <label for="especieMascota">Especie de la mascota</label> 
            </div><br>
            <div class="form-floating"> 
                <input type="text" class="form-control" id="razaMascota" name="razaMascota" maxlength="20" placeholder="Siames" required> 
                <label for="razaMascota">Raza de la mascota</label> 
            </div><br>
            <div class="form-floating"> 
                <select name="sexoMascota" id="sexoMascota" class="form-select" required>
                    <option value="Macho">Macho</option>
                    <option value="Hembra">Hembra</option>
                    <option value="Caso especial">Caso especial</option>
                </select>
                <label for="sexoMascota">Sexo de la mascota</label>
            </div><br>
            <div class="form-floating"> 
                <select name="idCliente" id="idCliente" class="form-select" required>
                    <option value="" disabled selected>Seleccione un cliente</option>
                    <?php foreach ($clientes as $cliente): ?>
                        <option value="<?php echo htmlspecialchars($cliente['idCliente']); ?>">
                            <?php echo htmlspecialchars($cliente['nombreCliente']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <label for="idCliente">ID del cliente</label> 
            </div><br>
            <button class="btn bg-info-subtle me-2 border border-info-subtle rounded-5 w-100" type="submit">Registrar</button><br><br>
        </form>
        <div class="d-flex justify-content-center">
        <a href="/Subida/manejarmascotas.php" class="text-secondary nohighlight"><button class="btn bg-light-subtle border border-secondary-subtle rounded-5">Ir al menu de manejo</button></a><br><br>
        </div>
    </main></div><br>
    
    <!-- END: REGISTER FORM -->

    <!-- ★★★★★★★★★★★★★★★★★★★★★★★★★ -->

    <!-- JS MANAGERS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
</body>
</html>