<?php
session_start();
require_once __DIR__ . '/conexion.php';

if (!isset($_SESSION['id_usuario'])) {
    header("Location: login.html");
    exit();
}

$idUsuario = $_SESSION['id_usuario'];
$idGasto = $_GET['id'];

try {
    $conexion = Conexion::conectar();

    $sqlCategorias = "SELECT id, nombre FROM categorias";
    $stmtCategorias = $conexion->prepare($sqlCategorias);
    $stmtCategorias->execute();
    $categorias = $stmtCategorias->fetchAll(PDO::FETCH_ASSOC);

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $monto = $_POST['monto'];
        $descripcion = $_POST['descripcion'];
        $idCategoria = $_POST['categoria'];

        $sql = "UPDATE gastos 
                SET monto = :monto, descripcion = :descripcion, id_categoria = :id_categoria 
                WHERE id = :id AND id_usuario = :idUsuario";
        $stmt = $conexion->prepare($sql);
        $stmt->bindParam(':monto', $monto);
        $stmt->bindParam(':descripcion', $descripcion);
        $stmt->bindParam(':id_categoria', $idCategoria);
        $stmt->bindParam(':id', $idGasto);
        $stmt->bindParam(':idUsuario', $idUsuario);
        $stmt->execute();

        header("Location: ver_gastos.php");
        exit();
    } else {
        $sql = "SELECT g.monto, g.descripcion, g.id_categoria 
                FROM gastos g 
                WHERE g.id = :id AND g.id_usuario = :idUsuario";
        $stmt = $conexion->prepare($sql);
        $stmt->bindParam(':id', $idGasto);
        $stmt->bindParam(':idUsuario', $idUsuario);
        $stmt->execute();
        $gasto = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$gasto) {
            echo "Gasto no encontrado.";
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
    <title>Editar Gasto - GastoSimple</title>
    <link rel="stylesheet" href="estilos.css">
</head>
<body>
    <h2>Editar Gasto</h2>
    <form method="POST">
        <label for="monto">Monto:</label>
        <input type="number" name="monto" value="<?= htmlspecialchars($gasto['monto']) ?>" required><br>

        <label for="descripcion">Descripción:</label>
        <input type="text" name="descripcion" value="<?= htmlspecialchars($gasto['descripcion']) ?>" required><br>

        <label for="categoria">Categoría:</label>
        <select name="categoria" required>
            <?php foreach ($categorias as $cat): ?>
                <option value="<?= $cat['id'] ?>" <?= ($cat['id'] == $gasto['id_categoria']) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($cat['nombre']) ?>
                </option>
            <?php endforeach; ?>
        </select><br>

        <button type="submit">Actualizar</button>
    </form>
    <a href="ver_gastos.php">Volver a los gastos</a>
</body>
</html>
