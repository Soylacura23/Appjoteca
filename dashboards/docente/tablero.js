document.addEventListener('DOMContentLoaded', () => {
  const api = '/Appjoteca/backend/controllers/reading_features.php';
  const list = document.getElementById('teacher-note-list');
  const form = document.getElementById('note-form');
  const formMessage = document.getElementById('note-form-message');
  const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';

  const escapeHtml = value => String(value ?? '').replace(/[&<>"']/g, char => ({
    '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;'
  })[char]);

  async function request(action, data) {
    const url = `${api}?action=${encodeURIComponent(action)}`;
    const options = { credentials: 'same-origin' };
    if (data) {
      options.method = 'POST';
      options.body = data;
      options.headers = { 'X-CSRF-TOKEN': csrf };
    }
    const response = await fetch(url, options);
    const result = await response.json();
    if (!response.ok || !result.ok) throw new Error(result.error || 'No se pudo completar la acción.');
    return result;
  }

  async function loadNotes() {
    try {
      const result = await request('notes_list');
      list.innerHTML = result.notes.length ? result.notes.map(note => `
        <article class="teacher-note-card ${Number(note.es_importante) ? 'is-important' : ''}">
          <div class="teacher-note-card-heading">
            <h2>${escapeHtml(note.titulo)}</h2>
            ${Number(note.es_importante) ? '<span class="material-symbols-outlined" aria-label="Importante">push_pin</span>' : ''}
          </div>
          <p>${escapeHtml(note.contenido).replace(/\n/g, '<br>')}</p>
          <small>${note.fecha_recordatorio ? `Recordatorio: ${escapeHtml(note.fecha_recordatorio)}` : 'Sin fecha de recordatorio'}</small>
          <div class="teacher-note-actions">
            <button type="button" data-edit="${Number(note.id)}">Editar</button>
            <button type="button" data-delete="${Number(note.id)}">Eliminar</button>
          </div>
        </article>`).join('') : '<p class="teacher-empty">Aún no tienes notas. Añade la primera desde aquí.</p>';
    } catch (error) {
      list.innerHTML = `<p class="teacher-empty teacher-error">${escapeHtml(error.message)}</p>`;
    }
  }

  document.getElementById('new-note-button')?.addEventListener('click', () => {
    form.reset();
    form.elements.id.value = '';
    form.hidden = false;
    form.elements.titulo.focus();
  });

  document.getElementById('cancel-note-button')?.addEventListener('click', () => {
    form.reset();
    form.hidden = true;
    formMessage.textContent = '';
  });

  form?.addEventListener('submit', async event => {
    event.preventDefault();
    const data = new FormData(form);
    data.append('action', 'note_save');
    data.set('es_importante', form.elements.es_importante.checked ? '1' : '0');
    formMessage.textContent = 'Guardando…';
    try {
      await request('note_save', data);
      form.hidden = true;
      form.reset();
      formMessage.textContent = '';
      await loadNotes();
    } catch (error) {
      formMessage.textContent = error.message;
    }
  });

  list?.addEventListener('click', async event => {
    const edit = event.target.closest('[data-edit]');
    const remove = event.target.closest('[data-delete]');
    if (edit) {
      try {
        const result = await request('notes_list');
        const note = result.notes.find(item => Number(item.id) === Number(edit.dataset.edit));
        if (!note) return;
        form.elements.id.value = note.id;
        form.elements.titulo.value = note.titulo;
        form.elements.contenido.value = note.contenido;
        form.elements.fecha_recordatorio.value = note.fecha_recordatorio || '';
        form.elements.es_importante.checked = Number(note.es_importante) === 1;
        form.hidden = false;
        form.elements.titulo.focus();
      } catch (error) {
        formMessage.textContent = error.message;
      }
    }
    if (remove && window.confirm('¿Eliminar esta nota?')) {
      const data = new FormData();
      data.append('action', 'note_delete');
      data.append('id', remove.dataset.delete);
      try {
        await request('note_delete', data);
        await loadNotes();
      } catch (error) {
        window.alert(error.message);
      }
    }
  });

  loadNotes();
});
