<?php
session_start();
require_once 'db.php';
require_once 'auth.php';
require '../../vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\IOFactory;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['archivo_excel'])) {
    $archivo = $_FILES['archivo_excel']['tmp_name'];
    $spreadsheet = IOFactory::load($archivo);
    $hoja = $spreadsheet->getActiveSheet();
    $filas = $hoja->toArray();
    $conn = db::conectar();
    $idUsuario = $_SESSION['usuario_id'];
    // Asume que la primera fila son los encabezados
    for ($i = 1; $i < count($filas); $i++) {
        $fila = $filas[$i];
        $tipo = strtolower(trim($fila[0])); // "ingreso" o "gasto"
        $fecha = $fila[1];
        $monto = floatval($fila[2]);
        $categoria = trim($fila[3]);
        $descripcion = trim($fila[4]);

        $stmt = $conn->prepare("INSERT INTO transacciones (id_usuario, tipo, fecha, monto, categoria, descripcion)
                                VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$idUsuario, $tipo, $fecha, $monto, $categoria, $descripcion]);
    }
    header('Location: ../registro.php');
    exit();
} else {
    echo "Archivo no válido.";
}
?>