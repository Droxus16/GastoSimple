<?php
session_start();
require_once __DIR__ . '/conexion.php';


if (!isset($_SESSION['id_usuario'])) {
    header("Location: login.html");
    exit();
}

$conexion = Conexion::conectar();

$usuario_id = $_SESSION['id_usuario'];

$stmt = $conexion->prepare("SELECT nombre, correo, moneda FROM usuarios WHERE id = ?");
$stmt->execute([$usuario_id]);
$usuario = $stmt->fetch(PDO::FETCH_ASSOC);

$nombre = htmlspecialchars($usuario['nombre']);
$correo = htmlspecialchars($usuario['correo']);
$moneda = htmlspecialchars($usuario['moneda']);
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Perfil - GastoSimple</title>
  <link rel="stylesheet" href="estilos.css">
</head>
<body>

  <div class="perfil">
    <h2>Mi Perfil</h2>

    <p><strong>Nombre:</strong> <?= $nombre ?></p>
    <p><strong>Correo:</strong> <?= $correo ?></p>
    <p><strong>Moneda:</strong> <?= $moneda ?></p>

    <div class="botones">
      <a href="menu.php">Volver al Menú</a>
      <a href="configuracion_usuario.php">Configuración</a>
    </div>
  </div>

</body>
</html>
