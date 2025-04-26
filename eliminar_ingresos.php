<?php
session_start();
require_once 'conexion.php';

// Verificar si el usuario está autenticado
if (!isset($_SESSION['id_usuario'])) {
    header("Location: login.php");
    exit();
}

$idUsuario = $_SESSION['id_usuario'];

if (isset($_POST['eliminar_todos']) && isset($_POST['ingresos_eliminar'])) {
    $ingresosAEliminar = $_POST['ingresos_eliminar'];

    try {
        $conexion = Conexion::conectar();

        // Eliminar los ingresos seleccionados
        $sqlEliminar = "DELETE FROM ingresos WHERE id = :id AND id_usuario = :id_usuario";
        $stmtEliminar = $conexion->prepare($sqlEliminar);

        foreach ($ingresosAEliminar as $idIngreso) {
            $stmtEliminar->bindParam(':id', $idIngreso);
            $stmtEliminar->bindParam(':id_usuario', $idUsuario);
            $stmtEliminar->execute();
        }

        header("Location: ver_ingresos.php");  // Redirigir después de eliminar
        exit();
    } catch (PDOException $e) {
        echo "Error al eliminar ingresos: " . $e->getMessage();
    }
} else {
    header("Location: ver_ingresos.php"); // Si no se envió el formulario correctamente
    exit();
}
?>
