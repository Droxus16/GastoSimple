<?php
session_start();
require_once '../includes/db.php';
require '../vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Writer\Pdf\Mpdf;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
//Validar solo admin
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'admin') {
  die("Acceso denegado");
}
//Conexión
$conn = db::conectar();
$filtro = $_POST['filtro'] ?? '';
//Consulta
$sql = "SELECT u.nombre AS usuario, t.tipo, t.categoria, t.monto, t.fecha, t.descripcion 
        FROM transacciones t 
        INNER JOIN usuarios u ON t.id_usuario = u.id 
        WHERE u.nombre LIKE ? OR t.categoria LIKE ?
        ORDER BY t.fecha DESC";
$stmt = $conn->prepare($sql);
$filtroSQL = "%$filtro%";
$stmt->execute([$filtroSQL, $filtroSQL]);
$data = $stmt->fetchAll(PDO::FETCH_ASSOC);
//Crear hoja
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle('Reporte Global');
//Logo
$logo = new Drawing();
$logo->setName('Logo');
$logo->setDescription('Logo Reporte');
$logo->setPath(__DIR__ . '/../img/reportes/logo1.png');
$logo->setHeight(70);
$logo->setCoordinates('A1');
$logo->setWorksheet($sheet);
$sheet->getRowDimension('1')->setRowHeight(70);
//Título
$sheet->mergeCells('B1:F1');
$sheet->setCellValue('B1', 'Reporte Global de Transacciones');
$sheet->getStyle('B1')->applyFromArray([
  'font' => ['bold' => true, 'size' => 16],
  'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
]);
//Fecha
$sheet->mergeCells('B2:F2');
$sheet->setCellValue('B2', 'Generado el: ' . date('Y-m-d H:i:s'));
$sheet->getStyle('B2')->applyFromArray([
  'font' => ['italic' => true, 'size' => 10],
  'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
]);
//Encabezados
$filaInicio = 4;
$encabezados = ['Usuario', 'Tipo', 'Categoría', 'Monto', 'Fecha', 'Descripción'];
$sheet->fromArray($encabezados, NULL, "A$filaInicio");
$sheet->getStyle("A$filaInicio:F$filaInicio")->applyFromArray([
  'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
  'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '005580']],
  'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
  'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
]);
// Datos
$fila = $filaInicio + 1;
foreach ($data as $row) {
  $sheet->fromArray(array_values($row), NULL, "A$fila");
  $colorFondo = ($fila % 2 == 0) ? 'e6f2ff' : 'ffffff';
  $sheet->getStyle("A$fila:F$fila")->applyFromArray([
    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $colorFondo]],
    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
  ]);

  $fila++;
}
// Ajustar ancho
foreach (range('A', 'F') as $col) {
  $sheet->getColumnDimension($col)->setAutoSize(true);
}
// Exportar
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
//Redirigir en fallback
header("Location: ../admin_reportes.php");
exit;