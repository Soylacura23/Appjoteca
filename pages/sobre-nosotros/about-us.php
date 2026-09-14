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
    <!-- Fuentes -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@300;400;500;600;700&family=Noto+Serif:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0" />
    
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- Estilos -->
    <link rel="stylesheet" href="../../auth/components/theme.css">
    <link rel="stylesheet" href="about-us.css">
    <link rel="stylesheet" href="shared/css/components/footer.css">
    <link rel="icon" type="image/png"  href="../../shared/images/logo-appjoteca.png">
</head>
<body>

    <!-- CABECERA -->
    <header class="main-header" id="mainHeader">
    <div class="header-container">
        <a href="../../index.php" class="logo-link">
            <img src="../../shared/images/logo-appjoteca.png" alt="Appjoteca Logo" class="logo-img">
            <span class="logo-text">APPJOTECA</span>
        </a>

        <nav class="main-nav">
            <ul class="nav-list">
                <li><a href="#" class="nav-link">Sobre Nosotros</a></li>
                <li><a href="#" class="nav-link">Contacto</a></li>
            </ul>
        </nav>

        <div class="header-actions">
            <span class="header-divider"></span>
            <a href="auth/login/login.php" class="btn btn-primary btn-header">
                <span>Acceder</span>
            </a>
        </div>

        <!-- Menú móvil -->
        <button class="mobile-menu-btn" aria-label="Menú">
            <span class="material-symbols-outlined">menu</span>
        </button>
    </div>
