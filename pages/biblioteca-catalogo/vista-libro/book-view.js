/* book-view.js — Vista de detalle del libro */

document.addEventListener('DOMContentLoaded', function () {
    'use strict';

    var API = '/Appjoteca/backend/controllers/reservas_estudiante.php';

    var btnReservar    = document.getElementById('btn-reservar');
    var btnCancelar    = document.getElementById('btn-cancelar-reserva');
    var btnAddLibrary  = document.getElementById('btn-add-library');
    var synopsisText = document.querySelector('.synopsis-text');
    var synopsisToggle = document.querySelector('.synopsis-toggle');

    function mostrarAlerta(icon, title, text, onClose) {
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

    if (synopsisText && synopsisToggle) {
        synopsisText.classList.add('is-collapsed');
        if (synopsisText.scrollHeight > synopsisText.clientHeight + 2) {
            synopsisToggle.hidden = false;
            synopsisToggle.addEventListener('click', function () {
                var expanded = synopsisToggle.getAttribute('aria-expanded') === 'true';
                synopsisToggle.setAttribute('aria-expanded', String(!expanded));
                synopsisText.classList.toggle('is-collapsed', expanded);
                synopsisToggle.textContent = expanded ? 'Ver más' : 'Ver menos';
            });
        } else {
            synopsisText.classList.remove('is-collapsed');
        }
    }

    // ── Botón Reservar: va al formulario de reservación ──
    if (btnReservar) {
        btnReservar.addEventListener('click', function () {
            var id = btnReservar.getAttribute('data-id');
            if (!id) return;
            window.location.href =
                '/Appjoteca/pages/biblioteca-catalogo/biblioteca-reservacion/reservacion.php?id=' +
                encodeURIComponent(id);
        });
    }

    // ── Botón Cancelar solicitud pendiente ──
    if (btnCancelar) {
        btnCancelar.addEventListener('click', function () {
            var idReserva = btnCancelar.getAttribute('data-id-reserva');
            if (!idReserva) return;

            Swal.fire({
                icon: 'question',
                title: '¿Cancelar solicitud?',
                text: 'La reserva dejará de estar pendiente para el bibliotecario.',
                showCancelButton: true,
                confirmButtonText: 'Sí, cancelar',
                cancelButtonText: 'Volver',
                background: '#121212',
                color: '#e2e2e2',
                confirmButtonColor: '#f2ca50'
            }).then(function (result) {
                if (!result.isConfirmed) return;

                var csrfMeta = document.querySelector('meta[name="csrf-token"]');
                var body = new FormData();
                body.append('accion', 'cancelar');
                body.append('id_reserva', idReserva);
                body.append('csrf_token', csrfMeta ? csrfMeta.content : '');

                fetch(API, { method: 'POST', body: body, credentials: 'same-origin' })
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
                    })
                    .then(function (data) {
                        if (data.ok) {
                            mostrarAlerta('success', 'Listo', data.mensaje, function () {
                                window.location.reload();
                            });
                        } else {
                            mostrarAlerta('error', 'No se pudo cancelar', data.error || data.message || 'Inténtalo de nuevo');
                        }
                    })
                    .catch(function (error) {
                        mostrarAlerta('error', 'Error', error.message || 'No se pudo conectar con el servidor');
                    });
            });
        });
    }

    // ── Agregar a mi Biblioteca (pendiente de endpoint) ──
    if (btnAddLibrary) {
        btnAddLibrary.addEventListener('click', function () {
            var id = btnAddLibrary.getAttribute('data-id');
            if (!id) return;
            console.log('[book-view] Agregar a biblioteca, id:', id);
            // TODO: conectar con el endpoint cuando exista
        });
    }
});