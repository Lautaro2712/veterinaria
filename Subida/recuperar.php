<?php
require_once __DIR__ . "/../!PHPchecks/conexion.php";
require_once __DIR__ . "/../!PHPchecks/correo.php";

$exito = '';
$error   = null;

$conexion->exec('DELETE FROM recuperaciones WHERE expira < NOW()');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_var(trim($_POST['email'] ?? ''), FILTER_VALIDATE_EMAIL);

    if (!$email) {
        $error = 'Ingresá un correo electrónico válido.';
    } else {
        try {
            $stmt = $conexion->prepare('SELECT idCliente FROM cliente WHERE correoCliente = ? AND activo = TRUE');
            $stmt->execute([$email]);

            if ($stmt->fetch()) {
                $codigo = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

                $conexion->prepare(
                    'REPLACE INTO recuperaciones (email, codigo_hash, expira, intentos)
                     VALUES (?, ?, DATE_ADD(NOW(), INTERVAL 15 MINUTE), 0)'
                )->execute([$email, password_hash($codigo, PASSWORD_DEFAULT)]);

                $enviado = enviarCorreo(
                    $email,
                    'Código de recuperación',
                    "Tu código es: $codigo\nVence en 15 minutos.\nSi no lo pediste, ignora este mensaje."
                );

                if ($enviado) {
                    $exito = 'Te enviamos un código a tu correo.';
                } else {
                    $error = 'No pudimos enviar el correo. Intentá nuevamente en unos minutos.';
                }
            } else {
                $error = 'No existe una cuenta registrada con ese correo.';
            }
        } catch (PDOException $e) {
            error_log('Error en recuperar.php: ' . $e->getMessage());
            $error = 'Ocurrió un error. Intentá nuevamente.';
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

    <!-- FORMULARIO RECUPERAR CONTRASEÑA -->
    <div class="container-fluid margins"><main class="form-signin m-5">
        <?php if (!empty($error)): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        ✘ <?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
        <?php endif; ?>

        <?php if ($exito): ?>
            <div class="alert alert-success" role="alert">
                ✔ <?php echo htmlspecialchars($exito, ENT_QUOTES, 'UTF-8'); ?>
            </div>
            <a href="restablecer.php" class="nohighlight text-dark"><button class="btn bg-info-subtle me-2 border border-info-subtle rounded-5 w-100">Ya tengo mi código</button></a><br><br>
            <a href="recuperar.php" class="nohighlight text-dark"><button class="btn bg-primary-subtle me-2 border border-primary-subtle rounded-5 w-100">Solicitar un nuevo código</button></a><br><br>
            <a href="/Subida/login.php" class="nohighlight text-dark"><h6 class="h6 mb-3 fw-normal text-secondary">Ir a iniciar sesión</h6></a>

        <?php else: ?>
            <br><form method="POST">
                <h1 class="h3 mb-3 fw-normal text-secondary">Recuperar contraseña</h1>
                <p class="text-secondary" style="font-size: 0.9rem;">
                    Ingresá tu correo y te enviaremos un código de 6 dígitos para restablecer tu contraseña.
                </p>

                <div class="form-floating">
                    <input type="email" class="form-control" id="email" name="email" placeholder="nombre@ejemplo.com" maxlength="60" required>
                    <label for="email">Correo electronico</label>
                </div><br>

                <button class="btn bg-info-subtle me-2 border border-info-subtle rounded-5 w-100" type="submit">Enviar código</button><br><br>
                
                <a href="/Subida/login.php" class="nohighlight"><h6 class="h6 mb-3 fw-normal text-secondary">Ir a iniciar sesión</h6></a>
            </form>
        <?php endif; ?>
    </main></div><br>
    <!-- END: FORMULARIO RECUPERAR CONTRASEÑA -->

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
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
    <script src="/../!JS/showpw.js"></script>
    </div>
</body>
</html>