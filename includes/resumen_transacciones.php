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
$periodo = $_POST['periodo'] ?? 'mes';
// Validar periodo
$periodosValidos = ['dia', 'semana', 'mes', 'anio'];
if (!in_array($periodo, $periodosValidos)) {
    $periodo = 'mes';
}
// Calcular fecha de inicio
switch ($periodo) {
    case 'dia':
        $fechaInicio = date('Y-m-d');
        break;
    case 'semana':
        $fechaInicio = date('Y-m-d', strtotime('-7 days'));
        break;
    case 'mes':
        $fechaInicio = date('Y-m-01');
        break;
    case 'anio':
        $fechaInicio = date('Y-01-01');
        break;
}
try {
    $conn = db::conectar();
    $sql = "SELECT tipo, SUM(monto) as total 
            FROM transacciones 
            WHERE id_usuario = :usuario_id AND fecha >= :fecha_inicio
            GROUP BY tipo";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':usuario_id', $idUsuario);
    $stmt->bindParam(':fecha_inicio', $fechaInicio);
    $stmt->execute();
    $ingresos = 0;
    $gastos = 0;
    while ($fila = $stmt->fetch(PDO::FETCH_ASSOC)) {
        if ($fila['tipo'] === 'ingreso') {
            $ingresos = $fila['total'];
        } elseif ($fila['tipo'] === 'gasto') {
            $gastos = $fila['total'];
        }
    }
    $ahorro = $ingresos - $gastos;
    echo json_encode([
        'ingresos' => round($ingresos, 2),
        'gastos' => round($gastos, 2),
        'ahorro' => round($ahorro, 2)
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}