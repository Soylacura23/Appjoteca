// ── Rutas de los controladores ──
const URL_PROCESAR   = '../../../../backend/controllers/procesar_parametros.php';
const URL_OBTENER    = '../../../../backend/controllers/obtener_parametros.php'; // ?tabla=bibliotecas|dewey|...

const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

// ── Estado: conteos que desbloquean los pasos dependientes ──
const conteos = {
    bibliotecas: 0,
    dewey: 0,
    tipos_materiales: 0,
    colecciones: 0,
    materias: 0
};

// ── Referencias del DOM ──
const stepper     = document.getElementById('config-stepper');
const stepButtons = stepper.querySelectorAll('.step-btn');
const stepContents = {
    1: document.getElementById('paso-1'),
    2: document.getElementById('paso-2'),
    3: document.getElementById('paso-3'),
    4: document.getElementById('paso-4'),
    5: document.getElementById('paso-5'),
    registros: document.getElementById('paso-registros')
};

let pasoActual = 1;
let tablaRegistros = null;
let tablaInicializada = false;


/* ================================================================
   Navegación del Stepper
   ================================================================ */
function pasoBloqueado(paso) {
    if (paso === 4 && conteos.bibliotecas === 0) return true;
    if (paso === 5 && conteos.dewey === 0) return true;
    return false;
}

function mostrarPaso(paso) {
    if (paso !== 'registros' && pasoBloqueado(paso)) {
        const msg = paso === 4
            ? 'Primero registra al menos una biblioteca en el Paso 1.'
            : 'Primero registra al menos un área Dewey en el Paso 2.';
        window.alerta('warning', 'Paso bloqueado', msg);
        return;
    }

    pasoActual = paso;

    stepButtons.forEach(btn => {
        const esActivo = btn.dataset.paso == paso;
        btn.classList.toggle('active', esActivo);
        if (esActivo) btn.scrollIntoView({ behavior: 'smooth', inline: 'center', block: 'nearest' });
    });

    Object.values(stepContents).forEach(el => el.classList.remove('active'));
    stepContents[paso].classList.add('active');

    if (paso === 'registros') {
        if (!tablaInicializada) {
            inicializarTabla();
            tablaInicializada = true;
        }
        recargarTabla();
    }
}

stepButtons.forEach(btn => {
    btn.addEventListener('click', () => mostrarPaso(
        btn.dataset.paso === 'registros' ? 'registros' : Number(btn.dataset.paso)
    ));
});


async function guardarParametro(formElement, accion) {
    const boton = formElement.querySelector('button[type="submit"]');
    boton.disabled = true;

    const formData = new FormData(formElement);
    formData.append('accion', accion);

    try {
        const respuesta = await fetch(URL_PROCESAR, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrfToken },
            body: formData
        });
        const resultado = await respuesta.json();

        if (resultado.status === 'success') {
            window.alerta('success', 'Registro guardado', 'El registro se guardó exitosamente.');
            formElement.reset();

            await Promise.all([cargarBibliotecas(), cargarDewey(), cargarConteos()]);

            if (pasoActual === 'registros') recargarTabla();
        } else {
            window.alerta('error', 'Error al guardar', resultado.mensaje || 'No se pudo guardar el registro.');
        }
    } catch (error) {
        window.alerta('error', 'Error de conexión', 'No se pudo contactar con el servidor.');
    } finally {
        boton.disabled = false;
    }
}

document.getElementById('form-biblioteca').addEventListener('submit', e => {
    e.preventDefault();
    guardarParametro(e.target, 'crear_biblioteca');
});
document.getElementById('form-dewey').addEventListener('submit', e => {
    e.preventDefault();
    guardarParametro(e.target, 'crear_dewey');
});
document.getElementById('form-tipo-material').addEventListener('submit', e => {
    e.preventDefault();
    guardarParametro(e.target, 'crear_tipo_material');
});
document.getElementById('form-coleccion').addEventListener('submit', e => {
    e.preventDefault();
    guardarParametro(e.target, 'crear_coleccion');
});
document.getElementById('form-materia').addEventListener('submit', e => {
    e.preventDefault();
    guardarParametro(e.target, 'crear_materia');
});


/* 
   Carga dinámica de selects dependientes y conteos
    */
async function obtenerDatos(tabla) {
    const res = await fetch(`${URL_OBTENER}?tabla=${tabla}`);
    if (!res.ok) throw new Error(`HTTP ${res.status}`);
    return await res.json();
}

