<?php
require_once 'includes/db.php';
$conn = db::conectar();
$hoy = date('Y-m-d');
$dia = date('j'); // Día sin ceros

$sql = "SELECT * FROM transacciones_recurrentes WHERE activa = 1 AND (fecha_fin IS NULL OR fecha_fin >= ?) AND dia_fijo = ?";
$stmt = $conn->prepare($sql);
$stmt->execute([$hoy, $dia]);
$registros = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($registros as $r) {
    if ($r['monto_variable']) {
        // Guardar notificación pendiente (o email, etc.)
        continue;
    }

    // Insertar en tabla transacciones automáticamente
    $sqlInsert = "INSERT INTO transacciones (id_usuario, tipo, fecha, monto, categoria, descripcion)
                  VALUES (?, ?, ?, ?, ?, ?)";
    $stmtInsert = $conn->prepare($sqlInsert);
    $stmtInsert->execute([
        $r['id_usuario'], $r['tipo'], $hoy, $r['monto'], $r['categoria_id'], $r['descripcion']
    ]);

    // Actualizar última ejecución
    $update = $conn->prepare("UPDATE transacciones_recurrentes SET ultima_ejecucion = ? WHERE id = ?");
    $update->execute([$hoy, $r['id']]);
}
?>
