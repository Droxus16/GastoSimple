<?php
session_start();
require_once 'includes/db.php';
require_once 'includes/auth.php';

$conn = db::conectar();
$idUsuario = $_SESSION['usuario_id'];

// Obtener datos del usuario
$stmt = $conn->prepare("SELECT nombre, correo, pregunta_secreta, ingreso_minimo, saldo_minimo FROM usuarios WHERE id = ?");
$stmt->execute([$idUsuario]);
$usuario = $stmt->fetch(PDO::FETCH_ASSOC);

// Totales para notificaciones
$totales = $conn->prepare("
  SELECT
    (SELECT COALESCE(SUM(monto),0) FROM ingresos WHERE usuario_id = :usuario_id AND MONTH(fecha) = MONTH(CURDATE()) AND YEAR(fecha) = YEAR(CURDATE())) AS total_ingresos,
    (SELECT COALESCE(SUM(monto),0) FROM gastos WHERE usuario_id = :usuario_id AND MONTH(fecha) = MONTH(CURDATE()) AND YEAR(fecha) = YEAR(CURDATE())) AS total_gastos,
    (SELECT COALESCE(SUM(monto),0) FROM aportes_ahorro a JOIN metas_ahorro m ON a.meta_id = m.id WHERE m.usuario_id = :usuario_id AND MONTH(a.fecha) = MONTH(CURDATE()) AND YEAR(a.fecha) = YEAR(CURDATE())) AS total_aportes
");
$totales->execute(['usuario_id' => $idUsuario]);
$datos = $totales->fetch(PDO::FETCH_ASSOC);

$totalIngresos = $datos['total_ingresos'];
$totalGastos = $datos['total_gastos'];
$totalAportes = $datos['total_aportes'];
$saldoActual = $totalIngresos - $totalGastos - $totalAportes;
?>

<?php include 'includes/header.php'; ?>
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
#particles-js { position: fixed; top: 0; left: 0; width: 100%; height: 100%; z-index: -1; }
.dashboard-container { display: flex; height: 100vh; padding: 20px; gap: 20px; box-sizing: border-box; }
.sidebar { width: 220px; display: flex; flex-direction: column; justify-content: space-between; }
.sidebar button {
  display: flex; align-items: center; gap: 10px; padding: 12px; font-size: 1rem;
  border: none; border-radius: 12px; background: rgba(255,255,255,0.08); color: #00D4FF;
  font-weight: bold; cursor: pointer; transition: all 0.3s ease; backdrop-filter: blur(6px);
}
.sidebar button:hover { background-color: #00D4FF; color: #0C1634; transform: scale(1.05); }
.notificaciones-dropdown {
  position: absolute; top: 80px; left: 20px; width: 250px;
  background: rgba(0,0,0,0.85); border-radius: 8px; backdrop-filter: blur(6px);
  color: white; display: none; flex-direction: column; padding: 15px; z-index: 999;
}
.notificaciones-dropdown h4 { margin: 0 0 10px; font-size: 1rem; border-bottom: 1px solid #00D4FF; padding-bottom: 5px; }
.notificaciones-dropdown ul { list-style: none; padding: 0; margin: 0; }
.notificaciones-dropdown li { padding: 5px 0; border-bottom: 1px solid rgba(255,255,255,0.1); font-size: 0.9rem; }
.shake { animation: shake 0.5s; }
@keyframes shake { 0%{transform:rotate(0)}20%{transform:rotate(-15deg)}40%{transform:rotate(15deg)}60%{transform:rotate(-10deg)}80%{transform:rotate(10deg)}100%{transform:rotate(0)} }
.main-content { flex: 1; background: rgba(255,255,255,0.07); padding: 30px; border-radius: 20px; backdrop-filter: blur(10px); overflow-y: auto; box-sizing: border-box; display: flex; flex-direction: column; gap: 30px; }
.form-container { background: rgba(255,255,255,0.05); padding: 25px 30px; border-radius: 15px; box-shadow: 0 0 8px rgba(0,212,255,0.2); }
.form-container h2 { font-size: 1.8rem; text-align: center; margin-bottom: 20px; }
label { font-weight: 600; margin-bottom: 6px; display: block; color: #00D4FF; }
input, select, textarea {
  width: 100%; padding: 10px 12px; margin-bottom: 16px; border-radius: 10px; border: none;
  background: rgba(255,255,255,0.1); color: white; font-size: 1rem;
}
textarea { resize: vertical; }
button[type="submit"] { background-color: #00D4FF; color: #0B0B52; font-weight: bold; padding: 10px 20px; border: none; border-radius: 12px; cursor: pointer; transition: all 0.3s ease; }
button[type="submit"]:hover { background-color: #00aacc; }
</style>

<div id="particles-js"></div>
<div class="dashboard-container">
  <div class="sidebar">
    <div class="menu-top">
      <button onclick="location.href='dashboard.php'"><i class="bi bi-pie-chart-fill"></i> Panel</button>
      <button onclick="location.href='registro.php'"><i class="bi bi-pencil-square"></i> Registro</button>
      <button onclick="location.href='metas.php'"><i class="bi bi-flag-fill"></i> Metas</button>
      <button id="btn-notificaciones" onclick="toggleNotificaciones()">
        <i id="icono-campana" class="bi bi-bell-fill"></i> Notificaciones
        <span id="badge-alerta" style="display:none; background:red; border-radius:50%; width:12px; height:12px; display:inline-block; margin-left:5px;"></span>
      </button>
    </div>
    <div id="panel-notificaciones" class="notificaciones-dropdown">
      <h4>Notificaciones</h4>
      <ul id="lista-notificaciones"></ul>
    </div>
    <div class="menu-bottom">
      <button onclick="location.href='logout.php'"><i class="bi bi-box-arrow-right"></i> Salir</button>
    </div>
  </div>

  <div class="main-content">
    <div class="form-container">
      <h2>Editar Perfil</h2>
      <form id="form-ajustes" action="includes/actualizar_usuario.php" method="POST">
        <label for="nombre">Nombre:</label>
        <input type="text" name="nombre" required value="<?= htmlspecialchars($usuario['nombre']) ?>">
        <label for="correo">Correo:</label>
        <input type="email" name="correo" required value="<?= htmlspecialchars($usuario['correo']) ?>">
        <label>Tu Pregunta Secreta:</label>
        <p style="background: rgba(255,255,255,0.1); padding: 10px; border-radius: 8px;"><?= htmlspecialchars($usuario['pregunta_secreta']) ?></p>
        <label for="respuesta_actual">Responde para cambiar clave:</label>
        <input type="text" name="respuesta_actual">
        <label for="respuesta_secreta">Nueva Respuesta (opcional):</label>
        <input type="text" name="respuesta_secreta">
        <label for="nueva_clave">Nueva Contraseña:</label>
        <input type="password" name="nueva_clave">
        <label for="confirmar_clave">Confirmar Nueva Contraseña:</label>
        <input type="password" name="confirmar_clave">
        <label for="ingreso_minimo">Ingreso Mínimo:</label>
        <input type="number" step="0.01" name="ingreso_minimo" value="<?= htmlspecialchars($usuario['ingreso_minimo']) ?>">
        <label for="saldo_minimo">Saldo Mínimo:</label>
        <input type="number" step="0.01" name="saldo_minimo" value="<?= htmlspecialchars($usuario['saldo_minimo']) ?>">
        <button type="submit">Guardar Cambios</button>
      </form>
    </div>

    <div class="form-container">
      <h2>Enviar PQR</h2>
      <form id="form-pqr" method="POST">
        <label for="tipo">Tipo:</label>
        <select name="tipo" required>
          <option value="">-- Selecciona --</option>
          <option value="Pregunta">Pregunta</option>
          <option value="Queja">Queja</option>
          <option value="Reclamo">Reclamo</option>
        </select>
        <label for="asunto">Asunto:</label>
        <input type="text" name="asunto" required>
        <label for="descripcion">Descripción:</label>
        <textarea name="descripcion" rows="4" required></textarea>
        <button type="submit">Enviar PQR</button>
      </form>
    </div>
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

// ✅ DATOS PHP a JS
const saldoActual = Number(<?= json_encode($saldoActual) ?>) || 0;
const ingresosTotales = Number(<?= json_encode($totalIngresos) ?>) || 0;
const ingresoMinimo = Number(<?= json_encode($usuario['ingreso_minimo']) ?>) || 0;
const saldoMinimo = Number(<?= json_encode($usuario['saldo_minimo']) ?>) || 0;

console.log('Debug valores:', { saldoActual, ingresosTotales, ingresoMinimo, saldoMinimo });

const listaNotificaciones = document.getElementById('lista-notificaciones');
const badgeAlerta = document.getElementById('badge-alerta');
const iconoCampana = document.getElementById('icono-campana');
const notificaciones = [];

// ✅ LÓGICA SEGURA
if (saldoActual <= saldoMinimo) notificaciones.push(`⚠️ Saldo bajo: $${saldoActual.toFixed(2)}`);
if (ingresosTotales <= ingresoMinimo) notificaciones.push(`⚠️ Ingresos bajos: $${ingresosTotales.toFixed(2)}`);
if (saldoActual <= 0) notificaciones.push(`⚠️ No generas ahorro este mes.`);

console.log('Notificaciones generadas:', notificaciones);

// ✅ PINTAR NOTIFICACIONES
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
  li.textContent = '✅ Sin notificaciones.';
  listaNotificaciones.appendChild(li);
}

// ✅ TOGGLE NOTIFICACIONES
function toggleNotificaciones() {
  const panel = document.getElementById('panel-notificaciones');
  panel.style.display = (panel.style.display === 'flex') ? 'none' : 'flex';
  iconoCampana.classList.remove('shake');
  badgeAlerta.style.display = 'none';
}

document.addEventListener('click', e => {
  const panel = document.getElementById('panel-notificaciones');
  const boton = document.getElementById('btn-notificaciones');
  if (panel.style.display === 'flex' && !panel.contains(e.target) && !boton.contains(e.target)) {
    panel.style.display = 'none';
  }
});

// ✅ GUARDAR AJUSTES
document.querySelector('#form-ajustes').addEventListener('submit', async function(e) {
  e.preventDefault();
  const form = e.target;
  const data = new FormData(form);
  const ingresoMinimo = parseFloat(data.get('ingreso_minimo')) || 0;
  const saldoMinimo = parseFloat(data.get('saldo_minimo')) || 0;

  if (ingresoMinimo < 0 || saldoMinimo < 0) {
    Swal.fire({ icon: 'error', title: 'Valores inválidos', text: '❌ No pueden ser negativos.' });
    return;
  }

  const response = await fetch(form.action, { method: 'POST', body: data });
  const result = await response.json();
  if (result.success) {
    Swal.fire({ icon: 'success', title: 'Guardado', text: '✅ Cambios actualizados.' });
  } else {
    Swal.fire({ icon: 'error', title: 'Error', text: result.error || 'Error inesperado.' });
  }
});

// ✅ GUARDAR PQR
document.getElementById('form-pqr').addEventListener('submit', async e => {
  e.preventDefault();
  const form = e.target;
  const data = new FormData(form);

  const response = await fetch('includes/guardar_pqr.php', { method: 'POST', body: data });
  const result = await response.json();

  if (result.success) {
    Swal.fire('✅ Éxito', 'Tu PQR fue enviado correctamente.', 'success');
    form.reset();
  } else {
    Swal.fire('❌ Error', result.error || 'Ocurrió un error.', 'error');
  }
});
</script>
<?php include 'includes/footer.php'; ?>
