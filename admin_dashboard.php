<?php
session_start();
require_once 'includes/db.php';
require_once 'includes/auth.php';

// Solo admin
if ($_SESSION['rol'] !== 'admin') {
  header("Location: dashboard.php");
  exit;
}

$conn = db::conectar();

// Totales globales
$stmtReportes = $conn->prepare("
  SELECT 
    (SELECT COALESCE(SUM(monto),0) FROM ingresos) AS total_ingresos,
    (SELECT COALESCE(SUM(monto),0) FROM gastos) AS total_gastos,
    (SELECT COALESCE(SUM(monto),0) FROM aportes_ahorro) AS total_aportes
");
$stmtReportes->execute();
$reportes = $stmtReportes->fetch(PDO::FETCH_ASSOC);
// Mostrar en notación científica
function formatoCientifico($numero) {
  if ($numero == 0) return '0';
  $potencia = floor(log10(abs($numero)));
  $coef = round($numero / pow(10, $potencia), 2);
  return "{$coef}e{$potencia}";
}
// PQRs
$stmtPQR = $conn->prepare("
  SELECT p.*, u.nombre, u.correo 
  FROM pqrs p 
  JOIN usuarios u ON p.usuario_id = u.id
  ORDER BY p.fecha_creacion DESC
");
$stmtPQR->execute();
$pqrs = $stmtPQR->fetchAll(PDO::FETCH_ASSOC);
?>

<?php include 'includes/header.php'; ?>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">
<style>
/* Estilos existentes sin cambios importantes */
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
  display: flex; align-items: center; gap: 10px;
  padding: 12px; font-size: 1rem;
  border: none; border-radius: 12px;
  background: rgba(255, 255, 255, 0.08);
  color: #00D4FF; font-weight: bold;
  cursor: pointer; transition: all 0.3s ease;
  backdrop-filter: blur(6px);
}
.sidebar button:hover, .sidebar button.activo {
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
h2 { font-size: 2rem; color: #00D4FF; text-align: center; }
.cards { display: flex; gap: 20px; flex-wrap: wrap; justify-content: center; }
.card-glass {
  flex: 1; min-width: 200px; background: rgba(255,255,255,0.08);
  backdrop-filter: blur(8px); padding: 30px;
  border-radius: 15px; text-align: center;
  transition: transform 0.3s ease;
}
.card-glass:hover { transform: translateY(-5px); }
.card-glass h5 { color: #00D4FF; margin-bottom: 10px; }
.card-glass p { font-size: 1.5rem; font-weight: bold; }
.table-glass {
  background: rgba(255,255,255,0.05);
  backdrop-filter: blur(6px);
  border-radius: 12px; overflow: auto;
}
.table-glass th { color: #00D4FF; }
.table-glass td { color: white; }
.table-glass th, .table-glass td { text-align: center; padding: 10px; }
.table-glass thead { background: rgba(255,255,255,0.1); }
.table-glass tr:hover { background: rgba(0, 212, 255, 0.1); }
@media (max-width: 768px) {
  .dashboard-container { flex-direction: column; }
  .sidebar { width: 100%; flex-direction: row; justify-content: space-around; }
  .main-content { padding: 20px; }
}
</style>
<div id="particles-js"></div>
<div class="dashboard-container">
  <!-- Sidebar -->
  <div class="sidebar">
    <div>
      <button onclick="location.href='admin_dashboard.php'" class="activo"><i class="bi bi-speedometer2"></i> Panel Admin</button>
      <button onclick="location.href='admin_reportes.php'"><i class="bi bi-bar-chart-fill"></i> Reportes Globales</button>
    </div>
    <div>
      <button onclick="location.href='logout.php'"><i class="bi bi-box-arrow-right"></i> Cerrar Sesión</button>
    </div>
  </div>
  <!-- Main Content -->
  <div class="main-content">
    <h2>Bienvenido, Admin</h2>

    <div class="cards">
      <div class="card-glass">
        <h5>Total Ingresos</h5>
        <p>$<?= formatoCientifico($reportes['total_ingresos']) ?></p>
      </div>
      <div class="card-glass">
        <h5>Total Gastos</h5>
        <p>$<?= formatoCientifico($reportes['total_gastos']) ?></p>
      </div>
      <div class="card-glass">
        <h5>Total Aportes Ahorro</h5>
        <p>$<?= formatoCientifico($reportes['total_aportes']) ?></p>
      </div>
    </div>
    <h2 style="margin-top: 40px;">PQR Recibidos</h2>
    <div class="table-responsive">
      <table class="table table-glass">
        <thead>
          <tr>
            <th>#</th>
            <th>Usuario</th>
            <th>Correo</th>
            <th>Tipo</th>
            <th>Asunto</th>
            <th>Descripción</th>
            <th>Fecha</th>
          </tr>
        </thead>
        <tbody>
          <?php if ($pqrs): ?>
            <?php foreach ($pqrs as $pqr): ?>
              <tr>
                <td><?= htmlspecialchars($pqr['id']) ?></td>
                <td><?= htmlspecialchars($pqr['nombre']) ?></td>
                <td><?= htmlspecialchars($pqr['correo']) ?></td>
                <td><?= htmlspecialchars($pqr['tipo']) ?></td>
                <td><?= htmlspecialchars($pqr['asunto']) ?></td>
                <td><?= htmlspecialchars($pqr['descripcion']) ?></td>
                <td><?= htmlspecialchars($pqr['fecha_creacion']) ?></td>
              </tr>
            <?php endforeach; ?>
          <?php else: ?>
            <tr><td colspan="7">No hay PQRs registrados.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/particles.js"></script>
<script>
  particlesJS("particles-js", {
    "particles": {
      "number": { "value": 80, "density": { "enable": true, "value_area": 800 } },
      "color": { "value": "#00D4FF" },
      "shape": { "type": "circle" },
      "opacity": { "value": 0.5, "anim": { "enable": true, "speed": 1 } },
      "size": { "value": 3, "random": true, "anim": { "enable": true, "speed": 40 } },
      "line_linked": { "enable": true, "distance": 150, "color": "#00D4FF", "opacity": 0.4, "width": 1 },
      "move": { "enable": true, "speed": 3 }
    },
    "interactivity": {
      "events": {
        "onhover": { "enable": true, "mode": "repulse" },
        "onclick": { "enable": true, "mode": "push" }
      },
      "modes": {
        "repulse": { "distance": 100, "duration": 0.4 },
        "push": { "particles_nb": 4 }
      }
    },
    "retina_detect": true
  });
</script>
<?php include 'includes/footer.php'; ?>