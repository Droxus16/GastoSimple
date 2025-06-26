<?php
session_start();
require_once 'includes/db.php';
require_once 'includes/auth.php';

$conn = db::conectar();
$usuario_id = $_SESSION['usuario_id'];

// Consulta de metas y aportes por meta
$metas = $conn->prepare("
    SELECT m.*, 
    (SELECT SUM(a.monto) FROM aportes_ahorro a WHERE a.meta_id = m.id) AS total_aportado
    FROM metas_ahorro m 
    WHERE m.usuario_id = ?
    ORDER BY m.fecha_limite ASC
");
$metas->execute([$usuario_id]);
$lista_metas = $metas->fetchAll(PDO::FETCH_ASSOC);

// Consulta para total ahorrado del mes y del año completo (permitiendo negativos)
// Consulta para total ahorrado real (ingresos - gastos), mensual y anual
$totales = $conn->prepare("
    SELECT
        (SELECT COALESCE(SUM(monto), 0) FROM ingresos 
         WHERE usuario_id = :usuario_id 
         AND MONTH(fecha) = MONTH(CURDATE()) AND YEAR(fecha) = YEAR(CURDATE()))
      -
        (SELECT COALESCE(SUM(monto), 0) FROM gastos 
         WHERE usuario_id = :usuario_id 
         AND MONTH(fecha) = MONTH(CURDATE()) AND YEAR(fecha) = YEAR(CURDATE()))
      AS total_mes,
      
        (SELECT COALESCE(SUM(monto), 0) FROM ingresos 
         WHERE usuario_id = :usuario_id 
         AND YEAR(fecha) = YEAR(CURDATE()))
      -
        (SELECT COALESCE(SUM(monto), 0) FROM gastos 
         WHERE usuario_id = :usuario_id 
         AND YEAR(fecha) = YEAR(CURDATE()))
      AS total_anual
");
$totales->execute(['usuario_id' => $usuario_id]);
$ahorro = $totales->fetch(PDO::FETCH_ASSOC);

?>

<?php include 'includes/header.php'; ?>
<link rel="stylesheet" href="assets/css/estilos.css">
<script src="https://cdn.jsdelivr.net/npm/particles.js"></script>

<style>
  body {
    margin: 0;
    background: linear-gradient(135deg, #0B0B52, #1D2B64, #0C1634);
    background-size: 300% 300%;
    animation: backgroundAnim 25s ease-in-out infinite;
    color: white;
  }

  #particles-js {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    z-index: -1;
  }

  .main-content {
    padding: 30px;
    max-width: 1200px;
    margin: auto;
  }

  table {
    width: 100%;
    border-collapse: collapse;
  }

  th, td {
    padding: 10px;
    border: 1px solid rgba(255, 255, 255, 0.2);
    text-align: center;
    background-color: rgba(0, 0, 0, 0.3);
  }

  @keyframes backgroundAnim {
    0% { background-position: 0% 50%; }
    50% { background-position: 100% 50%; }
    100% { background-position: 0% 50%; }
  }

  @media screen and (max-width: 768px) {
    .resumen-ahorro {
      flex-direction: column;
    }
  }
</style>

<div id="particles-js"></div>

<div class="main-content">
  <a href="dashboard.php">
    <button style="padding:10px 20px; background-color:#00D4FF; border:none; border-radius:8px; font-weight:bold;">&larr; Volver al Dashboard</button>
  </a>

  <h2>Metas de Ahorro</h2>

  <div class="resumen-ahorro" style="display: flex; gap: 20px; margin-bottom: 25px;">
    <div style="flex:1; background: rgba(0,255,255,0.1); padding: 15px; border-radius: 12px;">
      <h4>Total Ahorro del Mes</h4>
      <p style="font-size: 1.5em; color: <?= $ahorro['total_mes'] < 0 ? '#ff6b6b' : '#00ffcc' ?>;">
        $<?= number_format($ahorro['total_mes'] ?? 0, 2, ',', '.') ?>
      </p>
    </div>
    <div style="flex:1; background: rgba(0,255,255,0.1); padding: 15px; border-radius: 12px;">
      <h4>Total Ahorro Anual</h4>
      <p style="font-size: 1.5em; color: <?= $ahorro['total_anual'] < 0 ? '#ff6b6b' : '#00ffcc' ?>;">
        $<?= number_format($ahorro['total_anual'] ?? 0, 2, ',', '.') ?>
      </p>
    </div>
  </div>

  <form action="crear_meta.php" method="POST" style="margin-bottom: 30px; background-color: rgba(255,255,255,0.1); padding: 20px; border-radius: 10px;">
    <h4>Crear Nueva Meta de Ahorro</h4>
    <input type="text" name="nombre" placeholder="Nombre de la meta" required style="width:100%; padding:10px; margin:10px 0;">
    <input type="number" name="monto_objetivo" placeholder="Monto objetivo" required step="0.01" style="width:100%; padding:10px; margin:10px 0;">
    <input type="date" name="fecha_limite" required style="width:100%; padding:10px; margin:10px 0;">
    <button type="submit" style="padding: 10px 20px; background: #00D4FF; border: none; border-radius: 5px;">Guardar Meta</button>
  </form>

  <table>
    <thead>
      <tr>
        <th>Meta</th>
        <th>Objetivo</th>
        <th>Aportado</th>
        <th>Progreso</th>
        <th>Fecha Límite</th>
        <th>Acciones</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($lista_metas as $meta): 
        $aportado = floatval($meta['total_aportado'] ?? 0);
        $porcentaje = $meta['monto_objetivo'] > 0 ? min(100, ($aportado / $meta['monto_objetivo']) * 100) : 0;
      ?>
        <tr>
          <td><?= htmlspecialchars($meta['nombre']) ?></td>
          <td>$<?= number_format($meta['monto_objetivo'], 2, ',', '.') ?></td>
          <td>$<?= number_format($aportado, 2, ',', '.') ?></td>
          <td>
            <div style="width: 100%; background: #444; border-radius: 5px;">
              <div style="width: <?= $porcentaje ?>%; background: #00D4FF; padding: 5px; border-radius: 5px; text-align: center;">
                <?= round($porcentaje) ?>%
              </div>
            </div>
          </td>
          <td><?= $meta['fecha_limite'] ?></td>
          <td>
            <form action="aportar.php" method="POST" style="display:inline;">
              <input type="hidden" name="meta_id" value="<?= $meta['id'] ?>">
              <input type="number" name="monto" placeholder="$0.00" step="0.01" required style="width: 80px;">
              <button type="submit">Aportar</button>
            </form>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>

<script>
  particlesJS('particles-js', {
    particles: {
      number: { value: 80, density: { enable: true, value_area: 800 } },
      color: { value: "#00D4FF" },
      shape: { type: "circle" },
      opacity: { value: 0.5, random: true },
      size: { value: 3, random: true },
      line_linked: { enable: true, distance: 150, color: "#00D4FF", opacity: 0.4, width: 1 },
      move: { enable: true, speed: 6 }
    },
    interactivity: {
      events: {
        onhover: { enable: true, mode: "repulse" },
        onclick: { enable: true, mode: "push" }
      }
    },
    retina_detect: true
  });
</script>

<?php include 'includes/footer.php'; ?>
