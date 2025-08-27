<?php
session_start();
require_once 'includes/db.php';
require_once 'includes/auth.php';
require 'vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Writer\Pdf\Mpdf;
use PhpOffice\PhpSpreadsheet\IOFactory;
//Asegurar zona horaria correcta
date_default_timezone_set('America/Bogota');

$conn = db::conectar();
$idUsuario = intval($_SESSION['usuario_id']);

//Obtener ajustes del usuario
$sqlUsuario = "SELECT ingreso_minimo FROM usuarios WHERE id = ?";
$stmtUsuario = $conn->prepare($sqlUsuario);
$stmtUsuario->execute([$idUsuario]);
$usuario = $stmtUsuario->fetch(PDO::FETCH_ASSOC);
$ingresoMinimo = floatval($usuario['ingreso_minimo'] ?? 0);

//Obtener todas las transacciones (vista o tabla) para mostrar en la tabla
$sqlTodos = "SELECT id_transaccion, tipo, fecha, monto, categoria, descripcion 
             FROM transacciones 
             WHERE id_usuario = ?
             ORDER BY fecha DESC";
$stmtTodos = $conn->prepare($sqlTodos);
$stmtTodos->execute([$idUsuario]);
$transacciones = $stmtTodos->fetchAll(PDO::FETCH_ASSOC);

// Obtener ingresos del mes actual directamente desde la tabla ingresos
$inicioMes = date('Y-m-01');
$finMes    = date('Y-m-t');
$sqlIngresos = "SELECT monto FROM ingresos 
                WHERE usuario_id = ? AND DATE(fecha) BETWEEN ? AND ?";
$stmtIngresos = $conn->prepare($sqlIngresos);
$stmtIngresos->execute([$idUsuario, $inicioMes, $finMes]);
$ingresosMes = $stmtIngresos->fetchAll(PDO::FETCH_ASSOC);
// Obtener gastos del mes actual directamente desde la tabla gastos
$sqlGastos = "SELECT monto FROM gastos 
              WHERE usuario_id = ? AND DATE(fecha) BETWEEN ? AND ?";
$stmtGastos = $conn->prepare($sqlGastos);
$stmtGastos->execute([$idUsuario, $inicioMes, $finMes]);
$gastosMes = $stmtGastos->fetchAll(PDO::FETCH_ASSOC);
// Calcular totales
$totalIngresos = array_sum(array_column($ingresosMes, 'monto'));
$totalGastos   = array_sum(array_column($gastosMes, 'monto'));
// Obtener categorías
$sqlCategorias = "SELECT id, nombre, tipo FROM categorias 
                  WHERE usuario_id = ? OR usuario_id IS NULL 
                  ORDER BY tipo, nombre";
$stmtCategorias = $conn->prepare($sqlCategorias);
$stmtCategorias->execute([$idUsuario]);
$categorias = $stmtCategorias->fetchAll(PDO::FETCH_ASSOC);
?>
<script src="js/notificaciones.js" defer></script>
<?php include 'includes/header.php'; ?>
<body 
  data-total-ingresos="<?= $totalIngresos ?>" 
  data-total-gastos="<?= $totalGastos ?>" 
  data-ingreso-minimo="<?= $ingresoMinimo ?>">
