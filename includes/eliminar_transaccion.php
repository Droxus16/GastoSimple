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
$id = $_POST['id'] ?? null;

if (!$id) {
    http_response_code(400);
    echo json_encode(['error' => 'ID requerido']);
    exit;
}

try {
    $conn = db::conectar();

    // Validar que la transacción pertenece al usuario
    $verificar = $conn->prepare("SELECT id FROM transacciones WHERE id = :id AND id_usuario = :usuario_id");
    $verificar->bindParam(':id', $id);
    $verificar->bindParam(':usuario_id', $idUsuario);
    $verificar->execute();

    if ($verificar->rowCount() === 0) {
        http_response_code(404);
        echo json_encode(['error' => 'Transacción no encontrada']);
        exit;
    }

    $sql = "DELETE FROM transacciones WHERE id = :id AND id_usuario = :usuario_id";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':id', $id);
    $stmt->bindParam(':usuario_id', $idUsuario);
    $stmt->execute();

    echo json_encode(['success' => true]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
