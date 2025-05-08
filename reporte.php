<?php
require_once __DIR__ . '/conexion.php';

$conexion = Conexion::conectar();

$condicion = "";
if (isset($_GET['buscar']) && !empty($_GET['buscar'])) {
    $buscar = $_GET['buscar'];
    $condicion = "WHERE nombre LIKE :buscar OR correo LIKE :buscar";
}

$sql = "SELECT id, nombre, correo, moneda, idioma FROM usuarios " . $condicion;

$stmt = $conexion->prepare($sql);

if (!empty($condicion)) {
    $stmt->bindValue(':buscar', '%' . $buscar . '%', PDO::PARAM_STR);
}

$stmt->execute();
$resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de Usuarios - GastoSimple</title>
    <link rel="stylesheet" href="estilos.css">
</head>
<body>

<div class="container">
    <h1>Reporte de Usuarios</h1>

    <form method="GET" action="reporte.php">
        <input type="text" name="buscar" placeholder="Buscar por nombre o correo">
        <button type="submit">Buscar</button>
    </form>

    <br>

    <?php if (!empty($resultados)) { ?>
        <table border="1">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nombre</th>
                    <th>Correo</th>
                    <th>Moneda</th>
                    <th>Idioma</th>
                    <th>Opciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($resultados as $row) { ?>
                    <tr>
                        <td><?= htmlspecialchars($row['id']) ?></td>
                        <td><?= htmlspecialchars($row['nombre']) ?></td>
                        <td><?= htmlspecialchars($row['correo']) ?></td>
                        <td><?= htmlspecialchars($row['moneda']) ?></td>
                        <td><?= htmlspecialchars($row['idioma']) ?></td>
                        <td>
                            <a href="reporte_usuario.php?id=<?= $row['id'] ?>" target="_blank">Ver Reporte</a>
                        </td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>

        <br>

        <form method="POST" action="reporte_pdf.php" target="_blank">
            <button type="submit">Descargar Reporte Completo en PDF</button>
        </form>

    <?php } else { ?>
        <p>No hay usuarios que coincidan.</p>
    <?php } ?>
</div>

</body>
</html>