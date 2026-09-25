(function () {
    "use strict";
  
    const escapeHTML = (str = "") =>
        String(str).replace(/[&<>"']/g, (c) => ({
            "&": "&amp;", "<": "&lt;", ">": "&gt;", '"': "&quot;", "'": "&#39;"
        }[c] || c));
  
    var reduceMotion = window.matchMedia("(prefers-reduced-motion: reduce)").matches;
  
    /* ---------- AOS ---------- */
    if (typeof AOS !== "undefined") {
        AOS.init({ duration: 800, once: true, offset: 80, easing: "ease-out-cubic" });
    }
  
    /* ---------- Animaciones de Revelado (fallback) ---------- */
    const revealTargets = document.querySelectorAll("[data-reveal]");
    if ("IntersectionObserver" in window && revealTargets.length) {
        const revealObserver = new IntersectionObserver(
            (entries) => {
                entries.forEach((entry) => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add("is-visible");
                        revealObserver.unobserve(entry.target);
                    }
                });
            },
            { threshold: 0.15, rootMargin: "0px 0px -10% 0px" }
        );
        revealTargets.forEach((el) => revealObserver.observe(el));
    } else {
        revealTargets.forEach((el) => el.classList.add("is-visible"));
    }
  
    /* ---------- Parallax Ligero ---------- */
    const parallaxEls = document.querySelectorAll("[data-parallax]");
    const updateParallax = () => {
        if (reduceMotion || !parallaxEls.length) return;
        const viewportH = window.innerHeight;
        parallaxEls.forEach((el) => {
            const parent = el.parentElement;
            if (!parent) return;
            const rect = parent.getBoundingClientRect();
            if (rect.bottom < 0 || rect.top > viewportH) return;
            const speed = parseFloat(el.getAttribute("data-parallax")) || 0.15;
            const offset = (rect.top - viewportH / 2) * speed;
            el.style.transform = `translateY(${offset}px)`;
        });
    };
  
    /* ---------- Header scroll ---------- */
    const header = document.getElementById("mainHeader");
    const updateHeader = () => {
        if (!header) return;
        if (window.scrollY > 30) header.classList.add("is-scrolled");
        else header.classList.remove("is-scrolled");
    };
  
    /* ---------- SCROLL HORIZONTAL DE LIBROS ---------- */
    const booksScroll = document.getElementById("booksScroll");
    const booksTrack = document.getElementById("booksTrack");
    const booksViewport = booksScroll ? booksScroll.querySelector(".books-track-viewport") : null;
  
    function isHorizontalScrollActive() {
        return booksScroll && booksTrack && booksViewport && window.innerWidth > 768 && !reduceMotion;
    }
    function updateBooksScroll() {
        if (!isHorizontalScrollActive()) return;
        const scrollRect = booksScroll.getBoundingClientRect();
        const trackWidth = booksTrack.scrollWidth;
        const viewportWidth = booksViewport.clientWidth;
        const maxTranslate = Math.max(trackWidth - viewportWidth, 0);
        const totalScrollable = booksScroll.offsetHeight - window.innerHeight;
        if (totalScrollable <= 0) return;
        let progressed = -scrollRect.top;
        let progress = progressed / totalScrollable;
        progress = Math.min(Math.max(progress, 0), 1);
        booksTrack.style.transform = `translateX(${-progress * maxTranslate}px)`;
    }
    function resetBooksScroll() {
        if (booksTrack) booksTrack.style.transform = "";
    }
  
    /* ---------- Bucle de Animación ---------- */
    let ticking = false;
    const onScrollOrResize = () => {
        if (!ticking) {
            window.requestAnimationFrame(() => {
                updateHeader();
                updateParallax();
                if (isHorizontalScrollActive()) updateBooksScroll();
                else resetBooksScroll();
                ticking = false;
            });
            ticking = true;
        }
    };
  
    /* ---------- Renderizado de Tarjetas de Libros ---------- */
    const renderizarTarjetas = (libros = []) => {
        if (!booksTrack) return;
        if (!Array.isArray(libros) || libros.length === 0) {
            booksTrack.innerHTML = `
            <div class="book-slide book-slide-cta" style="border-style: dashed;">
                <div class="book-slide-cta-inner" style="align-items: center; text-align: center;">
                    <span class="material-symbols-outlined" style="font-size: 3rem; color: var(--primary-color);">auto_stories</span>
                    <h3 style="color: var(--text-primary);">Aún no hay obras destacadas</h3>
                    <p style="color: var(--text-secondary);">Los libros más leídos aparecerán aquí.</p>
                </div>
            </div>`;
            return;
        }
        let html = "";
        libros.forEach((libro) => {
            const portada = libro.portada || "assets/images/books/default.jpg";
            const titulo = escapeHTML(libro.titulo_libro || "Sin título");
            const autor = escapeHTML(libro.autor || "Autor Desconocido");
            const categoria = escapeHTML(libro.categoria || "Destacado");
            html += `
            <div class="book-slide">
                <div class="card">
                    <div class="card-image-wrapper">
                        <img src="${escapeHTML(portada)}" alt="${titulo}" loading="lazy">
                        <div class="card-overlay">
                            <span class="tag">${categoria}</span>
                            <h3>${titulo}</h3>
                            <p>${autor}</p>
                        </div>
                    </div>
                </div>
            </div>`;
        });
        html += `
        <div class="book-slide book-slide-cta">
            <div class="book-slide-cta-inner">
                <p>El catálogo completo crece cada semana. Esto es solo una muestra.</p>
                <a href="auth/login/login.php" class="btn btn-secondary">
                    <span>Ver todas las obras</span>
                </a>
            </div>
        </div>`;
        booksTrack.innerHTML = html;
    };
  
    /* ---------- Libro de la semana ---------- */
    const renderizarRecomendado = (libro) => {
        const container = document.getElementById("bookWeekContainer");
        if (!container || !libro) {
            if (container) container.style.display = "none";
            return;
        }
        const portada = libro.portada || "assets/images/books/default.jpg";
        const autor = escapeHTML(libro.autor || "Autor Desconocido");
        const titulo = escapeHTML(libro.titulo_libro || "Sin Título");
        const resumen = escapeHTML(libro.resumen || "Una obra destacada y altamente solicitada por nuestra comunidad.");
        container.innerHTML = `
        <div class="book-week-cover" data-aos="fade-right">
            <img src="${escapeHTML(portada)}" alt="${titulo}">
        </div>
        <div class="book-week-content" data-aos="fade-left">
            <div class="book-week-eyebrow">
                <span class="section-label">✦ Recomendación del Curador ✦</span>
                <span class="book-week-eyebrow-dot"></span>
                <span>Obra Selecta</span>
            </div>
            <h2 class="book-week-title">${titulo}</h2>
            <p class="book-week-author">Por ${autor}</p>
            <p class="book-week-copy">${resumen}</p>
            <a href="#" class="btn btn-primary btn-glow">
                <span class="material-symbols-outlined">menu_book</span>
                Descubrir la Obra
            </a>
        </div>`;
        if (typeof AOS !== "undefined") AOS.refresh();
    };
  
    /* ---------- Cargar Data ---------- */
    const loadLandingContent = async () => {
        try {
            const res = await fetch("backend/get_landing_books.php");
            if (!res.ok) throw new Error(`HTTP ${res.status}`);
            const data = await res.json();
            renderizarTarjetas(data.libros);
            renderizarRecomendado(data.recomendado);
        } catch (err) {
            console.warn("No se pudo cargar el JSON dinámico.", err);
            renderizarTarjetas([]);
            renderizarRecomendado(null);
        }
    };
  
    document.addEventListener("DOMContentLoaded", () => {
        loadLandingContent();
        onScrollOrResize();
    });
    window.addEventListener("scroll", onScrollOrResize, { passive: true });
    window.addEventListener("resize", onScrollOrResize);
    window.addEventListener("load", onScrollOrResize);
  })();