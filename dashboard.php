<?php
session_start();

require_once 'includes/db.php';
require_once 'includes/auth.php';

// si el usuario ha iniciado sesión
if (!isset($_SESSION['id_usuario'])) {
    header("Location: login.php");
    exit();
}
$conn = db::conectar();
$idUsuario = $_SESSION['id_usuario'];

// Obtener el nombre del usuario
$query = "SELECT nombre FROM usuarios WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bindParam(1, $idUsuario);
$stmt->execute();
$nombreUsuario = $stmt->fetchColumn();
?>

<?php include 'includes/header.php'; ?>
<link rel="stylesheet" href="assets/css/estilos.css">

<div class="contenedor">
    <div class="tarjeta">
        <h2>Bienvenido, <?= htmlspecialchars($nombreUsuario) ?> 👋</h2>
        <p>Este es tu panel de control personal.</p>

        <div class="acciones">
            <a href="ingresos.php">Registrar Ingreso</a>
            <a href="gastos.php">Registrar Gasto</a>
            <a href="metas.php">Mis Metas</a>
            <a href="reportes.php">Reportes</a>
            <a href="logout.php" class="logout">Cerrar Sesión</a>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
