<?php
session_start();
require_once '../includes/db.php';
require '../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Writer\Pdf\Mpdf;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

if (!isset($_SESSION['usuario_id'])) {
    die("Acceso denegado");
}

$conn = db::conectar();
$usuario_id = intval($_SESSION['usuario_id']);
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle('Transacciones');

// Encabezados
$encabezados = ['ID', 'Tipo', 'Categoría', 'Monto', 'Fecha', 'Descripción'];
$sheet->fromArray($encabezados, NULL, 'A1');

// Estilo
$estiloEncabezado = [
    'font' => [
        'bold' => true,
        'color' => ['rgb' => 'FFFFFF'],
    ],
    'fill' => [
        'fillType' => Fill::FILL_SOLID,
        'startColor' => ['rgb' => '333333'],
    ],
    'alignment' => [
        'horizontal' => Alignment::HORIZONTAL_CENTER,
        'vertical' => Alignment::VERTICAL_CENTER,
    ],
    'borders' => [
        'allBorders' => [
            'borderStyle' => Border::BORDER_THIN,
            'color' => ['rgb' => '999999'],
        ],
    ],
];

$sheet->getStyle('A1:F1')->applyFromArray($estiloEncabezado);

// Consulta segura
$stmt = $conn->prepare("SELECT id, tipo, categoria, monto, fecha, descripcion FROM transacciones WHERE id_usuario = ?");
$stmt->execute([$usuario_id]);

// Escribir datos y aplicar estilo fila por fila
$fila = 2;
$estiloFila = [
    'fill' => [
        'fillType' => Fill::FILL_SOLID,
        'startColor' => ['rgb' => '555555'],
    ],
    'font' => [
        'color' => ['rgb' => 'FFFFFF'],
    ],
    'alignment' => [
        'horizontal' => Alignment::HORIZONTAL_CENTER,
        'vertical' => Alignment::VERTICAL_CENTER,
    ],
    'borders' => [
        'allBorders' => [
            'borderStyle' => Border::BORDER_THIN,
            'color' => ['rgb' => '999999'],
        ],
    ],
];

while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $sheet->fromArray(array_values($row), NULL, "A$fila");
    $sheet->getStyle("A$fila:F$fila")->applyFromArray($estiloFila);
    $fila++;
}

// Ajustar automáticamente el ancho de las columnas
foreach (range('A', 'F') as $col) {
    $sheet->getColumnDimension($col)->setAutoSize(true);
}

// Exportar Excel
if (isset($_POST['exportar_excel'])) {
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="transacciones.xlsx"');
    header('Cache-Control: max-age=0');
    $writer = new Xlsx($spreadsheet);
    $writer->save('php://output');
    exit;
}

// Exportar PDF
if (isset($_POST['exportar_pdf'])) {
    IOFactory::registerWriter('Pdf', Mpdf::class);
    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment;filename="transacciones.pdf"');
    header('Cache-Control: max-age=0');
    $writer = IOFactory::createWriter($spreadsheet, 'Pdf');
    $writer->save('php://output');
    exit;
}

// redirigir
header('Location: ../registro.php');
exit;
