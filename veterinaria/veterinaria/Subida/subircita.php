<?php

// Carga e interpreta
require_once __DIR__ . "/../!PHPchecks/conexion.php";
require_once __DIR__ . "/../!PHPchecks/auth.php";

requireRole(ROL_EMPLEADO);

// Zona horaria de Uruguay
date_default_timezone_set('America/Montevideo');

// Variables para controlar la visualización de mensajes en el HTML
$exito = false;
$error = null;

$tipoServicio  = "Cita";
$fechaServicio = '';
$horaServicio  = '';

$sql = "SELECT idCliente, nombreCliente, apellidoCliente, correoCliente, telefonoCliente
        FROM cliente
        WHERE activo = TRUE
        ORDER BY apellidoCliente ASC, nombreCliente ASC";
$stmt = $conexion->prepare($sql);
$stmt->execute();
$clientes = $stmt->fetchAll();

// Evalúa la variable global $_SERVER para verificar si el usuario envió el formulario por POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // 1. RECEPCIÓN Y VALIDACIÓN DE DATOS
    $fechaServicio = $_POST['fechaServicio'] ?? '';
    $horaServicio  = $_POST['horaServicio'] ?? '';
    $idCliente  = $_POST['idCliente'] ?? "";

    // Comprobamos que los campos requeridos no estén vacíos
    if (empty($fechaServicio) || empty($horaServicio) || empty($idCliente)) {

        $error = "Por favor, complete todos los campos.";

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

            // Comprobar si ya existe una cita ACTIVA en la misma fecha y hora
            $sqlCheck = "SELECT idServicio
                         FROM servicio
                         WHERE fechaServicio = ?
                         AND horaServicio = ?
                         AND tipoServicio = 'Cita'
                         AND activo = TRUE";

            try {

                $stmtCheck = $conexion->prepare($sqlCheck);

                $stmtCheck->execute([
                    $fechaServicio,
                    $horaServicio
                ]);

                // Si la consulta devuelve una fila, el horario está ocupado
                if ($stmtCheck->fetchColumn() !== false) {

                    $error = "Lo sentimos, ya existe una cita reservada para la fecha y hora seleccionadas.";

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

                        ✔ ¡Cita subida al sistema exitosamente para la fecha

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

                    <form action="" method="POST" id="formSolicitarCita">

                        <h1 class="h3 mb-3 fw-normal text-secondary">
                            Subir cita
                        </h1>

                        <div class="form-floating">

                            <input type="date" class="form-control" id="fechaServicio" name="fechaServicio" min="<?php echo $fechaMinima; ?>" max="<?php echo $fechaMaxima; ?>" required>

                            <label for="fechaServicio">
                                Fecha de la cita
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

                        <div class="form-floating"> 
                            <select name="idCliente" id="idCliente" class="form-select" required>
                            <option value="" disabled selected>Seleccione un cliente</option>
                            <?php foreach ($clientes as $cliente): ?>
                                <option value="<?php echo htmlspecialchars($cliente['idCliente']); ?>">
                                    <?php echo htmlspecialchars($cliente['nombreCliente']); ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                            <label for="idCliente">Cliente de la cita</label> 
                        </div><br>
                        
                        <button class="btn bg-info-subtle me-2 border border-info-subtle rounded-5 w-100" type="submit">
                            Solicitar
                        </button>

                        <br><br>

                    </form>
                
                    <div class="d-flex justify-content-center">
                    
                    <a href="/Subida/manejarcitas.php" class="text-secondary nohighlight"><button class="btn bg-light-subtle border border-secondary-subtle rounded-5">Ir al menu de manejo</button></a><br><br>
        
                    </div>

            </main>

        </div>

        <br>

        <!-- END: REGISTER FORM -->

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