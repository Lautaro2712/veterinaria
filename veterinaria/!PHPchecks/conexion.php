<?php
// Define la dirección del servidor de base de datos (localhost se refiere a la misma máquina donde corre PHP)
$host = "localhost";

// Usuario por defecto para conectarse a MySQL en entornos de desarrollo locales como XAMPP o WAMP
$usuario = "root";

// Contraseña para el usuario root (en XAMPP viene completamente vacía por defecto)
$password = "";

// Nombre exacto de la base de datos a la que nos queremos conectar en phpMyAdmin
$base_datos = "veterinaria";

// Iniciamos un bloque try-catch para capturar posibles errores de conexión sin que el programa colapse
try {
    // Instanciamos la clase PDO creando un nuevo objeto de conexión
    // Pasamos el DSN (Data Source Name) con el controlador (mysql), host, nombre de BD y codificación de caracteres (utf8)
    $conexion = new PDO("mysql:host=$host;dbname=$base_datos;charset=utf8", $usuario, $password);

    // Le indicamos a PDO que ante cualquier error de SQL, lance una excepción manejable (PDOException)
    $conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Si la línea de arriba falla por algún motivo (BD no existe, contraseña mal, etc.), se ejecuta el bloque catch
} catch (PDOException $e) {
    // Cortamos inmediatamente la ejecución del script (die) y mostramos un mensaje descriptivo con el error
    die("Error crítico al conectar con la base de datos: " . $e->getMessage());
}
?>