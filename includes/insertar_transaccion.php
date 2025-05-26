<?php
session_start();
require_once 'db.php';

// Validar usuario
$idUsuario = $_SESSION['usuario_id'] ?? null;
if (!$idUsuario) {
    header('Location: ../login.php'); // o la página que uses para login
    exit;
}

// Obtener datos del POST
$tipo = $_POST['tipo'] ?? '';
$monto = $_POST['monto'] ?? 0;
$fecha = $_POST['fecha'] ?? date('Y-m-d');
$descripcion = $_POST['descripcion'] ?? '';
$idCategoria = $_POST['categoria'] ?? null;

try {
    $conn = db::conectar();

    // Manejar nueva categoría
    if ($idCategoria === 'nueva') {
        $nuevaCategoriaNombre = trim($_POST['nueva_categoria'] ?? '');
        if ($nuevaCategoriaNombre === '') {
            // Podrías manejar un error o redirigir con mensaje
            die('Nombre de nueva categoría requerido');
        }

        $sqlCat = "INSERT INTO categorias (nombre, tipo, usuario_id) VALUES (:nombre, :tipo, :usuario_id)";
        $stmtCat = $conn->prepare($sqlCat);
        $stmtCat->bindParam(':nombre', $nuevaCategoriaNombre);
        $stmtCat->bindParam(':tipo', $tipo);
        $stmtCat->bindParam(':usuario_id', $idUsuario);
        $stmtCat->execute();

        $idCategoria = $conn->lastInsertId();
    }

    // Insertar en ingresos o gastos
    if ($tipo === 'ingreso') {
        $sql = "INSERT INTO ingresos (usuario_id, categoria_id, monto, fecha, descripcion) 
                VALUES (:usuario_id, :categoria_id, :monto, :fecha, :descripcion)";
    } else {
        $sql = "INSERT INTO gastos (usuario_id, categoria_id, monto, fecha, descripcion) 
                VALUES (:usuario_id, :categoria_id, :monto, :fecha, :descripcion)";
    }

    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':usuario_id', $idUsuario);
    $stmt->bindParam(':categoria_id', $idCategoria);
    $stmt->bindParam(':monto', $monto);
    $stmt->bindParam(':fecha', $fecha);
    $stmt->bindParam(':descripcion', $descripcion);

    $stmt->execute();

    // Redirigir al dashboard después del éxito
    header('Location: ../dashboard.php');
    exit;

} catch (PDOException $e) {
    die("Error en la base de datos: " . $e->getMessage());
}
