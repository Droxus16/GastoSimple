<?php
session_start();
require_once 'includes/db.php';
require_once 'includes/auth.php';
require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Writer\Pdf\Mpdf;
use PhpOffice\PhpSpreadsheet\IOFactory;

date_default_timezone_set('America/Bogota');
$conn = db::conectar();
$idUsuario = intval($_SESSION['usuario_id']);

// Obtener ingreso mínimo y saldo mínimo del usuario
$sqlUsuario = "SELECT ingreso_minimo, saldo_minimo FROM usuarios WHERE id = ?";
$stmtUsuario = $conn->prepare($sqlUsuario);
$stmtUsuario->execute([$idUsuario]);
$usuario = $stmtUsuario->fetch(PDO::FETCH_ASSOC);

$ingresoMinimo = floatval($usuario['ingreso_minimo'] ?? 0);
$saldoMinimo   = floatval($usuario['saldo_minimo'] ?? 0);

// Fechas de filtro
$fechaInicio = $_GET['fecha_inicio'] ?? null;
$fechaFin    = $_GET['fecha_fin'] ?? null;

// Consultar transacciones
$sqlTodos = "SELECT id_transaccion, tipo, fecha, monto, categoria, descripcion 
             FROM transacciones 
             WHERE id_usuario = ?";
$params = [$idUsuario];

if ($fechaInicio && $fechaFin) {
    $sqlTodos .= " AND DATE(fecha) BETWEEN ? AND ?";
    $params[] = $fechaInicio;
    $params[] = $fechaFin;
}

$sqlTodos .= " ORDER BY fecha DESC";
$stmtTodos = $conn->prepare($sqlTodos);
$stmtTodos->execute($params);
$transacciones = $stmtTodos->fetchAll(PDO::FETCH_ASSOC);

// Si no hay resultados, recargar todos los registros
if (!$transacciones && $fechaInicio && $fechaFin) {
    $sqlTodos = "SELECT id_transaccion, tipo, fecha, monto, categoria, descripcion 
                 FROM transacciones 
                 WHERE id_usuario = ?
                 ORDER BY fecha DESC";
    $stmtTodos = $conn->prepare($sqlTodos);
    $stmtTodos->execute([$idUsuario]);
    $transacciones = $stmtTodos->fetchAll(PDO::FETCH_ASSOC);

    $mensajeFiltro = "⚠️ No hay resultados entre $fechaInicio y $fechaFin. Se muestran todos los registros.";
}

// Ingresos del mes
$inicioMes = date('Y-m-01');
$finMes    = date('Y-m-t');

$sqlIngresos = "SELECT monto FROM ingresos 
                WHERE usuario_id = ? AND DATE(fecha) BETWEEN ? AND ?";
$stmtIngresos = $conn->prepare($sqlIngresos);
$stmtIngresos->execute([$idUsuario, $inicioMes, $finMes]);
$ingresosMes = $stmtIngresos->fetchAll(PDO::FETCH_ASSOC);

// Gastos del mes
$sqlGastos = "SELECT monto FROM gastos 
              WHERE usuario_id = ? AND DATE(fecha) BETWEEN ? AND ?";
$stmtGastos = $conn->prepare($sqlGastos);
$stmtGastos->execute([$idUsuario, $inicioMes, $finMes]);
$gastosMes = $stmtGastos->fetchAll(PDO::FETCH_ASSOC);

$totalIngresos = array_sum(array_column($ingresosMes, 'monto'));
$totalGastos   = array_sum(array_column($gastosMes, 'monto'));

// Calcular saldo actual
$saldoActual = $totalIngresos - $totalGastos;

// Categorías
$sqlCategorias = "SELECT id, nombre, tipo FROM categorias 
                  WHERE usuario_id = ? OR usuario_id IS NULL 
                  ORDER BY tipo, nombre";
