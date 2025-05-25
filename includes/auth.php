<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit();
}

// Función opcional para verificar si el usuario es administrador
function esAdmin() {
    return isset($_SESSION['rol']) && $_SESSION['rol'] === 'admin';
}

// Función opcional para verificar si el usuario es estándar
function esEstandar() {
    return isset($_SESSION['rol']) && $_SESSION['rol'] === 'estandar';
}
