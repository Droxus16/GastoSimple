<?php
require_once 'includes/db.php';

if (isset($_POST['correo'])) {
    $correo = $_POST['correo'];
    $db = db::conectar();
    $stmt = $db->prepare("SELECT id FROM usuarios WHERE correo = ?");
    $stmt->execute([$correo]);
    echo $stmt->rowCount() > 0 ? "existe" : "disponible";
}
?>
