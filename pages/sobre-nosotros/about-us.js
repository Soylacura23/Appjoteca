document.addEventListener('DOMContentLoaded', () => {
    const isMobile = window.matchMedia('(max-width: 768px)').matches;
    const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    // VANTA.JS — Fondo animado
    if (typeof VANTA !== 'undefined' && document.getElementById('vanta-bg') && !prefersReducedMotion) {
        VANTA.NET({
            el: "#vanta-bg",
            mouseControls: !isMobile,
            touchControls: !isMobile,
            gyroControls: false,
            minHeight: 200.00,
            minWidth: 200.00,
            scale: 1.00,
            scaleMobile: 0.75,
            color: 0xf2ca50,
            backgroundColor: 0x050505,
            points: isMobile ? 7.00 : 12.00,
            maxDistance: isMobile ? 18.00 : 22.00,
            spacing: isMobile ? 22.00 : 18.00,
            showDots: !isMobile
        });
    }

    // AOS — Animaciones al scroll
    if (typeof AOS !== 'undefined') {
        AOS.init({
            duration: isMobile ? 500 : 800,
            once: true,
            offset: 80,
            easing: 'ease-out-cubic',
            debounceDelay: 100,
            throttleDelay: 120
        });
    }

    // FORMULARIO
    const feedbackForm = document.getElementById('feedbackForm');

    if (feedbackForm) {
        const textarea = document.getElementById('mensaje');
        const contador = document.getElementById('contador');
        const MAX_CARACTERES = 1000;

        // Contador de caracteres
        if (textarea && contador) {
            textarea.addEventListener('input', () => {
                const longitudActual = textarea.value.length;
                contador.innerText = `${longitudActual} / ${MAX_CARACTERES} caracteres`;
                if (longitudActual >= MAX_CARACTERES) {
                    contador.style.color = '#ff4d4d';
                } else if (longitudActual > MAX_CARACTERES * 0.8) {
                    contador.style.color = '#f2ca50';
                } else {
                    contador.style.color = '';
                }
            });
        }

        feedbackForm.addEventListener('submit', async (e) => {
            e.preventDefault();

            const nombre = document.getElementById('nombre').value.trim();
            const correo = document.getElementById('correo').value.trim();
            const tipo = document.getElementById('tipo').value;
            const mensaje = textarea.value.trim();
            const privacidad = document.getElementById('privacidad').checked;

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
                errorMsg = "Debes aceptar la política de privacidad.";
            } else if (mensaje.length > MAX_CARACTERES) {
                isValid = false;
                errorMsg = `El comentario no puede superar los ${MAX_CARACTERES} caracteres.`;
            }

            if (!isValid) {
                window.alerta('warning', 'Campos incompletos', errorMsg);
                return;
            }

            const auxiliaryField = feedbackForm.querySelector('input[name="middle_name"]').value;
            if (auxiliaryField !== "") {
                console.warn("Spam detectado.");
                return;
            }

            const submitBtn = feedbackForm.querySelector('button[type="submit"]');
            const originalBtnContent = submitBtn.innerHTML;
            const formData = new FormData(feedbackForm);

            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="material-symbols-outlined">hourglass_top</span> Enviando...';

            try {
                const response = await fetch('../../backend/admin/comentarios-send.php', {
                    method: 'POST',
                    body: formData
                });

                if (response.ok) {
                    feedbackForm.reset();
                    if (contador) contador.innerText = `0 / ${MAX_CARACTERES} caracteres`;
                    window.alerta('success', '¡Gracias por tus comentarios!', 'Hemos recibido tu mensaje correctamente.');
                } else {
                    window.alerta('error', 'Error al enviar', 'Inténtalo de nuevo más tarde.');
                }
            } catch (error) {
                console.error('Error en la petición:', error);
                window.alerta('error', 'Error de conexión', 'No pudimos conectar con el servidor.');
            } finally {
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalBtnContent;
            }
        });
    }

    // El estado lo gestiona JavaScript; los estilos del header viven en su componente CSS.
    const header = document.getElementById('mainHeader');
    if (header) {
        const updateHeader = () => header.classList.toggle('is-scrolled', window.scrollY > 50);
        window.addEventListener('scroll', updateHeader, { passive: true });
        updateHeader();
    }
});