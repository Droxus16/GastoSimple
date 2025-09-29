<?php
require_once '../includes/db.php';
$conn = db::conectar();
$filtro = trim($_POST['filtro'] ?? '');
$modoFiltro = trim($_POST['modo_filtro'] ?? 'nombre');
if (!in_array($modoFiltro, ['nombre', 'categoria'])) $modoFiltro = 'nombre';
$filtroSQL = "%$filtro%";
$sql = "
  SELECT u.nombre AS usuario, t.tipo, t.categoria, t.monto, t.fecha, t.descripcion 
  FROM transacciones t 
  INNER JOIN usuarios u ON t.id_usuario = u.id 
";
$where = " WHERE 1 ";
$params = [];
if ($filtro !== '') {
  if ($modoFiltro === 'nombre') {
    $where .= " AND u.nombre LIKE ? ";
    $params[] = $filtroSQL;
  } elseif ($modoFiltro === 'categoria') {
    $where .= " AND t.categoria LIKE ? ";
    $params[] = $filtroSQL;
  }
}
$sql .= $where . " ORDER BY t.fecha DESC";
$stmt = $conn->prepare($sql);
$stmt->execute($params);
$resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);
// Render tabla coherente
echo '<div style="overflow-x:auto; border-radius:12px; margin-top:20px;">';
echo '<table style="
  width:100%; border-collapse:collapse; 
  backdrop-filter: blur(8px); 
  background: rgba(255,255,255,0.05); 
  border-radius:12px; color:white;
">';
echo '<thead style="background: rgba(255,255,255,0.1);">';
echo '<tr>
<th style="padding:12px;">Usuario</th>
<th style="padding:12px;">Tipo</th>
<th style="padding:12px;">Categoría</th>
<th style="padding:12px;">Fecha</th>
<th style="padding:12px;">Descripción</th>
</tr></thead><tbody>';
if ($resultados) {
  foreach ($resultados as $row) {
    echo '<tr style="text-align:center; border-bottom:1px solid rgba(255,255,255,0.1);">';
    echo '<td style="padding:10px;">' . htmlspecialchars($row['usuario']) . '</td>';
    echo '<td style="padding:10px;">' . htmlspecialchars($row['tipo']) . '</td>';
    echo '<td style="padding:10px;">' . htmlspecialchars($row['categoria']) . '</td>';
    echo '<td style="padding:10px;">' . htmlspecialchars($row['fecha']) . '</td>';
    echo '<td style="padding:10px;">' . htmlspecialchars($row['descripcion']) . '</td>';
    echo '</tr>';
  }
} else {
  echo '<tr><td colspan="6" style="text-align:center; padding:20px;">⚠️ No se encontraron resultados.</td></tr>';
}
echo '</tbody></table>';
echo '</div>';
?>