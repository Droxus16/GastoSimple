<?php
session_start();
require_once __DIR__ . '/db.php';

if (!isset($_SESSION['usuario_id'])) {
    header('Location: ../login.php');
    exit;
}

$conn = db::conectar();
$idUsuario = $_SESSION['usuario_id'];

if (
    isset($_POST['id'], $_POST['tipo'], $_POST['fecha'], $_POST['monto'], $_POST['categoria'], $_POST['descripcion'])
) {
    $id = intval($_POST['id']);
    $tipo = $_POST['tipo'];
    $fecha = $_POST['fecha'];
    $monto = floatval($_POST['monto']);
    $categoria = intval($_POST['categoria']);
    $descripcion = $_POST['descripcion'];

    $tabla = ($tipo === 'ingreso') ? 'ingresos' : 'gastos';

    $stmt = $conn->prepare("SELECT id FROM $tabla WHERE id = ? AND usuario_id = ?");
    $stmt->execute([$id, $idUsuario]);
    $registro = $stmt->fetch();

    if ($registro) {
        $stmt = $conn->prepare("UPDATE $tabla SET fecha = ?, monto = ?, categoria_id = ?, descripcion = ? WHERE id = ? AND usuario_id = ?");
        $stmt->execute([$fecha, $monto, $categoria, $descripcion, $id, $idUsuario]);

        header('Location: ../registro.php?mensaje=actualizado');
        exit;
    } else {
        header('Location: ../registro.php?error=no-autorizado');
        exit;
    }
} else {
    header('Location: ../registro.php?error=datos-incompletos');
    exit;
}
