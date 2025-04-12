<?php
session_start();
require_once 'conexion.php';

if (!isset($_SESSION['id_usuario'])) {
    header("Location: login.php");
    exit();
}

if (isset($_GET['id_ingreso'])) {
    $conexion = Conexion::conectar();
    $sql = "DELETE FROM ingresos WHERE id_ingreso = :id_ingreso";
    $stmt = $conexion->prepare($sql);
    $stmt->bindParam(':id_ingreso', $_GET['id_ingreso'], PDO::PARAM_INT);
    $stmt->execute();
    
    header("Location: ver_ingresos.php");
    exit();
}
?>
