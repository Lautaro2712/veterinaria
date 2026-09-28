<?php

require_once __DIR__ . "/!PHPchecks/auth.php";
require_once __DIR__ . "/!PHPchecks/conexion.php";

requireRole(ROL_CLIENTE);

$rol = rolActual();

$tipoPerfil = "";
$idUsuario = null;
$datos = null;


/**
 * =====================================================================
 * CLIENTE
 * =====================================================================
 */
if ($rol === ROL_CLIENTE) {

    $tipoPerfil = "cliente";

    $idUsuario = idClienteActual();

    if ($idUsuario === null) {
        header("Location: /Subida/login.php");
        exit();
    }

    $sql = "SELECT
                idCliente,
                nombreCliente,
                apellidoCliente,
                correoCliente,
                telefonoCliente
            FROM cliente
            WHERE idCliente = ?
            AND activo = TRUE";

    $stmt = $conexion->prepare($sql);
    $stmt->execute([$idUsuario]);

    $datos = $stmt->fetch();
}


/**
 * =====================================================================
 * EMPLEADO / ADMINISTRADOR
 * =====================================================================
 */
elseif (
    $rol === ROL_EMPLEADO ||
    $rol === ROL_ADMINISTRADOR
) {

    $tipoPerfil = "empleado";

    $idUsuario = idEmpleadoActual();

    if ($idUsuario === null) {
        header("Location: /Subida/login.php");
        exit();
    }

    $sql = "SELECT
                idEmpleado,
                nombreEmpleado,
                apellidoEmpleado,
                correoEmpleado,
                telefonoEmpleado,
                rolEmpleado
            FROM empleado
            WHERE idEmpleado = ?
            AND activo = TRUE";

    $stmt = $conexion->prepare($sql);
    $stmt->execute([$idUsuario]);

    $datos = $stmt->fetch();
}


if (!$datos) {
    die("No se pudo encontrar la información del perfil.");
}


/**
 * =====================================================================
 * PREPARAR DATOS
 * =====================================================================
 */
if ($tipoPerfil === "cliente") {

    $nombre = $datos["nombreCliente"];
    $apellido = $datos["apellidoCliente"];
    $correo = $datos["correoCliente"];
    $telefono = $datos["telefonoCliente"];

} else {

    $nombre = $datos["nombreEmpleado"];
    $apellido = $datos["apellidoEmpleado"];
    $correo = $datos["correoEmpleado"];
    $telefono = $datos["telefonoEmpleado"];
}

?>

<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8">

    <meta http-equiv="X-UA-Compatible" content="IE=edge">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <link
        rel="stylesheet"
        href="/!CSS/basecss.css"
    >

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css"
        rel="stylesheet"
        integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB"
        crossorigin="anonymous"
    >

    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"
    >

    <link
        rel="icon"
        type="image/png"
        href="/!Imagenes/favicon.png"
    >

    <title>Veterinaria del Mar</title>

</head>


<body>

