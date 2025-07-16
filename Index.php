<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Gasto Simple — Controla tus Finanzas</title>
  <meta name="description" content="Gasto Simple es tu app web para registrar ingresos, gastos y ahorrar mejor. 100% gratuita y segura.">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://unpkg.com/aos@2.3.1/dist/aos.css">
  <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
  <link rel="icon" href="img/favicon.png" type="image/png">
  <style>
    :root {
      --color-primary: #00D4FF;
      --color-bg-dark: #0B0B52;
      --color-bg-gradient: linear-gradient(135deg, #0B0B52, #1D2B64, #0C1634, #0B0B52);
      --color-text: #ffffff;
    }

    html { scroll-behavior: smooth; }
    body {
      margin: 0; font-family: 'Inter', sans-serif;
      color: var(--color-text); overflow-x: hidden;
      position: relative; box-sizing: border-box;
    }

    body::before {
      content: ''; position: fixed; top: 0; left: 0;
      width: 100%; height: 100%;
      background: var(--color-bg-gradient);
      background-size: 300% 300%;
      animation: backgroundAnim 25s ease-in-out infinite;
      z-index: -2;
    }

    @keyframes backgroundAnim {
      0%, 100% { background-position: 0% 50%; }
      50% { background-position: 100% 50%; }
    }

    #particles-js {
      position: fixed; top: 0; left: 0; width: 100%; height: 100%;
      z-index: -1;
    }

    .navbar {
      position: sticky;
      top: 0;
      width: 100%;
      background: rgba(0,0,0,0.5);
      backdrop-filter: blur(10px);
      z-index: 99;
      padding: 12px 0;
    }

    .nav-container {
      max-width: 1100px;
      margin: auto;
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 0 20px;
    }

    .nav-logo {
      font-size: 1.4rem;
      font-weight: bold;
      color: white;
      text-decoration: none;
    }

    .nav-links a {
      margin-left: 20px;
      text-decoration: none;
      color: white;
      font-weight: 500;
      transition: color 0.3s;
    }

    .nav-links a:hover {
      color: var(--color-primary);
    }

    .nav-login {
      font-size: 1.5rem;
      vertical-align: middle;
    }

    header {
      text-align: center;
      padding: 50px 20px 30px;
    }

    header img.logo {
      max-width: 240px;
      height: auto;
      margin-bottom: 20px;
      transition: transform 0.3s ease;
    }

    header img.logo:hover {
      transform: scale(1.05);
    }

    header h1 {
      font-size: 3.2rem;
      margin: 10px 0;
      color: var(--color-primary);
    }

    header p {
      font-size: 1.3rem;
      max-width: 600px;
      margin: 0 auto 20px;
      opacity: 0.9;
    }

    nav.botones {
      display: flex;
      justify-content: center;
      gap: 16px;
      flex-wrap: wrap;
      margin-top: 20px;
    }

    .btn {
      padding: 12px 28px;
      font-size: 1rem;
      font-weight: 600;
      border-radius: 50px;
      text-decoration: none;
      transition: all 0.3s ease;
      border: 2px solid transparent;
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
    }

    .btn-login {
      background-color: #ffffff;
      color: var(--color-bg-dark);
    }

    .btn-login:hover {
      background-color: transparent;
      color: #ffffff;
      border-color: #ffffff;
    }

    .btn-register {
      background-color: transparent;
      border: 2px solid #ffffff;
      color: #ffffff;
    }

    .btn-register:hover {
      background-color: var(--color-primary);
      color: var(--color-bg-dark);
      border-color: var(--color-primary);
    }

    section {
      max-width: 1100px;
      margin: auto;
      padding: 40px 20px;
    }

    section h2 {
      font-size: 2.2rem;
      margin-bottom: 30px;
      color: var(--color-primary);
      text-align: center;
    }

    .grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
      gap: 25px;
    }

    .card {
      background: rgba(255,255,255,0.05);
      padding: 20px;
      border-radius: 14px;
      backdrop-filter: blur(10px);
      box-shadow: 0 4px 12px rgba(0,0,0,0.2);
      transition: transform 0.3s;
      text-align: left;
    }

    .card:hover {
      transform: translateY(-5px);
    }

    .screenshots img {
      max-width: 100%;
      height: auto;
      border-radius: 12px;
      box-shadow: 0 4px 15px rgba(0,0,0,0.3);
    }

    .donate-buttons {
      display: flex;
      justify-content: center;
      gap: 30px;
      flex-wrap: wrap;
      margin-top: 20px;
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
      transition: transform 0.3s, box-shadow 0.3s;
      width: 160px;
      backdrop-filter: blur(6px);
    }

    .donate-btn:hover {
      transform: scale(1.08);
      box-shadow: 0 8px 25px rgba(0, 212, 255, 0.5);
    }

    .donate-btn img {
      max-width: 80px;
      height: auto;
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

    footer {
      text-align: center;
      padding: 30px;
      font-size: 0.9rem;
      color: #ccc;
    }

    footer a {
      color: var(--color-primary);
      margin: 0 10px;
      text-decoration: none;
    }

    @media (max-width: 768px) {
      .nav-links { display: none; }
      header h1 { font-size: 2.2rem; }
      header p { font-size: 1.1rem; }
    }
    :root {
      --color-primary: #00D4FF;
      --color-bg-dark: #0B0B52;
      --color-bg-gradient: linear-gradient(135deg, #0B0B52, #1D2B64, #0C1634, #0B0B52);
      --color-text: #ffffff;
    }

    html { scroll-behavior: smooth; }
    body {
      margin: 0; font-family: 'Inter', sans-serif;
      color: var(--color-text); overflow-x: hidden;
      position: relative; box-sizing: border-box;
    }

    body::before {
      content: ''; position: fixed; top: 0; left: 0;
      width: 100%; height: 100%;
      background: var(--color-bg-gradient);
      background-size: 300% 300%;
      animation: backgroundAnim 25s ease-in-out infinite;
      z-index: -2;
    }

    @keyframes backgroundAnim {
      0%, 100% { background-position: 0% 50%; }
      50% { background-position: 100% 50%; }
    }

    #particles-js {
      position: fixed; top: 0; left: 0; width: 100%; height: 100%;
      z-index: -1;
    }

    .navbar {
      position: sticky;
      top: 0;
      width: 100%;
      background: rgba(0,0,0,0.5);
      backdrop-filter: blur(10px);
      z-index: 99;
      padding: 12px 0;
    }

    .nav-container {
      max-width: 1100px;
      margin: auto;
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 0 20px;
    }

    .nav-logo {
      font-size: 1.4rem;
      font-weight: bold;
      color: white;
      text-decoration: none;
    }

    .nav-links {
      display: flex;
      align-items: center;
    }

    .nav-links a {
      margin-left: 20px;
      text-decoration: none;
      color: white;
      font-weight: 500;
      transition: color 0.3s;
    }

    .nav-links a:hover {
      color: var(--color-primary);
    }

    .menu-toggle {
      display: none;
      font-size: 1.8rem;
      color: white;
      cursor: pointer;
    }

    @media (max-width: 768px) {
      .nav-links {
        display: none;
        flex-direction: column;
        background: rgba(0, 0, 0, 0.8);
        position: absolute;
        top: 60px;
        right: 0;
        width: 100%;
        padding: 20px;
      }

      .nav-links.show {
        display: flex;
      }

      .menu-toggle {
        display: block;
      }
    }
  </style>
</head>
<body>
  <div id="particles-js"></div>
<nav class="navbar" data-aos="fade-down">
  <div class="nav-container">
    <a href="#inicio" class="nav-logo">GastoSimple</a>
    <!-- Botón hamburguesa -->
    <div class="menu-toggle" id="menu-toggle">
      <i class='bx bx-menu'></i>
    </div>
    <!-- Menú de enlaces -->
    <div class="nav-links" id="nav-links">
      <a href="#features">Características</a>
      <a href="#screenshots">Capturas</a>
      <a href="#testimonials">Testimonios</a>
      <a href="#faq">FAQ</a>
      <a href="#donate">Donar</a>
      <a href="login.php" class="nav-login"><i class='bx bx-user'></i></a>
    </div>
  </div>
</nav>
  <header class="hero" id="inicio" data-aos="fade-down">
    <img src="img/logo 1.png" alt="Logo Gasto Simple" class="logo">
    <h1>Gasto Simple</h1>
    <p>Tu herramienta intuitiva para registrar ingresos, gastos y ahorrar mejor.</p>
    <nav class="botones">
      <a href="login.php" class="btn btn-login">Iniciar sesión</a>
      <a href="register.php" class="btn btn-register">Registrarse</a>
    </nav>
  </header>
  <section id="features" class="features" data-aos="fade-up">
    <h2>Características Principales</h2>
    <div class="grid">
      <div class="card"><i class='bx bx-wallet-alt'></i> Registro rápido de ingresos y gastos</div>
      <div class="card"><i class='bx bx-line-chart'></i> Gráficas y reportes interactivos</div>
      <div class="card"><i class='bx bx-bullseye'></i> Metas de ahorro y alertas inteligentes</div>
    </div>
  </section>
  <section id="screenshots" class="screenshots" data-aos="fade-up">
    <h2>Tendras control financiero con nosotros</h2>
    <div class="grid"> 
      <img src="img/reportes/descontrol.jpg" alt="Sin GastoSimple">
      <img src="img/reportes/control.jpg" alt="Con GastoSimple">
    </div>
  </section>
  <section id="testimonials" class="testimonials" data-aos="fade-up">
    <h2>Lo que Dicen Nuestros Usuarios</h2>
    <div class="grid">
      <div class="card">“GastoSimple me ayudó a ahorrar sin darme cuenta.” — Laura</div>
      <div class="card">“Una app sencilla y eficaz para controlar mis gastos.” — Carlos</div>
    </div>
  </section>
  <section id="faq" class="faq" data-aos="fade-up">
    <h2>Preguntas Frecuentes</h2>
    <div class="grid">
      <div class="card"><strong>¿GastoSimple es gratuito?</strong><br>Sí, sin costos ocultos ni anuncios molestos.</div>
      <div class="card"><strong>¿Dónde se guardan mis datos?</strong><br>En servidores seguros. Tú tienes el control.</div>
    </div>
  </section>

  <section id="donate" class="donate" data-aos="fade-up">
    <h2>¿Te gusta Gasto Simple?</h2>
    <p style="text-align:center;">Apoya el proyecto con un café ☕</p>
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
    <a href="pqr_guia.php">PQR</a> |
    <a href="terminos.php">Términos y condiciones</a>
  </footer>

  <script src="https://cdn.jsdelivr.net/npm/aos@2.3.1/dist/aos.js"></script>
  <script src="https://cdn.jsdelivr.net/particles.js/2.0.0/particles.min.js"></script>
  <script>
    AOS.init({ duration: 1000, once: true });
    particlesJS('particles-js', {
      particles: {
        number: { value: 80, density: { enable: true, value_area: 800 } },
        color: { value: "#00D4FF" },
        shape: { type: "circle" },
        opacity: { value: 0.5, random: true },
        size: { value: 3, random: true },
        line_linked: { enable: true, distance: 150, color: "#00D4FF", opacity: 0.4, width: 1 },
        move: { enable: true, speed: 3 }
      },
      interactivity: {
        events: {
          onhover: { enable: true, mode: "repulse" },
          onclick: { enable: true, mode: "push" }
        }
      },
      retina_detect: true
    });

    // Toggle menú hamburguesa
    const toggle = document.getElementById('menu-toggle');
    const navLinks = document.getElementById('nav-links');
    toggle.addEventListener('click', () => {
      navLinks.classList.toggle('show');
    });
  </script>
</body>
</html>
<?php include 'includes/footer.php'; ?>
