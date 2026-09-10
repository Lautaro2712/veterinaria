<?php

require_once __DIR__ . "/../!PHPchecks/conexion.php";
require_once __DIR__ . "/../!PHPchecks/auth.php";

header('Content-Type: application/json');

/**
 * =====================================================================
 * CONFIGURACIÓN DE TABLAS EDITABLES
 * =====================================================================
 */
$configTablas = [

    'servicio' => [
        'idColumna' => 'idServicio',
        'rolesPermitidos' => [ROL_EMPLEADO, ROL_ADMINISTRADOR],
        'rolesEliminar' => [ROL_ADMINISTRADOR],
        'columnas' => [
            'fechaServicio' => ['tipo' => 'fecha'],
            'horaServicio'  => ['tipo' => 'hora'],
        ],
        'validacionExtra' => 'validarChoqueHorarioServicio',
    ],

    'producto' => [
        'idColumna' => 'idProducto',
        'rolesPermitidos' => [ROL_EMPLEADO, ROL_ADMINISTRADOR],
        'rolesEliminar' => [ROL_ADMINISTRADOR],
        'columnas' => [
            'nombreProducto' => ['tipo' => 'texto'],
            'tipoProducto'   => ['tipo' => 'texto'],
            'precioProducto' => ['tipo' => 'numero'],
            'stockProducto'  => ['tipo' => 'numero'],
            'idProveedor'    => ['tipo' => 'numero', 'resolverMuestra' => 'resolverNombreProveedor'],
        ],
        'validacionExtra' => 'validarProveedorExiste',
    ],

    'proveedor' => [
        'idColumna' => 'idProveedor',
        'rolesPermitidos' => [ROL_EMPLEADO, ROL_ADMINISTRADOR],
        'rolesEliminar' => [ROL_ADMINISTRADOR],
        'columnas' => [
            'rutProveedor'       => ['tipo' => 'numero'],
            'nombreProveedor'    => ['tipo' => 'texto'],
            'direccionProveedor' => ['tipo' => 'texto'],
            'telefonoProveedor'  => ['tipo' => 'numero'],
            'correoProveedor'    => ['tipo' => 'texto'],
        ],
    ],

    'cliente' => [
        'idColumna' => 'idCliente',
        'rolesPermitidos' => [ROL_EMPLEADO, ROL_ADMINISTRADOR],
        'rolesEliminar' => [ROL_ADMINISTRADOR],
        'columnas' => [
            'nombreCliente'   => ['tipo' => 'texto'],
            'apellidoCliente' => ['tipo' => 'texto'],
            'telefonoCliente' => ['tipo' => 'numero'],
            'correoCliente'   => ['tipo' => 'texto'],
        ],
    ],

    'empleado' => [
        'idColumna' => 'idEmpleado',
        'rolesPermitidos' => [ROL_ADMINISTRADOR],
        'rolesEliminar' => [ROL_ADMINISTRADOR],
        'columnas' => [
            'nombreEmpleado'   => ['tipo' => 'texto'],
            'apellidoEmpleado' => ['tipo' => 'texto'],
            'correoEmpleado'   => ['tipo' => 'texto'],
            'telefonoEmpleado' => ['tipo' => 'numero'],
            'rolEmpleado'      => ['tipo' => 'booleano', 'resolverMuestra' => 'resolverRolEmpleado'],
        ],
    ],

    'mascota' => [
        'idColumna' => 'idMascota',
        'rolesPermitidos' => [ROL_EMPLEADO, ROL_ADMINISTRADOR],
        'rolesEliminar' => [ROL_ADMINISTRADOR],
        'columnas' => [
            'nombreMascota'  => ['tipo' => 'texto'],
            'especieMascota' => ['tipo' => 'texto'],
            'razaMascota'    => ['tipo' => 'texto'],
            'sexoMascota'    => ['tipo' => 'texto'],
            'idCliente'      => ['tipo' => 'numero', 'resolverMuestra' => 'resolverNombreCliente'],
        ],
        'validacionExtra' => 'validarClienteExiste',
    ],

    'ficha_clinica' => [
        'idColumna' => 'idFichaClinica',
        'rolesPermitidos' => [ROL_EMPLEADO, ROL_ADMINISTRADOR],
        'rolesEliminar' => [ROL_ADMINISTRADOR],
        'columnas' => [
            'vacunacionFichaClinica'    => ['tipo' => 'texto'],
            'pesoFichaClinica'          => ['tipo' => 'numero'],
            'antecedentesFichaClinica'  => ['tipo' => 'texto'],
            'idMascota'                 => ['tipo' => 'numero', 'resolverMuestra' => 'resolverNombreMascota'],
        ],
        'validacionExtra' => 'validarMascotaExiste',
    ],

    'factura' => [
        'idColumna' => 'idFactura',
        'rolesPermitidos' => [ROL_EMPLEADO, ROL_ADMINISTRADOR],
        'rolesEliminar' => [ROL_ADMINISTRADOR],
        'columnas' => [
            'solicitaReciboFactura' => ['tipo' => 'booleano', 'resolverMuestra' => 'resolverSolicitaReciboFactura'],
            'fechaEmisionFactura'   => ['tipo' => 'fecha'],
            'montoTotalFactura'     => ['tipo' => 'numero'],
            'idCliente'             => ['tipo' => 'numero', 'resolverMuestra' => 'resolverNombreCliente'],
        ],
        // Se reutiliza la misma función que ya usa "mascota": solo mira
        // el nombre del campo (idCliente), no la tabla desde la que se
        // llama, así que sirve igual acá.
        'validacionExtra' => 'validarClienteExiste',
    ],

];


