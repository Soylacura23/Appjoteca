document.addEventListener('DOMContentLoaded', () => {
  const form = document.querySelector('[data-reading-goal-form]');
  if (!form) return;

  form.addEventListener('submit', async event => {
    event.preventDefault();
    const message = form.querySelector('[data-goal-message]');
    const body = new FormData(form);
    body.append('action', 'goal_create');
    body.append('csrf_token', window.getCSRFToken?.() || document.querySelector('#app-csrf-token')?.dataset.token || '');
    try {
      const response = await fetch('/Appjoteca/backend/controllers/reading_features.php', {
        method: 'POST',
        credentials: 'same-origin',
        body
      });
      const result = await response.json();
      if (!response.ok || !result.ok) throw new Error(result.error || 'No se pudo crear la meta.');
      window.location.reload();
    } catch (error) {
      message.textContent = error.message;
    }
  });
});
