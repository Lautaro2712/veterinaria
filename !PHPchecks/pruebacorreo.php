<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

use PHPMailer\PHPMailer\PHPMailer;

require __DIR__ . "/../vendor/autoload.php";

$mail = new PHPMailer(true);
try {
    $mail->SMTPDebug  = 2;
    $mail->Debugoutput = 'html';
    $mail->isSMTP();
    $mail->Host       = 'smtp.gmail.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = 'veterinariaadmin@gmail.com';
    $mail->Password   = 'gffx tgoh ucdf ytaf';
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = 587;

    $mail->SMTPOptions = ['ssl' => [
        'verify_peer' => false,
        'verify_peer_name' => false,
        'allow_self_signed' => true
    ]];

    $mail->setFrom('veterinariaadmin@gmail.com', 'Prueba');
    $mail->addAddress('lautaromachin06@gmail.com');
    $mail->Subject = 'Prueba';
    $mail->Body    = 'Si lees esto, funciona.';
    $mail->send();
    echo '<h3>ENVIADO</h3>';
} catch (Exception $e) {
    echo '<h3>ERROR: ' . $mail->ErrorInfo . '</h3>';
}