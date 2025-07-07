<?php
session_start();
require_once 'db.php';
//Validar usuario
$idUsuario = $_SESSION['usuario_id'] ?? null;
if (!$idUsuario) {
    header('Location: ../login.php');
    exit;
}
//Obtener datos del POST
$tipo = $_POST['tipo'] ?? '';
$monto = floatval($_POST['monto'] ?? 0);
$fecha = $_POST['fecha'] ?? date('Y-m-d');
$descripcion = trim($_POST['descripcion'] ?? '');
$idCategoria = $_POST['categoria'] ?? null;
try {
    $conn = db::conectar();
    // Manejar nueva categoría si fue seleccionada
    if ($idCategoria === 'nueva' && !empty($_POST['nueva_categoria'])) {
        $nuevaCategoriaNombre = trim($_POST['nueva_categoria']);
        $sqlCat = "INSERT INTO categorias (nombre, tipo, usuario_id) VALUES (:nombre, :tipo, :usuario_id)";
        $stmtCat = $conn->prepare($sqlCat);
        $stmtCat->execute([
            ':nombre'     => $nuevaCategoriaNombre,
            ':tipo'       => $tipo,
            ':usuario_id' => $idUsuario
        ]);
        $idCategoria = $conn->lastInsertId();
    }
    // Verificar tipo válido
    if ($tipo !== 'ingreso' && $tipo !== 'gasto') {
        throw new Exception("Tipo de transacción no válido.");
    }
    // Insertar según tipo
    $tablaDestino = ($tipo === 'ingreso') ? 'ingresos' : 'gastos';
    $sql = "INSERT INTO $tablaDestino (usuario_id, categoria_id, monto, fecha, descripcion) 
            VALUES (:usuario_id, :categoria_id, :monto, :fecha, :descripcion)";
    $stmt = $conn->prepare($sql);
    $stmt->execute([
        ':usuario_id'   => $idUsuario,
        ':categoria_id' => $idCategoria,
        ':monto'        => $monto,
        ':fecha'        => $fecha,
        ':descripcion'  => $descripcion
    ]);
    header('Location: ../registro.php'); // redirigir al formulario, no al dashboard
    exit;
} catch (PDOException $e) {
    die("Error en la base de datos: " . $e->getMessage());
} catch (Exception $e) {
    die("Error: " . $e->getMessage());
}