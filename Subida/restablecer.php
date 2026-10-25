<?php
require_once __DIR__ . "/../!PHPchecks/conexion.php";

$exito = false;
$error = null;
$email = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email     = trim($_POST['email'] ?? '');
    $codigo    = trim($_POST['codigo'] ?? '');
    $nueva     = $_POST['password'] ?? '';
    $confirmar = $_POST['password_confirm'] ?? '';

    $emailValido = filter_var($email, FILTER_VALIDATE_EMAIL);

    if (!$emailValido || $codigo === '' || $nueva === '' || $confirmar === '') {
        $error = "Por favor, complete todos los campos solicitados.";

    } elseif ($nueva !== $confirmar) {
        $error = "Las contraseñas no coinciden.";

    } elseif (!preg_match('/^(?=.*[A-Z])(?=.*[a-z])(?=.*\d)(?=.*[\W_]).{9,}$/u', $nueva)) {
        $error = "La contraseña debe tener más de 8 caracteres e incluir al menos una mayúscula, una minúscula, un número y un carácter especial.";

    } else {
        try {
            $stmt = $conexion->prepare('SELECT * FROM recuperaciones WHERE email = ? AND expira > NOW()');
            $stmt->execute([$emailValido]);
            $fila = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$fila || $fila['intentos'] >= 2) {
                $error = "Código inválido o vencido. Solicita uno nuevo.";

            } elseif (!password_verify($codigo, $fila['codigo_hash'])) {
                $conexion->prepare('UPDATE recuperaciones SET intentos = intentos + 1 WHERE email = ?')
                    ->execute([$emailValido]);
                $error = "Código incorrecto.";

            } else {
                $conexion->prepare('UPDATE cliente SET passwordCliente = ? WHERE correoCliente = ? AND activo = TRUE')
                    ->execute([password_hash($nueva, PASSWORD_DEFAULT), $emailValido]);
                $conexion->prepare('DELETE FROM recuperaciones WHERE email = ?')
                    ->execute([$emailValido]);
                $exito = true;
            }
        } catch (PDOException $e) {
            $error = "Error al restablecer la contraseña. Intenta nuevamente.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/../!CSS/basecss.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
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

    <!-- FORMULARIO RESTABLECER CONTRASEÑA -->
    <div class="container-fluid margins"><main class="form-signin m-5">
        <?php if (!empty($error)): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                ✘ <?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <?php if ($exito): ?>
            <div class="alert alert-success" role="alert">
                ✔ ¡Contraseña actualizada! Ya puedes iniciar sesión.
            </div>
            <a href="/Subida/login.php" class="nohighlight"><h6 class="h6 mb-3 fw-normal text-secondary">Ir a iniciar sesión</h6></a>

        <?php else: ?>
            <br><form method="POST">
                <h1 class="h3 mb-3 fw-normal text-secondary">Restablecer contraseña</h1>

                <div class="form-floating">
                    <input type="email" class="form-control" id="email" name="email" placeholder="nombre@ejemplo.com" maxlength="60" value="<?php echo htmlspecialchars($email, ENT_QUOTES, 'UTF-8'); ?>" required>
                    <label for="email">Correo electronico</label>
                </div><br>

                <div class="form-floating">
                    <input type="text" class="form-control" id="codigo" name="codigo" placeholder="123456" maxlength="6" inputmode="numeric" pattern="[0-9]{6}" title="Código de 6 dígitos" autocomplete="one-time-code" required>
                    <label for="codigo">Código de 6 dígitos</label>
                </div><br>

                <div class="form-floating">
                    <input type="password" class="form-control" id="password" name="password" placeholder="contraseña" minlength="9" pattern="^(?=.*[A-Z])(?=.*[a-z])(?=.*\d)(?=.*[^A-Za-z0-9]).{9,}$" title="Debe tener más de 8 caracteres, e incluir mayúscula, minúscula, número y carácter especial." autocomplete="new-password" required>
                    <label for="password">Nueva contraseña</label>
                </div><br>

                <div class="form-floating">
                    <input type="password" class="form-control" id="password_confirm" name="password_confirm" placeholder="contraseña" autocomplete="new-password" required>
                    <label for="password_confirm">Confirmar nueva contraseña</label>
                </div><br>

                <button class="btn bg-info-subtle me-2 border border-info-subtle rounded-5 w-100" type="submit">Cambiar contraseña</button><br><br>
                <a href="recuperar.php" class="nohighlight"><h6 class="h6 mb-3 fw-normal text-secondary">Solicitar un nuevo código</h6></a>
                <a href="/Subida/login.php" class="nohighlight"><h6 class="h6 mb-3 fw-normal text-secondary">Ir a iniciar sesión</h6></a>
            </form>
        <?php endif; ?>
    </main></div><br>
    <!-- END: FORMULARIO RESTABLECER CONTRASEÑA -->

    <!-- ★★★★★★★★★★★★★★★★★★★★★★★★★ -->

    <!-- BOTTOM BAR -->
    <footer class="p-3">
        <div class="container">
            <div>
                <ul class="nav col-12 col-lg-auto me-lg-auto mb-2 justify-content-center mb-md-0">
                    <li><a href="/Informacion/terminoscondiciones.pdf" class="nav-link px-2 text-secondary ahover">Terminos y Condiciones</a></li>
                    <li><a href="/Informacion/politicaprivacidad.pdf" class="nav-link px-2 text-secondary ahover">Politica de Privacidad</a></li>
                </ul>
            </div>
        </div>
    </footer><br>
    <!-- END: BOTTOM BAR -->

    <!-- ★★★★★★★★★★★★★★★★★★★★★★★★★ -->

    <!-- JS MANAGERS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@