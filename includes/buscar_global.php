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

echo '<table class="glass-table">';
echo '<thead><tr><th>Usuario</th><th>Tipo</th><th>Categoría</th><th>Monto</th><th>Fecha</th><th>Descripción</th></tr></thead><tbody>';
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
echo '</tbody></table>';
