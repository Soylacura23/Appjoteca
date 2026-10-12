<?php
require_once __DIR__ . '/../../../backend/config/auth.php';
require_once __DIR__ . '/../../../backend/config/user_context.php';
require_once __DIR__ . '/../../../backend/Database/conexion.php';
require_once __DIR__ . '/../../../backend/models/libro.php';
require_once __DIR__ . '/../../../backend/models/reserva.php';
require_once __DIR__ . '/../../../backend/helpers/libros.php';

requiereRol([1, 2, 4]);

$base_url = '/Appjoteca/';

// ── 1. Validar id ────────────────────────────────────────
$idLibro = (int) ($_GET['id'] ?? 0);
if ($idLibro <= 0) {
    header('Location: ../catalogo/catalogo.php');
    exit();
}

// ── 2. Obtener libro con el modelo ───────────────────────
$modeloLibro = new libro($connection);
$libro = $modeloLibro->obtenerLibro($idLibro);

if (!$libro) {
    header('Location: ../catalogo/catalogo.php');
    exit();
}

// ── 3. Relacionados (modelo) ─────────────────────────────
$librosRelacionados = $modeloLibro->obtenerRelacionados($idLibro, 4);

// ── 4. Reserva pendiente del usuario ─────────────────────
$modeloReserva = new Reserva($connection);
$reservaPendiente = $modeloReserva->obtenerPendienteDeUsuario(
    (int) $_SESSION['usuario_id'],
    $idLibro
);

// ── 5. Disponibilidad (helper) ───────────────────────────
$disp = disponibilidadLibro($libro);
$maxCantidad = max(1, (int) $disp['cantidad']);
$cantidadInicial = (int) ($_GET['cantidad'] ?? 1);
if ($cantidadInicial < 1) $cantidadInicial = 1;
if ($cantidadInicial > $maxCantidad) $cantidadInicial = $maxCantidad;

// ── 6. Datos para la vista ───────────────────────────────
$portada = urlPortada($libro['portada'] ?? '', $base_url);

$titulo  = $libro['titulo'] ?? 'Sin título';
$autores = $libro['autores'] ?: 'Autor desconocido';
$materia = $libro['materia'] ?? 'General';
$idioma  = $libro['nombre_idioma'] ?? 'Español';
$edicion = $libro['edicion'] ?? '';
$anio    = $libro['publicacion_year'] ?? '';
$edicionLabel = trim(($edicion !== '' ? $edicion : 'Edición') . ($anio ? ' · ' . $anio : ''));
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="<?php echo htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8'); ?>">
  <title>Reservación — <?php echo htmlspecialchars($titulo, ENT_QUOTES, 'UTF-8'); ?></title>

  <?php include_once '/Appjoteca/shared/layouts/anti-fouc.php'; ?>

  <!-- Fuentes -->
  <link href="https://fonts.googleapis.com/css2?family=Noto+Serif:ital,wght@0,400;0,700;1,300;1,400&family=Manrope:wght@300;400;500;700;800&display=swap" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200&display=swap" rel="stylesheet">

  <!-- Estilos compartidos -->
  <link rel="stylesheet" href="../../../shared/css/theme.css">
  <link rel="stylesheet" href="../../../shared/css/components/notifications.css">
  <link rel="stylesheet" href="../../../shared/css/components/navbar.css">
  <link rel="stylesheet" href="../../../shared/css/components/footer.css">
   <link rel="stylesheet" href="../../../shared/css/components/related-section.css">
  <link rel="stylesheet" href="../../../shared/css/components/book-card.css">


  <!-- Estilo propio -->
  <link rel="stylesheet" href="reservacion.css">
  <link rel="stylesheet" href="/Appjoteca/shared/css/components/topbar-search.css">
  <script src="https://cdn.jsdelivr.net/npm/fuse.js@7.0.0" defer></script>
  <script src="/Appjoteca/shared/js/components/topbar-search.js" defer></script>
  <?php require __DIR__ . '/../../../shared/layouts/favicon.php'; ?>
