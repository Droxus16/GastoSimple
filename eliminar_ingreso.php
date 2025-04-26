<?php
session_start();
require_once 'conexion.php';

if (!isset($_SESSION['id_usuario'])) {
    header("Location: login.html");
    exit();
}

$idUsuario = $_SESSION['id_usuario'];
$idIngreso = $_GET['id'];  // Obtenemos el ID del ingreso a eliminar

try {
    $conexion = Conexion::conectar();

    // Eliminar ingreso de la base de datos
    $sql = "DELETE FROM ingresos WHERE id = :id AND id_usuario = :idUsuario";
    $stmt = $conexion->prepare($sql);
    $stmt->bindParam(':id', $idIngreso);
    $stmt->bindParam(':idUsuario', $idUsuario);
    $stmt->execute();

    // Redirigir al listado de ingresos
    header("Location: ver_ingresos.php");
    exit();

} catch (PDOException $e) {
    echo "Error al eliminar ingreso: " . $e->getMessage();
}
?>
