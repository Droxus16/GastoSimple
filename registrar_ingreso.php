<?php
session_start();
require_once 'conexion.php';

// Verificar si el usuario está autenticado
if (!isset($_SESSION['id_usuario'])) {
    header("Location: login.php");
    exit();
}

$idUsuario = $_SESSION['id_usuario'];

// Obtener categorías disponibles para el formulario
try {
    $pdo = Conexion::conectar();
    $sql = "SELECT * FROM categorias";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $categorias = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error al obtener las categorías: " . $e->getMessage());
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $categoria = $_POST['categoria'];
    $monto = $_POST['monto'];
    $descripcion = $_POST['descripcion'];
    $fecha = $_POST['fecha'];

    // Validar que los campos no estén vacíos
    if (empty($categoria) || empty($monto) || empty($descripcion) || empty($fecha)) {
        $error = "Por favor, completa todos los campos.";
    } else {
        // Insertar el ingreso en la base de datos
        try {
            $sql = "INSERT INTO ingresos (id_usuario, id_categoria, monto, descripcion, fecha)
                    VALUES (:id_usuario, :id_categoria, :monto, :descripcion, :fecha)";
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':id_usuario', $idUsuario);
            $stmt->bindParam(':id_categoria', $categoria);
            $stmt->bindParam(':monto', $monto);
            $stmt->bindParam(':descripcion', $descripcion);
            $stmt->bindParam(':fecha', $fecha);
            $stmt->execute();
            header("Location: ver_ingresos.php");
            exit();
        } catch (PDOException $e) {
            $error = "Error al registrar el ingreso: " . $e->getMessage();
        }
    }
}

?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Registrar Ingreso – GastoSimple</title>
  <link rel="stylesheet" href="estilos.css">
</head>
<body>

  <header>
    <h1>Registrar Ingreso</h1>
    <nav>
      <ul>
        <li><a href="menu.php">Menú</a></li>
        <li><a href="perfil.php">Mi Perfil</a></li>
        <li><a href="logout.php">Cerrar Sesión</a></li>
      </ul>
    </nav>
  </header>

  <main>
    <h2>Nuevo Ingreso</h2>

    <?php if (isset($error)): ?>
      <p style="color:red;"><?= $error ?></p>
    <?php endif; ?>

    <form method="POST">
      <label for="categoria">Categoría:</label>
      <select name="categoria" id="categoria">
        <?php foreach ($categorias as $categoria): ?>
          <option value="<?= $categoria['id'] ?>"><?= htmlspecialchars($categoria['nombre']) ?></option>
        <?php endforeach; ?>
      </select><br>

      <label for="monto">Monto:</label>
      <input type="number" name="monto" id="monto" step="0.01" required><br>

      <label for="descripcion">Descripción:</label>
      <textarea name="descripcion" id="descripcion" required></textarea><br>

      <label for="fecha">Fecha:</label>
      <input type="date" name="fecha" id="fecha" required><br>

      <button type="submit">Registrar Ingreso</button>
    </form>
  </main>

  <footer>
    <ul>
      <li><a href="terminos.html">Términos y Condiciones</a></li>
    </ul>
  </footer>

</body>
</html>
