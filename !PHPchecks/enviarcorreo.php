<?php

// Subimos un nivel para encontrar la carpeta vendor en la raíz del proyecto
require_once dirname(__DIR__) . "/vendor/phpmailer/phpmailer/src/Exception.php";
require_once dirname(__DIR__) . "/vendor/phpmailer/phpmailer/src/PHPMailer.php";
require_once dirname(__DIR__) . "/vendor/phpmailer/phpmailer/src/SMTP.php";

// Si usas el autoloader de Composer, también puedes usar simplemente:
// require_once dirname(__DIR__) . "/vendor/autoload.php";

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

/**
 * Envía un correo de recordatorio de cita.
 *
 * @param string $correo
 * @param string $nombre
 * @param string $apellido
 * @param string $fecha
 * @param string $hora
 *
 * @return bool
 */
function enviarRecordatorio($correo, $nombre, $apellido, $fecha, $hora) {

    $mail = new PHPMailer(true);

    try {

        // ==========================================
        // CONFIGURACIÓN SMTP
        // ==========================================

        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        
        $mail->Username   = 'veterinariaadmin@gmail.com';
        $mail->Password   = 'gffx tgoh ucdf ytaf'; 
        
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        // ==========================================
        // REMITENTE Y DESTINATARIO
        // ==========================================

        $mail->setFrom('veterinariaadmin@gmail.com', 'Veterinaria del Mar');
        $mail->addAddress($correo, $nombre . ' ' . $apellido);

        // ==========================================
        // CONFIGURACIÓN DEL MENSAJE
        // ==========================================

        $mail->isHTML(true);
        $mail->CharSet = 'UTF-8';
        $mail->Subject = 'Recordatorio de cita veterinaria';

        $mail->Body = '
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <title>Recordatorio de cita</title>
        </head>
        <body style="margin:0; padding:0; background-color:#f4f4f4; font-family:Arial, sans-serif;">
            <div style="padding:30px 10px;">
                <div style="max-width:600px; margin:auto; background:white; padding:30px; border-radius:10px;">
                    <h2 style="text-align:center; color:#333;">🐾 Recordatorio de cita</h2>
                    <p>Hola <strong>' . htmlspecialchars($nombre) . '</strong>,</p>
                    <p>Te recordamos que tienes una <strong>cita veterinaria</strong> programada.</p>
                    <div style="background:#f1f1f1; padding:20px; border-radius:8px; margin:20px 0;">
                        <p>📅 <strong>Fecha:</strong> ' . htmlspecialchars($fecha) . '</p>
                        <p>🕐 <strong>Hora:</strong> ' . htmlspecialchars($hora) . '</p>
                    </div>
                    <p>Te esperamos.</p>
                    <p>Si necesitas modificar o cancelar tu cita, comunícate con la veterinaria.</p>
                    <hr>
                    <p style="text-align:center; color:#888; font-size:12px;">
                        Este correo fue enviado automáticamente.<br>Veterinaria
                    </p>
                </div>
            </div>
        </body>
        </html>';

        $mail->AltBody = "Hola " . $nombre . " " . $apellido . ".\n\n" .
                         "Te recordamos que tienes una cita veterinaria.\n\n" .
                         "Fecha: " . $fecha . "\n" .
                         "Hora: " . $hora . "\n\n" .
                         "Te esperamos.";

        $mail->send();
        return true;

    } catch (Exception $e) {
        // Opcional: registrar el error en log para depuración
        // error_log("Error PHPMailer: " . $mail->ErrorInfo);
        return false;
    }
}
?>