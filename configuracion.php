<?php
session_start();
require_once 'conexion.php';

if (!isset($_SESSION['id_usuario'])) {
    header("Location: login.php");
    exit();
}

$id_usuario = $_SESSION['id_usuario'];
$conexion = Conexion::conectar();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $moneda = $_POST['moneda'];
    $idioma = $_POST['idioma'];
    $notificaciones = isset($_POST['notificaciones']) ? 1 : 0;

    $sql = "UPDATE configuracion SET moneda = :moneda, idioma = :idioma, notificaciones = :notificaciones WHERE id_usuario = :id_usuario";
    $stmt = $conexion->prepare($sql);
    $stmt->bindParam(':moneda', $moneda);
    $stmt->bindParam(':idioma', $idioma);
    $stmt->bindParam(':notificaciones', $notificaciones);
    $stmt->bindParam(':id_usuario', $id_usuario);

    if ($stmt->execute()) {
        echo "<p style='color: green;'>Configuración actualizada.</p>";
    } else {
        echo "<p style='color: red;'>Error al actualizar configuración.</p>";
    }
}
?>
<form method="post">
    Moneda: 
    <select name="moneda">
        <option value="COP">Pesos Colombianos</option>
        <option value="USD">Dólares</option>
        <option value="EUR">Euros</option>
    </select><br>

    Idioma: 
    <select name="idioma">
        <option value="es">Español</option>
        <option value="en">Inglés</option>
    </select><br>

    Notificaciones: <input type="checkbox" name="notificaciones" checked><br>
    <input type="submit" value="Guardar Configuración">
</form>

<br>
<a href="menu.php">
    <button>Volver al Menú</button>
</a>
