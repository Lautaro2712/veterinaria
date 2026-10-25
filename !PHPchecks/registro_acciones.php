<?php
/**
 * registro_acciones.php — Bitácora de acciones de empleados/administradores.
 *
 * Guarda en la tabla registro_acciones QUIÉN hizo QUÉ y CUÁNDO cada vez que
 * un empleado o administrador da de alta, modifica o da de baja algo. Es un
 * proceso de fondo: no tiene pantalla ni formulario, y la lista solo se
 * consulta desde la base de datos.
 *
 * Va en la carpeta !PHPchecks, junto a auth.php y conexion.php.
 *
 * USO (una línea, DESPUÉS de que la acción ya se hizo con éxito):
 *
 *     require_once __DIR__ . "/../!PHPchecks/registro_acciones.php";
 *     ...
 *     registrarAccion($conexion, "Alta de producto (id 15): Alimento X");
 *
 * Convención para el texto: empezar con "Alta", "Modificación" o "Baja" y
 * después qué se tocó, para que sea fácil filtrar desde la base de datos.
 */

require_once __DIR__ . '/auth.php';


/**
 * Registra una acción del empleado/administrador que tiene la sesión abierta.
 *
 * - Si en la sesión no hay un empleado (un cliente o un visitante), no hace
 *   nada: solo se registran las acciones del personal. Por eso se puede
 *   llamar sin miedo desde código que también usan los clientes.
 * - Nunca lanza errores hacia afuera: si por algún motivo no se puede
 *   guardar el registro, el error va al log de PHP y la acción original no
 *   se ve afectada.
 * - Llamarla DESPUÉS de confirmar la acción (y, si hay transacción,
 *   después del commit): así no se registran acciones que se deshicieron.
 */
function registrarAccion(PDO $conexion, string $accion): void
{
    $idEmpleado = idEmpleadoActual();

    if ($idEmpleado === null) {
        return;
    }

    try {

        // Fecha y hora siempre en horario de Uruguay, sin depender de cómo
        // esté configurado el servidor ni de qué página la llame.
        $ahora = new DateTime('now', new DateTimeZone('America/Montevideo'));

        $sql = "INSERT INTO registro_acciones
                    (fechaRegistroAcciones, horaRegistroAcciones, accionRegistroAcciones, idEmpleado)
                VALUES (?, ?, ?, ?)";

        $stmt = $conexion->prepare($sql);
        $stmt->execute([
            $ahora->format('Y-m-d'),
            $ahora->format('H:i:s'),
            mb_substr($accion, 0, 1000, 'UTF-8'),
            $idEmpleado
        ]);

    } catch (Throwable $e) {

        error_log('registrarAccion: no se pudo guardar el registro (' . $e->getMessage() . ')');
    }
}