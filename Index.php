<?php
session_start();
// Si el usuario ya ha iniciado sesión
if (isset($_SESSION['usuario'])) {
    header("Location: dashboard.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Gasto Simple</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="assets/css/estilos.css">
</head>
<body>

  <header>
    <img src="img/logo.png" alt="Logo Gasto Simple">
    <h1>Bienvenido a Gasto Simple</h1>
    <p>Tu solución para el control de finanzas personales</p>
  </header>

  <div class="botones">
    <a href="login.php">Ingresar</a>
    <a href="register.php">Registrarse</a>
  </div>

  <section class="demos">
    <div class="demo">
      <img src="captura1.png" alt="Captura 1">
      <p>Visualiza tus gastos e ingresos fácilmente</p>
    </div>
    <div class="demo">
      <img src="captura2.png" alt="Captura 2">
      <p>Establece y sigue tus metas de ahorro</p>
    </div>
  </section>

  <footer>
    <a href="nosotros.php">Nosotros</a> |
    <a href="pqr.php">PQR</a> |
    <a href="terminos.php">Términos y Condiciones</a>
  </footer>

</body>
</html>
