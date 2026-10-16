<?php
require_once __DIR__ . '/../../backend/config/auth.php';
require_once __DIR__ . '/../../backend/config/user_context.php';

// Validar acceso exclusivo para Docente (Rol 2)
requiereRol([2]);

$mi_id = $_SESSION['id_usuario'] ?? $_SESSION['user_id'] ?? 0;
$mi_nombre = $_SESSION['nombre'] ?? 'Docente';
$mi_documento = $_SESSION['documento'] ?? 'Sin documento';

/* ==========================================================================
   CONSULTAS REALES A LA BASE DE DATOS
   ========================================================================== */

// 1. Mis Recordatorios/Notas activas
$teacherNotesQuery = $connection->prepare(
    'SELECT titulo, fecha_recordatorio, es_importante
     FROM notas_docente WHERE id_usuario = ?
     ORDER BY es_importante DESC, fecha_recordatorio IS NULL, fecha_recordatorio ASC, fecha_creacion DESC
     LIMIT 5'
);
$teacherNotesQuery->bind_param('i', $mi_id);
$teacherNotesQuery->execute();
$teacherNotesResult = $teacherNotesQuery->get_result();
$teacherNotes = [];
while ($note = $teacherNotesResult->fetch_assoc()) {
    $teacherNotes[] = $note;
}
$teacherNotesQuery->close();

// 2. Métricas reales del sistema y del docente
$queryStats = "SELECT 
    (SELECT COUNT(*) FROM ejemplares WHERE estado = 'Disponible') as libros_disponibles,
    (SELECT COUNT(p.id_prestamo) 
     FROM prestamos p 
     JOIN reservas r ON p.id_reserva = r.id_reserva 
     WHERE r.fk_id_usuario_reserva = ? AND p.estado = 'activo') as prestamos_activos,
    (SELECT COUNT(p.id_prestamo) 
     FROM prestamos p 
     JOIN reservas r ON p.id_reserva = r.id_reserva 
     WHERE r.fk_id_usuario_reserva = ? AND p.estado = 'activo' AND p.fecha_devolucion_prevista < CURDATE()) as prestamos_vencidos,
    (SELECT COUNT(*) FROM reservas WHERE fk_id_usuario_reserva = ?) as alumnos_consultaron";

$stmtStats = $connection->prepare($queryStats);
$stmtStats->bind_param("iii", $mi_id, $mi_id, $mi_id);
$stmtStats->execute();
$stats = $stmtStats->get_result()->fetch_assoc();
$stmtStats->close();

// 3. Mis Préstamos activos reales
$queryPrestamos = "SELECT l.titulo, p.fecha_devolucion_prevista 
                   FROM prestamos p
                   JOIN reservas r ON p.id_reserva = r.id_reserva
                   JOIN ejemplares e ON p.id_ejemplar = e.id_ejemplar
                   JOIN libros l ON e.fk_id_libro_ejemplar = l.id_libro
                   WHERE r.fk_id_usuario_reserva = ? AND p.estado = 'activo'
                   ORDER BY p.fecha_devolucion_prevista ASC LIMIT 3";
$stmtPres = $connection->prepare($queryPrestamos);
$stmtPres->bind_param("i", $mi_id);
$stmtPres->execute();
$prestamosActivos = $stmtPres->get_result()->fetch_all(MYSQLI_ASSOC);
$stmtPres->close();

// 4. Actividad reciente real
$queryActividad = "SELECT accion, detalle, fecha 
                   FROM historial 
                   WHERE id_usuario = ? 
                   ORDER BY fecha DESC LIMIT 3";
$stmtAct = $connection->prepare($queryActividad);
$stmtAct->bind_param("i", $mi_id);
$stmtAct->execute();
$actividadReciente = $stmtAct->get_result()->fetch_all(MYSQLI_ASSOC);
$stmtAct->close();

// 5. Último libro añadido
$queryNuevos = "SELECT titulo FROM libros WHERE fecha_eliminacion_libro IS NULL ORDER BY fecha_registro_libro DESC LIMIT 1";
$resNuevos = $connection->query($queryNuevos);
$nuevoLibro = $resNuevos ? $resNuevos->fetch_assoc() : null;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Docente | Dashboard - AppJoteca</title>

    <?php include_once __DIR__ . '/../../shared/layouts/anti-fouc.php'; ?>

    <!-- Fuentes e Íconos -->
    <link href="https://fonts.googleapis.com/css2?family=Noto+Serif:ital,wght@0,400;0,700;1,400;1,700&family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">

    <!-- Estilos del Sistema y del Módulo -->
    <link rel="stylesheet" href="../../shared/css/theme.css">
    <link rel="stylesheet" href="../../shared/css/components/notifications.css">
    <link rel="stylesheet" href="../../shared/css/components/navbar.css">
    <link rel="stylesheet" href="../../shared/css/components/footer.css">
    <link rel="stylesheet" href="../../shared/css/components/reading-summary.css">
    <link rel="stylesheet" href="docente.css">

    <link rel="stylesheet" href="/Appjoteca/shared/css/components/topbar-search.css">
    <script src="https://cdn.jsdelivr.net/npm/fuse.js@7.0.0" defer></script>
    <script src="/Appjoteca/shared/js/components/topbar-search.js" defer></script>
    
    <?php require __DIR__ . '/../../shared/layouts/favicon.php'; ?>