/**
 * Valida formatos.
 */
function validarFormato(string $tipo, string $valor): bool
{
    switch ($tipo) {

        case 'fecha':
            return (bool) preg_match('/^\d{4}-\d{2}-\d{2}$/', $valor);

        case 'hora':
            return (bool) preg_match('/^([01]\d|2[0-3]):[0-5]\d$/', $valor);

        case 'numero':
            return is_numeric($valor);

        case 'booleano':
            return in_array($valor, ['0', '1'], true);

        case 'texto':
            return trim($valor) !== '';

        default:
            return false;
    }
}


/**
 * Valida choque de horario.
 */
function validarChoqueHorarioServicio(
    PDO $conexion,
    string $idRegistro,
    string $campo,
    string $nuevoValor
): ?string {

    $sqlActual = "SELECT fechaServicio, horaServicio
                  FROM servicio
                  WHERE idServicio = ?
                  AND activo = TRUE";

    $stmtActual = $conexion->prepare($sqlActual);
    $stmtActual->execute([$idRegistro]);

    $actual = $stmtActual->fetch();

    if (!$actual) {
        return 'El registro no existe o está dado de baja.';
    }

    $nuevaFecha = ($campo === 'fechaServicio')
        ? $nuevoValor
        : $actual['fechaServicio'];

    $nuevaHora = ($campo === 'horaServicio')
        ? $nuevoValor
        : $actual['horaServicio'];

    $sqlCheck = "SELECT idServicio
                 FROM servicio
                 WHERE fechaServicio = ?
                 AND horaServicio = ?
                 AND idServicio != ?
                 AND activo = TRUE";

    $stmtCheck = $conexion->prepare($sqlCheck);
    $stmtCheck->execute([$nuevaFecha, $nuevaHora, $idRegistro]);

    if ($stmtCheck->fetchColumn() !== false) {
        return 'Ya existe otro servicio en esa fecha y hora.';
    }

    return null;
}


/**
 * Valida proveedor.
 */
function validarProveedorExiste(
    PDO $conexion,
    string $idRegistro,
    string $campo,
    string $nuevoValor
): ?string {

    if ($campo !== 'idProveedor') {
        return null;
    }

    $sqlProveedor = "SELECT idProveedor
                     FROM proveedor
                     WHERE idProveedor = ?
                     AND activo = TRUE";

    $stmtProveedor = $conexion->prepare($sqlProveedor);
    $stmtProveedor->execute([$nuevoValor]);

    if ($stmtProveedor->fetchColumn() === false) {
        return 'No existe ningún proveedor activo con ese ID.';
    }

    return null;
}


/**
 * Devuelve el nombre del proveedor.
 */
