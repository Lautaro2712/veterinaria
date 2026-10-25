<?php

// Carga e interpreta
require_once __DIR__ . "/../!PHPchecks/conexion.php";
require_once __DIR__ . "/../!PHPchecks/auth.php";

// https://imgflip.com/memegenerator/597934764/Complex-Building-Supported-by-Small-Part profvaor no preguntar
requireExactRole([ROL_CLIENTE]);

// Zona horaria de Uruguay
date_default_timezone_set('America/Montevideo');

// Variables para controlar la visualización de mensajes en el HTML
$exito = false;
$error = null;

$tipoServicio  = 0;   // id de tipo_servicio (numérico)
$fechaServicio = '';
$horaServicio  = '';

// Horarios disponibles, en el mismo orden en que aparecen en el <select>
// más abajo. El bloqueo de horarios por tipo de servicio se calcula
// sobre la POSICIÓN dentro de esta lista (no sobre el reloj), tal cual
// se pidió: "la siguiente hora disponible EN LA TABLA DE HORAS".
$horariosDisponibles = [
    '09:00', '09:30', '10:00', '10:30',
    '11:00', '11:30', '12:00', '12:30',
    '15:00', '15:30', '16:00', '16:30',
    '17:00', '17:30', '18:00', '18:30',
    '19:00', '19:30'
];

// Tipos de servicio que funcionan como CITA, leídos de la tabla tipo_servicio
// (esCita = 1). Clave = id. 'nombre' es para mostrar en pantalla y 'extra' =
// franjas de 30 min ADICIONALES que bloquea el servicio (total = 1 + extra).
// Agregar o cambiar un tipo de cita en esa tabla alcanza: no hay que tocar
// este archivo.
$stmtTipos = $conexion->query("SELECT id, servicio, franjasExtra FROM tipo_servicio WHERE esCita = 1");
$tiposServicio = [];
foreach ($stmtTipos->fetchAll() as $t) {
    $tiposServicio[(int) $t['id']] = [
        'nombre' => ucfirst((string) $t['servicio']),
        'extra'  => (int) $t['franjasExtra'],
    ];
}

// "1,2,3,4": para los IN (...). Son enteros que salen de la base (no del
// usuario), así que es seguro interpolarlos. Si no hubiera ningún tipo de
// cita cargado, '0' evita un "IN ()" vacío, que es un error de SQL.
$idsCitas = $tiposServicio ? implode(',', array_keys($tiposServicio)) : '0';

// Obtenemos el cliente autenticado desde la sesión
$idCliente = idClienteActual();

if ($idCliente === null) {
    header("Location: /Subida/login.php");
    exit();
}

// Un cliente solo puede tener una cita activa a la vez.

function obtenerCitaActivaDeCliente(PDO $conexion, int $idCliente): ?array
{
    // tipoServicio ahora es un id de tipo_servicio. "Tiene una cita activa"
    // significa tener un servicio activo cuyo tipo sea una cita (esCita = 1);
    // servicios como hotelería o venta no cuentan. El JOIN trae además el
    // nombre legible del tipo para mostrarlo en el aviso.
    $sql = "SELECT s.idServicio, s.tipoServicio, ts.servicio AS nombreTipoServicio,
                   s.fechaServicio, s.horaServicio
            FROM servicio s
            INNER JOIN tipo_servicio ts ON s.tipoServicio = ts.id
            WHERE s.idCliente = ?
            AND ts.esCita = 1
            AND s.activo = TRUE
            ORDER BY s.fechaServicio ASC, s.horaServicio ASC
            LIMIT 1";

    $stmt = $conexion->prepare($sql);
    $stmt->execute([$idCliente]);

    $fila = $stmt->fetch();

    return $fila ?: null;
}

$citaActiva = obtenerCitaActivaDeCliente($conexion, $idCliente);

