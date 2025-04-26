<?php
session_start();
require_once 'conexion.php';

if (!isset($_SESSION['id'])) {
    header("Location: login.html");
    exit();
}

$idUsuario = $_SESSION['id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verificar si los campos están presentes
    if (isset($_POST['nombre'], $_POST['correo'], $_POST['idioma'], $_POST['moneda'])) {
        $nombre   = $_POST['nombre'];
        $correo   = $_POST['correo'];
        $idioma   = $_POST['idioma'];
        $moneda   = $_POST['moneda'];
        $password = $_POST['password'];

        try {
            $conexion = Conexion::conectar();

            // 1. Actualizar datos básicos del usuario
            if (!empty($password)) {
                $passwordHash = password_hash($password, PASSWORD_DEFAULT);
                $sql = "UPDATE usuarios 
                        SET nombre = :nombre, 
                            correo = :correo, 
                            password = :password 
                        WHERE id = :id";
                $stmt = $conexion->prepare($sql);
                $stmt->bindParam(':password', $passwordHash);
            } else {
                $sql = "UPDATE usuarios 
                        SET nombre = :nombre, 
                            correo = :correo 
                        WHERE id = :id";
                $stmt = $conexion->prepare($sql);
            }

            $stmt->bindParam(':nombre', $nombre);
            $stmt->bindParam(':correo', $correo);
            $stmt->bindParam(':id', $idUsuario, PDO::PARAM_INT);
            $stmt->execute();

            // 2. Actualizar configuración de usuario (idioma y moneda)
            $sqlConfig = "UPDATE configuracion_usuario 
                          SET idioma = :idioma, 
                              moneda = :moneda 
                          WHERE id_usuario = :id";
            $stmtConfig = $conexion->prepare($sqlConfig);
            $stmtConfig->bindParam(':idioma', $idioma);
            $stmtConfig->bindParam(':moneda', $moneda);
            $stmtConfig->bindParam(':id', $idUsuario, PDO::PARAM_INT);
            $stmtConfig->execute();

            // Redirigir al menú después de la actualización
            header("Location: menu.html"); // Redirección al menú
            exit();

        } catch (PDOException $e) {
            echo "Error al actualizar perfil: " . $e->getMessage();
        }
    } else {
        echo "Faltan campos por llenar.";
    }
}
?>
