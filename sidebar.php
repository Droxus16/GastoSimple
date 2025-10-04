<!-- Sidebar -->
<div class="sidebar collapsed" id="sidebar">
  <!-- Botón hamburguesa -->
  <button class="hamburger" onclick="toggleSidebar()">
    <i class="bi bi-list"></i>
  </button>

  <div class="menu-content">
    <!-- Menú superior -->
    <div class="menu-top">
      <button onclick="location.href='dashboard.php'">
        <i class="bi bi-speedometer2"></i> <span class="label">Dashboard</span>
      </button>
      <button onclick="location.href='registro.php'">
        <i class="bi bi-pencil-square"></i> <span class="label">Registro</span>
      </button>
      <button onclick="location.href='metas.php'">
        <i class="bi bi-flag-fill"></i> <span class="label">Metas</span>
      </button>
    </div>

    <!-- Notificaciones -->
    <button id="btn-notificaciones" onclick="toggleNotificaciones()">
      <i class="bi bi-bell-fill" id="icono-campana"></i>
      <span class="label">Notificaciones</span>
      <span id="badge-alerta"></span>
    </button>

    <!-- Menú inferior -->
    <div class="bottom-buttons">
  <button onclick="location.href='ajustes.php'">
    <i class="bi bi-gear-fill"></i> <span class="label">Ajustes</span>
  </button>
  <button onclick="location.href='logout.php'">
    <i class="bi bi-box-arrow-right"></i> <span class="label">Salir</span>
  </button>
</div>

  </div>
</div>

<!-- Panel de notificaciones (fuera del sidebar) -->
<div id="panel-notificaciones">
  <h4>Notificaciones</h4>
  <ul id="lista-notificaciones"></ul>
</div>
