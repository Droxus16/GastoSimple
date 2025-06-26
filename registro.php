<?php
session_start();
require_once 'includes/db.php';
require_once 'includes/auth.php';

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
?>
<?php include 'includes/header.php'; ?>
<link rel="stylesheet" href="assets/css/estilos.css">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/particles.js"></script>
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

.sidebar button:hover {
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

/* Contenedor visual de formularios y tablas */
.form-container, .tabla-container {
  background: rgba(255, 255, 255, 0.05);
  padding: 25px 30px;
  border-radius: 15px;
  backdrop-filter: blur(5px);
  box-shadow: 0 0 8px rgba(0, 212, 255, 0.2);
}

.form-container h2,
.tabla-container h2 {
  font-size: 1.8rem;
  margin-bottom: 20px;
  text-align: center;
}

label {
  font-weight: 600;
  margin-bottom: 6px;
  display: block;
  color: #00D4FF;
}

input, select, textarea {
  width: 100%;
  padding: 10px 12px;
  margin-bottom: 16px;
  border-radius: 10px;
  border: none;
  background: rgba(255,255,255,0.1);
  color: white;
  font-size: 1rem;
}

input::placeholder,
textarea::placeholder {
  color: rgba(255,255,255,0.6);
}

input:focus, select:focus, textarea:focus {
  outline: none;
  box-shadow: 0 0 6px rgba(0, 212, 255, 0.5);
}

button[type="submit"], .acciones button {
  background-color: #00D4FF;
  color: #0B0B52;
  font-weight: bold;
  padding: 10px 20px;
  border: none;
  border-radius: 12px;
  cursor: pointer;
  transition: all 0.3s ease;
  margin-top: 10px;
}

button[type="submit"]:hover, .acciones button:hover {
  background-color: #00aacc;
}

/* Tabla */
.tabla-transacciones {
  width: 100%;
  border-collapse: collapse;
  margin-top: 15px;
}

.tabla-transacciones th, .tabla-transacciones td {
  padding: 12px;
  text-align: left;
  border-bottom: 1px solid rgba(255,255,255,0.1);
}

.tabla-transacciones th {
  color: #00D4FF;
  font-weight: 700;
  background-color: rgba(255,255,255,0.05);
}

.tabla-transacciones td {
  background-color: rgba(255,255,255,0.02);
}

/* Botón editar */
.editar-btn {
  background-color: #00D4FF;
  color: #0C1634;
  border: none;
  padding: 6px 14px;
  border-radius: 8px;
  cursor: pointer;
  font-weight: 600;
  transition: all 0.3s ease;
}

.editar-btn:hover {
  background-color: #00b8e6;
  transform: scale(1.05);
}

/* Modal */
.modal-overlay {
  position: fixed;
  top: 0; left: 0;
  width: 100%; height: 100%;
  background: rgba(0,0,0,0.6);
  display: none;
  justify-content: center;
  align-items: center;
  z-index: 9999;
}

.modal-overlay.active {
  display: flex;
}

.modal-content {
  background: rgba(0, 0, 0, 0.95);
  padding: 30px;
  border-radius: 15px;
  max-width: 500px;
  width: 90%;
  color: white;
  position: relative;
  box-shadow: 0 0 20px rgba(0, 212, 255, 0.3);
}

.modal-close {
  position: absolute;
  top: 10px;
  right: 15px;
  cursor: pointer;
  font-size: 2rem;
  color: #00D4FF;
}

/* Estilos para selects */
select {
  appearance: none;
  -webkit-appearance: none;
  -moz-appearance: none;
  background-color: rgba(0, 0, 0, 0.8);
  color: white;
  background-image: url("data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' width='10' height='5'><polygon points='0,0 10,0 5,5' fill='%23ffffff'/></svg>");
  background-repeat: no-repeat;
  background-position: right 10px center;
  background-size: 10px;
}

option {
  background-color: #0B0B52;
  color: white;
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
      <button onclick="location.href='registro.php'" class="activo"><i class="bi bi-pencil-square"></i> Registro</button>
      <button onclick="location.href='metas.php'"><i class="bi bi-flag-fill"></i> Metas</button>
    </div>
    <div class="menu-bottom">
      <button onclick="location.href='logout.php'"><i class="bi bi-box-arrow-right"></i> Salir</button>
      <button onclick="location.href='ajustes.php'"><i class="bi bi-gear-fill"></i> Ajustes</button>
    </div>
  </div>
  <div class="main-content">
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
<!-- Tabla -->
<div class="tabla-container">
  <h2>Registros</h2>
  <?php if (count($transacciones) > 0): ?>
    <div class="acciones">
      <!-- Botón exportar a Excel -->
      <form action="controllers/reportes.php" method="POST" style="display:inline;">
        <input type="hidden" name="exportar_excel" value="1">
        <button type="submit">Exportar a Excel</button>
      </form>
      <!-- Botón exportar a PDF -->
      <form action="controllers/reportes.php" method="POST" style="display:inline;">
        <input type="hidden" name="exportar_pdf" value="1">
        <button type="submit">Exportar a PDF</button>
      </form>
    </div>

    <div id="modal-editar" class="modal-overlay">
      <div class="modal-content">
        <span class="modal-close" onclick="cerrarModalEditar()">&times;</span>
        <h2>Editar Transacción</h2>
        <form id="form-editar" action="editar_transaccion.php" method="POST">
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
          <button type="button" id="eliminar-transaccion">Eliminar</button>

        </form>
      </div>
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
              <button class="editar-btn"
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

// Mostrar el modal
document.querySelectorAll('.editar-btn').forEach(button => {
  button.addEventListener('click', function() {
    const id = this.getAttribute('data-id');
    const tipo = this.getAttribute('data-tipo');
    const fecha = this.getAttribute('data-fecha');
    const monto = this.getAttribute('data-monto');
    const categoria = this.getAttribute('data-categoria');
    const descripcion = this.getAttribute('data-descripcion');

    document.getElementById('edit-id').value = id;
    document.getElementById('edit-tipo').value = tipo;
    document.getElementById('edit-fecha').value = fecha;
    document.getElementById('edit-monto').value = monto;
    document.getElementById('edit-categoria').value = categoria;
    document.getElementById('edit-descripcion').value = descripcion;

    // Mostrar el modal
    document.getElementById('modal-editar').classList.add('active');
  });
});

function cerrarModalEditar() {
  document.getElementById('modal-editar').classList.remove('active');
}

document.getElementById('modal-editar').addEventListener('click', function(event) {
  if (event.target === document.getElementById('modal-editar')) {
    cerrarModalEditar();
  }
});
document.addEventListener('keydown', function(event) {
  if (event.key === 'Escape') {
    cerrarModalEditar();
  }
});

// Botón para eliminar
document.getElementById('eliminar-transaccion').addEventListener('click', function() {
    const id = document.getElementById('edit-id').value;  // Obtener el ID de la transacción

    if (confirm('¿Estás seguro de que deseas eliminar esta transacción?')) {
        fetch('includes/eliminar_transaccion.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',  // Enviar como JSON
            },
            body: JSON.stringify({ id: id })  // Enviar el ID en el cuerpo como JSON
        })
        .then(response => {
            if (!response.ok) {  // Verifica si la respuesta fue exitosa (código 2xx)
                throw new Error('Error en la solicitud: ' + response.statusText);
            }
            return response.text();  // Obtén la respuesta como texto
        })
        .then(text => {
            console.log(text);
            try {
                const data = JSON.parse(text);
                if (data.success) {
                    alert('Transacción eliminada correctamente.');
                    cerrarModalEditar();
                } else {
                    alert('Hubo un error al eliminar la transacción: ' + data.error);
                }
            } catch (error) {
                // Si ocurre un error
                console.error('Error al parsear el JSON:', error);
                alert('Hubo un error en la solicitud. Respuesta inesperada del servidor.');
            }
        })
        .catch(error => {
            console.error('Error en la solicitud:', error);
            alert('Error en la solicitud: ' + error.message);
        });
    }
});
// Filtrar categorías
function filtrarCategorias() {
  const tipoSeleccionado = document.getElementById('tipo').value;
  const selectCategorias = document.getElementById('categoria');
  const opciones = selectCategorias.querySelectorAll('option');

  opciones.forEach(op => {
    if (op.value === '') return;
    if (op.value === 'nueva') {
      op.style.display = 'block';
      return;
    }
    const tipo = op.dataset.tipo;
    op.style.display = tipo === tipoSeleccionado ? 'block' : 'none';
  });

  selectCategorias.value = '';
  document.getElementById('nueva-categoria-container').style.display = 'none';
}
function mostrarCampoNuevaCategoria(select) {
  const valor = select.value;
  const contenedor = document.getElementById('nueva-categoria-container');
  contenedor.style.display = valor === 'nueva' ? 'block' : 'none';
}


</script>
    <?php include 'registro_content.php'; ?>
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
<?php include 'includes/footer.php'; ?>
