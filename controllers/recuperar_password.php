<?php
session_start();
require_once '../includes/db.php';

$paso = 1;
$mensaje = "";
$pregunta = "";
$correo = isset($_POST['correo']) ? trim($_POST['correo']) : "";

try {
  $db = db::conectar();

  if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Paso 1: Mostrar pregunta secreta
    if (isset($_POST['correo']) && !isset($_POST['respuesta']) && !isset($_POST['nueva_contrasena'])) {
      $stmt = $db->prepare("SELECT pregunta_secreta FROM usuarios WHERE correo = ?");
      $stmt->execute([$correo]);

      if ($stmt->rowCount() == 1) {
        $pregunta = $stmt->fetchColumn();
        $paso = 2;
      } else {
        $mensaje = "No se encontró un usuario con ese correo.";
      }

    // Paso 2: Validar respuesta secreta
    } elseif (isset($_POST['respuesta']) && isset($_POST['correo']) && !isset($_POST['nueva_contrasena'])) {
      $respuesta = trim($_POST['respuesta']);
      $stmt = $db->prepare("SELECT respuesta_secreta FROM usuarios WHERE correo = ?");
      $stmt->execute([$correo]);
      $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

      if ($usuario && strcasecmp($respuesta, $usuario['respuesta_secreta']) === 0) {
        $paso = 3;
      } else {
        $mensaje = "Respuesta incorrecta.";
        $paso = 2;

        $stmt = $db->prepare("SELECT pregunta_secreta FROM usuarios WHERE correo = ?");
        $stmt->execute([$correo]);
        $pregunta = $stmt->fetchColumn();
      }

    // Paso 3: Guardar nueva contraseña
    } elseif (isset($_POST['nueva_contrasena']) && isset($_POST['correo'])) {
      $nueva_contrasena = trim($_POST['nueva_contrasena']);

      if (strlen($nueva_contrasena) < 6) {
        $mensaje = "La contraseña debe tener al menos 6 caracteres.";
        $paso = 3;
      } else {
        $hash = password_hash($nueva_contrasena, PASSWORD_DEFAULT);

        $stmt = $db->prepare("UPDATE usuarios SET clave = ?, updated_at = NOW() WHERE correo = ?");
        $stmt->execute([$hash, $correo]);

        if ($stmt->rowCount() > 0) {
          // Redirige seguro
          header("Location: ../login.php");
          exit();
        } else {
          $mensaje = "No se pudo actualizar la contraseña. Verifica tu correo.";
          $paso = 3;
        }
      }
    }
  }
} catch (PDOException $e) {
  $mensaje = "Error: " . $e->getMessage();
}
?>

<?php include '../includes/header.php'; ?>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
body, html {
  height: 100%;
  margin: 0;
  background: linear-gradient(135deg, #0B0B52, #1D2B64, #0C1634, #0B0B52);
  background-size: 300% 300%;
  animation: backgroundAnim 25s ease-in-out infinite;
  font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
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
  border-radius: 20px;
  backdrop-filter: blur(20px);
  box-shadow: 0 8px 32px rgba(0,0,0,0.25);
  z-index: 1;
  transition: all 0.3s ease-in-out;
}

.glass-card:hover {
  transform: scale(1.02);
}

.glass-card h2 {
  color: #fff;
  text-align: center;
  margin-bottom: 1.5rem;
}

.glass-card label {
  color: #eee;
}

.glass-card input {
  background: rgba(255, 255, 255, 0.15);
  border: none;
  color: #fff;
}

.glass-card input:focus {
  background: rgba(255, 255, 255, 0.2);
}
.glass-card input[disabled] {
  background: rgba(255, 255, 255, 0.05) !important;
  color: #fff !important;
  opacity: 1 !important;
  font-weight: 500;
}

.glass-card a {
  color: #00D4FF;
}

.glass-card a:hover {
  text-decoration: underline;
}

.mensaje {
  background-color: #1E1E2F;
  color: #F8BBD0;
  padding: 10px 15px;
  border-radius: 8px;
  text-align: center;
  margin-bottom: 20px;
  animation: fadeIn 0.5s ease-in-out;
}

@keyframes fadeIn {
  from { opacity: 0; transform: translateY(-10px);}
  to { opacity: 1; transform: translateY(0);}
}

.btn-custom {
  background: #00D4FF;
  border: none;
  color: #0C1634;
  font-weight: bold;
  transition: all 0.3s ease-in-out;
}

.btn-custom:hover {
  background: #00a6cc;
}

</style>

<div id="particles-js"></div>
<div class="container d-flex justify-content-center align-items-center" style="min-height: 100vh; position: relative; z-index: 1;">
  <div class="glass-card p-4 col-md-6 col-lg-4">
    <h2>Recuperar Contraseña</h2>
    <?php if ($mensaje): ?>
      <div class="mensaje"><?= htmlspecialchars($mensaje) ?></div>
    <?php endif; ?>

    <?php if ($paso == 1): ?>
      <form method="POST" action="recuperar_password.php">
        <div class="mb-3">
          <label for="correo" class="form-label">Correo:</label>
          <input type="email" class="form-control" name="correo" id="correo" required>
        </div>
        <button type="submit" class="btn btn-custom w-100 mb-2">Continuar</button>
        <a href="../login.php" class="btn btn-outline-light w-100 mb-2">Ir al inicio de sesión</a>
        <a href="../register.php" class="btn btn-outline-light w-100">Ir al Registro</a>
      </form>

    <?php elseif ($paso == 2): ?>
      <form method="POST" action="recuperar_password.php">
        <input type="hidden" name="correo" value="<?= htmlspecialchars($correo) ?>">
        <div class="mb-3">
          <label class="form-label">Pregunta Secreta:</label>
          <input type="text" class="form-control" value="<?= htmlspecialchars($pregunta) ?>" disabled>
        </div>
        <div class="mb-3">
          <label for="respuesta" class="form-label">Tu Respuesta:</label>
          <input type="text" class="form-control" name="respuesta" id="respuesta" required>
        </div>
        <button type="submit" class="btn btn-custom w-100 mb-2">Verificar Respuesta</button>
        <a href="../login.php" class="btn btn-outline-light w-100 mb-2">Ir al inicio de sesión</a>
        <a href="../register.php" class="btn btn-outline-light w-100">Ir al Registro</a>
      </form>

    <?php elseif ($paso == 3): ?>
      <form method="POST" action="recuperar_password.php">
        <input type="hidden" name="correo" value="<?= htmlspecialchars($correo) ?>">
        <div class="mb-3">
          <label for="nueva_contrasena" class="form-label">Nueva Contraseña:</label>
          <input type="password" class="form-control" name="nueva_contrasena" id="nueva_contrasena" required>
        </div>
        <button type="submit" class="btn btn-success w-100 mb-2">Guardar Nueva Contraseña</button>
      </form>
    <?php endif; ?>
  </div>
</div>


<script src="https://cdn.jsdelivr.net/particles.js/2.0.0/particles.min.js"></script>
<script>
particlesJS("particles-js", {
  "particles": {
    "number": { "value": 80, "density": { "enable": true, "value_area": 800 } },
    "color": { "value": "#ffffff" },
    "shape": { "type": "circle" },
    "opacity": { "value": 0.3, "anim": { "enable": true, "speed": 1 } },
    "size": { "value": 3, "random": true },
    "line_linked": { "enable": true, "distance": 150, "color": "#ffffff", "opacity": 0.2, "width": 1 },
    "move": { "enable": true, "speed": 2 }
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

<?php include '../includes/footer.php'; ?>
