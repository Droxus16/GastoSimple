<?php
session_start();
require_once 'conexion.php';

// Validar si es admin
if (!isset($_SESSION['id_usuario']) || $_SESSION['rol'] != 2) {
    header("Location: menu.php");
    exit();
}

try {
    $conexion = Conexion::conectar();

    // Consultar ingresos por usuario
    $sqlIngresos = "
        SELECT u.correo, SUM(i.monto) AS total_ingresos
        FROM ingresos i
        JOIN usuarios u ON i.id_usuario = u.id
        GROUP BY u.id
    ";
    $stmtIngresos = $conexion->query($sqlIngresos);
    $ingresos = $stmtIngresos->fetchAll(PDO::FETCH_ASSOC);

    // Consultar gastos por usuario
    $sqlGastos = "
        SELECT u.correo, SUM(g.monto) AS total_gastos
        FROM gastos g
        JOIN usuarios u ON g.id_usuario = u.id
        GROUP BY u.id
    ";
    $stmtGastos = $conexion->query($sqlGastos);
    $gastos = $stmtGastos->fetchAll(PDO::FETCH_ASSOC);

    // Organizar datos
    $usuarios = [];
    $ingresosData = [];
    $gastosData = [];

    foreach ($ingresos as $i) {
        $usuarios[] = $i['correo'];
        $ingresosData[$i['correo']] = $i['total_ingresos'];
    }

    foreach ($gastos as $g) {
        $gastosData[$g['correo']] = $g['total_gastos'];
    }

    $ingresosFinal = [];
    $gastosFinal = [];

    foreach ($usuarios as $correo) {
        $ingresosFinal[] = $ingresosData[$correo] ?? 0;
        $gastosFinal[] = $gastosData[$correo] ?? 0;
    }

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte Global</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="estilos.css">
</head>
<body>
    <h2>📊 Reporte Global de Ingresos vs Gastos</h2>
    <canvas id="graficaGlobal" width="400" height="200"></canvas>

    <script>
        const ctx = document.getElementById('graficaGlobal').getContext('2d');
        const grafica = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: <?php echo json_encode($usuarios); ?>,
                datasets: [
                    {
                        label: 'Ingresos',
                        data: <?php echo json_encode($ingresosFinal); ?>,
                        backgroundColor: 'rgba(54, 162, 235, 0.6)'
                    },
                    {
                        label: 'Gastos',
                        data: <?php echo json_encode($gastosFinal); ?>,
                        backgroundColor: 'rgba(255, 99, 132, 0.6)'
                    }
                ]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    </script>

    <a href="admin_dashboard.php">🔙 Volver al panel</a>
</body>
</html>
