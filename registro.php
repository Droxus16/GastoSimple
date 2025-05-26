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

  <style>
  /* Contenedor principal para el formulario y tabla */
  .form-container, .tabla-container {
    background-color: rgba(255,255,255,0.07);
    padding: 20px;
    border-radius: 15px;
    backdrop-filter: blur(5px);
    margin-bottom: 20px;
    color: white;
    max-width: 600px; /* ancho máximo para que no se extienda demasiado */
    margin-left: auto;
    margin-right: auto; /* centra horizontalmente */
  }

  /* Título centrado */
  .form-container h2 {
    text-align: center;
    margin-bottom: 20px;
    font-weight: 700;
  }

  /* Tabla de transacciones */
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

  /* Inputs y select y textarea con buen tamaño y responsive */
  input, select, textarea {
    width: 100%;
    padding: 10px;
    margin-bottom: 15px;
    border-radius: 6px;
    border: none;
    font-size: 1rem;
    box-sizing: border-box;
  }

  /* Botones */
  .acciones {
    display: flex;
    gap: 10px;
    justify-content: center; /* centra los botones */
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

  /* Responsive: para pantallas chicas */
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
</style>


<div class="form-container">
  <h2>Registrar Gasto o Ingreso</h2>
  <form action="includes/insertar_transaccion.php" method="POST">
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
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  <?php else: ?>
    <p>No hay datos registrados aún.</p>
    <form action="includes/importar_excel.php" method="POST" enctype="multipart/form-data">
      <label for="archivo_excel">Importar desde Excel:</label>
      <input type="file" name="archivo_excel" accept=".xlsx, .xls" required>
      <button type="submit">Importar</button>
    </form>
  <?php endif; ?>
</div>

<script>
function filtrarCategorias() {
  const tipoSeleccionado = document.getElementById('tipo').value;
  const selectCategorias = document.getElementById('categoria');
  const opciones = selectCategorias.querySelectorAll('option');

  opciones.forEach(op => {
    if (op.value === '') return; // dejar "--Selecciona--"
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

<?php include 'includes/footer.php'; ?>
