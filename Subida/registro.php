<?php
// Carga e interpreta el código de conexion.php
require_once __DIR__ . "/../!PHPchecks/conexion.php";

// Clave SECRETA de hCaptcha (la que aparece en el panel de hcaptcha.com,
// junto a la sitekey que ya está en el formulario). A diferencia de la
// sitekey, esta NO es pública: no debe ir nunca en el HTML ni en el JS.
$hcaptchaSecret = 'ES_bbfad414eecd46dc8171e9497b51a6e1';
// true = al fallar el captcha se muestra el motivo técnico en pantalla.
// Ponerlo en false una vez que el captcha funcione.
$hcaptchaDepuracion = true;

/**
 * Verifica con los servidores de hCaptcha que el token que mandó el
 * navegador corresponde a un captcha realmente resuelto.
 *
 * Devuelve un array con:
 *   'exito'   => true/false
 *   'detalle' => texto técnico con el motivo del fallo (vacío si salió bien)
 *
 * Esta es la única comprobación que cuenta: el widget de hCaptcha solo
 * rellena un campo oculto (h-captcha-response) en el navegador, y el
 * navegador NO bloquea el envío del formulario si ese campo está vacío.
 * Sin esta llamada al servidor, cualquiera podría registrarse saltándose
 * el captcha.
 */
function verificarHCaptcha(string $token, string $secret): array
{
    if ($token === '') {
        return ['exito' => false, 'detalle' => 'no llegó el token del captcha (h-captcha-response vacío)'];
    }

    if ($secret === '' || $secret === 'PEGAR_AQUI_LA_SECRET_KEY_DE_HCAPTCHA') {
        return ['exito' => false, 'detalle' => 'falta configurar la secret key de hCaptcha en $hcaptchaSecret'];
    }

    $datos = http_build_query([
        'secret'   => $secret,
        'response' => $token,
        'remoteip' => $_SERVER['REMOTE_ADDR'] ?? ''
    ]);

    $respuesta = false;
    $errorConexion = '';

    if (function_exists('curl_init')) {

        $ch = curl_init('https://api.hcaptcha.com/siteverify');
        curl_setopt_array($ch, [
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $datos,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 10
        ]);
        $respuesta = curl_exec($ch);

        if ($respuesta === false) {
            $errorConexion = 'cURL: ' . curl_error($ch) . ' (código ' . curl_errno($ch) . ')';
        }

        curl_close($ch);

    } else {

        $contexto = stream_context_create([
            'http' => [
                'method'  => 'POST',
                'header'  => "Content-Type: application/x-www-form-urlencoded\r\n",
                'content' => $datos,
                'timeout' => 10
            ]
        ]);
        $respuesta = @file_get_contents('https://api.hcaptcha.com/siteverify', false, $contexto);

        if ($respuesta === false) {
            $errorConexion = 'no se pudo conectar con hCaptcha (file_get_contents)';
        }
    }

    if ($respuesta === false) {
        return ['exito' => false, 'detalle' => $errorConexion];
    }

    $json = json_decode($respuesta, true);

    if (!is_array($json)) {
        return ['exito' => false, 'detalle' => 'respuesta inesperada de hCaptcha: ' . substr($respuesta, 0, 200)];
    }

    if (!empty($json['success'])) {
        return ['exito' => true, 'detalle' => ''];
    }

    $codigos = isset($json['error-codes']) && is_array($json['error-codes'])
        ? implode(', ', $json['error-codes'])
        : 'sin códigos de error';

    return ['exito' => false, 'detalle' => 'hCaptcha rechazó la verificación: ' . $codigos];
}

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
    // hCaptcha agrega este campo oculto al formulario cuando se resuelve.
    $tokenCaptcha = $_POST["h-captcha-response"] ?? "";
 
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

    // 4.5 VALIDACIÓN: EL CAPTCHA DEBE ESTAR RESUELTO Y SER VÁLIDO
    //     Se verifica contra los servidores de hCaptcha (no alcanza con
    //     mirar que el campo no esté vacío: cualquiera podría inventar
    //     un valor a mano).
    } elseif (($resultadoCaptcha = verificarHCaptcha($tokenCaptcha, $hcaptchaSecret)) && !$resultadoCaptcha['exito']) {
        $error = "Por favor, complete el captcha para poder registrarse.";

        // Mientras se está poniendo a punto el captcha, se muestra el
        // motivo técnico real. Pasar $hcaptchaDepuracion a false cuando
        // ya funcione, para no mostrarle detalles internos a los usuarios.
        if ($hcaptchaDepuracion) {
            $error .= " [Detalle técnico: " . $resultadoCaptcha['detalle'] . "]";
        }
 
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
                ✔ ¡Registro exitoso!
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        <br><form method="POST">
            <h1 class="h3 mb-3 fw-normal text-secondary">Registro</h1> 
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
                <input type="password" class="form-control" id="passwordCliente" name="passwordCliente" placeholder="contraseña" minlength="9" pattern="^(?=.*[A-Z])(?=.*[a-z])(?=.*\d)(?=.*[^A-Za-z0-9]).{9,}$" title="La contraseña debe tener más de 8 caracteres, incluir mayúscula, minúscula, número y carácter especial (!, ?, etc)" required> 
                <label for="passwordCliente">Contraseña</label>
            </div><br>
            <div class="form-floating"> 
                <input type="password" class="form-control" id="repetirPasswordCliente" name="repetirPasswordCliente" placeholder="contraseña" required> 
                <label for="repetirPasswordCliente">Confirmar contraseña</label><br>
                <div class="ps-3 pe-2 pt-2 bg-light border rounded-3">
                    <p class="h6 mb-3 fs-6 text-secondary ">La contraseña debe incluir al menos 9 caracteres, una mayúscula, una minúscula, un número y un carácter especial (!, ?, etc)</p>
                </div>
            </div><br>
            <div class="h-captcha" id="captcha" data-sitekey="20989f14-9b54-4a1a-a80a-9e76ecda2ff4"></div><br>
            <button class="btn bg-info-subtle me-2 border border-info-subtle rounded-5 w-100" type="submit">Registrarse</button><br><br>
            <a href="/Subida/login.php" class="nohighlight"><h6 class="h6 mb-3 fw-normal text-secondary">Ya tengo una cuenta</h6></a>  
        </form> 
    </main></div><br>
    <!-- END: FORMULARIO REGISTRO -->
 
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

    <!-- Aviso rápido: si el captcha no está resuelto, frena el envío y lo
         avisa en el momento. Es solo comodidad para el usuario; la
         verificación que realmente protege el registro es la del servidor
         (verificarHCaptcha, arriba), porque esta se puede saltear. -->
    <script>
        (function () {
            const formulario = document.querySelector('form[method="POST"]');
            if (!formulario) return;

            formulario.addEventListener('submit', function (e) {
                const campoCaptcha = formulario.querySelector('[name="h-captcha-response"]');

                if (!campoCaptcha || campoCaptcha.value.trim() === '') {
                    e.preventDefault();
                    alert('Por favor, complete el captcha antes de registrarse.');
                }
            });
        })();
    </script>
</body>
</html>