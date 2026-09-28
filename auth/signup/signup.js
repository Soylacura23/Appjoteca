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

const stepPanels      = document.querySelectorAll('.step-panel');
const stepperSteps    = document.querySelectorAll('.step');
const stepperProgress = document.getElementById('stepper-progress');
const signupTitle     = document.getElementById('signup-title');
const signupSubtitle  = document.getElementById('signup-subtitle');
const visualStep      = document.getElementById('visual-step');

const strengthBar     = document.getElementById('strengthBar');
const strengthText    = document.getElementById('strengthText');
const matchHint       = document.getElementById('matchHint');

// ── Estado ──────────────────────────────────────────────────
let currentStep = 1;
const TOTAL_STEPS = 2;

// ── Utilidades ──────────────────────────────────────────────
function ocultarMensajes() {
  if (errorBox) errorBox.hidden = true;
  if (successBox) successBox.hidden = true;
}

function mostrarError(msg) {
  ocultarMensajes();
  if (!errorBox || !errorText) return;
  errorText.textContent = msg;
  errorBox.hidden = false;
  errorBox.style.animation = 'none';
  void errorBox.offsetWidth;
  errorBox.style.animation = '';
}

function mostrarExito(msg) {
  ocultarMensajes();
  if (!successBox || !successText) return;
  successText.textContent = msg;
  successBox.hidden = false;
}

function setLoading(isLoading) {
  if (!btnSubmit || !btnSignupText) return;
  btnSubmit.disabled = isLoading;
  btnSignupText.textContent = isLoading ? 'Creando cuenta…' : 'Crear cuenta';
}

// ── Completitud de pasos ────────────────────────────────────
function paso1Completo() {
  return (
    nombreInput.value.trim().length >= 3 &&
    cedulaInput.value.trim().length > 0 &&
    cedulaFileInput.files.length > 0
  );
}

function paso2Completo() {
  const usuario = usuarioInput.value.trim();
  const email   = emailInput.value.trim().toLowerCase();
  const pass    = passInput.value;
  const pass2   = pass2Input.value;

  return (
    usuario.length >= 3 &&
    /^[a-zA-Z0-9._-]+$/.test(usuario) &&
    email.length > 0 &&
    validarEmailInstitucional(email) &&
    pass.length >= 8 &&
    /[A-Z]/.test(pass) &&
    /[0-9]/.test(pass) &&
    pass === pass2
  );
}

function actualizarBotones() {
  // Atrás
  if (currentStep <= 1) {
    btnBack.hidden = true;
    btnBack.disabled = true;
  } else {
    btnBack.hidden = false;
    btnBack.disabled = false;
  }

  // Siguiente
  if (currentStep >= TOTAL_STEPS) {
    btnNext.hidden = true;
    btnNext.disabled = true;
  } else {
    btnNext.hidden = false;
    btnNext.disabled = false;
  }

  // Crear cuenta
  if (currentStep === TOTAL_STEPS) {
    btnSubmit.hidden = false;
    btnSubmit.disabled = !paso2Completo();
  } else {
    btnSubmit.hidden = true;
    btnSubmit.disabled = true;
  }
}

// ── Stepper ─────────────────────────────────────────────────
function renderStep() {
  stepPanels.forEach(function (panel) {
    panel.classList.toggle('is-active', Number(panel.dataset.step) === currentStep);
  });

  stepperSteps.forEach(function (step) {
    const n = Number(step.dataset.step);
    step.classList.toggle('is-active', n === currentStep);
    step.classList.toggle('is-done', n < currentStep);
  });

  const pct = ((currentStep - 1) / (TOTAL_STEPS - 1)) * 100;
  if (stepperProgress) stepperProgress.style.width = pct + '%';

  if (currentStep === 1) {
    if (signupTitle) signupTitle.textContent = 'Datos de identidad';
    if (signupSubtitle) {
      signupSubtitle.textContent = 'Cuéntanos quién eres para verificar tu cuenta institucional.';
    }
  } else {
    if (signupTitle) signupTitle.textContent = 'Credenciales de acceso';
    if (signupSubtitle) {
      signupSubtitle.textContent = 'Define cómo accederás a la plataforma.';
    }
  }

  if (visualStep) visualStep.textContent = currentStep;

  actualizarBotones();

  const activePanel = document.querySelector('.step-panel[data-step="' + currentStep + '"]');
  const firstInput  = activePanel
    ? activePanel.querySelector('input:not([type="radio"]):not([type="hidden"]):not([type="file"])')
    : null;

  if (firstInput) {
    setTimeout(function () {
      firstInput.focus({ preventScroll: true });
    }, 120);
  }
}

