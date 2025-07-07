document.addEventListener('DOMContentLoaded', () => {
  const listaNotificaciones = document.getElementById('lista-notificaciones');
  const badgeAlerta = document.getElementById('badge-alerta');
  const iconoCampana = document.getElementById('icono-campana');
  const panelNotificaciones = document.getElementById('panel-notificaciones');
  const body = document.body;
  const totalIngresos = parseFloat(body.dataset.totalIngresos) || 0;
  const totalGastos = parseFloat(body.dataset.totalGastos) || 0;
  const ingresoMinimo = parseFloat(body.dataset.ingresoMinimo) || 0;
  // Depuración: mostrar valores en consola
  console.log("Total ingresos:", totalIngresos);
  console.log("Total gastos:", totalGastos);
  console.log("Ingreso mínimo:", ingresoMinimo);
  const notificaciones = [];
  if (totalIngresos === 0) {
    notificaciones.push("⚠️ Aún no registras ingresos este mes.");
  }
  if (totalGastos === 0) {
    notificaciones.push("⚠️ Aún no registras gastos este mes.");
  }
  if (totalGastos > totalIngresos) {
    notificaciones.push("❗ Tus gastos superan tus ingresos. Revisa tu presupuesto.");
  }
  if (ingresoMinimo > 0 && totalIngresos < ingresoMinimo) {
    notificaciones.push(`⚠️ Tus ingresos están por debajo de tu mínimo configurado ($${ingresoMinimo.toFixed(2)}).`);
  }
  if (listaNotificaciones) {
    listaNotificaciones.innerHTML = '';
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
  }
  // Toggle del panel de notificaciones
  window.toggleNotificaciones = function () {
    if (!panelNotificaciones) return;
    if (panelNotificaciones.style.display === 'flex') {
      panelNotificaciones.style.display = 'none';
    } else {
      panelNotificaciones.style.display = 'flex';
      iconoCampana.classList.remove('shake');
      badgeAlerta.style.display = 'none';
    }
  };
  //Cierre si se hace clic fuera
  document.addEventListener('click', function (e) {
    const boton = document.getElementById('btn-notificaciones');
    if (
      panelNotificaciones.style.display === 'flex' &&
      !panelNotificaciones.contains(e.target) &&
      !boton.contains(e.target)
    ) {
      panelNotificaciones.style.display = 'none';
    }
  });
});