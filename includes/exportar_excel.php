<?php
session_start();
require_once 'db.php';
require_once 'auth.php';

require '../../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

$conn = db::conectar();
$idUsuario = $_SESSION['usuario_id'];

$sql = "SELECT tipo, fecha, monto, categoria, descripcion FROM transacciones WHERE id_usuario = ?";
$stmt = $conn->prepare($sql);
$stmt->execute([$idUsuario]);
$registros = $stmt->fetchAll(PDO::FETCH_ASSOC);

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

$sheet->setTitle("Transacciones");

// Encabezados
$encabezados = ["Tipo", "Fecha", "Monto", "Categoría", "Descripción"];
$sheet->fromArray($encabezados, NULL, 'A1');

// Contenido
$fila = 2;
foreach ($registros as $transaccion) {
    $sheet->setCellValue("A$fila", ucfirst($transaccion['tipo']));
    $sheet->setCellValue("B$fila", $transaccion['fecha']);
    $sheet->setCellValue("C$fila", $transaccion['monto']);
    $sheet->setCellValue("D$fila", $transaccion['categoria']);
    $sheet->setCellValue("E$fila", $transaccion['descripcion']);
    $fila++;
}

// Descargar
$writer = new Xlsx($spreadsheet);
$nombreArchivo = "reporte_transacciones.xlsx";

header("Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
header("Content-Disposition: attachment; filename=\"$nombreArchivo\"");
header("Cache-Control: max-age=0");

$writer->save("php://output");
exit;
?>