// ── Toggle contraseña ───────────────────────────────────────
function setupPasswordToggle(btn, input, icon) {
  if (!btn || !input || !icon) return;
  btn.addEventListener('click', function () {
    const visible = input.type === 'text';
    input.type = visible ? 'password' : 'text';
    icon.textContent = visible ? 'visibility' : 'visibility_off';
    btn.setAttribute('aria-label', visible ? 'Mostrar contraseña' : 'Ocultar contraseña');
    input.focus({ preventScroll: true });
  });
}

setupPasswordToggle(togglePassBtn, passInput, togglePassIcon);
setupPasswordToggle(togglePass2Btn, pass2Input, togglePass2Icon);

// ── Preview y eliminar documento ────────────────────────────
function limpiarPreviewCedula() {
  cedulaFileInput.value = '';
  try {
    const dt = new DataTransfer();
    cedulaFileInput.files = dt.files;
  } catch (err) {
    // ok
  }
  if (cedulaPreview) {
    cedulaPreview.hidden = true;
    cedulaPreview.innerHTML = '';
  }
}

if (cedulaFileInput) {
  cedulaFileInput.addEventListener('change', function () {
    const file = this.files[0];
    if (!file) {
      limpiarPreviewCedula();
      return;
    }

    const allowedTypes = ['application/pdf', 'image/png', 'image/jpeg', 'image/webp'];
    const maxSize = 10 * 1024 * 1024;

    if (!allowedTypes.includes(file.type)) {
      mostrarError('Formato no permitido. Usa PDF, PNG, JPG o WEBP.');
      limpiarPreviewCedula();
      return;
    }

    if (file.size > maxSize) {
      mostrarError('El archivo supera los 10 MB permitidos.');
      limpiarPreviewCedula();
      return;
    }

    ocultarMensajes();

    const isImage = file.type.startsWith('image/');
    const icon = isImage ? 'image' : 'picture_as_pdf';

    cedulaPreview.innerHTML =
      '<span class="material-symbols-outlined file-preview-icon" aria-hidden="true">' + icon + '</span>' +
      '<span class="file-preview-name">' + file.name + '</span>' +
      '<button type="button" class="file-preview-remove" aria-label="Eliminar archivo">' +
      '<span class="material-symbols-outlined">close</span></button>';

    cedulaPreview.hidden = false;

    const btnRemove = cedulaPreview.querySelector('.file-preview-remove');
    if (btnRemove) {
      btnRemove.addEventListener('click', function (e) {
        e.preventDefault();
        e.stopPropagation();
        limpiarPreviewCedula();
      });
    }
  });
}

// ── Validaciones ────────────────────────────────────────────
function validarEmailInstitucional(email) {
  const dominios = [
    '.edu', '.edu.co', '.edu.mx', '.edu.ar', '.edu.pe', '.edu.cl',
    '.ac.', '.gob.', '.gov.'
  ];
  const e = (email || '').toLowerCase();
  return dominios.some(function (d) {
    return e.includes(d);
  });
}

function validarPaso1() {
  const nombre = nombreInput.value.trim();
  const cedula = cedulaInput.value.trim();
  const cedulaFile = cedulaFileInput.files[0];

  if (!nombre) {
    mostrarError('Ingresa tus nombres y apellidos.');
    nombreInput.focus({ preventScroll: true });
    return false;
  }
  if (nombre.length < 3) {
    mostrarError('El nombre debe tener al menos 3 caracteres.');
    nombreInput.focus({ preventScroll: true });
    return false;
  }
  if (!cedula) {
    mostrarError('Ingresa tu número de cédula o tarjeta de identidad.');
    cedulaInput.focus({ preventScroll: true });
    return false;
  }
  if (!cedulaFile) {
    mostrarError('Debes subir una foto o PDF de tu documento de identidad.');
    return false;
  }
  return true;
}

