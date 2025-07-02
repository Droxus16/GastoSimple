<?php
session_start();
require_once 'db.php';
header('Content-Type: application/json');
// Comprobar si el usuario está autenticado
if (!isset($_SESSION['usuario_id'])) {
    http_response_code(403);
    echo json_encode(['error' => 'No autorizado']);
    exit;
}
$idUsuario = $_SESSION['usuario_id'];
// Leer los datos del cuerpo de la solicitud (JSON)
$data = json_decode(file_get_contents("php://input"), true);
// Verificar si el JSON es válido
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
    // Validar que la transacción pertenece al usuario
    $verificar = $conn->prepare("SELECT id, tipo FROM transacciones WHERE id = :id AND id_usuario = :usuario_id");
    $verificar->bindParam(':id', $id);
    $verificar->bindParam(':usuario_id', $idUsuario);
    $verificar->execute();

    if ($verificar->rowCount() === 0) {
        http_response_code(404);
        echo json_encode(['error' => 'Transacción no encontrada']);
        exit;
    }

    $transaccion = $verificar->fetch(PDO::FETCH_ASSOC);
    $tipo = $transaccion['tipo'];

    if ($tipo == 'ingreso') {
        $sql = "DELETE FROM ingresos WHERE id = :id AND usuario_id = :usuario_id";
    } else {
        $sql = "DELETE FROM gastos WHERE id = :id AND usuario_id = :usuario_id";
    }

    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':id', $id);
    $stmt->bindParam(':usuario_id', $idUsuario);
    $stmt->execute();

    echo json_encode(['success' => true]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}