<?php
session_start();
require_once __DIR__ . '/conexion.php';


if (!isset($_SESSION['id_usuario'])) {
    header("Location: login.html");
    exit();
}

$id_usuario = $_SESSION['id_usuario'];
$idioma = $_POST['idioma'] ?? 'es';
$moneda = $_POST['moneda'] ?? 'USD';

$conexion = Conexion::conectar();

$stmt = $conexion->prepare("UPDATE usuarios SET idioma_preferido = ?, moneda_preferida = ? WHERE id = ?");
$stmt->execute([$idioma, $moneda, $id_usuario]);

$_SESSION['idioma'] = $idioma;
$_SESSION['moneda'] = $moneda;

header("Location: configuracion_usuario.html");
exit();
