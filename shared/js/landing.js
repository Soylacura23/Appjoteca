(function () {
    "use strict";
  
    var reduceMotion = window.matchMedia("(prefers-reduced-motion: reduce)").matches;
  
    var header = document.getElementById("mainHeader");
    function updateHeader() {
      if (!header) return;
      if (window.scrollY > 24) {
        header.classList.add("is-scrolled");
      } else {
        header.classList.remove("is-scrolled");
      }
    }
  
    /* ============================================ */
    /*   REVEAL AL HACER SCROLL (encabezados de sección) */
    /* ============================================ */
    var revealTargets = document.querySelectorAll("[data-reveal]");
    if ("IntersectionObserver" in window && revealTargets.length) {
      var revealObserver = new IntersectionObserver(
        function (entries) {
          entries.forEach(function (entry) {
            if (entry.isIntersecting) {
              entry.target.classList.add("is-visible");
              revealObserver.unobserve(entry.target);
            }
          });
        },
        { threshold: 0.2, rootMargin: "0px 0px -10% 0px" }
      );
      revealTargets.forEach(function (el) {
        revealObserver.observe(el);
      });
    } else {
      revealTargets.forEach(function (el) {
        el.classList.add("is-visible");
      });
    }
  
    /* ============================================ */
    /*   PARALLAX SUAVE EN FONDOS DE IMAGEN         */
    /* ============================================ */
    var parallaxEls = document.querySelectorAll("[data-parallax]");
    function updateParallax() {
      if (reduceMotion || !parallaxEls.length) return;
      var viewportH = window.innerHeight;
      parallaxEls.forEach(function (el) {
        var rect = el.parentElement.getBoundingClientRect();
        if (rect.bottom < 0 || rect.top > viewportH) return;
        var speed = parseFloat(el.getAttribute("data-parallax")) || 0.15;
        var offset = (rect.top - viewportH / 2) * speed;
        el.style.transform = "translateY(" + offset + "px)";
      });
    }
  
    /* ============================================ */
    /*   SCROLL HORIZONTAL "ENGANCHADO" — LIBROS    */
    /* ============================================ */
    var booksScroll = document.getElementById("booksScroll");
    var booksTrack = document.getElementById("booksTrack");
    var booksViewport = booksScroll ? booksScroll.querySelector(".books-track-viewport") : null;
  
    function isHorizontalScrollActive() {
      return booksScroll && booksTrack && booksViewport && window.innerWidth > 768 && !reduceMotion;
    }
  
    function updateBooksScroll() {
      if (!isHorizontalScrollActive()) return;
  
      var scrollRect = booksScroll.getBoundingClientRect();
      var trackWidth = booksTrack.scrollWidth;
      var viewportWidth = booksViewport.clientWidth;
      var maxTranslate = Math.max(trackWidth - viewportWidth, 0);
  
      // Distancia vertical total disponible dentro de .books-scroll para
      // "alimentar" el desplazamiento horizontal (altura total - 100vh pegado).
      var totalScrollable = booksScroll.offsetHeight - window.innerHeight;
      if (totalScrollable <= 0) return;
  
      var progressed = -scrollRect.top;
      var progress = progressed / totalScrollable;
      progress = Math.min(Math.max(progress, 0), 1);
  
      var translate = -progress * maxTranslate;
      booksTrack.style.transform = "translateX(" + translate + "px)";
    }
  
    function resetBooksScroll() {
      if (booksTrack) booksTrack.style.transform = "";
    }
  
    /* ============================================ */
    /*   BUCLE DE SCROLL (rAF, un solo listener)     */
    /* ============================================ */
    var ticking = false;
    function onScrollOrResize() {
      if (!ticking) {
        window.requestAnimationFrame(function () {
          updateHeader();
          updateParallax();
          if (isHorizontalScrollActive()) {
            updateBooksScroll();
          } else {
            resetBooksScroll();
          }
          ticking = false;
        });
        ticking = true;
      }
    }
  
    window.addEventListener("scroll", onScrollOrResize, { passive: true });
    window.addEventListener("resize", onScrollOrResize);
    window.addEventListener("load", onScrollOrResize);
    document.addEventListener("DOMContentLoaded", onScrollOrResize);
    onScrollOrResize();
  })();