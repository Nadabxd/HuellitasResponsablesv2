-- ============================================
-- Base de Datos: HuellasResponsables
-- Sistema de Gestión de Adopciones Responsables
-- ============================================

CREATE DATABASE IF NOT EXISTS huellas_responsables CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE huellas_responsables;

-- ============================================
-- Tabla de Usuarios
-- ============================================
CREATE TABLE usuarios (
    id_usuario INT PRIMARY KEY AUTO_INCREMENT,
    nombre VARCHAR(100) NOT NULL,
    correo VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    rol ENUM('administrador', 'refugio', 'cliente') NOT NULL,
    telefono VARCHAR(20),
    direccion VARCHAR(255),
    estado ENUM('activo', 'inactivo') DEFAULT 'activo',
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_correo (correo),
    INDEX idx_rol (rol)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Tabla de Mascotas
-- ============================================
CREATE TABLE mascotas (
    id_mascota INT PRIMARY KEY AUTO_INCREMENT,
    nombre VARCHAR(50) NOT NULL,
    especie VARCHAR(30) NOT NULL,
    raza VARCHAR(50),
    edad INT,
    sexo ENUM('macho', 'hembra'),
    tamanio ENUM('pequeño', 'mediano', 'grande'),
    color VARCHAR(50),
    estado ENUM('disponible', 'adoptado', 'en riesgo') DEFAULT 'disponible',
    descripcion TEXT,
    foto VARCHAR(255),
    vacunado BOOLEAN DEFAULT FALSE,
    esterilizado BOOLEAN DEFAULT FALSE,
    id_refugio INT,
    id_adoptante INT,
    fecha_ingreso DATE,
    fecha_adopcion DATE,
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_refugio) REFERENCES usuarios(id_usuario) ON DELETE SET NULL,
    FOREIGN KEY (id_adoptante) REFERENCES usuarios(id_usuario) ON DELETE SET NULL,
    INDEX idx_estado (estado),
    INDEX idx_especie (especie)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Tabla de Seguimiento
-- ============================================
CREATE TABLE seguimientos (
    id_seguimiento INT PRIMARY KEY AUTO_INCREMENT,
    id_mascota INT NOT NULL,
    id_cliente INT NOT NULL,
    fecha_reporte DATE NOT NULL,
    foto_evidencia VARCHAR(255),
    observaciones TEXT,
    calificacion_bienestar INT CHECK (calificacion_bienestar BETWEEN 1 AND 5),
    estado_validacion ENUM('pendiente', 'aprobado', 'rechazado') DEFAULT 'pendiente',
    comentarios_refugio TEXT,
    fecha_validacion TIMESTAMP NULL,
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_mascota) REFERENCES mascotas(id_mascota) ON DELETE CASCADE,
    FOREIGN KEY (id_cliente) REFERENCES usuarios(id_usuario) ON DELETE CASCADE,
    INDEX idx_mascota (id_mascota),
    INDEX idx_cliente (id_cliente),
    INDEX idx_fecha (fecha_reporte)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Tabla de Intervenciones / Visitas Domiciliarias
-- ============================================
CREATE TABLE intervenciones (
    id_intervencion INT PRIMARY KEY AUTO_INCREMENT,
    id_mascota INT NOT NULL,
    id_adoptante INT NOT NULL,
    tipo ENUM('visita_domiciliaria', 'llamada', 'advertencia', 'rescate') NOT NULL,
    fecha_intervencion DATE NOT NULL,
    motivo TEXT,
    resultado TEXT,
    responsable VARCHAR(100),
    estado ENUM('programada', 'realizada', 'cancelada') DEFAULT 'programada',
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_mascota) REFERENCES mascotas(id_mascota) ON DELETE CASCADE,
    FOREIGN KEY (id_adoptante) REFERENCES usuarios(id_usuario) ON DELETE CASCADE,
    INDEX idx_tipo (tipo),
    INDEX idx_fecha (fecha_intervencion)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Tabla de Calificación de Adoptantes
-- ============================================
CREATE TABLE calificaciones_adoptantes (
    id_calificacion INT PRIMARY KEY AUTO_INCREMENT,
    id_adoptante INT NOT NULL,
    puntuacion_confianza DECIMAL(3,2) DEFAULT 5.00 CHECK (puntuacion_confianza BETWEEN 0 AND 5),
    total_adopciones INT DEFAULT 0,
    adopciones_exitosas INT DEFAULT 0,
    adopciones_fallidas INT DEFAULT 0,
    reportes_a_tiempo INT DEFAULT 0,
    reportes_tardios INT DEFAULT 0,
    ultima_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (id_adoptante) REFERENCES usuarios(id_usuario) ON DELETE CASCADE,
    UNIQUE KEY idx_adoptante (id_adoptante)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Insertar Usuario Administrador por Defecto
-- ============================================
-- Contraseña: admin123 (hasheada con password_hash)
INSERT INTO usuarios (nombre, correo, password, rol) VALUES
('Administrador', 'admin@huellas.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'administrador');

-- ============================================
-- Datos de Ejemplo (Opcional)
-- ============================================

-- Usuario Refugio
INSERT INTO usuarios (nombre, correo, password, rol, telefono) VALUES
('Refugio Patitas Felices', 'refugio@huellas.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'refugio', '555-0001');

-- Usuario Cliente
INSERT INTO usuarios (nombre, correo, password, rol, telefono, direccion) VALUES
('Juan Pérez', 'juan@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'cliente', '555-0002', 'Calle Principal 123');

-- Mascotas de Ejemplo
INSERT INTO mascotas (nombre, especie, raza, edad, sexo, tamanio, color, estado, descripcion, vacunado, esterilizado, id_refugio, fecha_ingreso) VALUES
('Max', 'Perro', 'Labrador', 2, 'macho', 'grande', 'Dorado', 'disponible', 'Perro amigable y juguetón', TRUE, TRUE, 2, '2024-01-15'),
('Luna', 'Gato', 'Siamés', 1, 'hembra', 'pequeño', 'Blanco y gris', 'disponible', 'Gata tranquila y cariñosa', TRUE, TRUE, 2, '2024-02-20'),
('Rocky', 'Perro', 'Pastor Alemán', 3, 'macho', 'grande', 'Negro y marrón', 'adoptado', 'Perro guardián leal', TRUE, TRUE, 2, '2023-11-10');

-- Actualizar adoptante de Rocky
UPDATE mascotas SET id_adoptante = 3, fecha_adopcion = '2024-03-15' WHERE id_mascota = 3;

-- Inicializar calificación del adoptante
INSERT INTO calificaciones_adoptantes (id_adoptante, total_adopciones, adopciones_exitosas) VALUES
(3, 1, 1);

-- Seguimiento de ejemplo
INSERT INTO seguimientos (id_mascota, id_cliente, fecha_reporte, observaciones, calificacion_bienestar, estado_validacion) VALUES
(3, 3, '2024-04-15', 'Rocky está muy bien adaptado, come bien y juega en el jardín.', 5, 'aprobado');
