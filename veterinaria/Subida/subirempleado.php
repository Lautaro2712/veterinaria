<?php
// Carga e interpreta el código de conexion.php
require_once __DIR__ . "/../!PHPchecks/conexion.php";
require_once __DIR__ . "/../!PHPchecks/auth.php";
requireExactRole([ROL_ADMINISTRADOR]);

// Variables para controlar la visualización de mensajes en el HTML
$exito = false;
$error = null;
$nombreEmpleado  = "";
$apellidoEmpleado  = "";
$telefonoEmpleado  = '';
$correoEmpleado = "";
$rolEmpleado = "";

// Evalúa la variable global $_SERVER para verificar si el usuario envió el formulario por POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // 1. RECEPCIÓN DE DATOS
    $nombreEmpleado = $_POST["nombreEmpleado"] ?? "";
    $apellidoEmpleado = $_POST["apellidoEmpleado"] ?? "";
    $telefonoEmpleado = $_POST['telefonoEmpleado'] ?? '';
    $correoEmpleado = $_POST["correoEmpleado"] ?? "";
    $passwordEmpleado = $_POST["passwordEmpleado"] ?? "";
    $repetirPasswordEmpleado = $_POST["repetirPasswordEmpleado"] ?? "";
    $rolEmpleado = $_POST["rolEmpleado"] ?? "";

    // 2. VALIDACIÓN DE CAMPOS OBLIGATORIOS
    if (empty($nombreEmpleado) || empty($apellidoEmpleado) || empty($telefonoEmpleado) || empty($correoEmpleado) || empty($passwordEmpleado) || empty($repetirPasswordEmpleado) || $rolEmpleado === "") {
        $error = "Por favor, complete todos los campos solicitados.";

    // 3. VALIDACIÓN: EL ROL DEBE SER UNO DE LOS DOS VALORES PERMITIDOS
    //    (evita que alguien manipule el <select> del HTML y mande cualquier cosa)
    } elseif (!in_array($rolEmpleado, ["0", "1"], true)) {
        $error = "El rol seleccionado no es válido.";

    // 4. VALIDACIÓN: LAS DOS CONTRASEÑAS DEBEN COINCIDIR
    } elseif ($passwordEmpleado !== $repetirPasswordEmpleado) {
        $error = "Las contraseñas no coinciden.";

    // 5. VALIDACIÓN: LA CONTRASEÑA DEBE CUMPLIR LAS PAUTAS DE SEGURIDAD
    //    - Más de 8 caracteres (mínimo 9)
    //    - Al menos una mayúscula
    //    - Al menos una minúscula
    //    - Al menos un número
    //    - Al menos un carácter especial
    } elseif (!preg_match('/^(?=.*[A-Z])(?=.*[a-z])(?=.*\d)(?=.*[\W_]).{9,}$/u', $passwordEmpleado)) {
        $error = "La contraseña debe tener más de 8 caracteres e incluir al menos una mayúscula, una minúscula, un número y un carácter especial.";

    } else {
        // -----------------------------------------------------------------------------------------
        // 6. VERIFICACIÓN DE DISPONIBILIDAD E INSERCIÓN
        // -----------------------------------------------------------------------------------------

        // Paso A: Comprobar si ya existe un empleado con ese correo (el correo debería ser único)
        $sqlCheck = "SELECT idEmpleado FROM empleado WHERE correoEmpleado = ?";
        try {
            $stmtCheck = $conexion->prepare($sqlCheck);
            $stmtCheck->execute([$correoEmpleado]);

            // Si la consulta devuelve 1 o más filas, el empleado existe
            if ($stmtCheck->fetchColumn() !== false) {
                $error = "¡Ese empleado ya existe! Si se ha olvidado de su contraseña, porfavor contactese con nosotros.";
            } else {
                // Paso B: Hashear la contraseña ANTES de guardarla
                $passwordHasheada = password_hash($passwordEmpleado, PASSWORD_DEFAULT);

                // Paso C: Si no existe, proceder con la inserción
                $sqlInsert = "INSERT INTO empleado (nombreEmpleado, apellidoEmpleado, telefonoEmpleado, correoEmpleado, passwordEmpleado, rolEmpleado) VALUES (?, ?, ?, ?, ?, ?)";
                $stmtInsert = $conexion->prepare($sqlInsert);
                $stmtInsert->execute([$nombreEmpleado, $apellidoEmpleado, $telefonoEmpleado, $correoEmpleado, $passwordHasheada, (int) $rolEmpleado]);
                $exito = true;
            }
        } catch (PDOException $e) {
            $error = "Error al subir el empleado al sistema: " . $e->getMessage();
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

    <!-- FORMULARIO SUBIDA DE EMPLEADO -->
    <div class="container-fluid margins"><main class="form-signin m-5">
        <?php if (!empty($error)): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                ✘ <?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        <?php if (!empty($exito)): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                ✔ ¡Empleado subido con éxito al sistema!
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        <br><form method="POST">
            <h1 class="h3 mb-3 fw-normal text-secondary">Subir empleado</h1> 
            <div class="form-floating"> 
                <input type="text" class="form-control" id="nombreEmpleado" name="nombreEmpleado" minlength="2" maxlength="20" placeholder="John" value="<?php echo htmlspecialchars($nombreEmpleado); ?>" required> 
                <label for="nombreEmpleado">Nombre</label> 
            </div><br>
            <div class="form-floating"> 
                <input type="text" class="form-control" id="apellidoEmpleado" name="apellidoEmpleado" minlength="2" maxlength="20" placeholder="John" value="<?php echo htmlspecialchars($apellidoEmpleado); ?>" required> 
                <label for="apellidoEmpleado">Apellido</label> 
            </div><br>
            <div class="form-floating"> 
                <input type="email" class="form-control" id="correoEmpleado" name="correoEmpleado" maxlength="60" placeholder="nombre@ejemplo.com" value="<?php echo htmlspecialchars($correoEmpleado); ?>" required> 
                <label for="correoEmpleado">Correo electronico</label> 
            </div><br>
            <div class="form-floating"> 
                <input type="tel" class="form-control" id="telefonoEmpleado" name="telefonoEmpleado" placeholder="123456789" pattern="[0-9]{3}[0-9]{3}[0-9]{3}" value="<?php echo htmlspecialchars($telefonoEmpleado); ?>" required> 
                <label for="telefonoEmpleado">Número telefónico</label> 
            </div><br>
            <div class="form-floating">
                <select name="rolEmpleado" id="rolEmpleado" class="form-select" required>
                    <option value="" disabled <?php echo ($rolEmpleado === "") ? "selected" : ""; ?>>Seleccione un rol</option>
                    <option value="0" <?php echo ($rolEmpleado === "0") ? "selected" : ""; ?>>Empleado</option>
                    <option value="1" <?php echo ($rolEmpleado === "1") ? "selected" : ""; ?>>Administrador</option>
                </select>
                <label for="rolEmpleado">Rol</label>
            </div><br>
            <div class="form-floating"> 
                <input type="password" class="form-control" id="passwordEmpleado" name="passwordEmpleado" placeholder="contraseña" minlength="9" pattern="^(?=.*[A-Z])(?=.*[a-z])(?=.*\d)(?=.*[^A-Za-z0-9]).{9,}$" title="Debe tener más de 8 caracteres, e incluir mayúscula, minúscula, número y carácter especial." required> 
                <label for="passwordEmpleado">Contraseña</label> 
            </div><br>
            <div class="form-floating"> 
                <input type="password" class="form-control" id="repetirPasswordEmpleado" name="repetirPasswordEmpleado" placeholder="contraseña" required> 
                <label for="repetirPasswordEmpleado">Confirmar contraseña</label> 
            </div><br>
            <button class="btn bg-info-subtle me-2 border border-info-subtle rounded-5 w-100" type="submit">Subir al sistema</button><br><br>
        </form>
        <div class="d-flex justify-content-center">
        <a href="/Subida/manejarempleados.php" class="text-secondary nohighlight"><button class="btn bg-light-subtle border border-secondary-subtle rounded-5">Ir al menu de manejo</button></a><br><br>
        </div>
    </main></div><br>
    <!-- END: FORMULARIO SUBIDA DE EMPLEADO -->

    <!-- ★★★★★★★★★★★★★★★★★★★★★★★★★ -->

    <!-- JS MANAGERS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
</body>
</html>