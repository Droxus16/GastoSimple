<?php
session_start();
if (!isset($_SESSION['id_usuario'])) {
    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Menú Principal</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="container">
        <h2>Bienvenido, <?php echo htmlspecialchars($_SESSION['username']); ?>!</h2>
        <nav class="menu">
            <ul>
                <li><a href="registro_gasto.php">Registrar Gasto</a></li>
                <li><a href="registro_ingreso.php">Registrar Ingreso</a></li>
                <li><a href="ver_gastos.php">Ver Gastos</a></li>
                <li><a href="ver_ingresos.php">Ver Ingresos</a></li>
                <li><a href="configuracion.php">Configuración</a></li>
                <li><a href="logout.php">Cerrar Sesión</a></li>
            </ul>
        </nav>
        <form method="POST" action="eliminar_todo.php" style="margin-top: 20px;">
            <button type="submit" name="eliminar_todo" onclick="return confirm('¿Eliminar todos los gastos e ingresos?');">
                🗑 Eliminar Todos los Gastos e Ingresos
            </button>
        </form>
    </div>
</body>
</html>
