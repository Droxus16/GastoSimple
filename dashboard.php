<?php
session_start();
require_once 'includes/db.php';
require_once 'includes/auth.php';
$conn = DB::conectar();
$idUsuario = $_SESSION['usuario_id'];
// Traer nombre, ingreso_minimo y saldo_minimo del usuario
$stmt = $conn->prepare("SELECT nombre, ingreso_minimo, saldo_minimo FROM usuarios WHERE id = ?");
$stmt->execute([$idUsuario]);
$usuario = $stmt->fetch(PDO::FETCH_ASSOC);
$nombreUsuario = $usuario ? htmlspecialchars($usuario['nombre']) : "Usuario";
// Totales de mes para notificaciones
$totales = $conn->prepare("
  SELECT
    (SELECT COALESCE(SUM(monto),0) FROM ingresos WHERE usuario_id = :usuario_id AND MONTH(fecha) = MONTH(CURDATE()) AND YEAR(fecha) = YEAR(CURDATE())) AS total_ingresos,
    (SELECT COALESCE(SUM(monto),0) FROM gastos WHERE usuario_id = :usuario_id AND MONTH(fecha) = MONTH(CURDATE()) AND YEAR(fecha) = YEAR(CURDATE())) AS total_gastos,
    (SELECT COALESCE(SUM(monto),0) FROM aportes_ahorro a JOIN metas_ahorro m ON a.meta_id = m.id WHERE m.usuario_id = :usuario_id AND MONTH(a.fecha) = MONTH(CURDATE()) AND YEAR(a.fecha) = YEAR(CURDATE())) AS total_aportes
");
$totales->execute(['usuario_id' => $idUsuario]);
$datos = $totales->fetch(PDO::FETCH_ASSOC);
$totalIngresos = $datos['total_ingresos'];
$totalGastos = $datos['total_gastos'];
$totalAportes = $datos['total_aportes'];
$saldoActual = $totalIngresos - $totalGastos - $totalAportes;
// Metas (si aplica)
$stmtMetas = $conn->prepare("SELECT nombre, fecha_limite, monto_objetivo, 
  (SELECT COALESCE(SUM(monto),0) FROM aportes_ahorro WHERE meta_id = metas_ahorro.id) AS total_aportado 
  FROM metas_ahorro WHERE usuario_id = ?");
$stmtMetas->execute([$idUsuario]);
$lista_metas = $stmtMetas->fetchAll(PDO::FETCH_ASSOC);
?>
<?php include 'includes/header.php'; ?>
<link rel="stylesheet" href="assets/css/estilos.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/gridstack@8.2.1/dist/gridstack.min.css">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/gridstack@8.2.1/dist/gridstack-all.js"></script>
<script src="https://cdn.jsdelivr.net/npm/particles.js"></script>
<style>
/* ================================
   🎨 Fondo animado + partículas
   ================================ */
body {
  position: relative;
  width: 100%;
  height: 100%;
  background: linear-gradient(135deg, #0B0B52, #1D2B64, #0C1634, #0B0B52);
  background-size: 300% 300%;
  animation: backgroundAnim 25s ease-in-out infinite;
  z-index: -2;
  opacity: 0.95;
  overflow: hidden;
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

/* ================================
   🗂️ Layout principal
   ================================ */
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

.sidebar .menu-top,
.sidebar .menu-bottom {
  display: flex;
  flex-direction: column;
  gap: 15px;
}

.sidebar button {
  display: flex;
  align-items: center;
  gap: 14px;
  padding: 14px 18px;
  font-size: 1.08rem;
  border: none;
  border-radius: 16px;
  background: linear-gradient(90deg, rgba(0,212,255,0.13) 0%, rgba(11,20,60,0.92) 100%);
  color: #e0f7fa;
  font-weight: 600;
  cursor: pointer;
  transition: 
    background 0.18s, 
    color 0.18s, 
    box-shadow 0.18s, 
    transform 0.18s;
  box-shadow: 0 2px 12px rgba(0,212,255,0.08);
  margin-bottom: 8px;
  position: relative;
  outline: none;
}
.sidebar button i {
  font-size: 1.35em;
  color: #00D4FF;
  transition: color 0.18s;
}
.sidebar button:hover, .sidebar button:focus {
  background: linear-gradient(90deg, #00D4FF 0%, #1D2B64 100%);
  color: #fff;
  box-shadow: 0 4px 18px rgba(0,212,255,0.18);
  transform: translateY(-2px) scale(1.04);
}
.sidebar button:hover i, .sidebar button:focus i {
  color: #fff;
}
.menu-top, .menu-bottom {
  margin-bottom: 18px;
}

.sidebar button:hover {
  background-color: #00D4FF;
  color: #0C1634;
  transform: scale(1.05);
}

.main-content {
  flex: 1;
  display: flex;
  flex-direction: column;
  background: rgba(255, 255, 255, 0.05);
  padding: 25px;
  border-radius: 20px;
  backdrop-filter: blur(10px);
  overflow: hidden;
  color: white;
  box-sizing: border-box;
}

/* ================================
   🧩 Cuadrícula GridStack
   ================================ */
.grid-stack {
  flex: 1;
  overflow: auto; /* Si quieres scroll general */
  width: 100%;
  height: 100%;
  box-sizing: border-box;
  padding: 20px; /* Espacio alrededor */
}

.grid-stack-item {
  min-height: 150px; /* Ajusta según tu diseño */
  margin-bottom: 20px; /* Espaciado entre widgets */
}

.grid-stack-item-content {
  position: relative;
  display: flex; /* Clave para layout flexible */
  flex-direction: column;
  align-items: center;
  justify-content: flex-start;
  background: rgba(255, 255, 255, 0.15);
  border-radius: 20px;
  padding: 25px;
  box-shadow: 0 8px 32px rgba(0, 0, 0, 0.25);
  color: white;
  text-align: center;
  transition: transform 0.2s ease-in-out;
  overflow: hidden; /* Evita scroll interno */
}

.grid-stack-item-content:hover {
  transform: scale(1.01);
}
.grid-stack-item-content canvas {
  display: block;
  width: 100% !important;   /* Ocupar todo horizontal */
  height: 100% !important;  /* Forzar altura responsiva */
  max-height: 300px;        /* O el máximo que prefieras */
  margin: 0 auto;
}


/* ================================
   🔘 Botones de filtros de gráfica
   ================================ */
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

/* ================================
   🔔 Panel de notificaciones
   ================================ */
.notificaciones-dropdown {
  position: absolute;
  top: 80px;
  left: 20px;
  width: 250px;
  background: rgba(255, 255, 255, 0.08);
  border-radius: 12px;
  backdrop-filter: blur(10px);
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
  background: red;
  border-radius: 50%;
  width: 12px;
  height: 12px;
  display: inline-block;
  margin-left: 5px;
}

.shake {
  animation: shake 0.5s;
}

@keyframes shake {
  0% { transform: rotate(0deg); }
  20% { transform: rotate(-15deg); }
  40% { transform: rotate(15deg); }
  60% { transform: rotate(-10deg); }
  80% { transform: rotate(10deg); }
  100% { transform: rotate(0deg); }
}

/* ================================
   🪧 Mensaje “sin registros”
   ================================ */
#mensaje-sin-registros {
  position: absolute;
  top: 50%; left: 60%;
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

#mensaje-sin-registros.visible {
  opacity: 1;
}

/* ================================
   📱 Responsivo
   ================================ */
@media (max-width: 768px) {
  .dashboard-container {
    flex-direction: column;
    overflow: auto;
  }

  .sidebar {
    width: 100%;
    flex-direction: row;
    justify-content: space-around;
    padding-bottom: 10px;
  }

  .sidebar .menu-top,
  .sidebar .menu-bottom {
    border-top: 1.5px solid rgba(0,212,255,0.13);
    padding-top: 18px;
    margin-top: 18px;
  }
  #btn-notificaciones {
  background: linear-gradient(90deg, rgba(0,212,255,0.18) 0%, rgba(11,20,60,0.92) 100%);
  color: #00D4FF;
  font-weight: 700;
  position: relative;
}

  .main-content {
    height: auto;
    max-height: none;
    margin-top: 10px;
  }
}
#btn-notificaciones:hover, #btn-notificaciones:focus {
  background: linear-gradient(90deg, #00D4FF 0%, #1D2B64 100%);
  color: #fff;
}
#btn-notificaciones i {
  color: #00D4FF;
}
#btn-notificaciones:hover i, #btn-notificaciones:focus i {
  color: #fff;
}
#badge-alerta {
  background: #FF6B6B;
  border-radius: 50%;
  width: 12px;
  height: 12px;
  display: inline-block;
  margin-left: 8px;
  border: 2px solid #fff;
  box-shadow: 0 0 6px #FF6B6B;
}
@media (max-width: 768px) {
  .sidebar button {
    font-size: 1rem;
    padding: 10px 8px;
    border-radius: 12px;
    gap: 8px;
  }
}
</style>
<!-- 🎆 Partículas de fondo -->
<div id="particles-js"></div>
<div class="dashboard-container">
  <div class="sidebar">
    <div class="menu-top">
      <button onclick="location.href='registro.php'">
        <i class="bi bi-pencil-square"></i> Registro
      </button>
      <button onclick="location.href='metas.php'">
        <i class="bi bi-flag-fill"></i> Metas
      </button>
    </div>

    <!-- 🔔 Notificaciones -->
    <button id="btn-notificaciones" onclick="toggleNotificaciones()">
      <i id="icono-campana" class="bi bi-bell-fill"></i> Notificaciones
      <span id="badge-alerta"></span>
    </button>
    <div id="panel-notificaciones" class="notificaciones-dropdown">
      <h4>Notificaciones</h4>
      <ul id="lista-notificaciones"></ul>
    </div>

    <div class="menu-bottom">
      <button onclick="location.href='logout.php'">
        <i class="bi bi-box-arrow-right"></i> Salir
      </button>
      <button onclick="location.href='ajustes.php'">
        <i class="bi bi-gear-fill"></i> Ajustes
      </button>
    </div>
  </div>

  <!-- 📊 Panel principal -->
  <div class="main-content">
    <h2>Bienvenido, <?php echo htmlspecialchars($nombreUsuario); ?>!</h2>

        <div class="grid-stack">
        <!-- 🟢 Gráfica con filtros -->
        <div class="grid-stack-item" 
            id="grafico-item" 
            gs-x="0" gs-y="0" gs-w="7" gs-h="5">
          <div class="grid-stack-item-content" style="position: relative;">
            <div class="chart-filters">
              <button onclick="filtrar('día')">Día</button>
              <button onclick="filtrar('semana')">Semana</button>
              <button onclick="filtrar('mes')" class="activo">Mes</button>
              <button onclick="filtrar('año')">Año</button>
            </div>
            <canvas id="graficoFinanzas"></canvas>

            <!--Mensaje -->
            <div id="mensaje-sin-registros" style="
              position: absolute;
              top: 50%; left: 50%;
              transform: translate(-50%, -50%);
              background: rgba(0, 0, 0, 0.4);
              padding: 20px;
              border-radius: 12px;
              color: #fff;
              text-align: center;">
              No hay registros aún.<br>
              Ve a <strong>Registro</strong> para comenzar.
            </div>
          </div>
        </div>
        <!-- Ingresos -->
        <div class="grid-stack-item"
            gs-x="8" gs-y="0" gs-w="2" gs-h="2">
          <div class="grid-stack-item-content">
            <h3>Ingresos</h3>
            <p id="ingresos">$0.00</p>
          </div>
        </div>
        <!-- Gastos -->
        <div class="grid-stack-item"
            gs-x="10" gs-y="0" gs-w="2" gs-h="2">
          <div class="grid-stack-item-content">
            <h3>Gastos</h3>
            <p id="gastos">$0.00</p>
          </div>
        </div>
        <!--Ahorro disponible -->
        <div class="grid-stack-item"
            id="contenedor-ahorro"
            gs-x="8" gs-y="2" gs-w="4" gs-h="2"
            style="display: none;">
          <div class="grid-stack-item-content">
            <h3>Ahorro disponible</h3>
            <p id="ahorro">$0.00</p>
          </div>
        </div>

      <!--Ahorro para metas (se oculta si no aplica) -->
      <div class="grid-stack-item" id="contenedor-aportes" gs-x="8" gs-y="4" gs-w="4" gs-h="1" style="display: none;">
        <div class="grid-stack-item-content">
          <h3>Ahorro invertido</h3>
          <p id="aportes">$0.00</p>
        </div>
      </div>
    </div>
  </div>
