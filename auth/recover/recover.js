// ── Referencias al DOM ──────────────────────────────────────
function getCSRFToken() {
  const meta = document.querySelector('meta[name="csrf-token"]');
  return meta ? meta.getAttribute('content') : '';
}
const form          = document.getElementById('recover-form');
const correoInput   = document.getElementById('correo');
const codeInputs    = document.querySelectorAll('.code-input');
const passInput     = document.getElementById('nueva-contrasena');
const confirmInput  = document.getElementById('confirmar-contrasena');
const step2         = document.getElementById('step-2');
const step3         = document.getElementById('step-3');
const btnSend       = document.getElementById('btn-send-code');
const btnSendText   = document.getElementById('btn-send-text');
const btnRecover    = document.getElementById('btn-recover');
const btnRecoverText = document.getElementById('btn-recover-text');
const errorBox      = document.getElementById('recover-error');
const errorText     = document.getElementById('recover-error-text');
const successBox    = document.getElementById('recover-success');
const successText   = document.getElementById('recover-success-text');

// ── Mensajes ────────────────────────────────────────────────
function mostrarError(msg) {
  errorText.textContent = msg;
  errorBox.hidden = false;
  errorBox.style.animation = 'none';
  void errorBox.offsetWidth; // reflow: reinicia la animación
  errorBox.style.animation = '';
}

function mostrarExito(msg) {
  successText.textContent = msg;
  successBox.hidden = false;
}

function ocultarMensajes() {
  errorBox.hidden   = true;
  successBox.hidden = true;
}

// ── Activar un paso (habilita sus campos) ───────────────────
function activarPaso(stepEl) {
  stepEl.classList.remove('step-inactive');
  stepEl.querySelectorAll('input, button').forEach(el => el.disabled = false);
}

// ── Paso 1 → 2: enviar correo a PHP ───────────────
btnSend.addEventListener('click', async () => {
  ocultarMensajes();
  const correo = correoInput.value.trim();

  if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(correo)) {
    mostrarError('Ingresa un correo institucional válido.');
    correoInput.focus();
    return;
  }

  // Estado de carga
  correoInput.disabled = true;
  btnSend.disabled     = true;
  btnSendText.textContent = 'Buscando y enviando...';

  try {
    const formData = new FormData();
    formData.append('action', 'send_code');
    formData.append('correo', correo);
    formData.append('csrf_token', getCSRFToken());

    const res = await fetch('../../backend/auth/procesarecover.php', { method: 'POST', body: formData });
    const data = await res.json();

    if (data.status === 'success') {
      btnSendText.textContent = 'Código Enviado ✓';
      activarPaso(step2);
      mostrarExito(data.message);
      codeInputs[0].focus();
    } else {
      mostrarError(data.message);
      correoInput.disabled = false;
      btnSend.disabled = false;
      btnSendText.textContent = 'Enviar Código de Verificación';
    }
  } catch (error) {
    mostrarError('Error de conexión con el servidor.');
    correoInput.disabled = false;
    btnSend.disabled = false;
    btnSendText.textContent = 'Enviar Código de Verificación';
  }
});

// ── Paso 2 → 3: verificar el código en PHP ─────────
codeInputs.forEach((input, i) => {
  input.addEventListener('input', async () => {
    input.value = input.value.replace(/\D/g, ''); // solo números
    if (input.value && i < codeInputs.length - 1) codeInputs[i + 1].focus();

    // Código completo
    const codigo = [...codeInputs].map(c => c.value).join('');
    
    if (codigo.length === 6) {
      ocultarMensajes();
      
      // Bloquear inputs para que no editen mientras carga
      codeInputs.forEach(c => c.disabled = true);

      try {
        const formData = new FormData();
        formData.append('action', 'verify_code');
        formData.append('codigo', codigo);
        formData.append('csrf_token', getCSRFToken());

        const res = await fetch('../../backend/auth/procesarecover.php', { method: 'POST', body: formData });
        const data = await res.json();

        if (data.status === 'success') {
          activarPaso(step3);
          mostrarExito(data.message + ' Ahora crea tu nueva contraseña.');
          passInput.focus();
        } else {
          mostrarError(data.message);
          // Rehabilitar inputs para que lo intenten de nuevo
          codeInputs.forEach(c => { c.disabled = false; c.value = ''; });
          codeInputs[0].focus();
        }
      } catch (error) {
        mostrarError('Error al verificar el código.');
        codeInputs.forEach(c => c.disabled = false);
      }
    }
  });

  input.addEventListener('keydown', (e) => {
    if (e.key === 'Backspace' && !input.value && i > 0) codeInputs[i - 1].focus();
  });
});

// ── Paso 3: actualizar contraseña en PHP ────────────────────────────
form.addEventListener('submit', async (e) => {
  e.preventDefault();
  ocultarMensajes();

  if (passInput.value.length < 6) {
    mostrarError('La contraseña debe tener al menos 6 caracteres.');
    passInput.focus();
    return;
  }

  if (passInput.value !== confirmInput.value) {
    mostrarError('Las contraseñas no coinciden. Inténtalo de nuevo.');
    confirmInput.value = '';
    confirmInput.focus();
    return;
  }

  btnRecoverText.textContent = 'Restableciendo…';
  btnRecover.disabled = true;

  try {
    const formData = new FormData();
    formData.append('action', 'update_password');
    formData.append('nueva_contrasena', passInput.value);
    formData.append('csrf_token', getCSRFToken());

    const res = await fetch('../../backend/auth/procesarecover.php', { method: 'POST', body: formData });
    const data = await res.json();

    if (data.status === 'success') {
      mostrarExito(data.message + ' Redirigiendo al inicio de sesión…');
      setTimeout(() => {
        window.location.href = '../login/login.php';
      }, 1500);
    } else {
      mostrarError(data.message);
      btnRecoverText.textContent = 'Restablecer Contraseña';
      btnRecover.disabled = false;
    }
  } catch (error) {
    mostrarError('Error al guardar la nueva contraseña.');
    btnRecoverText.textContent = 'Restablecer Contraseña';
    btnRecover.disabled = false;
  }
});