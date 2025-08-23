-- Crear base de datos
CREATE DATABASE IF NOT EXISTS utn_real_estate;
USE utn_real_estate;

-- Tabla de usuarios
CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    telefono VARCHAR(20),
    correo VARCHAR(100) UNIQUE NOT NULL,
    email VARCHAR(100),
    usuario VARCHAR(50) UNIQUE NOT NULL,
    contrasena VARCHAR(255) NOT NULL,
    privilegio ENUM('administrador', 'agente_ventas') DEFAULT 'agente_ventas',
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabla de configuración del sitio
CREATE TABLE configuracion_sitio (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tema_color ENUM('azul', 'amarillo_gris', 'blanco_gris') DEFAULT 'azul',
    icono_principal VARCHAR(255),
    icono_blanco VARCHAR(255),
    -- Adding new image fields for complete customization
    logo_principal VARCHAR(255),
    logo_blanco VARCHAR(255),
    imagen_banner VARCHAR(255),
    mensaje_banner TEXT,
    quienes_somos_texto TEXT,
    quienes_somos_imagen VARCHAR(255),
    imagen_quienes_somos VARCHAR(255),
    facebook_url VARCHAR(255),
    youtube_url VARCHAR(255),
    instagram_url VARCHAR(255),
    direccion TEXT,
    telefono_contacto VARCHAR(20),
    email_contacto VARCHAR(100)
);

