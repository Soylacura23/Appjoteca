CREATE TABLE IF NOT EXISTS metas (
    id INT NOT NULL AUTO_INCREMENT,
    id_usuario INT NOT NULL,
    titulo VARCHAR(120) NOT NULL,
    cantidad_objetivo INT NOT NULL,
    cantidad_actual INT NOT NULL DEFAULT 0,
    fecha_limite DATE NULL,
    completada TINYINT(1) NOT NULL DEFAULT 0,
    fecha_creacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_metas_usuario (id_usuario, completada),
    CONSTRAINT fk_metas_usuario FOREIGN KEY (id_usuario)
        REFERENCES usuarios (id_usuario) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS insignias (
    id INT NOT NULL AUTO_INCREMENT,
    codigo VARCHAR(50) NOT NULL,
    nombre VARCHAR(80) NOT NULL,
    descripcion VARCHAR(255) NOT NULL,
    icono VARCHAR(50) NOT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_insignias_codigo (codigo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS usuario_insignias (
    id INT NOT NULL AUTO_INCREMENT,
    id_usuario INT NOT NULL,
    id_insignia INT NOT NULL,
    fecha_obtencion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_usuario_insignia (id_usuario, id_insignia),
    CONSTRAINT fk_usuario_insignias_usuario FOREIGN KEY (id_usuario)
        REFERENCES usuarios (id_usuario) ON DELETE CASCADE,
    CONSTRAINT fk_usuario_insignias_insignia FOREIGN KEY (id_insignia)
        REFERENCES insignias (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS lista_deseos (
    id INT NOT NULL AUTO_INCREMENT,
    id_usuario INT NOT NULL,
    id_libro INT NOT NULL,
    fecha_agregado DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_lista_deseos_usuario_libro (id_usuario, id_libro),
    KEY idx_lista_deseos_libro (id_libro),
    CONSTRAINT fk_lista_deseos_usuario FOREIGN KEY (id_usuario)
        REFERENCES usuarios (id_usuario) ON DELETE CASCADE,
    CONSTRAINT fk_lista_deseos_libro FOREIGN KEY (id_libro)
        REFERENCES libros (id_libro) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS notas_docente (
    id INT NOT NULL AUTO_INCREMENT,
    id_usuario INT NOT NULL,
    titulo VARCHAR(120) NOT NULL,
    contenido TEXT NOT NULL,
    fecha_recordatorio DATE NULL,
    es_importante TINYINT(1) NOT NULL DEFAULT 0,
    recordatorio_enviado TINYINT(1) NOT NULL DEFAULT 0,
    fecha_creacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_notas_recordatorios (fecha_recordatorio, recordatorio_enviado),
    KEY idx_notas_usuario (id_usuario, es_importante, fecha_creacion),
    CONSTRAINT fk_notas_docente_usuario FOREIGN KEY (id_usuario)
        REFERENCES usuarios (id_usuario) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO insignias (codigo, nombre, descripcion, icono) VALUES
('primera_pagina', 'Primera Página', 'Realizaste tu primer préstamo.', 'auto_stories'),
('lector_novato', 'Lector Novato', 'Devolviste 3 libros.', 'menu_book'),
('lector_intermedio', 'Lector Intermedio', 'Devolviste 10 libros.', 'library_books'),
('lector_avanzado', 'Lector Avanzado', 'Devolviste 25 libros.', 'local_library'),
('lector_experto', 'Lector Experto', 'Devolviste 50 libros.', 'school'),
('lector_constante', 'Lector Constante', 'Leíste durante 3 meses consecutivos.', 'calendar_month'),
('explorador', 'Explorador', 'Leíste libros de 3 categorías.', 'explore'),
('polimata', 'Polímata', 'Leíste libros de 5 categorías.', 'psychology'),
('meta_cumplida', 'Meta Cumplida', 'Completaste una meta personal.', 'flag'),
('cumplidor_de_metas', 'Cumplidor de Metas', 'Completaste 3 metas personales.', 'military_tech'),
('devolutor_puntual', 'Devolutor Puntual', 'Devolviste 5 libros a tiempo.', 'schedule'),
('super_puntual', 'Súper Puntual', 'Devolviste 15 libros a tiempo.', 'verified'),
('veloz', 'Veloz', 'Devolviste 3 libros en un mismo mes.', 'bolt'),
('maratonista', 'Maratonista', 'Devolviste 5 libros en un mismo mes.', 'directions_run'),
('cientifico', 'Científico', 'Leíste 5 libros de Ciencias.', 'science'),
('literato', 'Literato', 'Leíste 5 libros de Literatura.', 'menu_book'),
('historiador', 'Historiador', 'Leíste 5 libros de Historia.', 'history_edu'),
('filosofo', 'Filósofo', 'Leíste 5 libros de Filosofía.', 'lightbulb')
ON DUPLICATE KEY UPDATE
    nombre = VALUES(nombre),
    descripcion = VALUES(descripcion),
    icono = VALUES(icono);
