<?php
/**
 * auth.php — Módulo central de control de acceso por roles.
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// -----------------------------------------------------------------------
// NIVELES DE ROL
// -----------------------------------------------------------------------

const ROL_INVITADO       = 0;
const ROL_CLIENTE        = 1;
const ROL_EMPLEADO       = 2;
const ROL_ADMINISTRADOR  = 3;


/**
 * Devuelve el nivel de rol actual.
 */
function rolActual(): int
{
    return $_SESSION['nivelRol'] ?? ROL_INVITADO;
}


/**
 * Devuelve true si hay alguien logueado.
 */
function estaLogueado(): bool
{
    return rolActual() > ROL_INVITADO;
}


/**
 * Devuelve el ID del cliente actual.
 */
function idClienteActual(): ?int
{
    return isset($_SESSION['idCliente'])
        ? (int) $_SESSION['idCliente']
        : null;
}


/**
 * Devuelve el ID del empleado actual.
 */
function idEmpleadoActual(): ?int
{
    return isset($_SESSION['idEmpleado'])
        ? (int) $_SESSION['idEmpleado']
        : null;
}


/**
 * Exige un rol mínimo.
 */
function requireRole(int $rolMinimo): void
{
    if (rolActual() < $rolMinimo) {

        if (!estaLogueado()) {
            header("Location: /Subida/login.php?redirigido=1");
        } else {
            header("Location: /index.php?error=sin_permiso");
        }

        exit();
    }
}


/**
 * Exige un rol exacto dentro de los permitidos.
 */
function requireExactRole(array $rolesPermitidos): void
{
    if (!in_array(rolActual(), $rolesPermitidos, true)) {

        if (!estaLogueado()) {
            header("Location: /Subida/login.php?redirigido=1");
        } else {
            header("Location: /index.php?error=sin_permiso");
        }

        exit();
    }
}

?>