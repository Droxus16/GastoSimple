<?php include 'includes/header.php'; ?>
<?php require_once 'includes/db.php'; ?>
<?php session_start(); ?>

<link rel="stylesheet" href="assets/css/estilos.css">

<style>
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

<?php include 'includes/footer.php'; ?>
