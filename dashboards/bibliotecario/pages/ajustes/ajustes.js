document.addEventListener('DOMContentLoaded', function () {
    'use strict';

    // ── Referencias del DOM ──
    const avatarInput       = document.getElementById('avatarInput');
    const avatarEditBtn     = document.getElementById('avatarEditBtn');
    const avatarImg         = document.getElementById('profileAvatarImg');
    const previewConfirmBtn = document.getElementById('previewConfirmBtn');
    const deletePhotoBtn    = document.getElementById('deletePhotoBtn');

    const usernameInput  = document.getElementById('usernameInput');
    const saveUsernameBtn = document.getElementById('saveUsernameBtn');

    const currentPass    = document.getElementById('currentPassword');
    const newPass        = document.getElementById('newPassword');
    const confirmPass    = document.getElementById('confirmPassword');
    const strengthBar    = document.getElementById('strengthBar');
    const strengthText   = document.getElementById('strengthText');
    const matchHint      = document.getElementById('matchHint');
    const updatePassBtn  = document.getElementById('updatePasswordBtn');

    const requestBtns    = document.querySelectorAll('[data-request]');
    const deleteAccountBtn = document.getElementById('deleteAccountBtn');
    const idPreview      = document.getElementById('idDocPreview');
    const idOverlay      = document.getElementById('idDocOverlay');
    const idOverlayClose = document.getElementById('idDocOverlayClose');

    // Guarda la foto original por si cancela el preview
    let fotoOriginal = avatarImg ? avatarImg.src : '';
    let archivoPendiente = null;

    const ENDPOINT = '../../../../backend/settings/configuracion-back.php';

    // ── Helper de alertas ──
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

    function getToken() {
        return window.getCSRFToken ? window.getCSRFToken() : (document.querySelector('meta[name="csrf-token"]')?.content || '');
    }

    if (idPreview && idOverlay) {
        idPreview.addEventListener('click', function () {
            Swal.fire({
                title: '¿Visualizar documento?',
                text: 'Este documento contiene información personal privada.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Visualizar',
                cancelButtonText: 'Volver',
                reverseButtons: true,
                background: '#121212',
                color: '#e2e2e2',
                confirmButtonColor: '#f2ca50',
                cancelButtonColor: 'rgba(255,255,255,0.1)'
            }).then(function (result) {
                if (!result.isConfirmed) return;
                idOverlay.classList.add('active');
                idOverlay.setAttribute('aria-hidden', 'false');
                document.body.style.overflow = 'hidden';
            });
        });

        function closeDocumentOverlay() {
            idOverlay.classList.remove('active');
            idOverlay.setAttribute('aria-hidden', 'true');
            document.body.style.overflow = '';
        }

        idOverlayClose?.addEventListener('click', closeDocumentOverlay);
        idOverlay.addEventListener('click', function (event) {
            if (event.target === idOverlay) closeDocumentOverlay();
        });
    }

    // 1. Foto de perfil (preview + confirmar + eliminar)
    if (avatarEditBtn && avatarInput) {
        avatarEditBtn.addEventListener('click', function () {
            avatarInput.click();
        });

        avatarInput.addEventListener('change', function () {
            const archivo = this.files[0];
            if (!archivo) return;

            // Solo preview, no sube todavía
            archivoPendiente = archivo;
            avatarImg.src = URL.createObjectURL(archivo);

            if (previewConfirmBtn) {
                previewConfirmBtn.hidden = false;
            }
        });
    }

    if (previewConfirmBtn) {
        previewConfirmBtn.addEventListener('click', function () {
            if (!archivoPendiente) return;

            const datos = new FormData();
            datos.append('accion', 'cambiar_foto');
            datos.append('nueva_foto', archivoPendiente);
            datos.append('csrf_token', getToken());

            fetch(ENDPOINT, {
                method: 'POST',
                body: datos
            })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                if (data.success) {
                    alerta('success', 'Listo', data.message);
                    fotoOriginal = avatarImg.src;
                    archivoPendiente = null;
                    previewConfirmBtn.hidden = true;
                    if (deletePhotoBtn) deletePhotoBtn.hidden = false;
                    if (avatarInput) avatarInput.value = '';
                } else {
                    alerta('error', 'Error', data.message);
                    avatarImg.src = fotoOriginal;
                    archivoPendiente = null;
                    previewConfirmBtn.hidden = true;
                }
            })
            .catch(function (err) {
                console.error(err);
                alerta('error', 'Error de red', 'No se pudo subir la foto');
                avatarImg.src = fotoOriginal;
                archivoPendiente = null;
                previewConfirmBtn.hidden = true;
            });
        });
    }

    if (deletePhotoBtn) {
        deletePhotoBtn.addEventListener('click', function () {
            Swal.fire({
                title: '¿Eliminar foto?',
                text: 'Se quitará tu foto de perfil y volverá a la predeterminada.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar',
                reverseButtons: true,
                background: '#121212',
                color: '#e2e2e2',
                confirmButtonColor: '#ffb4ab',
                cancelButtonColor: 'rgba(255,255,255,0.1)'
            }).then(function (result) {
                if (!result.isConfirmed) return;

                const datos = new FormData();
                datos.append('accion', 'eliminar_foto');
                datos.append('csrf_token', getToken());

                fetch(ENDPOINT, {
                    method: 'POST',
                    body: datos
                })
                .then(function (r) { return r.json(); })
                .then(function (data) {
                    if (data.success) {
                        alerta('success', 'Listo', data.message);
                        avatarImg.src = '/Appjoteca/assets/images/default-avatar.png';
                        fotoOriginal = avatarImg.src;
                        deletePhotoBtn.hidden = true;
                    } else {
                        alerta('error', 'Error', data.message);
                    }
                })
                .catch(function (err) {
                    console.error(err);
                    alerta('error', 'Error de red', 'No se pudo eliminar la foto');
                });
            });
        });
    }

    // 2. Guardar nombre de usuario
    if (saveUsernameBtn) {
        saveUsernameBtn.addEventListener('click', function () {
            const nuevo = usernameInput.value.trim();

            if (!nuevo) {
                alerta('warning', 'Campo vacío', 'El nombre de usuario no puede estar vacío');
                return;
            }

            const formData = new FormData();
            formData.append('accion', 'update_username');
            formData.append('username', nuevo);
            formData.append('csrf_token', getToken());

            fetch(ENDPOINT, {
                method: 'POST',
                body: formData
            })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                if (data.success) {
                    alerta('success', 'Nombre actualizado', data.message);
                    const display = document.getElementById('displayUsername');
                    if (display) display.textContent = nuevo;
                } else {
                    alerta('error', 'Error', data.message);
                }
            })
            .catch(function (err) {
                console.error(err);
                alerta('error', 'Error de red', 'No se pudo actualizar el nombre');
            });
        });
    }

    // 3. Mostrar / ocultar contraseñas
    document.querySelectorAll('.toggle-password').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const input = document.getElementById(this.dataset.target);
            const icon  = this.querySelector('.material-symbols-outlined');
            if (!input || !icon) return;

            if (input.type === 'password') {
                input.type = 'text';
                icon.textContent = 'visibility';
            } else {
                input.type = 'password';
                icon.textContent = 'visibility_off';
            }
        });
    });

    // 4. Fortaleza de contraseña
    if (newPass) {
        newPass.addEventListener('input', function () {
            const v = this.value;
            let puntos = 0;

            if (v.length >= 8) puntos++;
            if (/[A-Z]/.test(v)) puntos++;
            if (/[0-9]/.test(v)) puntos++;
            if (/[^A-Za-z0-9]/.test(v)) puntos++;

            if (v.length === 0) {
                strengthBar.className = 'strength-bar';
                strengthText.innerHTML = 'Fortaleza: <em>Débil</em>';
                return;
            }

            let label = 'Débil';
            let color = '#ffb4ab';
            let cls   = 'weak';

            if (puntos >= 4) {
                label = 'Fuerte';
                color = '#4ade80';
                cls   = 'strong';
            } else if (puntos >= 2) {
                label = 'Media';
                color = '#f2ca50';
                cls   = 'medium';
            }

            strengthBar.className = 'strength-bar ' + cls;
            strengthText.innerHTML = 'Fortaleza: <em style="color:' + color + '">' + label + '</em>';
        });
    }

    // 5. Coincidencia de contraseñas
    if (confirmPass) {
        confirmPass.addEventListener('input', function () {
            if (!newPass.value || !this.value) {
                matchHint.textContent = '';
                return;
            }

            if (this.value === newPass.value) {
                matchHint.textContent = 'Las contraseñas coinciden';
                matchHint.style.color = '#4ade80';
            } else {
                matchHint.textContent = 'Las contraseñas no coinciden';
                matchHint.style.color = '#ffb4ab';
            }
        });
    }

    // 6. Actualizar contraseña
    if (updatePassBtn) {
        updatePassBtn.addEventListener('click', function (e) {
            e.preventDefault();

            const actual  = currentPass.value;
            const nueva   = newPass.value;
            const confirm = confirmPass.value;

            if (!actual || !nueva || !confirm) {
                alerta('warning', 'Campos incompletos', 'Complete todos los campos');
                return;
            }

            if (nueva !== confirm) {
                alerta('error', 'No coinciden', 'Las contraseñas nuevas no coinciden');
                return;
            }

            if (nueva.length < 8) {
                alerta('warning', 'Muy corta', 'Mínimo 8 caracteres');
                return;
            }

            const formData = new FormData();
            formData.append('accion', 'update_password');
            formData.append('current_password', actual);
            formData.append('new_password', nueva);
            formData.append('confirm_password', confirm);
            formData.append('csrf_token', getToken());

            fetch(ENDPOINT, {
                method: 'POST',
                body: formData
            })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                if (data.success) {
                    alerta('success', 'Contraseña actualizada', data.message);
                    currentPass.value = '';
                    newPass.value = '';
                    confirmPass.value = '';
                    strengthBar.className = 'strength-bar';
                    strengthText.innerHTML = 'Fortaleza: <em>Débil</em>';
                    matchHint.textContent = '';
                } else {
                    alerta('warning', 'Error', data.message);
                }
            })
            .catch(function (err) {
                console.error(err);
                alerta('error', 'Error de red', 'No se pudo actualizar la contraseña');
            });
        });
    }

    // 7. Solicitudes al administrador
    requestBtns.forEach(function (btn) {
        btn.addEventListener('click', function () {
            const tipo = this.dataset.request;
            let archivoDocumento = null;

            const titulos = {
                email: 'Cambio de Correo',
                nombre: 'Cambio de Nombre y Apellidos',
                documento: 'Cambio de Documento y Foto'
            };

            const palabrasClave = {
                email: 'CORREO',
                nombre: 'NOMBRE',
                documento: 'DOCUMENTO'
            };

            const palabraClave = palabrasClave[tipo];

            Swal.fire({
                title: 'Solicitar ' + (titulos[tipo] || 'cambio'),
                html: `
                    <p style="margin-bottom: 8px;">Para continuar, escribe <strong>${palabraClave}</strong> e ingresa tu contraseña actual.</p>
                    <input id="confirmRequestText" class="swal2-input" placeholder="${palabraClave}" autocomplete="off" style="margin-bottom: 8px;">
                    <input id="confirmRequestPassword" class="swal2-input" type="password" placeholder="Contraseña actual" autocomplete="current-password" style="margin-bottom: 12px;">
                    <label style="display: block; margin-bottom: 4px; font-size: 0.875rem; color: #e2e2e2;">${tipo === 'documento' ? 'Nuevo número de documento' : 'Escribe el nuevo valor'}</label>
                    <input id="newValueInput" class="swal2-input" type="${tipo === 'email' ? 'email' : 'text'}" placeholder="${tipo === 'email' ? 'correo@institucion.edu' : 'Nuevo valor...'}" autocomplete="off" maxlength="${tipo === 'nombre' ? '100' : (tipo === 'documento' ? '25' : '60')}">
                    ${tipo === 'documento' ? `
                        <label class="change-document-upload" for="changeDocumentFile" style="margin-top: 12px;">
                            <span class="material-symbols-outlined">upload_file</span>
                            <span>Adjunta la foto del documento o un PDF (máximo 10 MB)</span>
                            <input id="changeDocumentFile" class="change-document-file-input" type="file" accept="image/jpeg,image/png,image/webp,application/pdf" aria-label="Selecciona una imagen o PDF del documento">
                        </label>
                        <p class="change-document-file-name" id="changeDocumentFileName" style="margin-top: 8px; font-size: 0.8125rem; color: #888;">Ningún archivo seleccionado</p>
                    ` : ''}
                `,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Enviar solicitud',
                cancelButtonText: 'Cancelar',
                reverseButtons: true,
                background: '#121212',
                color: '#e2e2e2',
                confirmButtonColor: '#f2ca50',
                cancelButtonColor: 'rgba(255,255,255,0.1)',
                didOpen: function () {
                    const fileInput = document.getElementById('changeDocumentFile');
                    fileInput?.addEventListener('change', function () {
                        archivoDocumento = this.files[0] || null;
                        const name = document.getElementById('changeDocumentFileName');
                        if (name) name.textContent = archivoDocumento ? archivoDocumento.name : 'Ningún archivo seleccionado';
                    });
                },
                preConfirm: function () {
                    const texto = document.getElementById('confirmRequestText').value.trim();
                    const password = document.getElementById('confirmRequestPassword').value;
                    const newValue = document.getElementById('newValueInput').value.trim();

                    if (texto !== palabraClave || !password) {
                        Swal.showValidationMessage(`Escribe ${palabraClave} e ingresa tu contraseña.`);
                        return false;
                    }

                    if (!newValue || !newValue.trim()) {
                        Swal.showValidationMessage('Debes escribir un valor.');
                        return false;
                    }

                    if (tipo === 'email' && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(newValue.trim())) {
                        Swal.showValidationMessage('Escribe un correo electrónico válido.');
                        return false;
                    }

                    if (tipo === 'nombre' && !/^[\p{L}\p{M} .'-]{3,100}$/u.test(newValue.trim())) {
                        Swal.showValidationMessage('Escribe un nombre válido de 3 a 100 caracteres.');
                        return false;
                    }

                    if (tipo === 'documento') {
                        if (!/^[0-9-]{1,25}$/.test(newValue.trim())) {
                            Swal.showValidationMessage('El documento solo puede contener números y guiones.');
                            return false;
                        }
                        if (!archivoDocumento) {
                            Swal.showValidationMessage('Adjunta la foto o el PDF del documento nuevo.');
                            return false;
                        }
                        if (archivoDocumento.size > 10 * 1024 * 1024) {
                            Swal.showValidationMessage('El archivo no puede superar 10 MB.');
                            return false;
                        }
                    }

                    return { texto: texto, password: password, valor: newValue };
                }
            }).then(function (result) {
                if (!result.isConfirmed) return;

                const formData = new FormData();
                formData.append('accion', 'solicitar_cambio');
                formData.append('tipo', tipo);
                formData.append('valor_nuevo', result.value.valor);
                formData.append('confirmacion', result.value.texto);
                formData.append('contrasena_actual', result.value.password);
                if (archivoDocumento) formData.append('archivo_documento', archivoDocumento);
                formData.append('csrf_token', getToken());

                fetch(ENDPOINT, {
                    method: 'POST',
                    body: formData
                })
                .then(function (r) { return r.json(); })
                .then(function (data) {
                    if (data.success) {
                        alerta('success', 'Solicitud enviada', data.message);
                    } else {
                        alerta('error', 'Error', data.message);
                    }
                })
                .catch(function (err) {
                    console.error(err);
                    alerta('error', 'Error de red', 'No se pudo enviar la solicitud');
                });
            });
        });
    });

    // 8. Solicitar eliminación de cuenta
    if (deleteAccountBtn) {
        deleteAccountBtn.addEventListener('click', function () {
            Swal.fire({
                title: '¿Solicitar eliminación?',
                text: 'Se enviará una solicitud de baja al administrador. ¿Estás seguro?',
                html: '<p style="margin-bottom: 8px;">Para continuar, escribe <strong>ELIMINAR</strong> e ingresa tu contraseña actual.</p><input id="confirmDeleteText" class="swal2-input" placeholder="ELIMINAR" autocomplete="off" style="margin-bottom: 8px;"><input id="confirmDeletePassword" class="swal2-input" type="password" placeholder="Contraseña actual" autocomplete="current-password">',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Sí, solicitar',
                cancelButtonText: 'Cancelar',
                reverseButtons: true,
                background: '#121212',
                color: '#e2e2e2',
                iconColor: '#ffb4ab',
                confirmButtonColor: '#ffb4ab',
                cancelButtonColor: 'rgba(255,255,255,0.1)',
                preConfirm: function () {
                    const texto = document.getElementById('confirmDeleteText').value.trim();
                    const password = document.getElementById('confirmDeletePassword').value;
                    if (texto !== 'ELIMINAR' || !password) {
                        Swal.showValidationMessage('Escribe ELIMINAR e ingresa tu contraseña.');
                        return false;
                    }
                    return { texto: texto, password: password };
                }
            }).then(function (result) {
                if (!result.isConfirmed) return;

                const formData = new FormData();
                formData.append('accion', 'solicitar_cambio');
                formData.append('tipo', 'eliminacion');
                formData.append('valor_nuevo', '');
                formData.append('confirmacion', result.value.texto);
                formData.append('contrasena_actual', result.value.password);
                formData.append('csrf_token', getToken());

                fetch(ENDPOINT, {
                    method: 'POST',
                    body: formData
                })
                .then(function (r) { return r.json(); })
                .then(function (data) {
                    if (data.success) {
                        alerta('success', 'Solicitud enviada', data.message);
                    } else {
                        alerta('error', 'Error', data.message);
                    }
                })
                .catch(function (err) {
                    console.error(err);
                    alerta('error', 'Error de red', 'No se pudo enviar la solicitud');
                });
            });
        });
    }

});