async function cargarBibliotecas() {
    const select = document.getElementById('select-bibliotecas');
    try {
        const datos = await obtenerDatos('bibliotecas');

        conteos.bibliotecas = datos.length;

        if (datos.length === 0) {

            select.innerHTML = '<option value="">Sin bibliotecas registradas (Paso 1)</option>';

            bloquearPaso(4, true);
            return;
        }

        select.innerHTML = '<option value="">Seleccione una biblioteca...</option>';

        datos.forEach(b => {

            select.innerHTML += `<option value="${b.id_biblioteca}">${b.nombre}</option>`;
        });
        bloquearPaso(4, false);

    } catch (error) {
        select.innerHTML = '<option value="">Error al cargar bibliotecas</option>';

    }
}

async function cargarDewey() {

    const select = document.getElementById('select-dewey');

    try {

        const datos = await obtenerDatos('dewey');

        conteos.dewey = datos.length;

        if (datos.length === 0) {

            select.innerHTML = '<option value="">Sin áreas Dewey registradas (Paso 2)</option>';

            bloquearPaso(5, true);
            return;
        }

        select.innerHTML = '<option value="">Seleccione un área Dewey...</option>';
        datos.forEach(d => {
            select.innerHTML += `<option value="${d.id_dewey}">${d.codigo} · ${d.nombre}</option>`;
        });
        bloquearPaso(5, false);
    } catch (error) {
        select.innerHTML = '<option value="">Error al cargar áreas Dewey</option>';
    }
}

// Actualiza los conteos del header y los candados del stepper
async function cargarConteos() {
    const tablas = ['tipos_materiales', 'colecciones', 'materias'];
    try {
        await Promise.all(tablas.map(async t => {
            const datos = await obtenerDatos(t);
            conteos[t] = datos.length;
        }));
    } catch (error) { /* se mantienen los conteos anteriores */ }

    document.getElementById('stat-bibliotecas').textContent = String(conteos.bibliotecas).padStart(2, '0');
    document.getElementById('stat-dewey').textContent      = String(conteos.dewey).padStart(2, '0');
    document.getElementById('stat-colecciones').textContent = String(conteos.colecciones).padStart(2, '0');
}

function bloquearPaso(paso, bloquear) {
    const btn = stepper.querySelector(`.step-btn[data-paso="${paso}"]`);
    btn.classList.toggle('locked', bloquear);
    btn.querySelector('.step-number').innerHTML = bloquear
        ? '<span class="material-symbols-outlined">lock</span>'
        : String(paso);
}


/* ================================================================
   Tabulator — Registros
   ================================================================ */
const COLUMNAS = {
    bibliotecas: [
        { title: 'ID', field: 'id_biblioteca', width: 70 },
        { title: 'Nombre', field: 'nombre' }
    ],
    dewey: [
        { title: 'ID', field: 'id_dewey', width: 70 },
        { title: 'Código', field: 'codigo', width: 110 },
        { title: 'Área', field: 'nombre' }
    ],
    tipos_materiales: [
        { title: 'ID', field: 'id_tipo_material', width: 70 },
        { title: 'Formato', field: 'nombre' }
    ],
    colecciones: [
        { title: 'ID', field: 'id_coleccion', width: 70 },
        { title: 'Colección', field: 'nombre' },
        { title: 'Biblioteca', field: 'biblioteca' }
    ],
    materias: [
        { title: 'ID', field: 'id_materia', width: 70 },
        { title: 'Materia', field: 'nombre' },
        { title: 'Área Dewey', field: 'dewey' }
    ]
};

const columnaAcciones = {
    title: 'Acciones',
    width: 110,
    hozAlign: 'center',
    headerSort: false,
    formatter: function() {
        return `
            <div class="tabla-acciones">
                <button type="button" class="btn-tabla btn-editar" title="Editar">
                    <span class="material-symbols-outlined">edit</span>
                </button>
                <button type="button" class="btn-tabla btn-eliminar" title="Eliminar">
                    <span class="material-symbols-outlined">delete</span>
                </button>
            </div>
        `;
    },
    cellClick: function(e, cell) {

        const boton = e.target.closest('button');

        if (!boton) return;

        const fila = cell.getRow().getData();

        const tabla = document.getElementById('registro-tipo').value;

        if (boton.classList.contains('btn-editar')) {

            editarRegistro(tabla, fila);
        } else if (boton.classList.contains('btn-eliminar')) {

            eliminarRegistro(tabla, fila);
        }
    }
};

Object.keys(COLUMNAS).forEach(entidad => {
    COLUMNAS[entidad].push(columnaAcciones);
});

