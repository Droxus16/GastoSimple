<?php include 'includes/header.php'; ?>
<?php require_once 'includes/db.php'; ?>
<?php session_start(); ?>
<!-- Bootstrap 5 + Bootstrap Icons -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
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
  background: rgba(255, 255, 255, 0.1);
  border: 1px solid rgba(255,255,255,0.2);
  border-radius: 15px;
  backdrop-filter: blur(15px);
  box-shadow: 0 8px 32px rgba(0,0,0,0.2);
  z-index: 1;
}

.glass-card h2 { color: #fff; text-align: center; }
.glass-card label { color: #fff; }
.glass-card a { color: #00D4FF; }
.glass-card a:hover { text-decoration: underline; }

.mensaje {
  background-color: #222;
  color: #ff9999;
  padding: 10px;
  border-radius: 8px;
  text-align: center;
  margin-bottom: 15px;
}
</style>
<?php
define('MAX_INTENTOS', 5);
define('TIEMPO_BLOQUEO', 15 * 60);
$mensaje = "";
// Proceso de autenticación
if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $correo = trim($_POST["correo"]);
  $claveIngresada = $_POST["contrasena"];
  $recordarSesion = isset($_POST["recordar_sesion"]);
  if (!empty($correo) && !empty($claveIngresada)) {
    try {
      $db = DB::conectar();
      $stmt = $db->prepare("SELECT id, nombre, clave, rol, intentos_fallidos, bloqueado_hasta FROM usuarios WHERE correo = ?");
      $stmt->execute([$correo]);
      if ($stmt->rowCount() == 1) {
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
        // Solo bloquear si es estandar
        if ($usuario['rol'] === 'estandar' && $usuario['bloqueado_hasta'] && strtotime($usuario['bloqueado_hasta']) > time()) {
          $mensaje = "Cuenta bloqueada hasta: " . $usuario['bloqueado_hasta'];
        } else {
          if (password_verify($claveIngresada, $usuario['clave'])) {
            $db->prepare("UPDATE usuarios SET intentos_fallidos = 0, bloqueado_hasta = NULL WHERE id = ?")
               ->execute([$usuario['id']]);
            session_regenerate_id(true);
            $_SESSION["usuario_id"] = $usuario["id"];
            $_SESSION["nombre"] = $usuario["nombre"];
            $_SESSION["rol"] = $usuario["rol"];
            $_SESSION["ultimo_acceso"] = time();
            if ($recordarSesion) {
              $token = bin2hex(random_bytes(16));
              setcookie("rememberme", $token, time() + (86400 * 30), "/", "", true, true);
            }
            // Redirección por rol
            if ($usuario['rol'] === 'admin') {
              header("Location: admin_dashboard.php");
            } else {
              header("Location: dashboard.php");
            }
            exit;
          } else {
            if ($usuario['rol'] === 'estandar') {
              $intentos = $usuario['intentos_fallidos'] + 1;
              if ($intentos >= MAX_INTENTOS) {
                $bloqueado_hasta = date("Y-m-d H:i:s", time() + TIEMPO_BLOQUEO);
                $db->prepare("UPDATE usuarios SET intentos_fallidos = ?, bloqueado_hasta = ? WHERE id = ?")
                   ->execute([$intentos, $bloqueado_hasta, $usuario['id']]);
                $mensaje = "Demasiados intentos. Bloqueado por 15 minutos.";
              } else {
                $db->prepare("UPDATE usuarios SET intentos_fallidos = ? WHERE id = ?")
                   ->execute([$intentos, $usuario['id']]);
                $mensaje = "Contraseña incorrecta. Intentos: $intentos.";
              }
            } else {
              // Si es admin, solo mostrar mensaje de error
              $mensaje = "Contraseña incorrecta.";
            }
          }
        }
      } else {
        $mensaje = "Usuario no encontrado.";
      }
    } catch (PDOException $e) {
      $mensaje = "Error: " . $e->getMessage();
    }
  } else {
    $mensaje = "Completa todos los campos.";
  }
}
?>
<div id="particles-js"></div>
<!--  Botón volver al inicio -->
<a href="index.php" class="btn btn-outline-light position-absolute top-0 end-0 m-4">
  <i class="bi bi-house-door-fill"></i> Inicio
</a>
<!--  Tarjeta centrada -->
<div class="container d-flex justify-content-center align-items-center" style="min-height: 100vh; position: relative; z-index: 1;">
  <div class="glass-card p-4 col-md-6 col-lg-4">
    <h2 class="mb-4">Iniciar Sesión</h2>
    <!-- Mensaje GET -->
    <?php if (isset($_GET['mensaje'])): ?>
      <div class="alert alert-info"><?= htmlspecialchars($_GET['mensaje']) ?></div>
    <?php endif; ?>
    <!-- Mensaje POST -->
    <?php if ($mensaje): ?>
      <div class="mensaje"><?= htmlspecialchars($mensaje) ?></div>
    <?php endif; ?>
    <!-- Formulario -->
    <form method="POST" action="login.php" novalidate>
      <div class="mb-3">
        <label for="correo" class="form-label">Correo:</label>
        <input type="email" class="form-control" name="correo" id="correo" required>
      </div>
      <div class="mb-3">
        <label for="contrasena" class="form-label">Contraseña:</label>
        <input type="password" class="form-control" name="contrasena" id="contrasena" required>
      </div>
      <div class="form-check mb-2">
        <input type="checkbox" class="form-check-input" id="togglePassword">
        <label class="form-check-label" for="togglePassword">Mostrar contraseña</label>
      </div>
      <div class="form-check mb-3">
        <input type="checkbox" class="form-check-input" name="recordar_sesion" id="recordar_sesion">
        <label class="form-check-label" for="recordar_sesion">Recordar sesión</label>
      </div>
      <button type="submit" class="btn btn-info w-100">Iniciar Sesión</button>

      <p class="mt-2 text-center">
        <a href="controllers/recuperar_password.php">¿Olvidaste tu contraseña?</a>
      </p>
      <p class="mt-3 text-center">¿No tienes cuenta? <a href="register.php">Regístrate</a></p>
    </form>
  </div>
</div>
<!-- Mostrar/ocultar contraseña -->
<script>
  document.getElementById("togglePassword").addEventListener("change", function() {
    const pass = document.getElementById("contrasena");
    pass.type = this.checked ? "text" : "password";
  });
</script>
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