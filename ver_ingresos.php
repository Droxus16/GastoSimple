<?php
session_start();
require_once 'conexion.php';

if (!isset($_SESSION['id_usuario'])) {
    header("Location: login.php");
    exit();
}

$id_usuario = $_SESSION['id_usuario'];
$conexion = Conexion::conectar();

// Actualizar ingreso
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_ingreso'])) {
    $sql = "UPDATE ingresos SET fecha = :fecha, valor = :valor WHERE id_ingreso = :id_ingreso AND id_usuario = :id_usuario";
    $stmt = $conexion->prepare($sql);
    $stmt->execute([
        ':fecha' => $_POST['fecha'],
        ':valor' => $_POST['valor'],
        ':id_ingreso' => $_POST['id_ingreso'],
        ':id_usuario' => $id_usuario
    ]);
}

// Actualizar gasto
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_gasto'])) {
    $sql = "UPDATE gastos SET fecha = :fecha, valor = :valor WHERE id_gasto = :id_gasto AND id_usuario = :id_usuario";
    $stmt = $conexion->prepare($sql);
    $stmt->execute([
        ':fecha' => $_POST['fecha'],
        ':valor' => $_POST['valor'],
        ':id_gasto' => $_POST['id_gasto'],
        ':id_usuario' => $id_usuario
    ]);
}

// Obtener ingresos y gastos
$sqlIngresos = "SELECT * FROM ingresos WHERE id_usuario = :id_usuario ORDER BY fecha DESC";
$stmtIngresos = $conexion->prepare($sqlIngresos);
$stmtIngresos->bindParam(':id_usuario', $id_usuario);
$stmtIngresos->execute();
$ingresos = $stmtIngresos->fetchAll();

$sqlGastos = "SELECT * FROM gastos WHERE id_usuario = :id_usuario ORDER BY fecha DESC";
$stmtGastos = $conexion->prepare($sqlGastos);
$stmtGastos->bindParam(':id_usuario', $id_usuario);
$stmtGastos->execute();
$gastos = $stmtGastos->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lista de Ingresos y Gastos</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="table-container">
        <h2>Lista de Ingresos</h2>
        <table border="1">
            <tr>
                <th>Fecha</th>
                <th>Valor</th>
                <th>Descripción</th>
                <th>Acciones</th>
            </tr>
            <?php foreach ($ingresos as $ingreso): ?>
            <tr>
                <form method="POST">
                    <td><input type="date" name="fecha" value="<?= $ingreso['fecha'] ?>" required></td>
                    <td><input type="number" name="valor" value="<?= $ingreso['valor'] ?>" required></td>
                    <td><?= $ingreso['descripcion'] ?></td>
                    <td>
                        <input type="hidden" name="id_ingreso" value="<?= $ingreso['id_ingreso'] ?>">
                        <button type="submit" name="update_ingreso">✏ Guardar</button>
                        <a href="eliminar_ingreso.php?id=<?= $ingreso['id_ingreso'] ?>">🗑</a>
                    </td>
                </form>
            </tr>
            <?php endforeach; ?>
        </table>

        <h2>Lista de Gastos</h2>
        <table border="1">
            <tr>
                <th>Fecha</th>
                <th>Valor</th>
                <th>Descripción</th>
                <th>Acciones</th>
            </tr>
            <?php foreach ($gastos as $gasto): ?>
            <tr>
                <form method="POST">
                    <td><input type="date" name="fecha" value="<?= $gasto['fecha'] ?>" required></td>
                    <td><input type="number" name="valor" value="<?= $gasto['valor'] ?>" required></td>
                    <td><?= $gasto['descripcion'] ?></td>
                    <td>
                        <input type="hidden" name="id_gasto" value="<?= $gasto['id_gasto'] ?>">
                        <button type="submit" name="update_gasto">✏ Guardar</button>
                        <a href="eliminar_gasto.php?id=<?= $gasto['id_gasto'] ?>">🗑</a>
                    </td>
                </form>
            </tr>
            <?php endforeach; ?>
        </table>
        <a href="menu.php">Volver al menú</a>
    </div>
</body>
</html>