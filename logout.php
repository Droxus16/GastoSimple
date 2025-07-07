<?php
session_start();
// Vaciar todas las variables de sesión
$_SESSION = [];
if (isset($_COOKIE['rememberme'])) {
    setcookie('rememberme', '', time() - 3600, '/');
}
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
// Redirigir al login con mensaje
header("Location: login.php?mensaje=Sesión cerrada correctamente");
exit();
?>
