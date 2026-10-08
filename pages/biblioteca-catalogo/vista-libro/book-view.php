<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cien Años de Soledad</title>

    <!-- Fuentes -->
    <link href="https://fonts.googleapis.com/css2?family=Noto+Serif:ital,wght@0,400;0,700;1,300;1,400&family=Manrope:wght@300;400;500;700;800&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200&display=swap" rel="stylesheet">

    <
    <link rel="stylesheet" href="../../../shared/css/theme.css">
    <link rel="stylesheet" href="../../../shared/css/components/notifications.css">
    <link rel="stylesheet" href="../../../shared/css/components/navbar.css">
    <link rel="stylesheet" href="../../../shared/css/components/book-card.css">
    <link rel="stylesheet" href="book-view.css">
    <link rel="stylesheet" href="../../../shared/css/components/footer.css">
    
    <link rel="stylesheet" href="/Appjoteca/shared/css/components/topbar-search.css">
    <script src="https://cdn.jsdelivr.net/npm/fuse.js@7.0.0" defer></script>
    <script src="/Appjoteca/shared/js/components/topbar-search.js" defer></script>
</head>
<body>

    <!-- ══════════════════════════════════════════
         OVERLAY GLOBAL
    ══════════════════════════════════════════════ -->
    <div id="overlay" class="overlay" aria-hidden="true"></div>
    <?php include __DIR__ . '/../../../shared/layouts/notifications.php'; ?>
    <?php include __DIR__ . '/../../../shared/layouts/menu-off-canvas.php'; ?>

    <?php include __DIR__ . '/../../../shared/layouts/topbar.php'; ?>


    <!-- ══════════════════════════════════════════
         MENÚ MÓVIL
    ══════════════════════════════════════════════ -->
    <div class="mobile-menu-overlay" aria-hidden="true"></div>

    <div class="mobile-menu" id="mobileMenu" role="dialog" aria-modal="true" aria-label="Menú de navegación">
        <div class="mobile-menu-header">
            <a href="#" class="mobile-menu-logo topbar-logo">
                <div class="logo" aria-hidden="true"></div>
                <span class="logo-text">AppJoteca</span>
            </a>
            <button class="mobile-menu-close" aria-label="Cerrar menú">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>

        <nav class="mobile-menu-nav" aria-label="Navegación">
            <a href="/pages/biblioteca-digital/index.html" class="mobile-nav-link active" data-nav="catalogo">Catálogo</a>
            <a href="#" class="mobile-nav-link" data-nav="biblioteca">Mi Biblioteca</a>
            <a href="#" class="mobile-nav-link" data-nav="panel">Panel</a>
        </nav>

        <div class="mobile-menu-divider" aria-hidden="true"></div>

        <div class="mobile-menu-section">
            <p class="mobile-menu-section-title">Más como este</p>
            <div class="mobile-menu-items">
                <a href="#" class="mobile-menu-item">
                    <div class="mobile-menu-item-thumb">
                        <img src="https://lh3.googleusercontent.com/aida-public/AB6AXuB6sz0IXEI3qaMrZk66pnYDlVcWcCOJEbeils34iNjsq37T3ClIJQ3dypWMnvOO73msvMa57bwkV7qVoAhK0D5N-7TAWI7Gqy_ds69ckiV90qrwLsVGzuaf69YVcTpXvAdR8fLj7BT-SQG69CQgiQYX_Mtr7hDjF-mzRaSqrrQ2xH_Ua5_HIt6GlOusj3nq1LCjZuyr3mfWno3XxRx9mL8vTM58QGIGk_vMWCCgSTn0lt15IO2APk-7GNm2d8sQEUQB11Jx9iQoG_bK" alt="Echoes of Autumn" loading="lazy">
                    </div>
                    <div class="mobile-menu-item-info">
                        <p class="mobile-menu-item-name">Echoes of Autumn</p>
                        <p class="mobile-menu-item-sub">J.L. Sterling · Realismo Mágico</p>
                    </div>
                    <span class="material-symbols-outlined mobile-menu-item-arrow">arrow_forward</span>
                </a>
                <a href="#" class="mobile-menu-item">
                    <div class="mobile-menu-item-thumb">
                        <img src="https://lh3.googleusercontent.com/aida-public/AB6AXuBdsL-KcmQL6YlyrQLrMMEMWsthcZY8c3FlkRzE_7PlH0WvlP3_5eSMMAuOkd-4jYsTIuCW05oTBBnVqoCTxNDkrO_3cSFwDpitGXkdiJtOvuMwpD-NuDY4AyrzYUkpsIc_5lvFjrmwByS7yAH0ujucNWcxM-Ph9F_Z7cFvFo2mQlSVPQYddp1qNIe_Srgxs3FFmihcGKmUXehqM1QMr3yPKPkvs-VrVqhYSw0zGZIrwJtJgONpxiutJOouvMkI4ktvLYJ4WRIRbuQo" alt="The Alchemist's Son" loading="lazy">
                    </div>
                    <div class="mobile-menu-item-info">
                        <p class="mobile-menu-item-name">The Alchemist's Son</p>
                        <p class="mobile-menu-item-sub">Marcus Finch · Fantasía</p>
                    </div>
                    <span class="material-symbols-outlined mobile-menu-item-arrow">arrow_forward</span>
                </a>
            </div>
            <a href="#" class="mobile-menu-all">
                <span>Ver todo el catálogo</span>
                <span class="material-symbols-outlined">chevron_right</span>
            </a>
        </div>

        <div class="mobile-menu-footer">
            <span class="mobile-menu-tagline">Sistema Institucional</span>
            <div class="mobile-menu-footer-actions">
                <button class="notification-tray icon-btn" aria-label="Notificaciones" aria-expanded="false">
                    <span class="material-symbols-outlined">notifications</span>
                    <span class="notification-badge" aria-hidden="true"></span>
                </button>
                <div id="profile-button-menu-footer"></div>
            </div>
        </div>
    </div>


    <!-- ══════════════════════════════════════════
         CONTENIDO PRINCIPAL
    ══════════════════════════════════════════════ -->
    <main class="catalogo-main" id="main-content">
        <div class="catalogo-inner">

            <!-- Botón volver -->
            <button class="back-btn" onclick="history.back()" aria-label="Volver al catálogo">
                <span class="material-symbols-outlined">arrow_back</span>
                Volver al Catálogo
            </button>


            <!-- ──────────────────────────────────────
                 HERO: Portada + Detalles
            ─────────────────────────────────────────── -->
            <section class="book-hero" aria-label="Detalle del libro">

                <!-- Portada -->
                <div class="book-cover-col">
                    <div class="book-cover-glow" aria-hidden="true"></div>
                    <div class="book-cover-frame">
                        <img
                            src="../../../assets/images/books/cien-años-de-soledad.jpg"
                            alt="Portada de Cien años de soledad"
                            class="book-cover-img"
                            loading="eager"
                        >
                    </div>
                </div>

                <!-- Detalles -->
                <div class="book-details-col">

                    <!-- Estado y edición -->
                    <div class="book-meta-row">
                        <span class="availability-badge available">Disponible</span>
                        <span class="book-edition-label">Primera Edición &nbsp;·&nbsp; 2023</span>
                    </div>

                    <!-- Título y autor -->
                    <div>
                        <h1 class="book-title-main">Cien años de soledad</h1>
                        <p class="book-author-main">Por Gabriel García Márquez</p>
                    </div>

                    <!-- Metadatos -->
                    <div class="book-metadata" role="list" aria-label="Información del libro">
                        
                        <div class="meta-item" role="listitem">
                            <span class="meta-label">Género</span>
                            <span class="meta-value">Realismo Mágico</span>
                        </div>
                        <div class="meta-item" role="listitem">
                            <span class="meta-label">Calificación</span>
                            <div class="meta-rating">
                                <span class="meta-rating-score">4.8</span>
                                <span class="material-symbols-outlined">star</span>
                            </div>
                        </div>
                    </div>

                    <!-- Sinopsis -->
                    <div class="synopsis-section">
                        <h2 class="synopsis-title">Sinopsis</h2>
                        <p class="synopsis-text">
                            Muchos años después, frente al pelotón de fusilamiento, el coronel Aureliano Buendía había de recordar aquella tarde remota en que su padre lo llevó a conocer el hielo. Macondo era entonces una aldea de veinte casas de barro y cañabrava construidas a la orilla de un río de aguas diáfanas que se precipitaban por un lecho de piedras pulidas, blancas y enormes como huevos prehistóricos.
                        </p>
                    </div>

                    <!-- Tags -->
                    <div class="book-tags-detail" role="list" aria-label="Etiquetas">
                        <span class="book-tag-detail" role="listitem">Realismo Mágico</span>
                        <span class="book-tag-detail" role="listitem">Naturaleza</span>
                        <span class="book-tag-detail" role="listitem">Memoria</span>
                        <span class="book-tag-detail" role="listitem">Misterio</span>
                    </div>

                    <!-- Acciones -->
                    <div class="book-actions">
                        <button class="btn-reservar" id="btn-reservar" onclick="cambiarPagina()">
                            <span class="material-symbols-outlined">bookmark_add</span>
                            Reservar
                        </button>
                        <button class="btn-add-library" id="btn-add-library">
                            <span class="material-symbols-outlined">bookmark</span>
                            Agregar a mi Biblioteca
                        </button>
                    </div>

                </div><!-- /book-details-col -->
            </section><!-- /book-hero -->


            <!-- ──────────────────────────────────────
                 LIBROS RELACIONADOS
            ─────────────────────────────────────────── -->
            <section class="related-section" aria-label="Libros relacionados">

                <div class="related-header">
                    <div class="related-header-text">
                        <h2 class="related-title">Libros Relacionados</h2>
                        <p class="related-subtitle">Seleccionados a partir de tu descubrimiento reciente.</p>
                    </div>
                    <button class="related-view-all">Ver todos</button>
                </div>

                <div class="related-grid">

                    <!-- Card 1 -->
                    <article class="book-card" aria-label="Echoes of Autumn" tabindex="0" role="button">
                        <div class="book-cover-wrapper">
                            <img
                                src="https://lh3.googleusercontent.com/aida-public/AB6AXuB6sz0IXEI3qaMrZk66pnYDlVcWcCOJEbeils34iNjsq37T3ClIJQ3dypWMnvOO73msvMa57bwkV7qVoAhK0D5N-7TAWI7Gqy_ds69ckiV90qrwLsVGzuaf69YVcTpXvAdR8fLj7BT-SQG69CQgiQYX_Mtr7hDjF-mzRaSqrrQ2xH_Ua5_HIt6GlOusj3nq1LCjZuyr3mfWno3XxRx9mL8vTM58QGIGk_vMWCCgSTn0lt15IO2APk-7GNm2d8sQEUQB11Jx9iQoG_bK"
                                alt="Echoes of Autumn"
                                class="book-cover"
                                loading="lazy"
                            >
                            <span class="book-badge">Ficción</span>
                            <div class="book-card-cta">
                                <button class="book-card-cta-btn">
                                    <span class="material-symbols-outlined">auto_stories</span>
                                    Ver Libro
                                </button>
                            </div>
                        </div>
                        <div class="book-info">
                            <h4 class="book-title">Echoes of Autumn</h4>
                            <p class="book-author">J.L. Sterling</p>
                        </div>
                    </article>

                    <!-- Card 2 -->
                    <article class="book-card" aria-label="The Alchemist's Son" tabindex="0" role="button">
                        <div class="book-cover-wrapper">
                            <img
                                src="https://lh3.googleusercontent.com/aida-public/AB6AXuBdsL-KcmQL6YlyrQLrMMEMWsthcZY8c3FlkRzE_7PlH0WvlP3_5eSMMAuOkd-4jYsTIuCW05oTBBnVqoCTxNDkrO_3cSFwDpitGXkdiJtOvuMwpD-NuDY4AyrzYUkpsIc_5lvFjrmwByS7yAH0ujucNWcxM-Ph9F_Z7cFvFo2mQlSVPQYddp1qNIe_Srgxs3FFmihcGKmUXehqM1QMr3yPKPkvs-VrVqhYSw0zGZIrwJtJgONpxiutJOouvMkI4ktvLYJ4WRIRbuQo"
                                alt="The Alchemist's Son"
                                class="book-cover"
                                loading="lazy"
                            >
                            <span class="book-badge">Fantasía</span>
                            <div class="book-card-cta">
                                <button class="book-card-cta-btn">
                                    <span class="material-symbols-outlined">auto_stories</span>
                                    Ver Libro
                                </button>
                            </div>
                        </div>
                        <div class="book-info">
                            <h4 class="book-title">The Alchemist's Son</h4>
                            <p class="book-author">Marcus Finch</p>
                        </div>
                    </article>

                    <!-- Card 3 -->
                    <article class="book-card" aria-label="Stellar Horizons" tabindex="0" role="button">
                        <div class="book-cover-wrapper">
                            <img
                                src="https://lh3.googleusercontent.com/aida-public/AB6AXuAMCUFmUn-oo6zsjcQFTl4sU6PDLJBdUsWIwp4ugrQdtGtw0-Q45jx6zLiUEMqyEvKzB7WrlFMoNff652wTL3zCiNfedaa92eM9DOhq2T39AVTBLpo9DNyCqor3DXNiB2YZnQF_QxCAZ3ZiI1b8HKYIyVvf1iE4_uuKr9pXWHPDGIRv2XJ28QIep90XpMhewrJbCGN18RAMe9McXDl6Nlf2CDHKrVbUt5CjOjkVfpWyAK4ZJhrssavbC4nVXZX9NLq_OyMkZNtd04R9"
                                alt="Stellar Horizons"
                                class="book-cover"
                                loading="lazy"
                            >
                            <span class="book-badge">Ciencia Ficción</span>
                            <div class="book-card-cta">
                                <button class="book-card-cta-btn">
                                    <span class="material-symbols-outlined">auto_stories</span>
                                    Ver Libro
                                </button>
                            </div>
                        </div>
                        <div class="book-info">
                            <h4 class="book-title">Stellar Horizons</h4>
                            <p class="book-author">Ava Thorne</p>
                        </div>
                    </article>

                    <!-- Card 4 -->
                    <article class="book-card" aria-label="Wilder Gardens" tabindex="0" role="button">
                        <div class="book-cover-wrapper">
                            <img
                                src="https://lh3.googleusercontent.com/aida-public/AB6AXuBq7ZJrRmZvX9fDKo2Y4mc687Jc9WTNoWXl1hlSEPTaq9h0hBAun_dunEim3g7zHf6ZmOZD7tPScAPXw8xyCKF6rTkt61JDIJ68Z4HVk_hxg0L3D-OvstfQNjEyErhfX3vitWUwBZSWAyDIwFXL7NKmVRwAfLWLbQMJefXcuQUysPIKTh-YcKm1BdXKG0vFCrTjwnc-gt6yS2BR3dPyd37DnHnj5G-7SMXk-5potelCeYVXkAIyfVLbrFrTwUePXuky2p7O8jQu8kDd"
                                alt="Wilder Gardens"
                                class="book-cover"
                                loading="lazy"
                            >
                            <span class="book-badge">Contemporáneo</span>
                            <div class="book-card-cta">
                                <button class="book-card-cta-btn">
                                    <span class="material-symbols-outlined">auto_stories</span>
                                    Ver Libro
                                </button>
                            </div>
                        </div>
                        <div class="book-info">
                            <h4 class="book-title">Wilder Gardens</h4>
                            <p class="book-author">S. Montgomery</p>
                        </div>
                    </article>

                </div><!-- /related-grid -->
            </section><!-- /related-section -->

        </div><!-- /catalogo-inner -->
    </main>


        <!-- ══════════════════════════════════════════
         FOOTER
    ══════════════════════════════════════════════ -->
    <footer class="footer" role="contentinfo">
        <div class="footer-inner">
            <div class="footer-brand">
                <span class="footer-logo">AppJoteca</span>
                <p class="footer-tagline">
                    Punto de acceso institucional para fomentar la lectura en los estudiantes de la institución.
                </p>
                <div class="footer-social">
                    <button class="footer-social-btn" aria-label="Sitio web">
                        <span class="material-symbols-outlined">language</span>
                    </button>
                    <button class="footer-social-btn" aria-label="Compartir">
                        <span class="material-symbols-outlined">share</span>
                    </button>
                    <button class="footer-social-btn" aria-label="Correo electrónico">
                        <span class="material-symbols-outlined">mail</span>
                    </button>
                </div>
            </div>

            <div class="footer-nav-cols">
                <div class="footer-col">
                    <p class="footer-col-title">Explorar</p>
                    <nav>
                        <a href="#">El Catálogo</a>
                        <a href="#">Nuevos Ingresos</a>
                        <a href="#">Mi Biblioteca</a>
                        <a href="#">Mapa Institucional</a>
                    </nav>
                </div>
                <div class="footer-col">
                    <p class="footer-col-title">Sistema</p>
                    <nav>
                        <a href="#">Términos de Uso</a>
                        <a href="#">Privacidad</a>
                        <a href="#">Soporte</a>
                        <a href="#">Accesibilidad</a>
                    </nav>
                </div>
                <div class="footer-col">
                    <p class="footer-col-title">Acceso</p>
                    <nav>
                        <a href="#">Acceso Institucional</a>
                        <a href="#">Panel Administrativo</a>
                        <a href="#">Contacto</a>
                    </nav>
                </div>
            </div>
        </div>

        <div class="footer-bottom">
            <p class="footer-copyright">
                &copy; 2024 AppJoteca &nbsp;·&nbsp; Sistema de Biblioteca Institucional
            </p>
        </div>
    </footer>


    <!-- Scripts -->
    <script src="../../../shared/js/components/navbar.js"></script>
    <script src="/Appjoteca/shared/js/components/notifications.js"></script>
    <script src="../../../shared/js/components/book-cards.js"></script>
    <script src="../../../shared/js/global.js"></script>
    <script src="book-view.js"></script>

</body>
</html>