<div style="overflow-x: hidden;">


    <?php include __DIR__ . "/topbar.php"; ?>


    <div class="container mt-5 pt-5">

        <div class="h-100 p-5 bg-light border rounded-3">

            <h1 class="h3 mb-4 fw-normal text-secondary">
                Mi perfil
            </h1>


            <div
                id="mensajeExito"
                class="alert alert-success d-none"
                role="alert"
            >
                Datos actualizados correctamente.
            </div>


            <div
                id="mensajeError"
                class="alert alert-danger d-none"
                role="alert"
            ></div>


            <div class="mb-3">

                <label
                    for="nombre"
                    class="form-label"
                >
                    Nombre
                </label>

                <input
                    type="text"
                    class="form-control"
                    id="nombre"
                    value="<?php echo htmlspecialchars($nombre, ENT_QUOTES, 'UTF-8'); ?>"
                    maxlength="60"
                    required
                >

            </div>


            <div class="mb-3">

                <label
                    for="apellido"
                    class="form-label"
                >
                    Apellido
                </label>

                <input
                    type="text"
                    class="form-control"
                    id="apellido"
                    value="<?php echo htmlspecialchars($apellido, ENT_QUOTES, 'UTF-8'); ?>"
                    maxlength="60"
                    required
                >

            </div>


            <div class="mb-3">

                <label
                    for="correo"
                    class="form-label"
                >
                    Correo electrónico
                </label>

                <input
                    type="email"
                    class="form-control"
                    id="correo"
                    value="<?php echo htmlspecialchars($correo, ENT_QUOTES, 'UTF-8'); ?>"
                    maxlength="60"
                    required
                >

            </div>


            <div class="mb-3">

                <label
                    for="telefono"
                    class="form-label"
                >
                    Teléfono
                </label>

                <input
                    type="text"
                    class="form-control"
                    id="telefono"
                    value="<?php echo htmlspecialchars($telefono, ENT_QUOTES, 'UTF-8'); ?>"
                    required
                >

            </div>


            <?php if ($tipoPerfil === "empleado"): ?>

                <div class="mb-3">

                    <label class="form-label">
                        Rol
                    </label>

                    <input
                        type="text"
                        class="form-control"
                        value="<?php
                            echo ((int) $datos["rolEmpleado"] === 1)
                                ? "Administrador"
                                : "Empleado";
                        ?>"
                        disabled
                    >

                    <div class="form-text">
                        El rol no puede modificarse desde el perfil.
                    </div>

                </div>

            <?php endif; ?>


            <hr class="my-4">


            <h2 class="h5 mb-3 fw-normal text-secondary">
                Cambiar contraseña
            </h2>

            <p class="text-secondary" style="font-size: 0.9rem;">
                Dejá estos tres campos vacíos si no querés cambiar tu contraseña.
            </p>


            <div class="mb-3">

                <label
                    for="passwordActual"
                    class="form-label"
                >
                    Contraseña actual
                </label>

                <input
                    type="password"
                    class="form-control"
                    id="passwordActual"
                    autocomplete="current-password"
                >

            </div>


            <div class="mb-3">

                <label
                    for="passwordNueva"
                    class="form-label"
                >
                    Nueva contraseña
                </label>

                <input
                    type="password"
                    class="form-control"
                    id="passwordNueva"
                    autocomplete="new-password"
                >

            </div>


            <div class="mb-3">

                <label
                    for="passwordConfirmar"
                    class="form-label"
                >
                    Confirmar nueva contraseña
                </label>

                <input
                    type="password"
                    class="form-control"
                    id="passwordConfirmar"
                    autocomplete="new-password"
                >

            </div>


            <button
                type="button"
                id="btnGuardarPerfil"
                class="btn bg-info-subtle me-2 border border-info-subtle rounded-5"
            >
                Guardar cambios
            </button>


        </div>

    </div>

</div><br><br>


<script>

const tipoPerfil = <?php echo json_encode($tipoPerfil); ?>;


const valoresOriginales = {

    nombre:
        document.getElementById('nombre').value,

    apellido:
        document.getElementById('apellido').value,

    correo:
        document.getElementById('correo').value,

    telefono:
        document.getElementById('telefono').value

};


