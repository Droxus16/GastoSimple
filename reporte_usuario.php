<?php
require 'vendor/autoload.php';
use Dompdf\Dompdf;

require_once __DIR__ . '/conexion.php';

$conexion = Conexion::conectar();

if (!isset($_GET['id'])) {
    die("ID de usuario no proporcionado.");
}

$id = $_GET['id'];
$sql = "SELECT id, nombre, correo, moneda, idioma FROM usuarios WHERE id = :id";
$stmt = $conexion->prepare($sql);
$stmt->bindParam(':id', $id, PDO::PARAM_INT);
$stmt->execute();
$usuario = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$usuario) {
    die("Usuario no encontrado.");
}
$html = "
<html>
<head>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; }
        h1 { color: #1a73e8; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        td, th { padding: 10px; border: 1px solid #000; }
        th { background: #f2f2f2; }
    </style>
</head>
<body>
    <h1>Reporte de Usuario</h1>
    <table>
        <tr><th>ID</th><td>{$usuario['id']}</td></tr>
        <tr><th>Nombre</th><td>{$usuario['nombre']}</td></tr>
        <tr><th>Correo</th><td>{$usuario['correo']}</td></tr>
        <tr><th>Moneda</th><td>{$usuario['moneda']}</td></tr>
        <tr><th>Idioma</th><td>{$usuario['idioma']}</td></tr>
    </table>
</body>
</html>
";

$dompdf = new Dompdf();
$dompdf->loadHtml($html);

$dompdf->setPaper('A4', 'portrait');

$dompdf->render();

$dompdf->stream("reporte_usuario_{$id}.pdf", array("Attachment" => true));
?>