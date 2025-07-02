<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/auth.php';
require '../../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

if (!isset($_SESSION['usuario_id'])) {
    die("Acceso denegado");
}

$conn = db::conectar();
$usuario_id = intval($_SESSION['usuario_id']);

// Nombre usuario
$stmtNombre = $conn->prepare("SELECT nombre FROM usuarios WHERE id = ?");
$stmtNombre->execute([$usuario_id]);
$nombreUsuario = $stmtNombre->fetchColumn() ?: 'Usuario';

// Crear hoja
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle('Transacciones');

// Insertar logo
$logo = new Drawing();
$logo->setName('Logo');
$logo->setDescription('Logo Reporte');
$logo->setPath('../img/reportes/logo1.png'); // ruta ajustada
$logo->setHeight(80);
$logo->setCoordinates('A1');
$logo->setWorksheet($sheet);
$sheet->getRowDimension('1')->setRowHeight(80);

// Título
$sheet->mergeCells('B1:E1');
$sheet->setCellValue('B1', 'Reporte de Transacciones - ' . $nombreUsuario);
$sheet->getStyle('B1')->applyFromArray([
    'font' => ['bold' => true, 'size' => 14],
    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
]);

// Fecha
$sheet->mergeCells('B2:E2');
$sheet->setCellValue('B2', 'Generado el: ' . date('Y-m-d H:i:s'));
$sheet->getStyle('B2')->applyFromArray([
    'font' => ['italic' => true, 'size' => 10],
    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
]);

// Encabezados
$filaInicio = 4;
$encabezados = ['Tipo', 'Categoría', 'Monto', 'Fecha', 'Descripción'];
$sheet->fromArray($encabezados, NULL, "A$filaInicio");

$sheet->getStyle("A$filaInicio:E$filaInicio")->applyFromArray([
    'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '005580']],
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

$sheet->getStyle("A" . ($fila - 2) . ":C$fila")->applyFromArray([
    'font' => ['bold' => true],
    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'd9edf7']],
    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
]);

// Ajustar ancho columnas
foreach (range('A', 'E') as $col) {
    $sheet->getColumnDimension($col)->setAutoSize(true);
}

// Descargar
$writer = new Xlsx($spreadsheet);
header("Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
header("Content-Disposition: attachment; filename=\"reporte_transacciones.xlsx\"");
header("Cache-Control: max-age=0");
$writer->save("php://output");
exit;
?>
