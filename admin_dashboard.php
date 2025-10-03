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

// Filtrado por estado
$estado = isset($_GET['estado']) ? $_GET['estado'] : '';
$query = "
  SELECT p.*, u.nombre, u.correo 
  FROM pqrs p 
  JOIN usuarios u ON p.usuario_id = u.id
";

if ($estado === 'pendiente') {
  $query .= " WHERE p.estado IS NULL OR p.estado='pendiente'";
} elseif ($estado === 'respondido') {
  $query .= " WHERE p.estado='respondido'";
}

$query .= " ORDER BY p.fecha_creacion DESC";
$stmtPQR = $conn->prepare($query);
$stmtPQR->execute();
$pqrs = $stmtPQR->fetchAll(PDO::FETCH_ASSOC);
?>

<?php include 'includes/header.php'; ?>

<!-- Bootstrap 5 -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">

<style>
/* ---------------- Global / Layout ---------------- */
body {
  margin: 0;
  font-family: 'Inter', sans-serif;
  color: white;
  background: linear-gradient(135deg, #0B0B52, #1D2B64, #0C1634);
  background-size: 300% 300%;
  animation: backgroundAnim 25s ease-in-out infinite;
  overflow-x: hidden;
}
@keyframes backgroundAnim {
  0% { background-position: 0% 50%; }
  50% { background-position: 100% 50%; }
  100% { background-position: 0% 50%; }
}
#particles-js { position: fixed; top: 0; left: 0; width: 100%; height: 100%; z-index: -1; }

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
  background: rgba(20,20,40,0.95);
  border-radius: 12px;
  padding: 15px;
}
.sidebar button {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 12px;
  font-size: 1rem;
  border: none;
  border-radius: 12px;
  background: transparent;
  color: #00D4FF;
  cursor: pointer;
  transition: all 0.25s ease;
}
.sidebar button:hover,
.sidebar button.activo {
  background: #00D4FF;
  color: #0C1634;
}
/* ---------------- Main area ---------------- */
.main-content {
  flex: 1;
  background: rgba(255, 255, 255, 0.04);
  padding: 30px;
  border-radius: 20px;
  backdrop-filter: blur(8px);
  overflow-y: auto;
  box-sizing: border-box;
  display: flex;
  flex-direction: column;
  gap: 30px;
}
h2 { font-size: 2rem; color: #00D4FF; text-align: center; }

/* Cards */
.cards { display: flex; gap: 20px; flex-wrap: wrap; justify-content: center; }
.card-glass {
  flex: 1; min-width: 200px; background: rgba(255,255,255,0.05);
  backdrop-filter: blur(8px); padding: 30px;
  border-radius: 15px; text-align: center;
  transition: transform 0.25s ease;
}
.card-glass:hover { transform: translateY(-6px); }
.card-glass h5 { color: #00D4FF; margin-bottom: 10px; }
.card-glass p { font-size: 1.5rem; font-weight: 700; }


.table-glass-wrapper {
  background: rgba(20,20,40,0.85);
  backdrop-filter: blur(12px);
  border-radius: 16px;
  padding: 10px;
  border: 1px solid rgba(255,255,255,0.08);
  box-shadow: 0 10px 30px rgba(0,0,0,0.55);
  margin: 0 8px;
  max-height: calc(100vh - 420px);
  overflow: auto;
}

.table-glass-wrapper .table {
  background: transparent !important;
  margin-bottom: 0;
  width: 100%;
  color: #000000ff !important;
}
.table-glass-wrapper .table thead th {
  background: rgba(0,212,255,0.12) !important;
  color: #00D4FF !important;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.6px;
  border: none !important;
}
.table-glass-wrapper .table tbody tr {
  background: transparent !important;
}
.table-glass-wrapper .table tbody tr:nth-child(even) {
  background: rgba(255,255,255,0.02) !important;
}
.table-glass-wrapper .table tbody tr:hover {
  background: rgba(0,212,255,0.10) !important;
}
.table-glass-wrapper .table td,
.table-glass-wrapper .table th {
  color: #000000ff !important;
  padding: 12px 16px !important;
  vertical-align: middle !important;
  border-top: none !important;
  white-space: nowrap;
}

.table-glass-wrapper::-webkit-scrollbar { height: 8px; width: 8px; }
.table-glass-wrapper::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.08); border-radius: 8px; }

.table.table-glass { border-collapse: separate; border-spacing: 0; }

