<?php
session_start();
require_once '../includes/db.php';
require '../vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\IOFactory;
if (!isset($_SESSION['usuario_id'])) {
    die("Acceso denegado");
}
$idUsuario = $_SESSION['usuario_id'];
if (!isset($_FILES['archivo_excel']) || $_FILES['archivo_excel']['error'] !== UPLOAD_ERR_OK) {
    die("Error al subir el archivo.");
}
$archivoTmp = $_FILES['archivo_excel']['tmp_name'];
$spreadsheet = IOFactory::load($archivoTmp);
$sheet = $spreadsheet->getActiveSheet();
$datos = $sheet->toArray();
//Validar encabezados esperados
$encabezadosEsperados = ['ID', 'Tipo', 'Categoría', 'Monto', 'Fecha', 'Descripción'];
if (array_map('strtolower', $datos[0]) !== array_map('strtolower', $encabezadosEsperados)) {
    die("Formato de Excel no válido. Asegúrate de usar los encabezados correctos.");
}
$conn = db::conectar();
// Saltar encabezado
for ($i = 1; $i < count($datos); $i++) {
    [$id, $tipo, $categoriaNombre, $monto, $fecha, $descripcion] = $datos[$i];

    if (!in_array($tipo, ['ingreso', 'gasto']) || !is_numeric($monto) || !$fecha) {
        continue; // saltar fila inválida
    }
    // Buscar o insertar categoría
    $stmt = $conn->prepare("SELECT id FROM categorias WHERE nombre = ? AND tipo = ? AND usuario_id = ?");
    $stmt->execute([$categoriaNombre, $tipo, $idUsuario]);
    $categoria = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$categoria) {
        $insertCat = $conn->prepare("INSERT INTO categorias (nombre, tipo, usuario_id) VALUES (?, ?, ?)");
        $insertCat->execute([$categoriaNombre, $tipo, $idUsuario]);
        $categoriaId = $conn->lastInsertId();
    } else {
        $categoriaId = $categoria['id'];
    }
    // Insertar en tabla ingresos o gastos
    $tabla = $tipo === 'ingreso' ? 'ingresos' : 'gastos';
    $insert = $conn->prepare("INSERT INTO $tabla (usuario_id, categoria_id, monto, descripcion, fecha) VALUES (?, ?, ?, ?, ?)");
    $insert->execute([$idUsuario, $categoriaId, $monto, $descripcion, $fecha]);
    // Obtener el ID insertado
    $idTransaccion = $conn->lastInsertId();
    // Insertar en tabla transacciones
    $insertTrans = $conn->prepare("INSERT INTO transacciones (id_usuario, tipo, categoria_id, monto, fecha, descripcion) VALUES (?, ?, ?, ?, ?, ?)");
    $insertTrans->execute([$idUsuario, $tipo, $categoriaId, $monto, $fecha, $descripcion]);
}
header("Location: ../registro.php?import=ok");
exit;
