document.addEventListener('DOMContentLoaded', function () {
    'use strict';

    // Helper para alertas SweetAlert2 con el estilo institucional
    function alerta(icon, title, text) {
        Swal.fire({
            icon: icon,
            title: title,
            text: text,
            background: '#121212',
            color: '#e2e2e2',
            confirmButtonColor: '#f2ca50'
        });
    }

    // --- 1. Inicialización de Tabulator.js ---
    const table = new Tabulator("#tabla-bibliotecarios", {
        ajaxURL: "../../backend/api_bibliotecarios.php?action=listar",
        layout: "fitColumns",
        responsiveLayout: "collapse",
        placeholder: "No se encontraron bibliotecarios registrados",
        columns: [
            { title: "ID", field: "id", width: 70, hozAlign: "center" },
            { title: "Documento", field: "documento" },
            { title: "Nombres", field: "nombres" },
            { title: "Apellidos", field: "apellidos" },
            { title: "Correo Electrónico", field: "correo" },
            { title: "Fecha de Alta", field: "creado_en", hozAlign: "center" },
            { 
                title: "Acciones", 
                hozAlign: "center",
                formatter: function() {
                    return `<button class="btn-danger-sm action-delete-btn" type="button">
                                <span class="material-symbols-outlined" style="font-size:16px;">delete</span> Eliminar
                            </button>`;
                },
                cellClick: function(e, cell) {
                    if (e.target.closest('.action-delete-btn')) {
                        const rowData = cell.getRow().getData();
                        confirmarEliminacion(rowData.id, `${rowData.nombres} ${rowData.apellidos}`);
                    }
                }
            }
        ]
    });

    // --- 2. Modal Controls ---
    const modal = document.getElementById('modalRegistro');
    const btnAbrir = document.getElementById('btnAbrirModal');
    const btnCerrar = document.getElementById('btnCerrarModal');

    btnAbrir.addEventListener('click', () => {
        modal.classList.add('active');
        document.body.style.overflow = 'hidden';
    });

    function closeModal() {
        modal.classList.remove('active');
        document.body.style.overflow = '';
    }

    btnCerrar.addEventListener('click', closeModal);
    modal.addEventListener('click', (e) => { if (e.target === modal) closeModal(); });

    // --- 3. Registrar Bibliotecario via AJAX ---
    const form = document.getElementById('formBibliotecario');
    form.addEventListener('submit', function (e) {
        e.preventDefault();

        const formData = new FormData(form);
        formData.append('action', 'crear');

        fetch('../../backend/api_bibliotecarios.php', {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (data.status === 'success') {
                alerta('success', 'Registro Exitoso', data.message);
                form.reset();
                closeModal();
                table.replaceData(); // Recargar datos en la tabla
            } else {
                alerta('error', 'Error de Registro', data.message);
            }
        })
        .catch(err => {
            console.error('Error:', err);
            alerta('error', 'Error del Servidor', 'No se pudo completar la operación.');
        });
    });

    // --- 4. Eliminar Bibliotecario ---
    function confirmarEliminacion(id, nombreCompleto) {
        Swal.fire({
            title: '¿Eliminar bibliotecario?',
            text: `Está a punto de remover el acceso a ${nombreCompleto}. Esta acción no se puede deshacer.`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar',
            reverseButtons: true,
            background: '#121212',
            color: '#e2e2e2',
            iconColor: '#ffb4ab',
            confirmButtonColor: '#ffb4ab',
            cancelButtonColor: 'rgba(255,255,255,0.1)'
        }).then((result) => {
            if (result.isConfirmed) {
                const formData = new FormData();
                formData.append('action', 'eliminar');
                formData.append('id', id);

                fetch('../../backend/api_bibliotecarios.php', {
                    method: 'POST',
                    body: formData
                })
                .then(res => res.json())
                .then(data => {
                    if (data.status === 'success') {
                        alerta('success', 'Eliminado', data.message);
                        table.replaceData();
                    } else {
                        alerta('error', 'Error', data.message);
                    }
                })
                .catch(err => {
                    console.error('Error:', err);
                    alerta('error', 'Error de Red', 'No se pudo eliminar el registro.');
                });
            }
        });
    }
});