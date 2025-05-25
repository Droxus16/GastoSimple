<?php
session_start();

// Verificamos si hay una sesión activa
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit();
}

require_once 'includes/db.php';

// Conexión a la base de datos
$conn = db::conectar();
$idUsuario = $_SESSION['usuario_id'];

// Obtener el nombre del usuario
$query = "SELECT nombre FROM usuarios WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bindParam(1, $idUsuario, PDO::PARAM_INT);
$stmt->execute();
$nombreUsuario = $stmt->fetchColumn();

if (!$nombreUsuario) {
    // Si no se encuentra el usuario, cerramos la sesión y redirigimos
    session_destroy();
    header("Location: login.php");
    exit();
}

// Obtener resumen financiero del usuario
$ingresos = $conn->query("SELECT SUM(monto) FROM ingresos WHERE usuario_id = $idUsuario")->fetchColumn() ?? 0;
$gastos = $conn->query("SELECT SUM(monto) FROM gastos WHERE usuario_id = $idUsuario")->fetchColumn() ?? 0;
$ahorro = $ingresos - $gastos;
?>

<?php include 'includes/header.php'; ?>
<link rel="stylesheet" href="assets/css/estilos.css">
<style>
/* (los estilos son los mismos que te pasé antes) */
</style>

<div class="dashboard-container">
    <div class="bienvenida">
        <h2>👋 Bienvenido, <?= htmlspecialchars($nombreUsuario) ?> </h2>
        <p>Este es tu panel de control personal</p>
    </div>

    <div class="resumen">
        <div class="tarjeta-resumen">
            <h3>Total de Ingresos</h3>
            <p>$<?= number_format($ingresos, 2) ?></p>
        </div>
        <div class="tarjeta-resumen">
            <h3>Total de Gastos</h3>
            <p>$<?= number_format($gastos, 2) ?></p>
        </div>
        <div class="tarjeta-resumen">
            <h3>Ahorro Disponible</h3>
            <p>$<?= number_format($ahorro, 2) ?></p>
        </div>
    </div>

    <div class="acciones">
        <a href="ingresos.php">Registrar Ingreso</a>
        <a href="gastos.php">Registrar Gasto</a>
        <a href="metas.php">Mis Metas</a>
        <a href="reportes.php">Reportes</a>
        <a href="configuracion.php">Configuración</a>
        <a href="logout.php">Cerrar Sesión</a>
    </div>

    <div class="graficas">
        <div class="filtros-tiempo">
            <label for="filtroTiempo">Ver por:</label>
            <select id="filtroTiempo">
                <option value="dia">Día</option>
                <option value="semana">Semana</option>
                <option value="mes">Mes</option>
                <option value="anio">Año</option>
            </select>
        </div>
        <canvas id="graficaResumen" height="100"></canvas>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener("DOMContentLoaded", () => {
    const ctx = document.getElementById("graficaResumen").getContext("2d");
    const grafica = new Chart(ctx, {
        type: "bar",
        data: {
            labels: ["Ingreso", "Gasto", "Ahorro"],
            datasets: [{
                label: "Resumen",
                data: [<?= $ingresos ?>, <?= $gastos ?>, <?= $ahorro ?>],
                backgroundColor: ["#00FF7F", "#FF4C4C", "#1E90FF"],
                borderRadius: 10
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    display: false
                }
            }
        }
    });

    document.getElementById("filtroTiempo").addEventListener("change", (e) => {
        alert("Filtro cambiado a: " + e.target.value);
        // Aquí podrías hacer una petición AJAX para actualizar la gráfica
    });
});
</script>

<?php include 'includes/footer.php'; ?>