<link rel="stylesheet" href="assets/css/estilos.css">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/particles.js@2.0.0"></script>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
<style>
  /*CONTENEDORES PRINCIPALES */
  .form-container, .tabla-container {
    background-color: rgba(255,255,255,0.07);
    padding: 20px;
    border-radius: 15px;
    backdrop-filter: blur(5px);
    margin-bottom: 20px;
    color: white;
    max-width: 900px;
    margin-left: auto;
    margin-right: auto;
    position: relative;
    z-index: 1;
  }
  .form-container h2 {
    text-align: center;
    margin-bottom: 20px;
    font-weight: 700;
  }
  .tabla-container {
    overflow-x: auto;
    display: block;
    margin: 20px auto;
  }
  .tabla-transacciones {
    width: 100%;
    border-collapse: collapse;
    min-width: 600px;
    margin-top: 10px;
  }
  .tabla-transacciones th, .tabla-transacciones td {
    border: 1px solid #ccc;
    padding: 8px;
    text-align: center;
    background-color: rgba(0, 0, 0, 0.3);
    color: white;
  }
  .tabla-transacciones th {
    background-color: rgba(255, 255, 255, 0.15);
    font-weight: bold;
  }
  @media (max-width: 768px) {
    .tabla-container::after {
      content: '← desliza la tabla →';
      display: block;
      text-align: center;
      font-size: 0.8rem;
      color: #bbb;
      margin-top: 5px;
    }
  }
  input, select, textarea {
    width: 100%;
    padding: 10px;
    margin-bottom: 15px;
    border-radius: 6px;
    border: none;
    font-size: 1rem;
    box-sizing: border-box;
  }
  .acciones {
    display: flex;
    gap: 10px;
    justify-content: center;
    flex-wrap: wrap;
  }
  button {
    background-color: #00D4FF;
    border: none;
    padding: 12px 20px;
    border-radius: 8px;
    cursor: pointer;
    font-weight: bold;
    color: #0C1634;
    font-size: 1rem;
    transition: background-color 0.3s ease;
  }
  button:hover {
    background-color: #00b8e6;
  }
  #particles-js {
    position: absolute;
    top: 0; left: 0;
    width: 100%; height: 100%;
    z-index: 0;
  }
  /*
     MODAL CORRECTAMENTE CENTRADO*/
  .modal-overlay {
    position: fixed;
    top: 0; left: 0;
    width: 100%; height: 100%;
    background-color: rgba(0, 0, 0, 0.7);
    display: none; /* Oculto por defecto */
    justify-content: center; /* Centra horizontal */
    align-items: center;     /* Centra vertical */
    transition: opacity 0.3s ease;
    z-index: 1000; /* Encima de todo */
    opacity: 0;
  }
  .modal-overlay.active {
    display: flex;
    opacity: 1;
  }
  .modal-content {
    background-color: rgba(0, 0, 0, 0.9);
    color: white;
    border-radius: 10px;
    padding: 30px;
    width: 90%;
    max-width: 600px;
    max-height: 90%;
    overflow-y: auto;
    position: relative;
    box-shadow: 0 0 20px rgba(255, 255, 255, 0.2);
    transform: translateY(-20px); /* Animación entrada */
    transition: transform 0.3s ease;
  }
  .modal-overlay.active .modal-content {
    transform: translateY(0);
  }
  .modal-close {
    position: absolute;
    top: 10px;
    right: 15px;
    font-size: 1.5rem;
    cursor: pointer;
    color: white;
  }
  /*DASHBOARD OPCIONAL */
  .dashboard-container {
    display: flex;
    height: 100vh;
    gap: 20px;
    padding: 20px;
    box-sizing: border-box;
    overflow: hidden;
    position: relative;
  }
  .sidebar {
    width: 220px;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
  }
  .sidebar .menu-top, .sidebar .menu-bottom {
    display: flex;
    flex-direction: column;
    gap: 15px;
  }
.sidebar button {
  display: flex;
  align-items: center;
  gap: 14px;
  padding: 14px 18px;
  font-size: 1.08rem;
  border: none;
  border-radius: 16px;
  background: linear-gradient(90deg, rgba(0,212,255,0.13) 0%, rgba(11,20,60,0.92) 100%);
  color: #e0f7fa;
  font-weight: 600;
  cursor: pointer;
  transition: 
    background 0.18s, 
    color 0.18s, 
    box-shadow 0.18s, 
    transform 0.18s;
  box-shadow: 0 2px 12px rgba(0,212,255,0.08);
  margin-bottom: 8px;
  position: relative;
  outline: none;
}
  .sidebar button:hover {
    background-color: #00D4FF;
    color: #0C1634;
    transform: scale(1.05);
  }