// Evalúa la variable global $_SERVER para verificar si el usuario envió el formulario por POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Defensa del lado del servidor: aunque el formulario esté oculto
    // para un cliente con cita activa, se vuelve a comprobar acá por si
    // alguien arma el POST a mano (inspección del navegador, curl, etc.),
    // sin pasar por la página. Nunca hay que confiar únicamente en que
    // la UI esconda el formulario.
    if ($citaActiva !== null) {

        $error = "Ya tenés una cita activa. No podés solicitar otra hasta que un empleado la dé de baja.";

    } else {

    // 1. RECEPCIÓN Y VALIDACIÓN DE DATOS
    $tipoServicio  = (int) ($_POST['tipoServicio'] ?? 0);
    $fechaServicio = $_POST['fechaServicio'] ?? '';
    $horaServicio  = $_POST['horaServicio'] ?? '';

    // Comprobamos que los campos requeridos no estén vacíos
    if ($tipoServicio === 0 || empty($fechaServicio) || empty($horaServicio)) {

        $error = "Por favor, complete todos los campos solicitados.";

    } elseif (!array_key_exists($tipoServicio, $tiposServicio)) {

        // El <select> del formulario ya restringe las opciones, pero esto
        // es la comprobación real: nunca hay que confiar en que el
        // navegador mande solo valores válidos.
        $error = "El tipo de servicio seleccionado no es válido.";

    } else {

        // Comprobar que la fecha y hora de la cita no hayan pasado
        $zonaHoraria = new DateTimeZone('America/Montevideo');

        $fechaHoraActual = new DateTime(
            'now',
            $zonaHoraria
        );

        $fechaHoraCita = DateTime::createFromFormat(
            'Y-m-d H:i',
            $fechaServicio . ' ' . $horaServicio,
            $zonaHoraria
        );

        if ($fechaHoraCita === false) {

            $error = "La fecha u hora ingresada no es válida.";

        } elseif ($fechaHoraCita <= $fechaHoraActual) {

            $error = "No es posible solicitar una cita en una fecha u hora que ya pasó.";

        } elseif ((int) $fechaHoraCita->format('Y') !== (int) $fechaHoraActual->format('Y')) {

            // Solo se permiten citas dentro del año en curso
            $error = "Solo se pueden solicitar citas dentro del año actual.";

        } elseif ((int) $fechaHoraCita->format('w') === 0) {

            // format('w') devuelve 0 para domingo
            $error = "No se pueden solicitar citas los días domingo.";

        } else {

            // -----------------------------------------------------------------
            // 2. VERIFICACIÓN DE DISPONIBILIDAD E INSERCIÓN
            // -----------------------------------------------------------------

            // Ubicar en qué posición de la tabla de horarios cae la hora
            // elegida, para saber cuáles son "las siguientes N franjas".
            $indiceInicio = array_search($horaServicio, $horariosDisponibles, true);

            if ($indiceInicio === false) {

                $error = "El horario seleccionado no es válido.";

            } else {

                $totalFranjas = 1 + $tiposServicio[$tipoServicio]['extra'];

                $franjasNecesarias = array_slice($horariosDisponibles, $indiceInicio, $totalFranjas);

                if (count($franjasNecesarias) < $totalFranjas) {

                    // No quedan suficientes franjas después de la elegida
                    // (por ejemplo, una Cirugía pedida a las 19:00, que no
                    // tiene 6 franjas por delante ese mismo día).
                    $error = "No hay suficientes horarios disponibles después de las "
                        . htmlspecialchars($horaServicio, ENT_QUOTES, 'UTF-8')
                        . " para un servicio de tipo \"" . htmlspecialchars($tiposServicio[$tipoServicio]['nombre'], ENT_QUOTES, 'UTF-8')
                        . "\". Elija un horario más temprano.";

                } else {

                    try {

                        // Trae, de cada servicio ya agendado ESE día, su
                        // tipo y su hora de INICIO. Ojo: la hora de inicio
                        // sola no alcanza para saber qué está realmente
                        // ocupado — una Cirugía a las 17:00 solo guarda
                        // "17:00" en la base, pero sigue tapando 17:30,
                        // 18:00, etc. Por eso, para cada fila existente, se
                        // reconstruye su propio rango completo de franjas
                        // (con la misma lógica que se usó para el servicio
                        // nuevo más arriba) antes de comparar.
                        $sqlOcupadas = "SELECT tipoServicio, horaServicio
                                        FROM servicio
                                        WHERE fechaServicio = ?
                                        AND tipoServicio IN ($idsCitas)
                                        AND activo = TRUE";

                        $stmtOcupadas = $conexion->prepare($sqlOcupadas);
                        $stmtOcupadas->execute([$fechaServicio]);

                        $horasOcupadas = [];

                        foreach ($stmtOcupadas->fetchAll() as $filaExistente) {

                            $horaExistente = substr($filaExistente['horaServicio'], 0, 5);
                            $indiceExistente = array_search($horaExistente, $horariosDisponibles, true);

                            if ($indiceExistente === false) {
                                // Dato fuera de la tabla de horarios (no
                                // debería pasar); se ignora por las dudas
                                // en vez de romper la validación.
                                continue;
                            }

                            $franjasExistente = 1 + ($tiposServicio[(int) $filaExistente['tipoServicio']]['extra'] ?? 0);

                            $horasOcupadas = array_merge(
                                $horasOcupadas,
                                array_slice($horariosDisponibles, $indiceExistente, $franjasExistente)
                            );
                        }

                        $franjasEnConflicto = array_intersect($franjasNecesarias, $horasOcupadas);

                        if (!empty($franjasEnConflicto)) {

                            $error = "Lo sentimos, el horario elegido se superpone con otro servicio ya agendado ("
                                . htmlspecialchars(implode(', ', $franjasEnConflicto), ENT_QUOTES, 'UTF-8')
                                . "). Elija otro horario.";

                        } else {

                            // ---------------------------------------------------------
                            // INSERTAR CITA ASOCIADA AL CLIENTE AUTENTICADO
                            // ---------------------------------------------------------

                            $sqlInsert = "INSERT INTO servicio
                                          (
                                              tipoServicio,
                                              fechaServicio,
                                              horaServicio,
                                              idCliente
                                          )
                                          VALUES (?, ?, ?, ?)";

                            $stmtInsert = $conexion->prepare($sqlInsert);

                            $stmtInsert->execute([
                                $tipoServicio,
                                $fechaServicio,
                                $horaServicio,
                                $idCliente
                            ]);

                            $exito = true;
                        }

                    } catch (PDOException $e) {

                        $error = "Error al guardar la cita: " . $e->getMessage();
                    }
                }
            }
        }
    }

    } // fin del else de "no tiene cita activa"
}

