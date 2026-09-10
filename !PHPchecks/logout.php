<?php
session_start();
 
// Vacía todas las variables de sesión
$_SESSION = [];
 
// Si se usan cookies de sesión, hay que borrar también la cookie del navegador
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    );
}
 
// Destruye la sesión en el servidor
session_destroy();
 
// Redirige al inicio
header("Location: /index.php");
exit();
?>