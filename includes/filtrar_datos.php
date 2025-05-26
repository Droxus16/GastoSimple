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

// Definir la fecha de inicio según el periodo seleccionado
switch ($periodo) {
    case 'dia':
        $fechaInicio = date('Y-m-d');
        break;
    case 'semana':
        $fechaInicio = date('Y-m-d', strtotime('monday this week'));
        break;
    case 'mes':
        $fechaInicio = date('Y-m-01');
        break;
    case 'anio':
        $fechaInicio = date('Y-01-01');
        break;
    default:
        $fechaInicio = date('Y-m-01');
        break;
}

try {
    $conn = db::conectar();

    // Sumar ingresos del usuario desde $fechaInicio hasta hoy
    $sqlIngreso = "SELECT COALESCE(SUM(monto), 0) FROM ingresos WHERE usuario_id = ? AND fecha >= ?";
    $stmtIngreso = $conn->prepare($sqlIngreso);
    $stmtIngreso->execute([$idUsuario, $fechaInicio]);
    $totalIngresos = (float) $stmtIngreso->fetchColumn();

    // Sumar gastos del usuario desde $fechaInicio hasta hoy
    $sqlGasto = "SELECT COALESCE(SUM(monto), 0) FROM gastos WHERE usuario_id = ? AND fecha >= ?";
    $stmtGasto = $conn->prepare($sqlGasto);
    $stmtGasto->execute([$idUsuario, $fechaInicio]);
    $totalGastos = (float) $stmtGasto->fetchColumn();

    // Calcular ahorro
    $ahorro = $totalIngresos - $totalGastos;

    echo json_encode([
        'ingresos' => $totalIngresos,
        'gastos' => $totalGastos,
        'ahorro' => $ahorro
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error en la base de datos: ' . $e->getMessage()]);
}