-- Adding new table for image gallery management
CREATE TABLE imagenes_sitio (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tipo ENUM('galeria', 'banner', 'logo', 'propiedad', 'quienes_somos') DEFAULT 'galeria',
    nombre_archivo VARCHAR(255) NOT NULL,
    ruta VARCHAR(500) NOT NULL,
    descripcion TEXT,
    fecha_subida TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabla de propiedades
CREATE TABLE propiedades (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tipo ENUM('alquiler', 'venta') NOT NULL,
    destacada BOOLEAN DEFAULT FALSE,
    titulo VARCHAR(200) NOT NULL,
    descripcion_breve TEXT,
    precio DECIMAL(10,2) NOT NULL,
    agente_id INT,
    imagen_destacada VARCHAR(255),
    descripcion_larga TEXT,
    mapa TEXT,
    ubicacion VARCHAR(255),
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (agente_id) REFERENCES usuarios(id) ON DELETE SET NULL
);

-- Adding table for property images gallery
CREATE TABLE imagenes_propiedades (
    id INT AUTO_INCREMENT PRIMARY KEY,
    propiedad_id INT NOT NULL,
    ruta_imagen VARCHAR(500) NOT NULL,
    descripcion VARCHAR(255),
    orden INT DEFAULT 0,
    fecha_subida TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (propiedad_id) REFERENCES propiedades(id) ON DELETE CASCADE
);

-- Insertar usuario administrador por defecto
INSERT INTO usuarios (nombre, telefono, correo, email, usuario, contrasena, privilegio) 
VALUES ('Administrador', '0000-0000', 'admin@utn.com', 'admin@utn.com', 'Admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'administrador');

-- Insertar configuración por defecto
INSERT INTO configuracion_sitio (
    tema_color, 
    mensaje_banner, 
    quienes_somos_texto,
    direccion,
    telefono_contacto,
    email_contacto,
    logo_principal,
    logo_blanco,
    imagen_banner,
    imagen_quienes_somos
) VALUES (
    'azul',
    'PERMITENOS SAYUDARTE A CUMPLIR TUS SUEÑOS',
    'Curabitur congue efficiend orci, at mollis elit tristique nec. Phasellus vestibulum nibh nisl. Donec ac volutpat nisl. Class aptent taciti sociosqu ad litora torquent per conubia nostra, per inceptos himenaeos. Morbi pretium erat a molestie bibendum. Suspendisse dui lectus, viverra commodo vehicula et, finibus et quam. Donec vel pretium nunc. Aenean vehicula leo non nisl varius, a pharetra sapien varius. Nam eget elit lorem.',
    'Cañas Guanacaste, 100 mts Este',
    '8800-3030',
    'info@utnrealestate.com',
    'uploads/logo_default.png',
    'uploads/logo_blanco_default.png',
    'uploads/banner_default.jpg',
    'uploads/quienes_somos_default.jpg'
);

-- Insertar propiedades de ejemplo
INSERT INTO propiedades (tipo, destacada, titulo, descripcion_breve, precio, imagen_destacada, descripcion_larga, ubicacion) VALUES
('venta', TRUE, 'Casa Mora', 'Ubicada en las faldas del volcán Arenal, contiene 1000 mts de terreno y uno de las mejores vistas del lugar', 65000, 'uploads/casa1.jpg', 'Hermosa casa ubicada en las faldas del volcán Arenal con vista espectacular y amplio terreno.', 'Volcán Arenal'),
('venta', TRUE, 'Casa Mora', 'Ubicada en las faldas del volcán Arenal, contiene 1000 mts de terreno y uno de las mejores vistas del lugar', 65000, 'uploads/casa2.jpg', 'Hermosa casa ubicada en las faldas del volcán Arenal con vista espectacular y amplio terreno.', 'Volcán Arenal'),
('venta', TRUE, 'Casa Mora', 'Ubicada en las faldas del volcán Arenal, contiene 1000 mts de terreno y uno de las mejores vistas del lugar', 65000, 'uploads/casa3.jpg', 'Hermosa casa ubicada en las faldas del volcán Arenal con vista espectacular y amplio terreno.', 'Volcán Arenal'),
('venta', FALSE, 'Casa Mora', 'Ubicada en las faldas del volcán Arenal, contiene 1000 mts de terreno y uno de las mejores vistas del lugar', 65000, 'uploads/casa4.jpg', 'Hermosa casa ubicada en las faldas del volcán Arenal con vista espectacular y amplio terreno.', 'Volcán Arenal'),
('venta', FALSE, 'Casa Mora', 'Ubicada en las faldas del volcán Arenal, contiene 1000 mts de terreno y uno de las mejores vistas del lugar', 65000, 'uploads/casa5.jpg', 'Hermosa casa ubicada en las faldas del volcán Arenal con vista espectacular y amplio terreno.', 'Volcán Arenal'),
('venta', FALSE, 'Casa Mora', 'Ubicada en las faldas del volcán Arenal, contiene 1000 mts de terreno y uno de las mejores vistas del lugar', 65000, 'uploads/casa6.jpg', 'Hermosa casa ubicada en las faldas del volcán Arenal con vista espectacular y amplio terreno.', 'Volcán Arenal'),
('alquiler', FALSE, 'Casa Mora', 'Ubicada en las faldas del volcán Arenal, contiene 1000 mts de terreno y uno de las mejores vistas del lugar', 800, 'uploads/casa7.jpg', 'Hermosa casa ubicada en las faldas del volcán Arenal con vista espectacular y amplio terreno.', 'Volcán Arenal'),
('alquiler', FALSE, 'Casa Mora', 'Ubicada en las faldas del volcán Arenal, contiene 1000 mts de terreno y uno de las mejores vistas del lugar', 800, 'uploads/casa8.jpg', 'Hermosa casa ubicada en las faldas del volcán Arenal con vista espectacular y amplio terreno.', 'Volcán Arenal'),
('alquiler', FALSE, 'Casa Mora', 'Ubicada en las faldas del volcán Arenal, contiene 1000 mts de terreno y uno de las mejores vistas del lugar', 800, 'uploads/casa9.jpg', 'Hermosa casa ubicada en las faldas del volcán Arenal con vista espectacular y amplio terreno.', 'Volcán Arenal');

-- Adding sample images to gallery
INSERT INTO imagenes_sitio (tipo, nombre_archivo, ruta, descripcion) VALUES
('banner', 'banner_default.jpg', 'uploads/banner_default.jpg', 'Imagen principal del banner'),
('logo', 'logo_default.png', 'uploads/logo_default.png', 'Logo principal del sitio'),
('logo', 'logo_blanco_default.png', 'uploads/logo_blanco_default.png', 'Logo blanco del sitio'),
('quienes_somos', 'quienes_somos_default.jpg', 'uploads/quienes_somos_default.jpg', 'Imagen de la sección quienes somos');
