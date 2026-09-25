// ── Referencias al DOM ──────────────────────────────────────
const form            = document.getElementById('signup-form');
const errorBox        = document.getElementById('signup-error');
const errorText       = document.getElementById('signup-error-text');
const successBox      = document.getElementById('signup-success');
const successText     = document.getElementById('signup-success-text');

const nombreInput     = document.getElementById('nombre');
const cedulaInput     = document.getElementById('cedula');
const cedulaFileInput = document.getElementById('cedula-file');
const cedulaPreview   = document.getElementById('cedula-preview');

const usuarioInput    = document.getElementById('usuario');
const emailInput      = document.getElementById('email');
const passInput       = document.getElementById('contrasena');
const pass2Input      = document.getElementById('contrasena2');

const togglePassBtn   = document.getElementById('toggle-pass');
const togglePassIcon  = document.getElementById('toggle-pass-icon');
const togglePass2Btn  = document.getElementById('toggle-pass2');
const togglePass2Icon = document.getElementById('toggle-pass2-icon');

const btnNext         = document.getElementById('btn-next');
const btnBack         = document.getElementById('btn-back');
const btnSubmit       = document.getElementById('btn-submit');
const btnSignupText   = document.getElementById('btn-signup-text');

const roleInputs      = document.querySelectorAll('.role-radio');
const stepPanels      = document.querySelectorAll('.step-panel');
const stepperSteps    = document.querySelectorAll('.step');
const stepperProgress = document.getElementById('stepper-progress');
const signupTitle     = document.getElementById('signup-title');
const signupSubtitle  = document.getElementById('signup-subtitle');
const visualStep      = document.getElementById('visual-step');

// ── Estado ──────────────────────────────────────────────────
let currentStep = 1;
const TOTAL_STEPS = 2;

// ── Utilidades ──────────────────────────────────────────────
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

function setLoading(isLoading) {
  btnSubmit.disabled = isLoading;
  btnSignupText.textContent = isLoading ? 'Creando cuenta…' : 'Crear cuenta';
}

// ── Stepper: pintar UI ──────────────────────────────────────
function renderStep() {
  stepPanels.forEach(panel => {
    panel.classList.toggle('is-active', Number(panel.dataset.step) === currentStep);
  });

  stepperSteps.forEach(step => {
    const n = Number(step.dataset.step);
    step.classList.toggle('is-active', n === currentStep);
    step.classList.toggle('is-done', n < currentStep);
  });

  const pct = ((currentStep - 1) / (TOTAL_STEPS - 1)) * 100;
  stepperProgress.style.width = pct + '%';

  if (currentStep === 1) {
    signupTitle.textContent = 'Datos de identidad';
    signupSubtitle.textContent = 'Cuéntanos quién eres para verificar tu cuenta institucional.';
  } else {
    signupTitle.textContent = 'Credenciales de acceso';
    signupSubtitle.textContent = 'Elige tu rol y define cómo accederás a la plataforma.';
  }

  if (visualStep) visualStep.textContent = currentStep;

  btnBack.hidden   = currentStep === 1;
  btnNext.hidden   = currentStep === TOTAL_STEPS;
  btnSubmit.hidden = currentStep !== TOTAL_STEPS;

  // Foco sin mover scroll
  const activePanel = document.querySelector(`.step-panel[data-step="${currentStep}"]`);
  const firstInput  = activePanel?.querySelector('input:not([type="radio"]):not([type="hidden"])');
  if (firstInput) {
    setTimeout(() => firstInput.focus({ preventScroll: true }), 120);
  }
}

// ── Mostrar / ocultar contraseña ────────────────────────────
function setupPasswordToggle(btn, input, icon) {
  btn.addEventListener('click', () => {
    const visible = input.type === 'text';
    input.type = visible ? 'password' : 'text';
    icon.textContent = visible ? 'visibility' : 'visibility_off';
    btn.setAttribute('aria-label', visible ? 'Ocultar contraseña' : 'Mostrar contraseña');
    input.focus({ preventScroll: true });
  });
}
setupPasswordToggle(togglePassBtn, passInput, togglePassIcon);
setupPasswordToggle(togglePass2Btn, pass2Input, togglePass2Icon);

// ── Preview del archivo ─────────────────────────────────────
cedulaFileInput.addEventListener('change', () => {
  const file = cedulaFileInput.files[0];
  if (!file) {
    cedulaPreview.hidden = true;
    cedulaPreview.innerHTML = '';
    return;
  }

  const allowedTypes = ['application/pdf', 'image/png', 'image/jpeg', 'image/webp'];
  const maxSize = 10 * 1024 * 1024;

  if (!allowedTypes.includes(file.type)) {
    mostrarError('Formato no permitido. Use PDF, PNG, JPG o WEBP.');
    cedulaFileInput.value = '';
    return;
  }
  if (file.size > maxSize) {
    mostrarError('El archivo supera los 10 MB permitidos.');
    cedulaFileInput.value = '';
    return;
  }

  ocultarMensajes();

  const isImage = file.type.startsWith('image/');
  const icon = isImage ? 'image' : 'picture_as_pdf';

  cedulaPreview.innerHTML = `
    <span class="material-symbols-outlined file-preview-icon" aria-hidden="true">${icon}</span>
    <span class="file-preview-name">${file.name}</span>
    <button type="button" class="file-preview-remove" aria-label="Eliminar archivo">
      <span class="material-symbols-outlined">close</span>
    </button>
  `;
  cedulaPreview.hidden = false;

  cedulaPreview.querySelector('.file-preview-remove').addEventListener('click', (e) => {
    e.stopPropagation();
    cedulaFileInput.value = '';
    cedulaPreview.hidden = true;
    cedulaPreview.innerHTML = '';
  });
});

