<?php
require_once 'includes/db.php';

$conn = db::conectar();

$hoy = date('Y-m-d');

// Buscar metas vencidas
$stmt = $conn->query("SELECT m.*, 
  (SELECT COALESCE(SUM(a.monto),0) FROM aportes_ahorro a WHERE a.meta_id = m.id) AS total_aportado
  FROM metas_ahorro m
  WHERE m.fecha_limite <= '$hoy'
");

while ($meta = $stmt->fetch(PDO::FETCH_ASSOC)) {
  $aporte = floatval($meta['total_aportado']);
  $objetivo = floatval($meta['monto_objetivo']);

  if ($aporte >= $objetivo) {
    $table = 'metas_logradas';
  } else {
    $table = 'metas_fracasadas';
  }

  // Insertar en tabla correspondiente
  $insert = $conn->prepare("INSERT INTO $table (usuario_id, nombre, monto_objetivo, total_aportado, fecha_limite) VALUES (?, ?, ?, ?, ?)");
  $insert->execute([
    $meta['usuario_id'],
    $meta['nombre'],
    $objetivo,
    $aporte,
    $meta['fecha_limite']
  ]);

  // Eliminar meta y sus aportes
  $conn->prepare("DELETE FROM aportes_ahorro WHERE meta_id = ?")->execute([$meta['id']]);
  $conn->prepare("DELETE FROM metas_ahorro WHERE id = ?")->execute([$meta['id']]);
}

echo "Metas procesadas correctamente.\n";
