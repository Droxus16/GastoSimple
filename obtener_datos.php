<?php
session_start();
require_once 'includes/db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['usuario_id'])) {
  echo json_encode(['error' => 'No autorizado']);
  exit;
}

$idUsuario = $_SESSION['usuario_id'];
$filtro = $_GET['periodo'] ?? 'mes';

$conn = db::conectar();

function obtenerCondicionFecha($filtro) {
  switch ($filtro) {
    case 'dia':
      return 'DATE(fecha) = CURDATE()';
    case 'semana':
      return 'YEARWEEK(fecha, 1) = YEARWEEK(CURDATE(), 1)';
    case 'mes':
      return 'MONTH(fecha) = MONTH(CURDATE()) AND YEAR(fecha) = YEAR(CURDATE())';
    case 'anio':
      return 'YEAR(fecha) = YEAR(CURDATE())';
    default:
      return '1'; // sin filtro
  }
}

$condicionFecha = obtenerCondicionFecha($filtro);

// INGRESOS
$stmt = $conn->prepare("SELECT IFNULL(SUM(cantidad), 0) FROM ingresos WHERE usuario_id = ? AND $condicionFecha");
$stmt->execute([$idUsuario]);
$ingresos = $stmt->fetchColumn();

// GASTOS
$stmt = $conn->prepare("SELECT IFNULL(SUM(cantidad), 0) FROM gastos WHERE usuario_id = ? AND $condicionFecha");
$stmt->execute([$idUsuario]);
$gastos = $stmt->fetchColumn();

// AHORRO = INGRESOS - GASTOS
$ahorro = $ingresos - $gastos;

echo json_encode([
  'ingresos' => round($ingresos, 2),
  'gastos' => round($gastos, 2),
  'ahorro' => round($ahorro, 2)
]);
