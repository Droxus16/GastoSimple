<?php
session_start();
if (isset($_SESSION['usuario'])) {
    header("Location: dashboard.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>GastoSimple – Controla tus Finanzas</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;700&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="assets/css/estilos.css" />
  <script src="https://cdn.jsdelivr.net/particles.js/2.0.0/particles.min.js"></script>
</head>
<body>
  <div id="particles-js"></div>

  <header>
    <img src="img/logo1.png" alt="Logo Gasto Simple" class="logo" />
    <h1>Domina tus finanzas personales</h1>
    <p>Registra tus gastos, analiza tus hábitos y alcanza tus metas con <strong>GastoSimple</strong>.</p>
    <div class="botones">
      <a href="login.php">Ingresar</a>
      <a href="register.php" class="btn-cta">Comienza Gratis</a>
    </div>
    <nav class="navegacion">
      <a href="#caracteristicas">Características</a>
      <a href="#capturas">Capturas</a>
      <a href="#soporte">Soporte</a>
    </nav>
  </header>

  <section id="caracteristicas" class="caracteristicas">
    <div class="caracteristica">
      <h3>📊 Informes claros</h3>
      <p>Gráficas simples para visualizar ingresos y gastos.</p>
    </div>
    <div class="caracteristica">
      <h3>📆 Registro diario</h3>
      <p>Registra tus movimientos en segundos.</p>
    </div>
    <div class="caracteristica">
      <h3>🎯 Metas financieras</h3>
      <p>Define objetivos y hazles seguimiento.</p>
    </div>
    <div class="caracteristica">
      <h3>🔐 Datos protegidos</h3>
      <p>Tu información está cifrada y segura.</p>
    </div>
  </section>

  <section id="capturas" class="demos">
    <div class="demo">
      <img src="captura1.png" alt="Captura 1" />
      <p>Controla tus finanzas con claridad</p>
    </div>
    <div class="demo">
      <img src="captura2.png" alt="Captura 2" />
      <p>Analiza tu progreso mes a mes</p>
    </div>
  </section>

  <section id="soporte" class="contenedor">
    <div class="tarjeta">
      <h2>¿Tienes dudas o necesitas ayuda?</h2>
      <p>Escríbenos a través del formulario de contacto o revisa nuestras preguntas frecuentes.</p>
      <div class="acciones">
        <a href="pqr.php">PQR</a>
        <a href="terminos.php">Términos y Condiciones</a>
      </div>
    </div>
  </section>

  <footer>
    <p>&copy; <?php echo date("Y"); ?> GastoSimple | <a href="nosotros.php">Nosotros</a> | <a href="pqr.php">Contacto</a></p>
  </footer>

  <script>
    particlesJS("particles-js", {
      "particles": {
        "number": { "value": 80, "density": { "enable": true, "value_area": 800 } },
        "color": { "value": "#ffffff" },
        "shape": { "type": "circle" },
        "opacity": {
          "value": 0.5,
          "random": false,
          "anim": { "enable": true, "speed": 1, "opacity_min": 0.1, "sync": false }
        },
        "size": {
          "value": 3,
          "random": true,
          "anim": { "enable": true, "speed": 40, "size_min": 0.1, "sync": false }
        },
        "line_linked": {
          "enable": true,
          "distance": 150,
          "color": "#ffffff",
          "opacity": 0.4,
          "width": 1
        },
        "move": {
          "enable": true,
          "speed": 3,
          "direction": "none",
          "random": false,
          "straight": false,
          "out_mode": "out",
          "bounce": false
        }
      },
      "interactivity": {
        "detect_on": "window",
        "events": {
          "onhover": { "enable": true, "mode": "repulse" },
          "onclick": { "enable": true, "mode": "push" }
        },
        "modes": {
          "repulse": { "distance": 100, "duration": 0.4 },
          "push": { "particles_nb": 4 }
        }
      },
      "retina_detect": true
    });
  </script>
</body>
</html>
