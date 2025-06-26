<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Gasto Simple - Controla tus Finanzas</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://unpkg.com/aos@2.3.1/dist/aos.css">
  <style>
    html {
      scroll-behavior: smooth;
    }
    * {
      margin: 0; padding: 0; box-sizing: border-box;
    }
    body {
      font-family: 'Inter', sans-serif;
      color: white;
      overflow-x: hidden;
      background: transparent;
      position: relative;
    }
    body::before {
      content: '';
      position: fixed;
      top: 0; left: 0;
      width: 100%; height: 100%;
      background: linear-gradient(135deg, #0B0B52, #1D2B64, #0C1634, #0B0B52);
      background-size: 300% 300%;
      animation: backgroundAnim 25s ease-in-out infinite;
      z-index: -2;
    }
    @keyframes backgroundAnim {
      0%, 100% { background-position: 0% 50%; }
      50% { background-position: 100% 50%; }
    }

    .botones {
      text-align: center;
      margin-top: 40px;
      display: flex;
      justify-content: center;
      gap: 20px;
      flex-wrap: wrap;
    }

    .btn {
      padding: 14px 32px;
      font-size: 1.1rem;
      font-weight: 600;
      border-radius: 50px;
      text-decoration: none;
      transition: all 0.3s ease;
      border: 2px solid transparent;
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
    }

    .btn-login {
      background-color: #ffffff;
      color: #0B0B52;
    }

    .btn-login:hover {
      background-color: transparent;
      border-color: #ffffff;
      color: #ffffff;
    }

    .btn-register {
      background-color: transparent;
      border: 2px solid #ffffff;
      color: #ffffff;
    }

    .btn-register:hover {
      background-color: #00D4FF;
      color: #0B0B52;
      border-color: #00D4FF;
    }

    #particles-js {
      position: fixed;
      width: 100%;
      height: 100%;
      top: 0; left: 0;
      z-index: -1;
    }

    header {
      text-align: center;
      padding: 60px 20px 30px;
    }

    .logo {
      max-width: 280px;
      margin: 0 auto 20px;
      display: block;
    }

    .hero h1 {
      font-size: 2.5rem;
      margin-bottom: 10px;
    }

    .hero p {
      font-size: 1.2rem;
      margin-bottom: 20px;
    }

    nav {
      text-align: center;
      margin: 30px 0;
    }

    nav a {
      color: white;
      margin: 0 15px;
      text-decoration: none;
      font-weight: bold;
      font-size: 1rem;
    }

    section {
      max-width: 1000px;
      margin: auto;
      padding: 60px 20px;
      text-align: center;
    }

    section h2 {
      font-size: 2rem;
      margin-bottom: 30px;
    }

    .features, .screenshots, .testimonials, .faq {
      display: grid;
      gap: 30px;
    }

    .features div, .testimonials div, .faq div {
      background: rgba(255,255,255,0.05);
      padding: 20px;
      border-radius: 10px;
      backdrop-filter: blur(10px);
    }

    .screenshots img {
      max-width: 100%;
      border-radius: 10px;
    }

    .donate img {
      height: 60px;
      margin: 10px;
    }

    footer {
      text-align: center;
      padding: 30px;
      font-size: 0.9rem;
      color: #ccc;
    }

    footer a {
      color: #00D4FF;
      margin: 0 10px;
      text-decoration: none;
    }

    .donate {
      margin: 60px auto;
      padding: 40px 20px;
      text-align: center;
      max-width: 800px;
      background: rgba(255, 255, 255, 0.05);
      backdrop-filter: blur(12px);
      border-radius: 16px;
      border: 1px solid rgba(255, 255, 255, 0.2);
      box-shadow: 0 8px 24px rgba(0, 0, 0, 0.4);
    }

    .donate h2 {
      font-size: 2rem;
      margin-bottom: 10px;
      color: #fff;
    }

    .donate p {
      font-size: 1.1rem;
      margin-bottom: 30px;
      color: #ccc;
    }

    .donate-buttons {
      display: flex;
      justify-content: center;
      gap: 30px;
      flex-wrap: wrap;
    }

    .donate-btn {
      display: flex;
      flex-direction: column;
      align-items: center;
      background: rgba(255, 255, 255, 0.08);
      border-radius: 16px;
      padding: 20px;
      text-decoration: none;
      color: white;
      transition: transform 0.3s ease, box-shadow 0.3s ease;
      width: 140px;
      backdrop-filter: blur(6px);
      border: 1px solid rgba(255, 255, 255, 0.15);
      box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3);
    }

    .donate-btn:hover {
      transform: scale(1.08);
      box-shadow: 0 8px 25px rgba(0, 212, 255, 0.5);
    }

    .donate-btn img {
      width: 64px;
      height: 64px;
      margin-bottom: 12px;
      transition: transform 0.3s;
    }

    .donate-btn:hover img {
      transform: rotate(8deg) scale(1.1);
    }

    .donate-btn span {
      font-weight: bold;
      text-align: center;
      font-size: 0.95rem;
    }

    @keyframes floatIn {
      from {
        opacity: 0;
        transform: translateY(50px);
      }
      to {
        opacity: 1;
        transform: translateY(0);
      }
    }
  </style>
