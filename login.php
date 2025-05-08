<?php
session_start();
require_once __DIR__ . '/conexion.php';


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $correo = $_POST['correo'];
    $password = $_POST['password'];

    try {
        $conexion = Conexion::conectar();
        $sql = "SELECT id, password, id_rol FROM usuarios WHERE correo = :correo";
        $stmt = $conexion->prepare($sql);
        $stmt->bindParam(':correo', $correo);
        $stmt->execute();

        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($usuario && password_verify($password, $usuario['password'])) {
            $_SESSION['id_usuario'] = $usuario['id'];
            $_SESSION['rol'] = $usuario['id_rol'];

            if ($usuario['id_rol'] == 2) {
                header("Location: admin_dashboard.php");
            } else {
                header("Location: menu.php");
            }
            exit();
        } else {
            echo "Correo o contraseña incorrectos.";
        }
    } catch (PDOException $e) {
        echo "Error en la conexión: " . $e->getMessage();
    }
}
?>
