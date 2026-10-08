<?php
require_once __DIR__ . '/../../../backend/config/auth.php';
require_once __DIR__ . '/../../../backend/config/user_context.php';
require_once __DIR__ . '/../../../backend/Database/conexion.php';
require_once __DIR__ . '/../../../backend/models/libro.php';
require_once __DIR__ . '/../../../backend/models/reserva.php';
require_once __DIR__ . '/../../../backend/helpers/libros.php';

$idLibro = (int) ($_GET['id'] ?? 0);
if ($idLibro <= 0) {
    header('Location: ../vista-libro/book-view.php');
    exit();
}

$modeloLibro = new libro($connection);
$libro = $modeloLibro->obtenerLibro($idLibro);

if (!$libro) {
    header('Location: ../../biblioteca-digital/index.php');
    exit();
}

$librosRelacionados = $modeloLibro->obtenerRelacionados($idLibro, 4);
$reservaPendiente = (new Reserva($connection))->obtenerPendienteDeUsuario((int) $_SESSION['usuario_id'], $idLibro);
$disp = disponibilidadLibro($libro);

$maxCantidad = max(1, (int) $disp['cantidad']);
$cantidadInicial = (int) ($_GET['cantidad'] ?? 1);

if ($cantidadInicial < 1) $cantidadInicial = 1;
if ($cantidadInicial > $maxCantidad) $cantidadInicial = $maxCantidad;

$portada = urlPortada($libro['portada'] ?? '');
$portadaDefault = __DIR__ . '/../../../assets/images/books/default-cover.jpg';

