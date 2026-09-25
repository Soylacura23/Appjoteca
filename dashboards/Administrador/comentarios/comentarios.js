document.addEventListener('DOMContentLoaded', function () {
    'use strict';

    const lista     = document.getElementById('lista-comentarios');
    const btnCargar = document.getElementById('btn-cargar-mas');
    const contador  = document.getElementById('contador-comentarios');
    const vacio     = document.getElementById('comentarios-vacio');
    const loading   = document.getElementById('comentarios-loading');

    let offsetActual  = 0;
    let cargando      = false;

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

    async function peticion(action, extra = {}) {
        const formData = new FormData();
        formData.append('action', action);
        formData.append('csrf_token', window.getCSRFToken());
        for (const key in extra) {
            formData.append(key, extra[key]);
        }
        const respuesta = await fetch('../../../backend/admin/api_comentarios.php', {
            method: 'POST',
            body: formData
        });
        return respuesta.json();
    }

    function escapeHtml(texto) {
        if (texto === null || texto === undefined) return '';
        const div = document.createElement('div');
        div.textContent = String(texto);
        return div.innerHTML;
    }

    function formatearTipo(tipo) {
        if (!tipo) return 'General';
        return tipo.charAt(0).toUpperCase() + tipo.slice(1);
    }

    function crearCard(c) {
        const card = document.createElement('article');
        card.className = 'comentario-card';
        card.dataset.id = c.id_comentario;

        card.innerHTML = `
            <header class="comentario-card__header">
                <div class="comentario-card__autor">
                    <div class="comentario-card__avatar">
                        <span class="material-symbols-outlined">person</span>
                    </div>
                    <div class="comentario-card__info">
                        <h4 class="comentario-card__nombre">${escapeHtml(c.nombre)}</h4>
                        <a class="comentario-card__correo" href="mailto:${escapeHtml(c.correo)}">
                            ${escapeHtml(c.correo)}
                        </a>
                    </div>
                </div>
                <div class="comentario-card__meta">
                    <span class="comentario-tag">${escapeHtml(formatearTipo(c.tipo_comentario))}</span>
                    <button type="button" class="comentario-card__delete action-delete-btn" aria-label="Eliminar comentario">
                        <span class="material-symbols-outlined">delete</span>
                    </button>
                </div>
            </header>
            <div class="comentario-card__body">
                <p>${escapeHtml(c.comentario)}</p>
            </div>
        `;

        card.querySelector('.action-delete-btn').addEventListener('click', () => {
            confirmarEliminacion(c.id_comentario, c.nombre);
        });

        return card;
    }

    async function cargarComentarios() {
        if (cargando) return;
        cargando = true;
    
        if (vacio) vacio.hidden = true;
        if (btnCargar) btnCargar.disabled = true;
    
        let loadingTimeout = setTimeout(() => {
            if (loading) loading.hidden = false;
        }, 300);
    
        try {
            const data = await peticion('listar', { offset: offsetActual });
    
            if (data.status !== 'success') {
                alerta('error', 'Error', data.message || 'No se pudieron cargar los comentarios.');
                return;
            }
    
            const comentarios = Array.isArray(data.comentarios) ? data.comentarios : [];
    
            if (contador) {
                const total = (typeof data.total === 'number')
                    ? data.total
                    : lista.children.length + comentarios.length;
                contador.textContent = total;
            }
    
            comentarios.forEach(c => {
                lista.appendChild(crearCard(c));
            });
    
            if (typeof data.limit === 'number') {
                offsetActual += data.limit;
            } else {
                offsetActual += comentarios.length;
            }
    
            const hayTarjetas = lista.children.length > 0;
    
            if (!hayTarjetas) {
                if (vacio) vacio.hidden = false;
                if (btnCargar) btnCargar.hidden = true;
            } else {
                if (vacio) vacio.hidden = true;
                if (btnCargar) {
                    const hayMas = !!data.hay_mas;
                    btnCargar.hidden = !hayMas;
                    btnCargar.disabled = false;
                }
            }
    
        } catch (error) {
            console.error('Error al cargar comentarios:', error);
            alerta('error', 'Error de Conexión', 'No se pudieron cargar los comentarios.');

            if (lista.children.length === 0 && vacio) {
                vacio.hidden = false;
            }
        } finally {
            cargando = false;
            clearTimeout(loadingTimeout);
            if (loading) loading.hidden = true;
        }
    }

    function confirmarEliminacion(id, nombre) {
        Swal.fire({
            title: '¿Eliminar comentario?',
            text: `Se eliminará el comentario de ${nombre}. Esta acción no se puede deshacer.`,
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
        }).then(async (result) => {
            if (!result.isConfirmed) return;
    
            try {
                const data = await peticion('eliminar', { id: id });
                if (data.status === 'success') {
                    const card = lista.querySelector(`.comentario-card[data-id="${id}"]`);
                    if (card) card.remove();
    
                    // Actualizar contador
                    if (contador) {
                        const nuevoTotal = Math.max(0, parseInt(contador.textContent || '0', 10) - 1);
                        contador.textContent = nuevoTotal;
                    }
    
                    // ✅ Estado vacío solo si la lista quedó realmente vacía
                    if (lista.children.length === 0) {
                        if (vacio) vacio.hidden = false;
                        if (btnCargar) btnCargar.hidden = true;
                    }
    
                    alerta('success', 'Eliminado', data.message);
                } else {
                    alerta('error', 'Error', data.message);
                }
            } catch (error) {
                console.error(error);
                alerta('error', 'Error de Red', 'No se pudo eliminar el comentario.');
            }
        });
    }

    if (btnCargar) {
        btnCargar.addEventListener('click', cargarComentarios);
    }

    cargarComentarios();
});