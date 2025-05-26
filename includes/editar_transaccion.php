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
$tipo = $_POST['tipo'] ?? '';
$monto = $_POST['monto'] ?? 0;
$fecha = $_POST['fecha'] ?? date('Y-m-d');
$descripcion = $_POST['descripcion'] ?? '';
$lugar = $_POST['lugar'] ?? '';
$categoriaId = $_POST['categoria_id'] ?? null;

if (!$id || !is_numeric($monto) || $monto <= 0 || !in_array($tipo, ['ingreso', 'gasto'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Datos inválidos']);
    exit;
}

try {
    $conn = db::conectar();

    // Validar propiedad de la transacción
    $verificar = $conn->prepare("SELECT id FROM transacciones WHERE id = :id AND id_usuario = :usuario_id");
    $verificar->bindParam(':id', $id);
    $verificar->bindParam(':usuario_id', $idUsuario);
    $verificar->execute();

    if ($verificar->rowCount() === 0) {
        http_response_code(404);
        echo json_encode(['error' => 'Transacción no encontrada']);
        exit;
    }

    $sql = "UPDATE transacciones 
            SET tipo = :tipo, monto = :monto, fecha = :fecha, descripcion = :descripcion, lugar = :lugar, id_categoria = :categoria_id
            WHERE id = :id AND id_usuario = :usuario_id";

    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':tipo', $tipo);
    $stmt->bindParam(':monto', $monto);
    $stmt->bindParam(':fecha', $fecha);
    $stmt->bindParam(':descripcion', $descripcion);
    $stmt->bindParam(':lugar', $lugar);
    $stmt->bindParam(':categoria_id', $categoriaId);
    $stmt->bindParam(':id', $id);
    $stmt->bindParam(':usuario_id', $idUsuario);

    $stmt->execute();

    echo json_encode(['success' => true]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
