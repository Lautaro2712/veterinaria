<?php
/**
 * correo.php — Envío de correos del sistema (por SMTP, con PHPMailer).
 *
 * ---------------------------------------------------------------------
 * PUESTA A PUNTO (se hace una sola vez)
 * ---------------------------------------------------------------------
 * 1) Instalar PHPMailer, de cualquiera de estas dos formas:
 *      a) Con Composer, desde la carpeta raíz del sitio:
 *             composer require phpmailer/phpmailer
 *         (queda en /vendor, que este archivo detecta solo).
 *      b) A mano: descargar PHPMailer desde
 *         https://github.com/PHPMailer/PHPMailer y copiar su carpeta
 *         "src" como  !PHPchecks/PHPMailer/src/
 *
 * 2) Completar los datos SMTP de más abajo. Con Gmail hace falta una
 *    "contraseña de aplicación" (no la contraseña normal de la cuenta);
 *    se genera en la configuración de seguridad de Google, con la
 *    verificación en dos pasos activada.
 *
 * Igual que la clave de conexion.php, la contraseña SMTP no debe
 * subirse a repositorios públicos.
 */

// ---------------------------------------------------------------------
// CONFIGURACIÓN SMTP
// ---------------------------------------------------------------------
const CORREO_SMTP_HOST        = 'smtp.gmail.com';
const CORREO_SMTP_PUERTO      = 587;
const CORREO_SMTP_SEGURIDAD   = 'tls';   // 'tls' (STARTTLS, puerto 587) o 'ssl' (puerto 465)
const CORREO_SMTP_USUARIO     = 'veterinariaadmin@gmail.com';
const CORREO_SMTP_PASSWORD    = 'gffx tgoh ucdf ytaf';
const CORREO_REMITENTE        = 'veterinariaadmin@gmail.com';
const CORREO_NOMBRE_REMITENTE = 'Veterinaria del Mar';


/**
 * Carga PHPMailer si está instalado (Composer o copia manual).
 */
function correoCargarPHPMailer(): bool
{
    $clase = 'PHPMailer\\PHPMailer\\PHPMailer';

    if (class_exists($clase)) {
        return true;
    }

    $autoloadComposer = __DIR__ . '/../vendor/autoload.php';
    if (is_file($autoloadComposer)) {
        require_once $autoloadComposer;
        if (class_exists($clase)) {
            return true;
        }
    }

    $carpetaManual = __DIR__ . '/PHPMailer/src/';
    if (is_file($carpetaManual . 'PHPMailer.php')) {
        require_once $carpetaManual . 'Exception.php';
        require_once $carpetaManual . 'PHPMailer.php';
        require_once $carpetaManual . 'SMTP.php';
    }

    return class_exists($clase);
}


/**
 * Envía al cliente el aviso de que su cita fue dada de baja, con el
 * motivo que escribió el empleado.
 *
 * Devuelve ['exito' => bool, 'error' => string]. Nunca lanza
 * excepciones hacia afuera: el que llama decide qué hacer si falla (la
 * baja ya está hecha y no se deshace por un problema con el mail).
 */
function enviarCorreoBajaCita(
    string $correoCliente,
    string $nombreCliente,
    string $tipoServicio,
    string $fechaServicio,
    string $horaServicio,
    string $motivo
): array {

    if (strpos(CORREO_SMTP_USUARIO, 'PEGAR_AQUI') === 0
        || strpos(CORREO_SMTP_PASSWORD, 'PEGAR_AQUI') === 0
        || strpos(CORREO_REMITENTE, 'PEGAR_AQUI') === 0) {

        return ['exito' => false, 'error' => 'Faltan configurar los datos SMTP en correo.php.'];
    }

    if (!filter_var($correoCliente, FILTER_VALIDATE_EMAIL)) {
        return ['exito' => false, 'error' => 'El correo registrado del cliente no es válido.'];
    }

    if (!correoCargarPHPMailer()) {
        return ['exito' => false, 'error' => 'PHPMailer no está instalado (ver instrucciones en correo.php).'];
    }

    // Fecha y hora en formato legible (d/m/Y y HH:MM).
    $fecha = DateTime::createFromFormat('Y-m-d', $fechaServicio);
    $fechaTexto = $fecha ? $fecha->format('d/m/Y') : $fechaServicio;
    $horaTexto = substr($horaServicio, 0, 5);

    // Todo lo que viene de la base o del empleado se escapa antes de
    // meterlo en el HTML del mail (el motivo es texto libre).
    $nombreHtml = htmlspecialchars($nombreCliente, ENT_QUOTES, 'UTF-8');
    $tipoHtml   = htmlspecialchars($tipoServicio, ENT_QUOTES, 'UTF-8');
    $fechaHtml  = htmlspecialchars($fechaTexto, ENT_QUOTES, 'UTF-8');
    $horaHtml   = htmlspecialchars($horaTexto, ENT_QUOTES, 'UTF-8');
    $motivoHtml = nl2br(htmlspecialchars($motivo, ENT_QUOTES, 'UTF-8'));

    $cuerpoHtml =
        "<p>Hola {$nombreHtml},</p>" .
        "<p>Lamentamos informarle que su cita (servicio: <strong>{$tipoHtml}</strong>) " .
        "del <strong>{$fechaHtml}</strong> a las <strong>{$horaHtml}</strong> " .
        "en Veterinaria del Mar fue dada de baja.</p>" .
        "<p><strong>Motivo:</strong><br>{$motivoHtml}</p>" .
        "<p>Si desea reprogramarla, puede solicitar una nueva cita desde nuestro sitio web. " .
        "Disculpe las molestias.</p>" .
        "<p>Veterinaria del Mar</p>";

    $cuerpoTexto =
        "Hola {$nombreCliente},\n\n" .
        "Lamentamos informarle que su cita (servicio: {$tipoServicio}) del {$fechaTexto} " .
        "a las {$horaTexto} en Veterinaria del Mar fue dada de baja.\n\n" .
        "Motivo:\n{$motivo}\n\n" .
        "Si desea reprogramarla, puede solicitar una nueva cita desde nuestro sitio web. " .
        "Disculpe las molestias.\n\n" .
        "Veterinaria del Mar";

    try {

        $mail = new \PHPMailer\PHPMailer\PHPMailer(true);

        $mail->isSMTP();
        $mail->Host       = CORREO_SMTP_HOST;
        $mail->SMTPAuth   = true;
        $mail->Username   = CORREO_SMTP_USUARIO;
        $mail->Password   = CORREO_SMTP_PASSWORD;
        $mail->Port       = CORREO_SMTP_PUERTO;
        $mail->SMTPSecure = (CORREO_SMTP_SEGURIDAD === 'ssl')
            ? \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_SMTPS
            : \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
        $mail->CharSet    = 'UTF-8';
        $mail->Timeout    = 15;

        $mail->setFrom(CORREO_REMITENTE, CORREO_NOMBRE_REMITENTE);
        $mail->addAddress($correoCliente, $nombreCliente);

        $mail->isHTML(true);
        $mail->Subject = 'Baja de su cita en Veterinaria del Mar';
        $mail->Body    = $cuerpoHtml;
        $mail->AltBody = $cuerpoTexto;

        $mail->send();

        return ['exito' => true, 'error' => ''];

    } catch (\Throwable $e) {

        return ['exito' => false, 'error' => 'No se pudo enviar el mail: ' . $e->getMessage()];
    }
}