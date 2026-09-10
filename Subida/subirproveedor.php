<?php
// Carga e interpreta el código de conexion.php
require_once __DIR__ . "/../!PHPchecks/conexion.php";
require_once __DIR__ . "/../!PHPchecks/auth.php";
requireRole(ROL_EMPLEADO);

// Variables para controlar la visualización de mensajes en el HTML
$exito = false;
$error = null;
$rutProveedor  = '';
$nombreProveedor  = "";
$telefonoProveedor  = '';
$correoProveedor = "";

// Evalúa la variable global $_SERVER para verificar si el usuario envió el formulario por POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // 1. RECEPCIÓN Y VALIDACIÓN DE DATOS
    $rutProveedor = $_POST['rutProveedor'] ?? '';
    $nombreProveedor  = $_POST["nombreProveedor"]  ?? '';
    $telefonoProveedor = $_POST['telefonoProveedor'] ?? '';
    $correoProveedor  = $_POST["correoProveedor"]  ?? '';

    // Comprobamos que los campos requeridos no estén vacíos
    if (empty($rutProveedor) || empty($nombreProveedor) || empty($telefonoProveedor) || empty($correoProveedor)) {
        $error = "Por favor, complete todos los campos solicitados.";
    } else {
       // -----------------------------------------------------------------------------------------
    // 2. VERIFICACIÓN DE DISPONIBILIDAD E INSERCIÓN
    // -----------------------------------------------------------------------------------------

    // Paso A: Comprobar si ya existe ese proveedor
    $sqlCheck = "SELECT idProveedor FROM proveedor WHERE rutProveedor = ? AND nombreProveedor = ? AND telefonoProveedor = ? AND correoProveedor = ?";
    try {
        $stmtCheck = $conexion->prepare($sqlCheck);
        $stmtCheck->execute([$rutProveedor, $nombreProveedor, $telefonoProveedor, $correoProveedor]);

        // Si la consulta devuelve 1 o más filas, el proveedor existe
        if ($stmtCheck->fetchColumn() !== false) {
            $error = "Ese proveedor ya existe.";
        } else {
            // Paso B: Si no existe, proceder con la inserción
            $sqlInsert = "INSERT INTO proveedor (rutProveedor, nombreProveedor, telefonoProveedor, correoProveedor) VALUES (?, ?, ?, ?)";
            $stmtInsert = $conexion->prepare($sqlInsert);
            $stmtInsert->execute([$rutProveedor, $nombreProveedor, $telefonoProveedor, $correoProveedor]);
            $exito = true;
        }
    } catch (PDOException $e) {
        $error = "Error al registrar al proveedor: " . $e->getMessage();
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
    <link rel="icon" type="image/png" href="/!Imagenes/favicon.png">
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
                ✔ ¡Proveedor subido con exito al sistema!
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
            <h1 class="h3 mb-3 fw-normal text-secondary">Subir proveedor al sistema</h1> 
            <div class="form-floating"> 
                <input type="number" class="form-control" id="rutProveedor" name="rutProveedor" min="1" min="99999999999" placeholder="12345678" required> 
                <label for="rutProveedor">RUT</label> 
            </div><br>
            <div class="form-floating"> 
                <input type="text" class="form-control" id="nombreProveedor" name="nombreProveedor" maxlength="20" placeholder="Whiskas" required> 
                <label for="nombreProveedor">Nombre</label> 
            </div><br>
            <div class="form-floating"> 
                <input type="tel" class="form-control" id="telefonoProveedor" name="telefonoProveedor" placeholder="123456789" pattern="[0-9]{3}[0-9]{3}[0-9]{3}" required> 
                <label for="telefonoProveedor">Número telefónico</label> 
            </div><br>
            <div class="form-floating"> 
                <input type="email" class="form-control" id="correoProveedor" name="correoProveedor" maxlength="60" placeholder="nombre@ejemplo.com" required> 
                <label for="correoProveedor">Correo electronico</label> 
            </div><br>
            <button class="btn bg-info-subtle me-2 border border-info-subtle rounded-5 w-100" type="submit">Subir al sistema</button><br><br> 
        </form> 
    </main></div><br>
    <!-- END: REGISTER FORM -->

    <!-- ★★★★★★★★★★★★★★★★★★★★★★★★★ -->

    <!-- BOTTOM BAR -->
    <header class="p-3"> 
        <div class="container"> 
            <div> 
                <ul class="nav col-12 col-lg-auto me-lg-auto mb-2 justify-content-center mb-md-0"> 
                    <li><a href="/Informacion/tos.html" class="nav-link px-2 text-secondary ahover">Terminos y Condiciones</a></li>
                    <li><a href="/Informacion/politicaprivacidad.html" class="nav-link px-2 text-secondary ahover">Politica de Privacidad</a></li> 
                </ul>
            </div> 
        </div>
    </header>
    <!-- END: BOTTOM BAR -->


    <!-- ★★★★★★★★★★★★★★★★★★★★★★★★★ -->

    <!-- JS MANAGERS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
</body>
</html>