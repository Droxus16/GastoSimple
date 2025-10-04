// Sidebar toggle
function toggleSidebar() {
  document.getElementById("sidebar").classList.toggle("collapsed");
}

// Notificaciones toggle
function toggleNotificaciones() {
  const panel = document.getElementById("panel-notificaciones");
  const iconoCampana = document.getElementById("icono-campana");
  const badge = document.getElementById("badge-alerta");

  panel.style.display = (panel.style.display === "flex") ? "none" : "flex";
  iconoCampana.classList.remove("shake");
  badge.style.display = "none";
}

// Cerrar notificaciones al hacer click fuera
document.addEventListener("click", e => {
  const panel = document.getElementById("panel-notificaciones");
  const boton = document.getElementById("btn-notificaciones");
  if (panel && panel.style.display === "flex" && !panel.contains(e.target) && !boton.contains(e.target)) {
    panel.style.display = "none";
  }
});

// ✅ Script de notificaciones dinámicas
document.addEventListener("DOMContentLoaded", () => {
  // Variables PHP ya deben estar definidas en el HTML:
  // saldoActual, ingresosTotales, ingresoMinimo, saldoMinimo, lista_metas

  const lista = document.getElementById("lista-notificaciones");
  const badge = document.getElementById("badge-alerta");
  const campana = document.getElementById("icono-campana");

  console.log("Debug valores sidebar:", { saldoActual, ingresosTotales, ingresoMinimo, saldoMinimo });

  const notificaciones = [];

  // --- Lógica de notificaciones ---
  if (saldoActual <= saldoMinimo) 
    notificaciones.push(`⚠️ Saldo bajo: $${saldoActual.toFixed(2)}`);
  if (ingresosTotales <= ingresoMinimo) 
    notificaciones.push(`⚠️ Ingresos bajos: $${ingresosTotales.toFixed(2)}`);
  if (saldoActual <= 0) 
    notificaciones.push(`⚠️ No generas ahorro este mes.`);

  // Revisar metas
  const hoy = new Date();
  if (typeof lista_metas !== "undefined") {
    lista_metas.forEach(meta => {
      const fechaLimite = new Date(meta.fecha_limite);
      const diasRestantes = Math.ceil((fechaLimite - hoy) / (1000 * 60 * 60 * 24));
      const porcentaje = meta.monto_objetivo > 0 ? 
          (parseFloat(meta.total_aportado) / meta.monto_objetivo) * 100 : 0;

      if (diasRestantes <= 5 && porcentaje < 100) {
        notificaciones.push(`⏳ Meta "${meta.nombre}" vence en ${diasRestantes} día(s).`);
      }
      if (porcentaje >= 100) {
        notificaciones.push(`✅ Meta "${meta.nombre}" alcanzada.`);
      }
    });
  }

  // --- Renderizado ---
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
