<?php
session_start();
require_once 'conexion.php';

if (!isset($_SESSION['id']) || $_SESSION['id_rol'] != 2) {
    http_response_code(403);
    echo "Acceso no autorizado.";
    exit();
}

try {
    $conexion = Conexion::conectar();

    $sqlGastos = "SELECT c.nombre AS categoria, SUM(g.monto) AS total
                  FROM gastos g
                  JOIN categorias c ON g.id_categoria = c.id
                  GROUP BY c.nombre";

    $stmt = $conexion->prepare($sqlGastos);
    $stmt->execute();
    $datos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($datos);
} catch (PDOException $e) {
    echo json_encode(["error" => $e->getMessage()]);
}
?>
