<?php
session_start();
require_once 'conexion.php';

if (!isset($_SESSION['id_usuario'])) {
    header("Location: login.html");
    exit();
}

$idUsuario = $_SESSION['id_usuario'];
$idIngreso = $_GET['id'];  // ID del ingreso a editar

try {
    $conexion = Conexion::conectar();

    // Obtener categorías para el select
    $sqlCategorias = "SELECT id, nombre FROM categorias";
    $stmtCategorias = $conexion->prepare($sqlCategorias);
    $stmtCategorias->execute();
    $categorias = $stmtCategorias->fetchAll(PDO::FETCH_ASSOC);

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $monto = $_POST['monto'];
        $descripcion = $_POST['descripcion'];
        $idCategoria = $_POST['categoria']; // esto es el id, no el nombre

        // Actualizar el ingreso
        $sql = "UPDATE ingresos 
                SET monto = :monto, descripcion = :descripcion, id_categoria = :id_categoria 
                WHERE id = :id AND id_usuario = :idUsuario";
        $stmt = $conexion->prepare($sql);
        $stmt->bindParam(':monto', $monto);
        $stmt->bindParam(':descripcion', $descripcion);
        $stmt->bindParam(':id_categoria', $idCategoria);
        $stmt->bindParam(':id', $idIngreso);
        $stmt->bindParam(':idUsuario', $idUsuario);
        $stmt->execute();

        header("Location: ver_ingresos.php");
        exit();
    } else {
        // Obtener los datos actuales del ingreso con JOIN
        $sql = "SELECT i.monto, i.descripcion, i.id_categoria 
                FROM ingresos i 
                WHERE i.id = :id AND i.id_usuario = :idUsuario";
        $stmt = $conexion->prepare($sql);
        $stmt->bindParam(':id', $idIngreso);
        $stmt->bindParam(':idUsuario', $idUsuario);
        $stmt->execute();
        $ingreso = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$ingreso) {
            echo "Ingreso no encontrado.";
            exit();
        }
    }
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar Ingreso - GastoSimple</title>
    <link rel="stylesheet" href="estilos.css">
</head>
<body>
    <h2>Editar Ingreso</h2>
    <form method="POST">
        <label for="monto">Monto:</label>
        <input type="number" name="monto" value="<?= htmlspecialchars($ingreso['monto']) ?>" required><br>

        <label for="descripcion">Descripción:</label>
        <input type="text" name="descripcion" value="<?= htmlspecialchars($ingreso['descripcion']) ?>" required><br>

        <label for="categoria">Categoría:</label>
        <select name="categoria" required>
            <?php foreach ($categorias as $cat): ?>
                <option value="<?= $cat['id'] ?>" <?= ($cat['id'] == $ingreso['id_categoria']) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($cat['nombre']) ?>
                </option>
            <?php endforeach; ?>
        </select><br>

        <button type="submit">Actualizar</button>
    </form>
    <a href="ver_ingresos.php">Volver a los ingresos</a>
</body>
</html>
