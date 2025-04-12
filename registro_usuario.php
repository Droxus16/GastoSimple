<?php
session_start();
require_once 'conexion.php';

class Usuario {
    private $conexion;

    public function __construct() {
        $this->conexion = Conexion::conectar();
    }

    public function registrarUsuario($username, $password, $correo) {
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

        $sql = "INSERT INTO usuarios (username, password_usuario, correo) VALUES (:username, :password, :correo)";
        $stmt = $this->conexion->prepare($sql);
        $stmt->bindParam(':username', $username);
        $stmt->bindParam(':password', $hashedPassword);
        $stmt->bindParam(':correo', $correo);

        if ($stmt->execute()) {
            $id_usuario = $this->conexion->lastInsertId();
            $this->crearConfiguracionPredeterminada($id_usuario);
            $_SESSION['id_usuario'] = $id_usuario;
            $_SESSION['username'] = $username;
            header("Location: menu.php");
            exit();
        } else {
            echo "Error al registrar usuario.";
        }
    }

    private function crearConfiguracionPredeterminada($id_usuario) {
        $sql = "INSERT INTO configuracion (id_usuario, moneda, idioma, notificaciones) VALUES (:id_usuario, 'COP', 'es', 1)";
        $stmt = $this->conexion->prepare($sql);
        $stmt->bindParam(':id_usuario', $id_usuario);
        $stmt->execute();
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $correo = $_POST['correo'];

    $usuario = new Usuario();
    $usuario->registrarUsuario($username, $password, $correo);
}
?>
