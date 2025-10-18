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

// ===================== VALIDAR SESIÓN =====================
if (!isset($_SESSION['usuario_id'])) {
    die("Acceso denegado");
}

$conn = db::conectar();
$usuario_id = intval($_SESSION['usuario_id']);

// ===================== NOMBRE DEL USUARIO =====================
$stmtNombre = $conn->prepare("SELECT nombre FROM usuarios WHERE id = ?");
$stmtNombre->execute([$usuario_id]);
$nombreUsuario = $stmtNombre->fetchColumn() ?: 'Usuario';

// ===================== FECHAS OPCIONALES =====================
$fecha_inicio = $_POST['fecha_inicio'] ?? null;
$fecha_fin    = $_POST['fecha_fin'] ?? null;

// ===================== CONSULTA DE TRANSACCIONES =====================
if ($fecha_inicio && $fecha_fin) {
    $stmt = $conn->prepare("
        SELECT tipo, categoria, monto, fecha, descripcion 
        FROM transacciones 
        WHERE id_usuario = ? AND DATE(fecha) BETWEEN ? AND ?
        ORDER BY fecha DESC
    ");
    $stmt->execute([$usuario_id, $fecha_inicio, $fecha_fin]);
} else {
    $stmt = $conn->prepare("
        SELECT tipo, categoria, monto, fecha, descripcion 
        FROM transacciones 
        WHERE id_usuario = ?
        ORDER BY fecha DESC
    ");
    $stmt->execute([$usuario_id]);
}

// ===================== CREAR HOJA =====================
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle('Transacciones');

// ===================== LOGO =====================
$logoPath = __DIR__ . '/../img/reportes/logo1.png';
if (file_exists($logoPath)) {
    $logo = new Drawing();
    $logo->setName('Logo');
    $logo->setDescription('Logo Reporte');
    $logo->setPath($logoPath);
    $logo->setHeight(70);
    $logo->setCoordinates('A1');
    $logo->setWorksheet($sheet);
}
$sheet->getRowDimension('1')->setRowHeight(70);

// ===================== TÍTULO =====================
$sheet->mergeCells('B1:E1');
$titulo = 'Reporte de Transacciones - ' . $nombreUsuario;
if ($fecha_inicio && $fecha_fin) {
    $titulo .= " ({$fecha_inicio} a {$fecha_fin})";
}
$sheet->setCellValue('B1', $titulo);
$sheet->getStyle('B1')->applyFromArray([
    'font' => ['bold' => true, 'size' => 16],
    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
]);

$sheet->mergeCells('B2:E2');
$sheet->setCellValue('B2', 'Generado el: ' . date('Y-m-d H:i:s'));
$sheet->getStyle('B2')->applyFromArray([
    'font' => ['italic' => true, 'size' => 10],
    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
]);

// ===================== FUNCIÓN: INSERTAR PANTALLAZO =====================
function insertarPantallazo($sheet, $base64, $celda = 'A4', $nombre = 'Pantallazo_Dashboard', $altura = 550) {
    if (empty($base64) || strpos($base64, 'data:image') === false) {
        return false;
    }

    // Limpiar y decodificar base64
    $base64 = preg_replace('#^data:image/\w+;base64,#i', '', $base64);
    $base64 = str_replace(' ', '+', $base64);
    $imagen = base64_decode($base64);

    if (!$imagen) {
        return false;
    }

    // Crear carpeta temporal
    $tempDir = __DIR__ . '/../temp/';
    if (!file_exists($tempDir)) {
        mkdir($tempDir, 0777, true);
    }

    $imgPath = $tempDir . $nombre . '.png';
    file_put_contents($imgPath, $imagen);

    if (!file_exists($imgPath) || filesize($imgPath) === 0) {
        return false;
    }

    // Insertar imagen en hoja
    $drawing = new Drawing();
    $drawing->setName($nombre);
    $drawing->setDescription($nombre);
    $drawing->setPath($imgPath);
    $drawing->setCoordinates($celda);
    $drawing->setHeight($altura);
    $drawing->setWorksheet($sheet);

    return $imgPath;
}

// ===================== ENCABEZADOS =====================
$filaInicio = 5;
$encabezados = ['Tipo', 'Categoría', 'Monto', 'Fecha', 'Descripción'];
$sheet->fromArray($encabezados, NULL, "A$filaInicio");
$sheet->getStyle("A$filaInicio:E$filaInicio")->applyFromArray([
    'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '003366']],
    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
]);

// ===================== DATOS =====================
$fila = $filaInicio + 1;
$totalIngresos = 0;
$totalGastos = 0;

while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $sheet->fromArray(array_values($row), NULL, "A$fila");

    $colorFondo = ($fila % 2 == 0) ? 'E6F2FF' : 'FFFFFF';
    $sheet->getStyle("A$fila:E$fila")->applyFromArray([
        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $colorFondo]],
        'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
    ]);

    if (strtolower($row['tipo']) === 'ingreso') $totalIngresos += $row['monto'];
    if (strtolower($row['tipo']) === 'gasto') $totalGastos += $row['monto'];
    $fila++;
}

// ===================== TOTALES =====================
$sheet->mergeCells("A$fila:B$fila");
$sheet->setCellValue("A$fila", "Total Ingresos");
$sheet->setCellValue("C$fila", $totalIngresos);
$fila++;

$sheet->mergeCells("A$fila:B$fila");
$sheet->setCellValue("A$fila", "Total Gastos");
$sheet->setCellValue("C$fila", $totalGastos);
$fila++;

$sheet->mergeCells("A$fila:B$fila");
$sheet->setCellValue("A$fila", "Saldo / Ahorro");
$sheet->setCellValue("C$fila", $totalIngresos - $totalGastos);

$sheet->getStyle("A" . ($fila - 2) . ":C$fila")->applyFromArray([
    'font' => ['bold' => true],
    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '99CCFF']],
    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
]);

foreach (range('A', 'E') as $col) {
    $sheet->getColumnDimension($col)->setAutoSize(true);
}

// ===================== INSERTAR PANTALLAZO AL FINAL =====================
$tempImage = null;
if (!empty($_POST['pantallazoDashboard'])) {
    $filaFinal = $fila + 2;
    $tempImage = insertarPantallazo($sheet, $_POST['pantallazoDashboard'], "A$filaFinal", 'Pantallazo_Dashboard', 550);
}

// ===================== EXPORTAR =====================
if (!empty($_POST['exportar_excel'])) {
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment; filename="reporte_transacciones.xlsx"');
    $writer = new Xlsx($spreadsheet);
    $writer->save('php://output');

    if ($tempImage && file_exists($tempImage)) unlink($tempImage);
    exit;
}

if (!empty($_POST['exportar_pdf'])) {
    IOFactory::registerWriter('Pdf', Mpdf::class);
    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="reporte_dashboard.pdf"');
    $writer = IOFactory::createWriter($spreadsheet, 'Pdf');
    $writer->save('php://output');

    if ($tempImage && file_exists($tempImage)) unlink($tempImage);
    exit;
}

// ===================== LIMPIEZA FINAL =====================
if ($tempImage && file_exists($tempImage)) {
    unlink($tempImage);
}

header('Location: ../registro.php');
exit;
?>
