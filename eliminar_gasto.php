<?php
session_start();
require_once 'conexion.php';

if (!isset($_SESSION['id_usuario'])) {
    header("Location: login.php");
    exit();
}

$id_gasto = filter_input(INPUT_GET, 'id_gasto', FILTER_VALIDATE_INT);

if ($id_gasto) {
    try {
        $conexion = Conexion::conectar();
        $sql = "DELETE FROM gastos WHERE id_gasto = :id_gasto";
        $stmt = $conexion->prepare($sql);
        $stmt->bindParam(':id_gasto', $id_gasto, PDO::PARAM_INT);
        $stmt->execute();
    } catch (PDOException $e) {
        error_log("Error al eliminar gasto: " . $e->getMessage());
    }
}

header("Location: ver_gastos.php");
exit();
?>
