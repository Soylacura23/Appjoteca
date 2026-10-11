/* reservacion.js — Formulario de Reservación */

(function () {
    'use strict';

    var API = '/Appjoteca/backend/controllers/reservas_estudiante.php';

    var form              = document.getElementById('reservation-form');
    var cancelBtn         = document.getElementById('cancel-btn');
    var cancelReservaBtn  = document.getElementById('btn-cancelar-reserva');

    // ═══════════════════════════════════════════════════════
    // 1. Botón CANCELAR solicitud pendiente (fuera del form)
    // ═══════════════════════════════════════════════════════
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
                        mostrarAlerta('success', 'Listo', data.mensaje, function () {
                            window.location.reload();
                        });
                    } else {
                        mostrarAlerta('error', 'No se pudo cancelar', data.error || '');
                    }
                }).catch(function (error) {
                    mostrarAlerta('error', 'Error', error.message || 'No se pudo conectar con el servidor');
                });
            });
        });
    }

    // ═══════════════════════════════════════════════════════
    // 2. Formulario de reservación (solo si existe)
    // ═══════════════════════════════════════════════════════
    if (!form) return;

    var idLibro     = form.getAttribute('data-id-libro');
    var reasonInput = document.getElementById('reason');
    var dateInput   = document.getElementById('return-date');
    var messageBox  = document.getElementById('message');
    var qtyInputEl  = document.getElementById('qty-input');
    var qtyMinus    = document.getElementById('qty-minus');
    var qtyPlus     = document.getElementById('qty-plus');

    // ── Fechas límite ──
    var firstBusinessDay = new Date();
    firstBusinessDay.setHours(0, 0, 0, 0);
    if (firstBusinessDay.getDay() === 6) {
        firstBusinessDay.setDate(firstBusinessDay.getDate() + 2);
    } else if (firstBusinessDay.getDay() === 0) {
        firstBusinessDay.setDate(firstBusinessDay.getDate() + 1);
    }
    var maxDate = new Date(firstBusinessDay);
    maxDate.setDate(maxDate.getDate() + 11);

    function formatDate(date) {
        var y = date.getFullYear();
        var m = String(date.getMonth() + 1).padStart(2, '0');
        var d = String(date.getDate()).padStart(2, '0');
        return y + '-' + m + '-' + d;
    }

    if (dateInput) {
        dateInput.min = formatDate(firstBusinessDay);
        dateInput.max = formatDate(maxDate);
    }

    // ── Contador de ejemplares ──
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
        qtyInputEl.addEventListener('blur',   function () { clampQty(qtyInputEl.value); });
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

    // ── Botón cancelar del formulario → atrás ──
    if (cancelBtn) {
        cancelBtn.addEventListener('click', function () {
            window.history.back();
        });
    }

    // ── Mensajes inline ──
    function showMessage(text, type) {
        if (!messageBox) return;
        messageBox.textContent = text;
        messageBox.className = 'message ' + type;
    }
    function hideMessage() {
        if (!messageBox) return;
        messageBox.className = 'message';
        messageBox.textContent = '';
    }

    function mostrarAlerta(icon, title, text, onClose) {
        if (icon === 'success' && window.Swal) {
            window.Swal.fire({
                icon: icon,
                title: title,
                text: text,
                showConfirmButton: true,
                confirmButtonText: 'Aceptar',
                allowOutsideClick: false,
                allowEscapeKey: false,
                background: '#121212',
                color: '#e2e2e2',
                confirmButtonColor: '#f2ca50'
            }).then(function (result) {
                if (result.isConfirmed && onClose) onClose();
            });
            return;
        }

        if (typeof window.alerta !== 'function') {
            if (onClose) onClose();
            return;
        }

        try {
            var alertResult = window.alerta(icon, title, text);
            if (alertResult && typeof alertResult.then === 'function') {
                alertResult.then(function () {
                    if (onClose) onClose();
                }).catch(function (error) {
                    console.error('No se pudo completar la alerta:', error);
                    if (onClose) onClose();
                });
            } else if (onClose) {
                onClose();
            }
        } catch (error) {
            console.error('No se pudo mostrar la alerta:', error);
            if (onClose) onClose();
        }
    }

    // ── Helper para POST ──
    function enviar(accion, campos) {
        var csrfMeta = document.querySelector('meta[name="csrf-token"]');
        var body = new FormData();
        body.append('accion', accion);
        body.append('csrf_token', csrfMeta ? csrfMeta.content : '');
        Object.keys(campos).forEach(function (key) {
            body.append(key, campos[key]);
        });
        return fetch(API, { method: 'POST', body: body, credentials: 'same-origin' })
            .then(function (res) {
                return res.text().then(function (text) {
                    var data;
                    try {
                        data = JSON.parse(text);
                    } catch (error) {
                        throw new Error('El servidor devolvió una respuesta no válida.');
                    }
                    if (!res.ok && !data.error) {
                        throw new Error(data.message || 'No se pudo completar la solicitud.');
                    }
                    return data;
                });
            });
    }

    // ── Submit ──
    form.addEventListener('submit', function (e) {
        e.preventDefault();
        hideMessage();

        var returnDate = dateInput ? dateInput.value : '';
        if (!returnDate) {
            showMessage('Por favor selecciona una fecha de devolución.', 'error');
            return;
        }
        if (returnDate < dateInput.min) {
            showMessage('La fecha no puede ser anterior al próximo día hábil.', 'error');
            return;
        }
        if (returnDate > dateInput.max) {
            showMessage('El plazo máximo es de 10 días desde el primer día hábil, con un día de tolerancia.', 'error');
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
        if (submitBtn) submitBtn.disabled = true;

        enviar('crear', {
            id_libro: idLibro,
            razon: reasonInput ? reasonInput.value.trim() : '',
            fecha_devolucion: returnDate,
            cantidad: cantidad
        }).then(function (data) {
            if (data.ok) {
                showMessage(data.mensaje, 'success');
                mostrarAlerta('success', 'Solicitud enviada', data.mensaje, function () {
                    window.location.reload();
                });
            } else {
                showMessage(data.error || 'No se pudo enviar la solicitud.', 'error');
                if (submitBtn) submitBtn.disabled = false;
            }
        }).catch(function (error) {
            showMessage(error.message || 'No se pudo conectar con el servidor.', 'error');
            if (submitBtn) submitBtn.disabled = false;
        });
    });

})();