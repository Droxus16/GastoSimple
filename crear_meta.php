<?php
session_start();
require_once 'includes/db.php';
require_once 'includes/auth.php';

// Verificar que llegaron todos los datos del formulario
if (!isset($_POST['nombre'], $_POST['monto_objetivo'], $_POST['fecha_limite'])) {
    $_SESSION['error'] = 'Todos los campos son obligatorios.';
    header('Location: metas.php');
    exit;
}

$usuario_id = $_SESSION['usuario_id'];
$nombre = trim($_POST['nombre']);
$monto_objetivo = floatval($_POST['monto_objetivo']);
$fecha_limite = $_POST['fecha_limite'];
$porcentaje_sugerido = 0; // Lo puedes calcular después si usas una regla

// Validaciones básicas
if ($monto_objetivo <= 0) {
    $_SESSION['error'] = 'El monto objetivo debe ser mayor a cero.';
    header('Location: metas.php');
    exit;
}

if (strtotime($fecha_limite) < strtotime(date('Y-m-d'))) {
    $_SESSION['error'] = 'La fecha límite no puede estar en el pasado.';
    header('Location: metas.php');
    exit;
}

// Guardar en la base de datos
$conn = db::conectar();
$stmt = $conn->prepare("
    INSERT INTO metas_ahorro (usuario_id, nombre, monto_objetivo, fecha_limite, porcentaje_sugerido, created_at, updated_at)
    VALUES (?, ?, ?, ?, ?, NOW(), NOW())
");

$exito = $stmt->execute([$usuario_id, $nombre, $monto_objetivo, $fecha_limite, $porcentaje_sugerido]);

if ($exito) {
    $_SESSION['success'] = 'Meta de ahorro creada con éxito.';
} else {
    $_SESSION['error'] = 'Ocurrió un error al crear la meta.';
}

header('Location: metas.php');
exit;
