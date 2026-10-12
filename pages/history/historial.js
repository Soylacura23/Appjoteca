   document.addEventListener('DOMContentLoaded', () => {
    const sidebar       = document.getElementById('historialSidebar');
    const sidebarToggle = document.getElementById('sidebarToggle');
    const mobileToggle  = document.getElementById('mobileSidebarToggle');
    const overlay       = document.getElementById('overlay');
    const sidebarItems  = document.querySelectorAll('.sidebar-item');
    const sections      = document.querySelectorAll('.historial-section');
  
    // ── Toggle sidebar desktop ──
    sidebarToggle?.addEventListener('click', () => {
      sidebar.classList.toggle('expanded');
      localStorage.setItem('sidebarExpanded', sidebar.classList.contains('expanded'));
    });
  
    // ── Restaurar estado del sidebar ──
    if (window.innerWidth >= 768) {
      const saved = localStorage.getItem('sidebarExpanded');
      if (saved === 'true') sidebar.classList.add('expanded');
    }
  
    // ── Mobile: abrir sidebar ──
    mobileToggle?.addEventListener('click', () => {
      sidebar.classList.add('mobile-open');
      sidebarToggle.style.visibility = 'hidden';
      overlay.classList.add('show');
    });
  
    // ── Cerrar sidebar mobile ──
    const closeMobileSidebar = () => {
      sidebar.classList.remove('mobile-open');
      sidebarToggle.style.visibility = 'visible';
      overlay.classList.remove('show');
    };
  
    overlay?.addEventListener('click', closeMobileSidebar);
  
    // ── Navegación por tabs ──
    sidebarItems.forEach(item => {
      item.addEventListener('click', () => {
        const targetId = item.dataset.section;
        if (!targetId) return;

        // Activar item
        sidebarItems.forEach(i => {
          i.classList.remove('active');
          i.setAttribute('aria-selected', 'false');
        });
        item.classList.add('active');
        item.setAttribute('aria-selected', 'true');
  
        // Mostrar sección
        sections.forEach(sec => {
          sec.classList.toggle('active', sec.id === targetId);
        });
  
        // Cerrar en mobile
        if (window.innerWidth < 768) closeMobileSidebar();
      });
    });

    const requestedSection = new URLSearchParams(window.location.search).get('section');
    if (requestedSection) {
      document.querySelector(`.sidebar-item[data-section="${CSS.escape(requestedSection)}"]`)?.click();
    }
  
    // ── Animación escalonada de tarjetas ──
    const cards = document.querySelectorAll('.timeline-card');
  
    const revealCards = () => {
      cards.forEach((card, i) => {
        setTimeout(() => card.classList.add('visible'), i * 120);
      });
    };
  
    // Usar IntersectionObserver para animar al entrar en viewport
    const observer = new IntersectionObserver((entries) => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          revealCards();
          observer.disconnect();
        }
      });
    }, { threshold: 0.05 });
  
    const firstSection = document.querySelector('.historial-section.active');
    if (firstSection) observer.observe(firstSection);
  
    // ── Re-animar al cambiar de tab ──
    sidebarItems.forEach(item => {
      item.addEventListener('click', () => {
        const targetId = item.dataset.section;
        const targetSection = document.getElementById(targetId);
        if (!targetSection) return;
  
        const newCards = targetSection.querySelectorAll('.timeline-card');
        newCards.forEach(c => c.classList.remove('visible'));
  
        setTimeout(() => {
          newCards.forEach((card, i) => {
            setTimeout(() => card.classList.add('visible'), i * 120);
          });
        }, 80);
      });
    });
  });
  