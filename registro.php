<?php
session_start();
require_once 'includes/db.php';
require_once 'includes/auth.php';

$conn = db::conectar();
$idUsuario = intval($_SESSION['usuario_id']);

// Obtener transacciones
$transacciones = [];
$sql = "SELECT tipo, fecha, monto, categoria, descripcion FROM transacciones WHERE id_usuario = ? ORDER BY fecha DESC";
$stmt = $conn->prepare($sql);
$stmt->execute([$idUsuario]);
$transacciones = $stmt->fetchAll(PDO::FETCH_ASSOC);


// Obtener categorías disponibles
$categorias = [];
$sqlCategorias = "SELECT id, nombre, tipo FROM categorias WHERE usuario_id = ? OR usuario_id IS NULL ORDER BY tipo, nombre";
$stmtCategorias = $conn->prepare($sqlCategorias);
$stmtCategorias->execute([$idUsuario]);
$categorias = $stmtCategorias->fetchAll(PDO::FETCH_ASSOC);

// Separar categorías por tipo
$categoriasIngreso = array_filter($categorias, fn($c) => $c['tipo'] === 'ingreso');
$categoriasGasto = array_filter($categorias, fn($c) => $c['tipo'] === 'gasto');
?>

<?php include 'includes/header.php'; ?>
<link rel="stylesheet" href="assets/css/estilos.css">

<script src="https://cdn.jsdelivr.net/npm/particles.js@2.0.0"></script>

<style>
  .form-container, .tabla-container {
    background-color: rgba(255,255,255,0.07);
    padding: 20px;
    border-radius: 15px;
    backdrop-filter: blur(5px);
    margin-bottom: 20px;
    color: white;
    max-width: 600px;
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

  .tabla-transacciones {
    width: 100%;
    border-collapse: collapse;
    margin-top: 10px;
  }

  .tabla-transacciones th, .tabla-transacciones td {
    border: 1px solid #ccc;
    padding: 8px;
    text-align: center;
    background-color: rgba(0,0,0,0.3);
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

  @media (max-width: 640px) {
    .form-container, .tabla-container {
      padding: 15px;
      max-width: 90%;
    }
    input, select, textarea {
      font-size: 0.9rem;
      padding: 8px;
    }
    button {
      padding: 10px 16px;
      font-size: 0.9rem;
    }
  }

  #particles-js {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    z-index: 0;
  }
</style>

<div id="particles-js"></div>

<!-- Formulario de registro -->
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

<!-- Formulario de edición (inicialmente oculto) -->
<!-- Modal -->
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
    </form>
  </div>
</div>


<!-- Tabla de transacciones -->
<div class="tabla-container">
  <h2>Registros</h2>
  <?php if (count($transacciones) > 0): ?>
    <div class="acciones">
      <form action="includes/exportar_excel.php" method="POST">
        <button type="submit">Exportar Excel</button>
      </form>
      <form action="includes/exportar_pdf.php" method="POST">
        <button type="submit">Exportar PDF</button>
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
             <button class="editar-btn"
                data-tipo="<?= $fila['tipo'] ?>"
                data-fecha="<?= $fila['fecha'] ?>"
                data-monto="<?= $fila['monto'] ?>"
                data-categoria="<?= htmlspecialchars($fila['categoria']) ?>"
                data-descripcion="<?= htmlspecialchars($fila['descripcion']) ?>">
              Editar
            </button>

          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  <?php else: ?>
    <p>No hay datos registrados aún.</p>
  <?php endif; ?>
</div>

<script>
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

      document.getElementById('form-editar-container').style.display = 'block'; // Mostrar el formulario de edición
    });
  });

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

  // Configuración de particles.js
  particlesJS("particles-js", {
    particles: {
      number: {
        value: 80,
        density: {
          enable: true,
          value_area: 800
        }
      },
      color: {
        value: "#00D4FF"
      },
      shape: {
        type: "circle",
        stroke: {
          width: 0,
          color: "#000000"
        }
      },
      opacity: {
        value: 0.5,
        random: false,
        anim: {
          enable: true,
          speed: 1,
          opacity_min: 0.1,
          sync: false
        }
      },
      size: {
        value: 5,
        random: true,
        anim: {
          enable: true,
          speed: 40,
          size_min: 0.1,
          sync: false
        }
      },
      line_linked: {
        enable: true,
        distance: 150,
        color: "#ffffff",
        opacity: 0.4,
        width: 1
      },
      move: {
        enable: true,
        speed: 6,
        direction: "none",
        random: false,
        straight: false,
        out_mode: "out",
        bounce: false,
        attract: {
          enable: false,
          rotateX: 600,
          rotateY: 1200
        }
      }
    },
    interactivity: {
      detect_on: "canvas",
      events: {
        onhover: {
          enable: true,
          mode: "repulse"
        },
        onclick: {
          enable: true,
          mode: "push"
        }
      }
    },
    retina_detect: true
  });
</script>

<?php include 'includes/footer.php'; ?>
``
