<?php
session_start();
require_once 'includes/db.php';
require_once __DIR__ . '/vendor/autoload.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// --- Seguridad: solo admin ---
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'admin') {
  header('Location: login.php');
  exit();
}

// Generar token CSRF simple
if (empty($_SESSION['mass_token'])) {
  $_SESSION['mass_token'] = bin2hex(random_bytes(16));
}

// Manejo de AJAX (inicio de campaña / envío por lote)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
  header('Content-Type: application/json; charset=utf-8');

  // Validar token
  $token = $_POST['token'] ?? '';
  if (!hash_equals($_SESSION['mass_token'], $token)) {
    echo json_encode(['status' => 'error', 'message' => 'Token inválido']);
    exit();
  }

  $action = $_POST['action'];

  if ($action === 'start_campaign') {
    // Recoger datos
    $subject = trim($_POST['subject'] ?? '');
    $body = trim($_POST['body'] ?? '');
    $batch_size = max(1, intval($_POST['batch_size'] ?? 20));

    if (empty($subject) || empty($body)) {
      echo json_encode(['status' => 'error', 'message' => 'Asunto y mensaje son obligatorios.']);
      exit();
    }

    try {
      $db = db::conectar();
      $stmt = $db->query("SELECT nombre, correo FROM usuarios WHERE correo <> ''");
      $recipients = $stmt->fetchAll(PDO::FETCH_ASSOC);

      // Guardar en sesión (para procesamiento por lotes)
      $_SESSION['mass_recipients'] = $recipients;
      $_SESSION['mass_subject'] = $subject;
      $_SESSION['mass_body'] = $body;
      $_SESSION['mass_batch_size'] = $batch_size;

      echo json_encode(['status' => 'ok', 'total' => count($recipients), 'batch_size' => $batch_size]);
      exit();
    } catch (PDOException $e) {
      echo json_encode(['status' => 'error', 'message' => 'Error BD: ' . $e->getMessage()]);
      exit();
    }
  }

  if ($action === 'send_batch') {
    $offset = max(0, intval($_POST['offset'] ?? 0));
    $recipients = $_SESSION['mass_recipients'] ?? [];
    $total = count($recipients);
    $batch_size = intval($_SESSION['mass_batch_size'] ?? 20);

    if ($offset >= $total) {
      unset($_SESSION['mass_recipients'], $_SESSION['mass_subject'], $_SESSION['mass_body'], $_SESSION['mass_batch_size']);
      echo json_encode(['status' => 'done', 'sent' => 0, 'failed' => 0, 'offset' => $offset, 'total' => $total]);
      exit();
    }

    $slice = array_slice($recipients, $offset, $batch_size);

    // Config SMTP
    $smtpUser = getenv('SMTP_USER') ?: 'gastosimpleservice@gmail.com';
    $smtpPass = getenv('SMTP_PASSWORD') ?: 'iokwsgdexwwvorcu';

    $sent = 0; $failed = 0; $errors = [];

    $mail = new PHPMailer(true);
    try {
      $mail->isSMTP();
      $mail->Host = 'smtp.gmail.com';
      $mail->SMTPAuth = true;
      $mail->Username = $smtpUser;
      $mail->Password = $smtpPass;
      $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
      $mail->Port = 587;
      $mail->setFrom($smtpUser, 'GastoSimple');
      $mail->isHTML(true);
      $mail->SMTPKeepAlive = true;

      foreach ($slice as $r) {
        try {
          $mail->clearAddresses();
          $email = $r['correo'];
          $name = $r['nombre'] ?? '';

          $personal_body = str_replace(['{{name}}', '{{email}}'], [$name, $email], $_SESSION['mass_body']);

          $mail->addAddress($email, $name);
          $mail->Subject = $_SESSION['mass_subject'];
          $mail->Body = $personal_body;
          $mail->AltBody = trim(strip_tags(str_replace(['<br>', '<br/>', '<p>'], "\n", $personal_body)));

          $mail->send();
          $sent++;
        } catch (Exception $e) {
          $failed++;
          $errors[] = "{$email}: " . $e->getMessage();
        }
      }

      $mail->smtpClose();
    } catch (Exception $e) {
      echo json_encode(['status' => 'error', 'message' => 'Error SMTP: ' . $e->getMessage()]);
      exit();
    }

    $newOffset = $offset + count($slice);
    $done = $newOffset >= $total;
    if ($done) {
      unset($_SESSION['mass_recipients'], $_SESSION['mass_subject'], $_SESSION['mass_body'], $_SESSION['mass_batch_size']);
    }

    echo json_encode(['status' => 'ok', 'sent' => $sent, 'failed' => $failed, 'errors' => $errors, 'offset' => $newOffset, 'total' => $total, 'done' => $done]);
    exit();
  }

  echo json_encode(['status' => 'error', 'message' => 'Acción desconocida']);
  exit();
}
// ---------- HTML (interfaz admin) ----------
?>
<?php include 'includes/header.php'; ?>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<!-- Bootstrap 5 -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">
<style>
body {
  background: linear-gradient(135deg, #0B0B52, #1D2B64, #0C1634);
  background-size: 300% 300%;
  animation: backgroundAnim 25s ease-in-out infinite;
  color: white;
}
@keyframes backgroundAnim {
  0% { background-position: 0% 50%; }
  50% { background-position: 100% 50%; }
  100% { background-position: 0% 50%; }
}
.dashboard-container {
  display: flex;
  height: 100vh;
  padding: 20px;
  gap: 20px;
}
.sidebar {
  width: 220px;
  display: flex;
  flex-direction: column;
  justify-content: space-between;
  background: rgba(20,20,40,0.95);
  border-radius: 12px;
  padding: 15px;
}
.sidebar button {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 12px;
  font-size: 1rem;
  border: none;
  border-radius: 12px;
  background: transparent;
  color: #00D4FF;
  cursor: pointer;
  transition: all 0.25s ease;
}
.sidebar button:hover,
.sidebar button.activo {
  background: #00D4FF;
  color: #0C1634;
}
.main-content {
  flex: 1;
  background: rgba(255, 255, 255, 0.04);
  padding: 30px;
  border-radius: 20px;
  backdrop-filter: blur(8px);
  overflow-y: auto;
}
.modal-content {
  background: rgba(20,20,40,0.95);
  backdrop-filter: blur(12px);
  border-radius: 14px;
  border: 1px solid rgba(255,255,255,0.08);
  color: white;
  box-shadow: 0 10px 30px rgba(0,0,0,0.6);
}
.modal-header h5 { color: #00D4FF; font-weight: 700; }
.glass-input {
  background: rgba(255,255,255,0.05) !important;
  border: 1px solid rgba(255,255,255,0.15) !important;
  color: white !important;
  border-radius: 10px !important;
}
.glass-input:focus {
  background: rgba(0,212,255,0.08) !important;
  border-color: #00D4FF !important;
  box-shadow: 0 0 8px rgba(0,212,255,0.5) !important;
}
</style>

<div class="dashboard-container">
  <!-- Sidebar -->
  <div class="sidebar">
    <div>
      <button onclick="location.href='admin_dashboard.php'"><i class="bi bi-speedometer2"></i> Panel Admin</button>
      <button onclick="location.href='admin_reportes.php'"><i class="bi bi-bar-chart-fill"></i> Reportes Globales</button>
      <button onclick="location.href='admin_masivo.php'" class="activo"><i class="bi bi-envelope-fill"></i> Correos Masivos</button>
    </div>
    <div>
      <button onclick="location.href='logout.php'"><i class="bi bi-box-arrow-right"></i> Cerrar Sesión</button>
    </div>
  </div>

  <!-- Main Content -->
  <div class="main-content">
    <div class="modal-content p-4">
      <div class="modal-header border-0">
        <h5 class="modal-title">Envío Masivo de Correos</h5>
      </div>
      <div class="modal-body">
        <form id="massForm">
          <input type="hidden" name="token" value="<?= htmlspecialchars($_SESSION['mass_token']) ?>">

          <div class="mb-3">
            <label class="form-label">Asunto</label>
            <input class="form-control glass-input" name="subject" required placeholder="Asunto del correo">
          </div>

          <div class="mb-3">
            <label class="form-label">Mensaje</label>
            <textarea class="form-control glass-input" name="body" rows="8" required placeholder="Puedes usar {{name}} y {{email}}"></textarea>
          </div>

          <div class="row g-2">
            <div class="col-md-4 mb-3">
              <label class="form-label">Tamaño lote (batch)</label>
              <input class="form-control glass-input" type="number" name="batch_size" value="20" min="1">
            </div>
            <div class="col-md-8 d-flex align-items-end mb-3">
              <button type="submit" class="btn btn-success me-2">Iniciar envío</button>
              <button type="button" id="btnCancel" class="btn btn-secondary" disabled>❌ Cancelar</button>
            </div>
          </div>
        </form>
      </div>
      <div class="modal-footer flex-column align-items-start">
        <h6 style="color:#00D4FF;">Progreso</h6>
        <div class="progress w-100 mb-2">
          <div id="progressBar" class="progress-bar bg-info" role="progressbar" style="width:0%">0%</div>
        </div>
        <div id="logArea" style="max-height:260px; overflow:auto; white-space:pre-line; font-family: monospace; background:rgba(0,0,0,0.3); padding:10px; border-radius:10px; color:white;"></div>
      </div>
    </div>
  </div>
</div>

<script>
const form = document.getElementById('massForm');
const progressBar = document.getElementById('progressBar');
const logArea = document.getElementById('logArea');
const btnCancel = document.getElementById('btnCancel');
let cancelled = false;

form.addEventListener('submit', async function(e){
  e.preventDefault();
  cancelled = false;
  btnCancel.disabled = false;
  logArea.textContent = '';

  const formData = new FormData(form);
  formData.append('action', 'start_campaign');

  const startResp = await fetch('', { method: 'POST', body: formData });
  const startJson = await startResp.json();
  if (startJson.status !== 'ok') {
    alert('Error: ' + (startJson.message || 'No se pudo iniciar campaña'));
    btnCancel.disabled = true;
    return;
  }

  const total = startJson.total;
  const batchSize = startJson.batch_size;
  if (total === 0) {
    alert('No hay destinatarios.');
    btnCancel.disabled = true;
    return;
  }

  logArea.textContent += `Total destinatarios: ${total}\n`;

  let offset = 0;
  while (offset < total && !cancelled) {
    const batchForm = new FormData();
    batchForm.append('action', 'send_batch');
    batchForm.append('offset', offset);
    batchForm.append('token', form.querySelector('[name=token]').value);

    const batchResp = await fetch('', { method: 'POST', body: batchForm });
    const batchJson = await batchResp.json();

    if (batchJson.status === 'error') {
      logArea.textContent += 'Error en lote: ' + (batchJson.message || 'error desconocido') + '\n';
      break;
    }

    if (batchJson.status === 'done') break;

    logArea.textContent += `Lote enviado: +${batchJson.sent} (fallos: ${batchJson.failed})\n`;
    if (batchJson.errors?.length) {
      batchJson.errors.forEach(err => logArea.textContent += err + '\n');
    }

    offset = batchJson.offset;
    const percent = Math.round((offset / total) * 100);
    progressBar.style.width = percent + '%';
    progressBar.textContent = percent + '%';

    await new Promise(r => setTimeout(r, 300));
  }

  btnCancel.disabled = true;
  logArea.textContent += '\nProceso finalizado.';
});

btnCancel.addEventListener('click', function(){
  cancelled = true;
  btnCancel.disabled = true;
  logArea.textContent += '\nOperación cancelada por el usuario.';
});
</script>

<?php include 'includes/footer.php'; ?>
