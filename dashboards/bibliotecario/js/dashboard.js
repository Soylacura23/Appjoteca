document.addEventListener('DOMContentLoaded', () => {
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
        formData.append('id_libro', idLibro);
        formData.append('accion', accion);

        try {
            const response = await fetch('/Appjoteca/backend/controllers/procesar_reserva.php', {
                method: 'POST',
                body: formData
            });

            const result = await response.json();

            if (result.success) {
                alert('Reserva procesada exitosamente.');
                table.setData(); // recarga la tabla
            } else {
                alert(`Error: ${result.error}`);
            }

        } catch (error) {
            console.error('Error al procesar:', error);
            alert('Error de conexión al procesar la reserva.');
        }
    };

    const table = new Tabulator("#reservas-table", {
        ajaxURL: "api/get_reservaciones.php",
        layout: "fitColumns",
        placeholder: "No hay reservas pendientes.",
        columns: [
            { title: "USUARIO", field: "usuario", widthGrow: 2 },
            { title: "LIBRO", field: "libro", widthGrow: 2 },
            { title: "SOLICITADO EN", field: "fecha_reserva", widthGrow: 1 },
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