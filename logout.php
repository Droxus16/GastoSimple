<?php
session_start();

// Vaciar todas las variables de sesión
$_SESSION = [];

// Eliminar cookie de "Remember Me" si existe
if (isset($_COOKIE['rememberme'])) {
    setcookie('rememberme', '', time() - 3600, '/');
}

// Opcional: eliminar todas las cookies de sesión
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    );
}

// Destruir la sesión
session_destroy();

// Redirigir al login con mensaje opcional
header("Location: login.php?mensaje=Sesión cerrada correctamente");
exit();
?>
