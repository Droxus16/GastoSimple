<?php
session_start();
require_once 'includes/db.php';

// --- PHPMailer ---
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require_once __DIR__ . '/vendor/autoload.php';

// Solo admin
if ($_SESSION['rol'] !== 'admin') {
  header("Location: dashboard.php");
  exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pqr_id = intval($_POST['pqr_id']);
    $respuesta = trim($_POST['respuesta']);

    if ($pqr_id && $respuesta) {
        try {
            $conn = db::conectar();

            // 1. Obtener correo y datos del usuario
            $stmt = $conn->prepare("
              SELECT p.asunto, u.nombre, u.correo 
              FROM pqrs p 
              JOIN usuarios u ON p.usuario_id = u.id
              WHERE p.id = ?
            ");
            $stmt->execute([$pqr_id]);
            $data = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$data) {
                die("No se encontró el PQR.");
            }

            $nombre = $data['nombre'];
            $correo = $data['correo'];
            $asunto = $data['asunto'];

            // 2. Actualizar en BD
            $stmt = $conn->prepare("UPDATE pqrs 
                                    SET respuesta=?, estado='atendido', respondido=1 
                                    WHERE id=?");
            $stmt->execute([$respuesta, $pqr_id]);

            // 3. Enviar correo al usuario (Opción 1: directo)
            $mail = new PHPMailer(true);
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = "gastosimpleservice@gmail.com"; // <-- tu correo
            $mail->Password   = "iokwsgdexwwvorcu";            // <-- tu app password
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;

            $mail->setFrom("gastosimpleservice@gmail.com", 'Soporte GastoSimple');
            $mail->addAddress($correo, $nombre);

            $mail->isHTML(true);
            $mail->Subject = "Respuesta a tu PQR - GastoSimple";
            $mail->Body = "
              <html>
              <body style='font-family: Arial, sans-serif; background-color: #f8f9fa; padding:20px;'>
                <div style='max-width:600px; margin:auto; background:#fff; border-radius:8px; padding:20px;'>
                  <h2 style='color:#007BFF;'>Hola $nombre,</h2>
                  <p>Hemos dado respuesta a tu PQR con asunto: <strong>$asunto</strong></p>
                  <p><strong>Respuesta del equipo de soporte:</strong></p>
                  <blockquote style='border-left:4px solid #007BFF; padding-left:10px; color:#333;'>
                    $respuesta
                  </blockquote>
                  <hr>
                  <p style='font-size:12px; color:#777;'>© ".date("Y")." GastoSimple - Equipo de soporte</p>
                </div>
              </body>
              </html>";

            $mail->AltBody = "Hola $nombre,\n\nHemos dado respuesta a tu PQR:\n\nAsunto: $asunto\n\nRespuesta: $respuesta\n\nEquipo de GastoSimple";

            $mail->send();

            // 4. Redirigir con mensaje
            header("Location: admin_dashboard.php?msg=respuesta_ok");
            exit;

        } catch (Exception $e) {
            die("Error en BD o correo: " . $e->getMessage());
        }
    }
}
?>
