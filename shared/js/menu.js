// ============================================
// APPJOTECA - MENÚ MÓVIL
// ============================================

document.addEventListener('DOMContentLoaded', function() {
    const menuBtn = document.querySelector('.mobile-menu-btn');
    const header = document.querySelector('.main-header');
    const nav = document.querySelector('.main-nav');
    const navLinks = document.querySelectorAll('.nav-link');
  
    // Crear overlay para el menú móvil
    const overlay = document.createElement('div');
    overlay.className = 'mobile-menu-overlay';
    document.body.appendChild(overlay);
  
    // Crear menú móvil expandido
    const mobileMenu = document.createElement('div');
    mobileMenu.className = 'mobile-menu';
    mobileMenu.innerHTML = `
      <nav class="mobile-nav">
        <ul class="mobile-nav-list">
          <li><a href="/Appjoteca/pages/sobre-nosotros/about-us.php" class="mobile-nav-link">Sobre Nosotros</a></li>
          <li><a href="#" class="mobile-nav-link">Contacto</a></li>
        </ul>
        <a href="/Appjoteca/auth/login/login.php" class="btn btn-primary btn-mobile">
          <span>Iniciar Sesión</span>
        </a>
      </nav>
    `;
    header.appendChild(mobileMenu);
  
    // Toggle menú
    function toggleMenu() {
      const isOpen = header.classList.contains('menu-open');
  
      if (isOpen) {
        closeMenu();
      } else {
        openMenu();
      }
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
  
    // Event listeners
    menuBtn.addEventListener('click', toggleMenu);
    overlay.addEventListener('click', closeMenu);
  
    // Cerrar menú al hacer clic en un enlace
    navLinks.forEach(link => {
      link.addEventListener('click', closeMenu);
    });
  
    // Cerrar menú con Escape
    document.addEventListener('keydown', function(e) {
      if (e.key === 'Escape' && header.classList.contains('menu-open')) {
        closeMenu();
      }
    });
  
    // Cerrar menú al redimensionar ventana
    let resizeTimer;
    window.addEventListener('resize', function() {
      clearTimeout(resizeTimer);
      resizeTimer = setTimeout(function() {
        if (window.innerWidth > 768 && header.classList.contains('menu-open')) {
          closeMenu();
        }
      }, 250);
    });
  });