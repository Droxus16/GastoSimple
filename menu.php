<?php
session_start();
require_once 'conexion.php';

if (!isset($_SESSION['id_usuario'])) {
    header("Location: login.php");
    exit();
}

// Obtener conexión con la clase Conexion
$conn = Conexion::conectar(); // 👈 Esta línea era la que faltaba

// Obtener el nombre del usuario desde la base de datos
$idUsuario = $_SESSION['id_usuario'];
$query = "SELECT nombre FROM usuarios WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bindParam(1, $idUsuario);
$stmt->execute();
$nombreUsuario = $stmt->fetchColumn();

// Cargar plantilla HTML y reemplazar {{username}}
$html = file_get_contents("menu.html");
$html = str_replace("{{username}}", htmlspecialchars($nombreUsuario), $html);

// Mostrar resultado
echo $html;
?>
