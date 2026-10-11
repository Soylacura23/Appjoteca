document.addEventListener('DOMContentLoaded', () => {
    const reservationsUrl = '/Appjoteca/dashboards/bibliotecario/pages/reservaciones/reservaciones.php';
    document.getElementById('btn-ver-reservaciones')?.addEventListener('click', () => {
        window.location.href = reservationsUrl;
    });

    const historyMore = document.querySelector('.history-more');
    if (historyMore) historyMore.href = reservationsUrl;

    const formatearAcciones = (cell) => {
        return `
            <button class="btn-accept" style="cursor:pointer; color:green; border:none; background:transparent;">
                <span class="material-symbols-outlined check">check</span>
            </button>
            <button class="btn-reject" style="cursor:pointer; color:red; border:none; background:transparent; margin-left:10px;">
                <span class="material-symbols-outlined reject">close</span>
            </button>
        `;
    };

    const procesarReserva = async (idReserva, idLibro, accion) => {

        if (!confirm(`¿Seguro que deseas ${accion} esta reserva?`)) return;

        const formData = new FormData();
        formData.append('id_reserva', idReserva);
        formData.append('accion', accion);
        formData.append('csrf_token', document.querySelector('meta[name="csrf-token"]')?.content || '');

        try {
            const response = await fetch('/Appjoteca/backend/controllers/procesar_reservas.php', {
                method: 'POST',
                body: formData,
                credentials: 'same-origin'
            });

            const result = await response.json();

            if (result.ok) {
                window.alerta('success', 'Solicitud procesada', 'La solicitud se procesó correctamente.');
                table.setData(); // recarga la tabla
            } else {
                window.alerta('error', 'No se pudo procesar', result.error || 'Inténtalo de nuevo.');
            }

        } catch (error) {
            console.error('Error al procesar:', error);
            window.alerta('error', 'Error de conexión', 'No se pudo procesar la solicitud.');
        }
    };

    const table = new Tabulator("#reservas-table", {
        ajaxURL: "api/get_reservaciones.php?tipo=solicitudes&page=1&limit=5",
        ajaxConfig: { method: 'GET' },
        layout: "fitColumns",
        placeholder: "No hay reservas pendientes.",
        pagination: false,
        ajaxResponse: (url, params, response) => response.data || [],
        columns: [
            { title: "USUARIO", field: "usuario", widthGrow: 2 },
            { title: "LIBRO", field: "libro", widthGrow: 2 },
            { title: "SOLICITADO EN", field: "fecha_reserva", widthGrow: 1 },
            { title: "EJEMPLARES", field: "cantidad", width: 110, hozAlign: "center" },
            {
                title: "ACCIÓN",
                formatter: formatearAcciones,
                hozAlign: "center",
                width: 100,
                headerSort: false,
                cellClick: (e, cell) => {
                    const btn = e.target.closest('button');
                    if (!btn) return;

                    const row = cell.getRow().getData();
                    if (btn.classList.contains('btn-accept')) {
                        procesarReserva(row.id_reserva, row.id_libro, 'aceptar');
                    } else if (btn.classList.contains('btn-reject')) {
                        procesarReserva(row.id_reserva, row.id_libro, 'rechazar');
                    }
                }
            }
        ]
    });

});