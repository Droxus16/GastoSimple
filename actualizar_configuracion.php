<?php
session_start();
require 'conexion.php'; // Incluye tu archivo de conexión a la BD

if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit();
}

$usuario_id = $_SESSION['usuario_id'];

// Recoge los valores del formulario
$nombre = trim($_POST['nombre'] ?? '');
$correo = trim($_POST['correo'] ?? '');
$contrasena = $_POST['contrasena'] ?? '';
$moneda = $_POST['moneda'] ?? '';
$idioma = $_POST['idioma'] ?? '';

// Crea arreglo dinámico para la consulta SQL
$campos = [];
$valores = [];

if ($nombre !== '') {
    $campos[] = "nombre = ?";
    $valores[] = $nombre;
}

if ($correo !== '') {
    $campos[] = "correo = ?";
    $valores[] = $correo;
}

if ($contrasena !== '') {
    $contrasena_hash = password_hash($contrasena, PASSWORD_DEFAULT);
    $campos[] = "contrasena = ?";
    $valores[] = $contrasena_hash;
}

if ($moneda !== '') {
    $campos[] = "moneda = ?";
    $valores[] = $moneda;
}

if ($idioma !== '') {
    $campos[] = "idioma = ?";
    $valores[] = $idioma;
}

if (!empty($campos)) {
    $sql = "UPDATE usuarios SET " . implode(', ', $campos) . " WHERE id = ?";
    $valores[] = $usuario_id;

    $stmt = $conexion->prepare($sql);
    $stmt->execute($valores);
}

// Redirige al perfil
header("Location: perfil.php");
exit();
