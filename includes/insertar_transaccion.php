<?php
session_start();
require_once 'db.php';

// Validar sesión de usuario
$idUsuario = $_SESSION['usuario_id'] ?? null;
if (!$idUsuario) {
    header('Location: ../login.php');
    exit;
}

// Obtener datos del formulario
$tipo         = $_POST['tipo'] ?? '';
$monto        = floatval($_POST['monto'] ?? 0);
$fecha        = $_POST['fecha'] ?? date('Y-m-d');
$descripcion  = trim($_POST['descripcion'] ?? '');
$idCategoria  = $_POST['categoria'] ?? null;
$recurrente   = isset($_POST['recurrente']) ? 1 : 0;
$frecuencia   = $_POST['frecuencia'] ?? null;
$diaFijo      = $_POST['dia_fijo'] ?? null;
$montoVariable = isset($_POST['monto_variable']) ? 1 : 0;

try {
    $conn = db::conectar();

    // Insertar nueva categoría si es el caso
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

    if (!in_array($tipo, ['ingreso', 'gasto'])) {
        throw new Exception("Tipo de transacción no válido.");
    }

    // Insertar transacción actual
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

    // Si es recurrente, guardar en tabla de recurrencias
    if ($recurrente && $frecuencia) {
        $sqlRecurrente = "INSERT INTO transacciones_recurrentes 
            (usuario_id, tipo, categoria_id, monto, fecha_inicio, frecuencia, dia_fijo, descripcion, monto_variable)
            VALUES (:usuario_id, :tipo, :categoria_id, :monto, :fecha_inicio, :frecuencia, :dia_fijo, :descripcion, :monto_variable)";
        $stmtR = $conn->prepare($sqlRecurrente);
        $stmtR->execute([
            ':usuario_id'     => $idUsuario,
            ':tipo'           => $tipo,
            ':categoria_id'   => $idCategoria,
            ':monto'          => $monto,
            ':fecha_inicio'   => $fecha,
            ':frecuencia'     => $frecuencia,
            ':dia_fijo'       => $diaFijo ?: null,
            ':descripcion'    => $descripcion,
            ':monto_variable' => $montoVariable
        ]);
    }

    header('Location: ../registro.php');
    exit;

} catch (PDOException $e) {
    die("Error en la base de datos: " . $e->getMessage());
} catch (Exception $e) {
    die("Error: " . $e->getMessage());
}