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
<script src="https://cdn.jsdelivr.net/npm/particles.js"></script>

<style>
body {
overflow: auto;
}
.main-content {
overflow-y: auto;
}

body {
  position: relative;
  width: 100%;
  height: 100%;
  background: linear-gradient(135deg, #0B0B52, #1D2B64, #0C1634, #0B0B52);
  background-size: 300% 300%;
  animation: backgroundAnim 25s ease-in-out infinite;
  z-index: -2;
  opacity: 0.95;
}

@keyframes backgroundAnim {
  0% { background-position: 0% 50%; }
  50% { background-position: 100% 50%; }
  100% { background-position: 0% 50%; }
}


#particles-js {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  z-index: -1;
}

.dashboard-container {
  display: flex;
  height: 100vh;
  gap: 20px;
  padding: 20px;
  box-sizing: border-box;
  overflow: hidden;
}

.sidebar {
  width: 220px;
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
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 12px;
  font-size: 1rem;
  border: none;
  border-radius: 12px;
  background: rgba(255, 255, 255, 0.08);
  color: #00D4FF;
  font-weight: bold;
  cursor: pointer;
  transition: all 0.3s ease;
  backdrop-filter: blur(6px);
}

.sidebar button:hover {
  background-color: #00D4FF;
  color: #0C1634;
  transform: scale(1.05);
}

.main-content {
  flex: 1;
  height: 100%;
  display: flex;
  flex-direction: column;
  background: rgba(255, 255, 255, 0.05);
  padding: 25px;
  border-radius: 20px;
  backdrop-filter: blur(10px);
  overflow: hidden;
  color: white;
  box-sizing: border-box;
}

.grid-stack {
  flex: 1;
  overflow: auto;
  width: 100%;
  box-sizing: border-box;
}

.grid-stack-item-content {
  background: rgba(255, 255, 255, 0.15);
  border-radius: 20px;
  padding: 25px;
  box-shadow: 0 8px 32px rgba(0, 0, 0, 0.25);
  color: white;
  text-align: center;
  transition: transform 0.2s ease-in-out;
}

.grid-stack-item-content:hover {
  transform: scale(1.01);
}

.chart-filters {
  display: flex;
  justify-content: center;
  gap: 10px;
  flex-wrap: wrap;
  margin-bottom: 20px;
}

.chart-filters button {
  padding: 8px 14px;
  border-radius: 8px;
  border: 1px solid #00D4FF;
  background-color: #ffffff22;
  color: white;
  font-weight: bold;
  cursor: pointer;
  transition: all 0.3s;
}

.chart-filters button:hover {
  background-color: #ffffff44;
}

.chart-filters button.activo {
  background-color: #00D4FF;
  color: #0C1634;
  box-shadow: 0 0 8px rgba(0, 212, 255, 0.7);
}

@media (max-width: 768px) {
  .dashboard-container {
    flex-direction: column;
    overflow: auto;
  }
  .sidebar {
    width: 100%;
    flex-direction: row;
    justify-content: space-around;
    padding-bottom: 10px;
  }
  .sidebar .menu-top,
  .sidebar .menu-bottom {
    flex-direction: row;
    flex-wrap: wrap;
    justify-content: center;
    gap: 10px;
  }
  .main-content {
    height: auto;
    max-height: none;
    margin-top: 10px;
  }
}
</style>

<div id="particles-js"></div>

