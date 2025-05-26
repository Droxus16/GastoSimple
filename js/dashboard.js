document.addEventListener('DOMContentLoaded', () => {
  const ctx = document.getElementById('graficoFinanzas').getContext('2d');
  const grafico = new Chart(ctx, {
    type: 'bar',
    data: {
      labels: ['Ingresos', 'Gastos'],
      datasets: [{
        label: 'Total',
        data: [0, 0],
        backgroundColor: ['#4CAF50', '#F44336']
      }]
    },
    options: {
      responsive: true,
      plugins: {
        legend: { display: false }
      }
    }
  });

  // Inicializar Gridstack
  GridStack.init();

  // Función para filtrar y actualizar datos
  window.filtrar = function (periodo) {
    fetch('includes/filtrar_datos.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: `periodo=${encodeURIComponent(periodo)}`
    })
    .then(res => res.json())
    .then(datos => {
      grafico.data.datasets[0].data = [datos.ingresos, datos.gastos];
      grafico.update();
      document.getElementById('ingresos').innerText = `$${datos.ingresos.toFixed(2)}`;
      document.getElementById('gastos').innerText = `$${datos.gastos.toFixed(2)}`;
      document.getElementById('ahorro').innerText = `$${datos.ahorro.toFixed(2)}`;
    })
    .catch(error => console.error('Error al filtrar datos:', error));
  }

  // Carga inicial
  filtrar('mes');
});