function obtenerCampoId(tabla) {
    const mapaIds = {
        bibliotecas: 'id_biblioteca',
        dewey: 'id_dewey',
        tipos_materiales: 'id_tipo_material',
        colecciones: 'id_coleccion',
        materias: 'id_materia'
    };
    return mapaIds[tabla];
}

/* ================================================================
   ELIMINAR REGISTRO
   ================================================================ */
async function eliminarRegistro(tabla, fila) {
    const campoId = obtenerCampoId(tabla);
    const id = fila[campoId];
    const nombre = fila.nombre || fila.codigo || 'este registro';

    const confirmacion = await Swal.fire({
        title: '¿Confirmar eliminación?',
        text: `¿Deseas eliminar "${nombre}"? Esta acción no se puede deshacer.`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
    });

    if (!confirmacion.isConfirmed) return;

    const formData = new FormData();
    formData.append('accion', `eliminar_${tabla}`);
    formData.append('id', id);

    try {
        const res = await fetch(URL_PROCESAR, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrfToken },
            body: formData
        });
        const data = await res.json();

        if (data.status === 'success') {
            window.alerta('success', 'Eliminado', 'El registro fue eliminado correctamente.');
            await Promise.all([cargarBibliotecas(), cargarDewey(), cargarConteos()]);
            recargarTabla();
        } else {
            window.alerta('error', 'Error', data.mensaje || 'No se pudo eliminar el registro.');
        }
    } catch (error) {
        window.alerta('error', 'Error de conexión', 'No se pudo contactar con el servidor.');
    }
}

/* ================================================================
   EDITAR REGISTRO
   ================================================================ */
async function editarRegistro(tabla, fila) {
    const campoId = obtenerCampoId(tabla);
    const id = fila[campoId];

    // Formulario modal dinámico según la entidad
    let htmlInputs = `<input id="swal-nombre" class="swal2-input" placeholder="Nombre" value="${fila.nombre || ''}">`;
    if (tabla === 'dewey') {
        htmlInputs = `
            <input id="swal-codigo" class="swal2-input" placeholder="Código" value="${fila.codigo || ''}">
            <input id="swal-nombre" class="swal2-input" placeholder="Nombre del área" value="${fila.nombre || ''}">
        `;
    }

    const { value: formValues } = await Swal.fire({
        title: 'Editar Registro',
        html: htmlInputs,
        focusConfirm: false,
        showCancelButton: true,
        confirmButtonText: 'Guardar cambios',
        cancelButtonText: 'Cancelar',
        preConfirm: () => {
            const nombre = document.getElementById('swal-nombre')?.value.trim();
            const codigo = document.getElementById('swal-codigo')?.value.trim();

            if (!nombre) {
                Swal.showValidationMessage('El nombre no puede estar vacío');
                return false;
            }
            return { nombre, codigo };
        }
    });

    if (!formValues) return;

    const formData = new FormData();
    formData.append('accion', `editar_${tabla}`);
    formData.append('id', id);
    formData.append('nombre', formValues.nombre);
    if (formValues.codigo) formData.append('codigo', formValues.codigo);

    try {
        const res = await fetch(URL_PROCESAR, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrfToken },
            body: formData
        });
        const data = await res.json();

        if (data.status === 'success') {
            window.alerta('success', 'Actualizado', 'Los cambios se guardaron correctamente.');
            await Promise.all([cargarBibliotecas(), cargarDewey(), cargarConteos()]);
            recargarTabla();
        } else {
            window.alerta('error', 'Error', data.mensaje || 'No se pudo actualizar.');
        }
    } catch (error) {
        window.alerta('error', 'Error de conexión', 'No se pudo contactar con el servidor.');
    }
}

function inicializarTabla() {
    tablaRegistros = new Tabulator('#tabla-registros', {
        data: [],
        columns: COLUMNAS.bibliotecas,
        layout: 'fitColumns',
        responsiveLayout: 'hide',
        height: '420px',
        pagination: true,
        paginationSize: 10,
        paginationSizeSelector: [10, 25, 50],
        movableColumns: false,
        placeholder: 'No hay registros para mostrar.',
        dataLoader: false
    });
}

function recargarTabla() {
    const tabla = document.getElementById('registro-tipo').value;
    tablaRegistros.setColumns(COLUMNAS[tabla]);
    tablaRegistros.setData(`${URL_OBTENER}?tabla=${tabla}`);
}

document.getElementById('registro-tipo').addEventListener('change', recargarTabla);
document.getElementById('registros-recargar').addEventListener('click', () => {
    recargarTabla();
    window.alerta('info', 'Registros', 'Tabla actualizada.');
});

(async function iniciar() {
    await Promise.all([cargarBibliotecas(), cargarDewey(), cargarConteos()]);
})();