<div class="dashboard-container">
  <div class="sidebar">
    <div class="menu-top">
      <button onclick="mostrarSeccion('panel')"><i class="bi bi-pie-chart-fill"></i> Panel</button>
      <button onclick="location.href='registro.php'"><i class="bi bi-pencil-square"></i> Registro</button>
      <button onclick="location.href='metas.php'"><i class="bi bi-flag-fill"></i> Metas</button>
    </div>

    <?php if ($_SESSION['rol'] === 'admin'): ?>
      <button onclick="location.href='admin_reportes.php'" style="background-color: #FFD700; color: black;">
        <i class="bi bi-bar-chart-fill"></i> Reportes Globales
      </button>
    <?php endif; ?>

    <div class="menu-bottom">
      <button onclick="location.href='logout.php'"><i class="bi bi-box-arrow-right"></i> Salir</button>
      <button onclick="location.href='ajustes.php'"><i class="bi bi-gear-fill"></i> Ajustes</button>
    </div>
  </div>

  <div class="main-content" id="panel">
    <h2>Bienvenido, <?php echo htmlspecialchars($nombreUsuario); ?>!</h2>
    <div class="grid-stack">
      <div class="grid-stack-item" gs-w="8" gs-h="6">
        <div class="grid-stack-item-content">
          <div class="chart-filters">
            <button onclick="filtrar('día')">Día</button>
            <button onclick="filtrar('semana')">Semana</button>
            <button onclick="filtrar('mes')" class="activo">Mes</button>
            <button onclick="filtrar('año')">Año</button>
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
particlesJS('particles-js', {
  particles: {
    number: { value: 80, density: { enable: true, value_area: 800 } },
    color: { value: "#00D4FF" },
    shape: { type: "circle", stroke: { width: 0, color: "#00D4FF" } },
    opacity: { value: 0.5, random: true, anim: { enable: true, speed: 1, opacity_min: 0.1, sync: false } },
    size: { value: 3, random: true, anim: { enable: true, speed: 4, size_min: 0.1, sync: false } },
    line_linked: { enable: true, distance: 150, color: "#00D4FF", opacity: 0.4, width: 1 },
    move: { enable: true, speed: 6, direction: "none", random: false, straight: false, out_mode: "out", bounce: false }
  },
  interactivity: {
    events: {
      onhover: { enable: true, mode: "repulse" },
      onclick: { enable: true, mode: "push" }
    }
  },
  retina_detect: true
});

const ctx = document.getElementById('graficoFinanzas').getContext('2d');
const grafico = new Chart(ctx, {
  type: 'bar',
  data: {
    labels: ['Ingresos', 'Gastos'],
    datasets: [{ label: 'Total', data: [0, 0], backgroundColor: ['#4CAF50', '#F44336'] }]
  },
  options: {
    responsive: true,
    plugins: { legend: { display: false } },
    scales: {
      x: { title: { display: true, text: 'Fechas' } },
      y: { beginAtZero: true, title: { display: true, text: 'Cantidad ($)' } }
    }
  }
});

function formatNumber(num) {
  return new Intl.NumberFormat('es-ES', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(num);
}

function aplicarColor(id, valor) {
  const el = document.getElementById(id);
  el.innerText = `$${formatNumber(valor)}`;
  el.style.color = valor < 0 ? '#FF6B6B' : '#FFFFFF';
}

function filtrar(periodo) {
  const periodoLower = periodo.toLowerCase();
  document.querySelectorAll('.chart-filters button').forEach(btn => {
    btn.classList.toggle('activo', btn.textContent.toLowerCase() === periodoLower);
  });

  $.ajax({
    url: 'includes/filtrar_datos.php',
    method: 'POST',
    data: { periodo: periodoLower },
    success: function (respuesta) {
      const datos = (typeof respuesta === 'string') ? JSON.parse(respuesta) : respuesta;
      if (datos.fechas && datos.ingresos && datos.gastos) {
        grafico.data.labels = datos.fechas;
        grafico.data.datasets = [
          { label: 'Ingresos', data: datos.ingresos, backgroundColor: '#4CAF50' },
          { label: 'Gastos', data: datos.gastos, backgroundColor: '#F44336' }
        ];
        grafico.update();

        const totalIngresos = datos.ingresos.reduce((a, b) => a + b, 0);
        const totalGastos = datos.gastos.reduce((a, b) => a + b, 0);

        aplicarColor('ingresos', totalIngresos);
        aplicarColor('gastos', totalGastos);
        aplicarColor('ahorro', datos.ahorro);
      }
    }
  });
}

document.addEventListener('DOMContentLoaded', () => {
  GridStack.init();
  filtrar('mes');
});
</script>

<?php include 'includes/footer.php'; ?>
