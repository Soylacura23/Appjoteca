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
    // Petición al servidor (AJAX / Fetch)
    const response = await fetch('estudiante-back.php');

    if (!response.ok) {
      throw new Error(`HTTP error! estado: ${response.status}`);
    }

    const books = await response.json();

    // Validar respuesta vacía
    if (!Array.isArray(books) || books.length === 0) {
      container.innerHTML = '<p class="empty-msg">No hay libros sugeridos actualmente.</p>';
      return;
    }

    // Inyectar HTML de las tarjetas
    container.innerHTML = books.map(book => `
      <article class="book-card" data-id="${book.id || ''}">
        <div class="book-cover">
          <img src="${book.portada_url || 'https://images.unsplash.com/photo-1544947950-fa07a98d237f?q=80&w=800'}" 
               alt="${escapeHtml(book.titulo)}" 
               loading="lazy">
        </div>
        <div class="book-info">
          <h3 class="book-title">${escapeHtml(book.titulo)}</h3>
          <p class="book-author">${escapeHtml(book.autor)}</p>
        </div>
      </article>
    `).join('');

  } catch (error) {
    console.error('Error al obtener los libros sugeridos:', error);
    container.innerHTML = '<p class="error-msg">No se pudieron cargar las sugerencias.</p>';
  }
}

/**
 * Función de escape para prevenir posibles vulnerabilidades XSS
 */
function escapeHtml(str) {
  if (!str) return '';
  return String(str)
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;')
    .replace(/'/g, '&#039;');
}