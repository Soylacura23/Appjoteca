document.addEventListener('DOMContentLoaded', () => {
    const reservationsUrl = '/Appjoteca/dashboards/bibliotecario/pages/reservaciones/reservaciones.php';
    document.getElementById('btn-ver-reservaciones')?.addEventListener('click', () => {
        window.location.href = reservationsUrl;
    });

    const historyMore = document.querySelector('.history-more');
    if (historyMore) historyMore.href = reservationsUrl;

    const formatearAcciones = () => {
        return `
            <div class="reservation-actions">
            <button class="btn-accept" type="button" aria-label="Aprobar reserva" title="Aprobar reserva">
                <span class="material-symbols-outlined">check</span>
            </button>
            <button class="btn-reject" type="button" aria-label="Rechazar reserva" title="Rechazar reserva">
                <span class="material-symbols-outlined">close</span>
            </button>
            </div>
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
        ajaxURL: "/Appjoteca/dashboards/bibliotecario/api/get_reservaciones.php?tipo=solicitudes",
        ajaxConfig: { method: 'GET' },
        layout: "fitColumns",
        placeholder: "No hay reservas pendientes.",
        pagination: true,
        paginationMode: "remote",
        paginationSize: 5,
        paginationSizeSelector: [5, 10, 20],
        paginationDataSent: { page: "page", size: "limit" },
        responsiveLayout: "collapse",
        responsiveLayoutCollapseStartOpen: false,
        ajaxResponse: (url, params, response) => ({
            data: response.data || [],
            last_page: response.pages || 1
        }),
        columns: [
            { title: "ID", field: "id_reserva", width: 70, minWidth: 60, hozAlign: "center", responsive: 0 },
            { title: "USUARIO", field: "usuario", minWidth: 120, widthGrow: 2, responsive: 1 },
            { title: "LIBRO", field: "libro", minWidth: 130, widthGrow: 2, responsive: 2 },
            { title: "SOLICITADO EN", field: "fecha_reserva", minWidth: 105, widthGrow: 1, responsive: 3 },
            { title: "EJEMPLARES", field: "cantidad", width: 100, minWidth: 85, hozAlign: "center", responsive: 4 },
            {
                title: "ACCIÓN",
                field: "acciones",
                formatter: formatearAcciones,
                hozAlign: "center",
                width: 105,
                minWidth: 95,
                responsive: 0,
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
        ],
        rowFormatter: (row) => {
            const labels = {
                id_reserva: "Reserva",
                usuario: "Usuario",
                libro: "Libro",
                fecha_reserva: "Solicitado",
                cantidad: "Ejemplares",
                acciones: "Acciones"
            };
            row.getCells().forEach((cell, index) => {
                const element = cell.getElement();
                const label = labels[cell.getColumn().getField()] || "";
                if (label) element.setAttribute("data-label", label);
            });
        }
    });

});