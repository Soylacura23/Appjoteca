document.addEventListener('DOMContentLoaded', function () {
    'use strict';

    // ── Referencias ──
    const avatarInput       = document.getElementById('avatarInput');
    const avatarEditBtn     = document.getElementById('avatarEditBtn');
    const avatarImg         = document.getElementById('profileAvatarImg');
    const previewConfirmBtn = document.getElementById('previewConfirmBtn');
    const deletePhotoBtn    = document.getElementById('deletePhotoBtn');

    const usernameInput   = document.getElementById('usernameInput');
    const saveUsernameBtn = document.getElementById('saveUsernameBtn');

    const idPreview      = document.getElementById('idDocPreview');
    const idOverlay      = document.getElementById('idDocOverlay');
    const idOverlayClose = document.getElementById('idDocOverlayClose');

    const currentPass   = document.getElementById('currentPassword');
    const newPass       = document.getElementById('newPassword');
    const confirmPass   = document.getElementById('confirmPassword');
    const strengthBar   = document.getElementById('strengthBar');
    const strengthText  = document.getElementById('strengthText');
    const matchHint     = document.getElementById('matchHint');
    const updatePassBtn = document.getElementById('updatePasswordBtn');

    const requestBtns      = document.querySelectorAll('[data-request]');
    const deleteAccountBtn = document.getElementById('deleteAccountBtn');

    let fotoOriginal = avatarImg ? avatarImg.src : '';
    let archivoPendiente = null;

    const ENDPOINT = '../../backend/settings/configuracion-back.php';

    // ── Helpers ──
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
        return window.getCSRFToken
            ? window.getCSRFToken()
            : (document.querySelector('meta[name="csrf-token"]')?.content || '');
    }

    // ════════════════════════════════════
    // 1. Foto de perfil (preview + confirmar + eliminar)
    // ════════════════════════════════════
    if (avatarEditBtn && avatarInput) {
        avatarEditBtn.addEventListener('click', function () {
            avatarInput.click();
        });

        avatarInput.addEventListener('change', function () {
            const archivo = this.files[0];
            if (!archivo) return;

            archivoPendiente = archivo;
            avatarImg.src = URL.createObjectURL(archivo);

            if (previewConfirmBtn) {
                previewConfirmBtn.style.display = 'inline-flex';
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
                    alerta('success', 'Foto actualizada', data.message);
                    fotoOriginal = avatarImg.src;
                    archivoPendiente = null;
                    previewConfirmBtn.style.display = 'none';
                    if (avatarInput) avatarInput.value = '';
                } else {
                    alerta('error', 'Error', data.message);
                    avatarImg.src = fotoOriginal;
                    archivoPendiente = null;
                    previewConfirmBtn.style.display = 'none';
                }
            })
            .catch(function (err) {
                console.error(err);
                alerta('error', 'Error de red', 'No se pudo subir la foto');
                avatarImg.src = fotoOriginal;
                archivoPendiente = null;
                previewConfirmBtn.style.display = 'none';
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

    // ════════════════════════════════════
    // 2. Guardar nombre de usuario
    // ════════════════════════════════════
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

    // ════════════════════════════════════
    // 3. Documento ID — overlay
    // ════════════════════════════════════
    if (idPreview && idOverlay) {
        idPreview.addEventListener('click', function () {
            Swal.fire({
                title: '¿Visualizar documento?',
                text: '¿Estás seguro de que deseas visualizar tu foto de documento de identidad? Es información personal privada.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Visualizar',
                cancelButtonText: 'Volver',
                reverseButtons: true,
                background: '#121212',
                color: '#e2e2e2',
                iconColor: '#f2ca50',
                confirmButtonColor: '#f2ca50',
                cancelButtonColor: 'rgba(255,255,255,0.1)'
            }).then(function (result) {
                if (result.isConfirmed) {
                    idOverlay.classList.add('active');
                    document.body.style.overflow = 'hidden';
                }
            });
        });

        function closeOverlay() {
            idOverlay.classList.remove('active');
            document.body.style.overflow = '';
        }

        if (idOverlayClose) {
            idOverlayClose.addEventListener('click', closeOverlay);
        }

        idOverlay.addEventListener('click', function (e) {
            if (e.target === idOverlay) closeOverlay();
        });
    }

    // ════════════════════════════════════
    // 4. Mostrar / ocultar contraseñas
    // ════════════════════════════════════
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

    // ════════════════════════════════════
    // 5. Fortaleza de contraseña
    // ════════════════════════════════════
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

    // ════════════════════════════════════
    // 6. Coincidencia de contraseñas
    // ════════════════════════════════════
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

    // ════════════════════════════════════
    // 7. Actualizar contraseña
    // ════════════════════════════════════
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

    // ════════════════════════════════════
    // 8. Solicitudes al bibliotecario
    // ════════════════════════════════════
    requestBtns.forEach(function (btn) {
        btn.addEventListener('click', function () {
            const tipo = this.dataset.request;

            const titulos = {
                email: 'Cambio de Correo Institucional',
                nombre: 'Cambio de Nombre y Apellidos',
                documento: 'Cambio de Documento'
            };

            Swal.fire({
                title: 'Solicitar ' + (titulos[tipo] || 'cambio'),
                input: 'text',
                inputLabel: 'Escribe el nuevo valor',
                inputPlaceholder: 'Nuevo valor...',
                showCancelButton: true,
                confirmButtonText: 'Enviar solicitud',
                cancelButtonText: 'Cancelar',
                reverseButtons: true,
                background: '#121212',
                color: '#e2e2e2',
                confirmButtonColor: '#f2ca50',
                cancelButtonColor: 'rgba(255,255,255,0.1)',
                inputValidator: function (value) {
                    if (!value || !value.trim()) {
                        return 'Debes escribir un valor';
                    }
                }
            }).then(function (result) {
                if (!result.isConfirmed) return;

                const formData = new FormData();
                formData.append('accion', 'solicitar_cambio');
                formData.append('tipo', tipo);
                formData.append('valor_nuevo', result.value.trim());
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

    // ════════════════════════════════════
    // 9. Solicitar eliminación de cuenta
    // ════════════════════════════════════
    if (deleteAccountBtn) {
        deleteAccountBtn.addEventListener('click', function () {
            Swal.fire({
                title: '¿Solicitar eliminación?',
                text: 'Se enviará una solicitud de baja definitiva al administrador. ¿Está seguro?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Sí, solicitar',
                cancelButtonText: 'Cancelar',
                reverseButtons: true,
                background: '#121212',
                color: '#e2e2e2',
                iconColor: '#ffb4ab',
                confirmButtonColor: '#ffb4ab',
                cancelButtonColor: 'rgba(255,255,255,0.1)'
            }).then(function (result) {
                if (!result.isConfirmed) return;

                const formData = new FormData();
                formData.append('accion', 'solicitar_cambio');
                formData.append('tipo', 'eliminacion');
                formData.append('valor_nuevo', '');
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