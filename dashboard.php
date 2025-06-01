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
<script src="https://cdn.jsdelivr.net/npm/particles.js"></script> <!-- Agregar particles.js -->

<style>
  /* Asegúrate de que el body se superponga con el canvas de particles.js */
  body {
    font-family: 'Inter', sans-serif;
    position: relative;
    color: white;
    padding: 20px;
    margin: 0;
    overflow: hidden;
  }

  /* Agrega el estilo para el contenedor de particles.js */
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
    transition: background-color 0.3s;
  }

  .chart-filters button:hover {
    background-color: #ffffff55;
  }

  /* Nuevo estilo para botón activo */
  .chart-filters button.activo {
    background-color: #00D4FF;
    color: #0C1634;
    font-weight: bold;
    box-shadow: 0 0 8px #00D4FF;
  }
</style>

<!-- Agregar el contenedor para particles.js -->
<div id="particles-js"></div>

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
  // Configuración de particles.js
  particlesJS('particles-js', {
    particles: {
      number: { value: 80, density: { enable: true, value_area: 800 } },
      color: { value: "#00D4FF" },
      shape: {
        type: "circle",
        stroke: { width: 0, color: "#00D4FF" }
      },
      opacity: {
        value: 0.5,
        random: true,
        anim: { enable: true, speed: 1, opacity_min: 0.1, sync: false }
      },
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
  
  // Funciones de filtrado y gráfico (como antes)
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
      },
      scales: {
        x: { title: { display: true, text: 'Fechas' } },
        y: { beginAtZero: true, title: { display: true, text: 'Cantidad ($)' } }
      }
    }
  });

  // Función para formatear números con separadores
  function formatNumber(num) {
    return new Intl.NumberFormat('es-ES', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(num);
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
        
        // Actualiza el gráfico y los totales de finanzas
        if (datos.fechas && datos.ingresos && datos.gastos) {
          grafico.data.labels = datos.fechas;
          grafico.data.datasets = [
            {
              label: 'Ingresos',
              data: datos.ingresos,
              backgroundColor: '#4CAF50'
            },
            {
              label: 'Gastos',
              data: datos.gastos,
              backgroundColor: '#F44336'
            }
          ];
          grafico.update();
          
          const totalIngresos = datos.ingresos.reduce((a, b) => a + b, 0);
          const totalGastos = datos.gastos.reduce((a, b) => a + b, 0);

          document.getElementById('ingresos').innerText = `$${formatNumber(totalIngresos)}`;
          document.getElementById('gastos').innerText = `$${formatNumber(totalGastos)}`;
          document.getElementById('ahorro').innerText = `$${formatNumber(datos.ahorro)}`;
        }
      }
    });
  }

  document.addEventListener('DOMContentLoaded', () => {
    GridStack.init();
    filtrar('mes'); // carga inicial con datos del mes
  });
</script>

<?php include 'includes/footer.php'; ?>