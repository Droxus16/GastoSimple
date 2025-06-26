<?php
session_start();
require_once 'includes/db.php';
require_once 'includes/auth.php';

$conn = db::conectar();
$usuario_id = $_SESSION['usuario_id'];

// Consulta de metas y aportes
$metas = $conn->prepare("
    SELECT m.*, 
    (SELECT SUM(a.monto) FROM aportes_ahorro a WHERE a.meta_id = m.id) AS total_aportado
    FROM metas_ahorro m 
    WHERE m.usuario_id = ?
    ORDER BY m.fecha_limite ASC
");
$metas->execute([$usuario_id]);
$lista_metas = $metas->fetchAll(PDO::FETCH_ASSOC);

// Ahorros mensual y anual
$totales = $conn->prepare("
    SELECT
        (SELECT COALESCE(SUM(monto), 0) FROM ingresos 
         WHERE usuario_id = :usuario_id AND MONTH(fecha) = MONTH(CURDATE()) AND YEAR(fecha) = YEAR(CURDATE()))
      -
        (SELECT COALESCE(SUM(monto), 0) FROM gastos 
         WHERE usuario_id = :usuario_id AND MONTH(fecha) = MONTH(CURDATE()) AND YEAR(fecha) = YEAR(CURDATE()))
      AS total_mes,
        (SELECT COALESCE(SUM(monto), 0) FROM ingresos 
         WHERE usuario_id = :usuario_id AND YEAR(fecha) = YEAR(CURDATE()))
      -
        (SELECT COALESCE(SUM(monto), 0) FROM gastos 
         WHERE usuario_id = :usuario_id AND YEAR(fecha) = YEAR(CURDATE()))
      AS total_anual
");
$totales->execute(['usuario_id' => $usuario_id]);
$ahorro = $totales->fetch(PDO::FETCH_ASSOC);
?>

<?php include 'includes/header.php'; ?>
<link rel="stylesheet" href="assets/css/estilos.css">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/particles.js"></script>

<style>
body {
  margin: 0;
  background: linear-gradient(135deg, #0B0B52, #1D2B64, #0C1634);
  background-size: 300% 300%;
  animation: backgroundAnim 25s ease-in-out infinite;
  color: white;
  font-family: 'Inter', sans-serif;
  overflow: hidden;
}

@keyframes backgroundAnim {
  0% { background-position: 0% 50%; }
  50% { background-position: 100% 50%; }
  100% { background-position: 0% 50%; }
}

#particles-js {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  z-index: -1;
}

.dashboard-container {
  display: flex;
  height: 100vh;
  gap: 20px;
  padding: 20px;
  box-sizing: border-box;
}

.sidebar {
  width: 220px;
  display: flex;
  flex-direction: column;
  justify-content: space-between;
}

.sidebar button {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 12px;
  font-size: 1rem;
  border: none;
  border-radius: 12px;
  background: rgba(255, 255, 255, 0.08);
  color: #00D4FF;
  font-weight: bold;
  cursor: pointer;
  transition: all 0.3s ease;
  backdrop-filter: blur(6px);
}

.sidebar button:hover {
  background-color: #00D4FF;
  color: #0C1634;
  transform: scale(1.05);
}

.main-content {
  flex: 1;
  background: rgba(255, 255, 255, 0.05);
  padding: 25px;
  border-radius: 20px;
  backdrop-filter: blur(10px);
  color: white;
  overflow-y: auto;
  box-sizing: border-box;
}

.card-valor {
  flex: 1;
  background: rgba(255, 255, 255, 0.15);
  padding: 20px;
  border-radius: 15px;
  backdrop-filter: blur(8px);
  text-align: center;
}

.formulario-meta {
  background: rgba(255, 255, 255, 0.1);
  padding: 20px;
  border-radius: 10px;
  margin-bottom: 30px;
  display: flex;
  flex-direction: column;
  gap: 10px;
}

.formulario-meta input,
.formulario-meta button {
  padding: 12px;
  border-radius: 8px;
  border: none;
  font-size: 1rem;
}

.formulario-meta input {
  background: rgba(255, 255, 255, 0.2);
  color: white;
}

.formulario-meta button {
  background-color: #00D4FF;
  color: #0C1634;
  font-weight: bold;
  cursor: pointer;
  transition: transform 0.3s;
}

.formulario-meta button:hover {
  transform: scale(1.05);
}

table {
  width: 100%;
  border-collapse: collapse;
  background: rgba(255, 255, 255, 0.05);
  backdrop-filter: blur(6px);
  border-radius: 12px;
  overflow: hidden;
}

th, td {
  padding: 12px;
  border: 1px solid rgba(255, 255, 255, 0.1);
  text-align: center;
}

.barra-progreso {
  background: #444;
  border-radius: 5px;
  overflow: hidden;
}

.barra-progreso > div {
  background: #00D4FF;
  color: #0C1634;
  padding: 5px;
  text-align: center;
}

@media screen and (max-width: 768px) {
  .dashboard-container {
    flex-direction: column;
  }

  .sidebar {
    width: 100%;
    flex-direction: row;
    justify-content: space-around;
  }

  .resumen-ahorro {
    flex-direction: column;
  }
}
</style>

<div id="particles-js"></div>

<div class="dashboard-container">
  <div class="sidebar">
    <div class="menu-top">
      <button onclick="location.href='dashboard.php'"><i class="bi bi-pie-chart-fill"></i> Panel</button>
      <button onclick="location.href='registro.php'"><i class="bi bi-pencil-square"></i> Registro</button>
      <button onclick="location.href='metas.php'" class="activo"><i class="bi bi-flag-fill"></i> Metas</button>
    </div>
    <div class="menu-bottom">
      <button onclick="location.href='logout.php'"><i class="bi bi-box-arrow-right"></i> Salir</button>
      <button onclick="location.href='ajustes.php'"><i class="bi bi-gear-fill"></i> Ajustes</button>
    </div>
  </div>

  <div class="main-content">
    <h2>Metas de Ahorro</h2>

    <div class="resumen-ahorro" style="display: flex; gap: 20px; margin-bottom: 25px;">
      <div class="card-valor">
        <h4>Total Ahorro del Mes</h4>
        <p style="font-size: 1.5em; color: <?= $ahorro['total_mes'] < 0 ? '#FF6B6B' : '#00FFCC' ?>;">
          $<?= number_format($ahorro['total_mes'] ?? 0, 2, ',', '.') ?>
        </p>
      </div>
      <div class="card-valor">
        <h4>Total Ahorro Anual</h4>
        <p style="font-size: 1.5em; color: <?= $ahorro['total_anual'] < 0 ? '#FF6B6B' : '#00FFCC' ?>;">
          $<?= number_format($ahorro['total_anual'] ?? 0, 2, ',', '.') ?>
        </p>
      </div>
    </div>

    <form action="crear_meta.php" method="POST" class="formulario-meta">
      <h4>Crear Nueva Meta de Ahorro</h4>
      <input type="text" name="nombre" placeholder="Nombre de la meta" required>
      <input type="number" name="monto_objetivo" placeholder="Monto objetivo" required step="0.01">
      <input type="date" name="fecha_limite" required>
      <button type="submit">Guardar Meta</button>
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
            <div class="barra-progreso">
              <div style="width: <?= $porcentaje ?>%;"><?= round($porcentaje) ?>%</div>
            </div>
          </td>
          <td><?= $meta['fecha_limite'] ?></td>
          <td>
            <form action="aportar.php" method="POST" style="display: flex; flex-direction: column; gap: 5px;">
              <input type="hidden" name="meta_id" value="<?= $meta['id'] ?>">
              <input type="number" name="monto" placeholder="$0.00" step="0.01" required>
              <button type="submit" style="background: #00D4FF; border: none; border-radius: 6px; padding: 6px 10px; font-weight: bold; cursor: pointer;">Aportar</button>
            </form>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
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
