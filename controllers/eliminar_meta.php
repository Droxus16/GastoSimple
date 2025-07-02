<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/auth.php';

$usuario_id = $_SESSION['usuario_id'];
$conn = db::conectar();

// Leer meta_id de JSON o POST
$input = json_decode(file_get_contents('php://input'), true);

if (isset($input['meta_id'])) {
  $meta_id = intval($input['meta_id']);
} elseif (isset($_POST['meta_id'])) {
  $meta_id = intval($_POST['meta_id']);
} else {
  // Si es JSON, devolver error
  if ($input !== null) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => 'Meta inválida.']);
    exit;
  }
  // Si es formulario, redirigir con error
  $_SESSION['error'] = 'Meta inválida.';
  header('Location: ../metas.php');
  exit;
}

// Verificar si la meta pertenece al usuario
$stmt = $conn->prepare("SELECT id FROM metas_ahorro WHERE id = ? AND usuario_id = ?");
$stmt->execute([$meta_id, $usuario_id]);

if (!$stmt->fetch()) {
  if ($input !== null) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => 'Meta no encontrada.']);
    exit;
  }
  $_SESSION['error'] = 'Meta no encontrada.';
  header('Location: ../metas.php');
  exit;
}

// Eliminar aportes y meta
$conn->prepare("DELETE FROM aportes_ahorro WHERE meta_id = ?")->execute([$meta_id]);
$conn->prepare("DELETE FROM metas_ahorro WHERE id = ?")->execute([$meta_id]);

if ($input !== null) {
  header('Content-Type: application/json');
  echo json_encode(['success' => true]);
} else {
  $_SESSION['success'] = 'Meta eliminada correctamente.';
  header('Location: ../metas.php');
}
exit;
