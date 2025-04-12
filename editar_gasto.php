<?php
session_start();
require_once 'conexion.php';

if (!isset($_SESSION['id_usuario'])) {
    header("Location: login.php");
    exit();
}

$conexion = Conexion::conectar();

// Verificar si se envió un id de gasto válido
if (!isset($_GET['id_gasto']) || empty($_GET['id_gasto'])) {
    die("ID de gasto inválido.");
}

$id_gasto = $_GET['id_gasto'];
$id_usuario = $_SESSION['id_usuario'];

// Obtener los datos actuales del gasto
$sql = "SELECT * FROM gastos WHERE id_gasto = :id_gasto AND id_usuario = :id_usuario";
$stmt = $conexion->prepare($sql);
$stmt->bindParam(':id_gasto', $id_gasto);
$stmt->bindParam(':id_usuario', $id_usuario);
$stmt->execute();
$gasto = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$gasto) {
    die("Gasto no encontrado o no tienes permiso para editarlo.");
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $fecha = $_POST['fecha'] ?? '';
    $tipo = trim($_POST['tipo'] ?? '');
    $valor = $_POST['valor'] ?? 0;
    $descripcion = trim($_POST['descripcion'] ?? '');

    if (empty($fecha) || empty($tipo) || $valor <= 0) {
        die("Todos los campos son obligatorios y el valor debe ser positivo.");
    }

    // Actualizar el gasto en la base de datos
    $sql = "UPDATE gastos SET fecha = :fecha, tipo = :tipo, valor = :valor, descripcion = :descripcion WHERE id_gasto = :id_gasto AND id_usuario = :id_usuario";
    $stmt = $conexion->prepare($sql);
    $stmt->bindParam(':fecha', $fecha);
    $stmt->bindParam(':tipo', $tipo);
    $stmt->bindParam(':valor', $valor);
    $stmt->bindParam(':descripcion', $descripcion);
    $stmt->bindParam(':id_gasto', $id_gasto);
    $stmt->bindParam(':id_usuario', $id_usuario);

    if ($stmt->execute()) {
        header("Location: ver_gastos.php");
        exit();
    } else {
        echo "Error al actualizar el gasto.";
    }
}
?>

<!-- Formulario de edición de gasto con datos prellenados -->
<form method="POST">
    <input type="hidden" name="id_gasto" value="<?php echo htmlspecialchars($id_gasto); ?>">
    
    <label>Fecha:</label>
    <input type="date" name="fecha" value="<?php echo htmlspecialchars($gasto['fecha']); ?>" required>

    <label>Tipo:</label>
    <input type="text" name="tipo" value="<?php echo htmlspecialchars($gasto['tipo']); ?>" required>

    <label>Valor:</label>
    <input type="number" name="valor" step="0.01" value="<?php echo htmlspecialchars($gasto['valor']); ?>" required>

    <label>Descripción:</label>
    <textarea name="descripcion"><?php echo htmlspecialchars($gasto['descripcion']); ?></textarea>

    <button type="submit">Actualizar Gasto</button>
</form>