// ── Ocultar error al interactuar ────────────────────────────
const allInputs = [
  nombreInput, cedulaInput, cedulaFileInput,
  usuarioInput, emailInput, passInput, pass2Input
];
allInputs.forEach(input => input.addEventListener('input', ocultarMensajes));
roleInputs.forEach(r => r.addEventListener('change', ocultarMensajes));

// ── Validaciones ────────────────────────────────────────────
function validarEmailInstitucional(email) {
  const dominiosPermitidos = [
    '.edu', '.edu.co', '.edu.mx', '.edu.ar', '.edu.pe', '.edu.cl',
    '.ac.', '.gob.', '.gov.'
  ];
  return dominiosPermitidos.some(d => email.toLowerCase().includes(d));
}

function validarPaso1() {
  const nombre = nombreInput.value.trim();
  const cedula = cedulaInput.value.trim();
  const cedulaFile = cedulaFileInput.files[0];

  if (!nombre) { mostrarError('Por favor, ingresa tus nombres y apellidos.'); nombreInput.focus({ preventScroll: true }); return false; }
  if (nombre.length < 3) { mostrarError('El nombre debe tener al menos 3 caracteres.'); nombreInput.focus({ preventScroll: true }); return false; }
  if (!cedula) { mostrarError('Por favor, ingresa tu número de cédula o tarjeta de identidad.'); cedulaInput.focus({ preventScroll: true }); return false; }
  if (!cedulaFile) { mostrarError('Debes subir una foto o PDF de tu documento de identidad.'); return false; }
  return true;
}

function validarPaso2() {
  const usuario = usuarioInput.value.trim();
  const email   = emailInput.value.trim().toLowerCase();
  const pass    = passInput.value;
  const pass2   = pass2Input.value;
  const rol     = document.querySelector('.role-radio:checked')?.value;

  if (!rol) { mostrarError('Selecciona tu tipo de acceso.'); return false; }
  if (!usuario) { mostrarError('Por favor, ingresa un nombre de usuario.'); usuarioInput.focus({ preventScroll: true }); return false; }
  if (usuario.length < 3) { mostrarError('El nombre de usuario debe tener al menos 3 caracteres.'); usuarioInput.focus({ preventScroll: true });
   return false; }

  if (!/^[a-zA-Z0-9._-]+$/.test(usuario)) { mostrarError('El usuario solo puede contener letras, números, punto, guión y guión bajo.');

     usuarioInput.focus({ preventScroll: true });
      return false; }

  if (!email) { mostrarError('Por favor, ingresa tu correo institucional.');
     emailInput.focus({ preventScroll: true });
      return false; }

  if (!validarEmailInstitucional(email)) {
     mostrarError('El correo debe ser de un dominio institucional (.edu, .ac, .gob, etc.).');
      emailInput.focus({ preventScroll: true });
       return false; }

  if (!pass) { mostrarError('Por favor, ingresa una contraseña.');
     passInput.focus({ preventScroll: true });
      return false; }

  if (pass.length < 8) { mostrarError('La contraseña debe tener al menos 8 caracteres.'); passInput.focus({ preventScroll: true }); return false; }
  if (pass !== pass2) { mostrarError('Las contraseñas no coinciden.'); pass2Input.focus({ preventScroll: true }); return false; }
  return true;
}

// ── Navegación ──────────────────────────────────────────────
btnNext.addEventListener('click', () => {
  ocultarMensajes();
  if (currentStep === 1 && !validarPaso1()) return;
  if (currentStep < TOTAL_STEPS) {
    currentStep++;
    renderStep();
  }
});

btnBack.addEventListener('click', () => {
  ocultarMensajes();
  if (currentStep > 1) {
    currentStep--;
    renderStep();
  }
});

// ── Envío final ─────────────────────────────────────────────
form.addEventListener('submit', async (e) => {
  e.preventDefault();
  ocultarMensajes();

  if (!validarPaso1() || !validarPaso2()) return;

  setLoading(true);

  try {
    const formData = new FormData(form);

    const respuesta = await fetch(form.action, {
      method: form.method,
      body: formData
    });

if (!respuesta.ok) {
  const errorText = await respuesta.text();
  throw new Error(`Error del servidor: ${respuesta.status} - ${errorText}`);
}

    const resultado = await respuesta.json();

    if (resultado.status === 'success') {
      mostrarExito(resultado.message || '¡Cuenta creada! Espera la aprobación de un administrador.');
      setLoading(false);
      btnSubmit.disabled = true;
      btnNext.disabled = true;
      btnBack.disabled = true;

      setTimeout(() => {
        window.location.href = '../login/login.php';
      }, 2600);
    } else {
      setLoading(false);
      mostrarError(resultado.message || 'Error al registrar la cuenta.');
    }
  } catch (err) {
    setLoading(false);
    mostrarError('Error de red o servidor. Inténtalo de nuevo.');
    console.error(err);
  }
});

// ── Init ────────────────────────────────────────────────────
renderStep();