function resolverNombreProveedor(PDO $conexion, string $nuevoValor): ?string
{
    $sql = "SELECT nombreProveedor
            FROM proveedor
            WHERE idProveedor = ?";

    $stmt = $conexion->prepare($sql);
    $stmt->execute([$nuevoValor]);

    $fila = $stmt->fetch();

    return $fila ? $fila['nombreProveedor'] : null;
}


/**
 * Valida cliente (para idCliente de mascota).
 */
function validarClienteExiste(
    PDO $conexion,
    string $idRegistro,
    string $campo,
    string $nuevoValor
): ?string {

    if ($campo !== 'idCliente') {
        return null;
    }

    $sqlCliente = "SELECT idCliente
                   FROM cliente
                   WHERE idCliente = ?
                   AND activo = TRUE";

    $stmtCliente = $conexion->prepare($sqlCliente);
    $stmtCliente->execute([$nuevoValor]);

    if ($stmtCliente->fetchColumn() === false) {
        return 'No existe ningún cliente activo con ese ID.';
    }

    return null;
}


/**
 * Devuelve el nombre completo del cliente.
 */
function resolverNombreCliente(PDO $conexion, string $nuevoValor): ?string
{
    $sql = "SELECT nombreCliente, apellidoCliente
            FROM cliente
            WHERE idCliente = ?";

    $stmt = $conexion->prepare($sql);
    $stmt->execute([$nuevoValor]);

    $fila = $stmt->fetch();

    return $fila ? trim($fila['nombreCliente'] . ' ' . $fila['apellidoCliente']) : null;
}


/**
 * Valida mascota (para idMascota de ficha_clinica).
 */
function validarMascotaExiste(
    PDO $conexion,
    string $idRegistro,
    string $campo,
    string $nuevoValor
): ?string {

    if ($campo !== 'idMascota') {
        return null;
    }

    $sqlMascota = "SELECT idMascota
                   FROM mascota
                   WHERE idMascota = ?
                   AND activo = TRUE";

    $stmtMascota = $conexion->prepare($sqlMascota);
    $stmtMascota->execute([$nuevoValor]);

    if ($stmtMascota->fetchColumn() === false) {
        return 'No existe ninguna mascota activa con ese ID.';
    }

    return null;
}


/**
 * Devuelve el nombre de la mascota.
 */
function resolverNombreMascota(PDO $conexion, string $nuevoValor): ?string
{
    $sql = "SELECT nombreMascota
            FROM mascota
            WHERE idMascota = ?";

    $stmt = $conexion->prepare($sql);
    $stmt->execute([$nuevoValor]);

    $fila = $stmt->fetch();

    return $fila ? $fila['nombreMascota'] : null;
}


/**
 * Devuelve el rol del empleado.
 */
function resolverRolEmpleado(PDO $conexion, string $nuevoValor): ?string
{
    return ($nuevoValor === '1')
        ? 'Administrador'
        : 'Empleado';
}


/**
 * Devuelve el texto legible de "¿Se necesita recibo de factura?".
 */
function resolverSolicitaReciboFactura(PDO $conexion, string $nuevoValor): ?string
{
    return ($nuevoValor === '1')
        ? 'Sí'
        : 'No';
}


/**
 * =====================================================================
 * DATOS RECIBIDOS
 * =====================================================================
 */
$tabla = $_POST['tabla'] ?? '';
$idRegistro = $_POST['id'] ?? '';
$campo = $_POST['campo'] ?? '';
$valor = $_POST['valor'] ?? '';
$accion = $_POST['accion'] ?? 'actualizar';


/**
 * =====================================================================
 * PERFIL CLIENTE
 * =====================================================================
 */
