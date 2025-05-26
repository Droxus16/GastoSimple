<?php
session_start();
require_once 'includes/db.php';

$mensaje = "";

// Lógica del formulario antes de enviar cualquier salida HTML
if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $nombre = trim($_POST["nombre"]);
  $correo = trim($_POST["correo"]);
  $contrasena = $_POST["contrasena"];
  $rol = "estandar";

  if (!empty($nombre) && !empty($correo) && !empty($contrasena)) {
    if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
      $mensaje = "Por favor ingresa un correo válido.";
    } else {
      try {
        $db = db::conectar();

        $stmt = $db->prepare("SELECT id FROM usuarios WHERE correo = ?");
        $stmt->execute([$correo]);

        if ($stmt->rowCount() > 0) {
          $mensaje = "El correo ya está registrado.";
        } else {
          $hash = password_hash($contrasena, PASSWORD_DEFAULT);
          $stmt = $db->prepare("INSERT INTO usuarios (nombre, correo, clave, rol) VALUES (?, ?, ?, ?)");
          $stmt->execute([$nombre, $correo, $hash, $rol]);

          $idUsuario = $db->lastInsertId();
          $_SESSION['usuario_id'] = $idUsuario;
          $_SESSION['nombre'] = $nombre;
          $_SESSION['rol'] = $rol;

          // Redirige a dashboard antes de enviar cualquier salida HTML
          header("Location: dashboard.php");
          exit();
        }
      } catch (PDOException $e) {
        $mensaje = "Error: " . $e->getMessage();
      }
    }
  } else {
    $mensaje = "Por favor completa todos los campos.";
  }
}
?>

<?php include 'includes/header.php'; ?>
<link rel="stylesheet" href="assets/css/estilos.css">

<style>
  /* Aseguramos que el fondo ocupe toda la pantalla */
  body, html {
    height: 100%;
    margin: 0;
    padding: 0;
    overflow: hidden;
    font-family: 'Inter', sans-serif;
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
  <h2>Registro de Usuario</h2>

  <?php if ($mensaje): ?>
    <p class="mensaje"><?= htmlspecialchars($mensaje) ?></p>
  <?php endif; ?>

  <form id="registroForm" method="POST" action="register.php" novalidate>
    <label for="nombre">Nombre:</label>
    <input type="text" name="nombre" id="nombre" required>

    <label for="correo">Correo:</label>
    <input type="email" name="correo" id="correo" required>

    <label for="contrasena">Contraseña:</label>
    <input type="password" name="contrasena" id="contrasena" required>
    <label><input type="checkbox" id="togglePassword"> Mostrar contraseña</label>

    <label><input type="checkbox" id="terminos"> Acepto los <a href="terminos.php">términos y condiciones</a></label>

    <button type="submit" id="btnRegistro" disabled>Registrarse</button>

    <p style="text-align:center; margin-top:10px;">¿Ya tienes un usuario? <a href="login.php">Inicia sesión</a></p>
  </form>
</div>

<script>
  document.getElementById('togglePassword').addEventListener('change', function () {
    const passwordInput = document.getElementById('contrasena');
    passwordInput.type = this.checked ? 'text' : 'password';
  });

  const terminos = document.getElementById('terminos');
  const btnRegistro = document.getElementById('btnRegistro');
  terminos.addEventListener('change', function () {
    btnRegistro.disabled = !this.checked;
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
