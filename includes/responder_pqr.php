<?php
require 'conexion.php'; // tu conexión MySQL

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pqr_id = intval($_POST['pqr_id']);
    $respuesta = trim($_POST['respuesta']);

    if ($pqr_id && $respuesta) {
        $stmt = $conn->prepare("UPDATE pqrs SET respuesta=?, estado='respondido', fecha_respuesta=NOW() WHERE id=?");
        $stmt->bind_param("si", $respuesta, $pqr_id);
        if ($stmt->execute()) {
            // Opcional: enviar correo al usuario
            // mail($correoUsuario, "Respuesta a su PQR", $respuesta);
            header("Location: admin_dashboard.php?msg=respuesta_ok");
        } else {
            echo "Error al guardar respuesta.";
        }
    }
}
?>
