<?php
require_once 'includes/db.php';

// Datos del nuevo admin
$nombre = 'Administrador';
$correo = 'admin@gastosimple.com';
$contrasena = 'admin12345';

// Encriptar la contraseña
$hash = password_hash($contrasena, PASSWORD_DEFAULT);

// Crear el usuario admin
try {
    $db = DB::conectar();
    $stmt = $db->prepare("INSERT INTO usuarios (nombre, correo, clave, rol, created_at, updated_at, intentos_fallidos, bloqueado_hasta, pregunta_secreta, respuesta_secreta, ingreso_minimo, saldo_minimo)
        VALUES (?, ?, ?, 'admin', NOW(), NOW(), 0, NULL, NULL, NULL, 0.00, 0.00)");
    $stmt->execute([$nombre, $correo, $hash]);
    echo "Administrador creado correctamente.<br>";
    echo "Correo: $correo<br>";
    echo "Contraseña: $contrasena<br>";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>