document.addEventListener('DOMContentLoaded', function () {
    'use strict';

    // ── Referencias ──
    const avatarInput    = document.getElementById('avatarInput');
    const avatarEditBtn  = document.getElementById('avatarEditBtn');
    const avatarImg      = document.getElementById('profileAvatarImg');

    const usernameInput  = document.getElementById('usernameInput');
    const saveUserBtn    = document.getElementById('saveUsernameBtn');

    const idPreview      = document.getElementById('idDocPreview');
    const idImg          = document.getElementById('idDocImage');
    const idOverlay      = document.getElementById('idDocOverlay');
    const idOverlayImg   = document.getElementById('idDocOverlayImg');
    const idOverlayClose = document.getElementById('idDocOverlayClose');
    const idInput        = document.getElementById('idDocInput');

    const newPass        = document.getElementById('newPassword');
    const confirmPass    = document.getElementById('confirmPassword');
    const strengthBar    = document.getElementById('strengthBar');
    const strengthText   = document.getElementById('strengthText');
    const matchHint      = document.getElementById('matchHint');
    const updatePassBtn  = document.getElementById('updatePasswordBtn');

    const requestBtns    = document.querySelectorAll('[data-request]');
    const deleteBtn      = document.getElementById('deleteAccountBtn');

    // Helper para alertas
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

    // ════════════════════════════════════════════
    // 1. Cambiar foto de perfil
    // ════════════════════════════════════════════
    avatarEditBtn.addEventListener('click', () => avatarInput.click());

    avatarInput.addEventListener('change', function () {
        const archivo = this.files[0];
        if (!archivo) return;

        avatarImg.src = URL.createObjectURL(archivo);

        const datos = new FormData();
        datos.append('accion', 'cambiar_foto');
        datos.append('nueva_foto', archivo);
        datos.append('csrf_token', window.getCSRFToken());

        fetch('../../backend/settings/configuracion-back.php', {
            method: 'POST',
            body: datos
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                alerta('success', 'Foto de perfil cambiada', data.message);
            } else {
                alerta('error', 'Error', data.message);
            }
        })
        .catch(err => {
            console.error('Error:', err);
            alerta('error', 'Error del servidor', 'No se pudo procesar la respuesta');
        });
    });


    // ════════════════════════════════════════════
    // 2. Guardar nombre de usuario
    // ════════════════════════════════════════════
    saveUserBtn.addEventListener('click', function () {
        const nuevo = usernameInput.value.trim();

        if (!nuevo) {
            alerta('warning', 'Campo vacío', 'El nombre de usuario no puede estar vacío');
            return;
        }

        const formData = new FormData();
        formData.append('update_username', '1');
        formData.append('username', nuevo);
        formData.append('csrf_token', window.getCSRFToken());

        fetch('../../backend/settings/configuracion-back.php', {
            method: 'POST',
            body: formData
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                alerta('success', 'Nombre actualizado', data.message);
                document.getElementById('displayUsername').textContent = nuevo;
            } else {
                alerta('error', 'Error', data.message);
            }
        })
        .catch(err => {
            console.error('Error:', err);
            alerta('error', 'Error de red', 'No se pudo actualizar el nombre');
        });
    });


    // ════════════════════════════════════════════
    // 3. Documento ID — modal y overlay
    // ════════════════════════════════════════════
    idPreview.addEventListener('click', function (e) {
        if (e.target.closest('#idDocChangeBtn')) return;

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
        }).then((result) => {
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

    idOverlayClose.addEventListener('click', closeOverlay);
    idOverlay.addEventListener('click', e => { if (e.target === idOverlay) closeOverlay(); });

    idInput.addEventListener('change', function () {
        const file = this.files[0];
        if (!file) return;
        const reader = new FileReader();
        reader.onload = e => {
            idImg.src = e.target.result;
            idOverlayImg.src = e.target.result;
        };
        reader.readAsDataURL(file);
    });


    // ════════════════════════════════════════════
    // 4. Mostrar / ocultar contraseñas
    // ════════════════════════════════════════════
    document.querySelectorAll('.toggle-password').forEach(btn => {
        btn.addEventListener('click', function () {
            const input = document.getElementById(this.dataset.target);
            const icon  = this.querySelector('.material-symbols-outlined');
            input.type  = input.type === 'password' ? 'text' : 'password';
            icon.textContent = input.type === 'password' ? 'visibility_off' : 'visibility';
        });
    });


    // ════════════════════════════
    // 5. Fortaleza de contraseña 
    // ════════════════════════════
    newPass.addEventListener('input', function () {
        const v = this.value;
        let s = 0;
        if (v.length >= 8) s++;
        if (/[A-Z]/.test(v)) s++;
        if (/[0-9]/.test(v)) s++;
        if (/[^A-Za-z0-9]/.test(v)) s++;

        if (v.length === 0) {
            strengthBar.style.width = '0%';
            strengthBar.className = 'strength-bar';
            strengthText.innerHTML = 'Fortaleza: <em>Débil</em>';
            return;
        }

        let label = 'Débil', color = '#ffb4ab', cls = 'weak';
        if (s >= 4)       { label = 'Fuerte'; color = '#4ade80'; cls = 'strong'; }
        else if (s >= 2)  { label = 'Media';  color = '#f2ca50'; cls = 'medium'; }

        strengthBar.className = 'strength-bar ' + cls;
        strengthText.innerHTML = `Fortaleza: <em style="color:${color}">${label}</em>`;
    });


    // ════════════════════════════════════════════
    // 6. Coincidencia de contraseñas
    // ════════════════════════════════════════════
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


    // ════════════════════════════════════════════
    // 7. Actualizar contraseña
    // ════════════════════════════════════════════
    updatePassBtn.addEventListener('click', function (e) {
        e.preventDefault();

        const currentPass = document.getElementById('currentPassword').value;
        const newPassVal  = newPass.value;
        const confirmVal  = confirmPass.value;

        if (!currentPass || !newPassVal || !confirmVal) {
            alerta('warning', 'Campos incompletos', 'Complete todos los campos');
            return;
        }
        if (newPassVal !== confirmVal) {
            alerta('error', 'No coinciden', 'Las contraseñas nuevas no coinciden');
            return;
        }
        if (newPassVal.length < 8) {
            alerta('warning', 'Muy corta', 'Mínimo 8 caracteres');
            return;
        }

        const formData = new FormData();
        formData.append('update_password', '1');
        formData.append('current_password', currentPass);
        formData.append('new_password', newPassVal);
        formData.append('confirm_password', confirmVal);
        formData.append('csrf_token', window.getCSRFToken());

        fetch('../../backend/settings/configuracion-back.php', {
            method: 'POST',
            body: formData
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                alerta('success', 'Contraseña actualizada', data.message);
                document.getElementById('currentPassword').value = '';
                newPass.value = '';
                confirmPass.value = '';
                strengthBar.style.width = '0%';
                strengthBar.className = 'strength-bar';
                strengthText.innerHTML = 'Fortaleza: <em>Débil</em>';
                matchHint.textContent = '';
            } else {
                alerta('warning', 'Error', data.message);
            }
        })
        .catch(err => {
            console.error('Error:', err);
            alerta('error', 'Error de red', 'No se pudo actualizar la contraseña');
        });
    });


    // ════════════════════════════════════════════
    // 8. Solicitudes al bibliotecario
    // ════════════════════════════════════════════
    requestBtns.forEach(btn => {
        btn.addEventListener('click', function () {
            const tipo = this.dataset.request;
            const titulos = {
                email: 'Cambio de Correo Institucional',
                name: 'Cambio de Nombre y Apellidos',
                document: 'Cambio de Documento y Foto'
            };

            Swal.fire({
                title: '¿Enviar solicitud?',
                text: `¿Desea solicitar ${titulos[tipo] || 'este cambio'} al bibliotecario?`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Sí, solicitar',
                cancelButtonText: 'Cancelar',
                reverseButtons: true,
                background: '#121212',
                color: '#e2e2e2',
                confirmButtonColor: '#f2ca50',
                cancelButtonColor: 'rgba(255,255,255,0.1)'
            }).then((result) => {
                if (result.isConfirmed) {
                    const urls = {
                        email: 'solicitud-email.php',
                        name: 'solicitud-nombre.php',
                        document: 'solicitud-documento.php'
                    };
                    window.location.href = urls[tipo] || '#';
                }
            });
        });
    });


    // ════════════════════════════════════════════
    // 9. Eliminar cuenta
    // ════════════════════════════════════════════
    deleteBtn.addEventListener('click', function () {
        Swal.fire({
            title: '¿Solicitar eliminación?',
            text: 'Esta acción enviará una solicitud de baja definitiva al administrador. ¿Está seguro?',
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
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = 'solicitud-eliminacion.php';
            }
        });
    });

});
