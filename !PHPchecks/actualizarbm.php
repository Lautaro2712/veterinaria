<?php

require_once __DIR__ . "/../!PHPchecks/conexion.php";
require_once __DIR__ . "/../!PHPchecks/auth.php";
require_once __DIR__ . "/../!PHPchecks/registro_acciones.php";

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
            'fechaRegistroFichaClinica' => ['tipo' => 'fecha'],
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
            'numeroFactura'         => ['tipo' => 'numero'],
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

    'registro_hotel' => [
        'idColumna' => 'idRegistroHotel',
        'rolesPermitidos' => [ROL_EMPLEADO, ROL_ADMINISTRADOR],
        'rolesEliminar' => [ROL_ADMINISTRADOR],
        'columnas' => [
            // numeroCuchetaRegistroHotel NO está acá a propósito: se
            // asigna sola al ingresar la mascota (subirmascotahotel.php)
            // según qué cuchetas estén libres en ese momento, y no debe
            // poder editarse a mano desde este panel, porque eso podría
            // dejar a dos mascotas activas compartiendo el mismo número
            // de cucheta.
            'idMascotaRegistroHotel'    => ['tipo' => 'numero', 'resolverMuestra' => 'resolverNombreMascota'],
            'fechaIngresoRegistroHotel' => ['tipo' => 'fecha'],
            'fechaPartidaRegistroHotel' => ['tipo' => 'fecha'],
        ],
        'validacionExtra' => 'validarMascotaEnHotel',
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
 * Valida el campo idMascotaRegistroHotel de "registro_hotel": la mascota
 * tiene que existir y estar activa, y además no puede estar ya alojada
 * en OTRA cucheta activa del hotel (si ya está en la fila que se está
 * editando, eso no cuenta como conflicto).
 */
function validarMascotaEnHotel(
    PDO $conexion,
    string $idRegistro,
    string $campo,
    string $nuevoValor
): ?string {

    if ($campo !== 'idMascotaRegistroHotel') {
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

    $sqlOtraCucheta = "SELECT idRegistroHotel
                       FROM registro_hotel
                       WHERE idMascotaRegistroHotel = ?
                       AND activo = TRUE
                       AND idRegistroHotel != ?";

    $stmtOtraCucheta = $conexion->prepare($sqlOtraCucheta);
    $stmtOtraCucheta->execute([$nuevoValor, $idRegistro]);

    if ($stmtOtraCucheta->fetchColumn() !== false) {
        return 'Esa mascota ya está alojada en otra cucheta del hotel.';
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
 * CAMBIO DE CONTRASEÑA (CLIENTE O EMPLEADO/ADMINISTRADOR)
 * =====================================================================
 */
if ($accion === 'cambiarPassword') {

    if (!estaLogueado()) {

        http_response_code(401);

        echo json_encode([
            'exito' => false,
            'error' => 'Debés iniciar sesión para realizar esta acción.'
        ]);

        exit();
    }

    $passwordActual = (string) ($_POST['passwordActual'] ?? '');
    $passwordNueva = (string) ($_POST['passwordNueva'] ?? '');
    $passwordConfirmar = (string) ($_POST['passwordConfirmar'] ?? '');

    if ($passwordActual === '' || $passwordNueva === '' || $passwordConfirmar === '') {

        echo json_encode([
            'exito' => false,
            'error' => 'Completá los tres campos de contraseña.'
        ]);

        exit();
    }

    if ($passwordNueva !== $passwordConfirmar) {

        echo json_encode([
            'exito' => false,
            'error' => 'La nueva contraseña y su confirmación no coinciden.'
        ]);

        exit();
    }

    // Se vuelve a comprobar la longitud mínima del lado del servidor:
    // la comprobación de JS es solo para dar feedback rápido, nunca hay
    // que confiar únicamente en la validación del navegador.
    if (strlen($passwordNueva) < 8) {

        echo json_encode([
            'exito' => false,
            'error' => 'La nueva contraseña debe tener al menos 8 caracteres.'
        ]);

        exit();
    }

    if ($passwordNueva === $passwordActual) {

        echo json_encode([
            'exito' => false,
            'error' => 'La nueva contraseña debe ser distinta de la actual.'
        ]);

        exit();
    }

    $rol = rolActual();

    // Según el rol de la sesión, se determina en qué tabla y con qué
    // columnas hay que trabajar. El rol se lee de $_SESSION, nunca de
    // algo que mande el cliente, así que un empleado no puede pedir
    // "cambiame la contraseña de cliente" ni viceversa.
    if ($rol === ROL_CLIENTE) {

        $idUsuario = idClienteActual();
        $tablaUsuario = 'cliente';
        $columnaId = 'idCliente';
        $columnaPassword = 'passwordCliente';

    } elseif ($rol === ROL_EMPLEADO || $rol === ROL_ADMINISTRADOR) {

        $idUsuario = idEmpleadoActual();
        $tablaUsuario = 'empleado';
        $columnaId = 'idEmpleado';
        $columnaPassword = 'passwordEmpleado';

    } else {

        http_response_code(403);

        echo json_encode([
            'exito' => false,
            'error' => 'No tenés permiso para realizar esta acción.'
        ]);

        exit();
    }

    if ($idUsuario === null) {

        http_response_code(401);

        echo json_encode([
            'exito' => false,
            'error' => 'No se pudo identificar al usuario de la sesión.'
        ]);

        exit();
    }

    try {

        $sqlBuscar = "SELECT `$columnaPassword` AS hashActual
                      FROM `$tablaUsuario`
                      WHERE `$columnaId` = ?
                      AND activo = TRUE";

        $stmtBuscar = $conexion->prepare($sqlBuscar);
        $stmtBuscar->execute([$idUsuario]);

        $fila = $stmtBuscar->fetch();

        if (!$fila || !password_verify($passwordActual, $fila['hashActual'])) {

            echo json_encode([
                'exito' => false,
                'error' => 'La contraseña actual ingresada es incorrecta.'
            ]);

            exit();
        }

        $nuevoHash = password_hash($passwordNueva, PASSWORD_DEFAULT);

        $sqlActualizar = "UPDATE `$tablaUsuario`
                          SET `$columnaPassword` = ?
                          WHERE `$columnaId` = ?
                          AND activo = TRUE";

        $stmtActualizar = $conexion->prepare($sqlActualizar);
        $stmtActualizar->execute([$nuevoHash, $idUsuario]);

        // Solo se registra el hecho, nunca la contraseña. Si quien la cambió
        // es un cliente, registrarAccion() no hace nada (solo anota al personal).
        registrarAccion($conexion, "Modificación de su propia contraseña (empleado id $idUsuario)");

        echo json_encode([
            'exito' => true
        ]);

    } catch (PDOException $e) {

        echo json_encode([
            'exito' => false,
            'error' => 'No se pudo actualizar la contraseña.'
        ]);
    }

    exit();
}


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

        // Valor anterior, solo para dejarlo en la bitácora ($campo ya fue
        // validado contra la lista de campos permitidos más arriba).
        $stmtAnteriorPerfil = $conexion->prepare("SELECT `$campo` FROM empleado WHERE idEmpleado = ? AND activo = TRUE");
        $stmtAnteriorPerfil->execute([$idEmpleado]);
        $valorAnteriorPerfil = $stmtAnteriorPerfil->fetchColumn();

        $sqlPerfil = "UPDATE empleado
                      SET `$campo` = ?
                      WHERE idEmpleado = ?
                      AND activo = TRUE";

        $stmtPerfil = $conexion->prepare($sqlPerfil);
        $stmtPerfil->execute([$valor, $idEmpleado]);

        // Solo se registra si el dato realmente cambió (rowCount = 0 significa
        // que se guardó el mismo valor que ya había).
        if ($stmtPerfil->rowCount() > 0) {
            registrarAccion(
                $conexion,
                "Modificación de su propio perfil (empleado id $idEmpleado): $campo '"
                . ($valorAnteriorPerfil === false || $valorAnteriorPerfil === null ? '(vacío)' : $valorAnteriorPerfil)
                . "' → '$valor'"
            );
        }

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

        // --- Motivo de baja opcional (solo para servicios/citas) ---
        // Si el empleado escribió un motivo, después de la baja se le avisa
        // por mail al cliente. Sin motivo no se envía nada: la baja se hace
        // igual, en silencio (por ejemplo, para liberar espacio).
        $motivoBaja = '';
        $datosCorreo = null;

        if ($tabla === 'servicio') {

            $motivoBaja = trim((string) ($_POST['motivo'] ?? ''));

            if (mb_strlen($motivoBaja, 'UTF-8') > 500) {
                $motivoBaja = mb_substr($motivoBaja, 0, 500, 'UTF-8');
            }

            if ($motivoBaja !== '') {

                // Se leen los datos ANTES de dar de baja, mientras el
                // servicio todavía figura como activo.
                // tipoServicio ahora es un id de tipo_servicio: se hace JOIN
                // para obtener el nombre legible (alias tipoServicio, así el
                // resto del código no cambia) y solo se avisa por mail si
                // el servicio es una cita (esCita = 1).
                $sqlDatosCorreo = "SELECT ts.servicio AS tipoServicio, s.fechaServicio, s.horaServicio,
                                          c.nombreCliente, c.apellidoCliente, c.correoCliente
                                   FROM servicio s
                                   INNER JOIN tipo_servicio ts ON s.tipoServicio = ts.id AND ts.esCita = 1
                                   LEFT JOIN cliente c ON s.idCliente = c.idCliente
                                   WHERE s.idServicio = ?
                                   AND s.activo = TRUE";

                $stmtDatosCorreo = $conexion->prepare($sqlDatosCorreo);
                $stmtDatosCorreo->execute([$idRegistro]);

                $datosCorreo = $stmtDatosCorreo->fetch() ?: null;
            }
        }

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

        // La baja ya está hecha: se registra ANTES de intentar el mail, para
        // que quede anotada aunque algo falle después.
        registrarAccion(
            $conexion,
            "Baja en $tabla (id $idRegistro)"
            . ($motivoBaja !== '' ? ". Motivo ingresado para avisar al cliente: $motivoBaja" : '')
        );

        $respuestaBaja = ['exito' => true];

        // El mail se manda DESPUÉS de confirmada la baja, y un fallo del
        // mail nunca la deshace: solo se le avisa al empleado que el
        // cliente no fue notificado.
        if ($motivoBaja !== '') {

            $respuestaBaja['correoEnviado'] = false;

            if ($datosCorreo === null) {

                $respuestaBaja['errorCorreo'] = 'No se encontraron los datos del cliente de este servicio.';

            } elseif (trim((string) ($datosCorreo['correoCliente'] ?? '')) === '') {

                $respuestaBaja['errorCorreo'] = 'El cliente no tiene un correo registrado.';

            } elseif (!is_file(__DIR__ . '/correo.php')) {

                $respuestaBaja['errorCorreo'] = 'Falta el archivo correo.php en !PHPchecks.';

            } else {

                try {

                    require_once __DIR__ . '/correo.php';

                    $resultadoCorreo = enviarCorreoBajaCita(
                        (string) $datosCorreo['correoCliente'],
                        trim($datosCorreo['nombreCliente'] . ' ' . $datosCorreo['apellidoCliente']),
                        ucfirst((string) $datosCorreo['tipoServicio']),
                        (string) $datosCorreo['fechaServicio'],
                        (string) $datosCorreo['horaServicio'],
                        $motivoBaja
                    );

                    $respuestaBaja['correoEnviado'] = (bool) $resultadoCorreo['exito'];

                    if (!$resultadoCorreo['exito']) {
                        $respuestaBaja['errorCorreo'] = $resultadoCorreo['error'];
                    }

                } catch (Throwable $e) {

                    $respuestaBaja['errorCorreo'] = 'Error inesperado al enviar el mail.';
                }
            }
        }

        echo json_encode($respuestaBaja);

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

    // Valor anterior, solo para dejarlo en la bitácora ($tabla, $campo e
    // $idColumna ya fueron validados contra la lista de tablas editables).
    $stmtAnterior = $conexion->prepare("SELECT `$campo` FROM `$tabla` WHERE `$idColumna` = ? AND activo = TRUE");
    $stmtAnterior->execute([$idRegistro]);
    $valorAnterior = $stmtAnterior->fetchColumn();

    $sqlUpdate = "UPDATE `$tabla`
                  SET `$campo` = ?
                  WHERE `$idColumna` = ?
                  AND activo = TRUE";

    $stmtUpdate = $conexion->prepare($sqlUpdate);
    $stmtUpdate->execute([$valor, $idRegistro]);

    // Solo se registra si algo cambió de verdad: rowCount = 0 significa que
    // el registro no existe/está dado de baja, o que se guardó el mismo valor.
    if ($stmtUpdate->rowCount() > 0) {
        registrarAccion(
            $conexion,
            "Modificación en $tabla (id $idRegistro): $campo '"
            . ($valorAnterior === false || $valorAnterior === null ? '(vacío)' : $valorAnterior)
            . "' → '$valor'"
        );
    }

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