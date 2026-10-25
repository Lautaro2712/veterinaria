<?php

// ==========================================
// CONEXIÓN
// ==========================================
require_once __DIR__ . "/conexion.php";

// Verificamos que la variable de conexión existe y es una instancia de PDO
if (!isset($conexion) || !($conexion instanceof PDO)) {
    die("Error crítico: No se pudo verificar la conexión PDO a la base de datos.");
}

// ==========================================
// FUNCIÓN PARA ENVIAR CORREOS
// ==========================================

require_once __DIR__ . "/enviarcorreo.php";


// ==========================================
// BUSCAR CITAS DE MAÑANA
// ==========================================

// ==========================================
// BUSCAR CITAS DE MAÑANA
// ==========================================

// Zona horaria de Uruguay: "mañana" se calcula acá y no con CURDATE(),
// que depende de la hora del servidor MySQL.
date_default_timezone_set('America/Montevideo');
$manana = date('Y-m-d', strtotime('+1 day'));

$sql = "SELECT
            s.idServicio,
            s.tipoServicio,
            ts.servicio AS nombreServicio,
            s.fechaServicio,
            s.horaServicio,
            s.idCliente,
            c.nombreCliente,
            c.apellidoCliente,
            c.correoCliente
        FROM servicio s
        INNER JOIN cliente c
            ON s.idCliente = c.idCliente
        INNER JOIN tipo_servicio ts
            ON ts.id = s.tipoServicio
        WHERE ts.esCita = 1
            AND s.fechaServicio = :manana
            AND s.notificacion_enviada = 0
            AND s.activo = 1
            AND c.activo = 1";

try {
    $stmt = $conexion->prepare($sql);
    $stmt->execute([':manana' => $manana]);
    // Obtenemos todas las citas en un array asociativo
    $citas = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("✗ Correo enviado, pero falló la actualización en base de datos: " . htmlspecialchars($e->getMessage()) . ")");
}

// ==========================================
// CONTADORES
// ==========================================

$encontradas = 0;
$enviadas = 0;
$errores = 0;


// ==========================================
// RECORRER LAS CITAS
// ==========================================

foreach ($citas as $cita) {

    $encontradas++;


    // ======================================
    // DATOS
    // ======================================

    $idServicio = $cita['idServicio'];
    $nombre     = $cita['nombreCliente'];
    $apellido   = $cita['apellidoCliente'];
    $correo     = $cita['correoCliente'];
    $fecha      = $cita['fechaServicio'];
    $hora       = $cita['horaServicio'];


    // ======================================
    // FORMATEAR FECHA Y HORA
    // ======================================

    $fechaFormateada = date("d/m/Y", strtotime($fecha));
    $horaFormateada  = date("H:i", strtotime($hora));


    // ======================================
    // ENVIAR CORREO
    // ======================================

    $correoEnviado = enviarRecordatorio
    (
        $correo,
        $nombre,
        $apellido,
        $fechaFormateada,
        $horaFormateada
    );


    // ======================================
    // SI SE ENVIÓ CORRECTAMENTE
    // ======================================

    if ($correoEnviado) {

        // ==================================
        // MARCAR COMO ENVIADA (PDO Prepared Statement)
        // ==================================

        try {
            $sqlActualizar = "UPDATE servicio 
                              SET notificacion_enviada = 1 
                              WHERE idServicio = :idServicio";

            $stmtUpdate = $conexion->prepare($sqlActualizar);
            $stmtUpdate->execute([':idServicio' => $idServicio]);

            $enviadas++;

            // ==============================
            // MOSTRAR RESULTADO EXITOSO
            // ==============================

            echo "
                <p style='color:green;'>
                    ✓ Correo enviado correctamente
                    <br>
                    Cliente:
                    <strong>
                        " . htmlspecialchars($nombre) . "
                        " . htmlspecialchars($apellido) . "
                    </strong>
                    <br>
                    Correo:
                    " . htmlspecialchars($correo) . "
                    <br>
                    Fecha:
                    " . $fechaFormateada . "
                    <br>
                    Hora:
                    " . $horaFormateada . "
                </p>
            ";

        } catch (PDOException $e) {
            $errores++;
            echo "
                <p style='color:red;'>
                    ✗ Correo enviado, pero falló la actualización en base de datos: " . $e->getMessage() . "
                </p>
            ";
        }

    } else {

        // ==================================
        // ERROR EN EL ENVÍO
        // ==================================

        $errores++;

        echo "
            <p style='color:red;'>
                ✗ No se pudo enviar el correo
                <br>
                Cliente:
                <strong>
                    " . htmlspecialchars($nombre) . "
                    " . htmlspecialchars($apellido) . "
                </strong>
                <br>
                Correo:
                " . htmlspecialchars($correo) . "
            </p>
        ";

    }

}


// ==========================================
// RESUMEN
// ==========================================

echo "<hr>";
echo "<h3>Proceso terminado</h3>";

echo "
    <p>
        Citas encontradas:
        <strong>$encontradas</strong>
    </p>
";

echo "
    <p>
        Correos enviados:
        <strong>$enviadas</strong>
    </p>
";

echo "
    <p>
        Errores:
        <strong>$errores</strong>
    </p>
";


// ==========================================
// CERRAR CONEXIÓN
// ==========================================

// En PDO no existe mysqli_close(). La conexión se cierra asignándole null
$conexion = null;

?>