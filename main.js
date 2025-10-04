// --- TOGGLE SIDEBAR ---
function toggleSidebar() {
  const sidebar = document.getElementById("sidebar");
  const panel = document.getElementById("panel-notificaciones");
  if (sidebar) sidebar.classList.toggle("collapsed");
  // Ajuste panel al colapsar/expandir
  if (panel) {
    panel.style.left = sidebar.classList.contains("collapsed") ? "70px" : "240px";
  }
}

// --- TOGGLE NOTIFICACIONES ---
function toggleNotificaciones() {
  const panel = document.getElementById("panel-notificaciones");
  const iconoCampana = document.getElementById("icono-campana");
  const badge = document.getElementById("badge-alerta");

  if (!panel) return;
  panel.style.display = (panel.style.display === "flex") ? "none" : "flex";

  if (iconoCampana) iconoCampana.classList.remove("shake");
  if (badge) badge.style.display = "none";
}

// --- CERRAR NOTIFICACIONES AL HACER CLICK FUERA ---
document.addEventListener("click", e => {
  const panel = document.getElementById("panel-notificaciones");
  const boton = document.getElementById("btn-notificaciones");
  if (panel && panel.style.display === "flex" && !panel.contains(e.target) && !boton.contains(e.target)) {
    panel.style.display = "none";
  }
});

// --- NOTIFICACIONES DINÁMICAS ---
document.addEventListener("DOMContentLoaded", () => {
  const lista = document.getElementById("lista-notificaciones");
  const badge = document.getElementById("badge-alerta");
  const campana = document.getElementById("icono-campana");

  if (!lista) return;

  const notificaciones = [];

  const saldoActual = window.saldoActual || 0;
  const ingresosTotales = window.ingresosTotales || 0;
  const ingresoMinimo = window.ingresoMinimo || 0;
  const saldoMinimo = window.saldoMinimo || 0;
  const lista_metas = window.lista_metas || [];

  // Lógica
  if (saldoActual <= saldoMinimo && saldoMinimo > 0) notificaciones.push(`⚠️ Saldo bajo: $${saldoActual.toFixed(2)}`);
  if (ingresosTotales <= ingresoMinimo && ingresoMinimo > 0) notificaciones.push(`⚠️ Ingresos bajos: $${ingresosTotales.toFixed(2)}`);
  if (saldoActual <= 0) notificaciones.push(`⚠️ No generas ahorro este mes.`);

  const hoy = new Date();
  lista_metas.forEach(meta => {
    if (!meta.fecha_limite || !meta.monto_objetivo) return;
    const fechaLimite = new Date(meta.fecha_limite);
    const diasRestantes = Math.ceil((fechaLimite - hoy) / (1000 * 60 * 60 * 24));
    const porcentaje = meta.monto_objetivo > 0 ? (parseFloat(meta.total_aportado) / meta.monto_objetivo) * 100 : 0;

    if (diasRestantes <= 5 && porcentaje < 100) notificaciones.push(`⏳ Meta "${meta.nombre}" vence en ${diasRestantes} día(s).`);
    if (porcentaje >= 100) notificaciones.push(`✅ Meta "${meta.nombre}" alcanzada.`);
  });

  lista.innerHTML = "";
  if (notificaciones.length > 0) {
    notificaciones.forEach(msg => {
      const li = document.createElement("li");
      li.textContent = msg;
      lista.appendChild(li);
    });
    if (badge) {
      badge.textContent = notificaciones.length;
      badge.style.display = "inline-block";
    }
    if (campana) campana.classList.add("shake");
  } else {
    lista.innerHTML = "<li>✅ Sin notificaciones.</li>";
  }
});
