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
  <script src="https://cdn.jsdelivr.net/particles.js/2.0.0/particles.min.js"></script>
</head>
<body>
  <!-- Contenedor de partículas -->
  <div id="particles-js"></div>

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

  <!-- Configuración de partículas -->
  <script>
    particlesJS("particles-js", {
      "particles": {
        "number": {
          "value": 80,  // Número de partículas
          "density": {
            "enable": true,
            "value_area": 800  // Área de densidad
          }
        },
        "color": {
          "value": "#ffffff"  // Color de las partículas
        },
        "shape": {
          "type": "circle"  // Forma de las partículas
        },
        "opacity": {
          "value": 0.5,  // Opacidad de las partículas
          "random": false,
          "anim": {
            "enable": true,
            "speed": 1,
            "opacity_min": 0.1,
            "sync": false
          }
        },
        "size": {
          "value": 3,  // Tamaño de las partículas
          "random": true,
          "anim": {
            "enable": true,
            "speed": 40,
            "size_min": 0.1,
            "sync": false
          }
        },
        "line_linked": {
          "enable": true,
          "distance": 150,  // Distancia entre las partículas para la conexión
          "color": "#ffffff",  // Color de las líneas
          "opacity": 0.4,  // Opacidad de las líneas
          "width": 1
        },
        "move": {
          "enable": true,
          "speed": 3,  // Velocidad de las partículas
          "direction": "none",
          "random": false,
          "straight": false,
          "out_mode": "out",  // Cuando las partículas se mueven fuera de la pantalla
          "bounce": false
        }
      },
      "interactivity": {
        "detect_on": "window",
        "events": {
          "onhover": {
            "enable": true,
            "mode": "repulse"  // Efecto al pasar el mouse
          },
          "onclick": {
            "enable": true,
            "mode": "push"  // Efecto al hacer clic
          }
        },
        "modes": {
          "grab": {
            "distance": 400,
            "line_linked": {
              "opacity": 1
            }
          },
          "repulse": {
            "distance": 100,
            "duration": 0.4
          },
          "push": {
            "particles_nb": 4
          },
          "remove": {
            "particles_nb": 2
          }
        }
      },
      "retina_detect": true
    });
  </script>

</body>
</html>
