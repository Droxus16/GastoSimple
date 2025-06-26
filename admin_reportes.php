<?php
session_start();
require_once 'includes/db.php';
require_once 'includes/auth.php';

if ($_SESSION['rol'] !== 'admin') {
    die("Acceso denegado");
}

$conn = db::conectar();
?>
<?php include 'includes/header.php'; ?>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">

<style>
body {
  margin: 0;
  font-family: 'Inter', sans-serif;
  color: white;
  background: linear-gradient(135deg, #0B0B52, #1D2B64, #0C1634);
  background-size: 300% 300%;
  animation: backgroundAnim 25s ease-in-out infinite;
  overflow: hidden;
}

@keyframes backgroundAnim {
  0% { background-position: 0% 50%; }
  50% { background-position: 100% 50%; }
  100% { background-position: 0% 50%; }
}

#particles-js {
  position: fixed;
  top: 0; left: 0;
  width: 100%; height: 100%;
  z-index: -1;
}

.dashboard-container {
  display: flex;
  height: 100vh;
  padding: 20px;
  gap: 20px;
  box-sizing: border-box;
}

.sidebar {
  width: 220px;
  display: flex;
  flex-direction: column;
  justify-content: space-between;
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

.sidebar button:hover,
.sidebar button.activo {
  background-color: #00D4FF;
  color: #0C1634;
  transform: scale(1.05);
}

.main-content {
  flex: 1;
  background: rgba(255, 255, 255, 0.07);
  padding: 30px;
  border-radius: 20px;
  backdrop-filter: blur(10px);
  overflow-y: auto;
  box-sizing: border-box;
  max-height: 100%;
  display: flex;
  flex-direction: column;
  gap: 30px;
}

h2 {
  font-size: 2rem;
  color: #00D4FF;
  margin-bottom: 10px;
  text-align: center;
}

input[type="text"] {
  width: 100%;
  padding: 10px 14px;
  border-radius: 10px;
  border: none;
  background: rgba(255,255,255,0.1);
  color: white;
  font-size: 1rem;
}

input[type="text"]::placeholder {
  color: rgba(255,255,255,0.6);
}

input[type="text"]:focus {
  outline: none;
  box-shadow: 0 0 5px rgba(0,212,255,0.6);
}

button {
  font-weight: bold;
  padding: 10px 18px;
  border-radius: 10px;
  border: none;
  cursor: pointer;
  transition: 0.3s ease;
  font-size: 1rem;
}

.btn-success {
  background-color: #00D4FF;
  color: #0B0B52;
}

.btn-danger {
  background-color: #ff4d4d;
  color: white;
}

.btn-success:hover {
  background-color: #00b8e6;
}

.btn-danger:hover {
  background-color: #e60000;
}

#tabla-resultados {
  background: rgba(255,255,255,0.05);
  padding: 20px;
  border-radius: 12px;
  backdrop-filter: blur(4px);
  overflow-x: auto;
  max-height: 60vh;
}

/* Responsive */
@media screen and (max-width: 768px) {
  .dashboard-container {
    flex-direction: column;
  }

  .sidebar {
    width: 100%;
    flex-direction: row;
    justify-content: space-around;
  }

  .main-content {
    padding: 20px;
  }
}
</style>

<div id="particles-js"></div>

<div class="dashboard-container">
  <div class="sidebar">
    <div class="menu-top">
      <button onclick="location.href='dashboard.php'"><i class="bi bi-pie-chart-fill"></i> Panel</button>
      <button onclick="location.href='registro.php'"><i class="bi bi-pencil-square"></i> Registro</button>
      <button onclick="location.href='metas.php'"><i class="bi bi-flag-fill"></i> Metas</button>
    </div>
    <button onclick="location.href='admin_reportes.php'" class="activo">
      <i class="bi bi-bar-chart-fill"></i> Reportes Globales
    </button>
    <div class="menu-bottom">
      <button onclick="location.href='logout.php'"><i class="bi bi-box-arrow-right"></i> Salir</button>
    </div>
  </div>

  <div class="main-content">
    <h2>Reportes Globales</h2>
    
    <form id="form-exportar" method="POST" action="includes/exportar_global.php" target="_blank">
      <div style="display: flex; gap: 10px; flex-wrap: wrap; margin-bottom: 20px;">
        <input type="text" name="filtro" id="filtro" placeholder="Buscar por usuario o categoría">
        <button type="submit" name="exportar_excel" class="btn btn-success">Exportar Excel</button>
        <button type="submit" name="exportar_pdf" class="btn btn-danger">Exportar PDF</button>
      </div>
    </form>

    <div id="tabla-resultados"></div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/particles.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
particlesJS('particles-js', {
  particles: {
    number: { value: 80, density: { enable: true, value_area: 800 } },
    color: { value: "#00D4FF" },
    shape: { type: "circle" },
    opacity: { value: 0.5, random: true },
    size: { value: 3, random: true },
    line_linked: { enable: true, distance: 150, color: "#00D4FF", opacity: 0.4, width: 1 },
    move: { enable: true, speed: 6 }
  },
  interactivity: {
    events: {
      onhover: { enable: true, mode: "repulse" },
      onclick: { enable: true, mode: "push" }
    }
  },
  retina_detect: true
});

function cargarDatos(filtro = '') {
  $.ajax({
    url: 'includes/buscar_global.php',
    method: 'POST',
    data: { filtro },
    success: function(data) {
      $('#tabla-resultados').html(data);
    }
  });
}

$('#filtro').on('input', function () {
  const valor = $(this).val();
  cargarDatos(valor);
});

$(document).ready(() => {
  cargarDatos();
});
</script>
<?php include 'includes/footer.php'; ?>
