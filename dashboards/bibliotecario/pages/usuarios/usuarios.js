  document.addEventListener('DOMContentLoaded', function () {
  
    var filtroRol = document.getElementById('filter-role');
    var filtroEstado = document.getElementById('filter-status');
    var buscador = document.getElementById('search');
    var buscadorMovil = document.getElementById('search-mobile-input');
  
    var statTotal = document.getElementById('stat-total');
    var statActivos = document.getElementById('stat-active');
    var statEstudiantes = document.getElementById('stat-students');
    var statDocentes = document.getElementById('stat-teachers');
  
    var overlayDetalle = document.getElementById('user-detail-overlay');
    var fondoDetalle = document.getElementById('user-detail-backdrop');
    var botonCerrarDetalle = document.getElementById('user-detail-close');
  
    var modalConfirmar = document.getElementById('delete-confirm-modal');
    var fondoConfirmar = document.getElementById('delete-confirm-backdrop');
    var botonCancelar = document.getElementById('delete-confirm-cancel');
    var botonEliminar = document.getElementById('delete-confirm-accept');
    var botonEliminarCuenta = document.getElementById('delete-account-btn');

    var usuarioSeleccionado = null;
  
    /* ── Tabulator ─────────────────────────────────────────────── */
  
    var tabla = new Tabulator('#users-table', {
      layout: 'fitColumns',
      placeholder: 'No hay usuarios para mostrar',
      pagination: true,
      paginationSize: 10,
      paginationSizeSelector: [10, 25, 50, true],
      paginationButtonNext: "Siguiente &rarr;", 
      paginationButtonPrev: "&larr; Anterior",  
      paginationButtonFirst: "&laquo; Primera", 
      paginationButtonLast: "Última &raquo;",   
      columns: [
        {
          title: 'Perfil',
          field: 'nombre',
          minWidth: 240,
          formatter: function (cell) {
            var d = cell.getData();
            return '<div class="user-cell">' +
                     '<img src="' + d.foto_perfil + '" alt="' + d.nombre + '" class="user-avatar">' +
                     '<div class="user-info">' +
                       '<span class="user-name">' + d.nombre + '</span>' +
                       '<span class="user-subtitle">@' + d.usuario + '</span>' +
                     '</div>' +
                   '</div>';
          }
        },
        {
          title: 'Rol',
          field: 'rol',
          width: 150,
          formatter: function (cell) {
            var d = cell.getData();
            var clase = '';
            if (d.rol === 'Docente') clase = 'rol-docente';
            if (d.rol === 'Bibliotecario') clase = 'rol-bibliotecario';
            return '<span class="rol-badge ' + clase + '">' + d.rol + '</span>';
          }
        },
        { title: 'Documento', field: 'documento', width: 160 },
        {
          title: 'Estado',
          field: 'estado',
          width: 130,
          formatter: function (cell) {
            var valor = cell.getValue();
            var clase = valor === 'activo' ? 'status-active' : 'status-inactive';
            return '<span class="status-badge ' + clase + '">' + valor + '</span>';
          }
        },
        { 
          title: 'Fecha de creación', 
          field: 'fecha_creacion', 
          width: 160 
        },
  
        {
          title: 'Acciones',
          field: 'acciones',
          width: 200,
          hozAlign: "center",
          formatter: function(cell) {
            var d = cell.getData();
            
            if (!d.acciones || d.acciones === null) {
              return '<span class="acciones-placeholder">No hay nada de acciones aquí.</span>';
            }
            
            return `
              <div class="acciones-btns">
                <button class="btn-icon btn-accept" title="Aceptar"><i class="fa-solid fa-check"></i></button>
                <button class="btn-icon btn-reject" title="Rechazar"><i class="fa-solid fa-xmark"></i></button>
              </div>
            `;
          },
          
          cellClick: function(e, cell) {
            var target = e.target.closest('button');
            if (!target) return;

            e.stopPropagation();

            var id_usuario = cell.getData().id;
            var accion = target.classList.contains('btn-accept') ? 'aceptar' : 'rechazar';

            
            fetch('procesar-accion.php', {
              method: 'POST',
              headers: { 'Content-Type': 'application/x-www-form-urlencoded',
                'X-CSRF-TOKEN': window.getCSRFToken()
               },
              body: new URLSearchParams({ id: id_usuario, accion: accion })
            })
            .then(respuesta => respuesta.json())
            .then(data => {
              if (data.ok) {
                alerta('success', '¡Éxito!', 'La acción fue procesada correctamente.');
                // Opcional: Recargar la tabla o actualizar la fila actual
              } else {
                alerta('error', 'Error', data.error);
              }
            })
            .catch(error => {
              console.error(error);
              alerta('error', 'Error', 'Fallo de conexión.');
            });
          }
        }
      ]
    });

    document.getElementById("users-search").addEventListener("input", function(e) {
      let termino = e.target.value.toLowerCase();
  
      if (termino === "") {
  
          tabla.clearFilter();
      } else {

        tabla.setFilter(function(data) {
              
              let filaCompleta = Object.values(data).join(" ").toLowerCase();
              return filaCompleta.includes(termino);
              
          });
      }
  });
        
    /* ── Cargar usuarios desde el backend ──────────────────────── */
  
    fetch('usuarios-listar.php')
    .then(function (respuesta) {
      if (!respuesta.ok) {
        throw new Error('El servidor respondió con un error.');
      }
      return respuesta.json();
    })
      .then(function (data) {
        if (data.ok) {
          tabla.setData(data.usuarios);
          actualizarStats(data.usuarios);
        } else {
          alerta('error', 'No se cargaron los usuarios', (data.error || 'Error desconocido'));
        }
      })
    .catch(function (error) {
      alerta('error', 'Error de conexión', 'No se pudo obtener la lista de usuarios.');
      console.error(error);
    });
  
    /* ── Click en una fila: abrir overlay con los datos ────────── */
  
    tabla.on('rowClick', function (e, row) {
      abrirOverlay(row.getData());
    });
  
    function abrirOverlay(d) {
      usuarioSeleccionado = d;
  
      document.getElementById('user-detail-title').textContent = d.nombre;
      document.getElementById('detail-avatar').src = d.foto_perfil;
      document.getElementById('detail-avatar').alt = 'Foto de ' + d.nombre;
      document.getElementById('detail-name').textContent = d.nombre;
      document.getElementById('detail-subtitle').textContent = '@' + d.usuario + ' · ' + d.rol;
      document.getElementById('detail-bio').textContent = d.biografia ? '"' + d.biografia + '"' : 'Sin biografía registrada.';
      document.getElementById('detail-usuario').textContent = d.usuario || '—';
      document.getElementById('detail-email').textContent = d.correo || '—';
      document.getElementById('detail-documento').textContent = d.documento || '—';
      document.getElementById('detail-rol').textContent = d.rol || '—';
  
      // Estado activo / inactivo
      var badgeEstado = document.getElementById('detail-status-badge');
      if (d.estado === 'activo') {
        badgeEstado.textContent = 'Activo';
        badgeEstado.classList.remove('inactive');
      } else {
        badgeEstado.textContent = 'Inactivo';
        badgeEstado.classList.add('inactive');
      }
  
      var esMiCuenta = window.AppUser && String(window.AppUser.id) === String(d.id);
      botonEliminarCuenta.hidden = esMiCuenta;
  
      // Foto del documento de identidad
      var imgDoc = document.getElementById('detail-doc-img');
      var placeholderDoc = document.getElementById('id-placeholder');
      if (d.foto_documento) {
        imgDoc.src = d.foto_documento;
        imgDoc.hidden = false;
        placeholderDoc.hidden = true;
      } else {
        imgDoc.src = '';
        imgDoc.hidden = true;
        placeholderDoc.hidden = false;
      }
  
      overlayDetalle.hidden = false;
      document.body.style.overflow = 'hidden';
    }
  
    function cerrarOverlay() {
      overlayDetalle.hidden = true;
      document.body.style.overflow = '';
      usuarioSeleccionado = null;
    }
  
    /* ── Eliminar usuario ──────────────────────────────────────── */
  
    function mostrarConfirmacion() {
      if (!usuarioSeleccionado) return;
      document.getElementById('delete-confirm-name').textContent = usuarioSeleccionado.nombre;
      modalConfirmar.hidden = false;
    }
  
    function ocultarConfirmacion() {
      modalConfirmar.hidden = true;
    }
  
    function eliminarUsuario() {
      if (!usuarioSeleccionado) return;
  
      var id = usuarioSeleccionado.id;
  
      fetch('usuarios-eliminar.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded',
                   'X-CSRF-TOKEN': window.getCSRFToken()
         },
        body: 'id_usuario=' + encodeURIComponent(id)
      })
        .then(function (respuesta) { return respuesta.json(); })
        .then(function (data) {
          if (data.ok) {
            
            tabla.deleteRow(id);
            cerrarOverlay();
            actualizarStats(tabla.getData());
          } else {
            alerta('error', 'No se pudo actualizar al usuario', data.error);
          }
        })
        .catch(function () {
          alerta('error', 'Error de conexión', 'No se pudo eliminar eliminar el usuario');
        });
    }
  
    /* ── Filtros y búsqueda ────────────────────────────────────── */
  
    function aplicarFiltros() {
      var rol = filtroRol.value;
      var estado = filtroEstado.value;
      var texto = (buscador.value || '').toLowerCase().trim();
  
      tabla.setFilter(function (data) {
        if (rol && data.rol !== rol) return false;
        if (estado && data.estado !== estado) return false;
        if (texto) {
          var textoFila = (data.nombre + ' ' + data.usuario + ' ' + data.rol + ' ' +
                           data.documento + ' ' + data.correo).toLowerCase();
          if (textoFila.indexOf(texto) === -1) return false;
        }
        return true;
      });
    }
  
    filtroRol.addEventListener('change', aplicarFiltros);
    filtroEstado.addEventListener('change', aplicarFiltros);
    buscador.addEventListener('input', aplicarFiltros);
    buscadorMovil.addEventListener('input', aplicarFiltros);
  
    /* ── Estadísticas ──────────────────────────────────────────── */
  
    function actualizarStats(lista) {
      var activos = 0;
      var estudiantes = 0;
      var docentes = 0;
  
      for (var i = 0; i < lista.length; i++) {
        if (lista[i].estado === 'activo') activos++;
        if (lista[i].rol === 'Estudiante') estudiantes++;
        if (lista[i].rol === 'Docente') docentes++;
      }
  
      statTotal.textContent = lista.length;
      statActivos.textContent = activos;
      statEstudiantes.textContent = estudiantes;
      statDocentes.textContent = docentes;
    }
  
    /* ── Eventos ───────────────────────────────────────────────── */
  
    botonCerrarDetalle.addEventListener('click', cerrarOverlay);
    fondoDetalle.addEventListener('click', cerrarOverlay);
  
    botonEliminarCuenta.addEventListener('click', function (e) {
      e.stopPropagation();
      mostrarConfirmacion();
    });
  
    botonCancelar.addEventListener('click', ocultarConfirmacion);
    fondoConfirmar.addEventListener('click', ocultarConfirmacion);
    botonEliminar.addEventListener('click', function () {
      ocultarConfirmacion();
      eliminarUsuario();
    });
  
    // Cerrar con la tecla Escape
    document.addEventListener('keydown', function (e) {
      if (e.key !== 'Escape') return;
      if (!modalConfirmar.hidden) {
        ocultarConfirmacion();
      } else if (!overlayDetalle.hidden) {
        cerrarOverlay();
      }
    });
  
  });
  