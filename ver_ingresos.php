<?php
session_start();
require_once 'conexion.php';

// Verificar si el usuario está autenticado
if (!isset($_SESSION['id_usuario'])) {
    header("Location: login.php");
    exit();
}

$idUsuario = $_SESSION['id_usuario'];

try {
    $conexion = Conexion::conectar();

    // Obtener los ingresos registrados por el usuario con el nombre de la categoría
    $sql = "SELECT ingresos.id, ingresos.descripcion, ingresos.monto, ingresos.fecha, categorias.nombre AS categoria
            FROM ingresos
            LEFT JOIN categorias ON ingresos.id_categoria = categorias.id
            WHERE ingresos.id_usuario = :id_usuario";
    $stmt = $conexion->prepare($sql);
    $stmt->bindParam(':id_usuario', $idUsuario);
    $stmt->execute();
    $ingresos = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    echo "Error al obtener ingresos: " . $e->getMessage();
}

// Eliminar ingreso
if (isset($_GET['eliminar_id'])) {
    $idIngreso = $_GET['eliminar_id'];

    try {
        $sqlEliminar = "DELETE FROM ingresos WHERE id = :id AND id_usuario = :id_usuario";
        $stmtEliminar = $conexion->prepare($sqlEliminar);
        $stmtEliminar->bindParam(':id', $idIngreso);
        $stmtEliminar->bindParam(':id_usuario', $idUsuario);
        $stmtEliminar->execute();

        header("Location: ver_ingresos.php"); // Redirigir después de eliminar
        exit();
    } catch (PDOException $e) {
        echo "Error al eliminar el ingreso: " . $e->getMessage();
    }
}

?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Ver Ingresos - GastoSimple</title>
  <link rel="stylesheet" href="estilos.css">
</head>
<body>

  <header>
    <h1>Mis Ingresos</h1>
    <nav>
      <ul>
        <li><a href="menu.php">Menú</a></li>
        <li><a href="perfil.php">Mi Perfil</a></li>
        <li><a href="logout.php">Cerrar Sesión</a></li>
      </ul>
    </nav>
  </header>

  <main>
    <h2>Ingresos Registrados</h2>

    <?php if (empty($ingresos)): ?>
      <p>No tienes ingresos registrados. <a href="registrar_ingreso.php">Haz clic aquí</a> para agregar un ingreso.</p>
    <?php else: ?>
      <form method="POST" action="eliminar_ingresos.php">
        <table>
          <thead>
            <tr>
              <th>Descripción</th>
              <th>Monto</th>
              <th>Fecha</th>
              <th>Categoría</th>
              <th>Acciones</th>
              <th><input type="checkbox" id="seleccionar_todos" onclick="seleccionarTodos()"></th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($ingresos as $ingreso): ?>
              <tr>
                <td><?= htmlspecialchars($ingreso['descripcion']) ?></td>
                <td><?= number_format($ingreso['monto'], 2) ?></td>
                <td><?= date('d/m/Y', strtotime($ingreso['fecha'])) ?></td>
                <td><?= htmlspecialchars($ingreso['categoria']) ?></td>
                <td>
                  <a href="editar_ingreso.php?id=<?= $ingreso['id'] ?>">Editar</a>
                  <a href="ver_ingresos.php?eliminar_id=<?= $ingreso['id'] ?>" onclick="return confirm('¿Estás seguro de eliminar este ingreso?')">Eliminar</a>
                </td>
                <td>
                  <input type="checkbox" name="ingresos_eliminar[]" value="<?= $ingreso['id'] ?>">
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>

        <?php if (count($ingresos) > 1): ?>
          <button type="submit" name="eliminar_todos" onclick="return confirm('¿Estás seguro de eliminar todos los ingresos seleccionados?')">Eliminar Ingresos Seleccionados</button>
        <?php endif; ?>
      </form>
    <?php endif; ?>

  </main>

  <footer>
    <ul>
      <li><a href="terminos.html">Términos y Condiciones</a></li>
    </ul>
  </footer>

  <script>
    function seleccionarTodos() {
      var checkboxes = document.querySelectorAll('input[type="checkbox"]');
      var seleccionarTodos = document.getElementById('seleccionar_todos').checked;
      checkboxes.forEach(function (checkbox) {
        checkbox.checked = seleccionarTodos;
      });
    }
  </script>

</body>
</html>