</header>

    <!-- 1. HERO CINEMATOGRÁFICO -->
    <section class="hero-cinematic">
        <div class="hero-bg-abstract"></div>
        <div class="hero-content">
            <h1 class="hero-title">
                <span class="title-line">PRESERVANDO</span>
                <span class="title-accent">EL LEGADO DE LA</span>
                <span class="title-line">HUMANIDAD</span>
            </h1>
            <p class="hero-description">
                AppJoteca es una plataforma web creada para transformar la manera en que la comunidad educativa interactúa con la biblioteca. Centralizamos la información, facilitamos el acceso al catálogo y hacemos más sencilla la gestión de préstamos, reservas e inventario.
            </p>
            <div class="hero-actions">
                <a href="#que-es" class="btn btn-primary">Conocer AppJoteca</a>
                <a href="#equipo" class="btn btn-secondary">Explorar el proyecto</a>
            </div>
        </div>
    </section>

    <!-- 2. QUÉ ES APPJOTECA -->
    <section id="que-es" class="section-container">
        <div class="section-header">
            <h2 class="section-title">Una biblioteca más organizada, accesible y conectada.</h2>
            <p class="section-copy">AppJoteca es una plataforma de gestión bibliotecaria diseñada para facilitar la administración de los recursos de la biblioteca y mejorar la experiencia de sus usuarios. El sistema permite centralizar información sobre libros y otros materiales, consultar el catálogo, realizar reservas, gestionar usuarios y mantener un historial de las diferentes operaciones realizadas dentro de la biblioteca.</p>
        </div>
        <div class="grid-4">
            <div class="card">
                <span class="material-symbols-outlined card-icon">auto_stories</span>
                <h3>Catálogo</h3>
                <p>Explora y consulta los recursos disponibles en la biblioteca.</p>
            </div>
            <div class="card">
                <span class="material-symbols-outlined card-icon">book_online</span>
                <h3>Reservaciones</h3>
                <p>Solicita y administra reservas de manera organizada.</p>
            </div>
            <div class="card">
                <span class="material-symbols-outlined card-icon">inventory_2</span>
                <h3>Inventario</h3>
                <p>Mantén un registro centralizado de los materiales y ejemplares.</p>
            </div>
            <div class="card">
                <span class="material-symbols-outlined card-icon">history</span>
                <h3>Historial</h3>
                <p>Consulta las reservas, préstamos y devoluciones realizadas.</p>
            </div>
        </div>
    </section>

    <!-- 3. POR QUÉ NACIÓ -->
    <section class="section-container bg-surface">
        <div class="section-header text-center">
            <h2 class="section-title">De una necesidad a una solución.</h2>
            <p class="section-copy limit-width">La gestión de una biblioteca puede involucrar una gran cantidad de información y procesos. Cuando estos se realizan de manera manual, pueden aparecer dificultades para consultar datos, realizar seguimientos, organizar los recursos y mantener actualizada la información. AppJoteca nace como una propuesta para afrontar estas necesidades mediante una plataforma que centraliza los procesos principales de la biblioteca y facilita la interacción entre estudiantes, docentes y bibliotecarios.</p>
        </div>
        
        <div class="transformation-line">
            <div class="transform-side before">
                <span class="badge">Antes</span>
                <ul>
                    <li>Información dispersa</li>
                    <li class="arrow-down">↓</li>
                    <li>Procesos manuales</li>
                    <li class="arrow-down">↓</li>
                    <li>Mayor dificultad para realizar seguimientos</li>
                </ul>
            </div>
            <div class="transform-side after">
                <span class="badge badge-primary">Con AppJoteca</span>
                <ul>
                    <li>Información centralizada</li>
                    <li class="arrow-down">↓</li>
                    <li>Procesos organizados</li>
                    <li class="arrow-down">↓</li>
                    <li>Mayor facilidad de consulta y gestión</li>
                </ul>
            </div>
        </div>
    </section>

    <!-- 4 y 5. MISIÓN Y VISIÓN -->
    <section class="section-container">
        <div class="grid-2">
            <div class="card card-mission">
                <span class="material-symbols-outlined card-icon large">flag</span>
                <h2 class="section-title">Nuestra misión</h2>
                <p>Facilitar la gestión y consulta de los recursos bibliotecarios mediante una plataforma digital organizada, intuitiva y accesible, que contribuya a mejorar la experiencia de estudiantes, docentes y bibliotecarios dentro de la comunidad educativa.</p>
            </div>
            <div class="card card-vision">
                <span class="material-symbols-outlined card-icon large">public</span>
                <h2 class="section-title">Nuestra visión</h2>
                <p>Consolidar AppJoteca como una herramienta digital capaz de fortalecer la gestión bibliotecaria, facilitar el acceso a la información y promover una relación más cercana entre la comunidad educativa y los recursos disponibles en la biblioteca.</p>
            </div>
        </div>
    </section>

    <!-- 6. NUESTRO EQUIPO -->
    <section id="equipo" class="section-container bg-surface">
        <div class="section-header text-center">
            <h2 class="section-title">Un proyecto construido en equipo.</h2>
            <p class="section-copy limit-width">AppJoteca es el resultado del trabajo colaborativo de cinco estudiantes. Todos los integrantes participaron en el desarrollo del software y en la construcción de la documentación del proyecto, aportando ideas, conocimientos y trabajo durante sus diferentes etapas.<br><br>La coordinación general estuvo a cargo de Simón Montoya Soto, quien además de participar en el desarrollo y la documentación, asumió responsabilidades de liderazgo, organización y seguimiento del proyecto.</p>
        </div>

        <div class="team-grid">
            <!-- Simón destacado sutilmente -->
            <div class="card team-card lead-card">
                <h3>Simón Montoya Soto</h3>
                <span class="role">Líder del proyecto • Desarrollo de software</span>
                <p>Participación en el desarrollo del sistema y la documentación, junto con la coordinación, organización y seguimiento general del proyecto.</p>
            </div>
            <div class="card team-card">
                <h3>Kevin Estiven Espejo García</h3>
                <span class="role">Desarrollo de software</span>
                <p>Participación en el desarrollo del sistema y en la construcción de la documentación del proyecto.</p>
            </div>
            <div class="card team-card">
                <h3>Damián Andrés Sabogal Morales</h3>
                <span class="role">Desarrollo de software</span>
                <p>Participación en el desarrollo del sistema y en la construcción de la documentación del proyecto.</p>
            </div>
            <div class="card team-card">
                <h3>Matías Ruiz López</h3>
                <span class="role">Desarrollo de software</span>
                <p>Participación en el desarrollo del sistema y en la construcción de la documentación del proyecto.</p>
            </div>
            <div class="card team-card">
                <h3>Juan Esteban Ríos Vargas</h3>
                <span class="role">Desarrollo de software</span>
                <p>Participación en el desarrollo del sistema y en la construcción de la documentación del proyecto.</p>
            </div>
        </div>
        <h3 class="team-quote text-center">Un proyecto, cinco perspectivas, un mismo objetivo.</h3>
    </section>

    <!-- 7. CÓMO FUNCIONA -->
    <section class="section-container">
        <div class="section-header text-center">
            <h2 class="section-title">De encontrar un libro a gestionarlo.</h2>
        </div>
        <div class="steps-container">
            <div class="step">
                <span class="step-num">01</span>
                <h4>Explora</h4>
                <p>Accede al catálogo y descubre los materiales disponibles.</p>
            </div>
            <div class="step-arrow">→</div>
            <div class="step">
                <span class="step-num">02</span>
                <h4>Consulta</h4>
                <p>Revisa la información y disponibilidad de cada recurso.</p>
            </div>
            <div class="step-arrow">→</div>
            <div class="step">
                <span class="step-num">03</span>
                <h4>Reserva</h4>
                <p>Solicita una reserva de acuerdo con la disponibilidad.</p>
            </div>
            <div class="step-arrow">→</div>
            <div class="step">
                <span class="step-num">04</span>
                <h4>Gestiona</h4>
                <p>Los responsables administran reservas, inventario y usuarios.</p>
            </div>
            <div class="step-arrow">→</div>
            <div class="step">
                <span class="step-num">05</span>
                <h4>Historial</h4>
                <p>Mantén un registro de tus reservas, préstamos y devoluciones.</p>
            </div>
        </div>
    </section>

    <!-- 8. PARA QUIÉN ES -->
    <section class="section-container bg-surface">
        <div class="section-header text-center">
            <h2 class="section-title">Una plataforma para toda la comunidad.</h2>
        </div>
        <div class="grid-3">
            <div class="card visual-card">
                <span class="material-symbols-outlined card-icon large gold">school</span>
                <h3>Estudiantes</h3>
                <p>Pueden consultar el catálogo, conocer la disponibilidad de los materiales, realizar reservas y consultar su historial.</p>
            </div>
            <div class="card visual-card">
                <span class="material-symbols-outlined card-icon large gold">local_library</span>
                <h3>Docentes</h3>
                <p>Pueden acceder al catálogo, gestionar sus reservas y consultar el historial relacionado con el uso de los recursos bibliotecarios.</p>
            </div>
            <div class="card visual-card">
                <span class="material-symbols-outlined card-icon large gold">manage_accounts</span>
                <h3>Bibliotecarios</h3>
                <p>Cuentan con herramientas para administrar el inventario, los usuarios, las reservas y los reportes de la biblioteca.</p>
            </div>
        </div>
    </section>

    <!-- 9. LO QUE BUSCAMOS TRANSFORMAR -->
    <section class="section-container">
        <div class="section-header">
            <h2 class="section-title">Más que digitalizar procesos.</h2>
            <p class="section-copy">AppJoteca busca facilitar la relación entre las personas y los recursos de la biblioteca. La plataforma no se limita a almacenar información: busca convertir procesos que pueden resultar complejos en experiencias más claras, organizadas y fáciles de consultar.</p>
        </div>
        <div class="grid-4 concepts">
            <div class="concept-item">
                <h4>Organización</h4>
                <p>Información centralizada y estructurada.</p>
            </div>
            <div class="concept-item">
                <h4>Accesibilidad</h4>
                <p>Consulta más sencilla de los recursos disponibles.</p>
            </div>
            <div class="concept-item">
                <h4>Seguimiento</h4>
                <p>Mayor control sobre reservas, préstamos y devoluciones.</p>
            </div>
            <div class="concept-item">
                <h4>Experiencia</h4>
                <p>Una interacción más clara entre usuarios y la biblioteca.</p>
            </div>
        </div>
    </section>

    <!-- 10. NUESTRO ENFOQUE -->
    <section class="section-container text-center bg-surface">
        <h2 class="section-title">Trabajamos de forma colaborativa.</h2>
        <p class="section-copy limit-width">El desarrollo de AppJoteca se llevó a cabo mediante un enfoque de trabajo colaborativo, permitiendo organizar las tareas, realizar seguimiento al progreso y desarrollar el proyecto de manera progresiva.</p>
        
        <div class="approach-steps">
            <span>Planificar</span> <span class="arr">→</span> 
            <span>Desarrollar</span> <span class="arr">→</span> 
            <span>Revisar</span> <span class="arr">→</span> 
            <span>Mejorar</span>
        </div>
        <p class="approach-quote">Cada avance nos permitió identificar nuevas necesidades y mejorar progresivamente la propuesta.</p>
    </section>

    <!-- 11. NUESTRO IMPACTO -->
    <section class="section-container">
        <div class="section-header text-center">
            <h2 class="section-title">¿Qué queremos lograr?</h2>
            <p class="section-copy limit-width">Con AppJoteca buscamos contribuir a una gestión bibliotecaria más organizada y facilitar el acceso de la comunidad educativa a los recursos disponibles.</p>
        </div>
        <div class="grid-4">
            <div class="card">
                <h3>Menos procesos manuales</h3>
                <p>Facilitar la gestión de información que anteriormente podía requerir procesos manuales.</p>
            </div>
            <div class="card">
                <h3>Información centralizada</h3>
                <p>Reunir en un mismo sistema la información relacionada con usuarios, materiales y operaciones.</p>
            </div>
            <div class="card">
                <h3>Mejor seguimiento</h3>
                <p>Facilitar el control de reservas, préstamos, devoluciones e inventario.</p>
            </div>
            <div class="card">
                <h3>Mayor accesibilidad</h3>
                <p>Permitir que los usuarios consulten los recursos de la biblioteca de manera sencilla.</p>
            </div>
        </div>
    </section>

    <!-- 12. NUESTROS PRINCIPIOS -->
    <section class="section-container bg-surface">
        <div class="section-header text-center">
            <h2 class="section-title">Lo que representa AppJoteca.</h2>
        </div>
        <div class="principles-grid">
            <div class="card principle-card">
                <h4>Organización</h4>
                <p>Creemos que la información debe ser clara y fácil de encontrar.</p>
            </div>
            <div class="card principle-card">
                <h4>Accesibilidad</h4>
                <p>La tecnología debe facilitar el acceso a los recursos.</p>
            </div>
            <div class="card principle-card">
                <h4>Colaboración</h4>
                <p>El proyecto nace del trabajo y las ideas de todo el equipo.</p>
            </div>
            <div class="card principle-card">
                <h4>Innovación</h4>
                <p>Buscamos nuevas formas de mejorar procesos existentes.</p>
            </div>
            <div class="card principle-card">
                <h4>Conocimiento</h4>
                <p>La biblioteca representa un espacio para aprender, descubrir y preservar conocimiento.</p>
            </div>
        </div>
    </section>

    <!-- 13. PREGUNTAS FRECUENTES -->
    <section class="section-container limit-width">
        <h2 class="section-title text-center">Preguntas frecuentes</h2>
        <div class="faq-accordion">
            <details class="faq-item">
                <summary>¿Qué es AppJoteca?</summary>
                <div class="faq-content">Es una plataforma web orientada a la gestión de los procesos principales de una biblioteca educativa.</div>
            </details>
            <details class="faq-item">
                <summary>¿Quiénes pueden utilizar AppJoteca?</summary>
                <div class="faq-content">Estudiantes, docentes y bibliotecarios, de acuerdo con las funciones correspondientes a cada rol.</div>
            </details>
            <details class="faq-item">
                <summary>¿Qué puedo hacer como estudiante o docente?</summary>
                <div class="faq-content">Consultar el catálogo, revisar la información de los recursos, realizar reservas y consultar el historial.</div>
            </details>
            <details class="faq-item">
                <summary>¿Qué puede gestionar un bibliotecario?</summary>
                <div class="faq-content">Inventario, usuarios, reservas, reportes y diferentes configuraciones relacionadas con la gestión bibliotecaria.</div>
            </details>
            <details class="faq-item">
                <summary>¿AppJoteca es una aplicación móvil?</summary>
                <div class="faq-content">AppJoteca es una plataforma web diseñada para adaptarse a diferentes tamaños de pantalla, permitiendo utilizarla desde computadores, tabletas y dispositivos móviles.</div>
            </details>
            <details class="faq-item">
                <summary>¿Puedo crear una cuenta como bibliotecario?</summary>
                <div class="faq-content">El acceso correspondiente al rol de bibliotecario es administrado de manera controlada por el sistema.</div>
            </details>
        </div>
    </section>

    <!-- 14. FORMULARIO DE COMENTARIOS -->
    <section id="contacto" class="section-container bg-surface">
        <div class="grid-2 align-start">
            <div class="contact-info">
                <h2 class="section-title">Queremos escucharte.</h2>
                <p class="section-copy">Tu opinión nos ayuda a identificar oportunidades de mejora y a construir una experiencia cada vez más útil para la comunidad educativa.</p>
                
                <div class="card idea-card">
                    <span class="material-symbols-outlined card-icon gold">lightbulb</span>
                    <h3>¿Tienes una idea?</h3>
                    <p>Cada comentario puede convertirse en una oportunidad para mejorar AppJoteca.</p>
                </div>
            </div>
            
            <div class="form-wrapper">
                <form id="feedbackForm" class="custom-form">


                    <!-- Token oculto de seguridad -->
                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                    <!-- Honeypot oculto para bots -->
                    <input type="text" name="website_url_hp" class="honeypot" tabindex="-1" autocomplete="off">

                    <div class="form-group">
                        <label for="nombre">Nombre</label>
                        <input type="text" id="nombre" name="nombre" placeholder="Escribe tu nombre" required>
                    </div>

                    <div class="form-group">
                        <label for="correo">Correo electrónico</label>
                        <input type="email" id="correo" name="correo" placeholder="Escribe tu correo" required>
                    </div>

                    <div class="form-group">
                        <label for="tipo">Tipo de comentario</label>
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
                        <label for="mensaje">Mensaje</label>
                        <textarea id="mensaje" name="mensaje" rows="4" placeholder="Cuéntanos qué piensas..." maxlength="1000" required></textarea>
                        <small id="contador">0 / 1000 caracteres</small>
                    </div>

                    <div class="form-group checkbox-group">
                        <input type="checkbox" id="privacidad" name="privacidad" required>
                        <label for="privacidad">Acepto la política de privacidad y el tratamiento de datos.</label>
                    </div>

                    <button type="submit" class="btn btn-primary w-100">Enviar comentario</button>
                </form>
            </div>
        </div>
    </section>

    <?php include '../../shared/layouts/footer.php'; ?>
    <?php include '../../shared/js/components/alert.js'; ?>

    <script src="about.js"></script>
</body>
</html>