.btn-primary { background-color: #0b67ff; border-color: #0b67ff; }
.badge.bg-success { background-color: #28a745; color: white; }

.modal-content {
  background: rgba(20,20,40,0.95);
  backdrop-filter: blur(12px);
  border-radius: 14px;
  border: 1px solid rgba(255,255,255,0.08);
  color: white;
  box-shadow: 0 10px 30px rgba(0,0,0,0.6);
}
.modal-header h5 { color: #00D4FF; font-weight: 700; }


@media (max-width: 1100px) {
  .table-glass-wrapper { max-height: calc(100vh - 360px); }
}
@media (max-width: 768px) {
  .dashboard-container { flex-direction: column; }
  .sidebar { width: 100%; flex-direction: row; justify-content: space-around; }
  .main-content { padding: 20px; }
  .table-glass-wrapper { max-height: 40vh; }
}
</style>

<div id="particles-js"></div>
<div class="dashboard-container">
  <!-- Sidebar -->
  <div class="sidebar">
    <div>
      <button onclick="location.href='admin_dashboard.php'" class="activo"><i class="bi bi-speedometer2"></i> Panel Admin</button>
      <button onclick="location.href='admin_reportes.php'"><i class="bi bi-bar-chart-fill"></i> Reportes Globales</button>
      <button onclick="location.href='admin_masivo.php'"><i class="bi bi-envelope-fill"></i> Correos Masivos</button>
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

    <h2 style="margin-top: 20px;">PQR Recibidos</h2>

    <!-- Filtros -->
    <div class="mb-3 text-center">
      <form method="get" class="d-inline-block">
        <select name="estado" class="form-select" style="width:auto;display:inline-block;" onchange="this.form.submit()">
          <option value="">-- Todos --</option>
          <option value="pendiente" <?= ($estado=='pendiente')?'selected':'' ?>>Pendientes</option>
          <option value="respondido" <?= ($estado=='respondido')?'selected':'' ?>>Respondidos</option>
        </select>
      </form>
    </div>

    <!-- TABLE: put the glass panel wrapper around the table -->
    <div class="table-responsive">
      <div class="table-glass-wrapper">
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
              <th>Estado</th>
              <th>Acciones</th>
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
                  <td><?= htmlspecialchars($pqr['estado'] ?: 'Pendiente') ?></td>
                  <td>
                    <?php if ($pqr['estado'] !== 'respondido'): ?>
                      <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#modalRespuesta<?= $pqr['id'] ?>">Responder</button>
                    <?php else: ?>
                      <span class="badge bg-success">Ya respondido</span>
                    <?php endif; ?>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php else: ?>
              <tr><td colspan="9">No hay PQRs registrados.</td></tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<!-- ==================== MODALES ==================== -->
<?php if ($pqrs): ?>
  <?php foreach ($pqrs as $pqr): ?>
    <div class="modal fade" id="modalRespuesta<?= $pqr['id'] ?>" tabindex="-1">
      <div class="modal-dialog modal-lg">
        <form method="post" action="responder_pqr.php">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title">Responder PQR #<?= $pqr['id'] ?></h5>
              <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
              <p><strong>Usuario:</strong> <?= htmlspecialchars($pqr['nombre']) ?></p>
              <p><strong>Correo:</strong> <?= htmlspecialchars($pqr['correo']) ?></p>
              <p><strong>Asunto:</strong> <?= htmlspecialchars($pqr['asunto']) ?></p>
              <p><strong>Descripción:</strong> <?= htmlspecialchars($pqr['descripcion']) ?></p>
              <hr>
              <input type="hidden" name="pqr_id" value="<?= $pqr['id'] ?>">
              <div class="mb-3">
                <label class="form-label">Respuesta:</label>
                <textarea class="form-control" name="respuesta" required></textarea>
              </div>
            </div>
            <div class="modal-footer">
              <button type="submit" class="btn btn-success">Enviar</button>
            </div>
          </div>
        </form>
      </div>
    </div>
  <?php endforeach; ?>
<?php endif; ?>
<?php if (isset($_GET['msg']) && $_GET['msg'] === 'respuesta_ok'): ?>
<!-- Modal de confirmación -->
<div class="modal fade" id="modalConfirmacion" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content" style="border-radius:15px;">
      <div class="modal-header bg-success text-white">
        <h5 class="modal-title">¡Respuesta enviada!</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body text-center">
        <i class="bi bi-check-circle-fill text-success" style="font-size:3rem;"></i>
        <p class="mt-3">La respuesta fue enviada correctamente al usuario.</p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-success" data-bs-dismiss="modal">Aceptar</button>
      </div>
    </div>
  </div>
</div>

<script>
  // Mostrar modal automáticamente al cargar la página
  document.addEventListener("DOMContentLoaded", function() {
    var modal = new bootstrap.Modal(document.getElementById('modalConfirmacion'));
    modal.show();
  });
</script>
<?php endif; ?>


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
      "detect_on": "canvas",
      "events": { "onhover": { "enable": false }, "onclick": { "enable": false } }
    },
    "retina_detect": true
  });
</script>

<?php include 'includes/footer.php'; ?>
