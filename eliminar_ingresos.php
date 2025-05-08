<?php
session_start();
require_once __DIR__ . '/conexion.php';


if (!isset($_SESSION['id_usuario'])) {
    header("Location: login.html");
    exit();
}

$idUsuario = $_SESSION['id_usuario'];

if (isset($_POST['eliminar_todos']) && isset($_POST['ingresos_eliminar'])) {
    $ingresosAEliminar = $_POST['ingresos_eliminar'];

    try {
        $conexion = Conexion::conectar();

        $sqlEliminar = "DELETE FROM ingresos WHERE id = :id AND id_usuario = :id_usuario";
        $stmtEliminar = $conexion->prepare($sqlEliminar);

        foreach ($ingresosAEliminar as $idIngreso) {
            $stmtEliminar->bindParam(':id', $idIngreso);
            $stmtEliminar->bindParam(':id_usuario', $idUsuario);
            $stmtEliminar->execute();
        }

        header("Location: ver_ingresos.php"); 
        exit();
    } catch (PDOException $e) {
        echo "Error al eliminar ingresos: " . $e->getMessage();
    }
} else {
    header("Location: ver_ingresos.php"); 
    exit();
}
?>
