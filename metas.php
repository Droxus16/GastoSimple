<?php
session_start();
require_once 'includes/db.php';
require_once 'includes/auth.php';

if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit();
}

$conn = db::conectar();
$usuario_id = $_SESSION['usuario_id'];

// Consulta de metas con total aportado
$metas = $conn->prepare("
    SELECT m.*, 
        (SELECT COALESCE(SUM(a.monto), 0) FROM aportes_ahorro a WHERE a.meta_id = m.id) AS total_aportado
    FROM metas_ahorro m 
    WHERE m.usuario_id = ?
    ORDER BY m.fecha_limite ASC
");
$metas->execute([$usuario_id]);
$lista_metas = $metas->fetchAll(PDO::FETCH_ASSOC);

// Cálculo del ahorro total mensual y anual
$totales = $conn->prepare("
    SELECT
        (
            (SELECT COALESCE(SUM(monto), 0) FROM ingresos 
             WHERE usuario_id = :usuario_id 
               AND MONTH(fecha) = MONTH(CURDATE()) 
               AND YEAR(fecha) = YEAR(CURDATE()))
          -
            (SELECT COALESCE(SUM(monto), 0) FROM gastos 
             WHERE usuario_id = :usuario_id 
               AND MONTH(fecha) = MONTH(CURDATE()) 
               AND YEAR(fecha) = YEAR(CURDATE()))
          -
            (SELECT COALESCE(SUM(a.monto), 0)
             FROM aportes_ahorro a
             JOIN metas_ahorro m ON a.meta_id = m.id
             WHERE m.usuario_id = :usuario_id 
               AND MONTH(a.fecha) = MONTH(CURDATE()) 
               AND YEAR(a.fecha) = YEAR(CURDATE()))
        ) AS total_mes,
        (
            (SELECT COALESCE(SUM(monto), 0) FROM ingresos 
             WHERE usuario_id = :usuario_id 
               AND YEAR(fecha) = YEAR(CURDATE()))
          -
            (SELECT COALESCE(SUM(monto), 0) FROM gastos 
             WHERE usuario_id = :usuario_id 
               AND YEAR(fecha) = YEAR(CURDATE()))
          -
            (SELECT COALESCE(SUM(a.monto), 0)
             FROM aportes_ahorro a
             JOIN metas_ahorro m ON a.meta_id = m.id
             WHERE m.usuario_id = :usuario_id 
               AND YEAR(a.fecha) = YEAR(CURDATE()))
        ) AS total_anual
");
$totales->execute(['usuario_id' => $usuario_id]);
$ahorro = $totales->fetch(PDO::FETCH_ASSOC);

// Notificación (si existe)
$mensaje = '';
if (isset($_SESSION['mensaje'])) {
    $mensaje = $_SESSION['mensaje'];
    unset($_SESSION['mensaje']);
}
?>

<?php include 'includes/header.php'; ?>
<link rel="stylesheet" href="assets/css/estilos.css">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/particles.js"></script>
<style>
/*FONDO Y ANIMACIÓN*/
body {
  margin: 0;
  background: linear-gradient(135deg, #0B0B52, #1D2B64, #0C1634);
  background-size: 300% 300%;
  animation: backgroundAnim 25s ease-in-out infinite;
  color: white;
  font-family: 'Inter', sans-serif;
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

/*LAYOUT PRINCIPAL*/
.dashboard-container {
  display: flex;
  height: 100vh;
  gap: 20px;
  padding: 20px;
  box-sizing: border-box;
  position: relative;
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

.sidebar button:hover {
  background-color: #00D4FF;
  color: #0C1634;
  transform: scale(1.05);
}

.main-content {
  flex: 1;
  background: rgba(255, 255, 255, 0.05);
  padding: 25px;
  border-radius: 20px;
  backdrop-filter: blur(10px);
  color: white;
  overflow-y: auto;
  box-sizing: border-box;
}

/*RESUMEN AHORRO Y FORMULARIO*/
.card-valor {
  flex: 1;
  background: rgba(255, 255, 255, 0.15);
  padding: 20px;
  border-radius: 15px;
  backdrop-filter: blur(8px);
  text-align: center;
}

.formulario-meta {
  background: rgba(255, 255, 255, 0.1);
  padding: 20px;
  border-radius: 10px;
  margin-bottom: 30px;
  display: flex;
  flex-direction: column;
  gap: 10px;
}

.formulario-meta input,
.formulario-meta button {
  padding: 12px;
  border-radius: 8px;
  border: none;
  font-size: 1rem;
}

.formulario-meta input {
  background: rgba(255, 255, 255, 0.2);
  color: white;
}

.formulario-meta button {
  background-color: #00D4FF;
  color: #0C1634;
  font-weight: bold;
  cursor: pointer;
  transition: transform 0.3s;
}

.formulario-meta button:hover {
  transform: scale(1.05);
}

/*TABLA DE METAS*/
.table-container {
  width: 100%;
  overflow-x: auto;
  -webkit-overflow-scrolling: touch;
}

.table-container::after {
  content: '← desliza la tabla →';
  display: block;
  text-align: center;
  font-size: 0.8rem;
  color: #bbb;
  margin-top: 5px;
}

table {
  width: 100%;
  border-collapse: collapse;
  min-width: 600px;
  background: rgba(255, 255, 255, 0.05);
  backdrop-filter: blur(8px);
  border-radius: 15px;
  overflow: hidden;
  margin-bottom: 20px;
}

th, td {
  border: 1px solid rgba(255, 255, 255, 0.1);
  padding: 12px 10px;
  text-align: center;
  background-color: rgba(0, 0, 0, 0.3);
  color: white;
}

th {
  background-color: rgba(255, 255, 255, 0.15);
  font-weight: bold;
}
tr:nth-child(even) td {
  background-color: rgba(0, 0, 0, 0.2);
}
.barra-progreso {
  background: #444;
  border-radius: 5px;
  overflow: hidden;
}
.barra-progreso > div {
  background: #00D4FF;
  color: #0C1634;
  padding: 5px;
  text-align: center;
  font-weight: bold;
}
/*MODAL EDICIÓN */
.modal-overlay {
  position: fixed;
  top: 0; left: 0;
  width: 100%; height: 100%;
  background-color: rgba(0,0,0,0.7);
  display: none;
  justify-content: center;
  align-items: center;
  transition: opacity 0.3s ease;
  z-index: 1000;
  opacity: 0;
}
.modal-overlay.active {
  display: flex;
  opacity: 1;
}
.modal-content {
  background-color: rgba(0, 0, 0, 0.85);
  color: white;
  border-radius: 10px;
  padding: 30px;
  width: 90%;
  max-width: 600px;
  max-height: 90%;
  overflow-y: auto;
  position: relative;
  box-shadow: 0 0 20px rgba(255, 255, 255, 0.1);
  transform: translateY(20px);
  transition: transform 0.3s ease;
}
.modal-overlay.active .modal-content {
  transform: translateY(0);
}
.modal-close {
  position: absolute;
  top: 10px; right: 15px;
  font-size: 1.5rem;
  cursor: pointer;
  color: white;
}
button#eliminar-meta-btn {
  background: #FF6B6B;
  color: #fff;
  border: none;
  border-radius: 6px;
  padding: 10px 15px;
  margin-top: 15px;
  font-weight: bold;
  cursor: pointer;
}
button#eliminar-meta-btn:hover {
  background: #FF4C4C;
}
  .notificaciones-dropdown {
    position: absolute;
    top: 80px;
    left: 20px;
    width: 250px;
    background: rgba(255, 255, 255, 0.08); /* Mismo fondo translúcido */
    border-radius: 12px;
    backdrop-filter: blur(10px); /* Igual que main-content */
    color: white;
    display: none;
    flex-direction: column;
    padding: 15px 20px;
    z-index: 999;
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.25); /* Igual sombra */
  }
  .notificaciones-dropdown h4 {
    margin: 0 0 10px;
    font-size: 1rem;
    border-bottom: 1px solid #00D4FF;
    padding-bottom: 5px;
  }
  .notificaciones-dropdown ul {
    list-style: none;
    padding: 0;
    margin: 0;
  }
  .notificaciones-dropdown li {
    padding: 5px 0;
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    font-size: 0.9rem;
  }
  #badge-alerta {
  background: red;
  border-radius: 50%;
  width: 12px;
  height: 12px;
  display: inline-block;
  margin-left: 5px;
  }
  .shake {
    animation: shake 0.5s;
  }
    @keyframes shake {
    0% { transform: rotate(0deg); }
    20% { transform: rotate(-15deg); }
    40% { transform: rotate(15deg); }
    60% { transform: rotate(-10deg); }
    80% { transform: rotate(10deg); }
    100% { transform: rotate(0deg); }
  }
</style>
<div id="particles-js"></div>
<div class="dashboard-container">
  <div class="sidebar">
    <div class="menu-top">
      <button onclick="location.href='dashboard.php'">
        <i class="bi bi-pie-chart-fill"></i> Panel
      <button onclick="location.href='registro.php'"><i class="bi bi-pencil-square"></i> Registro</button>
    </div>
    <button id="btn-notificaciones" onclick="toggleNotificaciones()">
      <i id="icono-campana" class="bi bi-bell-fill"></i> Notificaciones
      <span id="badge-alerta" style="display:none; background:red; border-radius:50%; width:12px; height:12px; display:inline-block; margin-left:5px;"></span>
    </button>
    <div id="panel-notificaciones" class="notificaciones-dropdown">
      <h4>Notificaciones</h4>
      <ul id="lista-notificaciones"></ul>
    </div>
    <div class="menu-bottom">
      <button onclick="location.href='logout.php'"><i class="bi bi-box-arrow-right"></i> Salir</button>
      <button onclick="location.href='ajustes.php'"><i class="bi bi-gear-fill"></i> Ajustes</button>
    </div>
  </div>
  <!-- MAIN CONTENT -->
  <div class="main-content">
    <!-- Resumen Ahorro -->
    <div class="resumen-ahorro" style="display: flex; gap: 20px; margin-bottom: 25px;">
      <div class="card-valor">
        <h4>Total Ahorro del Mes</h4>
        <p>$<?= number_format($ahorro['total_mes'] ?? 0, 2) ?></p>
      </div>
      <div class="card-valor">
        <h4>Total Ahorro Anual</h4>
        <p>$<?= number_format($ahorro['total_anual'] ?? 0, 2) ?></p>
      </div>
    </div>
    <!-- Formulario Nueva Meta -->
    <form action="crear_meta.php" method="POST" class="formulario-meta">
      <h4>Crear Nueva Meta de Ahorro</h4>
      <input type="text" name="nombre" placeholder="Nombre de la meta" required>
      <input type="number" name="monto_objetivo" placeholder="Monto objetivo" required step="0.01">
      <input type="date" name="fecha_limite" required>
      <button type="submit">Guardar Meta</button>
    </form>
    <!-- Tabla Metas -->
    <div class="table-container">
      <table>
        <thead>
          <tr>
            <th>Meta</th>
            <th>Objetivo</th>
            <th>Aportado</th>
            <th>Progreso</th>
            <th>Fecha Límite</th>
            <th>Acciones</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($lista_metas as $meta):
            $aportado = floatval($meta['total_aportado'] ?? 0);
            $porcentaje = $meta['monto_objetivo'] > 0 ? min(100, ($aportado / $meta['monto_objetivo']) * 100) : 0;
          ?>
            <tr>
              <td><?= htmlspecialchars($meta['nombre']) ?></td>
              <td>$<?= number_format($meta['monto_objetivo'], 2) ?></td>
              <td>$<?= number_format($aportado, 2) ?></td>
              <td>
                <div class="barra-progreso">
                  <div style="width: <?= $porcentaje ?>%;"><?= round($porcentaje) ?>%</div>
                </div>
              </td>
              <td><?= $meta['fecha_limite'] ?></td>
              <td style="display: flex; flex-direction: column; gap: 5px;">
                <form action="aportar.php" method="POST">
                  <input type="hidden" name="meta_id" value="<?= $meta['id'] ?>">
                  <input type="number" name="monto" placeholder="$0.00" step="0.01" required>
                  <button type="submit">Aportar</button>
                </form>
                <button class="editar-meta-btn"
                  data-id="<?= $meta['id'] ?>"
                  data-nombre="<?= htmlspecialchars($meta['nombre']) ?>"
                  data-monto="<?= $meta['monto_objetivo'] ?>"
                  data-fecha="<?= $meta['fecha_limite'] ?>"
                >Editar</button>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
<!--Modal global al final -->
<div id="modal-editar-meta" class="modal-overlay">
  <div class="modal-content">
    <span class="modal-close" onclick="cerrarModalMeta()">&times;</span>
    <h2>Editar Meta</h2>
    <form id="form-editar-meta" action="controllers/editar_meta.php" method="POST">
      <input type="hidden" id="edit-meta-id" name="meta_id">
      <label for="edit-nombre">Nombre:</label>
      <input type="text" id="edit-nombre" name="nombre" required>
      <label for="edit-monto">Monto Objetivo:</label>
      <input type="number" id="edit-monto" name="monto_objetivo" step="0.01" required>
      <label for="edit-fecha">Fecha Límite:</label>
      <input type="date" id="edit-fecha" name="fecha_limite" required>
      <button type="submit">Guardar Cambios</button>
      <button type="button" id="eliminar-meta-btn">Eliminar Meta</button>
    </form>
  </div>
</div>
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
</script>
<script>
// Abrir modal y pasar datos
document.querySelectorAll('.editar-meta-btn').forEach(button => {
  button.addEventListener('click', function () {
    document.getElementById('edit-meta-id').value = this.dataset.id;
    document.getElementById('edit-nombre').value = this.dataset.nombre;
    document.getElementById('edit-monto').value = this.dataset.monto;
    document.getElementById('edit-fecha').value = this.dataset.fecha;
    document.getElementById('modal-editar-meta').classList.add('active');
  });
});
// Cerrar modal
function cerrarModalMeta() {
  document.getElementById('modal-editar-meta').classList.remove('active');
}
// Cerrar al hacer clic fuera
document.getElementById('modal-editar-meta').addEventListener('click', function (e) {
  if (e.target === this) cerrarModalMeta();
});
// Cerrar con ESC
document.addEventListener('keydown', function (e) {
  if (e.key === 'Escape') cerrarModalMeta();
});
</script>
<script>
// Eliminar meta usando fetch desde botón del modal
document.getElementById('eliminar-meta-btn').addEventListener('click', function () {
  const metaId = document.getElementById('edit-meta-id').value;
  if (!metaId) {
    alert('ID de meta no válido.');
    return;
  }
  if (confirm('¿Estás seguro de eliminar esta meta de ahorro?')) {
    fetch('controllers/eliminar_meta.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ meta_id: metaId })
    })
    .then(res => res.json())
    .then(data => {
      if (data.success) {
        cerrarModalMeta();
        location.reload();
      } else {
        alert('Error al eliminar: ' + data.error);
      }
    })
    .catch(err => {
      alert('Error de red: ' + err.message);
    });
  }
});
</script>
<script>
  // VARIABLES GLOBALES
  const listaNotificaciones = document.getElementById('lista-notificaciones');
  const badgeAlerta = document.getElementById('badge-alerta');
  const iconoCampana = document.getElementById('icono-campana');
  const panelNotificaciones = document.getElementById('panel-notificaciones');

  const metas = <?= json_encode($lista_metas ?? []) ?>;
  const notificaciones = [];

  const hoy = new Date();

  metas.forEach(meta => {
    if (!meta || !meta.fecha_limite || !meta.nombre) return;

    const fechaLimite = new Date(meta.fecha_limite);
    const diasRestantes = Math.ceil((fechaLimite - hoy) / (1000 * 60 * 60 * 24));
    const porcentaje = meta.monto_objetivo > 0
      ? (parseFloat(meta.total_aportado || 0) / meta.monto_objetivo) * 100
      : 0;

    // Notificación: meta creada hace menos de 1 día
    const fechaCreacion = new Date(meta.created_at);
    const horasDesdeCreacion = Math.abs(hoy - fechaCreacion) / 36e5;
    if (horasDesdeCreacion <= 24) {
      notificaciones.push(`Nueva meta "${meta.nombre}" creada hoy.`);
    }

    // Notificación: sin fecha límite
    if (!meta.fecha_limite) {
      notificaciones.push(`La meta "${meta.nombre}" no tiene fecha límite.`);
    }

    // Notificación: cerca de vencer y no alcanzada
    if (diasRestantes <= 5 && porcentaje < 100 && diasRestantes >= 0) {
      notificaciones.push(`Meta "${meta.nombre}" vence en ${diasRestantes} día(s).`);
    }

    // Notificación: vencida sin lograr
    if (diasRestantes < 0 && porcentaje < 100) {
      notificaciones.push(`Meta "${meta.nombre}" venció sin cumplirse.`);
    }

    // Notificación: alcanzada
    if (porcentaje >= 100) {
      notificaciones.push(`Meta "${meta.nombre}" alcanzada.`);
    }

    // Notificación: sin aportes en 7 días
    const fechaUltimoAporte = new Date(meta.updated_at);
    const diasSinAportes = Math.floor((hoy - fechaUltimoAporte) / (1000 * 60 * 60 * 24));
    if (diasSinAportes >= 7 && porcentaje < 100) {
      notificaciones.push(`Meta "${meta.nombre}" no tiene aportes hace ${diasSinAportes} días.`);
    }
  });

  // Renderizar notificaciones
  if (listaNotificaciones) {
    listaNotificaciones.innerHTML = '';

    if (notificaciones.length > 0) {
      badgeAlerta.style.display = 'inline-block';
      iconoCampana.classList.add('shake');

      notificaciones.forEach(msg => {
        const li = document.createElement('li');
        li.textContent = msg;
        listaNotificaciones.appendChild(li);
      });
    } else {
      const li = document.createElement('li');
      li.textContent = 'Sin notificaciones.';
      listaNotificaciones.appendChild(li);
    }
  }

  // Toggle de panel
  function toggleNotificaciones() {
    if (!panelNotificaciones) return;

    if (panelNotificaciones.style.display === 'flex') {
      panelNotificaciones.style.display = 'none';
    } else {
      panelNotificaciones.style.display = 'flex';
      iconoCampana.classList.remove('shake');
      badgeAlerta.style.display = 'none';
    }
  }

  // Cerrar al hacer clic fuera
  document.addEventListener('click', function (e) {
    const boton = document.getElementById('btn-notificaciones');
    if (
      panelNotificaciones &&
      panelNotificaciones.style.display === 'flex' &&
      !panelNotificaciones.contains(e.target) &&
      !boton.contains(e.target)
    ) {
      panelNotificaciones.style.display = 'none';
    }
  });
</script>
<?php include 'includes/footer.php'; ?>