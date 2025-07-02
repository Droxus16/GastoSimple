#  GastoSimple — Software web para la gestión de finanzas personales

**GastoSimple** es un software web desarrollado como parte del programa **ADSO (Análisis y Desarrollo de Software)** del SENA — diseñado para **gestionar finanzas personales** de forma simple, visual y segura.

---

##  Funcionalidades Principales

 **Registro y Autenticación de Usuarios**  
- Registro seguro con validación y preguntas secretas.  
- Login con roles diferenciados (usuario estándar y administrador).

 **Dashboard Financiero Interactivo**  
- Visualiza ingresos, gastos y metas de ahorro.
- Gráficas dinámicas (Chart.js).
- Alertas automáticas de saldo bajo o ingresos bajos.
- Panel drag & drop (GridStack.js) totalmente personalizable.

 **Módulo de Ingresos y Gastos**  
- CRUD de transacciones.
- Filtrado y búsqueda avanzada.
- Exportación de reportes individuales.

 **Metas de Ahorro**  
- Crea metas de ahorro y realiza aportes periódicos.
- Visualiza el progreso.

 **Reportes Avanzados (Administrador)**  
- Reportes globales de todos los usuarios.
- Filtro por categorías y usuarios.
- Exportación en PDF o Excel (PhpSpreadsheet, Mpdf).

 **Módulo de PQRS**  
- Los usuarios pueden enviar Peticiones, Quejas, Reclamos o Sugerencias.
- Retroalimentación para mejorar el sistema.

 **Notificaciones y Alertas**  
- Sistema de notificaciones contextual.
- Alertas de desempeño financiero (ingresos/saldo mínimo).

 **Interfaz Moderna y Responsiva**  
- Animaciones de fondo con Particles.js.
- Efectos AOS para secciones informativas.
- Totalmente adaptable a dispositivos móviles y escritorio.

 **Términos y Condiciones Detallados**  
- Página dedicada con cláusulas realistas adaptadas a la legislación colombiana.

---

## Tecnologías y Herramientas

###  Frontend
- HTML5, CSS3, JavaScript (ES6)
- Chart.js (gráficas dinámicas)
- GridStack.js (panel interactivo)
- Bootstrap Icons
- jQuery
- Particles.js
- AOS.js (animaciones on scroll)

###  Backend
- PHP 8+
- Librerías:
  - PhpSpreadsheet (Excel y PDF)
  - Mpdf
- MySQL (modelada en Workbench)

###  Otros
- Visual Paradigm (diagramación UML y ER)
- Git + GitHub para control de versiones

---

##  Requisitos No Funcionales Implementados

- Alta disponibilidad y rendimiento en servidor local.
- Seguridad básica: validación de datos, protección de sesión, roles.
- Interfaz intuitiva, responsive y de bajo consumo de recursos.
- Zona horaria y formato de fecha ajustado a Colombia.
- Documentación técnica y manual de usuario adjunto en `/docs`.

---

##  Créditos

- **Autor:** Daniel Felipe Ramos Salazar  
- **Formación:** ADSO SENA  
- **Contacto:** [email@example.com]  

---

##  Licencia

Este proyecto es de uso académico y personal. Puedes modificarlo bajo las condiciones establecidas en los [Términos y Condiciones](./terminos.php).

---

¡Gracias por apoyar **GastoSimple**!