if ($accion === 'actualizarPerfilCliente') {

    if (rolActual() !== ROL_CLIENTE) {

        http_response_code(403);

        echo json_encode([
            'exito' => false,
            'error' => 'No tenés permiso para realizar esta acción.'
        ]);

        exit();
    }

    $idCliente = idClienteActual();

    if ($idCliente === null) {

        http_response_code(401);

        echo json_encode([
            'exito' => false,
            'error' => 'No se pudo identificar al cliente de la sesión.'
        ]);

        exit();
    }

    $camposPermitidosCliente = [
        'nombreCliente',
        'apellidoCliente',
        'telefonoCliente',
        'correoCliente'
    ];

    if (!in_array($campo, $camposPermitidosCliente, true)) {

        echo json_encode([
            'exito' => false,
            'error' => 'No podés modificar ese campo.'
        ]);

        exit();
    }

    $valor = trim($valor);

    if ($valor === '') {

        echo json_encode([
            'exito' => false,
            'error' => 'El valor no puede estar vacío.'
        ]);

        exit();
    }

    if ($campo === 'correoCliente' &&
        !filter_var($valor, FILTER_VALIDATE_EMAIL)) {

        echo json_encode([
            'exito' => false,
            'error' => 'El correo electrónico no es válido.'
        ]);

        exit();
    }

    if ($campo === 'telefonoCliente' &&
        !is_numeric($valor)) {

        echo json_encode([
            'exito' => false,
            'error' => 'El teléfono debe contener solamente números.'
        ]);

        exit();
    }

    try {

        $sqlPerfil = "UPDATE cliente
                      SET `$campo` = ?
                      WHERE idCliente = ?
                      AND activo = TRUE";

        $stmtPerfil = $conexion->prepare($sqlPerfil);
        $stmtPerfil->execute([$valor, $idCliente]);

        if ($campo === 'nombreCliente') {
            $_SESSION['nombreCliente'] = $valor;
        }

        if ($campo === 'apellidoCliente') {
            $_SESSION['apellidoCliente'] = $valor;
        }

        echo json_encode([
            'exito' => true
        ]);

    } catch (PDOException $e) {

        echo json_encode([
            'exito' => false,
            'error' => 'No se pudieron actualizar los datos del perfil.'
        ]);
    }

    exit();
}


/**
 * =====================================================================
 * PERFIL EMPLEADO / ADMINISTRADOR
 * =====================================================================
 */
if ($accion === 'actualizarPerfilEmpleado') {

    if (!in_array(
        rolActual(),
        [ROL_EMPLEADO, ROL_ADMINISTRADOR],
        true
    )) {

        http_response_code(403);

        echo json_encode([
            'exito' => false,
            'error' => 'No tenés permiso para realizar esta acción.'
        ]);

        exit();
    }

    $idEmpleado = idEmpleadoActual();

    if ($idEmpleado === null) {

        http_response_code(401);

        echo json_encode([
            'exito' => false,
            'error' => 'No se pudo identificar al empleado de la sesión.'
        ]);

        exit();
    }

    $camposPermitidosEmpleado = [
        'nombreEmpleado',
        'apellidoEmpleado',
        'correoEmpleado',
        'telefonoEmpleado'
    ];

    if (!in_array($campo, $camposPermitidosEmpleado, true)) {

        echo json_encode([
            'exito' => false,
            'error' => 'No podés modificar ese campo.'
        ]);

        exit();
    }

    $valor = trim($valor);

    if ($valor === '') {

        echo json_encode([
            'exito' => false,
            'error' => 'El valor no puede estar vacío.'
        ]);

        exit();
    }

    if ($campo === 'correoEmpleado' &&
        !filter_var($valor, FILTER_VALIDATE_EMAIL)) {

        echo json_encode([
            'exito' => false,
            'error' => 'El correo electrónico no es válido.'
        ]);

        exit();
    }

    if ($campo === 'telefonoEmpleado' &&
        !is_numeric($valor)) {

        echo json_encode([
            'exito' => false,
            'error' => 'El teléfono debe contener solamente números.'
        ]);

        exit();
    }

    try {

        $sqlPerfil = "UPDATE empleado
                      SET `$campo` = ?
                      WHERE idEmpleado = ?
                      AND activo = TRUE";

        $stmtPerfil = $conexion->prepare($sqlPerfil);
        $stmtPerfil->execute([$valor, $idEmpleado]);

        if ($campo === 'nombreEmpleado') {
            $_SESSION['nombreCliente'] = $valor;
        }

        if ($campo === 'apellidoEmpleado') {
            $_SESSION['apellidoCliente'] = $valor;
        }

        echo json_encode([
            'exito' => true
        ]);

    } catch (PDOException $e) {

        echo json_encode([
            'exito' => false,
            'error' => 'No se pudieron actualizar los datos del perfil.'
        ]);
    }

    exit();
}


