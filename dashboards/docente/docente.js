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

/**
 * Obtiene los libros desde el backend y construye las tarjetas dinámicamente
 */
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
      container.innerHTML = '<p class="empty-msg">No hay sugerencias pedagógicas disponibles actualmente.</p>';
      return;
    }

    container.innerHTML = books.map(book => `
      <article class="book-card" data-id="${book.id || ''}">
        <div class="book-cover">
        <img src="../../${libro.portada ? libro.portada : 'shared/images/logo-appjoteca.svg'}" 
     alt="${libro.titulo}" 
     class="libro-img"
     onerror="this.onerror=null; this.src='../../shared/images/logo-appjoteca.svg';">
        </div>
        <div class="book-info">
          <h3 class="book-title">${escapeHtml(book.titulo)}</h3>
          <p class="book-author">${escapeHtml(book.autor)}</p>
        </div>
      </article>
    `).join('');

  } catch (error) {
    console.warn('Utilizando datos de reserva local para sugerencias:', error);
    // Fallback con materiales orientados a docencia
    container.innerHTML = `
      <article class="book-card">
        <div class="book-cover">
          <img src="https://images.unsplash.com/photo-1589829085413-56de8ae18c73?q=80&w=800" alt="Física Aplicada" loading="lazy">
        </div>
        <div class="book-info">
          <h3 class="book-title">Física Aplicada</h3>
          <p class="book-author">Paul G. Hewitt</p>
        </div>
      </article>
      <article class="book-card">
        <div class="book-cover">
          <img src="https://images.unsplash.com/photo-1516979187457-637abb4f9353?q=80&w=800" alt="Pedagogía Ética" loading="lazy">
        </div>
        <div class="book-info">
          <h3 class="book-title">Pedagogía Ética</h3>
          <p class="book-author">Fernando Savater</p>
        </div>
      </article>
    `;
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