</head>
<body>

    <div id="overlay" class="overlay" aria-hidden="true"></div>

    <?php include __DIR__ . '/../../shared/layouts/notifications.php'; ?>
    <?php include __DIR__ . '/../../shared/layouts/menu-off-canvas.php'; ?>
    <?php include __DIR__ . '/../../shared/layouts/topbar.php'; ?>
    <?php include_once __DIR__ . '/../../shared/layouts/menu-movil.php'; ?>

    <main class="dashboard-main">
        <div class="dashboard-container">

            <!-- SALUDO -->
            <section class="welcome-section">
                <div class="welcome-text">
                    <h1 class="welcome-title"><span id="greetingTime"></span>, Prof. <?= htmlspecialchars($mi_nombre) ?></h1>
                    <p class="welcome-subtitle">Gestiona los libros y recursos pedagógicos para tus clases fácilmente</p>
                </div>
                <div class="welcome-meta">
                    <p class="welcome-info">Estado de cuenta: <strong>Al día</strong></p>
                    <p class="welcome-date" id="currentDate">Cargando fecha...</p>
                </div>
            </section>

            <?php include __DIR__ . '/../../shared/layouts/reading-summary.php'; ?>

            <!-- ACCIONES RÁPIDAS CON RUTAS VÁLIDAS -->
            <section class="quick-actions">
                <a href="tablero.php" class="action-card">
                    <span class="material-symbols-outlined action-icon">edit_note</span>
                    <div class="action-text">
                        <h3>Mi Tablero</h3>
                        <p>Notas y recordatorios privados</p>
                    </div>
                </a>
                <a href="../../pages/catalogo/index.php" class="action-card">
                    <span class="material-symbols-outlined action-icon">search</span>
                    <div class="action-text">
                        <h3>Catálogo General</h3>
                        <p>Explorar acervo escolar</p>
                    </div>
                </a>
                <a href="../../pages/history/historial.php" class="action-card">
                    <span class="material-symbols-outlined action-icon">menu_book</span>
                    <div class="action-text">
                        <h3>Mis Préstamos</h3>
                        <p>Gestión de ejemplares</p>
                    </div>
                </a>
                <a href="../../pages/history/historial.php" class="action-card">
                    <span class="material-symbols-outlined action-icon">bookmark</span>
                    <div class="action-text">
                        <h3>Reservas de Clase</h3>
                        <p>Solicitar colecciones</p>
                    </div>
                </a>
            </section>

            <!-- MÉTRICAS REALES -->
            <section class="metrics-grid">
                <div class="metric-card">
                    <div class="metric-header">
                        <span class="material-symbols-outlined metric-icon">library_books</span>
                        <span class="metric-number"><?= $stats['libros_disponibles'] ?? 0 ?></span>
                    </div>
                    <p class="metric-label">Libros disponibles</p>
                </div>

                <div class="metric-card">
                    <div class="metric-header">
                        <span class="material-symbols-outlined metric-icon">auto_stories</span>
                        <span class="metric-number"><?= $stats['prestamos_activos'] ?? 0 ?></span>
                    </div>
                    <p class="metric-label">Préstamos activos</p>
                </div>

                <div class="metric-card warning">
                    <div class="metric-header">
                        <span class="material-symbols-outlined metric-icon">event_busy</span>
                        <span class="metric-number"><?= $stats['prestamos_vencidos'] ?? 0 ?></span>
                    </div>
                    <p class="metric-label">Préstamos vencidos</p>
                </div>

                <div class="metric-card success">
                    <div class="metric-header">
                        <span class="material-symbols-outlined metric-icon">group</span>
                        <span class="metric-number"><?= $stats['alumnos_consultaron'] ?? 0 ?></span>
                    </div>
                    <p class="metric-label">Alumnos consultaron material</p>
                </div>
            </section>

            <!-- COLUMNAS PRINCIPALES -->
            <div class="dashboard-layout">

                <div class="dashboard-content">
                    <!-- RECURSOS DESDE docente-back.php -->
                    <section class="dashboard-block">
                        <div class="block-header">
                            <h2 class="block-title">Recursos Recomendados para Clase</h2>
                        </div>
                        <div class="books-grid" id="booksGrid">
                            <!-- Inyectado por docente.js -->
                        </div>
                    </section>

                    <!-- ACTIVIDAD RECIENTE DE LA TABLA historial -->
                    <section class="dashboard-block">
                        <div class="block-header">
                            <h2 class="block-title">Actividad Reciente</h2>
                        </div>
                        <div class="activity-card">
                            <ul class="activity-list">
                                <?php if (!empty($actividadReciente)): ?>
                                    <?php foreach ($actividadReciente as $act): ?>
                                        <li class="activity-item">
                                            <span class="material-symbols-outlined activity-icon">history</span>
                                            <div class="activity-details">
                                                <p class="activity-text"><strong><?= htmlspecialchars($act['accion']) ?></strong>: <?= htmlspecialchars($act['detalle']) ?></p>
                                                <span class="activity-time"><?= date('d/m/Y - H:i', strtotime($act['fecha'])) ?></span>
                                            </div>
                                        </li>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <li class="activity-item">
                                        <p class="activity-text">No registras actividad reciente.</p>
                                    </li>
                                <?php endif; ?>
                            </ul>
                        </div>
                    </section>
                </div>

                <aside class="dashboard-sidebar">
                    <!-- RECORDATORIOS DESDE notas_docente -->
                    <div class="sidebar-card teacher-reminders">
                        <div class="block-header">
                            <h3 class="sidebar-title">Mis recordatorios</h3>
                            <a href="tablero.php">Ver todos</a>
                        </div>
                        <?php if ($teacherNotes): ?>
                            <ul class="panel-list">
                                <?php foreach ($teacherNotes as $note): ?>
                                    <li>
                                        <span class="material-symbols-outlined"><?= (int) $note['es_importante'] ? 'push_pin' : 'event_note' ?></span>
                                        <div>
                                            <strong><?= htmlspecialchars($note['titulo'], ENT_QUOTES, 'UTF-8') ?></strong>
                                            <small><?= $note['fecha_recordatorio'] ? htmlspecialchars(date('d/m/Y', strtotime($note['fecha_recordatorio'])), ENT_QUOTES, 'UTF-8') : 'Sin fecha' ?></small>
                                        </div>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php else: ?>
                            <p class="teacher-empty">Aún no tienes notas. Crea un recordatorio para tenerlo a mano.</p>
                        <?php endif; ?>
                    </div>

                    <!-- PERFIL DOCENTE -->
                    <div class="sidebar-card">
                        <h3 class="sidebar-title">Perfil Docente</h3>
                        <div class="profile-summary-content">
                            <div class="profile-avatar-small">
                                <span class="material-symbols-outlined">school</span>
                            </div>
                            <div class="profile-details">
                                <h4>Prof. <?= htmlspecialchars($mi_nombre) ?></h4>
                                <span class="student-id">Doc: <?= htmlspecialchars($mi_documento); ?></span>
                            </div>
                        </div>
                        <div class="sidebar-actions-group">
                            <a href="../../pages/settings/configuracion.php" class="btn-outline-small w-100 text-center">Ajustes de Perfil</a>
                        </div>
                    </div>

                    <!-- PRÉSTAMOS ACTIVOS EN SIDEBAR -->
                    <div class="sidebar-card">
                        <h3 class="sidebar-title">Préstamos Activos</h3>
                        <ul class="panel-list">
                            <?php if (!empty($prestamosActivos)): ?>
                                <?php foreach ($prestamosActivos as $pa): ?>
                                    <li>
                                        <span class="material-symbols-outlined">menu_book</span>
                                        <div>
                                            <strong><?= htmlspecialchars($pa['titulo']) ?></strong>
                                            <small>Devolución: <?= date('d M', strtotime($pa['fecha_devolucion_prevista'])) ?></small>
                                        </div>
                                    </li>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <li><small>No tienes préstamos activos.</small></li>
                            <?php endif; ?>
                        </ul>
                    </div>

                    <!-- NUEVOS LIBROS -->
                    <div class="sidebar-card">
                        <h3 class="sidebar-title">Nuevos Libros / Recursos</h3>
                        <ul class="panel-list">
                            <?php if (!empty($nuevoLibro)): ?>
                                <li>
                                    <span class="material-symbols-outlined">new_releases</span>
                                    <div>
                                        <strong><?= htmlspecialchars($nuevoLibro['titulo']) ?></strong>
                                        <small>Disponible en Hemeroteca</small>
                                    </div>
                                </li>
                            <?php else: ?>
                                <li><small>No hay nuevos libros registrados.</small></li>
                            <?php endif; ?>
                        </ul>
                    </div>
                </aside>

            </div>
        </div>
    </main>

    <?php include __DIR__ . '/../../shared/layouts/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="../../shared/js/components/navbar.js"></script>
    <script src="/Appjoteca/shared/js/components/notifications.js"></script>
    <script src="/Appjoteca/shared/js/components/reading-summary.js" defer></script>
    <script src="../../shared/js/global.js"></script>
    <script src="docente.js"></script>
    <script src="/Appjoteca/shared/js/register-sw.js"></script>
</body>
</html>