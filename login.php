<?php include 'includes/header.php'; ?>
<?php require_once 'includes/db.php'; ?>
<?php session_start(); ?>

<link rel="stylesheet" href="assets/css/estilos.css">

<style>
  /* Aseguramos que el fondo ocupe toda la pantalla */
body, html {
  content: '';
  position: absolute;
  top: 0; left: 0;
  width: 100%; height: 100%;
  background: linear-gradient(135deg, #0B0B52, #1D2B64, #0C1634, #0B0B52);
  background-size: 300% 300%;
  animation: backgroundAnim 25s ease-in-out infinite;
  z-index: -2;
  opacity: 0.95; /* toque de elegancia */
}

@keyframes backgroundAnim {
  0%, 100% { background-position: 0% 50%; }
  50% { background-position: 100% 50%; }
}

  /* Fondo de partículas */
  #particles-js {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    z-index: -1; /* Asegura que las partículas estén detrás del contenido */
  }

  /* Estilo para el contenedor de registro */
  .registro-wrapper {
    max-width: 400px;
    margin: 40px auto;
    padding: 30px;
    background: rgba(255, 255, 255, 0.05);
    border: 1px solid white;
    border-radius: 15px;
    backdrop-filter: blur(10px);
  }

  .registro-wrapper h2 {
    text-align: center;
    margin-bottom: 20px;
  }

  .registro-wrapper label {
    display: block;
    margin: 15px 0 5px;
    font-weight: bold;
  }

  .registro-wrapper input[type="text"],
  .registro-wrapper input[type="email"],
  .registro-wrapper input[type="password"] {
    width: 100%;
    padding: 10px;
    border: none;
    border-radius: 8px;
    margin-bottom: 10px;
  }

  .registro-wrapper input[type="checkbox"] {
    margin-right: 10px;
  }

  .registro-wrapper .checkbox-group {
    display: flex;
    align-items: center;
    margin-bottom: 15px;
  }

  .registro-wrapper button {
    width: 100%;
    padding: 10px;
    background: #00D4FF;
    color: #0C1634;
    font-weight: bold;
    border: none;
    border-radius: 10px;
    cursor: pointer;
    transition: background 0.3s;
  }

  .registro-wrapper button:hover {
    background: #00b8e6;
  }

  .registro-wrapper .mensaje {
    background-color: #222;
    padding: 10px;
    border-radius: 8px;
    text-align: center;
    margin-bottom: 15px;
    color: #ff9999;
  }

  .registro-wrapper a {
    color: #00D4FF;
    text-decoration: none;
  }

  .registro-wrapper a:hover {
    text-decoration: underline;
  }
</style>

<!-- Contenedor de partículas -->
<div id="particles-js"></div>

<div class="registro-wrapper">
  <h2>Iniciar Sesión</h2>

  <?php
  $mensaje = "";
  if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $correo = trim($_POST["correo"]);
    $claveIngresada = $_POST["contrasena"];

    if (!empty($correo) && !empty($claveIngresada)) {
      try {
        $db = db::conectar();
        $stmt = $db->prepare("SELECT id, nombre, clave, rol FROM usuarios WHERE correo = ?");
        $stmt->execute([$correo]);

        if ($stmt->rowCount() == 1) {
          $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
          if (password_verify($claveIngresada, $usuario['clave'])) {
            $_SESSION["usuario_id"] = $usuario["id"];
            $_SESSION["nombre"] = $usuario["nombre"];
            $_SESSION["rol"] = $usuario["rol"];
            header("Location: dashboard.php");
            exit;
          } else {
            $mensaje = "Contraseña incorrecta.";
          }
        } else {
          $mensaje = "Usuario no encontrado.";
        }
      } catch (PDOException $e) {
        $mensaje = "Error: " . $e->getMessage();
      }
    } else {
      $mensaje = "Por favor completa todos los campos.";
    }
  }
  ?>

  <?php if ($mensaje): ?>
    <p class="mensaje"><?= htmlspecialchars($mensaje) ?></p>
  <?php endif; ?>

  <form method="POST" action="login.php" novalidate>
    <label for="correo">Correo:</label>
    <input type="email" name="correo" id="correo" required>

    <label for="contrasena">Contraseña:</label>
    <input type="password" name="contrasena" id="contrasena" required>

    <div class="checkbox-group">
      <input type="checkbox" id="togglePassword">
      <label for="togglePassword" style="margin: 0;">Mostrar contraseña</label>
    </div>

    <button type="submit">Iniciar Sesión</button>
    <p style="text-align:center; margin-top:10px;">¿No tienes cuenta? <a href="register.php">Regístrate</a></p>
  </form>
</div>

<script>
  document.getElementById("togglePassword").addEventListener("change", function () {
    const pass = document.getElementById("contrasena");
    pass.type = this.checked ? "text" : "password";
  });
</script>

<script src="https://cdn.jsdelivr.net/particles.js/2.0.0/particles.min.js"></script>
<script>
  particlesJS("particles-js", {
    "particles": {
      "number": {
        "value": 80,
        "density": {
          "enable": true,
          "value_area": 800
        }
      },
      "color": {
        "value": "#ffffff"
      },
      "shape": {
        "type": "circle"
      },
      "opacity": {
        "value": 0.5,
        "random": false,
        "anim": {
          "enable": true,
          "speed": 1,
          "opacity_min": 0.1,
          "sync": false
        }
      },
      "size": {
        "value": 3,
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
        "onhover": {
          "enable": true,
          "mode": "repulse"
        },
        "onclick": {
          "enable": true,
          "mode": "push"
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

<?php include 'includes/footer.php'; ?>
