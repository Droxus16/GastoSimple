<?php
require_once '/conexion.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = $_POST['nombre'];
    $correo = $_POST['correo'];
    $password = $_POST['password'];
    $confirmar = $_POST['confirmar'];

    if ($password !== $confirmar) {
        echo "Las contraseñas no coinciden.";
        exit();
    }

    $conexion = Conexion::conectar();
    $stmt = $conexion->prepare("SELECT id FROM usuarios WHERE correo = :correo");
    $stmt->bindParam(':correo', $correo);
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        echo "El correo ya está registrado.";
        exit();
    }

    $stmt = $conexion->prepare("SELECT id FROM roles WHERE nombre = 'usuario_estandar'");
    $stmt->execute();
    $rol = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$rol) {
        echo "Error: Rol 'usuario_estandar' no encontrado.";
        exit();
    }

    $idRol = $rol['id'];
    $passwordHash = password_hash($password, PASSWORD_DEFAULT);

    $sql = "INSERT INTO usuarios (nombre, correo, password, id_rol) 
            VALUES (:nombre, :correo, :password, :id_rol)";
    $stmt = $conexion->prepare($sql);
    $stmt->bindParam(':nombre', $nombre);
    $stmt->bindParam(':correo', $correo);
    $stmt->bindParam(':password', $passwordHash);
    $stmt->bindParam(':id_rol', $idRol);

    if ($stmt->execute()) {
        $idUsuario = $conexion->lastInsertId();
        $stmtConfig = $conexion->prepare("INSERT INTO configuracion_usuario (id_usuario) VALUES (:id_usuario)");
        $stmtConfig->bindParam(':id_usuario', $idUsuario);
        $stmtConfig->execute();
        session_start();
        $_SESSION['id_usuario'] = $idUsuario;

        header("Location: menu.php");
        exit();
    } else {
        echo "Error al registrar el usuario.";
    }
}
?>
