<?php
  session_start();
  require_once 'includes/db.php';

  $mensaje = "";

  if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre = trim($_POST["nombre"]);
    $correo = trim($_POST["correo"]);
    $contrasena = $_POST["contrasena"];
    $pregunta = trim($_POST["pregunta_secreta"]);
    $respuesta = trim($_POST["respuesta_secreta"]);
    $rol = "estandar";

    if (!empty($nombre) && !empty($correo) && !empty($contrasena) && !empty($pregunta) && !empty($respuesta)) {
      if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        $mensaje = "Correo inválido.";
      } else {
        try {
          $db = db::conectar();
          $stmt = $db->prepare("SELECT id FROM usuarios WHERE correo = ?");
          $stmt->execute([$correo]);

          if ($stmt->rowCount() > 0) {
            $mensaje = "Este correo ya está registrado.";
          } else {
            $hash = password_hash($contrasena, PASSWORD_DEFAULT);
            $stmt = $db->prepare("INSERT INTO usuarios (nombre, correo, clave, rol, pregunta_secreta, respuesta_secreta) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$nombre, $correo, $hash, $rol, $pregunta, $respuesta]);

            $_SESSION['usuario_id'] = $db->lastInsertId();
            $_SESSION['nombre'] = $nombre;
            $_SESSION['rol'] = $rol;

            header("Location: dashboard.php");
            exit();
          }
        } catch (PDOException $e) {
          $mensaje = "Error: " . $e->getMessage();
        }
      }
    } else {
      $mensaje = "Completa todos los campos.";
    }
  }
?>
<?php include 'includes/header.php'; ?>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
body, html {
  height: 100%;
  margin: 0;
  background: linear-gradient(135deg, #0B0B52, #1D2B64, #0C1634, #0B0B52);
  background-size: 300% 300%;
  animation: backgroundAnim 25s ease-in-out infinite;
}
@keyframes backgroundAnim {
  0%, 100% { background-position: 0% 50%; }
  50% { background-position: 100% 50%; }
}
#particles-js {
  position: fixed;
  top: 0; left: 0;
  width: 100%; height: 100%;
  z-index: 0;
}
.glass-card {
  background: rgba(255, 255, 255, 0.13);
  border: 1.5px solid rgba(0,212,255,0.18);
  border-radius: 22px;
  backdrop-filter: blur(18px);
  box-shadow: 0 12px 40px rgba(0,212,255,0.13), 0 4px 16px rgba(0,0,0,0.13);
  z-index: 1;
  padding: 38px 32px 32px 32px;
  text-align: center;
  position: relative;
  transition: box-shadow 0.2s;
}
.glass-card:hover {
  box-shadow: 0 20px 60px rgba(0,212,255,0.18), 0 8px 32px rgba(0,0,0,0.18);
}
.glass-card h2 {
  color: #00D4FF;
  text-align: center;
  font-weight: 700;
  margin-bottom: 18px;
  letter-spacing: 1px;
}
.glass-card label {
  color: #e0f7fa;
  font-weight: 500;
}
.glass-card a {
  color: #00D4FF;
  transition: color 0.2s;
}
.glass-card a:hover {
  color: #fff;
  text-decoration: underline;
}
.glass-card .logo-register {
  width: 64px;
  height: 64px;
  object-fit: contain;
  margin-bottom: 12px;
  background: transparent;
  border-radius: 4px;
}
.form-control:focus {
  border-color: #00D4FF;
  box-shadow: 0 0 0 2px rgba(0,212,255,0.18);
  background: rgba(255,255,255,0.09);
  color: #fff;
}
.btn-info, .btn-info:focus {
  background: linear-gradient(90deg, #00D4FF 0%, #1D2B64 100%);
  border: none;
  color: #fff;
  font-weight: 600;
  font-size: 1.08rem;
  box-shadow: 0 2px 12px rgba(0,212,255,0.10);
  transition: background 0.18s, color 0.18s, transform 0.18s;
}
.btn-info:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}
.btn-info:hover:enabled {
  background: #fff;
  color: #00D4FF;
  transform: translateY(-2px) scale(1.04);
}
.mensaje {
  background-color: #222;
  color: #ff9999;
  padding: 10px;
  border-radius: 8px;
  text-align: center;
  margin-bottom: 15px;
}
.divider {
  border-bottom: 1.5px solid #00D4FF;
  opacity: 0.18;
  margin: 18px 0 18px 0;
}
@media (max-width: 600px) {
  .glass-card {
    padding: 18px 4vw 18px 4vw;
    max-width: 98vw;
  }
}
</style>
<div id="particles-js"></div>
<div class="container d-flex justify-content-center align-items-center" style="min-height: 100vh; position: relative; z-index: 1;">
  <div class="glass-card p-4 col-md-6 col-lg-4">
    <img src="img/logo 1.png" alt="Logo GastoSimple" class="logo-register">
    <h2 class="mb-4">Crear Cuenta</h2>
    <?php if ($mensaje): ?>
      <div class="mensaje"><?= htmlspecialchars($mensaje) ?></div>
    <?php endif; ?>
    <form method="POST" action="register.php" novalidate>
      <div class="mb-3 text-start">
        <label for="nombre" class="form-label">Nombre:</label>
        <input type="text" class="form-control" name="nombre" id="nombre" required>
      </div>
      <div class="mb-3 text-start">
        <label for="correo" class="form-label">Correo:</label>
        <input type="email" class="form-control" name="correo" id="correo" required>
      </div>
      <div class="mb-3 text-start">
        <label for="contrasena" class="form-label">Contraseña:</label>
        <input type="password" class="form-control" name="contrasena" id="contrasena" required>
      </div>
      <div class="mb-3 text-start">
        <label for="pregunta_secreta" class="form-label">Pregunta Secreta:</label>
        <input type="text" class="form-control" name="pregunta_secreta" id="pregunta_secreta" required>
      </div>
      <div class="mb-3 text-start">
        <label for="respuesta_secreta" class="form-label">Respuesta Secreta:</label>
        <input type="text" class="form-control" name="respuesta_secreta" id="respuesta_secreta" required>
      </div>
      <div class="form-check mb-2 text-start">
        <input type="checkbox" class="form-check-input" id="togglePassword">
        <label class="form-check-label" for="togglePassword">Mostrar contraseña</label>
      </div>
      <div class="form-check mb-3 text-start">
        <input type="checkbox" class="form-check-input" id="terminos">
        <label class="form-check-label" for="terminos">
          Acepto los <a href="terminos.php" target="_blank">términos y condiciones</a>
        </label>
      </div>
      <button type="submit" class="btn btn-info w-100" id="btnRegistro" disabled>Registrarse</button>
      <div class="divider"></div>
      <p class="mt-3 text-center text-white">¿Ya tienes cuenta? <a href="login.php" class="text-blue-400 hover:text-blue-600 font-semibold"><i class="bi bi-box-arrow-in-right"></i> Inicia sesión</a></p>
    </form>
  </div>
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
      "number": { "value": 80, "density": { "enable": true, "value_area": 800 } },
      "color": { "value": "#00D4FF" },
      "shape": { "type": "circle" },
      "opacity": { "value": 0.5, "anim": { "enable": true, "speed": 1 } },
      "size": { "value": 3, "random": true, "anim": { "enable": true, "speed": 40 } },
      "line_linked": { "enable": true, "distance": 150, "color": "#00D4FF", "opacity": 0.4, "width": 1 },
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