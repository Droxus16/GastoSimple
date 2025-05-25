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
  <h2>Registro de Usuario</h2>

  <?php
  $mensaje = "";
  if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre = trim($_POST["nombre"]);
    $correo = trim($_POST["correo"]);
    $contrasena = $_POST["contrasena"];
    $rol = "estandar";

    if (!empty($nombre) && !empty($correo) && !empty($contrasena)) {
      try {
        $db = db::conectar();

        $stmt = $db->prepare("SELECT id FROM usuarios WHERE correo = ?");
        $stmt->execute([$correo]);

        if ($stmt->rowCount() > 0) {
          $mensaje = "El correo ya está registrado.";
        } else {
          $hash = password_hash($contrasena, PASSWORD_DEFAULT);
          $stmt = $db->prepare("INSERT INTO usuarios (nombre, correo, contrasena, rol) VALUES (?, ?, ?, ?)");
          $stmt->execute([$nombre, $correo, $hash, $rol]);

          $idUsuario = $db->lastInsertId();
          $_SESSION['usuario_id'] = $idUsuario;
          $_SESSION['nombre'] = $nombre;
          $_SESSION['rol'] = $rol;

          header("Location: dashboard.php");
          exit();
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

    <!-- ENLACE A LOGIN -->
    <p style="text-align:center; margin-top:10px;">¿Ya tienes un usuario? <a href="login.php">Inicia sesión</a></p>
  </form>
</div>

<script>
  // Mostrar/ocultar contraseña
  document.getElementById('togglePassword').addEventListener('change', function () {
    const passwordInput = document.getElementById('contrasena');
    passwordInput.type = this.checked ? 'text' : 'password';
  });

  // Habilitar botón solo si se aceptan los términos
  const terminos = document.getElementById('terminos');
  const btnRegistro = document.getElementById('btnRegistro');
  terminos.addEventListener('change', function () {
    btnRegistro.disabled = !this.checked;
  });
</script>

<?php include 'includes/footer.php'; ?>