// Si la cita se guardó con éxito recién ahora, ya no hay que seguir
// mostrando el aviso de "cita activa" con los datos viejos: se vuelve a
// consultar para que, si el cliente refresca la página, vea reflejado
// el nuevo estado (el formulario debe desaparecer y mostrar el aviso
// con la cita recién creada).
if ($exito) {
    $citaActiva = obtenerCitaActivaDeCliente($conexion, $idCliente);
}

// Límites para el <input type="date">: no antes de hoy, no después del
// 31 de diciembre del año en curso (el bloqueo de domingos no se puede
// expresar con atributos HTML nativos, así que se hace con JS más abajo,
// y se vuelve a validar en el servidor arriba por las dudas).
$fechaMinima = date('Y-m-d');
$fechaMaxima = date('Y-12-31');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="stylesheet" href="/../!CSS/basecss.css">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css"rel="stylesheet"integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB"crossorigin="anonymous">

    <script src="https://unpkg.com/scrollreveal"></script>

    <link rel="stylesheet"href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">

    <link rel="icon" type="image/png" href="/!Imagenes/favicon.png">

    <title>Veterinaria del Mar</title>
</head>

<body>

    <!-- DETAIL: HIDE OVERFLOW-X -->
    <div style="overflow-x: hidden;">

        <!-- TOP BAR -->
        <?php include '../topbar.php'; ?>

        <!-- ★★★★★★★★★★★★★★★★★★★★★★★★★ -->

        <!-- REGISTER FORM -->
        <div class="container-fluid margins">

            <main class="form-signin m-5">

                <?php if (!empty($exito)): ?>

                    <div class="alert alert-success alert-dismissible fade show" role="alert">

                        ✔ ¡<?php echo htmlspecialchars($tiposServicio[$tipoServicio]['nombre'] ?? 'Cita', ENT_QUOTES, 'UTF-8'); ?> solicitada con éxito para la fecha

                        <strong>
                            <?php
                            echo date_format(
                                date_create($fechaServicio),
                                'd/m/Y'
                            );
                            ?>
                        </strong>

                        a las

                        <strong>
                            <?php
                            echo htmlspecialchars(
                                $horaServicio,
                                ENT_QUOTES,
                                'UTF-8'
                            );
                            ?>
                        </strong>!

                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close">
                        </button>

                    </div>

                <?php endif; ?>


                <?php if (!empty($error)): ?>

                    <div class="alert alert-danger alert-dismissible fade show" role="alert">

                        ✘
                        <?php
                        echo htmlspecialchars(
                            $error,
                            ENT_QUOTES,
                            'UTF-8'
                        );
                        ?>

                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close">
                        </button>

                    </div>

                <?php endif; ?>

                <br>

                <?php if ($citaActiva === null): ?>

                    <form action="" method="POST" id="formSolicitarCita">

                        <h1 class="h3 mb-3 fw-normal text-secondary">
                            Solicitar cita
                        </h1>

                        <div class="form-floating">

                            <input type="date" class="form-control" id="fechaServicio" name="fechaServicio" min="<?php echo $fechaMinima; ?>" max="<?php echo $fechaMaxima; ?>" required>

                            <label for="fechaServicio">
                                Fecha de la cita
                            </label>

                        </div>

                        <br>

                        <div class="form-floating">

                            <select name="tipoServicio" id="tipoServicio" class="form-select" required>

                                <option value="" disabled selected>Seleccione un tipo de servicio</option>
                                <?php foreach ($tiposServicio as $id => $t): ?>
                                    <option value="<?php echo $id; ?>"><?php echo htmlspecialchars($t['nombre'], ENT_QUOTES, 'UTF-8'); ?></option>
                                <?php endforeach; ?>

                            </select>

                            <label for="tipoServicio">
                                Tipo de servicio
                            </label>

                        </div>

                        <br>

                        <div class="form-floating">

                            <select name="horaServicio" id="horaServicio" class="form-select" required>

                                <option value="09:00">9:00</option>
                                <option value="09:30">9:30</option>
                                <option value="10:00">10:00</option>
                                <option value="10:30">10:30</option>

                                <option value="11:00">11:00</option>
                                <option value="11:30">11:30</option>
                                <option value="12:00">12:00</option>
                                <option value="12:30">12:30</option>

                                <option value="15:00">15:00</option>
                                <option value="15:30">15:30</option>
                                <option value="16:00">16:00</option>
                                <option value="16:30">16:30</option>

                                <option value="17:00">17:00</option>
                                <option value="17:30">17:30</option>
                                <option value="18:00">18:00</option>
                                <option value="18:30">18:30</option>

                                <option value="19:00">19:00</option>
                                <option value="19:30">19:30</option>

                            </select>

                            <label for="horaServicio">
                                Horario
                            </label>

                        </div>

                        <br>

                        <button class="btn bg-info-subtle me-2 border border-info-subtle rounded-5 w-100" type="submit">
                            Solicitar
                        </button>

                        <br><br>

                    </form>

                <?php else: ?>

                    <h1 class="h3 mb-3 fw-normal text-secondary">
                        Solicitar cita
                    </h1>

                    <div class="alert alert-info" role="alert">

                        Ya tenés un servicio agendado (<?php echo htmlspecialchars(ucfirst((string) $citaActiva["nombreTipoServicio"]), ENT_QUOTES, 'UTF-8'); ?>) para el

                        <strong>
                            <?php
                            echo date_format(
                                date_create($citaActiva["fechaServicio"]),
                                'd/m/Y'
                            );
                            ?>
                        </strong>

                        a las

                        <strong>
                            <?php
                            echo htmlspecialchars(
                                substr($citaActiva["horaServicio"], 0, 5),
                                ENT_QUOTES,
                                'UTF-8'
                            );
                            ?>
                        </strong>.

                        <br><br>

                        No podés solicitar una nueva cita hasta que sea atendida.

                    </div>

                    <p class="text-secondary">¿Ya atendieron tu cita y seguís sin poder agendar? <a href="https://api.whatsapp.com/send?phone=598094191775" class="nohighlight">Contactanos</a>.</p>
                    <br>

                <?php endif; ?>

            </main>

        </div>

        <br>

        <!-- END: REGISTER FORM -->

        <!-- ★★★★★★★★★★★★★★★★★★★★★★★★★ -->

        <!-- BOTTOM BAR -->
    <footer class="p-3">
        <div class="container"> 
            <div> 
                <ul class="nav col-12 col-lg-auto me-lg-auto mb-2 justify-content-center mb-md-0"> 
                    <li><a href="/Informacion/terminoscondiciones.pdf" class="nav-link px-2 text-secondary ahover">Terminos y Condiciones</a></li>
                    <li><a href="/Informacion/politicaprivacidad.pdf" class="nav-link px-2 text-secondary ahover">Politica de Privacidad</a></li> 
                </ul>
            </div> 
        </div>
    </footer><br>

    <!-- ★★★★★★★★★★★★★★★★★★★★★★★★★ -->

    <!-- JS MANAGERS -->
    <script
        src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI"
        crossorigin="anonymous">
    </script>

    <!-- Bloqueo de domingos en el selector de fecha: el <input type="date">
         nativo no permite deshabilitar días de la semana puntuales (solo
         soporta min/max), así que se revisa acá el día elegido y, si es
         domingo, se avisa y se limpia el campo. La validación real e
         infalible sigue estando del lado del servidor, más arriba. -->
    <script>
        (function () {
            const inputFecha = document.getElementById('fechaServicio');
            const formulario = document.getElementById('formSolicitarCita');

            if (!inputFecha) {
                return;
            }

            function esDomingo(valorFecha) {
                if (!valorFecha) {
                    return false;
                }
                // new Date('YYYY-MM-DD') interpreta la fecha en UTC; se arma
                // la fecha con los componentes por separado para evitar
                // corrimientos de día por zona horaria.
                const partes = valorFecha.split('-').map(Number);
                const fecha = new Date(partes[0], partes[1] - 1, partes[2]);
                return fecha.getDay() === 0;
            }

            inputFecha.addEventListener('input', function () {
                if (esDomingo(inputFecha.value)) {
                    alert('No se pueden solicitar citas los días domingo. Por favor, elija otro día.');
                    inputFecha.value = '';
                }
            });

            if (formulario) {
                formulario.addEventListener('submit', function (e) {
                    if (esDomingo(inputFecha.value)) {
                        e.preventDefault();
                        alert('No se pueden solicitar citas los días domingo. Por favor, elija otro día.');
                        inputFecha.value = '';
                    }
                });
            }
        })();
    </script>

</body>
</html>