document.addEventListener("DOMContentLoaded", function () {
    const correoInput = document.getElementById("correo");
    const terminosCheckbox = document.getElementById("terminos");
    const btnRegistro = document.getElementById("btnRegistro");
    const passwordInput = document.getElementById("contrasena");
    const togglePassword = document.getElementById("togglePassword");

    // Mostrar/ocultar contraseña
    togglePassword.addEventListener("change", function () {
        passwordInput.type = this.checked ? "text" : "password";
    });

    // Habilitar botón solo si acepta términos
    terminosCheckbox.addEventListener("change", function () {
        btnRegistro.disabled = !this.checked;
    });

    // Verificación AJAX del correo
    correoInput.addEventListener("input", function () {
        const correo = this.value;

        if (correo.length > 5) {
            fetch("verificar_correo.php", {
                method: "POST",
                headers: {
                    "Content-Type": "application/x-www-form-urlencoded"
                },
                body: "correo=" + encodeURIComponent(correo)
            })
            .then(res => res.text())
            .then(data => {
                if (data === "existe") {
                    correoInput.setCustomValidity("Este correo ya está registrado.");
                } else {
                    correoInput.setCustomValidity("");
                }
            });
        } else {
            correoInput.setCustomValidity("");
        }
    });
});