$titulo  = $libro['titulo'] ?? 'Sin título';
$autores = $libro['autores'] ?: 'Autor desconocido';
$materia = $libro['materia'] ?? 'General';
$idioma  = $libro['idioma_nombre'] ?? ($libro['nombre_idioma'] ?? 'Español');
$edicion = $libro['edicion'] ?? '';
$anio    = $libro['publicacion_year'] ?? '';
$edicionLabel = trim(($edicion !== '' ? $edicion : 'Edición') . ($anio ? ' · ' . $anio : ''));
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Reservación — Appjoteca</title>

  <!-- Fuentes -->
  <link href="https://fonts.googleapis.com/css2?family=Noto+Serif:ital,wght@0,400;0,700;1,300;1,400&family=Manrope:wght@300;400;500;700;800&display=swap" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200&display=swap" rel="stylesheet">

  <!-- Estilos compartidos -->
  <link rel="stylesheet" href="../../../shared/css/theme.css">
  <link rel="stylesheet" href="../../../shared/css/components/notifications.css">
  <link rel="stylesheet" href="../../../shared/css/components/navbar.css">
  <link rel="stylesheet" href="../../../shared/css/components/footer.css">
  <link rel="stylesheet" href="../../../shared/css/components/book-card.css">
  <link rel="stylesheet" href="../../../shared/css/components/related-section.css">

  <!-- Estilo propio -->
  <link rel="stylesheet" href="reservacion.css">
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
      
      </a>
      <button class="mobile-menu-close" aria-label="Cerrar menú">
        <span class="material-symbols-outlined">close</span>
      </button>
    </div>
    <nav class="mobile-menu-nav" aria-label="Navegación">
      <a href="../../biblioteca-digital/index.php" class="mobile-nav-link" data-nav="catalogo">Catálogo</a>
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
  <main class="reservacion-main" id="main-content">
    <div class="reservacion-inner">

      <!-- Botón volver -->
      <button class="back-btn" onclick="history.back()" aria-label="Volver al catálogo">
        <span class="material-symbols-outlined">arrow_back</span>
        Volver al Catálogo
      </button>


      <!-- ════════════════════════════════════════
           LAYOUT PRINCIPAL: Portada + Formulario
      ═══════════════════════════════════════════ -->
      <div class="reservation-layout">

        <!-- ── Columna izquierda: Mini portada + info ── -->
        <aside class="book-aside" aria-label="Información del libro">
          <div class="aside-cover-wrap">
            <img
              src="../../../assets/images/books/cien-años-de-soledad.jpg"
              alt="Cien años de Soledad"
              class="aside-cover"
              loading="eager"
            >
          </div>
          <div class="aside-info">
            <h1 class="aside-title">Cien Años de Soledad</h1>
            <p class="aside-author">Gabriel García Márquez</p>
            <div class="aside-meta">
              <span class="aside-badge available">Disponible</span>
              <span class="aside-edition">Coleccionista · 1924</span>
            </div>
            <div class="aside-details">
              <div class="aside-detail">
                <span class="aside-detail-label">Género</span>
                <span class="aside-detail-value">Realismo Mágico</span>
              </div>
              <div class="aside-detail">
                <span class="aside-detail-label">Idioma</span>
                <span class="aside-detail-value">Español</span>
              </div>
              <div class="aside-detail">
                <span class="aside-detail-label">Calificación</span>
                <span class="aside-detail-value">
                  4.6 <span class="material-symbols-outlined">star</span>
                </span>
              </div>
            </div>
          </div>
        </aside>

        <!-- ── Columna derecha: Formulario de reservación ── -->
        <section class="reservation-section" aria-label="Solicitud de reservación">
          <div class="reservation-card">
            <h2 class="reservation-title">
              <span class="material-symbols-outlined">bookmark_add</span>
              Solicitud de Reservación
            </h2>

            <?php if ($reservaPendiente): ?>
              <p class="pending-note">
                Ya tienes una solicitud <strong>pendiente</strong> de este libro.
                El bibliotecario la verá hasta que la apruebe o la rechace. Puedes cancelarla cuando quieras.
              </p>
              <div class="form-actions">
                <button
                  type="button"
                  class="btn btn-secondary"
                  id="cancel-reserva-btn"
                  data-id-reserva="<?php echo (int) $reservaPendiente['id_reserva']; ?>"
                >
                  Cancelar solicitud
                </button>
              </div>
            <?php else: ?>
              
              <form id="reservation-form" data-id-libro="<?php echo $idLibro; ?>" data-max-cantidad="<?php echo $maxCantidad; ?>">
                <div class="form-group">
                  <label for="qty-input">CANTIDAD DE EJEMPLARES</label>
                  <div class="qty-control" aria-label="Cantidad de ejemplares a reservar">
                    <button type="button" class="qty-btn" id="qty-minus" aria-label="Disminuir cantidad">−</button>
                    <input
                      type="number"
                      id="qty-input"
                      name="cantidad"
                      class="qty-input"
                      value="<?php echo $cantidadInicial; ?>"
                      min="1"
                      max="<?php echo $maxCantidad; ?>"
                      data-max="<?php echo $maxCantidad; ?>"
                      aria-label="Cantidad"
                      required
                    >
                    <button type="button" class="qty-btn" id="qty-plus" aria-label="Aumentar cantidad">+</button>
                  </div>
                  <p class="loan-note">Máximo disponible: <?php echo $maxCantidad; ?> ejemplar<?php echo $maxCantidad === 1 ? '' : 'es'; ?>.</p>
                </div>

                <div class="form-group">
                  <label for="reason">RAZÓN DE RESERVA (OPCIONAL)</label>
                  <textarea
                    id="reason"
                    name="reason"
                    maxlength="100"
                    placeholder="Describe brevemente el motivo..."
                  ></textarea>
                </div>

              <div class="date-row">
                <div class="form-group">
                  <label for="return-date">FECHA DE DEVOLUCIÓN ESTIMADA</label>
                  <input type="date" id="return-date" name="returnDate" required>
                  <p class="loan-note">*El plazo máximo de préstamo es de 2 meses para esta edición.</p>
                </div>
                <a href="#" class="rules-link">
                  Ver Normas
                  <span class="material-symbols-outlined">open_in_new</span>
                </a>
              </div>

              <div class="form-actions">
                <button type="submit" class="btn btn-primary">
                  <span class="material-symbols-outlined">check</span>
                  Confirmar
                </button>
                <button type="button" id="cancel-btn" class="btn btn-secondary">
                  Cancelar
                </button>
              </div>

              <div id="message" class="message"></div>

              <?php endif; ?>
            </form>
          </div>
        </section>

      </div>


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

    </div>
  </main>


  <!-- ══════════════════════════════════════════
       FOOTER
  ══════════════════════════════════════════════ -->
  <?php include __DIR__ . '/../../../shared/layouts/footer.php'; ?>

  <!-- Scripts compartidos -->
  <script src="../../../shared/js/components/navbar.js"></script>
  <script src="/Appjoteca/shared/js/components/notifications.js"></script>
  <script src="../../../shared/js/components/book-cards.js"></script>
  <script src="../../../shared/js/components/alert.js"></script>
  <script src="../../../shared/js/global.js"></script>

  <!-- Script propio -->
  <script src="reservacion.js"></script>
</body>
</html>