$stmtCategorias = $conn->prepare($sqlCategorias);
$stmtCategorias->execute([$idUsuario]);
$categorias = $stmtCategorias->fetchAll(PDO::FETCH_ASSOC);
?>
<?php include 'includes/header.php'; ?>
<head>
  <link rel="stylesheet" href="sidebar.css">
  <script src="sidebar.js" defer></script>
</head>
<body 
  data-total-ingresos="<?= $totalIngresos ?>" 
  data-total-gastos="<?= $totalGastos ?>" 
  data-ingreso-minimo="<?= $ingresoMinimo ?>" 
  data-saldo-minimo="<?= $saldoMinimo ?>" 
  data-saldo-actual="<?= $saldoActual ?>">

<link rel="stylesheet" href="assets/css/estilos.css">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/particles.js@2.0.0"></script>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
<!-- Librerías necesarias -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
<script>
// Script de notificaciones dinámicas unificado
document.addEventListener("DOMContentLoaded", () => {
  const body = document.body;

  const saldoActual = Number(body.dataset.saldoActual) || 0;
  const ingresosTotales = Number(body.dataset.totalIngresos) || 0;
  const ingresoMinimo = Number(body.dataset.ingresoMinimo) || 0;
  const saldoMinimo = Number(body.dataset.saldoMinimo) || 0;

  console.log("Debug registro.php:", { saldoActual, ingresosTotales, ingresoMinimo, saldoMinimo });

  const lista = document.getElementById("lista-notificaciones");
  const badge = document.getElementById("badge-alerta");
  const campana = document.getElementById("icono-campana");

  const notificaciones = [];

  if (saldoActual <= saldoMinimo) 
    notificaciones.push(`⚠️ Saldo bajo: $${saldoActual.toFixed(2)}`);
  if (ingresosTotales <= ingresoMinimo) 
    notificaciones.push(`⚠️ Ingresos bajos: $${ingresosTotales.toFixed(2)}`);
  if (saldoActual <= 0) 
    notificaciones.push(`⚠️ No generas ahorro este mes.`);

  // Renderizar
  lista.innerHTML = "";
  if (notificaciones.length > 0) {
    notificaciones.forEach(msg => {
      const li = document.createElement("li");
      li.textContent = msg;
      lista.appendChild(li);
    });
    badge.textContent = notificaciones.length;
    badge.style.display = "inline-block";
    campana.classList.add("shake");
  } else {
    lista.innerHTML = "<li>✅ Sin notificaciones.</li>";
  }
});
</script>
<script src="https://cdn.jsdelivr.net/npm/html2canvas@1.4.1/dist/html2canvas.min.js"></script>
<script>
async function exportarReporte(tipo) {
  const status = document.getElementById('exportStatus');
  status.style.display = 'inline';

  try {
    const dashboard = document.querySelector('#graficas-dashboard');

    if (!dashboard) {
      alert("No se encontró el dashboard (id='graficas-dashboard') para capturar.");
      status.style.display = 'none';
      return;
    }

    // 🖼️ Ajuste de tamaño y resolución
    dashboard.style.width = "1200px"; // fuerza ancho para buena proporción
    const canvas = await html2canvas(dashboard, {
      scale: 3, // más resolución
      backgroundColor: '#ffffff',
      useCORS: true,
      logging: false,
      windowWidth: 1200, // asegura buena escala
    });

    const dataURL = canvas.toDataURL('image/png', 1.0);

    // 📨 Enviar captura al backend
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = 'controllers/reportes.php';

    const inputImg = document.createElement('input');
    inputImg.type = 'hidden';
    inputImg.name = 'pantallazoDashboard';
    inputImg.value = dataURL;
    form.appendChild(inputImg);

    const tipoInput = document.createElement('input');
    tipoInput.type = 'hidden';
    tipoInput.name = (tipo === 'pdf') ? 'exportar_pdf' : 'exportar_excel';
    tipoInput.value = '1';
    form.appendChild(tipoInput);

    document.body.appendChild(form);
    form.submit();

  } catch (error) {
    console.error("Error exportando el reporte:", error);
    alert("Ocurrió un error al generar el reporte. Revisa la consola.");
    status.style.display = 'none';
  }
}
</script>
<style>
    /*CONTENEDORES PRINCIPALES */
    .form-container, .tabla-container {
      background-color: rgba(255,255,255,0.07);
      padding: 20px;
      border-radius: 15px;
      backdrop-filter: blur(5px);
      margin-bottom: 20px;
      color: white;
      max-width: 900px;
      margin-left: auto;
      margin-right: auto;
      position: relative;
      z-index: 1;
    }
    .form-container h2 {
      text-align: center;
      margin-bottom: 20px;
      font-weight: 700;
    }
    .tabla-container {
      overflow-x: auto;
      display: block;
      margin: 20px auto;
    }
    .tabla-transacciones {
      width: 100%;
      border-collapse: collapse;
      min-width: 600px;
      margin-top: 10px;
    }
    .tabla-transacciones th, .tabla-transacciones td {
      border: 1px solid #ccc;
      padding: 8px;
      text-align: center;
      background-color: rgba(0, 0, 0, 0.3);
      color: white;
    }
    .tabla-transacciones th {
      background-color: rgba(255, 255, 255, 0.15);
      font-weight: bold;
    }
    @media (max-width: 768px) {
      .tabla-container::after {
        content: '← desliza la tabla →';
        display: block;
        text-align: center;
        font-size: 0.8rem;
        color: #bbb;
        margin-top: 5px;
      }
    }
    input, select, textarea {
      width: 100%;
      padding: 10px;
      margin-bottom: 15px;
      border-radius: 6px;
      border: none;
      font-size: 1rem;
      box-sizing: border-box;
    }
    .acciones {
      display: flex;
      gap: 10px;
      justify-content: center;
      flex-wrap: wrap;
    }
    button {
      background-color: #00D4FF;
      border: none;
      padding: 12px 20px;
      border-radius: 8px;
      cursor: pointer;
      font-weight: bold;
      color: #0C1634;
      font-size: 1rem;
      transition: background-color 0.3s ease;
    }
    button:hover {
      background-color: #00b8e6;
    }
    #particles-js {
      position: absolute;
      top: 0; left: 0;
      width: 100%; height: 100%;
      z-index: 0;
    }
    /*MODAL CORRECTAMENTE CENTRADO*/
