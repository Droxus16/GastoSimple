<?php
session_start();
require_once 'includes/db.php';
require_once 'includes/auth.php';

$conn = db::conectar();
$idUsuario = $_SESSION['usuario_id'];

$query = "SELECT nombre FROM usuarios WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bindParam(1, $idUsuario);
$stmt->execute();
$nombreUsuario = $stmt->fetchColumn();
?>

<?php include 'includes/header.php'; ?>
<link rel="stylesheet" href="assets/css/estilos.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/gridstack@8.2.1/dist/gridstack.min.css">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/gridstack@8.2.1/dist/gridstack-all.js"></script>

<style>
  body {
    font-family: 'Inter', sans-serif;
    background: linear-gradient(-40deg, #0C1634, #1D2B64, #0C1634);
    background-size: 400% 400%;
    animation: backgroundAnim 15s ease infinite;
    color: white;
    padding: 20px;
    margin: 0;
  }

  @keyframes backgroundAnim {
    0% { background-position: 0% 50%; }
    50% { background-position: 100% 50%; }
    100% { background-position: 0% 50%; }
  }

  .dashboard-container {
    display: flex;
    height: 90vh;
    gap: 20px;
  }

  .sidebar {
    width: 200px;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
  }

  .sidebar .menu-top,
  .sidebar .menu-bottom {
    display: flex;
    flex-direction: column;
    gap: 15px;
  }

  .sidebar button {
    padding: 10px;
    border: none;
    border-radius: 10px;
    background-color: #00D4FF;
    color: #0C1634;
    cursor: pointer;
    font-weight: bold;
    transition: background 0.3s;
  }

  .sidebar button:hover {
    background-color: #00b8e6;
  }

  .main-content {
    flex: 1;
    background: rgba(255, 255, 255, 0.05);
    padding: 20px;
    border-radius: 20px;
    backdrop-filter: blur(10px);
    overflow: auto;
  }

  .grid-stack {
    width: 100%;
    min-height: 500px;
  }

  .grid-stack-item-content {
    background: rgba(255, 255, 255, 0.1);
    border-radius: 15px;
    padding: 20px;
    overflow: hidden;
  }

  .chart-filters {
    display: flex;
    justify-content: center;
    gap: 10px;
    margin-bottom: 10px;
  }

  .chart-filters button {
    padding: 5px 10px;
    border-radius: 8px;
    border: none;
    cursor: pointer;
    font-weight: bold;
    background-color: #ffffff33;
    color: white;
  }

  .chart-filters button:hover {
    background-color: #ffffff55;
  }
</style>

<div class="dashboard-container">
  <div class="sidebar">
    <div class="menu-top">
      <button onclick="mostrarSeccion('panel')">Gastos e Ingresos</button>
      <button onclick="location.href='registro.php'">Registro</button>
      <button onclick="location.href='metas.php'">Metas de Ahorro</button>
    </div>
    <div class="menu-bottom">
      <button onclick="location.href='logout.php'"><i class="bi bi-box-arrow-right"></i> Salir</button>
      <button onclick="location.href='ajustes.php'"><i class="bi bi-gear"></i> Ajustes</button>
    </div>
  </div>

  <div class="main-content" id="panel">
    <h2>Bienvenido, <?php echo htmlspecialchars($nombreUsuario); ?>!</h2>
    <div class="grid-stack">
      <div class="grid-stack-item" gs-w="8" gs-h="6">
        <div class="grid-stack-item-content">
          <div class="chart-filters">
            <button onclick="filtrar('dia')">Día</button>
            <button onclick="filtrar('semana')">Semana</button>
            <button onclick="filtrar('mes')">Mes</button>
            <button onclick="filtrar('anio')">Año</button>
          </div>
          <canvas id="graficoFinanzas"></canvas>
        </div>
      </div>

      <div class="grid-stack-item" gs-w="4" gs-h="2">
        <div class="grid-stack-item-content">
          <h3>Ingresos</h3>
          <p id="ingresos">$0.00</p>
        </div>
      </div>

      <div class="grid-stack-item" gs-w="4" gs-h="2">
        <div class="grid-stack-item-content">
          <h3>Gastos</h3>
          <p id="gastos">$0.00</p>
        </div>
      </div>

      <div class="grid-stack-item" gs-w="4" gs-h="2">
        <div class="grid-stack-item-content">
          <h3>Ahorro</h3>
          <p id="ahorro">$0.00</p>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
  const ctx = document.getElementById('graficoFinanzas').getContext('2d');
  const grafico = new Chart(ctx, {
    type: 'bar',
    data: {
      labels: ['Ingresos', 'Gastos'],
      datasets: [{
        label: 'Total',
        data: [0, 0],
        backgroundColor: ['#4CAF50', '#F44336']
      }]
    },
    options: {
      responsive: true,
      plugins: {
        legend: { display: false }
      }
    }
  });

  function filtrar(periodo) {
    $.ajax({
      url: 'includes/filtrar_datos.php',
      method: 'POST',
      data: { periodo: periodo },
      success: function (respuesta) {
        // Si la respuesta viene como string, parseamos JSON
        const datos = (typeof respuesta === 'string') ? JSON.parse(respuesta) : respuesta;

        // Actualizar gráfico
        grafico.data.datasets[0].data = [datos.ingresos, datos.gastos];
        grafico.update();

        // Actualizar valores en la página
        document.getElementById('ingresos').innerText = `$${datos.ingresos.toFixed(2)}`;
        document.getElementById('gastos').innerText = `$${datos.gastos.toFixed(2)}`;
        document.getElementById('ahorro').innerText = `$${datos.ahorro.toFixed(2)}`;
      },
      error: function (xhr, status, error) {
        console.error('Error al obtener datos:', error);
      }
    });
  }

  document.addEventListener('DOMContentLoaded', () => {
    GridStack.init();
    filtrar('mes'); // carga inicial con datos del mes
  });

  // Función para mostrar secciones si tienes más, puedes eliminar si no usas
  function mostrarSeccion(id) {
    document.querySelectorAll('.main-content').forEach(div => div.style.display = 'none');
    document.getElementById(id).style.display = 'block';
  }
</script>

<?php include 'includes/footer.php'; ?>
