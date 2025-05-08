<?php
session_start();
require_once __DIR__ . '/conexion.php';


if (!isset($_SESSION['id_usuario'])) {
    header("Location: login.php");
    exit();
}

$usuario_id = $_SESSION['id_usuario'];
$conexion = Conexion::conectar();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre'] ?? '');
    $correo = trim($_POST['correo'] ?? '');
    $contrasena = $_POST['contrasena'] ?? '';
    $moneda = $_POST['moneda'] ?? '';
    $idioma = $_POST['idioma'] ?? '';

    $campos = [];
    $valores = [];

    if ($nombre !== '') {
        $campos[] = "nombre = ?";
        $valores[] = $nombre;
    }

    if ($correo !== '') {
        $campos[] = "correo = ?";
        $valores[] = $correo;
    }

    if ($contrasena !== '') {
        $campos[] = "password = ?";
        $valores[] = password_hash($contrasena, PASSWORD_DEFAULT);
    }

    if ($moneda !== '') {
        $campos[] = "moneda = ?";
        $valores[] = $moneda;
    }

    if ($idioma !== '') {
        $campos[] = "idioma = ?";
        $valores[] = $idioma;
    }

    if (!empty($campos)) {
        $sql = "UPDATE usuarios SET " . implode(', ', $campos) . " WHERE id = ?";
        $valores[] = $usuario_id;

        $stmt = $conexion->prepare($sql);
        $stmt->execute($valores);
    }

    header("Location: perfil.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Configuración de Usuario - GastoSimple</title>
    <link rel="stylesheet" href="estilos.css">
</head>
<body>

<div class="configuracion">
    <h2>Configuración de Usuario</h2>

    <form method="POST">
        <div>
            <label for="nombre">Nombre (opcional):</label>
            <input type="text" name="nombre" id="nombre" placeholder="Nuevo nombre">
        </div>

        <div>
            <label for="correo">Correo (opcional):</label>
            <input type="email" name="correo" id="correo" placeholder="Nuevo correo">
        </div>

        <div>
            <label for="contrasena">Contraseña (opcional):</label>
            <input type="password" name="contrasena" id="contrasena" placeholder="Nueva contraseña">
        </div>

        <div>
            <label for="moneda">Moneda (opcional):</label>
            <select name="moneda" id="moneda">
                <option value="">-- Seleccionar --</option>
                <option value="COP">COP</option>
                <option value="USD">USD</option>
                <option value="EUR">EUR</option>
                <option value="MXN">MXN</option>
            </select>
        </div>

        <div>
            <label for="idioma">Idioma (opcional):</label>
            <select name="idioma" id="idioma">
                <option value="">-- Seleccionar --</option>
                <option value="es">Español</option>
                <option value="en">Inglés</option>
                <option value="fr">Francés</option>
                <option value="zh">Chino</option>
            </select>
        </div>

        <button type="submit">Guardar Cambios</button>
    </form>
</div>

</body>
</html>
