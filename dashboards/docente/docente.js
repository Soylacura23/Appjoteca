document.addEventListener('DOMContentLoaded', () => {
  setGreeting();
  setDate();
  loadSuggestedBooks();
});

function setGreeting() {
  const el = document.getElementById('greetingTime');
  if (!el) return;

  const hour = new Date().getHours();
  let text = 'Buenas noches';
  if (hour >= 5 && hour < 12) text = 'Buenos días';
  else if (hour >= 12 && hour < 19) text = 'Buenas tardes';

  el.textContent = text;
}

function setDate() {
  const el = document.getElementById('currentDate');
  if (!el) return;

  const opts = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
  let txt = new Date().toLocaleDateString('es-ES', opts);
  el.textContent = txt.charAt(0).toUpperCase() + txt.slice(1);
}

async function loadSuggestedBooks() {
  const container = document.getElementById('booksGrid');
  if (!container) return;

  try {
    const response = await fetch('docente-back.php');

    if (!response.ok) {
      throw new Error(`HTTP error! estado: ${response.status}`);
    }

    const books = await response.json();

    if (!Array.isArray(books) || books.length === 0) {
      container.innerHTML = '<p class="teacher-empty">No hay sugerencias pedagógicas disponibles actualmente.</p>';
      return;
    }

    const defaultCover = '../../shared/images/logo-appjoteca.svg';

    container.innerHTML = books.map(book => `
      <article class="book-card" data-id="${Number(book.id) || 0}">
        <div class="book-cover">
          <img src="${escapeHtml(book.portada_url || defaultCover)}"
               alt="${escapeHtml(book.titulo)}"
               class="libro-img"
               onerror="this.onerror=null; this.src='${defaultCover}';">
        </div>
        <div class="book-info">
          <h3 class="book-title">${escapeHtml(book.titulo)}</h3>
          <p class="book-author">${escapeHtml(book.autor)}</p>
        </div>
      </article>
    `).join('');

  } catch (error) {
    console.warn('No se pudieron obtener las sugerencias del servidor:', error);
    container.innerHTML = '<p class="teacher-empty">No se pudieron cargar los libros sugeridos en este momento.</p>';
  }
}

function escapeHtml(str) {
  if (!str) return '';
  return String(str)
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;')
    .replace(/'/g, '&#039;');
}