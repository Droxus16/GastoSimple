<?php
session_start();
require_once 'conexion.php';

// Verificar si el usuario está autenticado
if (!isset($_SESSION['id_usuario'])) {
    header("Location: login.php");
    exit();
}

$idUsuario = $_SESSION['id_usuario'];

if (isset($_POST['eliminar_todos']) && isset($_POST['gastos_eliminar'])) {
    $gastosAEliminar = $_POST['gastos_eliminar'];

    try {
        $conexion = Conexion::conectar();

        // Eliminar los gastos seleccionados
        $sqlEliminar = "DELETE FROM gastos WHERE id = :id AND id_usuario = :id_usuario";
        $stmtEliminar = $conexion->prepare($sqlEliminar);

        foreach ($gastosAEliminar as $idGasto) {
            $stmtEliminar->bindParam(':id', $idGasto);
            $stmtEliminar->bindParam(':id_usuario', $idUsuario);
            $stmtEliminar->execute();
        }

        header("Location: ver_gastos.php");  // Redirigir después de eliminar
        exit();
    } catch (PDOException $e) {
        echo "Error al eliminar gastos: " . $e->getMessage();
    }
} else {
    header("Location: ver_gastos.php"); // Si no se envió el formulario correctamente
    exit();
}
?>
