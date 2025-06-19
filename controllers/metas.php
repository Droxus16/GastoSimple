<?php
session_start();
require_once 'includes/db.php';
require_once 'includes/auth.php';

$conn = db::conectar();
$idUsuario = $_SESSION['usuario_id'];

// Crear nueva meta
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['crear_meta'])) {
    $nombre = $_POST['nombre'];
    $monto_objetivo = $_POST['monto_objetivo'];
    $fecha_limite = $_POST['fecha_limite'];
    $porcentaje_sugerido = $_POST['porcentaje_sugerido'];

    $stmt = $conn->prepare("INSERT INTO metas_ahorro (usuario_id, nombre, monto_objetivo, fecha_limite, porcentaje_sugerido, created_at, updated_at)
                            VALUES (?, ?, ?, ?, ?, NOW(), NOW())");
    $stmt->execute([$idUsuario, $nombre, $monto_objetivo, $fecha_limite, $porcentaje_sugerido]);
}

// Consultar metas
$stmt = $conn->prepare("SELECT * FROM metas_ahorro WHERE usuario_id = ?");
$stmt->execute([$idUsuario]);
$metas = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<?php include 'includes/header.php'; ?>
<link rel="stylesheet" href="assets/css/estilos.css">

<div class="form-container">
  <h2>Crear Meta de Ahorro</h2>
  <form method="POST">
    <input type="text" name="nombre" placeholder="Nombre de la meta" required>
    <input type="number" name="monto_objetivo" placeholder="Monto objetivo ($)" step="0.01" required>
    <input type="date" name="fecha_limite" required>
    <input type="number" name="porcentaje_sugerido" placeholder="Porcentaje sugerido (%)" step="0.01" min="0" max="100" required>
    <button type="submit" name="crear_meta">Guardar Meta</button>
  </form>
</div>

<div class="tabla-container">
  <h2>Mis Metas de Ahorro</h2>
  <?php if (count($metas) > 0): ?>
    <table class="tabla-transacciones">
      <thead>
        <tr>
          <th>Nombre</th>
          <th>Objetivo</th>
          <th>Fecha Límite</th>
          <th>% Sugerido</th>
          <th>Fecha de Creación</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($metas as $meta): ?>
          <tr>
            <td><?= htmlspecialchars($meta['nombre']) ?></td>
            <td>$<?= number_format($meta['monto_objetivo'], 2) ?></td>
            <td><?= htmlspecialchars($meta['fecha_limite']) ?></td>
            <td><?= number_format($meta['porcentaje_sugerido'], 2) ?>%</td>
            <td><?= date("Y-m-d", strtotime($meta['created_at'])) ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  <?php else: ?>
    <p>Aún no has creado metas de ahorro.</p>
  <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>
