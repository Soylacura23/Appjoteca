<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}
if (empty($_SESSION['csrf_token'])) {
  $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sobre Nosotros - AppJoteca</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@300;400;500;600;700;800&family=Noto+Serif:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0" />

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>

    <link rel="stylesheet" href="../../auth/components/theme.css">

    <link rel="stylesheet" href="about-us.css">
    <!-- Topbar compartido -->
    <link rel="stylesheet" href="../../shared/css/components/topbar-landing.css">

    <link rel="stylesheet" href="../../shared/css/components/footer.css">
    <link rel="icon" type="image/png" href="../../shared/images/logo-appjoteca.png">
</head>
<body>

<header class="main-header" id="mainHeader">
    <div class="header-container">
        <a href="../../index.php" class="logo-link">
            <img src="../../shared/images/logo-appjoteca.png" alt="Appjoteca Logo" class="logo-img">
            <span class="logo-text">APPJOTECA</span>
        </a>
        <nav class="main-nav">
            <ul class="nav-list">
                <li><a href="../../index.php" class="nav-link">Inicio</a></li>
                <li><a href="#equipo" class="nav-link">Equipo</a></li>
                <li><a href="#mision-vision" class="nav-link">Misión y Visión</a></li>
                <li><a href="#faq" class="nav-link">FAQ</a></li>
                <li><a href="#contacto" class="nav-link">Contacto</a></li>
            </ul>
        </nav>
        <div class="header-actions">
            <a href="../../auth/login/login.php" class="btn btn-primary btn-header">
                <span class="material-symbols-outlined">login</span>
                Acceder
            </a>
        </div>
        <button class="mobile-menu-btn" aria-label="Menú">
            <span class="material-symbols-outlined">menu</span>
        </button>
    </div>
</header>

    <!-- fondo con VANTA.js -->
    <section class="hero-direct">
        <div id="vanta-bg" class="hero-bg-abstract"></div>
        <div class="hero-content" data-aos="fade-up" data-aos-duration="1000">
            <span class="hero-badge">✦ Sobre Nosotros ✦</span>
            <h1 class="hero-title">
                <span class="gradient-text">Construimos</span>
                <span class="title-outline">el futuro</span>
                <span class="gradient-text">de tu biblioteca</span>
            </h1>
            <p class="hero-subtitle">Conoce al equipo, nuestra misión y cómo transformamos la gestión bibliotecaria.</p>
            <div class="hero-actions">
                <a href="#equipo" class="btn btn-primary btn-glow">
                    <span class="material-symbols-outlined">groups</span>
                    Conocer al equipo
                </a>
                <a href="#contacto" class="btn btn-secondary">
                    <span class="material-symbols-outlined">chat</span>
                    Enviar comentario
                </a>
            </div>
        </div>
        <div class="scroll-indicator">
            <span class="material-symbols-outlined">expand_more</span>
        </div>
    </section>

    <!-- EQUIPO -->
    <section id="equipo" class="section-container team-section">
        <div class="section-header text-center" data-aos="fade-up">
            <span class="section-label">✦ Nuestro Equipo ✦</span>
            <h2 class="section-title">Las mentes detrás de <span class="gradient-text">AppJoteca</span></h2>
            <p class="section-copy limit-width">Cinco estudiantes, un objetivo: transformar la biblioteca.</p>
        </div>

        <div class="team-grid">
            <div class="team-card lead-card" data-aos="fade-up" data-aos-delay="0">
                <div class="team-photo">
                    <img src="../../shared/images/team/simon.jpg" alt="Simón Montoya Soto" loading="lazy">
                    <div class="team-glow"></div>
                    <span class="lead-badge">Líder</span>
                </div>
                <div class="team-info">
                    <h3>Simón Montoya Soto</h3>
                    <span class="team-role">Líder · Desarrollador</span>
                </div>
            </div>
            <div class="team-card" data-aos="fade-up" data-aos-delay="100">
                <div class="team-photo">
                    <img src="../../shared/images/team/kevin.jpg" alt="Kevin Estiven Espejo García" loading="lazy">
                    <div class="team-glow"></div>
                </div>
                <div class="team-info">
                    <h3>Kevin Estiven Espejo García</h3>
                    <span class="team-role">Desarrollador</span>
                </div>
            </div>
            <div class="team-card" data-aos="fade-up" data-aos-delay="200">
                <div class="team-photo">
                    <img src="../../shared/images/team/damian.jpg" alt="Damián Andrés Sabogal Morales" loading="lazy">
                    <div class="team-glow"></div>
                </div>
                <div class="team-info">
                    <h3>Damián Andrés Sabogal Morales</h3>
                    <span class="team-role">Desarrollador</span>
                </div>
            </div>
            <div class="team-card" data-aos="fade-up" data-aos-delay="300">
                <div class="team-photo">
                    <img src="../../shared/images/team/matias.jpg" alt="Matías Ruiz López" loading="lazy">
                    <div class="team-glow"></div>
                </div>
                <div class="team-info">
                    <h3>Matías Ruiz López</h3>
                    <span class="team-role">Desarrollador</span>
                </div>
            </div>
            <div class="team-card" data-aos="fade-up" data-aos-delay="400">
                <div class="team-photo">
                    <img src="../../shared/images/team/juan.jpg" alt="Juan Esteban Ríos Vargas" loading="lazy">
                    <div class="team-glow"></div>
                </div>
                <div class="team-info">
                    <h3>Juan Esteban Ríos Vargas</h3>
                    <span class="team-role">Desarrollador</span>
                </div>
            </div>
        </div>
        <p class="team-quote text-center" data-aos="fade-up">"Un proyecto, cinco perspectivas, un mismo objetivo."</p>
    </section>

    <!-- MARQUESINA ELEGANTE -->
    <div class="marquee-section">
        <div class="marquee">
            <div class="marquee-content">
                <span>Organización</span><span class="dot">✦</span>
                <span>Accesibilidad</span><span class="dot">✦</span>
                <span>Colaboración</span><span class="dot">✦</span>
                <span>Innovación</span><span class="dot">✦</span>
                <span>Conocimiento</span><span class="dot">✦</span>
                <span>Organización</span><span class="dot">✦</span>
                <span>Accesibilidad</span><span class="dot">✦</span>
                <span>Colaboración</span><span class="dot">✦</span>
                <span>Innovación</span><span class="dot">✦</span>
                <span>Conocimiento</span><span class="dot">✦</span>
            </div>
        </div>
    </div>

    <!-- MISIÓN Y VISIÓN -->
    <section id="mision-vision" class="section-container mv-section">
        <div class="mv-grid">
            <div class="mv-card mission" data-aos="fade-right" data-aos-duration="800">
                <div class="mv-icon-wrapper">
                    <span class="material-symbols-outlined mv-icon">flag</span>
                </div>
                <h2>Misión</h2>
                <p>Facilitar la gestión y consulta de los recursos bibliotecarios mediante una plataforma digital organizada, intuitiva y accesible.</p>
            </div>
            <div class="mv-card vision" data-aos="fade-left" data-aos-duration="800">
                <div class="mv-icon-wrapper">
                    <span class="material-symbols-outlined mv-icon">public</span>
                </div>
                <h2>Visión</h2>
                <p>Consolidar AppJoteca como una herramienta que fortalezca la gestión bibliotecaria y acerque la comunidad educativa a los recursos.</p>
            </div>
        </div>
    </section>

    <!-- QUÉ ES APPJOTECA -->
    <section class="section-container bg-surface">
        <div class="section-header text-center" data-aos="fade-up">
            <span class="section-label">✦ La Plataforma ✦</span>
            <h2 class="section-title">¿Qué es <span class="gradient-text">AppJoteca</span>?</h2>
            <p class="section-copy limit-width">Una plataforma web que centraliza la información, facilita el acceso al catálogo y simplifica la gestión de préstamos, reservas e inventario.</p>
        </div>
        <div class="grid-4">
            <div class="card feature-card" data-aos="zoom-in" data-aos-delay="0">
                <div class="card-icon-wrapper"><span class="material-symbols-outlined card-icon">auto_stories</span></div>
                <h3>Catálogo</h3>
                <p>Explora y consulta los recursos disponibles.</p>
            </div>
            <div class="card feature-card" data-aos="zoom-in" data-aos-delay="100">
                <div class="card-icon-wrapper"><span class="material-symbols-outlined card-icon">book_online</span></div>
                <h3>Reservas</h3>
                <p>Solicita y administra reservas fácilmente.</p>
            </div>
            <div class="card feature-card" data-aos="zoom-in" data-aos-delay="200">
                <div class="card-icon-wrapper"><span class="material-symbols-outlined card-icon">inventory_2</span></div>
                <h3>Inventario</h3>
                <p>Registro centralizado de materiales.</p>
            </div>
            <div class="card feature-card" data-aos="zoom-in" data-aos-delay="300">
                <div class="card-icon-wrapper"><span class="material-symbols-outlined card-icon">history</span></div>
                <h3>Historial</h3>
                <p>Consulta reservas, préstamos y devoluciones.</p>
            </div>
        </div>
    </section>

    <!-- PREGUNTAS FRECUENTES -->
    <section id="faq" class="section-container faq-section">
        <div class="section-header text-center" data-aos="fade-up">
            <span class="section-label">✦ Dudas ✦</span>
            <h2 class="section-title">Preguntas <span class="gradient-text">Frecuentes</span></h2>
        </div>
        <div class="faq-accordion" data-aos="fade-up">
            <details class="faq-item" open>
                <summary>¿Qué es AppJoteca?</summary>
                <div class="faq-content">Una plataforma web para la gestión de procesos bibliotecarios educativos.</div>
            </details>
            <details class="faq-item">
                <summary>¿Quiénes pueden usarla?</summary>
                <div class="faq-content">Estudiantes, docentes y bibliotecarios, según su rol.</div>
            </details>
            <details class="faq-item">
                <summary>¿Qué puedo hacer como estudiante o docente?</summary>
                <div class="faq-content">Consultar el catálogo, reservar recursos y revisar tu historial.</div>
            </details>
            <details class="faq-item">
                <summary>¿Qué gestiona un bibliotecario?</summary>
                <div class="faq-content">Inventario, usuarios, reservas, reportes y configuraciones.</div>
            </details>
            <details class="faq-item">
                <summary>¿Es una app móvil?</summary>
                <div class="faq-content">Es una plataforma web responsive, accesible desde cualquier dispositivo.</div>
            </details>
            <details class="faq-item">
                <summary>¿Puedo crear una cuenta como bibliotecario?</summary>
                <div class="faq-content">El acceso es administrado controladamente por el sistema.</div>
            </details>
        </div>
    </section>

    <!-- CONTACTO -->
    <section id="contacto" class="section-container bg-surface contact-section">
        <div class="contact-grid">
            <div class="contact-info" data-aos="fade-right">
                <span class="section-label">✦ Contacto ✦</span>
                <h2 class="section-title">Queremos <span class="gradient-text">escucharte</span></h2>
                <p class="section-copy">Tu opinión nos ayuda a mejorar. Envíanos tus sugerencias, reportes o felicitaciones.</p>
                <div class="contact-idea">
                    <div class="idea-icon-wrapper">
                        <span class="material-symbols-outlined">lightbulb</span>
                    </div>
                    <p>¿Tienes una idea? Cada comentario cuenta.</p>
                </div>
            </div>
            <div class="form-wrapper" data-aos="fade-left">
                <form id="feedbackForm" class="custom-form" novalidate>
                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>" class="hidden-input">
                    <input type="text" name="website_url_hp" class="honeypot" tabindex="-1" autocomplete="off" aria-hidden="true">

                    <div class="form-group">
                        <label for="nombre"><span class="material-symbols-outlined">person</span> Nombre</label>
                        <input type="text" id="nombre" name="nombre" placeholder="Tu nombre" required>
                    </div>
                    <div class="form-group">
                        <label for="correo"><span class="material-symbols-outlined">mail</span> Correo electrónico</label>
                        <input type="email" id="correo" name="correo" placeholder="tu@correo.com" required>
                    </div>
                    <div class="form-group">
                        <label for="tipo"><span class="material-symbols-outlined">category</span> Tipo de comentario</label>
                        <select id="tipo" name="tipo" required>
                            <option value="" disabled selected>Selecciona una opción</option>
                            <option value="sugerencia">Sugerencia</option>
                            <option value="problema">Reportar un problema</option>
                            <option value="felicitacion">Felicitación</option>
                            <option value="pregunta">Pregunta</option>
                            <option value="otro">Otro</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="mensaje"><span class="material-symbols-outlined">chat</span> Mensaje</label>
                        <textarea id="mensaje" name="mensaje" rows="4" placeholder="Cuéntanos..." maxlength="1000" required></textarea>
                        <small id="contador" class="char-counter">0 / 1000 caracteres</small>
                    </div>
                    <div class="form-group checkbox-group">
                        <input type="checkbox" id="privacidad" name="privacidad" required>
                        <label for="privacidad">Acepto la política de privacidad.</label>
                    </div>
                    <button type="submit" class="btn btn-primary w-100 btn-glow">
                        <span class="material-symbols-outlined">send</span>
                        Enviar comentario
                    </button>
                </form>
            </div>
        </div>
    </section>

    <?php include '../../shared/layouts/footer.php'; ?>
    
    
    <script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r134/three.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/vanta@latest/dist/vanta.net.min.js"></script>
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <?php include '../../shared/js/components/alert.js'; ?>

    
    <script src="about-us.js"></script>
</body>
</html>