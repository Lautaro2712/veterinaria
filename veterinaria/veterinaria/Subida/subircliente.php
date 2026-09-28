<?php
// Carga e interpreta el código de conexion.php
require_once __DIR__ . "/../!PHPchecks/conexion.php";

// Variables para controlar la visualización de mensajes en el HTML
$exito = false;
$error = null;
$nombreCliente  = "";
$apellidoCliente  = "";
$telefonoCliente  = '';
$correoCliente = "";
 
// Evalúa la variable global $_SERVER para verificar si el usuario envió el formulario por POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
 
    // 1. RECEPCIÓN DE DATOS
    $nombreCliente = $_POST["nombreCliente"] ?? "";
    $apellidoCliente = $_POST["apellidoCliente"] ?? "";
    $telefonoCliente = $_POST['telefonoCliente'] ?? '';
    $correoCliente = $_POST["correoCliente"] ?? "";
    $passwordCliente = $_POST["passwordCliente"] ?? "";
    $repetirPasswordCliente = $_POST["repetirPasswordCliente"] ?? "";
 
    // 2. VALIDACIÓN DE CAMPOS OBLIGATORIOS
    if (empty($nombreCliente) || empty($apellidoCliente) || empty($telefonoCliente) || empty($correoCliente) || empty($passwordCliente) || empty($repetirPasswordCliente)) {
        $error = "Por favor, complete todos los campos solicitados.";
 
    // 3. VALIDACIÓN: LAS DOS CONTRASEÑAS DEBEN COINCIDIR
    } elseif ($passwordCliente !== $repetirPasswordCliente) {
        $error = "Las contraseñas no coinciden.";
 
    // 4. VALIDACIÓN: LA CONTRASEÑA DEBE CUMPLIR LAS PAUTAS DE SEGURIDAD
    //    - Más de 8 caracteres (mínimo 9)
    //    - Al menos una mayúscula
    //    - Al menos una minúscula
    //    - Al menos un número
    //    - Al menos un carácter especial
    } elseif (!preg_match('/^(?=.*[A-Z])(?=.*[a-z])(?=.*\d)(?=.*[\W_]).{9,}$/u', $passwordCliente)) {
        $error = "La contraseña debe tener más de 8 caracteres e incluir al menos una mayúscula, una minúscula, un número y un carácter especial.";
 
    } else {
        // -----------------------------------------------------------------------------------------
        // 5. VERIFICACIÓN DE DISPONIBILIDAD E INSERCIÓN
        // -----------------------------------------------------------------------------------------
 
        // Paso A: Comprobar si ya existe un cliente con ese correo (el correo debería ser único)
        $sqlCheck = "SELECT idCliente FROM cliente WHERE correoCliente = ?";
        try {
            $stmtCheck = $conexion->prepare($sqlCheck);
            $stmtCheck->execute([$correoCliente]);
 
            // Si la consulta devuelve 1 o más filas, el cliente existe
            if ($stmtCheck->fetchColumn() !== false) {
                $error = "¡Ese usuario ya existe! Si se ha olvidado de su contraseña, porfavor contactese con nosotros.";
            } else {
                // Paso B: Hashear la contraseña ANTES de guardarla
                $passwordHasheada = password_hash($passwordCliente, PASSWORD_DEFAULT);
 
                // Paso C: Si no existe, proceder con la inserción
                $sqlInsert = "INSERT INTO cliente (nombreCliente, apellidoCliente, telefonoCliente, correoCliente, passwordCliente) VALUES (?, ?, ?, ?, ?)";
                $stmtInsert = $conexion->prepare($sqlInsert);
                $stmtInsert->execute([$nombreCliente, $apellidoCliente, $telefonoCliente, $correoCliente, $passwordHasheada]);
                $exito = true;
            }
        } catch (PDOException $e) {
            $error = "Error al registrarse: " . $e->getMessage();
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
    <script src="https://js.hcaptcha.com/1/api.js" async defer></script>
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
 
    <!-- FORMULARIO REGISTRO -->
    <div class="container-fluid margins"><main class="form-signin m-5">
        <?php if (!empty($error)): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                ✘ <?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        <?php if (!empty($exito)): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                ✔ ¡Cliente subido con éxito al sistema!
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        <br><form method="POST">
            <h1 class="h3 mb-3 fw-normal text-secondary">Subir cliente</h1> 
            <div class="form-floating"> 
                <input type="text" class="form-control" id="nombreCliente" name="nombreCliente" minlength="2" maxlength="20" placeholder="John" value="<?php echo htmlspecialchars($nombreCliente); ?>" required> 
                <label for="nombreCliente">Nombre</label> 
            </div><br>
            <div class="form-floating"> 
                <input type="text" class="form-control" id="apellidoCliente" name="apellidoCliente" minlength="2" maxlength="20" placeholder="John" value="<?php echo htmlspecialchars($apellidoCliente); ?>" required> 
                <label for="apellidoCliente">Apellido</label> 
            </div><br>
            <div class="form-floating"> 
                <input type="email" class="form-control" id="correoCliente" name="correoCliente" placeholder="nombre@ejemplo.com" maxlength="60" value="<?php echo htmlspecialchars($correoCliente); ?>" required> 
                <label for="correoCliente">Correo electronico</label> 
            </div><br>
            <div class="form-floating"> 
                <input type="tel" class="form-control" id="telefonoCliente" name="telefonoCliente" placeholder="123456789" pattern="[0-9]{3}[0-9]{3}[0-9]{3}" value="<?php echo htmlspecialchars($telefonoCliente); ?>" required> 
                <label for="telefonoCliente">Número telefónico</label> 
            </div><br>
            <div class="form-floating"> 
                <input type="password" class="form-control" id="passwordCliente" name="passwordCliente" placeholder="contraseña" minlength="9" pattern="^(?=.*[A-Z])(?=.*[a-z])(?=.*\d)(?=.*[^A-Za-z0-9]).{9,}$" title="Debe tener más de 8 caracteres, e incluir mayúscula, minúscula, número y carácter especial." required> 
                <label for="passwordCliente">Contraseña</label> 
            </div><br>
            <div class="form-floating"> 
                <input type="password" class="form-control" id="repetirPasswordCliente" name="repetirPasswordCliente" placeholder="contraseña" required> 
                <label for="repetirPasswordCliente">Confirmar contraseña</label> 
            </div><br>
            <button class="btn bg-info-subtle me-2 border border-info-subtle rounded-5 w-100" type="submit">Subir al sistema</button><br><br>
        </form> 
        <div class="d-flex justify-content-center">
        <a href="/Subida/manejarclientes.php" class="text-secondary nohighlight"><button class="btn bg-light-subtle border border-secondary-subtle rounded-5">Ir al menu de manejo</button></a><br><br>
        </div>
    </main></div><br>
    <!-- END: FORMULARIO REGISTRO -->
 
    <!-- ★★★★★★★★★★★★★★★★★★★★★★★★★ -->
 
    <!-- JS MANAGERS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
    <script src="/../!JS/showpw.js"></script>
</body>
</html>
 