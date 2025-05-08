<?php
require_once 'vendor/autoload.php';
use Dompdf\Dompdf;

require_once __DIR__ . '/conexion.php';

$conexion = Conexion::conectar();

$sql = "SELECT id, nombre, correo, moneda, idioma FROM usuarios";
$stmt = $conexion->prepare($sql);
$stmt->execute();
$resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Construir HTML
$html = "
<h1>Reporte Completo de Usuarios</h1>
<table border='1' cellpadding='5' cellspacing='0' style='width:100%; border-collapse: collapse;'>
    <thead>
        <tr><th>ID</th><th>Nombre</th><th>Correo</th><th>Moneda</th><th>Idioma</th></tr>
    </thead>
    <tbody>";

foreach ($resultados as $row) {
    $html .= "<tr>
                <td>{$row['id']}</td>
                <td>{$row['nombre']}</td>
                <td>{$row['correo']}</td>
                <td>{$row['moneda']}</td>
                <td>{$row['idioma']}</td>
              </tr>";
}

$html .= "</tbody></table>";

$dompdf = new Dompdf();
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();
$dompdf->stream("reporte_completo.pdf", ["Attachment" => false]); // true para descargar directamente
?>