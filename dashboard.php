<?php
session_start();
require_once 'includes/db.php';
require_once 'includes/auth.php';
$conn = DB::conectar();

$idUsuario = $_SESSION['usuario_id'] ?? null;
if (!$idUsuario) {
  header("Location: login.php");
  exit;
}

// === Datos básicos del usuario ===
$stmt = $conn->prepare("SELECT nombre, ingreso_minimo, saldo_minimo 
                        FROM usuarios 
                        WHERE id = ?");
$stmt->execute([$idUsuario]);
$usuario = $stmt->fetch(PDO::FETCH_ASSOC);
$nombreUsuario = $usuario ? htmlspecialchars($usuario['nombre']) : "Usuario";

// === Totales del mes actual ===
$totales = $conn->prepare("
  SELECT
    (SELECT COALESCE(SUM(monto),0) 
     FROM ingresos 
     WHERE usuario_id = :usuario_id 
       AND MONTH(fecha) = MONTH(CURDATE()) 
       AND YEAR(fecha) = YEAR(CURDATE())) AS total_ingresos,

    (SELECT COALESCE(SUM(monto),0) 
     FROM gastos 
     WHERE usuario_id = :usuario_id 
       AND MONTH(fecha) = MONTH(CURDATE()) 
       AND YEAR(fecha) = YEAR(CURDATE())) AS total_gastos,

    (SELECT COALESCE(SUM(monto),0) 
     FROM aportes_ahorro a 
     JOIN metas_ahorro m ON a.meta_id = m.id 
     WHERE m.usuario_id = :usuario_id 
       AND MONTH(a.fecha) = MONTH(CURDATE()) 
       AND YEAR(a.fecha) = YEAR(CURDATE())) AS total_aportes
");
$totales->execute(['usuario_id' => $idUsuario]);
$datos = $totales->fetch(PDO::FETCH_ASSOC);

$totalIngresos = $datos['total_ingresos'] ?? 0;
$totalGastos   = $datos['total_gastos'] ?? 0;
$totalAportes  = $datos['total_aportes'] ?? 0;
$saldoActual   = $totalIngresos - $totalGastos - $totalAportes;

// === Metas de ahorro ===
$stmtMetas = $conn->prepare("
  SELECT nombre, fecha_limite, monto_objetivo, 
         (SELECT COALESCE(SUM(monto),0) 
          FROM aportes_ahorro 
          WHERE meta_id = metas_ahorro.id) AS total_aportado 
  FROM metas_ahorro 
  WHERE usuario_id = ?
");
$stmtMetas->execute([$idUsuario]);
$lista_metas = $stmtMetas->fetchAll(PDO::FETCH_ASSOC);

// === Por día (últimos 7 días) ===
$stmtDia = $conn->prepare("
  SELECT DATE(fecha) AS periodo,
         SUM(CASE WHEN tipo='ingreso' THEN monto ELSE 0 END) AS ingresos,
         SUM(CASE WHEN tipo='gasto' THEN monto ELSE 0 END) AS gastos,
         SUM(CASE WHEN tipo='ahorro' THEN monto ELSE 0 END) AS ahorro
  FROM (
    SELECT fecha, monto, 'ingreso' AS tipo FROM ingresos WHERE usuario_id = :id
    UNION ALL
    SELECT fecha, monto, 'gasto' AS tipo FROM gastos WHERE usuario_id = :id
    UNION ALL
    SELECT a.fecha, a.monto, 'ahorro' AS tipo 
    FROM aportes_ahorro a 
    JOIN metas_ahorro m ON a.meta_id = m.id 
    WHERE m.usuario_id = :id
  ) t
  WHERE fecha >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
  GROUP BY periodo
  ORDER BY periodo
");
$stmtDia->execute(['id'=>$idUsuario]);
$dataDia = $stmtDia->fetchAll(PDO::FETCH_ASSOC);

// === Por semana (últimas 6 semanas) ===
$stmtSemana = $conn->prepare("
  SELECT YEARWEEK(fecha, 1) AS periodo,
         SUM(CASE WHEN tipo='ingreso' THEN monto ELSE 0 END) AS ingresos,
         SUM(CASE WHEN tipo='gasto' THEN monto ELSE 0 END) AS gastos,
         SUM(CASE WHEN tipo='ahorro' THEN monto ELSE 0 END) AS ahorro
  FROM (
    SELECT fecha, monto, 'ingreso' AS tipo FROM ingresos WHERE usuario_id = :id
    UNION ALL
    SELECT fecha, monto, 'gasto' AS tipo FROM gastos WHERE usuario_id = :id
    UNION ALL
    SELECT a.fecha, a.monto, 'ahorro' AS tipo 
    FROM aportes_ahorro a 
    JOIN metas_ahorro m ON a.meta_id = m.id 
    WHERE m.usuario_id = :id
  ) t
  WHERE fecha >= DATE_SUB(CURDATE(), INTERVAL 6 WEEK)
  GROUP BY periodo
  ORDER BY periodo
");
$stmtSemana->execute(['id'=>$idUsuario]);
$dataSemana = $stmtSemana->fetchAll(PDO::FETCH_ASSOC);

// === Por mes (últimos 12 meses) ===
$stmtMes = $conn->prepare("
  SELECT DATE_FORMAT(fecha, '%Y-%m') AS periodo,
         SUM(CASE WHEN tipo='ingreso' THEN monto ELSE 0 END) AS ingresos,
         SUM(CASE WHEN tipo='gasto' THEN monto ELSE 0 END) AS gastos,
         SUM(CASE WHEN tipo='ahorro' THEN monto ELSE 0 END) AS ahorro
  FROM (
    SELECT fecha, monto, 'ingreso' AS tipo FROM ingresos WHERE usuario_id = :id
    UNION ALL
    SELECT fecha, monto, 'gasto' AS tipo FROM gastos WHERE usuario_id = :id
    UNION ALL
    SELECT a.fecha, a.monto, 'ahorro' AS tipo 
    FROM aportes_ahorro a 
    JOIN metas_ahorro m ON a.meta_id = m.id 
    WHERE m.usuario_id = :id
  ) t
  WHERE fecha >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)
  GROUP BY periodo
  ORDER BY periodo
");
$stmtMes->execute(['id'=>$idUsuario]);
$dataMes = $stmtMes->fetchAll(PDO::FETCH_ASSOC);

// === Por año (últimos 5 años) ===
$stmtAnio = $conn->prepare("
  SELECT YEAR(fecha) AS periodo,
         SUM(CASE WHEN tipo='ingreso' THEN monto ELSE 0 END) AS ingresos,
         SUM(CASE WHEN tipo='gasto' THEN monto ELSE 0 END) AS gastos,
         SUM(CASE WHEN tipo='ahorro' THEN monto ELSE 0 END) AS ahorro
  FROM (
    SELECT fecha, monto, 'ingreso' AS tipo FROM ingresos WHERE usuario_id = :id
    UNION ALL
    SELECT fecha, monto, 'gasto' AS tipo FROM gastos WHERE usuario_id = :id
    UNION ALL
    SELECT a.fecha, a.monto, 'ahorro' AS tipo 
    FROM aportes_ahorro a 
    JOIN metas_ahorro m ON a.meta_id = m.id 
    WHERE m.usuario_id = :id
  ) t
  WHERE fecha BETWEEN DATE_SUB(CURDATE(), INTERVAL 5 YEAR) 
                  AND DATE_ADD(CURDATE(), INTERVAL 5 YEAR)
  GROUP BY periodo
  ORDER BY periodo
");
$stmtAnio->execute(['id'=>$idUsuario]);
$dataAnio = $stmtAnio->fetchAll(PDO::FETCH_ASSOC);

// === Exportar datos a JS ===
?>
<script>
  const nombreUsuario = "<?= $nombreUsuario ?>";
  const totalesMes = {
    ingresos: <?= $totalIngresos ?>,
    gastos: <?= $totalGastos ?>,
    aportes: <?= $totalAportes ?>,
    saldo: <?= $saldoActual ?>
  };

  const dataDia    = <?= json_encode($dataDia) ?>;
  const dataSemana = <?= json_encode($dataSemana) ?>;
  const dataMes    = <?= json_encode($dataMes) ?>;
  const dataAnio   = <?= json_encode($dataAnio) ?>;
</script>
<script>
  // Función genérica para preparar datos según el tipo de gráfico
function prepararDatos(dataset, tipo) {
  const labels = dataset.map(d => d.periodo);

  if (tipo === "ingresosGastos") {
    return {
      labels,
      datasets: [
        {
          label: "Ingresos",
          data: dataset.map(d => parseFloat(d.ingresos) || 0),
          borderColor: "rgba(0, 255, 0, 0.8)",
          backgroundColor: "rgba(0, 255, 0, 0.3)",
          fill: true
        },
        {
          label: "Gastos",
          data: dataset.map(d => parseFloat(d.gastos) || 0),
          borderColor: "rgba(255, 0, 0, 0.8)",
          backgroundColor: "rgba(255, 0, 0, 0.3)",
          fill: true
        }
      ]
    };
  }

  if (tipo === "ahorro") {
    // Calcular evolución del ahorro: ingresos - gastos - ahorro
    let acumulado = 0;
    const valores = dataset.map(d => {
      acumulado += (parseFloat(d.ingresos) || 0) - (parseFloat(d.gastos) || 0) - (parseFloat(d.ahorro) || 0);
      return acumulado;
    });

    return {
      labels,
      datasets: [
        {
          label: "Ahorro acumulado",
          data: valores,
          borderColor: "rgba(0, 200, 255, 0.9)",
          backgroundColor: "rgba(0, 200, 255, 0.3)",
          tension: 0.3,
          fill: true,
          pointBackgroundColor: "#fff"
        }
      ]
    };
  }

  return { labels: [], datasets: [] };
}
</script>
<?php include 'includes/header.php'; ?>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/gridstack@8.2.1/dist/gridstack.min.css">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/gridstack@8.2.1/dist/gridstack-all.js"></script>
<script src="https://cdn.jsdelivr.net/npm/particles.js"></script>
<style>
      /* ===== Fondo animado ===== */
    body {
      position: relative;
      width: 100%;
      height: 100%;
      background: linear-gradient(135deg, #0B0B52, #1D2B64, #0C1634, #0B0B52);
      background-size: 300% 300%;
      animation: backgroundAnim 25s ease-in-out infinite;
      z-index: -2;
      overflow: hidden;
      color: white;
      font-family: 'Segoe UI', sans-serif;
    }
    html, body {
      height: 100%;
      margin: 0;
      overflow: hidden; /* evita doble scroll */
    }
    @keyframes backgroundAnim {
      0% { background-position: 0% 50%; }
      50% { background-position: 100% 50%; }
      100% { background-position: 0% 50%; }
    }
    #particles-js {
      position: absolute;
      top: 0; left: 0;
      width: 100%; height: 100%;
      z-index: -1;
    }

    /* ===== Layout ===== */
    .dashboard-container {
      display: flex;
      height: 100vh;
      gap: 20px;
      padding: 20px;
      box-sizing: border-box;
    }

    /* ===== Sidebar ===== */
    .sidebar {
      width: 240px; /* un poquito más ancho */
      display: flex;
      flex-direction: column;
      background: rgba(255, 255, 255, 0.05);
      border-radius: 20px;
      padding: 12px 0;
      transition: width 0.3s ease-in-out;
      overflow: hidden;
      box-shadow: 0 8px 32px rgba(0,0,0,0.25);
    }

    /* Sidebar colapsada */
    .sidebar.collapsed {
      width: 120px;
    }

    /* Botón hamburguesa */
    .sidebar .hamburger {
      align-self: flex-start; /* ahora a la izquierda */
      margin: 10px 16px;
      font-size: 2rem; /* más grande */
      background: transparent;
      border: none;
      color: #00D4FF;
      cursor: pointer;
      transition: transform 0.3s ease, color 0.3s ease;
    }
    .sidebar .hamburger:hover {
      transform: rotate(90deg);
      color: #fff;
    }

    /* Contenedor del menú */
    .menu-content {
      display: flex;
      flex-direction: column;
      flex: 1;
      gap: 12px;
      padding: 12px;
    }

    /* Botones uniformes */
    .sidebar button {
      display: flex;
      align-items: center;
      gap: 16px;
      padding: 16px 18px; /* más grandes */
      font-size: 1.1rem;  /* fuente más legible */
      border: none;
      border-radius: 14px;
      background: transparent;
      color: #e0f7fa;
      font-weight: 500;
      cursor: pointer;
      transition: 
        background 0.2s, 
        color 0.2s, 
        transform 0.2s;
      overflow: hidden;
      text-align: left;
    }

    /* Hover de botones */
    .sidebar button:hover {
      background: rgba(0, 212, 255, 0.2);
      color: #fff;
      transform: translateY(-2px);
    }

    /* Íconos */
    .sidebar button i {
      font-size: 1.5em; /* más grandes */
      color: #00D4FF;
      flex-shrink: 0;
      transition: color 0.2s;
    }
    .sidebar button:hover i {
      color: #fff;
    }

    /* Etiquetas de texto */
    .sidebar button .label {
      transition: opacity 0.3s ease, transform 0.3s ease;
      white-space: nowrap;
    }
    .sidebar.collapsed .label {
      opacity: 0;
      transform: translateX(-15px);
      pointer-events: none;
    }
    /* Contenedor del menú inferior */
    .menu-bottom {
      margin-top: auto; /* se pega abajo */
      display: flex;
      flex-direction: column;
      gap: 12px;
      padding: 6px;
    }

    /* Botón Ajustes (azul elegante) */
    .menu-bottom button.ajustes {
      background: rgba(0, 212, 255, 0.15);
      border: 1px solid rgba(0, 212, 255, 0.3);
      color: #fff;
      font-weight: 600;
      padding: 14px 18px;
      border-radius: 14px;
      font-size: 1.05rem;
      box-shadow: 0 4px 16px rgba(0, 212, 255, 0.25);
      transition: all 0.25s ease;
    }
    .menu-bottom button.ajustes:hover {
      background: #00d4ff;
      color: #111;
      transform: translateY(-2px);
      box-shadow: 0 0 12px #00d4ff, 0 0 24px rgba(0, 212, 255, 0.6);
    }
    .menu-bottom button.ajustes i {
      color: #00d4ff;
      font-size: 1.4em;
      transition: color 0.25s;
    }
    .menu-bottom button.ajustes:hover i {
      color: #111;
    }

    /* Botón Salir (rojo crítico) */
    .menu-bottom button.salir {
      background: rgba(255, 77, 77, 0.15);
      border: 1px solid rgba(255, 77, 77, 0.3);
      color: #fff;
      font-weight: 600;
      padding: 14px 18px;
      border-radius: 14px;
      font-size: 1.05rem;
      box-shadow: 0 4px 16px rgba(255, 77, 77, 0.25);
      transition: all 0.25s ease;
    }
    .menu-bottom button.salir:hover {
      background: #ff4d4d;
      color: #111;
      transform: translateY(-2px);
      box-shadow: 0 0 12px #ff4d4d, 0 0 24px rgba(255, 77, 77, 0.6);
    }
    .menu-bottom button.salir i {
      color: #ff4d4d;
      font-size: 1.4em;
      transition: color 0.25s;
    }
    .menu-bottom button.salir:hover i {
      color: #111;
    }

    /* ===== Notificaciones ===== */
    .notificaciones-dropdown {
      position: absolute;
      top: 70px; /* alineado debajo del hamburguesa */
      left: 250px; /* al lado de la sidebar */
      width: 280px;
      background: rgba(255, 255, 255, 0.1);
      border-radius: 14px;
      backdrop-filter: blur(14px);
      color: white;
      display: none;
      flex-direction: column;
      padding: 18px 20px;
      z-index: 999;
      box-shadow: 0 8px 32px rgba(0, 0, 0, 0.25);
    }
    .notificaciones-dropdown h4 {
      margin: 0 0 12px;
      font-size: 1.2rem; /* más grande */
      border-bottom: 1px solid #00D4FF;
      padding-bottom: 6px;
    }
    .notificaciones-dropdown li {
      padding: 8px 0;
      border-bottom: 1px solid rgba(255, 255, 255, 0.1);
      font-size: 1rem;
    }


    /* ===== Contenido principal ===== */
    .main-content {
      flex: 1;
      display: flex;
      flex-direction: column;
      background: rgba(255, 255, 255, 0.05);
      padding: 25px;
      border-radius: 20px;
      backdrop-filter: blur(10px);
      overflow: hidden;
      box-sizing: border-box;
      box-shadow: 0 8px 32px rgba(0,0,0,0.25);
    }
    .main-content {
      height: 100%;
      overflow-y: auto; /* scroll vertical global */
      padding: 1rem;
    }

    /* ===== Gridstack items ===== */
    .grid-stack {
      flex: 1;
      overflow-y: auto;   /* 🔹 scroll vertical */
      max-height: 90vh;
      width: 100%;
      height: 100%;
      box-sizing: border-box;
      padding: 20px;
      min-height: 100%;
    }

    .grid-stack-item {
      min-height: 200px;   /* un poco más grande */
    }
    .grid-stack-item-content {
      position: relative;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: flex-start;
      background: rgba(255, 255, 255, 0.1);
      backdrop-filter: blur(8px);
      border-radius: 12px;
      padding: 25px;
      box-shadow: 0 8px 32px rgba(0, 0, 0, 0.25);
      text-align: center;
      transition: transform 0.2s ease-in-out;
      overflow: hidden;
    }
    .grid-stack-item-content:hover {
      transform: scale(1.01);
    }
    .grid-stack-item-content canvas {
      display: block;
      width: 100% !important;
      height: 100% !important;
      max-height: 300px;
      margin: 0 auto;
    }

    /* ===== Chart filters ===== */
    .chart-filters {
      display: flex;
      justify-content: center;
      gap: 10px;
      flex-wrap: wrap;
      margin-bottom: 20px;
    }
    .chart-filters button {
      padding: 8px 14px;
      border-radius: 8px;
      border: 1px solid #00D4FF;
      background-color: #ffffff22;
      color: white;
      font-weight: bold;
      cursor: pointer;
      transition: all 0.3s;
    }
    .chart-filters button:hover {
      background-color: #ffffff44;
    }
    .chart-filters button.activo {
      background-color: #00D4FF;
      color: #0C1634;
      box-shadow: 0 0 8px rgba(0, 212, 255, 0.7);
    }
    .sin-datos {
      opacity: 0.4;
      filter: grayscale(50%);
    }
    @keyframes parpadeo {
      0% { opacity: 1; }
      50% { opacity: 0.3; }
      100% { opacity: 1; }
    }

    .parpadeo {
      animation: parpadeo 1s infinite;
    }

    /* ===== Notificaciones ===== */
    .notificaciones-dropdown {
      position: absolute;
      top: 60px; /* alineado con botones */
      left: 80px; /* para que no se tape con sidebar colapsada */
      width: 260px;
      background: rgba(255, 255, 255, 0.1);
      border-radius: 12px;
      backdrop-filter: blur(12px);
      color: white;
      display: none;
      flex-direction: column;
      padding: 15px 20px;
      z-index: 999;
      box-shadow: 0 8px 32px rgba(0, 0, 0, 0.25);
    }
    .notificaciones-dropdown h4 {
      margin: 0 0 10px;
      font-size: 1rem;
      border-bottom: 1px solid #00D4FF;
      padding-bottom: 5px;
    }
    .notificaciones-dropdown ul {
      list-style: none;
      padding: 0;
      margin: 0;
    }
    .notificaciones-dropdown li {
      padding: 5px 0;
      border-bottom: 1px solid rgba(255, 255, 255, 0.1);
      font-size: 0.9rem;
    }
    #badge-alerta {
      background: #FF6B6B;
      border-radius: 50%;
      width: 12px;
      height: 12px;
      display: inline-block;
      margin-left: auto;
      border: 2px solid #fff;
      box-shadow: 0 0 6px #FF6B6B;
    }

    /* ===== Mensaje sin registros ===== */
    .mensaje-vacio {
      position: absolute;
      top: 50%; left: 50%;
      transform: translate(-50%, -50%);
      max-width: 300px;
      background: rgba(0, 0, 0, 0.6);
      padding: 30px 20px;
      border-radius: 15px;
      font-size: 1rem;
      line-height: 1.4;
      text-align: center;
      opacity: 0;
      transition: opacity 0.3s ease;
      pointer-events: none;
    }
    .mensaje-vacio.visible {
      opacity: 1;
    }

    /* ===== Responsive ===== */
    @media (max-width: 768px) {
      .dashboard-container {
        flex-direction: column;
        overflow: auto;
      }
      .sidebar {
        flex-direction: row;
        width: 100% !important;
        height: auto;
      }
      .hamburger {
        right: 15px;
      }
    }
    /* Widgets sin datos */
    .grid-stack-item-content.empty {
      opacity: 0.4;
      filter: grayscale(60%);
      pointer-events: none; /* No interactuable */
      transition: opacity 0.3s ease;
    }

    /* Parpadeo para resaltar gráfico principal */
    @keyframes blink {
      0% { box-shadow: 0 0 10px rgba(255,255,255,0.6); }
      50% { box-shadow: 0 0 20px rgba(255,255,255,1); }
      100% { box-shadow: 0 0 10px rgba(255,255,255,0.6); }
    }
    #grafico-item.blink {
      animation: blink 1.5s infinite;
    }

    /* Parpadeo para botones de filtro */
    @keyframes pulse {
      0% { background-color: rgba(255, 255, 255, 0.2); }
      50% { background-color: rgba(255, 255, 255, 0.5); }
      100% { background-color: rgba(255, 255, 255, 0.2); }
    }
    .chart-filters button.blink {
      animation: pulse 1.2s infinite;
      border: 1px solid #fff;
    }
</style>
<div id="particles-js"></div>
<div class="dashboard-container">
  <!-- Sidebar -->
  <div class="sidebar collapsed" id="sidebar">
    <!-- Botón hamburguesa -->
    <button class="hamburger" onclick="toggleSidebar()">
      <i class="bi bi-list"></i>
    </button>
    <div class="menu-content">
      <div class="menu-top">
        <button onclick="location.href='registro.php'">
          <i class="bi bi-pencil-square"></i> <span class="label">Registro</span>
        </button>
        <button onclick="location.href='metas.php'">
          <i class="bi bi-flag-fill"></i> <span class="label">Metas</span>
        </button>
      </div>
      <!-- Notificaciones -->
      <button id="btn-notificaciones" onclick="toggleNotificaciones()">
        <i id="icono-campana" class="bi bi-bell-fill"></i> 
        <span class="label">Notificaciones</span>
        <span id="badge-alerta"></span>
      </button>
      <div id="panel-notificaciones" class="notificaciones-dropdown">
        <h4>Notificaciones</h4>
        <ul id="lista-notificaciones"></ul>
      </div>
      <div class="menu-bottom">
        <button class="ajustes" onclick="location.href='ajustes.php'">
          <i class="bi bi-gear-fill"></i> <span class="label">Ajustes</span>
        </button>
        <button class="salir" onclick="location.href='logout.php'">
          <i class="bi bi-box-arrow-right"></i> <span class="label">Salir</span>
        </button>
      </div>
    </div>
  </div>
  <script>
  function toggleSidebar() {
    document.getElementById("sidebar").classList.toggle("collapsed");
  }
  </script>
  <!-- Contenido principal -->
  <div class="main-content">
    <h2>Bienvenido, <?php echo htmlspecialchars($nombreUsuario); ?>!</h2>
    <div class="grid-stack">
      <!-- Gráfico -->
      <div class="grid-stack-item" id="grafico-item" gs-x="6" gs-y="0" gs-w="5" gs-h="4">
        <div class="grid-stack-item-content" style="position: relative;">
          <div class="chart-filters">
            <button onclick="filtrar('día')">Día</button>
            <button onclick="filtrar('semana')">Semana</button>
            <button onclick="filtrar('mes')" class="activo">Mes</button>
            <button onclick="filtrar('año')">Año</button>
          </div>
          <canvas id="graficoFinanzas"></canvas>
          <!-- Mensaje -->
          <div id="mensaje-sin-registros" class="mensaje-vacio">
            No hay registros aún.<br>
            Ve a <strong>Registro</strong> para comenzar.
          </div>
        </div>
      </div>
      <div class="grid-stack-item" gs-x="0" gs-y="0" gs-w="6" gs-h="4">
        <div class="grid-stack-item-content glass-card">
          <h3>Ingresos vs Gastos</h3>
          <canvas id="chartIngresosGastos"></canvas>
        </div>
      </div>
      <!-- Distribución General -->
      <div class="grid-stack-item" gs-x="7" gs-y="4" gs-w="3" gs-h="4">
        <div class="grid-stack-item-content glass-card">
          <h3>Distribución General</h3>
          <canvas id="chartDistribucion"></canvas>
        </div>
      </div>
      <!-- Evolución del Ahorro -->
      <div class="grid-stack-item" gs-x="0" gs-y="4" gs-w="6" gs-h="4">
        <div class="grid-stack-item-content glass-card">
          <h3>Evolución del Ahorro</h3>
          <canvas id="chartAhorro"></canvas>
        </div>
      </div>
      <!-- Ingresos -->
      <div class="grid-stack-item" gs-x="6" gs-y="4" gs-h="2" style="display: block;">
        <div class="grid-stack-item-content">
          <h3>Ingresos</h3>
          <p id="ingresos">$0.00</p>
        </div>
      </div>
      <!-- Gastos -->
      <div class="grid-stack-item" gs-x="10" gs-y="4" gs-h="2" style="display: block;">
        <div class="grid-stack-item-content">
          <h3>Gastos</h3>
          <p id="gastos">$0.00</p>
        </div>
      </div>
      <!-- Ahorro disponible -->
      <div class="grid-stack-item ui-resizable-autohide" id="contenedor-ahorro" gs-x="10" gs-y="6" style="display: block;" gs-h="2">
        <div class="grid-stack-item-content">
          <h3>Ahorro disponible</h3>
          <p id="ahorro">$0.00</p>
        </div>
      </div>
      <!-- Ahorro invertido -->
      <div class="grid-stack-item ui-resizable-autohide" id="contenedor-aportes" gs-x="6" gs-y="6" style="display: block;" gs-h="2">
        <div class="grid-stack-item-content">
          <h3>Ahorro invertido</h3>
          <p id="aportes">$0.00</p>
        </div>
      </div>
    </div>
  </div>
</div>
<script>
  const datosGraficos = {
    ingresos: <?= (int)$totalIngresos ?>,
    gastos: <?= (int)$totalGastos ?>,
    ahorroDisponible: <?= (int)$saldoActual ?>,
    ahorroInvertido: <?= (int)$totalAportes ?>
  };
</script>
<script>
  let grid;
  document.addEventListener('DOMContentLoaded', () => {
    grid = GridStack.init({
      float: true,             // Widgets libres
      cellHeight: 120,         // Altura base por fila
      margin: 10,              // Espacio entre widgets
      disableOneColumnMode: true
    });
    // 🔹 Expande dinámicamente las filas
    grid.on('added removed change', function () {
      grid.engine.maxRow = grid.engine.getRow();
    });
    // 🔹 Forzamos scroll vertical
    const mainContent = document.querySelector('.main-content');
    if (mainContent) {
      mainContent.style.overflowY = 'auto';
      mainContent.style.maxHeight = 'calc(100vh - 50px)';
    }
    filtrar('mes'); // carga inicial
  });
  // Inicialización del gráfico
  const ctx = document.getElementById('graficoFinanzas').getContext('2d');
  const grafico = new Chart(ctx, {
    type: 'bar',
    data: {
      labels: [],
      datasets: [
        { label: 'Ingresos', data: [], backgroundColor: '#4CAF50' },
        { label: 'Gastos', data: [], backgroundColor: '#F44336' }
      ]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: {
          display: true,
          labels: {
            color: '#fff',
            font: { size: 14 }
          }
        }
      },
      scales: {
        x: {
          ticks: { color: '#fff' },
          grid: { color: 'rgba(255,255,255,0.2)' }
        },
        y: {
          beginAtZero: true,
          ticks: { color: '#fff' },
          grid: { color: 'rgba(255,255,255,0.2)' }
        }
      }
    }
  });
  // 🔹 Formatear números
  function formatNumber(num) {
    return new Intl.NumberFormat('es-ES', { minimumFractionDigits: 2 }).format(num);
  }
  // 🔹 Actualizar color según positivo/negativo
  function aplicarColor(id, valor) {
    const el = document.getElementById(id);
    el.innerText = `$${formatNumber(valor)}`;
    el.style.color = valor < 0 ? '#FF6B6B' : '#FFFFFF';
  }
  // 🔹 Filtrar datos
  function filtrar(periodo) {
    const periodoLower = periodo.toLowerCase();

    // Botón activo
    document.querySelectorAll('.chart-filters button').forEach(btn => {
      btn.classList.toggle('activo', btn.textContent.toLowerCase() === periodoLower);
      btn.classList.remove('parpadeo'); // limpiamos parpadeo previo
    });
    $.post('includes/filtrar_datos.php', { periodo: periodoLower }, function (respuesta) {
      const datos = typeof respuesta === 'string' ? JSON.parse(respuesta) : respuesta;

      const tieneDatos =
        datos.fechas && datos.fechas.length > 0 &&
        ((datos.ingresos && datos.ingresos.some(v => v !== 0)) ||
         (datos.gastos && datos.gastos.some(v => v !== 0)));
      const mensaje = document.getElementById('mensaje-sin-registros');
      const graficoItem = document.getElementById('grafico-item');
      if (tieneDatos) {
        grafico.data.labels = datos.fechas;
        grafico.data.datasets = [
          { label: 'Ingresos', data: datos.ingresos, backgroundColor: '#4CAF50' },
          { label: 'Gastos', data: datos.gastos, backgroundColor: '#F44336' }
        ];
        mensaje.classList.remove('visible');
        graficoItem.classList.remove('parpadeo');
      } else {
        grafico.data.labels = [];
        grafico.data.datasets.forEach(ds => ds.data = []);
        mensaje.classList.add('visible');
        graficoItem.classList.add('parpadeo');
      }
      grafico.update();
      // Totales
      aplicarColor('ingresos', datos.ingresos?.reduce((a, b) => a + b, 0) || 0);
      aplicarColor('gastos', datos.gastos?.reduce((a, b) => a + b, 0) || 0);
      aplicarColor('ahorro', datos.ahorro || 0);
      aplicarColor('aportes', datos.aportes || 0);
      // 🔹 Widgets: semi-transparente si no hay datos
      const widgets = [
        { id: 'ingresos', cont: '.grid-stack-item:has(#ingresos)' },
        { id: 'gastos', cont: '.grid-stack-item:has(#gastos)' },
        { id: 'ahorro', cont: '#contenedor-ahorro' },
        { id: 'aportes', cont: '#contenedor-aportes' }
      ];
      widgets.forEach(w => {
        const el = document.querySelector(w.cont);
        if (!el) return;
        const valor = document.getElementById(w.id).innerText.replace(/[^\d.-]/g, '');
        const num = parseFloat(valor) || 0;
        if (tieneDatos && num > 0) {
          el.classList.remove('sin-datos');
        } else {
          el.classList.add('sin-datos');
        }
      });
      // 🔹 Buscar si otro periodo tiene datos (parpadeo)
      const periodos = ['día', 'semana', 'mes', 'año'];
      periodos.forEach(p => {
        if (p !== periodoLower) {
          $.post('includes/filtrar_datos.php', { periodo: p }, function (resp) {
            const d = typeof resp === 'string' ? JSON.parse(resp) : resp;
            const hay = d.fechas && d.fechas.length > 0 &&
              ((d.ingresos && d.ingresos.some(v => v !== 0)) ||
               (d.gastos && d.gastos.some(v => v !== 0)));
            if (hay && !tieneDatos) {
              document.querySelector(`.chart-filters button:nth-child(${periodos.indexOf(p) + 1})`)
                .classList.add('parpadeo');
            }
          });
        }
      });
    });
  }
</script>
<script>
// === Configuración global de colores ===
const colores = {
  ingresos: "#00D4FF",
  gastos: "#FF6B6B",
  ahorro: "#00FF7F"
};
// === Función para transformar datos del backend en labels y datasets ===
function prepararDatos(dataset, tipo = "ingresosGastos") {
  const labels = dataset.map(d => d.periodo);
  if (tipo === "ingresosGastos") {
    return {
      labels,
      datasets: [
        { label: "Ingresos", data: dataset.map(d => Number(d.ingresos) || 0), backgroundColor: colores.ingresos },
        { label: "Gastos", data: dataset.map(d => Number(d.gastos) || 0), backgroundColor: colores.gastos }
      ]
    };
  }
  if (tipo === "ahorro") {
    // Evolución acumulada del ahorro = ingresos - gastos
    let acumulado = 0;
    const valores = dataset.map(d => {
      acumulado += (Number(d.ingresos) || 0) - (Number(d.gastos) || 0);
      return acumulado;
    });
    return {
      labels,
      datasets: [
        { 
          label: "Ahorro acumulado", 
          data: valores,
          borderColor: colores.ahorro,
          backgroundColor: "rgba(0,255,127,0.2)", 
          fill: true, 
          tension: 0.3,
          pointBackgroundColor: "#fff"
        }
      ]
    };
  }
  return { labels: [], datasets: [] };
}
// === Inicializar gráficos ===
const ctx1 = document.getElementById("chartIngresosGastos").getContext("2d");
const ctx2 = document.getElementById("chartDistribucion").getContext("2d");
const ctx3 = document.getElementById("chartAhorro").getContext("2d");
// Gráfico Ingresos vs Gastos
let chartIngresosGastos = new Chart(ctx1, {
  type: "bar",
  data: prepararDatos(dataMes, "ingresosGastos"), // por defecto mes
  options: {
    responsive: true,
    plugins: { legend: { labels: { color: "#fff" } } },
    scales: {
      x: { ticks: { color: "#fff" } },
      y: { ticks: { color: "#fff", beginAtZero: true } }
    }
  }
});
// Distribución general (fijo)
new Chart(ctx2, {
  type: "doughnut",
  data: {
    labels: ["Ingresos", "Gastos", "Ahorro"],
    datasets: [{
      data: [
        Number(totalesMes.ingresos) || 0,
        Number(totalesMes.gastos) || 0,
        (Number(totalesMes.ingresos) || 0) - (Number(totalesMes.gastos) || 0)
      ],
      backgroundColor: [colores.ingresos, colores.gastos, colores.ahorro],
      borderWidth: 1
    }]
  },
  options: {
    responsive: true,
    plugins: { legend: { labels: { color: "#fff", font: { size: 14 } } } }
  }
});
// Evolución del Ahorro
let chartAhorro = new Chart(ctx3, {
  type: "line",
  data: prepararDatos(dataMes, "ahorro"), // por defecto mes
  options: {
    responsive: true,
    plugins: { legend: { labels: { color: "#fff" } } },
    scales: {
      x: { ticks: { color: "#fff" } },
      y: { ticks: { color: "#fff", beginAtZero: true } }
    }
  }
});
// === Función para cambiar rango de tiempo ===
function cambiarRango(rango) {
  let dataset;
  if (rango === "dia") dataset = dataDia;
  if (rango === "semana") dataset = dataSemana;
  if (rango === "mes") dataset = dataMes;
  if (rango === "anio") dataset = dataAnio;
  // Actualiza Ingresos vs Gastos
  chartIngresosGastos.data = prepararDatos(dataset, "ingresosGastos");
  chartIngresosGastos.update();
  // Actualiza Evolución del Ahorro
  chartAhorro.data = prepararDatos(dataset, "ahorro");
  chartAhorro.update();
}
// === Aplicar semitransparencia si no hay datos ===
function aplicarEstadoGrafica(canvasId, valores) {
  const contenedor = document.getElementById(canvasId).closest('.grid-stack-item');
  const tieneDatos = valores.some(v => v > 0);
  if (!tieneDatos) {
    contenedor.classList.add('sin-datos');
  } else {
    contenedor.classList.remove('sin-datos');
  }
}
// Aplica a cada gráfico
aplicarEstadoGrafica("chartIngresosGastos", [totalesMes.ingresos, totalesMes.gastos]);
aplicarEstadoGrafica("chartDistribucion", [totalesMes.ingresos, totalesMes.gastos]);
aplicarEstadoGrafica("chartAhorro", [(Number(totalesMes.ingresos) || 0) - (Number(totalesMes.gastos) || 0)]);
</script>
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
document.addEventListener('DOMContentLoaded', () => {
  GridStack.init();
  filtrar('mes');
  const saldoActual = Number(<?= json_encode($saldoActual) ?>);
  const ingresosTotales = Number(<?= json_encode($totalIngresos) ?>);
  const ingresoMinimo = Number(<?= json_encode($usuario['ingreso_minimo']) ?>);
  const saldoMinimo = Number(<?= json_encode($usuario['saldo_minimo']) ?>);
  const metas = <?= json_encode($lista_metas) ?>;

  const listaNotificaciones = document.getElementById('lista-notificaciones');
  const badgeAlerta = document.getElementById('badge-alerta');
  const iconoCampana = document.getElementById('icono-campana');
  const notificaciones = [];
  if (ingresoMinimo === 0 || saldoMinimo === 0) {
    notificaciones.push("⚠️ No tienes configurado tu ingreso o saldo mínimo. Ve a Ajustes para configurarlos.");
  } else {
    if (saldoActual <= saldoMinimo && saldoActual > 0) {
      notificaciones.push(`⚠️ Tu saldo está en el mínimo: $${saldoActual.toFixed(2)}`);
    }
    if (ingresosTotales <= ingresoMinimo && ingresosTotales > 0) {
      notificaciones.push(`⚠️ Tus ingresos están en el mínimo: $${ingresosTotales.toFixed(2)}`);
    }
    if (saldoActual <= 0) {
      notificaciones.push("⚠️ No estás generando ahorro este mes.");
    }
  }
  const hoy = new Date();
  metas.forEach(meta => {
    const fechaLimite = new Date(meta.fecha_limite);
    const diasRestantes = Math.ceil((fechaLimite - hoy) / (1000 * 60 * 60 * 24));
    const porcentaje = meta.monto_objetivo > 0 ? (parseFloat(meta.total_aportado) / meta.monto_objetivo) * 100 : 0;
    if (diasRestantes <= 5 && porcentaje < 100) {
      notificaciones.push(` Meta "${meta.nombre}" vence en ${diasRestantes} día(s).`);
    }
    if (porcentaje >= 100) {
      notificaciones.push(` Meta "${meta.nombre}" alcanzada.`);
    }
  });
  if (notificaciones.length > 0) {
    badgeAlerta.style.display = 'inline-block';
    iconoCampana.classList.add('shake');
    notificaciones.forEach(msg => {
      const li = document.createElement('li');
      li.textContent = msg;
      listaNotificaciones.appendChild(li);
    });
  } else {
    const li = document.createElement('li');
    li.textContent = 'Sin notificaciones.';
    listaNotificaciones.appendChild(li);
  }
});
//Panel de notificaciones
function toggleNotificaciones() {
  const panel = document.getElementById('panel-notificaciones');
  panel.style.display = panel.style.display === 'flex' ? 'none' : 'flex';
  document.getElementById('icono-campana').classList.remove('shake');
  document.getElementById('badge-alerta').style.display = 'none';
}
document.addEventListener('click', e => {
  const panel = document.getElementById('panel-notificaciones');
  const boton = document.getElementById('btn-notificaciones');
  if (panel && panel.style.display === 'flex' && !panel.contains(e.target) && !boton.contains(e.target)) {
    panel.style.display = 'none';
  }
});
</script>
<?php include 'includes/footer.php'; ?>