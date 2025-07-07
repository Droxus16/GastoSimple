<?php
session_start();
require_once 'db.php';
header('Content-Type: application/json');
//Seguridad: validar sesión
if (!isset($_SESSION['usuario_id'])) {
    http_response_code(403);
    echo json_encode(['error' => 'No autorizado']);
    exit;
}
$idUsuario = $_SESSION['usuario_id'];
$periodo   = $_POST['periodo'] ?? 'mes';
// 2) Definir rango de fechas según periodo
switch (strtolower($periodo)) {
    case 'día':
    case 'dia':
        $fechaInicio = date('Y-m-d');
        $groupBy     = 'fecha';
        break;
    case 'semana':
        $fechaInicio = date('Y-m-d', strtotime('monday this week'));
        $groupBy     = 'fecha';
        break;
    case 'mes':
        $fechaInicio = date('Y-m-01');
        $groupBy     = 'fecha';
        break;
    case 'año':
    case 'anio':
        $fechaInicio = date('Y-01-01');
        $groupBy     = "DATE_FORMAT(fecha, '%Y-%m')"; // agrupar por mes
        break;
    default:
        $fechaInicio = date('Y-m-01');
        $groupBy     = 'fecha';
        break;
}
try {
    $conn = db::conectar();
    // 3) Ingresos agrupados
    $sqlIngreso = "
        SELECT 
            " . ($groupBy === 'fecha' ? "fecha" : "$groupBy AS fecha") . ",
            COALESCE(SUM(monto), 0) AS total 
        FROM ingresos
        WHERE usuario_id = ? AND fecha >= ?
        GROUP BY fecha
        ORDER BY fecha ASC
    ";
    $stmtIngreso = $conn->prepare($sqlIngreso);
    $stmtIngreso->execute([$idUsuario, $fechaInicio]);
    $ingresosRaw = $stmtIngreso->fetchAll(PDO::FETCH_ASSOC);
    // 4) Gastos agrupados
    $sqlGasto = "
        SELECT 
            " . ($groupBy === 'fecha' ? "fecha" : "$groupBy AS fecha") . ",
            COALESCE(SUM(monto), 0) AS total 
        FROM gastos
        WHERE usuario_id = ? AND fecha >= ?
        GROUP BY fecha
        ORDER BY fecha ASC
    ";
    $stmtGasto = $conn->prepare($sqlGasto);
    $stmtGasto->execute([$idUsuario, $fechaInicio]);
    $gastosRaw = $stmtGasto->fetchAll(PDO::FETCH_ASSOC);
    // 5) Aportes filtrados en el periodo
    $sqlAportes = "
        SELECT COALESCE(SUM(a.monto), 0) AS total
        FROM aportes_ahorro a
        JOIN metas_ahorro m ON a.meta_id = m.id
        WHERE m.usuario_id = ? AND a.fecha >= ?
    ";
    $stmtAportes = $conn->prepare($sqlAportes);
    $stmtAportes->execute([$idUsuario, $fechaInicio]);
    $aporteTotal = $stmtAportes->fetchColumn();
    // 6) Construir rango de fechas
    $fechaFin = date('Y-m-d');
    $fechas   = [];
    if ($groupBy !== 'fecha') {
        $inicioDate = new DateTime($fechaInicio);
        $finDate    = new DateTime($fechaFin);
        while ($inicioDate <= $finDate) {
            $fechas[] = $inicioDate->format('Y-m');
            $inicioDate->modify('+1 month');
        }
    } else {
        $inicioDate = new DateTime($fechaInicio);
        $finDate    = new DateTime($fechaFin);
        while ($inicioDate <= $finDate) {
            $fechas[] = $inicioDate->format('Y-m-d');
            $inicioDate->modify('+1 day');
        }
    }
    //Mapear ingresos y gastos por fecha
    $ingresos = array_fill_keys($fechas, 0);
    foreach ($ingresosRaw as $item) {
        $ingresos[$item['fecha']] = (float)$item['total'];
    }
    $gastos = array_fill_keys($fechas, 0);
    foreach ($gastosRaw as $item) {
        $gastos[$item['fecha']] = (float)$item['total'];
    }
    //Calcular totales coherentes
    $totalIngresos = array_sum($ingresos);
    $totalGastos   = array_sum($gastos);
    $ahorro        = $totalIngresos - $totalGastos - $aporteTotal;
    //Respuesta JSON
    echo json_encode([
        'fechas'   => $fechas,
        'ingresos' => array_values($ingresos),
        'gastos'   => array_values($gastos),
        'aportes'  => $aporteTotal,
        'ahorro'   => $ahorro
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error en la base de datos: ' . $e->getMessage()]);
}