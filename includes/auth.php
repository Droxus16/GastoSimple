<?php
// Iniciar sesión si no está iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Validar sesión iniciada
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit();
}

// -------------------------
// ⚡ Cierre por inactividad
// -------------------------

$tiempo_limite = 15 * 60; // 15 minutos de inactividad

if (isset($_SESSION['ultimo_acceso'])) {
    $inactivo = time() - $_SESSION['ultimo_acceso'];

    if ($inactivo > $tiempo_limite) {
        // Destruir sesión y cookies recordar sesión
        session_unset();
        session_destroy();

        // Opcional: eliminar cookie rememberme
        if (isset($_COOKIE['rememberme'])) {
            setcookie('rememberme', '', time() - 3600, "/");
        }

        header("Location: login.php?mensaje=Sesión expirada");
        exit();
    }
}

// Refrescar tiempo de acceso
$_SESSION['ultimo_acceso'] = time();

// -------------------------
// Funciones de rol (opcional)
// -------------------------

function esAdmin() {
    return isset($_SESSION['rol']) && $_SESSION['rol'] === 'admin';
}

function esEstandar() {
    return isset($_SESSION['rol']) && $_SESSION['rol'] === 'estandar';
}