</head>
<body>
  <div id="particles-js"></div>
  <header class="hero" data-aos="fade-down">
    <img src="img/logo 1.png" alt="Logo Gasto Simple" class="logo">
    <h1>Bienvenido a Gasto Simple</h1>
    <p>Tu herramienta para controlar ingresos, gastos y ahorrar con inteligencia.</p>
    <nav class="botones">
      <a href="login.php" class="btn btn-login">Iniciar sesión</a>
      <a href="register.php" class="btn btn-register">Registrarse</a>
    </nav>
  </header>

  <section class="features" data-aos="fade-up">
    <h2>Características</h2>
    <div>Registro rápido de ingresos y gastos</div>
    <div>Gráficas para visualizar tus finanzas</div>
    <div>Metas de ahorro y alertas</div>
  </section>

  <section class="screenshots" data-aos="fade-up">
    <h2>Capturas del Dashboard</h2>
    <img src="captura1.png" alt="Dashboard 1">
    <img src="captura2.png" alt="Dashboard 2">
  </section>

  <section class="testimonials" data-aos="fade-up">
    <h2>Testimonios</h2>
    <div>"GastoSimple me ayudó a dejar de gastar en tonterías" - Ana</div>
    <div>"En dos meses logré ahorrar lo que antes no podía en un año." - Jorge</div>
  </section>

  <section class="faq" data-aos="fade-up">
    <h2>Preguntas Frecuentes</h2>
    <div><strong>¿GastoSimple es gratis?</strong><br>Sí, 100% gratuito y sin anuncios molestos.</div>
    <div><strong>¿Se guarda mi información?</strong><br>Todo se almacena de forma segura en tu cuenta personal.</div>
  </section>

  <section class="donate" data-aos="fade-up">
    <h2>Dóname un Café ☕</h2>
    <p>Apoya el desarrollo de Gasto Simple</p>
    <div class="donate-buttons">
      <a href="#" target="_blank" class="donate-btn">
        <img src="img/taza-de-cafe.png" alt="Buy me a coffee">
        <span>Buy me a coffee</span>
      </a>
      <a href="#" target="_blank" class="donate-btn">
        <img src="img/patreon.png" alt="Patreon">
        <span>Apóyame en Patreon</span>
      </a>
    </div>
  </section>

  <footer>
    <a href="nosotros.php">Nosotros</a> |
    <a href="pqr.php">PQR</a> |
    <a href="terminos.php">Términos</a>
  </footer>

  <script src="https://cdn.jsdelivr.net/npm/aos@2.3.1/dist/aos.js"></script>
  <script src="https://cdn.jsdelivr.net/particles.js/2.0.0/particles.min.js"></script>
  <script>
    AOS.init({
      duration: 1000,
      once: true,
    });

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
        "detect_on": "canvas",
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
