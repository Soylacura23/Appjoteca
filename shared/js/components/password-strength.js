(function (global) {
  'use strict';

  function calcularPuntos(v) {
    let puntos = 0;
    if (v.length >= 8)          puntos++;
    if (/[A-Z]/.test(v))        puntos++;
    if (/[a-z]/.test(v))        puntos++;
    if (/[0-9]/.test(v))        puntos++;
    if (/[^A-Za-z0-9]/.test(v)) puntos++;
    return puntos;
  }

  function reglasCumplidas(v) {
    return v.length >= 8
        && /[A-Z]/.test(v)
        && /[a-z]/.test(v)
        && /[0-9]/.test(v);
  }

  function pintarFortaleza(bar, text, v) {
    if (!bar || !text) return;

    if (v.length === 0) {
      bar.className = 'strength-bar';
      bar.style.width = '0%';
      text.innerHTML = 'Fortaleza: <em>Débil</em>';
      return;
    }

    const puntos = calcularPuntos(v);
    let label = 'Débil';
    let color = '#ffb4ab';
    let cls   = 'weak';

    if (puntos >= 5) {
      label = 'Fuerte';
      color = '#4ade80';
      cls   = 'strong';
    } else if (puntos >= 3) {
      label = 'Media';
      color = '#f2ca50';
      cls   = 'medium';
    }

    bar.className = 'strength-bar ' + cls;
    text.innerHTML = 'Fortaleza: <em style="color:' + color + '">' + label + '</em>';
  }

  function pintarMatch(input1, input2, hint) {
    if (!input1 || !input2 || !hint) return;
    if (!input1.value || !input2.value) {
      hint.textContent = '';
      return;
    }
    if (input2.value === input1.value) {
      hint.textContent = 'Las contraseñas coinciden';
      hint.style.color = '#4ade80';
    } else {
      hint.textContent = 'Las contraseñas no coinciden';
      hint.style.color = '#ffb4ab';
    }
  }

  function setupToggle(btn, input, icon) {
    if (!btn || !input || !icon) return;
    btn.addEventListener('click', function () {
      const visible = input.type === 'text';
      input.type = visible ? 'password' : 'text';
      icon.textContent = visible ? 'visibility' : 'visibility_off';
      btn.setAttribute('aria-label', visible ? 'Mostrar contraseña' : 'Ocultar contraseña');
      input.focus({ preventScroll: true });
    });
  }

  function initPasswordStrength(opts) {
    const input      = opts.input;
    const bar        = opts.bar;
    const text       = opts.text;
    const matchInput = opts.matchInput || null;
    const matchHint  = opts.matchHint  || null;
    const onChange   = typeof opts.onChange === 'function' ? opts.onChange : null;

    if (!input) return;

    setupToggle(opts.toggleBtn,  input,      opts.toggleIcon);
    setupToggle(opts.toggleBtn2, matchInput, opts.toggleIcon2);

    function refrescar() {
      pintarFortaleza(bar, text, input.value);
      if (matchInput && matchHint) pintarMatch(input, matchInput, matchHint);
      if (onChange) onChange();
    }

    input.addEventListener('input', refrescar);
    if (matchInput) matchInput.addEventListener('input', refrescar);

    // Estado inicial
    refrescar();
  }

  // Exponer helpers por si los necesitas sueltos
  global.initPasswordStrength  = initPasswordStrength;
  global.passwordRulesPassed   = reglasCumplidas;
})(window);