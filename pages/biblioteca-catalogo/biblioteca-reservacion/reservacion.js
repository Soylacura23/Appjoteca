/* ================================================================
   reservacion.js — Formulario de Reservación
   AppJoteca v2.0
   ================================================================ */

   (function () {
    'use strict';

    var form = document.getElementById('reservation-form');
    var reasonInput = document.getElementById('reason');
    var dateInput = document.getElementById('return-date');
    var messageBox = document.getElementById('message');
    var cancelBtn = document.getElementById('cancel-btn');

    if (!form || !dateInput) return;

    // ── Fechas límite ──
    var today = new Date();
    var maxDate = new Date();
    maxDate.setMonth(maxDate.getMonth() + 2);

    dateInput.min = formatDate(today);
    dateInput.max = formatDate(maxDate);

    function formatDate(date) {
        var y = date.getFullYear();
        var m = String(date.getMonth() + 1).padStart(2, '0');
        var d = String(date.getDate()).padStart(2, '0');
        return y + '-' + m + '-' + d;
    }

    function enviar(accion, campos) {
        var body = new FormData();
        body.append('accion', accion);
        Object.keys(campos).forEach(function (key) {
            body.append(key, campos[key]);
        });
        return fetch(API, { method: 'POST', body: body, credentials: 'same-origin' })
            .then(function (res) { return res.json(); });
    }

    if (form) {
        var reasonInput = document.getElementById('reason');
        var dateInput = document.getElementById('return-date');
        var messageBox = document.getElementById('message');
        var cancelBtn = document.getElementById('cancel-btn');
        var idLibro = form.getAttribute('data-id-libro');
        var qtyInputEl = document.getElementById('qty-input');
        var qtyMinus = document.getElementById('qty-minus');
        var qtyPlus = document.getElementById('qty-plus');

        function clampQty(val) {
            if (!qtyInputEl) return 1;
            var max = parseInt(qtyInputEl.getAttribute('data-max') || qtyInputEl.max || '1', 10);
            if (isNaN(max) || max < 1) max = 1;
            var n = parseInt(val, 10);
            if (isNaN(n) || n < 1) n = 1;
            if (n > max) n = max;
            qtyInputEl.value = n;
            return n;
        }

        if (qtyInputEl) {
            qtyInputEl.addEventListener('change', function () { clampQty(qtyInputEl.value); });
            qtyInputEl.addEventListener('blur', function () { clampQty(qtyInputEl.value); });
        }
        if (qtyMinus) {
            qtyMinus.addEventListener('click', function () {
                if (qtyInputEl) clampQty(parseInt(qtyInputEl.value, 10) - 1);
            });
        }
        if (qtyPlus) {
            qtyPlus.addEventListener('click', function () {
                if (qtyInputEl) clampQty(parseInt(qtyInputEl.value, 10) + 1);
            });
        }

        var today = new Date();
        var maxDate = new Date();
        maxDate.setMonth(maxDate.getMonth() + 2);

        dateInput.min = formatDate(today);
        dateInput.max = formatDate(maxDate);

    function showMessage(text, type) {
        messageBox.textContent = text;
        messageBox.className = 'message ' + type;
    }

    function hideMessage() {
        messageBox.className = 'message';
        messageBox.textContent = '';
    }

    // ── Submit ──
    form.addEventListener('submit', function (e) {
        e.preventDefault();
        hideMessage();

        var returnDate = dateInput.value;
        if (!returnDate) {
            showMessage('Por favor selecciona una fecha de devolución.', 'error');
            return;
        }

            if (returnDate < dateInput.min) {
                showMessage('La fecha no puede ser anterior a hoy.', 'error');
                return;
            }

            if (returnDate > dateInput.max) {
                showMessage('El plazo máximo es de 2 meses.', 'error');
                return;
            }

            var cantidad = 1;
            if (qtyInputEl) {
                var max = parseInt(qtyInputEl.getAttribute('data-max') || qtyInputEl.max || '1', 10);
                cantidad = parseInt(qtyInputEl.value, 10);
                if (isNaN(cantidad) || cantidad < 1) cantidad = 1;
                if (!isNaN(max) && cantidad > max) cantidad = max;
            }

            var submitBtn = form.querySelector('button[type="submit"]');
            submitBtn.disabled = true;

            enviar('crear', {
                id_libro: idLibro,
                razon: reasonInput.value.trim(),
                fecha_devolucion: returnDate,
                cantidad: cantidad
            }).then(function (data) {
                if (data.ok) {
                    showMessage(data.mensaje, 'success');
                    setTimeout(function () { window.location.reload(); }, 900);
                } else {
                    showMessage(data.error || 'No se pudo enviar la solicitud.', 'error');
                    submitBtn.disabled = false;
                }
            }).catch(function () {
                showMessage('No se pudo conectar con el servidor.', 'error');
                submitBtn.disabled = false;
            });
        });

        if (cancelBtn) {
            cancelBtn.addEventListener('click', function () {
                window.history.back();
            });
        }
    }

    if (cancelReservaBtn) {
        cancelReservaBtn.addEventListener('click', function () {
            var idReserva = cancelReservaBtn.getAttribute('data-id-reserva');
            if (!idReserva) return;

            Swal.fire({
                icon: 'question',
                title: '¿Cancelar solicitud?',
                text: 'Dejará de aparecer como pendiente para el bibliotecario.',
                showCancelButton: true,
                confirmButtonText: 'Sí, cancelar',
                cancelButtonText: 'Volver',
                background: '#121212',
                color: '#e2e2e2',
                confirmButtonColor: '#f2ca50'
            }).then(function (result) {
                if (!result.isConfirmed) return;

                enviar('cancelar', { id_reserva: idReserva }).then(function (data) {
                    if (data.ok) {
                        alerta('success', 'Listo', data.mensaje);
                        setTimeout(function () { window.location.reload(); }, 900);
                    } else {
                        alerta('error', 'No se pudo cancelar', data.error || '');
                    }
                }).catch(function () {
                    alerta('error', 'Error', 'No se pudo conectar con el servidor');
                });
            });
        });
    }
})();