.sidebar button i {
  font-size: 1.35em;
  color: #00D4FF;
  transition: color 0.18s;
}
.sidebar button:hover, .sidebar button:focus {
  background: linear-gradient(90deg, #00D4FF 0%, #1D2B64 100%);
  color: #fff;
  box-shadow: 0 4px 18px rgba(0,212,255,0.18);
  transform: translateY(-2px) scale(1.04);
}
.sidebar button:hover i, .sidebar button:focus i {
  color: #fff;
}
.menu-top, .menu-bottom {
  margin-bottom: 18px;
}
.sidebar .menu-bottom {
  border-top: 1.5px solid rgba(0,212,255,0.13);
  padding-top: 18px;
  margin-top: 18px;
}
#btn-notificaciones {
  background: linear-gradient(90deg, rgba(0,212,255,0.18) 0%, rgba(11,20,60,0.92) 100%);
  color: #00D4FF;
  font-weight: 700;
  position: relative;
}
#btn-notificaciones:hover, #btn-notificaciones:focus {
  background: linear-gradient(90deg, #00D4FF 0%, #1D2B64 100%);
  color: #fff;
}
#btn-notificaciones i {
  color: #00D4FF;
}
#btn-notificaciones:hover i, #btn-notificaciones:focus i {
  color: #fff;
}
#badge-alerta {
  background: #FF6B6B;
  border-radius: 50%;
  width: 12px;
  height: 12px;
  display: inline-block;
  margin-left: 8px;
  border: 2px solid #fff;
  box-shadow: 0 0 6px #FF6B6B;
}
@media (max-width: 768px) {
  .sidebar button {
    font-size: 1rem;
    padding: 10px 8px;
    border-radius: 12px;
    gap: 8px;
  }
}
  .main-content {
    flex: 1;
    overflow-y: auto;
    background: rgba(255,255,255,0.05);
    padding: 25px;
    border-radius: 20px;
    backdrop-filter: blur(10px);
    color: white;
    box-sizing: border-box;
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

  /* Animación suave personalizada */
.collapse-custom {
  max-height: 0;
  opacity: 0;
  overflow: hidden;
  transition: max-height 0.5s ease, opacity 0.5s ease;
}

.collapse-custom.show {
  max-height: 500px; /* Ajustable según el contenido */
  opacity: 1;
}
.glass-card {
  background: rgba(255, 255, 255, 0.13);
  border: 1.5px solid rgba(0,212,255,0.18);
  border-radius: 22px;
  backdrop-filter: blur(18px);
  box-shadow: 0 12px 40px rgba(0,212,255,0.13), 0 4px 16px rgba(0,0,0,0.13);
  z-index: 1;
  padding: 38px 32px 32px 32px;
  text-align: center;
  position: relative;
  transition: box-shadow 0.2s;
  max-width: 600px;
  margin: 0 auto 24px auto;
}
.glass-card h2 {
  color: #00D4FF;
  text-align: center;
  font-weight: 700;
  margin-bottom: 18px;
  letter-spacing: 1px;
}
.btn-info, .btn-info:focus {
  background: linear-gradient(90deg, #00D4FF 0%, #1D2B64 100%);
  border: none;
  color: #fff;
  font-weight: 600;
  font-size: 1.08rem;
  box-shadow: 0 2px 12px rgba(0,212,255,0.10);
  transition: background 0.18s, color 0.18s, transform 0.18s;
}
.btn-info:hover {
  background: #fff;
  color: #00D4FF;
  transform: translateY(-2px) scale(1.04);
}
.form-control:focus, .form-select:focus {
  border-color: #00D4FF;
  box-shadow: 0 0 0 2px rgba(0,212,255,0.18);
  background: rgba(255,255,255,0.09);
  color: #fff;
}
.logo-register {
  width: 54px;
  height: 54px;
  object-fit: contain;
  margin-bottom: 10px;
  background: transparent;
  border-radius: 12px;
  box-shadow: 0 2px 8px rgba(0,212,255,0.13);
}
select, select option {
  color: #222 !important; /* O el color que prefieras para contraste */
  background: #fff !important; /* Opcional: mejora la visibilidad del desplegable */
}
.form-select, .form-select option {
  color: #222 !important;
  background: #fff !important;
}
</style>
<div id="particles-js"></div>
<div class="dashboard-container">
  <div class="sidebar">
    <div class="menu-top">
      <button onclick="location.href='dashboard.php'">
        <i class="bi bi-pie-chart-fill"></i> Panel
      <button onclick="location.href='metas.php'"><i class="bi bi-flag-fill"></i> Metas</button>
    </div>
    <button id="btn-notificaciones" onclick="toggleNotificaciones()">
      <i id="icono-campana" class="bi bi-bell-fill"></i> Notificaciones
    <span id="badge-alerta" style="display: none;"></span>
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
 <!-- Contenido principal -->
<div class="main-content">
  <!-- Formulario de registro -->
<div class="form-container glass-card">
  <img src="img/logo 1.png" alt="Logo GastoSimple" class="logo-register" style="width:54px;height:54px;margin-bottom:10px;">
  <h2>Registrar Gasto o Ingreso</h2>
  <form id="form-registro" action="includes/insertar_transaccion.php" method="POST">
    <div class="mb-3 text-start">
      <label for="tipo" class="form-label">Tipo:</label>
      <select name="tipo" id="tipo" class="form-select" required onchange="filtrarCategorias()">
        <option value="">-- Selecciona --</option>
        <option value="ingreso">Ingreso</option>
        <option value="gasto">Gasto</option>
      </select>
    </div>
    <div class="mb-3 text-start">
      <label for="fecha" class="form-label">Fecha:</label>
      <input type="date" name="fecha" class="form-control" required>
    </div>
    <div class="mb-3 text-start">
      <label for="monto" class="form-label">Monto:</label>
      <input type="number" name="monto" class="form-control" step="0.01" required>
    </div>
    <div class="mb-3 text-start">
      <label for="categoria" class="form-label">Categoría:</label>
      <select name="categoria" id="categoria" class="form-select" required onchange="mostrarCampoNuevaCategoria(this)">
        <option value="">-- Selecciona categoría --</option>
        <?php foreach ($categorias as $cat): ?>
          <option value="<?= $cat['id'] ?>" data-tipo="<?= $cat['tipo'] ?>">
            <?= htmlspecialchars(ucfirst($cat['tipo']) . " - " . $cat['nombre']) ?>
          </option>
        <?php endforeach; ?>
        <option value="nueva">+ Agregar nueva categoría</option>
      </select>
    </div>
    <div id="nueva-categoria-container" style="display:none;">
      <label for="nueva_categoria" class="form-label">Nueva Categoría:</label>
      <input type="text" name="nueva_categoria" id="nueva_categoria" class="form-control">
    </div>
    <div class="mb-3 text-start">
      <label for="descripcion" class="form-label">Descripción:</label>
      <textarea name="descripcion" class="form-control" rows="2"></textarea>
    </div>
    <!-- NUEVA SECCIÓN DE CONFIGURACIÓN RECURRENTE -->
    <div class="form-check form-switch my-3 text-start">
      <input class="form-check-input" type="checkbox" role="switch" id="recurrente" name="recurrente">
      <label class="form-check-label fw-bold text-white" for="recurrente">
        <i class="bi bi-arrow-repeat me-1"></i> Registrar automáticamente
      </label>
    </div>
    <div id="config-recurrente" class="recurrente-config collapse-custom">
      <label for="frecuencia" class="form-label text-white">
        <i class="bi bi-calendar2-week-fill me-1"></i> Frecuencia:
      </label>
      <select class="form-select mb-3" name="frecuencia" id="frecuencia">
        <option value="mensual">Mensual</option>
        <option value="quincenal">Quincenal</option>
        <option value="semanal">Semanal</option>
      </select>
      <label for="dia_fijo" class="form-label text-white">
        <i class="bi bi-clock-fill me-1"></i> Día de ejecución (1-31):
      </label>
      <input type="number" class="form-control mb-3" name="dia_fijo" id="dia_fijo" min="1" max="31">
      <div class="form-check mt-2">
        <input class="form-check-input" type="checkbox" id="monto_variable" name="monto_variable">
        <label class="form-check-label text-white" for="monto_variable">
          <i class="bi bi-exclamation-circle-fill me-1"></i> Monto variable (pedir confirmación)
        </label>
      </div>
    </div>
    <button type="submit" class="btn btn-info w-100 mt-2">Guardar Registro</button>
  </form>
</div>

    <!-- Tabla de registros -->
    <div class="tabla-container">
      <h2>Registros</h2>
      <?php if (count($transacciones) > 0): ?>
        <div class="acciones">
          <form action="controllers/reportes.php" method="POST">
            <input type="hidden" name="exportar_excel" value="1">
            <button type="submit">Exportar a Excel</button>
          </form>
          <form action="controllers/reportes.php" method="POST">
            <input type="hidden" name="exportar_pdf" value="1">
            <button type="submit">Exportar a PDF</button>
          </form>
        </div>
        <table class="tabla-transacciones">
          <thead>
            <tr>
              <th>Tipo</th>
              <th>Fecha</th>
              <th>Monto</th>
              <th>Categoría</th>
              <th>Descripción</th>
              <th>Acciones</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($transacciones as $fila): ?>
              <tr>
                <td><?= ucfirst($fila['tipo']) ?></td>
                <td><?= htmlspecialchars($fila['fecha']) ?></td>
                <td>$<?= number_format($fila['monto'], 2) ?></td>
                <td><?= htmlspecialchars($fila['categoria']) ?></td>
                <td><?= htmlspecialchars($fila['descripcion']) ?></td>
                <td>
                  <button type="button" class="editar-btn"
                    data-id="<?= htmlspecialchars($fila['id_transaccion']) ?>"
                    data-tipo="<?= htmlspecialchars($fila['tipo']) ?>"
                    data-fecha="<?= htmlspecialchars($fila['fecha']) ?>"
                    data-monto="<?= htmlspecialchars($fila['monto']) ?>"
                    data-categoria="<?= htmlspecialchars($fila['categoria']) ?>"
                    data-descripcion="<?= htmlspecialchars($fila['descripcion']) ?>"
                  >Editar</button>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      <?php else: ?>
        <p>No hay datos registrados aún.</p>
      <?php endif; ?>
    </div>
  </div>
</div>
<!-- Modal Editar global-->
<div id="modal-editar" class="modal-overlay">
  <div class="modal-content">
    <span class="modal-close" onclick="cerrarModalEditar()">&times;</span>
    <h2>Editar Transacción</h2>
    <form id="form-editar" action="includes/editar_transaccion.php" method="POST">
      <input type="hidden" id="edit-id" name="id">
      <label for="edit-tipo">Tipo:</label>
      <select name="tipo" id="edit-tipo" required>
        <option value="ingreso">Ingreso</option>
        <option value="gasto">Gasto</option>
      </select>
      <label for="edit-fecha">Fecha:</label>
      <input type="date" name="fecha" id="edit-fecha" required>
      <label for="edit-monto">Monto:</label>
      <input type="number" name="monto" id="edit-monto" step="0.01" required>
      <label for="edit-categoria">Categoría:</label>
      <select name="categoria" id="edit-categoria" required>
        <?php foreach ($categorias as $cat): ?>
          <option value="<?= $cat['id'] ?>"><?= htmlspecialchars(ucfirst($cat['tipo']) . " - " . $cat['nombre']) ?></option>
        <?php endforeach; ?>
      </select>
      <label for="edit-descripcion">Descripción:</label>
      <textarea name="descripcion" id="edit-descripcion" rows="2"></textarea>
      <button type="submit">Guardar Cambios</button>
      <button type="button" id="eliminar-transaccion">Eliminar Transacción</button>
    </form>
  </div>
</div>
<script>
document.getElementById('recurrente').addEventListener('change', function () {
  const config = document.getElementById('config-recurrente');
  if (this.checked) {
    config.classList.add('show');
  } else {
    config.classList.remove('show');
  }
});
</script>
<script>
function toggleRecurrente() {
  const container = document.getElementById('config-recurrente');
  const checked = document.getElementById('recurrente').checked;
  container.style.display = checked ? 'block' : 'none';
}
</script>
<script>
particlesJS("particles-js", {
  particles: {
    number: { value: 80, density: { enable: true, value_area: 800 } },
    color: { value: "#00D4FF" },
    shape: { type: "circle" },
    opacity: { value: 0.5, anim: { enable: true, speed: 1, opacity_min: 0.1 } },
    size: { value: 5, random: true, anim: { enable: true, speed: 40, size_min: 0.1 } },
    line_linked: { enable: true, distance: 150, color: "#ffffff", opacity: 0.4, width: 1 },
    move: { enable: true, speed: 6, out_mode: "out" }
  },
  interactivity: {
    detect_on: "canvas",
    events: {
      onhover: { enable: true, mode: "repulse" },
      onclick: { enable: true, mode: "push" }
    }
  },
  retina_detect: true
});
//Categorías dinámicas
function filtrarCategorias() {
  const tipo = document.getElementById('tipo').value;
  const select = document.getElementById('categoria');
  select.querySelectorAll('option').forEach(op => {
    if (op.value === '') return;
    if (op.value === 'nueva') { op.style.display = 'block'; return; }
    op.style.display = op.dataset.tipo === tipo ? 'block' : 'none';
  });
  select.value = ''; document.getElementById('nueva-categoria-container').style.display = 'none';
}
function mostrarCampoNuevaCategoria(select) {
  document.getElementById('nueva-categoria-container').style.display = select.value === 'nueva' ? 'block' : 'none';
}
// Modal Editar
document.querySelectorAll('.editar-btn').forEach(button => {
  button.addEventListener('click', function() {
    document.getElementById('edit-id').value = this.dataset.id;
    document.getElementById('edit-tipo').value = this.dataset.tipo;
    document.getElementById('edit-fecha').value = this.dataset.fecha;
    document.getElementById('edit-monto').value = this.dataset.monto;
    document.getElementById('edit-categoria').value = this.dataset.categoria;
    document.getElementById('edit-descripcion').value = this.dataset.descripcion;
    document.getElementById('modal-editar').classList.add('active');
  });
});
function cerrarModalEditar() {
  document.getElementById('modal-editar').classList.remove('active');
}
document.getElementById('modal-editar').addEventListener('click', function(e) {
  if (e.target === this) cerrarModalEditar();
});
document.addEventListener('keydown', function(e) {
  if (e.key === 'Escape') cerrarModalEditar();
});
document.getElementById('eliminar-transaccion').addEventListener('click', function() {
  const id = document.getElementById('edit-id').value;
  if (confirm('¿Seguro de eliminar?')) {
    fetch('includes/eliminar_transaccion.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ id: id })
    })
    .then(res => res.json())
    .then(data => {
      if (data.success) {
        cerrarModalEditar(); location.reload();
      } else {
        alert('Error: ' + data.error);
      }
    }).catch(err => alert('Error: ' + err));
  }
});
</script>
<?php include 'includes/footer.php'; ?>