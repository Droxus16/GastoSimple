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
      font-family: 'Inter', 'Segoe UI', Arial, sans-serif;
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
      background: rgba(255, 255, 255, 0.12);
      border: 1.5px solid rgba(0,212,255,0.18);
      border-radius: 22px;
      backdrop-filter: blur(18px);
      box-shadow: 0 8px 32px rgba(0,212,255,0.10);
      padding: 44px 32px 36px 32px;
      z-index: 1;
      max-width: 540px;
      margin: 48px auto;
      position: relative;
    }
    .glass-card h2 {
      text-align: center; 
      margin-bottom: 18px; 
      color: #00D4FF;
      font-weight: 700;
      letter-spacing: 1px;
    }
    .glass-card .intro {
      text-align: center;
      color: #b2ebf2;
      font-size: 1.08rem;
      margin-bottom: 24px;
      opacity: 0.95;
    }
    .pqr-steps {
      margin: 0 0 24px 0;
      padding: 0;
      list-style: none;
      counter-reset: step;
    }
    .pqr-step {
      display: flex;
      align-items: flex-start;
      gap: 16px;
      margin-bottom: 22px;
      background: rgba(0,212,255,0.07);
      border-radius: 14px;
      padding: 16px 14px;
      box-shadow: 0 2px 8px rgba(0,212,255,0.07);
      position: relative;
    }
    .pqr-step-icon {
      flex-shrink: 0;
      background: #00D4FF;
      color: #0B0B52;
      border-radius: 50%;
      width: 38px;
      height: 38px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 1.5rem;
      font-weight: bold;
      box-shadow: 0 2px 8px rgba(0,212,255,0.13);
      margin-top: 2px;
    }
    .pqr-step-content {
      flex: 1;
      color: #e0f7fa;
      font-size: 1.05rem;
      line-height: 1.6;
    }
    .pqr-step-content a {
      color: #00D4FF;
      text-decoration: underline;
      font-weight: 500;
    }
    .pqr-step-content a:hover {
      color: #fff;
    }
    .glass-card hr {
      border-top: 1.5px solid #00D4FF;
      opacity: 0.25;
      margin: 32px 0 18px 0;
    }
    .pqr-actions {
      display: flex; 
      justify-content: center; 
      gap: 18px; 
      flex-wrap: wrap;
      margin-top: 18px;
    }
    .pqr-btn {
      background: linear-gradient(90deg, #00D4FF 0%, #1D2B64 100%);
      color: #fff;
      border: none;
      border-radius: 24px;
      padding: 12px 28px;
      font-size: 1.08rem;
      font-weight: 600;
      text-decoration: none;
      box-shadow: 0 2px 12px rgba(0,212,255,0.10);
      transition: background 0.18s, color 0.18s, transform 0.18s;
      display: flex;
      align-items: center;
      gap: 8px;
      cursor: pointer;
    }
    .pqr-btn:hover {
      background: #fff;
      color: #00D4FF;
      transform: translateY(-2px) scale(1.04);
    }
    .pqr-btn-outline {
      background: none;
      border: 2px solid #00D4FF;
      color: #00D4FF;
    }
    .pqr-btn-outline:hover {
      background: #00D4FF;
      color: #fff;
    }
    @media (max-width: 600px) {
      .glass-card {
        padding: 18px 4vw 18px 4vw;
        max-width: 98vw;
      }
      .pqr-step {
        flex-direction: column;
        gap: 8px;
        padding: 12px 8px;
      }
      .pqr-step-icon {
        margin-bottom: 4px;
      }
      .pqr-actions {
        flex-direction: column;
        gap: 10px;
      }
    }
  </style>
</head>
<body>

<div id="particles-js"></div>

<div class="container d-flex justify-content-center align-items-center" style="min-height: 100vh; position: relative; z-index: 1;">
  <div class="glass-card">
    <h2><i class="bi bi-question-circle"></i> Guía para enviar un PQR</h2>
    <div class="intro">
      ¿Tienes una <strong>petición</strong>, <strong>queja</strong> o <strong>reclamo</strong>? Aquí te explicamos paso a paso cómo hacerlo.<br>
      <span style="color:#00D4FF;">¡Es fácil, rápido y seguro!</span>
    </div>
    <ol class="pqr-steps">
      <li class="pqr-step">
        <div class="pqr-step-icon"><i class="bi bi-book"></i></div>
        <div class="pqr-step-content">
          <strong>Lee nuestros <a href="terminos.php" target="_blank">Términos y Condiciones</a></strong> para conocer tus derechos y cómo protegemos tu información.
        </div>
      </li>
      <li class="pqr-step">
        <div class="pqr-step-icon"><i class="bi bi-person-circle"></i></div>
        <div class="pqr-step-content">
          <strong>Inicia sesión</strong> con tu cuenta de usuario.<br>
          ¿No tienes cuenta? <a href="register.php">Regístrate aquí</a>.
        </div>
      </li>
      <li class="pqr-step">
        <div class="pqr-step-icon"><i class="bi bi-gear"></i></div>
        <div class="pqr-step-content">
          Ve a <strong>Ajustes</strong> en el menú lateral de la plataforma.
        </div>
      </li>
      <li class="pqr-step">
        <div class="pqr-step-icon"><i class="bi bi-chat-dots"></i></div>
        <div class="pqr-step-content">
          En la sección <strong>PQR</strong> escribe tu petición, queja o reclamo con todos los detalles necesarios.<br>
          <span style="color:#b2ebf2;">Entre más claro seas, más rápido podremos ayudarte.</span>
        </div>
      </li>
      <li class="pqr-step">
        <div class="pqr-step-icon"><i class="bi bi-shield-lock"></i></div>
        <div class="pqr-step-content">
          <strong>Tu información será gestionada de forma segura</strong> y según nuestros <a href="terminos.php" target="_blank">Términos y Condiciones</a>.
        </div>
      </li>
    </ol>
    <hr>
    <div class="text-center mb-2" style="font-size:1.08rem;">
      <strong>¿Listo para enviar tu PQR?</strong>
    </div>
    <div class="pqr-actions">
      <a href="login.php" class="pqr-btn"><i class="bi bi-box-arrow-in-right"></i> Iniciar sesión</a>
      <a href="register.php" class="pqr-btn pqr-btn-outline"><i class="bi bi-person-plus"></i> Registrarse</a>
      <a href="index.php" class="pqr-btn pqr-btn-outline"><i class="bi bi-house-door-fill"></i> Volver al Inicio</a>
    </div>
  </div>
</div>

<!-- Particles.js -->
<script src="https://cdn.jsdelivr.net/npm/particles.js@2.0.0/particles.min.js"></script>
  <script>
    particlesJS("particles-js", {
        "particles": {
        "number": { "value": 80, "density": { "enable": true, "value_area": 800 } },
        "color": { "value": "#00D4FF" },
        "shape": { "type": "circle" },
        "opacity": { "value": 0.5, "anim": { "enable": true, "speed": 1 } },
        "size": { "value": 3, "random": true, "anim": { "enable": true, "speed": 40 } },
        "line_linked": { "enable": true, "distance": 150, "color": "#00D4FF", "opacity": 0.4, "width": 1 },
        "move": { "enable": true, "speed": 1.2, "direction": "none", "random": false, "straight": false, "out_mode": "out" }
      },
      "interactivity": {
        "detect_on": "canvas",
        "events": {
          "onhover": { "enable": false },
          "onclick": { "enable": false }
        }
      },
      "retina_detect": true
    });
  </script>

<?php include 'includes/footer.php'; ?>
</body>
</html>