<?php
session_start();
require_once 'conexion.php';

if (!isset($_SESSION['id_usuario'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_usuario = $_SESSION['id_usuario'];
    $fecha = $_POST['fecha'];
    $tipo = $_POST['tipo'];
    $valor = $_POST['valor'];
    $descripcion = $_POST['descripcion'];

    $conexion = Conexion::conectar();
    $sql = "INSERT INTO ingresos (id_usuario, fecha, tipo, valor, descripcion) VALUES (:id_usuario, :fecha, :tipo, :valor, :descripcion)";
    $stmt = $conexion->prepare($sql);
    $stmt->bindParam(':id_usuario', $id_usuario);
    $stmt->bindParam(':fecha', $fecha);
    $stmt->bindParam(':tipo', $tipo);
    $stmt->bindParam(':valor', $valor);
    $stmt->bindParam(':descripcion', $descripcion);

    if ($stmt->execute()) {
        echo "<p style='color: green;'>Ingreso registrado correctamente.</p>";
    } else {
        echo "<p style='color: red;'>Error al registrar ingreso.</p>";
    }
}
?>
<form method="post">
    Fecha: <input type="date" name="fecha" required><br>
    Tipo: <input type="text" name="tipo" required><br>
    Valor: <input type="number" name="valor" step="0.01" required><br>
    Descripción: <textarea name="descripcion"></textarea><br>
    <input type="submit" value="Registrar Ingreso">
</form>
<br>
<a href="menu.php">
    <button>Volver al Menú</button>
</a>