/* Fondo del modal */
.modal-overlay {
  position: fixed;
  top: 0; left: 0;
  width: 100%; height: 100%;
  background: rgba(0, 0, 0, 0.75);
  display: none;
  justify-content: center;
  align-items: center;
  z-index: 1000;
  opacity: 0;
  transition: opacity 0.3s ease;
}

.modal-overlay.active {
  display: flex;
  opacity: 1;
}

/* Contenedor del modal */
.modal-content {
  background: linear-gradient(145deg, rgba(25, 25, 50, 0.95), rgba(15, 15, 30, 0.95));
  backdrop-filter: blur(18px);
  border-radius: 16px;
  border: 1px solid rgba(0, 212, 255, 0.2);
  color: #fff;
  padding: 30px;
  width: 90%;
  max-width: 550px;
  max-height: 90%;
  overflow-y: auto;
  position: relative;
  box-shadow: 0 0 30px rgba(0, 212, 255, 0.3);

  transform: translateY(-20px);
  transition: transform 0.3s ease, opacity 0.3s ease;
}

.modal-overlay.active .modal-content {
  transform: translateY(0);
  opacity: 1;
}

/* Botón cerrar */
.modal-close {
  position: absolute;
  top: 12px;
  right: 16px;
  font-size: 1.8rem;
  cursor: pointer;
  color: #bbb;
  transition: color 0.2s ease, transform 0.2s ease;
}

.modal-close:hover {
  color: #00D4FF;
  transform: rotate(90deg);
}

/* Encabezado */
.modal-content h2 {
  color: #00D4FF;
  font-weight: 700;
  margin-bottom: 20px;
  text-align: center;
}

