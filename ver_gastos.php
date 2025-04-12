<?php
session_start();
require_once 'conexion.php';

if (!isset($_SESSION['id_usuario'])) {
    header("Location: login.php");
    exit();
}

$id_usuario = $_SESSION['id_usuario'];
$conexion = Conexion::conectar();
$sql = "SELECT * FROM gastos WHERE id_usuario = :id_usuario ORDER BY fecha DESC";
$stmt = $conexion->prepare($sql);
$stmt->bindParam(':id_usuario', $id_usuario);
$stmt->execute();
$gastos = $stmt->fetchAll();

// Eliminar un gasto específico
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_gasto'])) {
    $id_gasto = $_POST['id_gasto'];
    $sql = "DELETE FROM gastos WHERE id_usuario = :id_usuario AND id_gasto = :id_gasto";
    $stmt = $conexion->prepare($sql);
    $stmt->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
    $stmt->bindParam(':id_gasto', $id_gasto, PDO::PARAM_INT);
    $stmt->execute();
    header("Location: ver_gastos.php");
    exit();
}

// Eliminar todos los gastos
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['eliminar_todos'])) {
    $sql = "DELETE FROM gastos WHERE id_usuario = :id_usuario";
    $stmt = $conexion->prepare($sql);
    $stmt->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
    $stmt->execute();
    header("Location: ver_gastos.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lista de Gastos</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="table-container">
        <h2>Lista de Gastos</h2>
        <table>
            <tr>
                <th>Fecha</th>
                <th>Tipo</th>
                <th>Valor</th>
                <th>Descripción</th>
                <th>Acciones</th>
            </tr>
            <?php foreach ($gastos as $gasto): ?>
            <tr>
                <td><?= htmlspecialchars($gasto['fecha']) ?></td>
                <td><?= htmlspecialchars($gasto['tipo']) ?></td>
                <td><?= htmlspecialchars($gasto['valor']) ?></td>
                <td><?= htmlspecialchars($gasto['descripcion']) ?></td>
                <td>
                    <form method="POST" style="display:inline;">
                        <input type="hidden" name="id_gasto" value="<?= $gasto['id_gasto'] ?>">
                        <button type="submit" onclick="return confirm('¿Eliminar este gasto?');">🗑</button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
        </table>

        <!-- Botón para eliminar todos los gastos -->
        <form method="POST">
            <input type="hidden" name="eliminar_todos" value="1">
            <button type="submit" onclick="return confirm('¿Eliminar todos los gastos?');">🗑 Eliminar Todos</button>
        </form>

        <a href="menu.php" class="volver">Volver al menú</a>
    </div>
</body>
</html>