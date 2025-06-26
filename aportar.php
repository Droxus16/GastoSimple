<?php
session_start();
require_once 'includes/db.php';
require_once 'includes/auth.php';

if (!isset($_POST['meta_id'], $_POST['monto'])) {
    header('Location: metas.php');
    exit;
}

$usuario_id = $_SESSION['usuario_id'];
$meta_id = intval($_POST['meta_id']);
$monto = floatval($_POST['monto']);
$fecha = date('Y-m-d');
$descripcion = "Aporte manual desde el sistema";

// 🔄 Validación básica: permitir negativos, pero no nulos
if ($monto == 0) {
    $_SESSION['error'] = "El monto no puede ser cero.";
    header('Location: metas.php');
    exit;
}

// Verificar que la meta pertenezca al usuario
$conn = db::conectar();
$stmt = $conn->prepare("SELECT usuario_id FROM metas_ahorro WHERE id = ?");
$stmt->execute([$meta_id]);
$meta = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$meta || $meta['usuario_id'] != $usuario_id) {
    $_SESSION['error'] = "Meta no válida o no pertenece al usuario.";
    header('Location: metas.php');
    exit;
}

// Registrar el aporte (positivo o negativo)
$stmt = $conn->prepare("INSERT INTO aportes_ahorro (meta_id, monto, fecha, descripcion, created_at, updated_at) 
VALUES (?, ?, ?, ?, NOW(), NOW())");

if ($stmt->execute([$meta_id, $monto, $fecha, $descripcion])) {
    $_SESSION['success'] = "Aporte registrado correctamente.";
} else {
    $_SESSION['error'] = "Ocurrió un error al guardar el aporte.";
}

header('Location: metas.php');
exit;
