<?php
require_once '../includes/db.php';
$conn = db::conectar();

$filtro = $_POST['filtro'] ?? '';

$sql = "SELECT u.nombre AS usuario, t.tipo, t.categoria, t.monto, t.fecha, t.descripcion 
        FROM transacciones t 
        INNER JOIN usuarios u ON t.id_usuario = u.id 
        WHERE u.nombre LIKE ? OR t.categoria LIKE ?
        ORDER BY t.fecha DESC";

$stmt = $conn->prepare($sql);
$filtroSQL = "%$filtro%";
$stmt->execute([$filtroSQL, $filtroSQL]);
$resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);

// ✅ Tabla coherente con diseño glass-table
echo '<div class="table-responsive">';
echo '<table class="table table-striped table-hover glass-table" style="backdrop-filter: blur(8px); background: rgba(255,255,255,0.05); border-radius:12px; color:white;">';
echo '<thead class="text-center" style="background: rgba(255,255,255,0.1);">';
echo '<tr>';
echo '<th>Usuario</th><th>Tipo</th><th>Categoría</th><th>Monto</th><th>Fecha</th><th>Descripción</th>';
echo '</tr></thead><tbody>';

if ($resultados) {
  foreach ($resultados as $row) {
    echo '<tr>';
    echo '<td>' . htmlspecialchars($row['usuario']) . '</td>';
    echo '<td>' . htmlspecialchars($row['tipo']) . '</td>';
    echo '<td>' . htmlspecialchars($row['categoria']) . '</td>';
    echo '<td>$' . number_format($row['monto'], 2) . '</td>';
    echo '<td>' . htmlspecialchars($row['fecha']) . '</td>';
    echo '<td>' . htmlspecialchars($row['descripcion']) . '</td>';
    echo '</tr>';
  }
} else {
  echo '<tr><td colspan="6" class="text-center">⚠️ No se encontraron resultados para este filtro.</td></tr>';
}

echo '</tbody></table>';
echo '</div>';
?>
