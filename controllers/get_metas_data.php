<?php
include("conexion.php"); // tu conexión MySQL

header('Content-Type: application/json');

$response = [
    "ahorros" => [],
    "gastos" => [],
    "ingresos" => []
];

// 🔹 Función para obtener datos
function getData($conn, $table, $field = "monto", $limit = 10) {
    $data = [];
    $sql = "SELECT $field, fecha FROM $table ORDER BY fecha DESC LIMIT $limit";
    if ($result = $conn->query($sql)) {
        while ($row = $result->fetch_assoc()) {
            $data[] = (float)$row[$field];
        }
        $result->free();
    }
    // Invertir para mostrar de más antiguo a más reciente
    return array_reverse($data);
}

// 🔹 Obtener datos
$response["ahorros"]  = getData($conn, "metas_ahorros");
$response["gastos"]   = getData($conn, "gastos");
$response["ingresos"] = getData($conn, "ingresos");

// 🔹 Devolver JSON
echo json_encode($response);

$conn->close();
?>
