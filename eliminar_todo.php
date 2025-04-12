<?php
session_start();
require_once 'conexion.php';

if (!isset($_SESSION['id_usuario'])) {
    header("Location: login.php");
    exit();
}

$id_usuario = $_SESSION['id_usuario'];
$conexion = Conexion::conectar();

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['eliminar_todo'])) {
    $sql1 = "DELETE FROM gastos WHERE id_usuario = :id_usuario";
    $sql2 = "DELETE FROM ingresos WHERE id_usuario = :id_usuario";

    $stmt1 = $conexion->prepare($sql1);
    $stmt2 = $conexion->prepare($sql2);

    $stmt1->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
    $stmt2->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);

    $stmt1->execute();
    $stmt2->execute();
}

header("Location: menu.php");
exit();
?>
