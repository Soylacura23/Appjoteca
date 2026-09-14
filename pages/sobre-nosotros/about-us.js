document.addEventListener('DOMContentLoaded', () => {

    const feedbackForm = document.getElementById('feedbackForm');

    if (feedbackForm) {
        feedbackForm.addEventListener('submit', async (e) => {
            e.preventDefault(); 

            
            const nombre = document.getElementById('nombre').value.trim();
            const correo = document.getElementById('correo').value.trim();
            const tipo = document.getElementById('tipo').value;
            const mensaje = document.getElementById('mensaje').value.trim();
            const textarea = document.getElementById('mensaje');
            const privacidad = document.getElementById('privacidad').checked;
            const MAX_CARACTERES = 1000;
            const contador = document.getElementById('contador'); 

            textarea.addEventListener('input', () => {
                const longitudActual = textarea.value.length;
                contador.innerText = `${longitudActual} / ${MAX_CARACTERES} caracteres`;
                
                if (longitudActual >= MAX_CARACTERES) {
                    contador.style.color = 'red';
                } else {
                    contador.style.color = 'inherit';
                }
            });

            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

            let isValid = true;
            let errorMsg = "";

            if (nombre === "" || correo === "" || tipo === "" || mensaje === "") {
                isValid = false;
                errorMsg = "Por favor, completa todos los campos requeridos.";
            } else if (!emailRegex.test(correo)) {
                isValid = false;
                errorMsg = "Por favor, ingresa un correo electrónico válido.";
            } else if (!privacidad) {
                isValid = false;
                errorMsg = "Debes aceptar la política de privacidad para enviar tu comentario.";
            } else if (mensaje.length === 0) {
                isValid = false;
                errorMsg = "El comentario no puede estar vacío.";
            } else if (mensaje.length > MAX_CARACTERES) {
                isValid = false;
                errorMsg = `El comentario no puede superar los ${MAX_CARACTERES} caracteres. (Llevas ${comentario.length})`;
            }

            if (!isValid) {
                window.alerta('warning', 'Campos incompletos', errorMsg);
                return; 
            }

            const honeypot = feedbackForm.querySelector('input[name="website_url_hp"]').value;
            if (honeypot !== "") {
                console.warn("Spam detectado.");
                return; 
            }

            const submitBtn = feedbackForm.querySelector('button[type="submit"]');
            const formData = new FormData(feedbackForm);
            
            submitBtn.disabled = true;
            submitBtn.textContent = "Enviando...";

            try {
                const response = await fetch('../../backend/admin/comentarios-send.php', {
                    method: 'POST',
                    body: formData
                });

                if (response.ok) {
                    feedbackForm.reset();
                    window.alerta('success', '¡Gracias por tus comentarios!', 'Hemos recibido tu mensaje correctamente.');
                } else {
                    window.alerta('error', response.message);
                }
            } catch (error) {
                console.error('Error en la petición:', error);
                window.alerta('error', 'Error de conexión', 'No pudimos conectar con el servidor. Revisa tu conexión a internet.');
            } finally {
                submitBtn.disabled = false;
                submitBtn.textContent = "Enviar comentario";
            }
        });
    }
});