<?php
session_start();
require_once 'includes/db.php';
define('MAX_INTENTOS', 5);
define('TIEMPO_BLOQUEO', 15 * 60);
$mensaje = "";
if ($_SERVER["REQUEST_METHOD"] === "POST") {
  $correo = trim($_POST["correo"] ?? "");
  $claveIngresada = $_POST["contrasena"] ?? "";
  $recordarSesion = isset($_POST["recordar_sesion"]);
  if ($correo === "" || $claveIngresada === "") {
    $mensaje = "Completa todos los campos.";
  } else {
    try {
      $db = DB::conectar();
      $stmt = $db->prepare("SELECT id, nombre, clave, rol, intentos_fallidos, bloqueado_hasta FROM usuarios WHERE correo = ?");
      $stmt->execute([$correo]);
      if ($stmt->rowCount() === 1) {
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
        // Si usuario estándar y bloqueado aún
        if ($usuario['rol'] === 'estandar' && !empty($usuario['bloqueado_hasta']) && strtotime($usuario['bloqueado_hasta']) > time()) {
          $mensaje = "Cuenta bloqueada hasta: " . $usuario['bloqueado_hasta'];
        } else {
          // Verificar contraseña
          if (password_verify($claveIngresada, $usuario['clave'])) {
            // resetear intentos
            $db->prepare("UPDATE usuarios SET intentos_fallidos = 0, bloqueado_hasta = NULL WHERE id = ?")
               ->execute([$usuario['id']]);
            session_regenerate_id(true);
            $_SESSION["usuario_id"] = $usuario["id"];
            $_SESSION["nombre"] = $usuario["nombre"];
            $_SESSION["rol"] = $usuario["rol"];
            $_SESSION["ultimo_acceso"] = time();
            if ($recordarSesion) {
              $token = bin2hex(random_bytes(16));
              // marcar secure solo si HTTPS
              $secureFlag = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
              setcookie("rememberme", $token, time() + (86400 * 30), "/", "", $secureFlag, true);
              // idea: guardar token en BD asociado al usuario para validar luego (no implementado aquí)
            }
            // Redirigir según rol (antes de imprimir cualquier HTML)
            header("Location: " . ($usuario['rol'] === 'admin' ? "admin_dashboard.php" : "dashboard.php"));
            exit;
          } else {
            // contraseña incorrecta -> incrementar intentos si estandar
            if ($usuario['rol'] === 'estandar') {
              $intentos = (int)$usuario['intentos_fallidos'] + 1;
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
              $mensaje = "Contraseña incorrecta.";
            }
          }
        }
      } else {
        $mensaje = "Usuario no encontrado.";
      }
    } catch (PDOException $e) {
      // Registrar el error en el log del servidor y mostrar un mensaje genérico al usuario
      error_log("Login error: " . $e->getMessage());
      $mensaje = "Error del servidor. Intenta de nuevo más tarde.";
    }
  }
}
include 'includes/header.php';
?>
<!-- Bootstrap 5 + Bootstrap Icons -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
<style>
body, html {
  height: 100%;
  margin: 0;
  font-family: "Poppins", sans-serif;
  background: linear-gradient(135deg, #0B0B52, #1D2B64, #0C1634, #0B0B52);
  background-size: 300% 300%;
  animation: backgroundAnim 25s ease-in-out infinite;
  color: #fff;
}

/* Animación fondo */
@keyframes backgroundAnim {
  0%, 100% { background-position: 0% 50%; }
  50% { background-position: 100% 50%; }
}

/* Partículas */
#particles-js {
  position: fixed; inset: 0;
  z-index: 0;
}

/* Tarjeta de login */
.glass-card {
  background: rgba(255, 255, 255, 0.08);
  border: 1px solid rgba(0, 212, 255, 0.25);
  border-radius: 24px;
  backdrop-filter: blur(20px);
  padding: 42px 32px;
  text-align: center;
  position: relative;
  z-index: 1;
  box-shadow: 0 8px 40px rgba(0, 212, 255, 0.15);
  transform: scale(0.95);
  opacity: 0;
  animation: fadeInUp 1s ease forwards;
}

/* Animación de entrada */
@keyframes fadeInUp {
  to { transform: scale(1); opacity: 1; }
}

/* Logo animado */
.logo-login {
  width: 72px;
  margin-bottom: 16px;
  animation: pulse 3s infinite ease-in-out;
}
@keyframes pulse {
  0%, 100% { transform: scale(1); }
  50% { transform: scale(1.1); }
}

.glass-card h2 {
  color: #00D4FF;
  font-weight: 700;
  margin-bottom: 20px;
  letter-spacing: 1.5px;
}

/* Inputs con iconos */
.input-group-text {
  background: rgba(255, 255, 255, 0.1);
  border: none;
  color: #00D4FF;
}

.form-control {
  background: rgba(255,255,255,0.07);
  border: none;
  color: #fff;
  padding: 12px;
}
.form-control:focus {
  background: rgba(255,255,255,0.12);
  box-shadow: 0 0 12px #00D4FF;
  color: #fff;
}