/* Formularios */
.modal-content form {
  display: flex;
  flex-direction: column;
  gap: 15px;
}

.modal-content label {
  font-size: 0.9rem;
  color: #aaa;
  margin-bottom: 4px;
}

.modal-content input,
.modal-content select,
.modal-content textarea {
  background: rgba(20, 20, 40, 0.9);  /* 👈 más oscuro y uniforme */
  border: 1px solid rgba(255, 255, 255, 0.15);
  border-radius: 10px;
  padding: 10px;
  color: #fff;
  font-size: 0.95rem;
  outline: none;
  transition: border 0.2s ease, background 0.2s ease;

  /* Fix para selects */
  appearance: none;        /* elimina estilo nativo */
  -webkit-appearance: none;
  -moz-appearance: none;
}

.modal-content input:focus,
.modal-content select:focus,
.modal-content textarea:focus {
  border-color: #00D4FF;
  background: rgba(0, 212, 255, 0.1);  /* efecto al enfocar */
}


/* Botones */
.modal-content button {
  padding: 12px;
  border: none;
  border-radius: 12px;
  cursor: pointer;
  font-size: 1rem;
  font-weight: 600;
  transition: background 0.3s ease, transform 0.2s ease;
}

.modal-content button[type="submit"] {
  background: #00D4FF;
  color: #111;
}

.modal-content button[type="submit"]:hover {
  background: #00AACC;
  transform: translateY(-2px);
}

.modal-content #eliminar-transaccion {
  background: #ff4b4b;
  color: white;
}

.modal-content #eliminar-transaccion:hover {
  background: #cc0000;
  transform: translateY(-2px);
}

/* Estilo general de select */
.modal-content select {
  background: rgba(20, 20, 40, 0.9) !important;
  color: #fff !important;
  border: 1px solid rgba(255, 255, 255, 0.15) !important;
  border-radius: 10px !important;
  padding: 10px 40px 10px 12px !important; /* espacio para la flecha */
  font-size: 0.95rem;
  cursor: pointer;
  outline: none;
  position: relative;
  
  /* Quitar estilos nativos */
  appearance: none;
  -webkit-appearance: none;
  -moz-appearance: none;

  transition: border 0.2s ease, background 0.2s ease, box-shadow 0.2s ease;
}
.modal-content select option {
  background-color: #141428 !important;
  color: #fff !important;
  padding: 10px;
}


/* Hover y Focus */
.modal-content select:hover {
  border-color: #00D4FF;
  box-shadow: 0 0 8px rgba(0, 212, 255, 0.4);
}

.modal-content select:focus {
  border-color: #00D4FF;
  background: rgba(0, 212, 255, 0.1);
  box-shadow: 0 0 12px rgba(0, 212, 255, 0.6);
}

/* Flecha personalizada en azul neón */
.modal-content select {
  background-image: url("data:image/svg+xml;utf8,<svg fill='%2300D4FF' height='24' viewBox='0 0 24 24' width='24' xmlns='http://www.w3.org/2000/svg'><path d='M7 10l5 5 5-5z'/></svg>");
  background-repeat: no-repeat;
  background-position: right 12px center;
  background-size: 16px 16px;
}

/* Opciones desplegadas */
.modal-content select option {
  background: #141428;   /* Fondo más oscuro */
  color: #fff;
  padding: 10px;
}

/* Ajustar el contenido para que no quede debajo del sidebar */
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

  /* 🔹 nuevo */
  margin-left: 240px; 
  transition: margin-left 0.4s ease-in-out;
}

