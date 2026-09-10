<?php
require_once __DIR__ . "/../!PHPchecks/auth.php";
require_once __DIR__ . "/../!PHPchecks/conexion.php";

$error = null;
$correo = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $correo = $_POST["correoCliente"] ?? "";
    $password = $_POST["passwordCliente"] ?? "";

    if (empty($correo) || empty($password)) {
        $error = "Por favor, complete todos los campos solicitados.";
    } else {
        try {
            // 1. Buscar primero en `cliente`
            $sql = "SELECT idCliente, nombreCliente, apellidoCliente, passwordCliente FROM cliente WHERE correoCliente = ? AND activo = TRUE";
            $stmt = $conexion->prepare($sql);
            $stmt->execute([$correo]);
            $cliente = $stmt->fetch();

            if ($cliente && password_verify($password, $cliente["passwordCliente"])) {
                session_regenerate_id(true);
                $_SESSION["idCliente"] = $cliente["idCliente"];
                $_SESSION["nombreCliente"] = $cliente["nombreCliente"];
                $_SESSION["apellidoCliente"] = $cliente["apellidoCliente"];
                $_SESSION["nivelRol"] = ROL_CLIENTE;

                header("Location: /index.php");
                exit();
            }

            // 2. Si no coincide con ningún cliente, buscar en `empleado`
            $sql = "SELECT idEmpleado, nombreEmpleado, apellidoEmpleado, passwordEmpleado, rolEmpleado FROM empleado WHERE correoEmpleado = ? AND activo = TRUE";
            $stmt = $conexion->prepare($sql);
            $stmt->execute([$correo]);
            $empleado = $stmt->fetch();

            if ($empleado && password_verify($password, $empleado["passwordEmpleado"])) {
                // rolEmpleado es TINYINT(1): 1 = administrador, 0 = empleado común
                $nivel = ((int) $empleado["rolEmpleado"] === 1) ? ROL_ADMINISTRADOR : ROL_EMPLEADO;

                session_regenerate_id(true);
                $_SESSION["idEmpleado"] = $empleado["idEmpleado"];
                $_SESSION["nombreCliente"] = $empleado["nombreEmpleado"]; // reutilizamos la misma clave para el topbar
                $_SESSION["apellidoCliente"] = $empleado["apellidoEmpleado"];
                $_SESSION["nivelRol"] = $nivel;

                header("Location: /index.php");
                exit();
            }

            // 3. Ni cliente ni empleado coincidieron
            $error = "Correo o contraseña incorrectos.";

        } catch (PDOException $e) {
            $error = "Error al iniciar sesión: " . $e->getMessage();
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
    <title>Veterinaria del Mar</title>
</head>

<body>
    <div style="overflow-x: hidden;">
    <?php include '../topbar.php'; ?>

    <div class="container-fluid margins"><main class="form-signin m-5"> 
        <?php if (!empty($error)): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                ✘ <?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        <br><form method="POST">  
            <h1 class="h3 mb-3 fw-normal text-secondary">Inicio de sesión</h1> 
            <div class="form-floating"> 
                <input type="email" class="form-control" id="correoCliente" name="correoCliente" placeholder="nombre@ejemplo.com" maxlength="60" value="<?php echo htmlspecialchars($correo); ?>" required> 
                <label for="correoCliente">Correo electronico</label>
            </div><br>
            <div class="form-floating mb-4"> 
                <input type="password" class="form-control passwordClass" id="passwordCliente" name="passwordCliente" placeholder="Contraseña" required> 
                <label for="passwordCliente">Contraseña</label> 
            </div>
            <button class="btn bg-info-subtle me-2 border border-info-subtle rounded-5 w-100" type="submit">Iniciar sesión</button><br><br>
            <a href="/Subida/registro.php" class="nohighlight"><h6 class="h6 mb-3 fw-normal text-secondary nohighlight">No tengo una cuenta</h6></a> 
            <a href="/index.php" class="nohighlight"><h6 class="h6 mb-3 fw-normal text-secondary nohighlight">Olvidé mi contraseña</h6></a>
        </form> 
    </main></div><br>

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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
    <script src="/../!JS/showpw.js"></script>
</body>
</html>