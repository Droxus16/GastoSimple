<?php
session_start();
require_once 'conexion.php';

if (!isset($_SESSION['id_usuario'])) {
    header("Location: gastos.php");
    exit();
}

$id_usuario = $_SESSION['id_usuario'];
$conexion = Conexion::conectar();

$sql = "DELETE FROM gastos WHERE id_usuario = :id_usuario";
$stmt = $conexion->prepare($sql);
$stmt->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
$stmt->execute();

header("Location: gastos.php");
exit();
?>
