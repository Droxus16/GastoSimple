<?php
session_start();
require_once 'db.php';
header('Content-Type: application/json');
// Verificar autenticación
if (!isset($_SESSION['usuario_id'])) {
    http_response_code(403);
    echo json_encode(['error' => 'No autorizado']);
    exit;
}
$idUsuario = $_SESSION['usuario_id'];
// Leer datos JSON del cuerpo
$data = json_decode(file_get_contents("php://input"), true);
if (json_last_error() !== JSON_ERROR_NONE) {
    http_response_code(400);
    echo json_encode(['error' => 'JSON mal formado']);
    exit;
}
$id = $data['id'] ?? null;
if (!$id) {
    http_response_code(400);
    echo json_encode(['error' => 'ID requerido']);
    exit;
}
try {
    $conn = db::conectar();
    // Buscar si es ingreso
    $sqlIngreso = "SELECT id FROM ingresos WHERE id = :id AND usuario_id = :usuario_id";
    $stmtIngreso = $conn->prepare($sqlIngreso);
    $stmtIngreso->execute([':id' => $id, ':usuario_id' => $idUsuario]);
    if ($stmtIngreso->rowCount() > 0) {
        $conn->prepare("DELETE FROM ingresos WHERE id = :id AND usuario_id = :usuario_id")
             ->execute([':id' => $id, ':usuario_id' => $idUsuario]);
        echo json_encode(['success' => true, 'tipo' => 'ingreso']);
        exit;
    }
    // Buscar si es gasto
    $sqlGasto = "SELECT id FROM gastos WHERE id = :id AND usuario_id = :usuario_id";
    $stmtGasto = $conn->prepare($sqlGasto);
    $stmtGasto->execute([':id' => $id, ':usuario_id' => $idUsuario]);
    if ($stmtGasto->rowCount() > 0) {
        $conn->prepare("DELETE FROM gastos WHERE id = :id AND usuario_id = :usuario_id")
             ->execute([':id' => $id, ':usuario_id' => $idUsuario]);
        echo json_encode(['success' => true, 'tipo' => 'gasto']);
        exit;
    }
    // Si no se encontró en ninguna tabla
    http_response_code(404);
    echo json_encode(['error' => 'Transacción no encontrada']);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}