/**
 * =====================================================================
 * COMPROBAR TABLA
 * =====================================================================
 */
if (!array_key_exists($tabla, $configTablas)) {

    echo json_encode([
        'exito' => false,
        'error' => 'Tabla no editable.'
    ]);

    exit();
}

$config = $configTablas[$tabla];


/**
 * =====================================================================
 * BAJA LÓGICA
 * =====================================================================
 */
if ($accion === 'eliminar') {

    if ($idRegistro === '' || !ctype_digit((string) $idRegistro)) {

        echo json_encode([
            'exito' => false,
            'error' => 'ID de registro inválido.'
        ]);

        exit();
    }

    $rolesEliminar = $config['rolesEliminar'] ?? $config['rolesPermitidos'];

    if (!in_array(rolActual(), $rolesEliminar, true)) {

        http_response_code(403);

        echo json_encode([
            'exito' => false,
            'error' => 'No tenés permiso para dar de baja registros de esta tabla.'
        ]);

        exit();
    }

    try {

        $idColumna = $config['idColumna'];

        $sqlBaja = "UPDATE `$tabla`
                    SET activo = FALSE
                    WHERE `$idColumna` = ?
                    AND activo = TRUE";

        $stmtBaja = $conexion->prepare($sqlBaja);
        $stmtBaja->execute([$idRegistro]);

        if ($stmtBaja->rowCount() === 0) {

            echo json_encode([
                'exito' => false,
                'error' => 'El registro no existe o ya estaba dado de baja.'
            ]);

            exit();
        }

        echo json_encode([
            'exito' => true
        ]);

    } catch (PDOException $e) {

        echo json_encode([
            'exito' => false,
            'error' => 'No se pudo realizar la baja.'
        ]);
    }

    exit();
}


/**
 * =====================================================================
 * ACTUALIZAR
 * =====================================================================
 */
if (!in_array(rolActual(), $config['rolesPermitidos'], true)) {

    http_response_code(403);

    echo json_encode([
        'exito' => false,
        'error' => 'No tenés permiso para editar esta tabla.'
    ]);

    exit();
}


if ($idRegistro === '' || !ctype_digit((string) $idRegistro)) {

    echo json_encode([
        'exito' => false,
        'error' => 'ID de registro inválido.'
    ]);

    exit();
}


if (!array_key_exists($campo, $config['columnas'])) {

    echo json_encode([
        'exito' => false,
        'error' => 'Ese campo no se puede editar.'
    ]);

    exit();
}


$tipo = $config['columnas'][$campo]['tipo'];

if (!validarFormato($tipo, $valor)) {

    echo json_encode([
        'exito' => false,
        'error' => 'Formato inválido para este campo.'
    ]);

    exit();
}


try {

    if (!empty($config['validacionExtra']) &&
        function_exists($config['validacionExtra'])) {

        $errorExtra = call_user_func(
            $config['validacionExtra'],
            $conexion,
            $idRegistro,
            $campo,
            $valor
        );

        if ($errorExtra !== null) {

            echo json_encode([
                'exito' => false,
                'error' => $errorExtra
            ]);

            exit();
        }
    }

    $idColumna = $config['idColumna'];

    $sqlUpdate = "UPDATE `$tabla`
                  SET `$campo` = ?
                  WHERE `$idColumna` = ?
                  AND activo = TRUE";

    $stmtUpdate = $conexion->prepare($sqlUpdate);
    $stmtUpdate->execute([$valor, $idRegistro]);

    $respuesta = [
        'exito' => true
    ];

    $resolver = $config['columnas'][$campo]['resolverMuestra'] ?? null;

    if ($resolver !== null && function_exists($resolver)) {

        $valorMostrado = call_user_func(
            $resolver,
            $conexion,
            $valor
        );

        if ($valorMostrado !== null) {
            $respuesta['valorMostrado'] = $valorMostrado;
        }
    }

    echo json_encode($respuesta);

} catch (PDOException $e) {

    echo json_encode([
        'exito' => false,
        'error' => 'Error al actualizar: ' . $e->getMessage()
    ]);
}

?>