</head>
<body>

  <!-- OVERLAY GLOBAL -->
  <div id="overlay" class="overlay" aria-hidden="true"></div>
  <?php include __DIR__ . '/../../../shared/layouts/notifications.php'; ?>
  <?php include __DIR__ . '/../../../shared/layouts/menu-off-canvas.php'; ?>
  <?php include __DIR__ . '/../../../shared/layouts/topbar.php'; ?>

  <!-- MENÚ MÓVIL -->
  <?php include_once __DIR__ . '/../../../shared/layouts/menu-movil.php'; ?>


  <!-- CONTENIDO PRINCIPAL -->
  <main class="reservacion-main" id="main-content">
    <div class="reservacion-inner">

      <!-- Botón volver -->
      <button class="back-btn" onclick="history.back()" aria-label="Volver al catálogo">
        <span class="material-symbols-outlined">arrow_back</span>
        Volver al Catálogo
      </button>


      <!-- LAYOUT PRINCIPAL: Portada + Formulario -->
      <div class="reservation-layout">

        <!-- ── Columna izquierda: Mini portada + info ── -->
        <aside class="book-aside" aria-label="Información del libro">
          <div class="aside-cover-wrap">
            <img
              src="<?php echo htmlspecialchars($portada, ENT_QUOTES, 'UTF-8'); ?>"
              alt="<?php echo htmlspecialchars($titulo, ENT_QUOTES, 'UTF-8'); ?>"
              class="aside-cover"
              loading="eager"
            >
          </div>
          <div class="aside-info">
            <h1 class="aside-title"><?php echo htmlspecialchars($titulo, ENT_QUOTES, 'UTF-8'); ?></h1>
            <p class="aside-author"><?php echo htmlspecialchars($autores, ENT_QUOTES, 'UTF-8'); ?></p>
            <div class="aside-meta">
              <span class="aside-badge <?php echo $disp['clase']; ?>">
                <?php echo htmlspecialchars($disp['texto'], ENT_QUOTES, 'UTF-8'); ?>
                <?php if ($disp['disponible']): ?>
                  · <?php echo (int) $disp['cantidad']; ?> en stock
                <?php endif; ?>
              </span>
              <?php if ($edicionLabel): ?>
                <span class="aside-edition"><?php echo htmlspecialchars($edicionLabel, ENT_QUOTES, 'UTF-8'); ?></span>
              <?php endif; ?>
            </div>
            <div class="aside-details">
              <div class="aside-detail">
                <span class="aside-detail-label">Género</span>
                <span class="aside-detail-value"><?php echo htmlspecialchars($materia, ENT_QUOTES, 'UTF-8'); ?></span>
              </div>
              <div class="aside-detail">
                <span class="aside-detail-label">Idioma</span>
                <span class="aside-detail-value"><?php echo htmlspecialchars($idioma, ENT_QUOTES, 'UTF-8'); ?></span>
              </div>
              <div class="aside-detail">
                <span class="aside-detail-label">Ejemplares</span>
                <span class="aside-detail-value"><?php echo (int) $disp['cantidad']; ?> disponibles</span>
              </div>
            </div>
          </div>
        </aside>

        <!-- ── Columna derecha: Formulario o estado de reserva ── -->
        <section class="reservation-section" aria-label="Solicitud de reservación">
          <div class="reservation-card">

            <?php if ($reservaPendiente): ?>
              <!-- ═══ ESTADO: Ya hay reserva pendiente ═══ -->
              <h2 class="reservation-title">
                <span class="material-symbols-outlined">pending_actions</span>
                Solicitud Pendiente
              </h2>

              <p class="pending-note">
                Ya tienes una solicitud <strong>pendiente</strong> de este libro.
                El bibliotecario la revisará hasta que la apruebe o la rechace.
                Puedes cancelarla cuando quieras.
              </p>

              <div class="reservation-summary">
                <div class="summary-item">
                  <span class="summary-label">Solicitada el</span>
                  <span class="summary-value">
                    <?php echo htmlspecialchars($reservaPendiente['fecha_reserva'] ?? '—', ENT_QUOTES, 'UTF-8'); ?>
                  </span>
                </div>
                <div class="summary-item">
                  <span class="summary-label">Fecha límite</span>
                  <span class="summary-value">
                    <?php echo htmlspecialchars($reservaPendiente['fecha_limite'] ?? '—', ENT_QUOTES, 'UTF-8'); ?>
                  </span>
                </div>
              </div>

              <div class="form-actions">
                <button
                  type="button"
                  class="btn btn-secondary btn-cancelar-reserva"
                  id="btn-cancelar-reserva"
                  data-id-reserva="<?php echo (int) $reservaPendiente['id_reserva']; ?>"
                >
                  <span class="material-symbols-outlined">cancel</span>
                  Cancelar solicitud
                </button>
                <a href="../vista-libro/book-view.php?id=<?php echo $idLibro; ?>" class="btn btn-secondary">
                  <span class="material-symbols-outlined">arrow_back</span>
                  Ver libro
                </a>
              </div>

            <?php elseif (!$disp['disponible']): ?>
              <!-- ═══ ESTADO: Sin stock ═══ -->
              <h2 class="reservation-title">
                <span class="material-symbols-outlined">block</span>
                No disponible
              </h2>
              <p class="pending-note">
                No hay ejemplares disponibles de este libro en este momento.
                Intenta de nuevo más tarde o contacta al bibliotecario.
              </p>

              <div class="form-actions">
                <a href="../vista-libro/book-view.php?id=<?php echo $idLibro; ?>" class="btn btn-secondary">
                  <span class="material-symbols-outlined">arrow_back</span>
                  Volver al libro
                </a>
              </div>

            <?php else: ?>
              <!-- ═══ ESTADO: Formulario de reservación ═══ -->
              <h2 class="reservation-title">
                <span class="material-symbols-outlined">bookmark_add</span>
                Solicitud de Reservación
              </h2>

              <form id="reservation-form"
                    data-id-libro="<?php echo $idLibro; ?>"
                    data-max-cantidad="<?php echo $maxCantidad; ?>">

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
                  <p class="loan-note">
                    Máximo disponible: <?php echo $maxCantidad; ?> ejemplar<?php echo $maxCantidad === 1 ? '' : 'es'; ?>.
                  </p>
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
                    <p class="loan-note">Máximo: 10 días desde el primer día hábil, con un día de tolerancia.</p>
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
              </form>
            <?php endif; ?>

          </div>
        </section>

      </div><!-- /reservation-layout -->


      <!-- LIBROS RELACIONADOS -->
      <?php include __DIR__ . '/../../../shared/layouts/related-section.php'; ?>

    </div>
  </main>


  <!-- FOOTER -->
  <?php include __DIR__ . '/../../../shared/layouts/footer.php'; ?>

  <!-- Scripts compartidos -->
  <script src="../../../shared/js/components/navbar.js"></script>
  <script src="/Appjoteca/shared/js/components/notifications.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>
  <script src="../../../shared/js/components/alert.js"></script>
  <script src="../../../shared/js/global.js"></script>

  <!-- Script propio -->
  <script src="reservacion.js"></script>
  <script src="/Appjoteca/shared/js/register-sw.js"></script>
</body>
</html>