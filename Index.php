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

.donate {
  text-align: center;
  margin-top: 40px;
}

.donate-desc {
  color: #e0f7fa;
  font-size: 1.15rem;
  margin-bottom: 24px;
  opacity: 0.92;
}

.donate-buttons {
  display: flex;
  justify-content: center;
  gap: 40px;
  flex-wrap: wrap;
  margin-top: 24px;
}

.donate-btn {
  display: flex;
  flex-direction: column;
  align-items: center;
  background: rgba(0,212,255,0.10);
  border-radius: 20px;
  padding: 28px 32px 20px 32px;
  text-decoration: none;
  color: #fff;
  transition: 
    transform 0.22s cubic-bezier(.4,2,.6,1),
    box-shadow 0.22s cubic-bezier(.4,2,.6,1),
    background 0.22s;
  width: 200px;
  box-shadow: 0 6px 24px rgba(0,212,255,0.10), 0 1.5px 8px rgba(0,0,0,0.10);
  position: relative;
  overflow: hidden;
  border: 2px solid transparent;
}

.donate-btn:hover {
  background: linear-gradient(135deg, #00D4FF 0%, #0B0B52 100%);
  color: #fff;
  transform: translateY(-8px) scale(1.045);
  box-shadow: 0 16px 40px 0 rgba(0,212,255,0.18), 0 3px 16px rgba(0,0,0,0.18);
  border-color: #00D4FF;
}

.donate-icon {
  background: #fff;
  border-radius: 50%;
  padding: 16px;
  margin-bottom: 16px;
  box-shadow: 0 2px 12px rgba(0,212,255,0.10);
  transition: background 0.22s;
  display: flex;
  align-items: center;
  justify-content: center;
}

.donate-btn:hover .donate-icon {
  background: #00D4FF;
}

.donate-btn img {
  max-width: 56px;
  height: 56px;
  transition: transform 0.22s;
}

.donate-btn:hover img {
  transform: scale(1.12) rotate(-6deg);
}

.donate-btn span {
  font-weight: bold;
  font-size: 1.08rem;
  margin-top: 6px;
  letter-spacing: 0.2px;
}

@media (max-width: 700px) {
  .donate-buttons {
    gap: 18px;
  }
  .donate-btn {
    width: 90vw;
    max-width: 320px;
    padding: 18px 8vw 14px 8vw;
  }
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
      width: 44px;
      height: 44px;
      cursor: pointer;
      position: relative;
      z-index: 120;
      justify-content: center;
      align-items: center;
      background: rgba(0,212,255,0.10);
      border-radius: 50%;
      transition: background 0.2s;
      border: 2px solid transparent;
      box-shadow: 0 2px 8px rgba(0,212,255,0.10);
    }
    .menu-toggle:hover {
      background: rgba(0,212,255,0.18);
      border-color: var(--color-primary);
    }
    .menu-toggle span {
      display: block;
      position: absolute;
      height: 3.5px;
      width: 26px;
      background: var(--color-primary);
      border-radius: 3px;
      left: 9px;
      transition: all 0.35s cubic-bezier(.4,2,.6,1);
    }
    .menu-toggle span:nth-child(1) { top: 13px; }
    .menu-toggle span:nth-child(2) { top: 21px; }
    .menu-toggle span:nth-child(3) { top: 29px; }

    .menu-toggle.active span:nth-child(1) {
      top: 21px;
      transform: rotate(45deg);
    }
    .menu-toggle.active span:nth-child(2) {
      opacity: 0;
      transform: translateX(-10px);
    }
    .menu-toggle.active span:nth-child(3) {
      top: 21px;
      transform: rotate(-45deg);
    }

    @media (max-width: 768px) {
      .menu-toggle {
        display: flex;
      }
    }
@media (max-width: 768px) {
  .nav-links {
    display: none;
    flex-direction: column;
    background: rgba(0, 0, 0, 0.92);
    position: fixed;
    top: 60px;
    left: 0;
    right: 0;
    width: 100vw;
    padding: 28px 0 32px 0;
    z-index: 999;
    box-shadow: 0 8px 32px rgba(0,0,0,0.18);
    border-radius: 0 0 18px 18px;
    align-items: center;
  }


      .nav-links.show {
        display: flex;
      }

      .menu-toggle {
        display: block;
      }
    }
    /* Versión móvil (pantallas menores a 768px) */
@media (max-width: 768px) {
  /* Reordenar elementos para columna */
  main {
    flex-direction: column;
    align-items: center;
    padding: 10px;
  }

  /* Ajustar tamaño de las tarjetas */
  .card {
    width: 100%;
    max-width: 350px;
    min-height: auto;
    padding: 15px;
    font-size: 14px;
  }

  /* Redimensionar gráficas */
  .chart-container {
    width: 100% !important;
    height: auto !important;
  }

  /* Botones y filtros ocupando todo el ancho */
  .filters {
    display: flex;
    flex-wrap: wrap;
    gap: 5px;
    width: 100%;
    justify-content: center;
  }
  .filters button {
    flex: 1 1 calc(50% - 10px);
    min-width: 120px;
  }

  /* Menú lateral se convierte en barra superior */
  nav.sidebar {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: auto;
    display: flex;
    justify-content: space-around;
    padding: 10px;
    z-index: 999;
  }

  nav.sidebar button {
    font-size: 14px;
    padding: 8px 12px;
  }

  /* Ajustar márgenes del contenido principal */
  main {
    margin-top: 60px;
  }

  /* Drag-and-drop optimizado para móvil */
  .grid-stack-item-content {
    padding: 10px;
    font-size: 14px;
  }
}
.features .grid {
  gap: 40px;
  margin-top: 30px;
}

.features .card {
  background: rgba(11, 11, 82, 0.85);
  border-radius: 18px;
  box-shadow: 0 8px 32px 0 rgba(0,212,255,0.15), 0 1.5px 8px rgba(0,0,0,0.12);
  padding: 32px 24px;
  text-align: center;
  transition: transform 0.25s, box-shadow 0.25s, border 0.25s;
  border: 2px solid transparent;
  position: relative;
  overflow: hidden;
}

.features .card:hover {
  transform: translateY(-10px) scale(1.03);
  box-shadow: 0 16px 40px 0 rgba(0,212,255,0.22), 0 3px 16px rgba(0,0,0,0.18);
  border-color: var(--color-primary);
  background: linear-gradient(135deg, #0B0B52 80%, #00D4FF 120%);
}

.features .card i {
  font-size: 2.8rem;
  color: var(--color-primary);
  margin-bottom: 18px;
  display: inline-block;
  background: rgba(0,212,255,0.08);
  border-radius: 50%;
  padding: 18px;
  box-shadow: 0 2px 8px rgba(0,212,255,0.10);
  transition: background 0.25s, color 0.25s;
}

.features .card:hover i {
  background: #fff;         /* Fondo blanco */
  color: #0B0B52;           /* Icono oscuro */
  box-shadow: 0 4px 16px rgba(0,212,255,0.18);
  border: 2px solid var(--color-primary);
}

.features .card h3 {
  font-size: 1.35rem;
  margin: 18px 0 10px 0;
  color: #fff;
  font-weight: 700;
  letter-spacing: 0.5px;
}

.features .card p {
  color: #e0f7fa;
  font-size: 1.05rem;
  line-height: 1.6;
  margin: 0;
}

@media (max-width: 900px) {
  .features .grid {
    gap: 20px;
  }
  .features .card {
    padding: 24px 12px;
  }
  .features .card h3 {
    font-size: 1.1rem;
  }
  .features .card p {
    font-size: 0.98rem;
  }
}

.screenshots-grid {
  display: flex;
  gap: 48px;
  justify-content: center;
  align-items: stretch;
  flex-wrap: wrap;
  margin-top: 40px;
  max-width: 1200px;
  margin-left: auto;
  margin-right: auto;
}

.screenshot-card {
  background: rgba(11, 20, 60, 0.70);
  border-radius: 28px;
  box-shadow: 0 12px 40px 0 rgba(0,212,255,0.18), 0 4px 16px rgba(0,0,0,0.13);
  padding: 32px 32px 24px 32px;
  max-width: 520px;
  min-width: 340px;
  width: 40vw;
  text-align: center;
  transition: 
    transform 0.28s cubic-bezier(.4,2,.6,1),
    box-shadow 0.28s cubic-bezier(.4,2,.6,1),
    background 0.28s;
  position: relative;
  overflow: hidden;
  backdrop-filter: blur(10px);
  border: 2px solid rgba(0,212,255,0.18);
  display: flex;
  flex-direction: column;
  justify-content: center;
}

.screenshot-card::before {
  content: "";
  position: absolute;
  top: -50px; left: -50px;
  width: 160px; height: 160px;
  background: radial-gradient(circle, rgba(0,212,255,0.13) 0%, transparent 80%);
  z-index: 0;
}

.screenshot-card:hover {
  transform: translateY(-16px) scale(1.045);
  box-shadow: 0 24px 64px 0 rgba(0,212,255,0.22), 0 8px 32px rgba(0,0,0,0.18);
  background: linear-gradient(135deg, rgba(0,212,255,0.13) 0%, rgba(11,20,60,0.92) 100%);
}

.screenshot-card img {
  width: 100%;
  max-height: 340px;
  object-fit: cover;
  border-radius: 18px;
  box-shadow: 0 8px 32px rgba(0,212,255,0.13), 0 2px 8px rgba(0,0,0,0.13);
  margin-bottom: 18px;
  transition: box-shadow 0.28s;
  z-index: 1;
  position: relative;
}

.screenshot-card:hover img {
  box-shadow: 0 16px 48px rgba(0,212,255,0.22), 0 12px 40px rgba(0,0,0,0.18);
}

.screenshot-card figure {
  margin: 0;
  position: relative;
  z-index: 1;
}

.screenshot-card figcaption {
  color: #00D4FF;
  font-weight: 700;
  font-size: 1.25rem;
  margin-top: 14px;
  letter-spacing: 0.3px;
  text-shadow: 0 1px 6px rgba(0,0,0,0.18);
}

@media (max-width: 1200px) {
  .screenshots-grid {
    gap: 32px;
  }
  .screenshot-card {
    max-width: 95vw;
    width: 90vw;
    padding: 24px 10px 18px 10px;
  }
}

@media (max-width: 900px) {
  .screenshots-grid {
    flex-direction: column;
    gap: 28px;
    align-items: center;
  }
  .screenshot-card {
    max-width: 98vw;
    width: 98vw;
    padding: 18px 4vw 14px 4vw;
  }
}

@media (max-width: 600px) {
  .screenshot-card {
    padding: 10px 2vw 8px 2vw;
    border-radius: 12px;
  }
  .screenshot-card img {
    border-radius: 7px;
    max-height: 180px;
  }
  .screenshot-card figcaption {
    font-size: 1rem;
  }
}

.testimonials-grid {
  display: flex;
  gap: 40px;
  justify-content: center;
  align-items: stretch;
  flex-wrap: wrap;
  margin-top: 30px;
}

.testimonial-card {
  background: rgba(11, 20, 60, 0.80);
  border-radius: 22px;
  box-shadow: 0 8px 32px 0 rgba(0,212,255,0.13), 0 2px 12px rgba(0,0,0,0.10);
  padding: 32px 28px 24px 28px;
  max-width: 370px;
  width: 100%;
  text-align: center;
  position: relative;
  overflow: hidden;
  backdrop-filter: blur(8px);
  border: 1.5px solid rgba(0,212,255,0.13);
  display: flex;
  flex-direction: column;
  align-items: center;
  transition: transform 0.25s, box-shadow 0.25s;
}

.testimonial-card:hover {
  transform: translateY(-10px) scale(1.03);
  box-shadow: 0 16px 48px 0 rgba(0,212,255,0.18), 0 6px 24px rgba(0,0,0,0.18);
}

.testimonial-avatar {
  width: 72px;
  height: 72px;
  border-radius: 50%;
  overflow: hidden;
  margin-bottom: 18px;
  border: 3px solid #00D4FF;
  box-shadow: 0 2px 8px rgba(0,212,255,0.10);
  background: #fff;
}

.testimonial-avatar img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.testimonial-card blockquote {
  color: #e0f7fa;
  font-size: 1.13rem;
  font-style: italic;
  margin: 0 0 18px 0;
  line-height: 1.6;
  position: relative;
}

.testimonial-card blockquote:before {
  content: "“";
  color: #00D4FF;
  font-size: 2.5rem;
  position: absolute;
  left: -18px;
  top: -18px;
  opacity: 0.4;
}

.testimonial-user .name {
  color: #fff;
  font-weight: 700;
  font-size: 1.08rem;
  display: block;
}

.testimonial-user .role {
  color: #00D4FF;
  font-size: 0.98rem;
  opacity: 0.85;
}

@media (max-width: 900px) {
  .testimonials-grid {
    flex-direction: column;
    gap: 28px;
    align-items: center;
  }
  .testimonial-card {
    max-width: 95vw;
  }
}

.carousel-container {
  position: relative;
  max-width: 1200px;
  margin: 0 auto 40px auto;
  overflow: hidden;
  padding: 0 60px;
}

.carousel-track {
  display: flex;
  transition: transform 0.6s cubic-bezier(.4,2,.6,1);
  will-change: transform;
  gap: 48px; /* Más separación entre tarjetas */
  padding: 20px 0; /* Espacio arriba y abajo */
}

@media (min-width: 1100px) {
  .testimonial-card {
    flex: 0 0 340px;
    max-width: 340px;
  }
  .carousel-container {
    max-width: 1200px;
    padding: 0 80px;
  }
}
@media (min-width: 901px) and (max-width: 1099px) {
  .testimonial-card {
    flex: 0 0 30vw;
    max-width: 350px;
  }
  .carousel-container {
    padding: 0 40px;
  }
}
@media (max-width: 900px) and (min-width: 601px) {
  .carousel-track { gap: 24px; }
  .testimonial-card {
    flex: 0 0 44vw;
    max-width: 95vw;
  }
  .carousel-container {
    padding: 0 16px;
  }
}

@media (max-width: 600px) {
  .carousel-track { gap: 12px; }
  .testimonial-card {
    flex: 0 0 96vw;
    max-width: 98vw;
  }
  .carousel-container {
    padding: 0 2vw;
  }
}

.carousel-btn {
  position: absolute;
  top: 50%;
  transform: translateY(-50%);
  background: rgba(0,212,255,0.18);
  border: none;
  color: #fff;
  font-size: 2.2rem;
  border-radius: 50%;
  width: 48px;
  height: 48px;
  cursor: pointer;
  z-index: 2;
  transition: background 0.2s, color 0.2s;
  box-shadow: 0 2px 8px rgba(0,212,255,0.10);
  display: flex;
  align-items: center;
  justify-content: center;
}

.carousel-btn:hover {
  background: #00D4FF;
  color: #0B0B52;
}

.carousel-btn.prev { left: 0; }
.carousel-btn.next { right: 0; }

@media (max-width: 900px) {
  .carousel-container { padding: 0 10px; }
  .carousel-btn { width: 38px; height: 38px; font-size: 1.5rem; }
  .carousel-track { gap: 18px; }
}

.faq-accordion {
  max-width: 700px;
  margin: 32px auto 0 auto;
  display: flex;
  flex-direction: column;
  gap: 18px;
}

.faq-item .faq-answer {
  max-height: 0;
  overflow: hidden;
  transition: max-height 0.35s cubic-bezier(.4,2,.6,1), padding 0.2s;
  padding: 0 24px 0 64px;
}

.faq-item.open .faq-answer {
  max-height: 200px;
  padding: 12px 24px 18px 64px;
}

.faq-question {
  width: 100%;
  background: none;
  border: none;
  color: #fff;
  font-size: 1.13rem;
  font-weight: 600;
  text-align: left;
  padding: 22px 24px;
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 16px;
  outline: none;
  transition: background 0.18s;
  position: relative;
}

.faq-question i {
  color: var(--color-primary);
  font-size: 1.5rem;
}

.faq-toggle {
  margin-left: auto;
  transition: transform 0.3s;
  display: flex;
  align-items: center;
}

.faq-question[aria-expanded="true"] .faq-toggle {
  transform: rotate(180deg);
}

.faq-answer {
  background: rgba(0,212,255,0.07);
  color: #e0f7fa;
  font-size: 1.05rem;
  padding: 0 24px 18px 64px;
  line-height: 1.6;
  max-height: 0;
  overflow: hidden;
  transition: max-height 0.35s cubic-bezier(.4,2,.6,1), padding 0.2s;
}

.faq-question[aria-expanded="true"] + .faq-answer {
  max-height: 200px;
  padding: 12px 24px 18px 64px;
}

@media (max-width: 700px) {
  .faq-accordion { max-width: 98vw; }
  .faq-question { font-size: 1rem; padding: 18px 12px; }
  .faq-answer { padding-left: 40px; }
}
  </style>
</head>
<body>
  <div id="particles-js"></div>
  <nav class="navbar" data-aos="fade-down">
  <div class="nav-container">
    <a href="#inicio" class="nav-logo">GastoSimple</a>
    <!-- Botón hamburguesa -->
    <div class="menu-toggle" id="menu-toggle" aria-label="Abrir menú" aria-expanded="false">
      <span></span>
      <span></span>
      <span></span>
    </div>
    <!-- Menú de enlaces -->
    <div class="nav-links" id="nav-links">
      <a href="#features">Características</a>
      <a href="#faq">Preguntas frecuentes</a>
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
        <div class="card">
          <i class='bx bx-wallet-alt' style="font-size:2.2rem;color:var(--color-primary);"></i>
          <h3>Registro Ágil de Movimientos</h3>
          <p>Agrega tus ingresos y gastos en segundos, con categorías personalizadas y una interfaz intuitiva que facilita el control diario de tus finanzas.</p>
        </div>
        <div class="card">
          <i class='bx bx-line-chart' style="font-size:2.2rem;color:var(--color-primary);"></i>
          <h3>Gráficas y Reportes Interactivos</h3>
          <p>Visualiza tu evolución financiera con gráficos claros y reportes detallados. Analiza tendencias, identifica oportunidades de ahorro y toma mejores decisiones.</p>
        </div>
        <div class="card">
          <i class='bx bx-bullseye' style="font-size:2.2rem;color:var(--color-primary);"></i>
          <h3>Metas y Alertas Inteligentes</h3>
          <p>Define objetivos de ahorro y recibe alertas automáticas cuando te acerques a tus límites de gasto. Mantente motivado y enfocado en tus metas financieras.</p>
        </div>
      </div>
    </section>
    <section id="screenshots" class="screenshots" data-aos="fade-up">
      <h2>Antes y Después de Usar GastoSimple</h2>
      <p style="text-align:center; color:#e0f7fa; max-width:600px; margin:0 auto 30px;">
        Descubre cómo GastoSimple transforma tu control financiero. Visualiza la diferencia entre la desorganización y la claridad financiera que puedes lograr.
      </p>
      <div class="screenshots-grid">
        <div class="screenshot-card">
          <figure>
            <img src="img/reportes/descontrol.jpg" alt="Sin GastoSimple">
            <figcaption>Antes: Sin control financiero</figcaption>
          </figure>
        </div>
        <div class="screenshot-card">
          <figure>
            <img src="img/reportes/control.jpg" alt="Con GastoSimple">
            <figcaption>Después: Finanzas organizadas con GastoSimple</figcaption>
          </figure>
        </div>
      </div>
    </section>
  </section>
    <section id="testimonials" class="testimonials" data-aos="fade-up">
      <h2>Lo que Dicen Nuestros Usuarios</h2>
      <div class="carousel-container">
        <button class="carousel-btn prev" aria-label="Anterior">&#10094;</button>
        <div class="carousel-track">
          <!-- 20 testimonios, puedes cambiar imágenes y textos -->
          <div class="testimonial-card">
            <div class="testimonial-avatar"><img src="img/avatars/laura.jpg" alt="Laura"></div>
            <blockquote>“GastoSimple me ayudó a ahorrar sin darme cuenta.”</blockquote>
            <div class="testimonial-user"><span class="name">Laura</span><span class="role">Emprendedora</span></div>
          </div>
          <div class="testimonial-card">
            <div class="testimonial-avatar"><img src="img/avatars/carlos.jpg" alt="Carlos"></div>
            <blockquote>“Una app sencilla y eficaz para controlar mis gastos.”</blockquote>
            <div class="testimonial-user"><span class="name">Carlos</span><span class="role">Diseñador Freelance</span></div>
          </div>
          <div class="testimonial-card">
            <div class="testimonial-avatar"><img src="img/avatars/ana.jpg" alt="Ana"></div>
            <blockquote>“Ahora sé exactamente a dónde va mi dinero cada mes.”</blockquote>
            <div class="testimonial-user"><span class="name">Ana</span><span class="role">Estudiante</span></div>
          </div>
          <div class="testimonial-card">
            <div class="testimonial-avatar"><img src="img/avatars/jose.jpg" alt="José"></div>
            <blockquote>“La interfaz es intuitiva y muy fácil de usar.”</blockquote>
            <div class="testimonial-user"><span class="name">José</span><span class="role">Contador</span></div>
          </div>
          <div class="testimonial-card">
            <div class="testimonial-avatar"><img src="img/avatars/maria.jpg" alt="María"></div>
            <blockquote>“Me ayudó a cumplir mis metas de ahorro.”</blockquote>
            <div class="testimonial-user"><span class="name">María</span><span class="role">Ingeniera</span></div>
          </div>
          <div class="testimonial-card">
            <div class="testimonial-avatar"><img src="img/avatars/pedro.jpg" alt="Pedro"></div>
            <blockquote>“¡Por fin tengo control sobre mis gastos!”</blockquote>
            <div class="testimonial-user"><span class="name">Pedro</span><span class="role">Padre de familia</span></div>
          </div>
          <div class="testimonial-card">
            <div class="testimonial-avatar"><img src="img/avatars/sofia.jpg" alt="Sofía"></div>
            <blockquote>“Las alertas me ayudan a no pasarme del presupuesto.”</blockquote>
            <div class="testimonial-user"><span class="name">Sofía</span><span class="role">Administradora</span></div>
          </div>
          <div class="testimonial-card">
            <div class="testimonial-avatar"><img src="img/avatars/juan.jpg" alt="Juan"></div>
            <blockquote>“Recomiendo GastoSimple a todos mis amigos.”</blockquote>
            <div class="testimonial-user"><span class="name">Juan</span><span class="role">Profesor</span></div>
          </div>
          <div class="testimonial-card">
            <div class="testimonial-avatar"><img src="img/avatars/lucia.jpg" alt="Lucía"></div>
            <blockquote>“La mejor app para finanzas personales.”</blockquote>
            <div class="testimonial-user"><span class="name">Lucía</span><span class="role">Psicóloga</span></div>
          </div>
          <div class="testimonial-card">
            <div class="testimonial-avatar"><img src="img/avatars/david.jpg" alt="David"></div>
            <blockquote>“Me encanta la visualización de los reportes.”</blockquote>
            <div class="testimonial-user"><span class="name">David</span><span class="role">Desarrollador</span></div>
          </div>
          <div class="testimonial-card">
            <div class="testimonial-avatar"><img src="img/avatars/paula.jpg" alt="Paula"></div>
            <blockquote>“Ahora puedo ahorrar para mis viajes.”</blockquote>
            <div class="testimonial-user"><span class="name">Paula</span><span class="role">Viajera</span></div>
          </div>
          <div class="testimonial-card">
            <div class="testimonial-avatar"><img src="img/avatars/andres.jpg" alt="Andrés"></div>
            <blockquote>“La recomiendo para familias y estudiantes.”</blockquote>
            <div class="testimonial-user"><span class="name">Andrés</span><span class="role">Estudiante</span></div>
          </div>
          <div class="testimonial-card">
            <div class="testimonial-avatar"><img src="img/avatars/valeria.jpg" alt="Valeria"></div>
            <blockquote>“Muy útil para organizar mis gastos mensuales.”</blockquote>
            <div class="testimonial-user"><span class="name">Valeria</span><span class="role">Nutricionista</span></div>
          </div>
          <div class="testimonial-card">
            <div class="testimonial-avatar"><img src="img/avatars/ricardo.jpg" alt="Ricardo"></div>
            <blockquote>“La mejor inversión de tiempo para mis finanzas.”</blockquote>
            <div class="testimonial-user"><span class="name">Ricardo</span><span class="role">Empresario</span></div>
          </div>
          <div class="testimonial-card">
            <div class="testimonial-avatar"><img src="img/avatars/monica.jpg" alt="Mónica"></div>
            <blockquote>“Me siento más tranquila con mis cuentas.”</blockquote>
            <div class="testimonial-user"><span class="name">Mónica</span><span class="role">Madre</span></div>
          </div>
          <div class="testimonial-card">
            <div class="testimonial-avatar"><img src="img/avatars/fernando.jpg" alt="Fernando"></div>
            <blockquote>“El soporte es excelente y rápido.”</blockquote>
            <div class="testimonial-user"><span class="name">Fernando</span><span class="role">Abogado</span></div>
          </div>
          <div class="testimonial-card">
            <div class="testimonial-avatar"><img src="img/avatars/alejandra.jpg" alt="Alejandra"></div>
            <blockquote>“Me ayudó a salir de deudas.”</blockquote>
            <div class="testimonial-user"><span class="name">Alejandra</span><span class="role">Contadora</span></div>
          </div>
          <div class="testimonial-card">
            <div class="testimonial-avatar"><img src="img/avatars/julian.jpg" alt="Julián"></div>
            <blockquote>“La app es rápida y segura.”</blockquote>
            <div class="testimonial-user"><span class="name">Julián</span><span class="role">Ingeniero</span></div>
          </div>
          <div class="testimonial-card">
            <div class="testimonial-avatar"><img src="img/avatars/karla.jpg" alt="Karla"></div>
            <blockquote>“Me motiva a ahorrar cada mes.”</blockquote>
            <div class="testimonial-user"><span class="name">Karla</span><span class="role">Estilista</span></div>
          </div>
          <div class="testimonial-card">
            <div class="testimonial-avatar"><img src="img/avatars/roberto.jpg" alt="Roberto"></div>
            <blockquote>“¡No sabía que ahorrar podía ser tan fácil!”</blockquote>
            <div class="testimonial-user"><span class="name">Roberto</span><span class="role">Chef</span></div>
          </div>
        </div>
        <button class="carousel-btn next" aria-label="Siguiente">&#10095;</button>
      </div>
    </section>
    <section id="faq" class="faq" data-aos="fade-up">
      <h2>Preguntas Frecuentes</h2>
      <div class="faq-accordion">
        <div class="faq-item">
          <button class="faq-question" aria-expanded="false">
            <i class='bx bx-help-circle'></i>
            ¿GastoSimple es gratuito?
            <span class="faq-toggle"><i class='bx bx-chevron-down'></i></span>
          </button>
          <div class="faq-answer">
            Sí, sin costos ocultos ni anuncios molestos.
          </div>
        </div>
        <div class="faq-item">
          <button class="faq-question" aria-expanded="false">
            <i class='bx bx-lock-alt'></i>
            ¿Dónde se guardan mis datos?
            <span class="faq-toggle"><i class='bx bx-chevron-down'></i></span>
          </button>
          <div class="faq-answer">
            En servidores seguros. Tú tienes el control.
          </div>
        </div>
        <div class="faq-item">
          <button class="faq-question" aria-expanded="false">
            <i class='bx bx-shield-quarter'></i>
            ¿Mis datos están protegidos?
            <span class="faq-toggle"><i class='bx bx-chevron-down'></i></span>
          </button>
          <div class="faq-answer">
            Sí, usamos cifrado y buenas prácticas de seguridad para proteger tu información.
          </div>
        </div>
        <div class="faq-item">
          <button class="faq-question" aria-expanded="false">
            <i class='bx bx-mobile-alt'></i>
            ¿Puedo usar GastoSimple en mi móvil?
            <span class="faq-toggle"><i class='bx bx-chevron-down'></i></span>
          </button>
          <div class="faq-answer">
            Sí, la plataforma es 100% responsive y funciona en cualquier dispositivo.
          </div>
        </div>
      </div>
    </section>
      <section id="donate" class="donate" data-aos="fade-up">
        <h2>¿Te gusta Gasto Simple?</h2>
        <p class="donate-desc">Apoya el proyecto y ayúdanos a seguir creciendo 🚀</p>
        <div class="donate-buttons">
          <a href="#" target="_blank" class="donate-btn">
            <div class="donate-icon">
              <img src="img/taza-de-cafe.png" alt="Buy me a coffee">
            </div>
            <span>Invítame un café</span>
          </a>
          <a href="#" target="_blank" class="donate-btn">
            <div class="donate-icon">
              <img src="img/patreon.png" alt="Patreon">
            </div>
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
    document.addEventListener('DOMContentLoaded', function() {
      const track = document.querySelector('.carousel-track');
      const cards = Array.from(track.children);
      const prevBtn = document.querySelector('.carousel-btn.prev');
      const nextBtn = document.querySelector('.carousel-btn.next');
      let currentIndex = 0;
      let cardsToShow = 3;

      function updateCardsToShow() {
        if(window.innerWidth < 600) cardsToShow = 1;
        else if(window.innerWidth < 900) cardsToShow = 2;
        else cardsToShow = 3;
      }

      function updateCarousel() {
        const card = cards[0];
        const cardStyle = window.getComputedStyle(card);
        const cardWidth = card.offsetWidth + parseInt(cardStyle.marginRight || 0) + parseInt(cardStyle.marginLeft || 0) + (window.innerWidth < 900 ? 18 : 40);
        const maxIndex = cards.length - cardsToShow;
        if(currentIndex < 0) currentIndex = 0;
        if(currentIndex > maxIndex) currentIndex = maxIndex;
        track.style.transform = `translateX(-${currentIndex * cardWidth}px)`;
      }

      prevBtn.addEventListener('click', () => {
        currentIndex--;
        updateCarousel();
      });

      nextBtn.addEventListener('click', () => {
        currentIndex++;
        updateCarousel();
      });

      window.addEventListener('resize', () => {
        updateCardsToShow();
        updateCarousel();
      });

      // Inicializar
      updateCardsToShow();
      updateCarousel();
    });
  </script>
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
        toggle.classList.toggle('active');
        navLinks.classList.toggle('show');
        toggle.setAttribute('aria-expanded', toggle.classList.contains('active') ? 'true' : 'false');
      });
  </script>
  <script>
  document.querySelectorAll('.faq-question').forEach(btn => {
    btn.addEventListener('click', function() {
      document.querySelectorAll('.faq-item').forEach(item => item.classList.remove('open'));
      const item = this.closest('.faq-item');
      if (this.getAttribute('aria-expanded') === 'false') {
        this.setAttribute('aria-expanded', 'true');
        item.classList.add('open');
      } else {
        this.setAttribute('aria-expanded', 'false');
        item.classList.remove('open');
      }
      // Cierra los demás
      document.querySelectorAll('.faq-question').forEach(b => {
        if (b !== this) b.setAttribute('aria-expanded', 'false');
      });
    });
  });
  </script>
</body>
</html>
<?php include 'includes/footer.php'; ?>