function validarPaso2() {
  const usuario = usuarioInput.value.trim();
  const email   = emailInput.value.trim().toLowerCase();
  const pass    = passInput.value;
  const pass2   = pass2Input.value;

  if (!usuario) {
    mostrarError('Ingresa un nombre de usuario.');
    usuarioInput.focus({ preventScroll: true });
    return false;
  }
  if (usuario.length < 3) {
    mostrarError('El usuario debe tener al menos 3 caracteres.');
    usuarioInput.focus({ preventScroll: true });
    return false;
  }
  if (!/^[a-zA-Z0-9._-]+$/.test(usuario)) {
    mostrarError('El usuario solo puede contener letras, números, punto (.), guión (-) y guión bajo (_).');
    usuarioInput.focus({ preventScroll: true });
    return false;
  }

  if (!email) {
    mostrarError('Ingresa tu correo institucional.');
    emailInput.focus({ preventScroll: true });
    return false;
  }
  if (!validarEmailInstitucional(email)) {
    mostrarError('Usa un correo institucional (.edu, .edu.co, .ac, .gob, etc.).');
    emailInput.focus({ preventScroll: true });
    return false;
  }

  if (!pass) {
    mostrarError('Ingresa una contraseña.');
    passInput.focus({ preventScroll: true });
    return false;
  }
  if (pass.length < 8) {
    mostrarError('La contraseña debe tener al menos 8 caracteres.');
    passInput.focus({ preventScroll: true });
    return false;
  }
  if (!/[A-Z]/.test(pass)) {
    mostrarError('La contraseña debe incluir al menos una letra mayúscula.');
    passInput.focus({ preventScroll: true });
    return false;
  }
  if (!/[0-9]/.test(pass)) {
    mostrarError('La contraseña debe incluir al menos un número.');
    passInput.focus({ preventScroll: true });
    return false;
  }
  if (pass !== pass2) {
    mostrarError('Las contraseñas no coinciden.');
    pass2Input.focus({ preventScroll: true });
    return false;
  }
  return true;
}

// ── Fortaleza de contraseña ─────────────────────────────────
function actualizarFortaleza() {
  if (!passInput || !strengthBar || !strengthText) return;

  const v = passInput.value;
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
  let cls = 'weak';

  if (puntos >= 4) {
    label = 'Fuerte';
    color = '#4ade80';
    cls = 'strong';
  } else if (puntos >= 2) {
    label = 'Media';
    color = '#f2ca50';
    cls = 'medium';
  }

  strengthBar.className = 'strength-bar ' + cls;
  strengthText.innerHTML = 'Fortaleza: <em style="color:' + color + '">' + label + '</em>';
}

function actualizarMatch() {
  if (!matchHint || !passInput || !pass2Input) return;

  if (!passInput.value || !pass2Input.value) {
    matchHint.textContent = '';
    return;
  }

  if (pass2Input.value === passInput.value) {
    matchHint.textContent = 'Las contraseñas coinciden';
    matchHint.style.color = '#4ade80';
  } else {
    matchHint.textContent = 'Las contraseñas no coinciden';
    matchHint.style.color = '#ffb4ab';
  }
}

if (passInput) {
  passInput.addEventListener('input', function () {
    ocultarMensajes();
    actualizarFortaleza();
    actualizarMatch();
    if (currentStep === TOTAL_STEPS) actualizarBotones();
  });
}

if (pass2Input) {
  pass2Input.addEventListener('input', function () {
    ocultarMensajes();
    actualizarMatch();
    if (currentStep === TOTAL_STEPS) actualizarBotones();
  });
}

// ── Navegación ──────────────────────────────────────────────
if (btnNext) {
  btnNext.addEventListener('click', function () {
    ocultarMensajes();
    if (currentStep === 1 && !validarPaso1()) return;
    if (currentStep < TOTAL_STEPS) {
      currentStep++;
      renderStep();
    }
  });
}

if (btnBack) {
  btnBack.addEventListener('click', function () {
    ocultarMensajes();
    if (currentStep > 1) {
      currentStep--;
      renderStep();
    }
  });
}

// ── Inputs: limpiar error y actualizar botón ────────────────
const allInputs = [
  nombreInput, cedulaInput, cedulaFileInput,
  usuarioInput, emailInput, passInput, pass2Input
];

allInputs.forEach(function (input) {
  if (!input) return;
  const evento = input.type === 'file' ? 'change' : 'input';
  input.addEventListener(evento, function () {
    ocultarMensajes();
    if (currentStep === TOTAL_STEPS) actualizarBotones();
  });
});

// ── Envío final ─────────────────────────────────────────────
if (form) {
  form.addEventListener('submit', async function (e) {
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
        const errorBody = await respuesta.text();
        throw new Error('Error del servidor: ' + respuesta.status + ' - ' + errorBody);
      }

      const resultado = await respuesta.json();

      if (resultado.status === 'success') {
        mostrarExito(resultado.message || '¡Cuenta creada! Espera la aprobación de un administrador.');
        setLoading(false);
        btnSubmit.disabled = true;
        btnNext.disabled = true;
        btnBack.disabled = true;

        setTimeout(function () {
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
}

// ── Init ────────────────────────────────────────────────────
renderStep();