<?php
session_start();
require_once 'db.php';
header('Content-Type: application/json');
if (!isset($_SESSION['usuario_id'])) {
  http_response_code(403);
  echo json_encode(['error' => 'No autorizado']);
  exit;
}
$idUsuario = $_SESSION['usuario_id'];
// === Obtener entradas ===
$nombre = trim($_POST['nombre'] ?? '');
$correo = trim($_POST['correo'] ?? '');
$nuevaClave = trim($_POST['nueva_clave'] ?? '');
$confirmarClave = trim($_POST['confirmar_clave'] ?? '');
$respuestaActual = trim($_POST['respuesta_actual'] ?? '');
$nuevaRespuesta = trim($_POST['respuesta_secreta'] ?? '');
$ingresoMinimo = floatval($_POST['ingreso_minimo'] ?? 0);
$saldoMinimo = floatval($_POST['saldo_minimo'] ?? 0);
// === Validaciones ===
if (!$nombre || !$correo) {
  echo json_encode(['error' => 'El nombre y correo son obligatorios.']);
  exit;
}
if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
  echo json_encode(['error' => 'Formato de correo inválido.']);
  exit;
}
if ($ingresoMinimo < 0 || $saldoMinimo < 0) {
  echo json_encode(['error' => 'Los valores de preferencias no pueden ser negativos.']);
  exit;
}
try {
  $conn = db::conectar();
  // === Obtener respuesta secreta guardada ===
  $stmt = $conn->prepare("SELECT respuesta_secreta FROM usuarios WHERE id = ?");
  $stmt->execute([$idUsuario]);
  $respuestaGuardada = $stmt->fetchColumn();
  // === Si va a cambiar clave validar ===
  if ($nuevaClave) {
    if ($nuevaClave !== $confirmarClave) {
      echo json_encode(['error' => 'Las contraseñas no coinciden.']);
      exit;
    }
    if (!$respuestaActual || strtolower(trim($respuestaActual)) !== strtolower(trim($respuestaGuardada))) {
      echo json_encode(['error' => 'La respuesta secreta es incorrecta.']);
      exit;
    }
    $claveHash = password_hash($nuevaClave, PASSWORD_DEFAULT);
    $sql = "UPDATE usuarios 
            SET nombre = ?, correo = ?, clave = ?, ingreso_minimo = ?, saldo_minimo = ?, updated_at = NOW()";
    $params = [$nombre, $correo, $claveHash, $ingresoMinimo, $saldoMinimo];
    if ($nuevaRespuesta) {
      $sql .= ", respuesta_secreta = ?";
      $params[] = $nuevaRespuesta;
    }
    $sql .= " WHERE id = ?";
    $params[] = $idUsuario;
    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
  } else {
    // === 5. Si no cambia clave ===
    $sql = "UPDATE usuarios 
            SET nombre = ?, correo = ?, ingreso_minimo = ?, saldo_minimo = ?, updated_at = NOW()";
    $params = [$nombre, $correo, $ingresoMinimo, $saldoMinimo];

    if ($nuevaRespuesta) {
      $sql .= ", respuesta_secreta = ?";
      $params[] = $nuevaRespuesta;
    }
    $sql .= " WHERE id = ?";
    $params[] = $idUsuario;

    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
  }
  echo json_encode(['success' => true]);
} catch (PDOException $e) {
  http_response_code(500);
  echo json_encode(['error' => 'Error en el servidor: ' . $e->getMessage()]);
}