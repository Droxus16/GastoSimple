<?php include 'includes/header.php'; ?>
<?php session_start(); ?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <title>Guía PQR - Gasto Simple</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
  <style>
    body, html {
      height: 100%;
      margin: 0;
      background: linear-gradient(135deg, #0B0B52, #1D2B64, #0C1634);
      background-size: 300% 300%;
      animation: backgroundAnim 25s ease-in-out infinite;
      color: #fff;
      font-family: 'Inter', sans-serif;
      position: relative;
    }
    @keyframes backgroundAnim {
      0% { background-position: 0% 50%; }
      50% { background-position: 100% 50%; }
      100% { background-position: 0% 50%; }
    }
    #particles-js {
      position: fixed; top: 0; left: 0; width: 100%; height: 100%;
      z-index: 0;
    }
    .glass-card {
      background: rgba(255, 255, 255, 0.1);
      border: 1px solid rgba(255,255,255,0.2);
      border-radius: 15px;
      backdrop-filter: blur(15px);
      box-shadow: 0 8px 32px rgba(0,0,0,0.2);
      padding: 40px;
      z-index: 1;
    }
    .glass-card h2 {
      text-align: center; margin-bottom: 20px; color: #00D4FF;
    }
    .glass-card p {
      font-size: 1rem; margin-bottom: 15px;
    }
    .glass-card ol {
      margin-left: 20px;
    }
    .glass-card a {
      color: #00D4FF;
      text-decoration: underline;
    }
    .glass-card a:hover {
      text-decoration: underline;
    }
    .btns {
      display: flex; justify-content: center; gap: 20px; flex-wrap: wrap;
    }
    .btn-login {
      background-color: #00D4FF !important;
      color: #ffffff !important;
    }
    .btn-login:hover {
      background-color: #00B3CC !important;
      color: #ffffff !important;
    }
  </style>
</head>
<body>

<div id="particles-js"></div>

<div class="container d-flex justify-content-center align-items-center" style="min-height: 100vh; position: relative; z-index: 1;">
  <div class="glass-card col-md-8 col-lg-6">
    <h2>Guía para enviar un PQR</h2>
    <p>En <strong>Gasto Simple</strong> nos importa tu opinión. Tu PQR (<strong>Petición, Queja o Reclamo</strong>) será atendido con gusto para <strong>mejorar nuestro software</strong> y <strong>brindarte la mejor experiencia</strong>.</p>
    <p>Sigue estos pasos para enviarnos tu PQR:</p>

    <ol>
      <li>Lee nuestros <a href="terminos.php" target="_blank">Términos y Condiciones</a> para conocer tus derechos y cómo tratamos la información.</li>
      <li><a href="login.php">Inicia sesión</a> con tu cuenta de usuario. Si aún no tienes cuenta, <a href="register.php">regístrate aquí</a>.</li>
      <li>Una vez dentro, ve a <strong>Ajustes</strong> desde el menú lateral.</li>
      <li>En la sección <strong>PQR</strong> podrás escribir tu petición, queja o reclamo con todos los detalles necesarios.</li>
      <li>Recuerda que toda la información será gestionada según nuestros <a href="terminos.php" target="_blank">Términos y Condiciones</a>.</li>
    </ol>

    <hr class="text-white">

    <p class="text-center"><strong>¿Deseas hacer un PQR? Haz clic para continuar:</strong></p>

    <div class="btns mt-3">
        <a href="login.php" class="btn btn-login">
            <i class="bi bi-box-arrow-in-right"></i> Ir a Login
        </a>
        <a href="index.php" class="btn btn-outline-light">
            <i class="bi bi-house-door-fill"></i> Volver al Inicio
        </a>
    </div>
  </div>
</div>

<!-- Particles.js -->
<script src="https://cdn.jsdelivr.net/particles.js/2.0.0/particles.min.js"></script>
<script>
particlesJS("particles-js", {
  "particles": {
    "number": { "value": 80, "density": { "enable": true, "value_area": 800 } },
    "color": { "value": "#ffffff" },
    "shape": { "type": "circle" },
    "opacity": { "value": 0.5, "anim": { "enable": true, "speed": 1 } },
    "size": { "value": 3, "random": true, "anim": { "enable": true, "speed": 40 } },
    "line_linked": { "enable": true, "distance": 150, "color": "#ffffff", "opacity": 0.4, "width": 1 },
    "move": { "enable": true, "speed": 3 }
  },
  "interactivity": {
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

<?php include 'includes/footer.php'; ?>
</body>
</html>
