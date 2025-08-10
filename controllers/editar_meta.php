<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/auth.php';

if (!isset($_POST['meta_id'], $_POST['nombre'], $_POST['monto_objetivo'], $_POST['fecha_limite'])) {
  $_SESSION['error'] = "Datos incompletos.";
  header('Location: ../metas.php');
  exit;
}

$usuario_id = $_SESSION['usuario_id'];
$meta_id = intval($_POST['meta_id']);
$nombre = trim($_POST['nombre']);
$monto_objetivo = floatval($_POST['monto_objetivo']);
$fecha_limite = $_POST['fecha_limite'];

$conn = db::conectar();

// Verificar que la meta pertenezca al usuario
$stmt = $conn->prepare("SELECT id FROM metas_ahorro WHERE id = ? AND usuario_id = ?");
$stmt->execute([$meta_id, $usuario_id]);

if (!$stmt->fetch()) {
  $_SESSION['error'] = "Meta no encontrada.";
  header('Location: ../metas.php');
  exit;
}

// Actualizar
$stmt = $conn->prepare("UPDATE metas_ahorro SET nombre = ?, monto_objetivo = ?, fecha_limite = ?, updated_at = NOW() WHERE id = ?");
if ($stmt->execute([$nombre, $monto_objetivo, $fecha_limite, $meta_id])) {
  $_SESSION['success'] = "Meta actualizada correctamente.";
} else {
  $_SESSION['error'] = "Error al actualizar.";
}

header('Location: ../metas.php');
exit;
