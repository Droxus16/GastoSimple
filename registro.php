<?php
session_start();
require_once 'includes/db.php';
require_once 'includes/auth.php';
require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Writer\Pdf\Mpdf;
use PhpOffice\PhpSpreadsheet\IOFactory;

$conn = db::conectar();
$idUsuario = intval($_SESSION['usuario_id']);

// Obtener transacciones
$transacciones = [];
$sql = "SELECT id, tipo, fecha, monto, categoria, descripcion FROM transacciones WHERE id_usuario = ? ORDER BY fecha DESC";
$stmt = $conn->prepare($sql);
$stmt->execute([$idUsuario]);
$transacciones = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Obtener categorías
$sqlCategorias = "SELECT id, nombre, tipo FROM categorias WHERE usuario_id = ? OR usuario_id IS NULL ORDER BY tipo, nombre";
$stmtCategorias = $conn->prepare($sqlCategorias);
$stmtCategorias->execute([$idUsuario]);
$categorias = $stmtCategorias->fetchAll(PDO::FETCH_ASSOC);
?>
<?php include 'includes/header.php'; ?>
<link rel="stylesheet" href="assets/css/estilos.css">
<script src="https://cdn.jsdelivr.net/npm/particles.js@2.0.0"></script>

<style>
  /* Tu mismo diseño: NO se toca, solo revisado */
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

  .form-container h2 { text-align: center; margin-bottom: 20px; font-weight: 700; }

  .tabla-container { overflow-x: auto; display: block; margin: 20px auto; }

  .tabla-transacciones {
    width: 100%; border-collapse: collapse; min-width: 600px; margin-top: 10px;
  }
  .tabla-transacciones th, .tabla-transacciones td {
    border: 1px solid #ccc; padding: 8px; text-align: center;
    background-color: rgba(0, 0, 0, 0.3); color: white;
  }
  .tabla-transacciones th {
    background-color: rgba(255, 255, 255, 0.15); font-weight: bold;
  }
  @media (max-width: 768px) {
    .tabla-container::after {
      content: '← desliza la tabla →';
      display: block; text-align: center; font-size: 0.8rem;
      color: #bbb; margin-top: 5px;
    }
  }

  input, select, textarea {
    width: 100%; padding: 10px; margin-bottom: 15px;
    border-radius: 6px; border: none; font-size: 1rem; box-sizing: border-box;
  }

  .acciones {
    display: flex; gap: 10px; justify-content: center; flex-wrap: wrap;
  }

  button {
    background-color: #00D4FF; border: none; padding: 12px 20px;
    border-radius: 8px; cursor: pointer; font-weight: bold;
    color: #0C1634; font-size: 1rem; transition: background-color 0.3s ease;
  }

  button:hover { background-color: #00b8e6; }

  #particles-js {
    position: absolute; top: 0; left: 0; width: 100%; height: 100%;
    z-index: 0;
  }

  .modal-overlay { position: fixed; top: 0; left: 0; width: 100%; height: 100%;
    background-color: rgba(0, 0, 0, 0.7);
    display: none; justify-content: center; align-items: center;
    transition: opacity 0.3s ease; z-index: 1000; opacity: 0;
  }
  .modal-overlay.active { display: flex; opacity: 1; }
  .modal-content {
    background-color: rgba(0, 0, 0, 0.85); color: white; border-radius: 10px;
    padding: 30px; width: 90%; max-width: 600px; max-height: 90%; overflow-y: auto;
    position: relative; box-shadow: 0 0 20px rgba(255, 255, 255, 0.1);
  }
  .modal-close { position: absolute; top: 10px; right: 15px; font-size: 1.5rem; cursor: pointer; color: white; }
</style>

<div id="particles-js"></div>

<!-- Formulario registro -->
<div class="form-container">
  <h2>Registrar Gasto o Ingreso</h2>
  <form id="form-registro" action="includes/insertar_transaccion.php" method="POST">
    <label for="tipo">Tipo:</label>
    <select name="tipo" id="tipo" required onchange="filtrarCategorias()">
      <option value="">-- Selecciona --</option>
      <option value="ingreso">Ingreso</option>
      <option value="gasto">Gasto</option>
    </select>
    <label for="fecha">Fecha:</label>
    <input type="date" name="fecha" required>
    <label for="monto">Monto:</label>
    <input type="number" name="monto" step="0.01" required>
    <label for="categoria">Categoría:</label>
    <select name="categoria" id="categoria" required onchange="mostrarCampoNuevaCategoria(this)">
      <option value="">-- Selecciona categoría --</option>
      <?php foreach ($categorias as $cat): ?>
        <option value="<?= $cat['id'] ?>" data-tipo="<?= $cat['tipo'] ?>">
          <?= htmlspecialchars(ucfirst($cat['tipo']) . " - " . $cat['nombre']) ?>
        </option>
      <?php endforeach; ?>
      <option value="nueva">+ Agregar nueva categoría</option>
    </select>
    <div id="nueva-categoria-container" style="display:none;">
      <label for="nueva_categoria">Nueva Categoría:</label>
      <input type="text" name="nueva_categoria" id="nueva_categoria">
    </div>
    <label for="descripcion">Descripción:</label>
    <textarea name="descripcion" rows="2"></textarea>
    <button type="submit">Guardar Registro</button>
  </form>
</div>

<!-- Modal Editar -->
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

<!-- Tabla -->
<div class="tabla-container">
  <h2>Registros</h2>
  <?php if (count($transacciones) > 0): ?>
    <div class="acciones">
      <!-- Exportar -->
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
                data-id="<?= $fila['id'] ?>"
                data-tipo="<?= $fila['tipo'] ?>"
                data-fecha="<?= $fila['fecha'] ?>"
                data-monto="<?= $fila['monto'] ?>"
                data-categoria="<?= htmlspecialchars($fila['categoria']) ?>"
                data-descripcion="<?= htmlspecialchars($fila['descripcion']) ?>">
                Editar
              </button>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  <?php else: ?>
    <p>No hay datos registrados aún.</p>
  <?php endif; ?>
</div>

<script>
// Modal editar
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
// Eliminar con fetch
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
// Categorías dinámicas
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
// Particles
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
</script>
<?php include 'includes/footer.php'; ?>
