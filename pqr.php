<?php
session_start();
require_once 'includes/db.php';
require_once 'includes/auth.php';

if (!isset($_SESSION['usuario_id'])) {
  header('Location: login.php');
  exit;
}

$idUsuario = $_SESSION['usuario_id'];

?>

<?php include 'includes/header.php'; ?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>PQR - Gasto Simple</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/particles.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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
      position: fixed; top: 0; left: 0; width: 100%; height: 100%; z-index: -1;
    }
    .dashboard-container {
      display: flex; height: 100vh; padding: 20px; gap: 20px; box-sizing: border-box;
    }
    .sidebar {
      width: 220px; display: flex; flex-direction: column; justify-content: space-between;
    }
    .sidebar button {
      display: flex; align-items: center; gap: 10px; padding: 12px; font-size: 1rem;
      border: none; border-radius: 12px; background: rgba(255,255,255,0.08); color: #00D4FF;
      font-weight: bold; cursor: pointer; transition: all 0.3s ease; backdrop-filter: blur(6px);
    }
    .sidebar button:hover, .sidebar .activo {
      background-color: #00D4FF; color: #0C1634; transform: scale(1.05);
    }
    .main-content {
      flex: 1; background: rgba(255,255,255,0.07); padding: 30px; border-radius: 20px;
      backdrop-filter: blur(10px); overflow-y: auto; box-sizing: border-box; max-height: 100%;
    }
    .form-container {
      background: rgba(255,255,255,0.05); padding: 25px 30px; border-radius: 15px;
      box-shadow: 0 0 8px rgba(0,212,255,0.2);
    }
    label {
      font-weight: 600; margin-bottom: 6px; display: block; color: #00D4FF;
    }
    input, textarea, select {
      width: 100%; padding: 10px 12px; margin-bottom: 16px;
      border-radius: 10px; border: none; background: rgba(255,255,255,0.1);
      color: white; font-size: 1rem;
    }
    input:focus, textarea:focus, select:focus {
      outline: none; box-shadow: 0 0 6px rgba(0,212,255,0.5);
    }
    button[type="submit"] {
      background-color: #00D4FF; color: #0B0B52; font-weight: bold; padding: 10px 20px;
      border: none; border-radius: 12px; cursor: pointer; transition: all 0.3s ease;
    }
    button[type="submit"]:hover {
      background-color: #00aacc;
    }
  </style>
</head>
<body>
  <div id="particles-js"></div>
  <div class="dashboard-container">
    <div class="sidebar">
      <div class="menu-top">
        <button onclick="location.href='dashboard.php'"><i class="bi bi-pie-chart-fill"></i> Panel</button>
        <button onclick="location.href='registro.php'"><i class="bi bi-pencil-square"></i> Registro</button>
        <button onclick="location.href='metas.php'"><i class="bi bi-flag-fill"></i> Metas</button>
      </div>
      <div class="menu-bottom">
        <button onclick="location.href='logout.php'"><i class="bi bi-box-arrow-right"></i> Salir</button>
      </div>
    </div>
    <div class="main-content">
      <div class="form-container">
        <h2 class="text-center mb-4">Enviar PQR</h2>
        <form id="form-pqr" action="includes/guardar_pqr.php" method="POST">
          <label for="tipo">Tipo de Solicitud:</label>
          <select name="tipo" required class="form-select">
            <option value="">Selecciona una opción</option>
            <option value="P">Petición</option>
            <option value="Q">Queja</option>
            <option value="R">Reclamo</option>
            <option value="S">Sugerencia</option>
          </select>
          <label for="asunto">Asunto:</label>
          <input type="text" name="asunto" required placeholder="Asunto del PQR">
          <label for="descripcion">Descripción:</label>
          <textarea name="descripcion" rows="5" required placeholder="Describe tu solicitud con detalle"></textarea>
          <button type="submit">Enviar PQR</button>
        </form>
      </div>
    </div>
  </div>

  <!-- Particles.js -->
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
      events: { onhover: { enable: true, mode: "repulse" }, onclick: { enable: true, mode: "push" } },
    },
    retina_detect: true
  });
  </script>

  <!-- Validación AJAX + SweetAlert2 -->
  <script>
  document.getElementById('form-pqr').addEventListener('submit', async function(e) {
    e.preventDefault();
    const form = e.target;
    const data = new FormData(form);

    try {
      const response = await fetch(form.action, {
        method: 'POST',
        body: data
      });

      const result = await response.json();

      if (result.success) {
        Swal.fire('✅ Enviado', 'Tu PQR se registró correctamente.', 'success');
        form.reset();
      } else {
        Swal.fire('❌ Error', result.error || 'Ocurrió un error inesperado.', 'error');
      }

    } catch (err) {
      Swal.fire('❌ Error', 'No se pudo conectar con el servidor.', 'error');
    }
  });
  </script>
</body>
</html>
<?php include 'includes/footer.php'; ?>
