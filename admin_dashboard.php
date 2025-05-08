<?php
session_start();

if (!isset($_SESSION['id_usuario']) || $_SESSION['rol'] != 2) {
    header("Location: menu.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Panel de Administración - GastoSimple</title>
    <link rel="stylesheet" href="estilos.css">
</head>
<body>
    <h2>Dashboard Administrativo</h2>
    <p>Bienvenido, Administrador. Aquí puedes visualizar reportes globales.</p>

    <ul>
        <li><a href="reporte_global.php"> Ver reportes globales</a></li>
        <li><a href="reporte.php"> Ver reportes</a></li>
        <li><a href="logout.php"> Cerrar sesión</a></li>
    </ul>
</body>
</html>
