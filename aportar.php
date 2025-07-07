<?php
session_start();
require_once 'includes/db.php';
require_once 'includes/auth.php';
if (!isset($_POST['meta_id'], $_POST['monto'])) {
    header('Location: metas.php');
    exit;
}
$usuario_id = $_SESSION['usuario_id'];
$meta_id = intval($_POST['meta_id']);
$monto = floatval($_POST['monto']);
$fecha = date('Y-m-d');
$descripcion = "Aporte manual desde el sistema";
//Validar monto
if ($monto == 0) {
    $_SESSION['error'] = "El monto no puede ser cero.";
    header('Location: metas.php');
    exit;
}
$conn = db::conectar();
//Verificar que la meta exista y pertenezca al usuario
$stmt = $conn->prepare("SELECT usuario_id FROM metas_ahorro WHERE id = ?");
$stmt->execute([$meta_id]);
$meta = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$meta || $meta['usuario_id'] != $usuario_id) {
    $_SESSION['error'] = "Meta no válida o no pertenece al usuario.";
    header('Location: metas.php');
    exit;
}
//Calcular ahorro disponible actual
$query = "
    SELECT 
        (SELECT COALESCE(SUM(monto), 0) FROM ingresos WHERE usuario_id = ?) -
        (SELECT COALESCE(SUM(monto), 0) FROM gastos WHERE usuario_id = ?) -
        (SELECT COALESCE(SUM(a.monto), 0) 
         FROM aportes_ahorro a 
         JOIN metas_ahorro m ON a.meta_id = m.id 
         WHERE m.usuario_id = ?) AS ahorro_disponible
";
$stmt = $conn->prepare($query);
$stmt->execute([$usuario_id, $usuario_id, $usuario_id]);
$ahorro_disponible = $stmt->fetchColumn();
//Si es un aporte positivo, verificar que no supere el ahorro disponible
if ($monto > 0 && $monto > $ahorro_disponible) {
    $_SESSION['error'] = "No tienes suficiente ahorro disponible para este aporte. Disponible: $" . number_format($ahorro_disponible, 2);
    header('Location: metas.php');
    exit;
}
// Registrar el aporte
$stmt = $conn->prepare("
    INSERT INTO aportes_ahorro (meta_id, monto, fecha, descripcion, created_at, updated_at)
    VALUES (?, ?, ?, ?, NOW(), NOW())
");
if ($stmt->execute([$meta_id, $monto, $fecha, $descripcion])) {
    $_SESSION['success'] = "Aporte registrado correctamente.";
} else {
    $_SESSION['error'] = "Ocurrió un error al guardar el aporte.";
}
header('Location: metas.php');
exit;