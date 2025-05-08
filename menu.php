<?php
session_start();
require_once __DIR__ . '/conexion.php';

if (!isset($_SESSION['id_usuario'])) {
    header("Location: login.html");
    exit();
}

$conn = Conexion::conectar();

$idUsuario = $_SESSION['id_usuario'];
$query = "SELECT nombre FROM usuarios WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bindParam(1, $idUsuario);
$stmt->execute();
$nombreUsuario = $stmt->fetchColumn();

$html = file_get_contents(__DIR__ . '/menu.html');
$html = str_replace("{{username}}", htmlspecialchars($nombreUsuario), $html);

echo $html;
?>
