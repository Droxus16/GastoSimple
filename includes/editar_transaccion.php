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
$categoriaId = $_POST['categoria_id'] ?? null;

if (!$id || !is_numeric($monto) || $monto <= 0 || !in_array($tipo, ['ingreso', 'gasto'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Datos inválidos']);
    exit;
}

try {
    $conn = db::conectar();

    // Determinar tabla según tipo
    $tabla = $tipo === 'ingreso' ? 'ingresos' : 'gastos';

    $verificar = $conn->prepare("SELECT id FROM $tabla WHERE id = :id AND usuario_id = :usuario_id");
    $verificar->execute([':id' => $id, ':usuario_id' => $idUsuario]);

    if ($verificar->rowCount() === 0) {
        http_response_code(404);
        echo json_encode(['error' => 'Transacción no encontrada']);
        exit;
    }

    $sql = "UPDATE $tabla SET monto = :monto, fecha = :fecha, descripcion = :descripcion, categoria_id = :categoria_id WHERE id = :id AND usuario_id = :usuario_id";
    $stmt = $conn->prepare($sql);
    $stmt->execute([
        ':monto' => $monto,
        ':fecha' => $fecha,
        ':descripcion' => $descripcion,
        ':categoria_id' => $categoriaId,
        ':id' => $id,
        ':usuario_id' => $idUsuario
    ]);

    echo json_encode(['success' => true]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error en el servidor: ' . $e->getMessage()]);
}
