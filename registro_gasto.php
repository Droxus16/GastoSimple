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
    $sql = "INSERT INTO gastos (id_usuario, fecha, tipo, valor, descripcion) VALUES (:id_usuario, :fecha, :tipo, :valor, :descripcion)";
    $stmt = $conexion->prepare($sql);
    $stmt->bindParam(':id_usuario', $id_usuario);
    $stmt->bindParam(':fecha', $fecha);
    $stmt->bindParam(':tipo', $tipo);
    $stmt->bindParam(':valor', $valor);
    $stmt->bindParam(':descripcion', $descripcion);

    if ($stmt->execute()) {
        $mensaje = "Gasto registrado correctamente.";
    } else {
        $mensaje = "Error al registrar gasto.";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrar Gasto</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="form-container">
        <h2>Registrar Gasto</h2>
        <?php if (isset($mensaje)) echo "<p class='mensaje'>$mensaje</p>"; ?>
        <form method="post">
            <label>Fecha:</label>
            <input type="date" name="fecha" required>
            
            <label>Tipo:</label>
            <input type="text" name="tipo" required>

            <label>Valor:</label>
            <input type="number" name="valor" step="0.01" required>

            <label>Descripción:</label>
            <textarea name="descripcion"></textarea>

            <input type="submit" value="Registrar Gasto">
        </form>
        <a href="menu.php" class="volver">Volver al menú</a>
    </div>
</body>
</html>
