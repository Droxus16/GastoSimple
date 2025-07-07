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

  .form-glass {
    background: rgba(255,255,255,0.05);
    backdrop-filter: blur(8px);
    padding: 20px;
    border-radius: 12px;
  }

  .form-glass input {
    width: 100%;
    padding: 10px 14px;
    border-radius: 10px;
    border: none;
    background: rgba(255,255,255,0.1);
    color: white;
    font-size: 1rem;
  }

  .form-glass input::placeholder {
    color: rgba(255,255,255,0.6);
  }

  .form-glass input:focus {
    outline: none;
    box-shadow: 0 0 5px rgba(0,212,255,0.6);
  }

  .form-glass button {
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
    backdrop-filter: blur(6px);
    overflow-x: auto;
    max-height: 60vh;
  }

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
  .form-glass .botones-exportar {
    display: flex;
    flex-wrap: wrap;
    gap: 12px;
  }

  .form-glass select, .form-glass input {
    padding: 10px 14px;
    border-radius: 10px;
    border: none;
    background: rgba(255,255,255,0.1);
    color: white;
    font-size: 1rem;
  }

  .form-glass select {
    min-width: 180px;
  }

  .form-glass option {
    background: #0C1634;
  }

  .btn-custom {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 10px 20px;
    border: none;
    border-radius: 10px;
    font-weight: bold;
    font-size: 1rem;
    cursor: pointer;
    transition: all 0.3s ease;
  }

  .btn-custom i {
    font-size: 1.2rem;
  }

  .export-excel {
    background: #00D4FF;
    color: #0B0B52;
  }

  .export-excel:hover {
    background: #00b8e6;
  }

  .export-pdf {
    background: #ff4d4d;
    color: #fff;
  }

  .export-pdf:hover {
    background: #e60000;
  }

</style>
  <div class="dashboard-container">
    <div class="sidebar">
      <div>
        <button onclick="location.href='admin_dashboard.php'"><i class="bi bi-speedometer2"></i> Panel Admin</button>
        <button onclick="location.href='admin_reportes.php'" class="activo"><i class="bi bi-bar-chart-fill"></i> Reportes Globales</button>
      </div>
       <div>
      <button onclick="location.href='logout.php'"><i class="bi bi-box-arrow-right"></i> Cerrar Sesión</button>
    </div>
  </div>
<!-- dentro de .dashboard-container -->
<div class="main-content">
  <h2>Reportes Globales</h2>
    <div class="form-glass">
      <form id="form-exportar" method="POST" action="includes/exportar_global.php" target="_blank">
        <div class="botones-exportar">
          <select name="modo_filtro" id="modo_filtro">
            <option value="nombre">Filtrar por Nombre</option>
            <option value="categoria">Filtrar por Categoría</option>
          </select>
          <input type="text" name="filtro" id="filtro" placeholder="Buscar valor..." onkeydown="return event.key !== 'Enter';">
          <button type="submit" name="exportar_excel" class="btn-custom export-excel">
            <i class="bi bi-file-earmark-excel"></i> Exportar Excel
          </button>
          <button type="submit" name="exportar_pdf" class="btn-custom export-pdf">
            <i class="bi bi-file-earmark-pdf"></i> Exportar PDF
          </button>
        </div>
      </form>
    </div>
  <div id="tabla-resultados"></div>
</div>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
function cargarDatos() {
  const filtro = $('#filtro').val().trim();
  const modo_filtro = $('#modo_filtro').val();
  $.ajax({
    url: 'includes/buscar_global.php',
    method: 'POST',
    data: { filtro: filtro, modo_filtro: modo_filtro },
    success: function(data) {
      $('#tabla-resultados').html(data);
    },
    error: function() {
      $('#tabla-resultados').html('<p style="color:red;text-align:center;">⚠️ Error al cargar datos.</p>');
    }
  });
}
$(document).ready(function() {
  $('#filtro').on('input', cargarDatos);
  $('#modo_filtro').on('change', cargarDatos);
  cargarDatos();
});
</script>