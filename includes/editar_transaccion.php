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
    $nuevoTipo = $_POST['tipo']; // ingreso o gasto
    $fecha = $_POST['fecha'];
    $monto = floatval($_POST['monto']);
    $categoria = intval($_POST['categoria']);
    $descripcion = $_POST['descripcion'];
    // 1. Verificar si es ingreso actual
    $sqlIngreso = "SELECT * FROM ingresos WHERE id = ? AND usuario_id = ?";
    $stmtIngreso = $conn->prepare($sqlIngreso);
    $stmtIngreso->execute([$id, $idUsuario]);
    $esIngreso = $stmtIngreso->fetch(PDO::FETCH_ASSOC);
    // 2. Verificar si es gasto actual
    $sqlGasto = "SELECT * FROM gastos WHERE id = ? AND usuario_id = ?";
    $stmtGasto = $conn->prepare($sqlGasto);
    $stmtGasto->execute([$id, $idUsuario]);
    $esGasto = $stmtGasto->fetch(PDO::FETCH_ASSOC);
    if ($esIngreso && $nuevoTipo === 'ingreso') {
        // Caso: era ingreso y sigue siendo ingreso
        $sql = "UPDATE ingresos SET fecha = ?, monto = ?, categoria_id = ?, descripcion = ? WHERE id = ? AND usuario_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$fecha, $monto, $categoria, $descripcion, $id, $idUsuario]);
    } elseif ($esGasto && $nuevoTipo === 'gasto') {
        // Caso: era gasto y sigue siendo gasto
        $sql = "UPDATE gastos SET fecha = ?, monto = ?, categoria_id = ?, descripcion = ? WHERE id = ? AND usuario_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$fecha, $monto, $categoria, $descripcion, $id, $idUsuario]);
    } elseif ($esIngreso && $nuevoTipo === 'gasto') {
        // Caso: era ingreso y lo cambia a gasto
        // 1. Eliminar de ingresos
        $conn->prepare("DELETE FROM ingresos WHERE id = ? AND usuario_id = ?")->execute([$id, $idUsuario]);
        // 2. Insertar en gastos
        $sql = "INSERT INTO gastos (usuario_id, categoria_id, monto, fecha, descripcion) VALUES (?, ?, ?, ?, ?)";
        $conn->prepare($sql)->execute([$idUsuario, $categoria, $monto, $fecha, $descripcion]);
    } elseif ($esGasto && $nuevoTipo === 'ingreso') {
        // Caso: era gasto y lo cambia a ingreso
        // 1. Eliminar de gastos
        $conn->prepare("DELETE FROM gastos WHERE id = ? AND usuario_id = ?")->execute([$id, $idUsuario]);
        // 2. Insertar en ingresos
        $sql = "INSERT INTO ingresos (usuario_id, categoria_id, monto, fecha, descripcion) VALUES (?, ?, ?, ?, ?)";
        $conn->prepare($sql)->execute([$idUsuario, $categoria, $monto, $fecha, $descripcion]);
    } else {
        header('Location: ../registro.php?error=no-encontrado');
        exit;
    }
    header('Location: ../registro.php?mensaje=actualizado');
    exit;
} else {
    header('Location: ../registro.php?error=datos-incompletos');
    exit;
}