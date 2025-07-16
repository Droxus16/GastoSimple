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
        $where   = "DATE(fecha) = CURDATE()";
        $groupBy = "fecha";
        break;
    case 'semana':
        $where   = "YEARWEEK(fecha, 1) = YEARWEEK(CURDATE(), 1)";
        $groupBy = "fecha";
        break;
    case 'mes':
        $where   = "MONTH(fecha) = MONTH(CURDATE()) AND YEAR(fecha) = YEAR(CURDATE())";
        $groupBy = "fecha";
        break;
    case 'año':
    case 'anio':
        $where   = "YEAR(fecha) = YEAR(CURDATE())";
        $groupBy = "DATE_FORMAT(fecha, '%Y-%m')";
        break;
    default:
        $where   = "MONTH(fecha) = MONTH(CURDATE()) AND YEAR(fecha) = YEAR(CURDATE())";
        $groupBy = "fecha";
        break;
}

try {
    $conn = db::conectar();

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

    // Si no hay datos, responde vacío para que el JS muestre el mensaje
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

    // Construir rango de fechas solo si hay registros
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
