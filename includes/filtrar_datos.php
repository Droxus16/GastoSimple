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
$periodo   = $_POST['periodo'] ?? 'mes';
switch (strtolower($periodo)) {
    case 'día':
    case 'dia':
        // Últimos 7 días
        $where   = "fecha >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)";
        $groupBy = "DATE(fecha)";
        break;

    case 'semana':
        // Últimas 6 semanas
        $where   = "fecha >= DATE_SUB(CURDATE(), INTERVAL 6 WEEK)";
        $groupBy = "YEARWEEK(fecha, 1)";
        break;

    case 'mes':
        // Últimos 12 meses
        $where   = "fecha >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)";
        $groupBy = "DATE_FORMAT(fecha, '%Y-%m')";
        break;

    case 'año':
    case 'anio':
        // Últimos 5 años
        $where   = "fecha >= DATE_SUB(CURDATE(), INTERVAL 5 YEAR)";
        $groupBy = "YEAR(fecha)";
        break;

    default:
        // Mes por defecto
        $where   = "fecha >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)";
        $groupBy = "DATE_FORMAT(fecha, '%Y-%m')";
        break;
}
try {
    $conn = DB::conectar();
    // Ingresos
    $sqlIngreso = "
        SELECT {$groupBy} AS fecha, SUM(monto) AS total
        FROM ingresos
        WHERE usuario_id = ? AND {$where}
        GROUP BY fecha
        ORDER BY fecha ASC
    ";
    $stmtIngreso = $conn->prepare($sqlIngreso);
    $stmtIngreso->execute([$idUsuario]);
    $ingresosRaw = $stmtIngreso->fetchAll(PDO::FETCH_ASSOC);
    // Gastos
    $sqlGasto = "
        SELECT {$groupBy} AS fecha, SUM(monto) AS total
        FROM gastos
        WHERE usuario_id = ? AND {$where}
        GROUP BY fecha
        ORDER BY fecha ASC
    ";
    $stmtGasto = $conn->prepare($sqlGasto);
    $stmtGasto->execute([$idUsuario]);
    $gastosRaw = $stmtGasto->fetchAll(PDO::FETCH_ASSOC);
    // Aportes
    $sqlAportes = "
        SELECT COALESCE(SUM(a.monto),0)
        FROM aportes_ahorro a
        JOIN metas_ahorro m ON a.meta_id = m.id
        WHERE m.usuario_id = ? AND {$where}
    ";
    $stmtAportes = $conn->prepare($sqlAportes);
    $stmtAportes->execute([$idUsuario]);
    $aporteTotal = $stmtAportes->fetchColumn();
    // Si no hay datos
    if (empty($ingresosRaw) && empty($gastosRaw)) {
        echo json_encode([
            'fechas'   => [],
            'ingresos' => [],
            'gastos'   => [],
            'aportes'  => 0,
            'ahorro'   => 0
        ]);
        exit;
    }
    // Construir rango de fechas
    $fechasUnicas = [];
    foreach ($ingresosRaw as $row) $fechasUnicas[$row['fecha']] = true;
    foreach ($gastosRaw as $row) $fechasUnicas[$row['fecha']] = true;

    $fechas = array_unique(array_keys($fechasUnicas));
    sort($fechas);
    // Mapear ingresos/gastos a fechas
    $ingresos = array_fill_keys($fechas, 0);
    foreach ($ingresosRaw as $row) $ingresos[$row['fecha']] = (float)$row['total'];

    $gastos = array_fill_keys($fechas, 0);
    foreach ($gastosRaw as $row) $gastos[$row['fecha']] = (float)$row['total'];

    $totalIngresos = array_sum($ingresos);
    $totalGastos   = array_sum($gastos);
    $ahorro        = $totalIngresos - $totalGastos - $aporteTotal;

    echo json_encode([
        'fechas'   => array_values($fechas),
        'ingresos' => array_values($ingresos),
        'gastos'   => array_values($gastos),
        'aportes'  => $aporteTotal,
        'ahorro'   => $ahorro
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error: '.$e->getMessage()]);
}