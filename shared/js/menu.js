// ============================================
// APPJOTECA - MENÚ MÓVIL (compartido)
// ============================================
document.addEventListener('DOMContentLoaded', function () {
  const menuBtn = document.querySelector('.mobile-menu-btn');
  const header = document.querySelector('.main-header');
  if (!menuBtn || !header) return;

  // Evitar duplicados si el script se carga dos veces
  if (header.dataset.menuInitialized === 'true') return;
  header.dataset.menuInitialized = 'true';

  // Overlay
  let overlay = document.querySelector('.mobile-menu-overlay');
  if (!overlay) {
      overlay = document.createElement('div');
      overlay.className = 'mobile-menu-overlay';
      document.body.appendChild(overlay);
  }

  // Menú móvil (toma los enlaces del nav-list existente)
  let mobileMenu = header.querySelector('.mobile-menu');
  if (!mobileMenu) {
      mobileMenu = document.createElement('div');
      mobileMenu.className = 'mobile-menu';

      const navLinksHTML = Array.from(document.querySelectorAll('.main-nav .nav-link'))
          .map(link => `<li><a href="${link.getAttribute('href')}" class="mobile-nav-link">${link.textContent.trim()}</a></li>`)
          .join('');

      const loginLink = document.querySelector('.header-actions .btn-header');
      const loginHref = loginLink ? loginLink.getAttribute('href') : '../../auth/login/login.php';

      mobileMenu.innerHTML = `
          <nav class="mobile-nav">
              <ul class="mobile-nav-list">
                  ${navLinksHTML}
              </ul>
              <a href="${loginHref}" class="btn btn-primary btn-mobile">
                  <span>Iniciar Sesión</span>
              </a>
          </nav>
      `;
      header.appendChild(mobileMenu);
  }

  function openMenu() {
      header.classList.add('menu-open');
      overlay.classList.add('active');
      document.body.style.overflow = 'hidden';
      menuBtn.setAttribute('aria-expanded', 'true');
  }
  function closeMenu() {
      header.classList.remove('menu-open');
      overlay.classList.remove('active');
      document.body.style.overflow = '';
      menuBtn.setAttribute('aria-expanded', 'false');
  }
  function toggleMenu() {
      if (header.classList.contains('menu-open')) closeMenu();
      else openMenu();
  }

  menuBtn.addEventListener('click', toggleMenu);
  overlay.addEventListener('click', closeMenu);

  document.querySelectorAll('.mobile-nav-link').forEach(link => {
      link.addEventListener('click', closeMenu);
  });

  document.addEventListener('keydown', function (e) {
      if (e.key === 'Escape' && header.classList.contains('menu-open')) closeMenu();
  });

  let resizeTimer;
  window.addEventListener('resize', function () {
      clearTimeout(resizeTimer);
      resizeTimer = setTimeout(function () {
          if (window.innerWidth > 900 && header.classList.contains('menu-open')) closeMenu();
      }, 250);
  });
});