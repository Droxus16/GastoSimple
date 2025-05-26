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

// Definir la fecha de inicio y la agrupación según el periodo seleccionado
switch ($periodo) {
    case 'día':
    case 'dia':
        $fechaInicio = date('Y-m-d');
        $groupBy = 'fecha';
        break;
    case 'semana':
        $fechaInicio = date('Y-m-d', strtotime('monday this week'));
        $groupBy = 'fecha';
        break;
    case 'mes':
        $fechaInicio = date('Y-m-01');
        $groupBy = 'fecha';
        break;
    case 'año':
    case 'anio':
        $fechaInicio = date('Y-01-01');
        $groupBy = "DATE_FORMAT(fecha, '%Y-%m')"; // Agrupar por mes en el año
        break;
    default:
        $fechaInicio = date('Y-m-01');
        $groupBy = 'fecha';
        break;
}

try {
    $conn = db::conectar();

    // Consultar ingresos agrupados por fecha o mes
    $sqlIngreso = "SELECT 
                      " . ($groupBy === 'fecha' ? "fecha" : "$groupBy AS fecha") . ", 
                      COALESCE(SUM(monto), 0) AS total 
                   FROM ingresos 
                   WHERE usuario_id = ? AND fecha >= ? 
                   GROUP BY fecha 
                   ORDER BY fecha ASC";
    $stmtIngreso = $conn->prepare($sqlIngreso);
    $stmtIngreso->execute([$idUsuario, $fechaInicio]);
    $ingresosRaw = $stmtIngreso->fetchAll(PDO::FETCH_ASSOC);

    // Consultar gastos agrupados por fecha o mes
    $sqlGasto = "SELECT 
                    " . ($groupBy === 'fecha' ? "fecha" : "$groupBy AS fecha") . ", 
                    COALESCE(SUM(monto), 0) AS total 
                 FROM gastos 
                 WHERE usuario_id = ? AND fecha >= ? 
                 GROUP BY fecha 
                 ORDER BY fecha ASC";
    $stmtGasto = $conn->prepare($sqlGasto);
    $stmtGasto->execute([$idUsuario, $fechaInicio]);
    $gastosRaw = $stmtGasto->fetchAll(PDO::FETCH_ASSOC);

    // Construir un array con todas las fechas entre $fechaInicio y hoy o fin de periodo
    $fechaFin = date('Y-m-d');
    if ($periodo === 'anio' || $periodo === 'año') {
        // Para año agrupado por meses, fechas serán meses en formato "YYYY-MM"
        $inicioDate = new DateTime($fechaInicio);
        $finDate = new DateTime($fechaFin);
        $fechas = [];
        while ($inicioDate <= $finDate) {
            $fechas[] = $inicioDate->format('Y-m');
            $inicioDate->modify('+1 month');
        }
    } else {
        // Para día, semana o mes fechas diarias
        $inicioDate = new DateTime($fechaInicio);
        $finDate = new DateTime($fechaFin);
        $fechas = [];
        while ($inicioDate <= $finDate) {
            $fechas[] = $inicioDate->format('Y-m-d');
            $inicioDate->modify('+1 day');
        }
    }

    // Crear arrays para ingresos y gastos con valores por fecha, 0 si no hay datos
    $ingresos = array_fill_keys($fechas, 0);
    foreach ($ingresosRaw as $item) {
        $ingresos[$item['fecha']] = (float)$item['total'];
    }

    $gastos = array_fill_keys($fechas, 0);
    foreach ($gastosRaw as $item) {
        $gastos[$item['fecha']] = (float)$item['total'];
    }

    // Calcular totales
    $totalIngresos = array_sum($ingresos);
    $totalGastos = array_sum($gastos);
    $ahorro = $totalIngresos - $totalGastos;

    echo json_encode([
        'fechas' => $fechas,
        'ingresos' => array_values($ingresos),
        'gastos' => array_values($gastos),
        'ahorro' => $ahorro
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error en la base de datos: ' . $e->getMessage()]);
}
