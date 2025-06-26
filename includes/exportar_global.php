<?php
require_once '../includes/db.php';
require '../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Writer\Pdf\Mpdf;
use PhpOffice\PhpSpreadsheet\IOFactory;

$conn = db::conectar();
$filtro = $_POST['filtro'] ?? '';

$sql = "SELECT u.nombre AS usuario, t.tipo, t.categoria, t.monto, t.fecha, t.descripcion 
        FROM transacciones t 
        INNER JOIN usuarios u ON t.id_usuario = u.id 
        WHERE u.nombre LIKE ? OR t.categoria LIKE ?
        ORDER BY t.fecha DESC";

$stmt = $conn->prepare($sql);
$filtroSQL = "%$filtro%";
$stmt->execute([$filtroSQL, $filtroSQL]);
$data = $stmt->fetchAll(PDO::FETCH_ASSOC);

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle('Reporte Global');

$sheet->fromArray(['Usuario', 'Tipo', 'Categoría', 'Monto', 'Fecha', 'Descripción'], NULL, 'A1');

$rowNum = 2;
foreach ($data as $row) {
    $sheet->fromArray([
        $row['usuario'],
        $row['tipo'],
        $row['categoria'],
        $row['monto'],
        $row['fecha'],
        $row['descripcion']
    ], NULL, "A$rowNum");
    $rowNum++;
}

// Estilo visual
foreach (range('A', 'F') as $col) {
    $sheet->getColumnDimension($col)->setAutoSize(true);
}

if (isset($_POST['exportar_excel'])) {
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="reporte_global.xlsx"');
    header('Cache-Control: max-age=0');
    $writer = new Xlsx($spreadsheet);
    $writer->save('php://output');
    exit;
}

if (isset($_POST['exportar_pdf'])) {
    IOFactory::registerWriter('Pdf', Mpdf::class);
    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment;filename="reporte_global.pdf"');
    header('Cache-Control: max-age=0');
    $writer = IOFactory::createWriter($spreadsheet, 'Pdf');
    $writer->save('php://output');
    exit;
}
