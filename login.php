<?php
session_start();
require_once 'conexion.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $correo = trim($_POST['correo']);
    $password = trim($_POST['password']);

    try {
        $conexion = Conexion::conectar();
        $sql = "SELECT id_usuario, username, password_usuario FROM usuarios WHERE correo = :correo";
        $stmt = $conexion->prepare($sql);
        $stmt->bindParam(':correo', $correo);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password_usuario'])) {
            $_SESSION['id_usuario'] = $user['id_usuario'];
            $_SESSION['username'] = $user['username'];
            header("Location: menu.php");
            exit();
        } else {
            $error = "Correo o contraseña incorrectos.";
        }
    } catch (PDOException $e) {
        $error = "Error en la base de datos. Intente más tarde.";
    }

    $conexion = null;
}
?>

<form method="POST">
    <input type="email" name="correo" placeholder="Correo electrónico" required autocomplete="off">
    <input type="password" name="password" placeholder="Contraseña" required autocomplete="off">
    <button type="submit">Iniciar Sesión</button>
</form>

<?php if ($error): ?>
    <p style="color: red;"><?php echo htmlspecialchars($error); ?></p>
<?php endif; ?>