document
    .getElementById('btnGuardarPerfil')
    .addEventListener('click', async function () {


        const boton = this;

        const mensajeExito =
            document.getElementById('mensajeExito');

        const mensajeError =
            document.getElementById('mensajeError');


        mensajeExito.classList.add('d-none');
        mensajeError.classList.add('d-none');

        boton.disabled = true;


        let campos;


        if (tipoPerfil === 'cliente') {

            campos = [

                {
                    elemento: 'nombre',
                    campo: 'nombreCliente'
                },

                {
                    elemento: 'apellido',
                    campo: 'apellidoCliente'
                },

                {
                    elemento: 'correo',
                    campo: 'correoCliente'
                },

                {
                    elemento: 'telefono',
                    campo: 'telefonoCliente'
                }

            ];

        } else {

            campos = [

                {
                    elemento: 'nombre',
                    campo: 'nombreEmpleado'
                },

                {
                    elemento: 'apellido',
                    campo: 'apellidoEmpleado'
                },

                {
                    elemento: 'correo',
                    campo: 'correoEmpleado'
                },

                {
                    elemento: 'telefono',
                    campo: 'telefonoEmpleado'
                }

            ];
        }


        try {

            let huboCambios = false;


            for (const item of campos) {

                const input =
                    document.getElementById(item.elemento);

                const nuevoValor =
                    input.value.trim();


                if (nuevoValor === '') {

                    throw new Error(
                        'Todos los campos deben estar completos.'
                    );
                }


                if (
                    nuevoValor ===
                    valoresOriginales[item.elemento]
                ) {
                    continue;
                }


                huboCambios = true;


                const cuerpo =
                    new URLSearchParams();


                if (tipoPerfil === 'cliente') {

                    cuerpo.set(
                        'accion',
                        'actualizarPerfilCliente'
                    );

                } else {

                    cuerpo.set(
                        'accion',
                        'actualizarPerfilEmpleado'
                    );
                }


                cuerpo.set(
                    'campo',
                    item.campo
                );


                cuerpo.set(
                    'valor',
                    nuevoValor
                );


                const respuesta =
                    await fetch(
                        '/!PHPchecks/actualizarbm.php',
                        {
                            method: 'POST',

                            headers: {
                                'Content-Type':
                                    'application/x-www-form-urlencoded'
                            },

                            body:
                                cuerpo.toString()
                        }
                    );


                const data =
                    await respuesta.json();


                if (!data.exito) {

                    throw new Error(
                        data.error ||
                        'No se pudo actualizar el perfil.'
                    );
                }


                valoresOriginales[item.elemento] =
                    nuevoValor;
            }


            // -----------------------------------------------------------
            // CAMBIO DE CONTRASEÑA (opcional)
            // -----------------------------------------------------------
            // Los tres campos son opcionales: si el usuario no quiere
            // cambiar su contraseña, los deja vacíos y no pasa nada acá.
            // Pero si completa alguno, tienen que estar los tres.

            const inputPasswordActual =
                document.getElementById('passwordActual');

            const inputPasswordNueva =
                document.getElementById('passwordNueva');

            const inputPasswordConfirmar =
                document.getElementById('passwordConfirmar');

            const passwordActual =
                inputPasswordActual.value;

            const passwordNueva =
                inputPasswordNueva.value;

            const passwordConfirmar =
                inputPasswordConfirmar.value;

            const algunCampoPassword =
                passwordActual !== '' ||
                passwordNueva !== '' ||
                passwordConfirmar !== '';

            if (algunCampoPassword) {

                if (
                    passwordActual === '' ||
                    passwordNueva === '' ||
                    passwordConfirmar === ''
                ) {

                    throw new Error(
                        'Para cambiar la contraseña, completá los tres campos de contraseña.'
                    );
                }


                if (passwordNueva !== passwordConfirmar) {

                    throw new Error(
                        'La nueva contraseña y su confirmación no coinciden.'
                    );
                }


                if (passwordNueva.length < 8) {

                    throw new Error(
                        'La nueva contraseña debe tener al menos 8 caracteres.'
                    );
                }


                if (passwordNueva === passwordActual) {

                    throw new Error(
                        'La nueva contraseña debe ser distinta de la actual.'
                    );
                }


                huboCambios = true;


                const cuerpoPassword =
                    new URLSearchParams();


                cuerpoPassword.set(
                    'accion',
                    'cambiarPassword'
                );


                cuerpoPassword.set(
                    'passwordActual',
                    passwordActual
                );


                cuerpoPassword.set(
                    'passwordNueva',
                    passwordNueva
                );


                cuerpoPassword.set(
                    'passwordConfirmar',
                    passwordConfirmar
                );


                const respuestaPassword =
                    await fetch(
                        '/!PHPchecks/actualizarbm.php',
                        {
                            method: 'POST',

                            headers: {
                                'Content-Type':
                                    'application/x-www-form-urlencoded'
                            },

                            body:
                                cuerpoPassword.toString()
                        }
                    );


                const dataPassword =
                    await respuestaPassword.json();


                if (!dataPassword.exito) {

                    throw new Error(
                        dataPassword.error ||
                        'No se pudo actualizar la contraseña.'
                    );
                }


                // Se limpian los campos de contraseña después de
                // guardarla con éxito; a diferencia de los demás campos,
                // nunca se vuelve a mostrar la contraseña en un input.
                inputPasswordActual.value = '';
                inputPasswordNueva.value = '';
                inputPasswordConfirmar.value = '';
            }


            if (huboCambios) {

                mensajeExito.textContent =
                    '✓ Datos actualizados correctamente.';

            } else {

                mensajeExito.textContent =
                    'No se realizaron cambios.';
            }


            mensajeExito.classList.remove('d-none');


        } catch (error) {


            mensajeError.textContent =
                '✘ ' + error.message;


            mensajeError.classList.remove('d-none');


        } finally {


            boton.disabled = false;

        }

    });

</script>


<script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"
    integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI"
    crossorigin="anonymous"
></script>
<script src="/../!JS/showpw.js"></script>


</body>

</html>