document.addEventListener('DOMContentLoaded', function () {
    var API = '/Appjoteca/backend/controllers/reservas_estudiante.php';
    var btnReservar = document.getElementById('btn-reservar');
    var btnCancelar = document.getElementById('btn-cancelar-reserva');

    if (btnReservar) {
        btnReservar.addEventListener('click', function () {
            var id = btnReservar.getAttribute('data-id');
            if (!id) return;
            window.location.href = '/Appjoteca/pages/biblioteca-catalogo/biblioteca-reservacion/reservacion.php?id='
                + encodeURIComponent(id);
        });
    }

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

                var body = new FormData();
                body.append('accion', 'cancelar');
                body.append('id_reserva', idReserva);

                fetch(API, { method: 'POST', body: body, credentials: 'same-origin' })
                    .then(function (res) { return res.json(); })
                    .then(function (data) {
                        if (data.ok) {
                            alerta('success', 'Listo', data.mensaje);
                            setTimeout(function () { window.location.reload(); }, 900);
                        } else {
                            alerta('error', 'No se pudo cancelar', data.error || 'Inténtalo de nuevo');
                        }
                    })
                    .catch(function () {
                        alerta('error', 'Error', 'No se pudo conectar con el servidor');
                    });
            });
        });
    }
});