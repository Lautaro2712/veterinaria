<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require dirname(__DIR__) . "/vendor/autoload.php";

function enviarCorreo(string $para, string $asunto, string $texto): bool {
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'veterinariaadmin@gmail.com';
        $mail->Password   = 'gffx tgoh ucdf ytaf';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;
        $mail->CharSet    = 'UTF-8';

        $mail->setFrom('veterinariaadmin@gmail.com', 'Veterinaria del Mar');
        $mail->addAddress($para);
        $mail->Subject = $asunto;
        $mail->Body    = $texto;
        return $mail->send();

    } catch (Exception $e) {
    die('Error de correo: ' . $mail->ErrorInfo);
}
}