/* Cuando el sidebar esté colapsado */
.sidebar.collapsed ~ .main-content {
  margin-left: 80px;
}

    /* Animación suave personalizada */
  .collapse-custom {
    max-height: 0;
    opacity: 0;
    overflow: hidden;
    transition: max-height 0.5s ease, opacity 0.5s ease;
  }

  .collapse-custom.show {
    max-height: 500px; /* Ajustable según el contenido */
    opacity: 1;
  }
  .glass-card {
    background: rgba(255, 255, 255, 0.13);
    border: 1.5px solid rgba(0,212,255,0.18);
    border-radius: 22px;
    backdrop-filter: blur(18px);
    box-shadow: 0 12px 40px rgba(0,212,255,0.13), 0 4px 16px rgba(0,0,0,0.13);
    z-index: 1;
    padding: 38px 32px 32px 32px;
    text-align: center;
    position: relative;
    transition: box-shadow 0.2s;
    max-width: 600px;
    margin: 0 auto 24px auto;
  }
  .glass-card h2 {
    color: #00D4FF;
    text-align: center;
    font-weight: 700;
    margin-bottom: 18px;
    letter-spacing: 1px;
  }
  .btn-info, .btn-info:focus {
    background: linear-gradient(90deg, #00D4FF 0%, #1D2B64 100%);
    border: none;
    color: #fff;
    font-weight: 600;
    font-size: 1.08rem;
    box-shadow: 0 2px 12px rgba(0,212,255,0.10);
    transition: background 0.18s, color 0.18s, transform 0.18s;
  }
  .btn-info:hover {
    background: #fff;
    color: #00D4FF;
    transform: translateY(-2px) scale(1.04);
  }
  .form-control:focus, .form-select:focus {
    border-color: #00D4FF;
    box-shadow: 0 0 0 2px rgba(0,212,255,0.18);
    background: rgba(255,255,255,0.09);
    color: #fff;
  }
  .logo-register {
    width: 54px;
    height: 54px;
    object-fit: contain;
    margin-bottom: 10px;
    background: transparent;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0,212,255,0.13);
  }
  select, select option {
    color: #222 !important; /* O el color que prefieras para contraste */
    background: #fff !important; /* Opcional: mejora la visibilidad del desplegable */
  }
  .form-select, .form-select option {
    color: #222 !important;
    background: #fff !important;
  }
  .filtro-container {
    background: rgba(255, 255, 255, 0.15);
    border: 1px solid #fff;
    border-radius: 12px;
    padding: 15px 20px;
    margin-bottom: 20px;
    backdrop-filter: blur(5px);
    text-align: center;
  }

  .filtro-container h3 {
    margin-bottom: 10px;
    color: #fff;
  }

  .filtro-container form {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    justify-content: center;
    align-items: center;
  }

  .filtro-container input,
  .filtro-container button,
  .filtro-container .btn-reset {
    padding: 8px 12px;
    border-radius: 8px;
    border: none;
  }

  .filtro-container button {
    background: #4cafef;
    color: #fff;
    cursor: pointer;
  }

  .filtro-container .btn-reset {
    background: #f44336;
    color: #fff;
    text-decoration: none;
  }
  .filtro-container {
    background: rgba(255, 255, 255, 0.07);
    border: 1.5px solid rgba(0,212,255,0.2);
    border-radius: 18px;
    padding: 20px 25px;
    margin: 0 auto 25px auto;
    max-width: 700px;
    backdrop-filter: blur(10px);
    text-align: center;
    box-shadow: 0 12px 40px rgba(0,212,255,0.12), 0 4px 16px rgba(0,0,0,0.15);
    transition: transform 0.2s ease, box-shadow 0.2s ease;
  }
  .filtro-container:hover {
    transform: translateY(-3px);
    box-shadow: 0 16px 48px rgba(0,212,255,0.18);
  }

  .filtro-container h2 {
    color: #00D4FF;
    font-weight: 700;
    margin-bottom: 18px;
    letter-spacing: 1px;
  }

  .filtro-container form {
    display: flex;
    flex-wrap: wrap;
    gap: 15px;
    justify-content: center;
    align-items: center;
  }

  .filtro-container input[type="date"] {
    padding: 10px 14px;
    border-radius: 10px;
    border: none;
    background: rgba(255, 255, 255, 0.12);
    color: #fff;
    font-size: 0.95rem;
    transition: background 0.3s ease, box-shadow 0.3s ease;
  }
  .filtro-container input[type="date"]:focus {
    outline: none;
    background: rgba(255,255,255,0.18);
    box-shadow: 0 0 8px #00D4FF;
  }

  .filtro-container button,
  .filtro-container .btn-reset {
    padding: 10px 16px;
    border-radius: 10px;
    font-size: 0.95rem;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 6px;
  }

  .filtro-container button {
    background: linear-gradient(90deg, #00D4FF 0%, #1D2B64 100%);
    color: #fff;
  }
  .filtro-container button:hover {
    background: #fff;
    color: #00D4FF;
  }

  .filtro-container .btn-reset {
    background: #f44336;
    color: #fff;
    text-decoration: none;
  }
  .filtro-container .btn-reset:hover {
    background: #d32f2f;
  }
</style>
<div id="particles-js"></div>
<div class="dashboard-container">
<?php include 'sidebar.php'; ?>
 <!-- Contenido principal -->
<div class="main-content">
  <!-- Formulario de registro -->
<div class="form-container glass-card">
  <img src="img/logo 1.png" alt="Logo GastoSimple" class="logo-register" style="width:54px;height:54px;margin-bottom:10px;">
  <h2>Registrar Gasto o Ingreso</h2>
  <form id="form-registro" action="includes/insertar_transaccion.php" method="POST">
    <div class="mb-3 text-start">
      <label for="tipo" class="form-label">Tipo:</label>
      <select name="tipo" id="tipo" class="form-select" required onchange="filtrarCategorias()">
        <option value="">-- Selecciona --</option>
        <option value="ingreso">Ingreso</option>
        <option value="gasto">Gasto</option>
      </select>
    </div>
    <div class="mb-3 text-start">
      <label for="fecha" class="form-label">Fecha:</label>
      <input type="date" name="fecha" class="form-control" required>
    </div>
    <div class="mb-3 text-start">
      <label for="monto" class="form-label">Monto:</label>
      <input type="number" name="monto" class="form-control" step="0.01" required>
    </div>
    <div class="mb-3 text-start">
      <label for="categoria" class="form-label">Categoría:</label>
      <select name="categoria" id="categoria" class="form-select" required onchange="mostrarCampoNuevaCategoria(this)">
        <option value="">-- Selecciona categoría --</option>
        <?php foreach ($categorias as $cat): ?>
          <option value="<?= $cat['id'] ?>" data-tipo="<?= $cat['tipo'] ?>">
            <?= htmlspecialchars(ucfirst($cat['tipo']) . " - " . $cat['nombre']) ?>
          </option>
        <?php endforeach; ?>
        <option value="nueva">+ Agregar nueva categoría</option>
      </select>
    </div>
    <div id="nueva-categoria-container" style="display:none;">
      <label for="nueva_categoria" class="form-label">Nueva Categoría:</label>
      <input type="text" name="nueva_categoria" id="nueva_categoria" class="form-control">
    </div>
    <div class="mb-3 text-start">
      <label for="descripcion" class="form-label">Descripción:</label>
      <textarea name="descripcion" class="form-control" rows="2"></textarea>
    </div>
    <!-- NUEVA SECCIÓN DE CONFIGURACIÓN RECURRENTE -->
    <div class="form-check form-switch my-3 text-start">
      <input class="form-check-input" type="checkbox" role="switch" id="recurrente" name="recurrente">
      <label class="form-check-label fw-bold text-white" for="recurrente">
        <i class="bi bi-arrow-repeat me-1"></i> Registrar automáticamente
      </label>
    </div>
    <div id="config-recurrente" class="recurrente-config collapse-custom">
      <label for="frecuencia" class="form-label text-white">
        <i class="bi bi-calendar2-week-fill me-1"></i> Frecuencia:
      </label>
      <select class="form-select mb-3" name="frecuencia" id="frecuencia">
        <option value="mensual">Mensual</option>
        <option value="quincenal">Quincenal</option>
        <option value="semanal">Semanal</option>
      </select>
      <label for="dia_fijo" class="form-label text-white">
        <i class="bi bi-clock-fill me-1"></i> Día de ejecución (1-31):
      </label>
      <input type="number" class="form-control mb-3" name="dia_fijo" id="dia_fijo" min="1" max="31">
      <div class="form-check mt-2">
        <input class="form-check-input" type="checkbox" id="monto_variable" name="monto_variable">
        <label class="form-check-label text-white" for="monto_variable">
          <i class="bi bi-exclamation-circle-fill me-1"></i> Monto variable (pedir confirmación)
        </label>
      </div>
    </div>
      <button type="submit" class="btn btn-info w-100 mt-2">Guardar Registro</button>
    </form>
  </div>
  <?php if (!empty($mensajeFiltro)): ?>
    <div class="alerta-filtro"><?= htmlspecialchars($mensajeFiltro) ?></div>
  <?php endif; ?>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <div class="filtro-container glass-card">
      <h2>Filtrar Registros</h2>
      <form method="GET" action="">
        <input type="date" 
              name="fecha_inicio" 
              id="fecha_inicio" 
              value="<?= htmlspecialchars($_GET['fecha_inicio'] ?? '') ?>" 
              class="form-control"/>

        <input type="date" 
              name="fecha_fin" 
              id="fecha_fin" 
              value="<?= htmlspecialchars($_GET['fecha_fin'] ?? '') ?>" 
              class="form-control"/>

        <button type="submit" class="btn-info">
          <i class="fas fa-filter"></i> Filtrar
        </button>
        <a href="registro.php" class="btn-reset">
          <i class="fas fa-times"></i> Reinicia
        </a>
      </form>
    </div>
  <div class="tabla-container">
  <h2>Registros</h2>

  <?php if (!empty($transacciones)): ?>
    <div>
      <table class="tabla-transacciones">
        <thead>
          <tr>
            <th>Tipo</th>
            <th>Fecha</th>
            <th>Monto</th>
            <th>Categoría</th>
            <th>Descripción</th>
            <th>Acciones</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($transacciones as $fila): ?>
            <tr>
              <td><?= ucfirst($fila['tipo']) ?></td>
              <td><?= htmlspecialchars($fila['fecha']) ?></td>
              <td>$<?= number_format($fila['monto'], 2) ?></td>
              <td><?= htmlspecialchars($fila['categoria']) ?></td>
              <td><?= htmlspecialchars($fila['descripcion']) ?></td>
              <td>
                <button 
                  type="button" 
                  class="editar-btn"
                  data-id="<?= htmlspecialchars($fila['id_transaccion']) ?>"
                  data-tipo="<?= htmlspecialchars($fila['tipo']) ?>"
                  data-fecha="<?= htmlspecialchars($fila['fecha']) ?>"
                  data-monto="<?= htmlspecialchars($fila['monto']) ?>"
                  data-categoria="<?= htmlspecialchars($fila['categoria']) ?>"
                  data-descripcion="<?= htmlspecialchars($fila['descripcion']) ?>"
                >Editar</button>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php else: ?>
    <p>No hay datos registrados aún.</p>
  <?php endif; ?>
</div>
<!-- Modal Editar global-->
<div id="modal-editar" class="modal-overlay">
  <div class="modal-content">
    <span class="modal-close" onclick="cerrarModalEditar()">&times;</span>
    <h2>Editar Transacción</h2>
    <form id="form-editar" action="includes/editar_transaccion.php" method="POST">
      <input type="hidden" id="edit-id" name="id">
      <label for="edit-tipo">Tipo:</label>
      <select name="tipo" id="edit-tipo" required>
        <option value="ingreso">Ingreso</option>
        <option value="gasto">Gasto</option>
      </select>
      <label for="edit-fecha">Fecha:</label>
      <input type="date" name="fecha" id="edit-fecha" required>
      <label for="edit-monto">Monto:</label>
      <input type="number" name="monto" id="edit-monto" step="0.01" required>
      <label for="edit-categoria">Categoría:</label>
      <select name="categoria" id="edit-categoria" required>
        <?php foreach ($categorias as $cat): ?>
          <option value="<?= $cat['id'] ?>"><?= htmlspecialchars(ucfirst($cat['tipo']) . " - " . $cat['nombre']) ?></option>
        <?php endforeach; ?>
      </select>
      <label for="edit-descripcion">Descripción:</label>
      <textarea name="descripcion" id="edit-descripcion" rows="2"></textarea>
      <button type="submit">Guardar Cambios</button>
      <button type="button" id="eliminar-transaccion">Eliminar Transacción</button>
    </form>
  </div>
</div>
<script>
document.getElementById('recurrente').addEventListener('change', function () {
  const config = document.getElementById('config-recurrente');
  if (this.checked) {
    config.classList.add('show');
  } else {
    config.classList.remove('show');
  }
});
</script>
<script>
function toggleRecurrente() {
  const container = document.getElementById('config-recurrente');
  const checked = document.getElementById('recurrente').checked;
  container.style.display = checked ? 'block' : 'none';
}
</script>
<script>
particlesJS("particles-js", {
  particles: {
    number: { value: 80, density: { enable: true, value_area: 800 } },
    color: { value: "#00D4FF" },
    shape: { type: "circle" },
    opacity: { value: 0.5, anim: { enable: true, speed: 1, opacity_min: 0.1 } },
    size: { value: 5, random: true, anim: { enable: true, speed: 40, size_min: 0.1 } },
    line_linked: { enable: true, distance: 150, color: "#ffffff", opacity: 0.4, width: 1 },
    move: { enable: true, speed: 6, out_mode: "out" }
  },
  interactivity: {
    detect_on: "canvas",
    events: {
      onhover: { enable: true, mode: "repulse" },
      onclick: { enable: true, mode: "push" }
    }
  },
  retina_detect: true
});
//Categorías dinámicas
function filtrarCategorias() {
  const tipo = document.getElementById('tipo').value;
  const select = document.getElementById('categoria');
  select.querySelectorAll('option').forEach(op => {
    if (op.value === '') return;
    if (op.value === 'nueva') { op.style.display = 'block'; return; }
    op.style.display = op.dataset.tipo === tipo ? 'block' : 'none';
  });
  select.value = ''; document.getElementById('nueva-categoria-container').style.display = 'none';
}
function mostrarCampoNuevaCategoria(select) {
  document.getElementById('nueva-categoria-container').style.display = select.value === 'nueva' ? 'block' : 'none';
}
// Modal Editar
document.querySelectorAll('.editar-btn').forEach(button => {
  button.addEventListener('click', function() {
    document.getElementById('edit-id').value = this.dataset.id;
    document.getElementById('edit-tipo').value = this.dataset.tipo;
    document.getElementById('edit-fecha').value = this.dataset.fecha;
    document.getElementById('edit-monto').value = this.dataset.monto;
    document.getElementById('edit-categoria').value = this.dataset.categoria;
    document.getElementById('edit-descripcion').value = this.dataset.descripcion;
    document.getElementById('modal-editar').classList.add('active');
  });
});
function cerrarModalEditar() {
  document.getElementById('modal-editar').classList.remove('active');
}
document.getElementById('modal-editar').addEventListener('click', function(e) {
  if (e.target === this) cerrarModalEditar();
});
document.addEventListener('keydown', function(e) {
  if (e.key === 'Escape') cerrarModalEditar();
});
document.getElementById('eliminar-transaccion').addEventListener('click', function() {
  const id = document.getElementById('edit-id').value;
  if (confirm('¿Seguro de eliminar?')) {
    fetch('includes/eliminar_transaccion.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ id: id })
    })
    .then(res => res.json())
    .then(data => {
      if (data.success) {
        cerrarModalEditar(); location.reload();
      } else {
        alert('Error: ' + data.error);
      }
    }).catch(err => alert('Error: ' + err));
  }
});
</script>

<?php include 'includes/footer.php'; ?>