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

    // Obtener los gastos registrados por el usuario con el nombre de la categoría
    $sql = "SELECT g.id, g.descripcion, g.monto, g.fecha, c.nombre AS categoria
            FROM gastos g
            LEFT JOIN categorias c ON g.id_categoria = c.id
            WHERE g.id_usuario = :id_usuario";
    $stmt = $conexion->prepare($sql);
    $stmt->bindParam(':id_usuario', $idUsuario);
    $stmt->execute();
    $gastos = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    echo "Error al obtener gastos: " . $e->getMessage();
}

// Eliminar gasto individual
if (isset($_GET['eliminar_id'])) {
    $idGasto = $_GET['eliminar_id'];

    try {
        $sqlEliminar = "DELETE FROM gastos WHERE id = :id AND id_usuario = :id_usuario";
        $stmtEliminar = $conexion->prepare($sqlEliminar);
        $stmtEliminar->bindParam(':id', $idGasto);
        $stmtEliminar->bindParam(':id_usuario', $idUsuario);
        $stmtEliminar->execute();

        header("Location: ver_gastos.php"); // Redirigir después de eliminar
        exit();
    } catch (PDOException $e) {
        echo "Error al eliminar el gasto: " . $e->getMessage();
    }
}

?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Mis Gastos – GastoSimple</title>
  <link rel="stylesheet" href="estilos.css">
</head>
<body>

  <header>
    <h1>Mis Gastos</h1>
    <nav>
      <ul>
        <li><a href="menu.php">Menú</a></li>
        <li><a href="perfil.php">Mi Perfil</a></li>
        <li><a href="logout.php">Cerrar Sesión</a></li>
      </ul>
    </nav>

  </header>

  <main>
    <h2>Gastos Registrados</h2>

    <?php if (empty($gastos)): ?>
      <p>No tienes gastos registrados. <a href="registrar_gasto.php">Haz clic aquí</a> para agregar un gasto.</p>
    <?php else: ?>
      <form method="POST" action="eliminar_gastos.php">
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
            <?php foreach ($gastos as $gasto): ?>
              <tr>
                <td><?= htmlspecialchars($gasto['descripcion']) ?></td>
                <td><?= number_format($gasto['monto'], 2) ?></td>
                <td><?= date('d/m/Y', strtotime($gasto['fecha'])) ?></td>
                <td><?= htmlspecialchars($gasto['categoria']) ?></td>
                <td>
                  <a href="editar_gasto.php?id=<?= $gasto['id'] ?>">Editar</a>
                  <a href="ver_gastos.php?eliminar_id=<?= $gasto['id'] ?>" onclick="return confirm('¿Estás seguro de eliminar este gasto?')">Eliminar</a>
                </td>
                <td>
                  <input type="checkbox" name="gastos_eliminar[]" value="<?= $gasto['id'] ?>">
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>

        <?php if (count($gastos) > 1): ?>
          <button type="submit" name="eliminar_todos" onclick="return confirm('¿Estás seguro de eliminar todos los gastos seleccionados?')">Eliminar Gastos Seleccionados</button>
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