/* Botón con gradiente animado */
.btn-info {
  background: linear-gradient(270deg, #00D4FF, #1D2B64, #00D4FF);
  background-size: 600% 600%;
  animation: gradientFlow 6s ease infinite;
  border: none;
  padding: 12px;
  border-radius: 12px;
  font-weight: 600;
  font-size: 1.1rem;
  color: #fff;
  transition: transform 0.2s;
}
.btn-info:hover {
  transform: scale(1.05);
  box-shadow: 0 0 16px #00D4FF;
}
@keyframes gradientFlow {
  0% { background-position: 0% 50%; }
  50% { background-position: 100% 50%; }
  100% { background-position: 0% 50%; }
}

/* Divider */
.divider {
  border-bottom: 1px solid rgba(0,212,255,0.3);
  margin: 20px 0;
}

.mensaje {
  background: rgba(255, 0, 0, 0.15);
  border-left: 4px solid #ff4d4d;
  padding: 10px;
  border-radius: 8px;
  color: #ffb3b3;
  margin-bottom: 15px;
}

/* Links */
.glass-card a {
  color: #00D4FF;
  transition: 0.3s;
}
.glass-card a:hover {
  color: #fff;
  text-shadow: 0 0 8px #00D4FF;
}
.form-control {
  background: rgba(255,255,255,0.07);
  border: none;
  color: #fff !important;   /* Texto blanco */
  padding: 12px;
}
.form-control::placeholder {
  color: rgba(255,255,255,0.7);  /* Placeholder gris claro */
}
.form-control {
  background: rgba(255,255,255,0.07);
  border: none;
  color: #fff !important;   /* Texto blanco para máxima legibilidad */
  padding: 12px;
}

.form-control::placeholder {
  color: rgba(255,255,255,0.6);  /* Placeholder gris claro */
}

.form-control:focus {
  outline: none;
  border: 1px solid #00D4FF;  /* Borde azul brillante al enfocar */
  box-shadow: 0 0 8px #00D4FF80; /* Glow sutil azul */
}
.btn-fab {
  width: 55px;
  height: 55px;
  border-radius: 50%;
  background: linear-gradient(135deg, #00D4FF, #007BFF);
  color: white;
  font-size: 22px;
  display: flex;
  align-items: center;
  justify-content: center;
  box-shadow: 0 6px 14px rgba(0, 212, 255, 0.4);
  transition: all 0.3s ease;
  text-decoration: none;
  border: none;
}

.btn-fab:hover {
  transform: scale(1.12);
  box-shadow: 0 8px 20px rgba(0, 212, 255, 0.7);
}

.btn-fab:active {
  transform: scale(0.95);
  box-shadow: 0 4px 10px rgba(0, 212, 255, 0.5);
}
/* Toggle moderno */
.form-check-input[type="checkbox"] {
  width: 42px; height: 22px;
  background: #555; border-radius: 12px; border: none;
  position: relative; cursor: pointer;
  transition: background 0.3s ease-in-out;
  appearance: none; /* quita estilo default */
}

.form-check-input[type="checkbox"]::before {
  content: "";
  position: absolute;
  width: 18px; height: 18px;
  background: #fff;
  border-radius: 50%;
  top: 2px; left: 2px;
  transition: transform 0.3s ease-in-out;
}

/* Color al estar activo */
.form-check-input:checked {
  background: #00D4FF;
}

/* Mueve el círculo a la derecha */
.form-check-input:checked::before {
  transform: translateX(20px);
}

</style>
<div id="particles-js"></div>
<a href="index.php" 
   class="btn-fab position-absolute top-0 end-0 m-4">
  <i class="bi bi-house-door-fill"></i>
</a>
<div class="container d-flex justify-content-center align-items-center" style="min-height: 100vh; position: relative; z-index: 1;">
  <div class="glass-card p-4 col-md-6 col-lg-4">
    <img src="img/logo 1.png" alt="Logo GastoSimple" class="logo-login">
    <h2 class="mb-4">Iniciar Sesión</h2>

    <?php if (isset($_GET['mensaje'])): ?>
      <div class="alert alert-info"><?= htmlspecialchars($_GET['mensaje']) ?></div>
    <?php endif; ?>
    <?php if (!empty($mensaje)): ?>
      <div class="mensaje"><?= htmlspecialchars($mensaje) ?></div>
    <?php endif; ?>

    <form method="POST" action="login.php" novalidate>
      <div class="mb-3 text-start input-group">
        <span class="input-group-text"><i class="bi bi-envelope"></i></span>
        <input type="email" class="form-control" name="correo" id="correo" placeholder="Correo electrónico" required>
      </div>
      <div class="mb-3 text-start input-group">
        <span class="input-group-text"><i class="bi bi-lock"></i></span>
        <input type="password" class="form-control" name="contrasena" id="contrasena" placeholder="Contraseña" required>
      </div>

      <!-- Mostrar contraseña -->
      <div class="form-check form-switch mb-2 text-start">
        <input type="checkbox" class="form-check-input" id="togglePassword">
        <label class="form-check-label text-light" for="togglePassword">Mostrar contraseña</label>
      </div>

      <!-- Recordar sesión -->
      <div class="form-check form-switch mb-3 text-start">
        <input type="checkbox" class="form-check-input" name="recordar_sesion" id="recordar_sesion">
        <label class="form-check-label text-light" for="recordar_sesion">Recordar sesión</label>
      </div>

      <button type="submit" class="btn btn-info w-100 mb-2">Iniciar Sesión</button>

      <div class="divider"></div>
      <p class="mt-2 text-center">
        <a href="controllers/recuperar_password.php"><i class="bi bi-key"></i> ¿Olvidaste tu contraseña?</a>
      </p>
      <p class="mt-3 text-center text-white">
        ¿No tienes cuenta?
        <a href="register.php" class="fw-bold"><i class="bi bi-person-plus"></i> Regístrate</a>
      </p>
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

<!-- Partículas -->
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