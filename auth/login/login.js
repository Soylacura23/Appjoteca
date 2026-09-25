// ── Referencias al DOM ──────────────────────────────────────
const form           = document.getElementById('login-form');
const errorBox       = document.getElementById('login-error');
const errorText      = document.getElementById('login-error-text');
const successBox     = document.getElementById('login-success');
const successText    = document.getElementById('login-success-text');
const usuarioInput   = document.getElementById('usuario');
const passInput      = document.getElementById('contrasena');
const togglePassBtn  = document.getElementById('toggle-pass');
const togglePassIcon = document.getElementById('toggle-pass-icon');
const btnLogin       = document.querySelector('.btn-login');
const btnLoginText   = document.getElementById('btn-login-text');
const rememberInput  = document.getElementById('remember');

// ── Utilidades de mensajes ──────────────────────────────────
function ocultarMensajes() {
  errorBox.hidden = true;
  successBox.hidden = true;
}

function mostrarError(msg) {
  ocultarMensajes();
  errorText.textContent = msg;
  errorBox.hidden = false;
  errorBox.style.animation = 'none';
  void errorBox.offsetWidth;
  errorBox.style.animation = '';
}

function mostrarExito(msg) {
  ocultarMensajes();
  successText.textContent = msg;
  successBox.hidden = false;
}

// ── Mostrar / ocultar contraseña 
togglePassBtn.addEventListener('click', () => {
  const visible = passInput.type === 'text';
  passInput.type = visible ? 'password' : 'text';
  togglePassIcon.textContent = visible ? 'visibility' : 'visibility_off';
  togglePassBtn.setAttribute('aria-label', visible ? 'Ocultar contraseña' : 'Mostrar contraseña');
  passInput.focus({ preventScroll: true });
});

// ── Ocultar mensajes al interactuar 
usuarioInput.addEventListener('input', ocultarMensajes);
passInput.addEventListener('input', ocultarMensajes);

// ── Envío del formulario 
form.addEventListener('submit', async (e) => {
  e.preventDefault();
  ocultarMensajes();

  const usuario    = usuarioInput.value.trim();
  const contrasena = passInput.value;

  // Validaciones locales
  if (!usuario) {
    mostrarError('Por favor, ingresa tu usuario o correo institucional.');
    usuarioInput.focus({ preventScroll: true });
    return;
  }
  if (!contrasena) {
    mostrarError('Por favor, ingresa tu contraseña.');
    passInput.focus({ preventScroll: true });
    return;
  }

  // Estado de carga
  btnLoginText.textContent = 'Verificando acceso…';
  btnLogin.disabled = true;

  try {
    const formData = new FormData(form);

    const respuesta = await fetch(form.action, {
      method: 'POST',
      body: formData
    });

    const resultado = await respuesta.json();

    if (resultado.status === 'success') {
      mostrarExito('Acceso correcto. Redirigiendo…');
  
      setTimeout(() => {
        window.location.href = resultado.redirect;
      }, 700);
    } else {
      mostrarError(resultado.message || 'Usuario o contraseña incorrectos.');
      btnLoginText.textContent = 'Iniciar sesión';
      btnLogin.disabled = false;
      passInput.value = '';
      passInput.focus({ preventScroll: true });
    }
  } catch (err) {
    mostrarError('Error de conexión con el servidor.');
    btnLoginText.textContent = 'Iniciar sesión';
    btnLogin.disabled = false;
    console.error(err);
  }
});