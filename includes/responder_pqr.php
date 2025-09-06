<?php
session_start();
require_once 'includes/db.php';
require_once 'includes/auth.php';

if ($_SESSION['rol'] !== 'admin') {
  header("Location: dashboard.php");
  exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $id = $_POST['pqr_id'];
  $respuesta = $_POST['respuesta'];

  $conn = db::conectar();

  $stmt = $conn->prepare("UPDATE pqrs SET respuesta = ?, respondido = 1 WHERE id = ?");
  $stmt->execute([$respuesta, $id]);

  // enviar correo al usuario
  // Obtener correo del usuario
  /*
  $stmtUser = $conn->prepare("SELECT u.correo FROM pqrs p JOIN usuarios u ON p.usuario_id = u.id WHERE p.id = ?");
  $stmtUser->execute([$id]);
  $usuario = $stmtUser->fetch();
  if ($usuario) {
    mail($usuario['correo'], "Respuesta a su PQR", $respuesta);
  }
  */

  header("Location: admin_dashboard.php?respuesta=ok");
  exit;
}
?>