</div>
<script>
  // ------------------------------------------
  // ⚙️ 1) Inicializa GridStack una vez y guarda la instancia
  // ------------------------------------------
  let grid;

  document.addEventListener('DOMContentLoaded', () => {
    grid = GridStack.init(); // ✅ Con v8 necesitas guardar tu instancia tú mismo
    filtrar('mes'); // Opcional: carga periodo por defecto
  });

  // ------------------------------------------
  // 📊 Inicializa gráfico vacío
  // ------------------------------------------
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
      maintainAspectRatio: false, // 👈 sin error de coma
      plugins: {
        legend: { display: true }
      },
      scales: {
        y: { beginAtZero: true }
      }
    }
  });

  // ------------------------------------------
  // 💵 Formatea números con decimales
  // ------------------------------------------
  function formatNumber(num) {
    return new Intl.NumberFormat('es-ES', { minimumFractionDigits: 2 }).format(num);
  }

  // ------------------------------------------
  // 🟥 Aplica valor y color según signo
  // ------------------------------------------
  function aplicarColor(id, valor) {
    const el = document.getElementById(id);
    el.innerText = `$${formatNumber(valor)}`;
    el.style.color = valor < 0 ? '#FF6B6B' : '#FFFFFF';
  }

  // ------------------------------------------
  // 🔎 Filtrar por periodo dinámico
  // ------------------------------------------
  function filtrar(periodo) {
    const periodoLower = periodo.toLowerCase();

    // Activa botón correcto
    document.querySelectorAll('.chart-filters button').forEach(btn => {
      btn.classList.toggle('activo', btn.textContent.toLowerCase() === periodoLower);
    });

    // Consulta AJAX
    $.post('includes/filtrar_datos.php', { periodo: periodoLower }, function (respuesta) {
      const datos = typeof respuesta === 'string' ? JSON.parse(respuesta) : respuesta;

      const tieneDatos =
        datos.fechas && datos.fechas.length > 0 &&
        ((datos.ingresos && datos.ingresos.some(v => v !== 0)) ||
         (datos.gastos && datos.gastos.some(v => v !== 0)));

      const mensaje = document.getElementById('mensaje-sin-registros');

      if (tieneDatos) {
        grafico.data.labels = datos.fechas;
        grafico.data.datasets = [
          { label: 'Ingresos', data: datos.ingresos, backgroundColor: '#4CAF50' },
          { label: 'Gastos', data: datos.gastos, backgroundColor: '#F44336' }
        ];
        mensaje.classList.remove('visible');
      } else {
        grafico.data.labels = [];
        grafico.data.datasets.forEach(ds => ds.data = []);
        mensaje.classList.add('visible');
      }
      grafico.update();

      aplicarColor('ingresos', datos.ingresos?.reduce((a, b) => a + b, 0) || 0);
      aplicarColor('gastos', datos.gastos?.reduce((a, b) => a + b, 0) || 0);
      aplicarColor('ahorro', datos.ahorro || 0);
      aplicarColor('aportes', datos.aportes || 0);

      // -------------------------------
      // Mostrar/ocultar contenedores correctamente
      // -------------------------------
      const ingresosItem = document.querySelector('.grid-stack-item:has(#ingresos)');
      const gastosItem = document.querySelector('.grid-stack-item:has(#gastos)');
      const contenedorAhorro = document.getElementById('contenedor-ahorro');
      const contenedorAportes = document.getElementById('contenedor-aportes');

      if (tieneDatos) {
        ingresosItem.style.display = 'block';
        gastosItem.style.display = 'block';
        contenedorAhorro.style.display = (datos.ahorro && datos.ahorro !== 0) ? 'block' : 'none';
        contenedorAportes.style.display = (datos.aportes && datos.aportes !== 0) ? 'block' : 'none';
      } else {
        ingresosItem.style.display = 'none';
        gastosItem.style.display = 'none';
        contenedorAhorro.style.display = 'none';
        contenedorAportes.style.display = 'none';
      }

      // -------------------------------
      // ✅ Posicionar y bloquear gráfica
      // -------------------------------
      const graficoItem = document.getElementById('grafico-item');

      if (tieneDatos) {
        grid.update(graficoItem, {
          x: 0,
          y: 0,
          w: 7,
          h: 5,
          locked: false
        });
      } else {
        grid.update(graficoItem, {
          x: 2, // Centro calculado: (12-8)/2 = 2
          y: 0,
          w: 8,
          h: 6,
          locked: true
        });
      }

      grid.compact();
    });
  }
</script>
<script>
// ------------------------------------------
// 🎆 Fondo de partículas
// ------------------------------------------
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



// ------------------------------------------
// 🚀 Inicializa dashboard
// ------------------------------------------
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
      notificaciones.push(`📌 Meta "${meta.nombre}" vence en ${diasRestantes} día(s).`);
    }
    if (porcentaje >= 100) {
      notificaciones.push(`🎉 Meta "${meta.nombre}" alcanzada.`);
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
    li.textContent = '✅ Sin notificaciones.';
    listaNotificaciones.appendChild(li);
  }
});

// ------------------------------------------
// 🔔 Panel de notificaciones
// ------------------------------------------
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