<?php
session_start();
require_once 'db.php';
header('Content-Type: application/json');
// Validar sesión
if (!isset($_SESSION['usuario_id'])) {
  http_response_code(403);
  echo json_encode(['error' => 'No autorizado. Debes iniciar sesión.']);
  exit;
}
$idUsuario = $_SESSION['usuario_id'];
//Sanitizar entradas
$tipo = ucfirst(strtolower(trim($_POST['tipo'] ?? '')));
$asunto = trim($_POST['asunto'] ?? '');
$descripcion = trim($_POST['descripcion'] ?? '');
// 3) Validar campos
$tiposPermitidos = ['Pregunta', 'Queja', 'Reclamo'];

if (!$tipo || !$asunto || !$descripcion) {
  echo json_encode(['error' => 'Todos los campos son obligatorios.']);
  exit;
}
if (!in_array($tipo, $tiposPermitidos)) {
  echo json_encode(['error' => 'Tipo de PQR inválido.']);
  exit;
}
if (strlen($asunto) < 5 || strlen($descripcion) < 10) {
  echo json_encode(['error' => 'Asunto o descripción demasiado cortos.']);
  exit;
}
//Mapear tipo a ENUM
$mapTipo = [
  'Pregunta' => 'P',
  'Queja'    => 'Q',
  'Reclamo'  => 'R'
];
$tipoEnum = $mapTipo[$tipo];
//Insertar en la base de datos
try {
  $conn = db::conectar();
  $stmt = $conn->prepare("
    INSERT INTO pqrs (usuario_id, tipo, asunto, descripcion, fecha_creacion, estado)
    VALUES (?, ?, ?, ?, NOW(), 'pendiente')
  ");
  $stmt->execute([$idUsuario, $tipoEnum, $asunto, $descripcion]);
  echo json_encode(['success' => true]);
} catch (PDOException $e) {
  http_response_code(500);
  echo json_encode(['error' => 'Error en el servidor: ' . $e->getMessage()]);
}