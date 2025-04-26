<?php
session_start();

// Verificar si el usuario está autenticado
if (!isset($_SESSION['id_usuario']) || $_SESSION['rol'] != 2) {
    // Si no es admin, redirigir al menú de usuario
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
        <li><a href="reporte_global.php">📊 Ver reportes globales</a></li>
        <li><a href="logout.php">🔒 Cerrar sesión</a></li>
    </ul>
</body>
</html>
