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
if (!isset($_SESSION['usuario_id'])) {
    die("Acceso denegado");
}
$conn = db::conectar();
$usuario_id = intval($_SESSION['usuario_id']);
// Obtener nombre usuario
$stmtNombre = $conn->prepare("SELECT nombre FROM usuarios WHERE id = ?");
$stmtNombre->execute([$usuario_id]);
$nombreUsuario = $stmtNombre->fetchColumn() ?: 'Usuario';
// Crear hoja
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle('Transacciones');
//Logo con ruta absoluta
$logo = new Drawing();
$logo->setName('Logo');
$logo->setDescription('Logo Reporte');
$logo->setPath(__DIR__ . '/../img/reportes/logo1.png'); // Ruta absoluta para evitar errores
$logo->setHeight(70);
$logo->setCoordinates('A1');
$logo->setOffsetX(10);
$logo->setWorksheet($sheet);
$sheet->getRowDimension('1')->setRowHeight(70);
// Título reporte
$sheet->mergeCells('B1:E1');
$sheet->setCellValue('B1', 'Reporte de Transacciones - ' . $nombreUsuario);
$sheet->getStyle('B1')->applyFromArray([
    'font' => ['bold' => true, 'size' => 16],
    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
]);
// Fecha y hora de generación
$sheet->mergeCells('B2:E2');
$sheet->setCellValue('B2', 'Generado el: ' . date('Y-m-d H:i:s'));
$sheet->getStyle('B2')->applyFromArray([
    'font' => ['italic' => true, 'size' => 10],
    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
]);
// Encabezados SIN ID
$filaInicio = 4;
$encabezados = ['Tipo', 'Categoría', 'Monto', 'Fecha', 'Descripción'];
$sheet->fromArray($encabezados, NULL, "A$filaInicio");
// Estilo encabezados
$sheet->getStyle("A$filaInicio:E$filaInicio")->applyFromArray([
    'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '003366']],
    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
]);
// Datos
$stmt = $conn->prepare("SELECT tipo, categoria, monto, fecha, descripcion FROM transacciones WHERE id_usuario = ?");
$stmt->execute([$usuario_id]);
$fila = $filaInicio + 1;
$totalIngresos = 0;
$totalGastos = 0;
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $sheet->fromArray(array_values($row), NULL, "A$fila");
    // Colores alternos para filas
    $colorFondo = ($fila % 2 == 0) ? 'e6f2ff' : 'ffffff';
    $sheet->getStyle("A$fila:E$fila")->applyFromArray([
        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $colorFondo]],
        'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
    ]);
    if (strtolower($row['tipo']) === 'ingreso') {
        $totalIngresos += $row['monto'];
    } elseif (strtolower($row['tipo']) === 'gasto') {
        $totalGastos += $row['monto'];
    }
    $fila++;
}
// Totales
$sheet->mergeCells("A$fila:B$fila");
$sheet->setCellValue("A$fila", "Total Ingresos");
$sheet->setCellValue("C$fila", $totalIngresos);
$fila++;
$sheet->mergeCells("A$fila:B$fila");
$sheet->setCellValue("A$fila", "Total Gastos");
$sheet->setCellValue("C$fila", $totalGastos);
$fila++;
$sheet->mergeCells("A$fila:B$fila");
$sheet->setCellValue("A$fila", "Saldo/Ahorro");
$sheet->setCellValue("C$fila", $totalIngresos - $totalGastos);
// Estilo totales
$sheet->getStyle("A" . ($fila - 2) . ":C$fila")->applyFromArray([
    'font' => ['bold' => true],
    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '99ccff']],
    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
]);
// Ajustar ancho columnas
foreach (range('A', 'E') as $col) {
    $sheet->getColumnDimension($col)->setAutoSize(true);
}
// Exportar
if (isset($_POST['exportar_excel'])) {
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment; filename="reporte_transacciones.xlsx"');
    header('Cache-Control: max-age=0');
    $writer = new Xlsx($spreadsheet);
    $writer->save('php://output');
    exit;
}
if (isset($_POST['exportar_pdf'])) {
    IOFactory::registerWriter('Pdf', Mpdf::class);
    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="reporte_transacciones.pdf"');
    header('Cache-Control: max-age=0');
    $writer = IOFactory::createWriter($spreadsheet, 'Pdf');
    $writer->save('php://output');
    exit;
}
// Redirigir
header('Location: ../registro.php');
exit;
