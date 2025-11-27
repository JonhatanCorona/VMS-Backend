CREATE DATABASE IF NOT EXISTS wms_db CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE wms_db;

-- Tabla de usuarios (t_usuarios)
CREATE TABLE IF NOT EXISTS t_usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(150) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    rol ENUM('operador','supervisor','admin') NOT NULL DEFAULT 'operador',
    almacen_id INT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Tabla tokens de sesión
CREATE TABLE IF NOT EXISTS t_tokens (
    id INT AUTO_INCREMENT PRIMARY KEY,
    token VARCHAR(128) NOT NULL UNIQUE,
    usuario_id INT NOT NULL,
    expires_at DATETIME NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES t_usuarios(id) ON DELETE CASCADE
);

-- Tablas para la PARTE 2 (te adelanto el esquema, te servirá luego)
CREATE TABLE IF NOT EXISTS t_paquete (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tracking VARCHAR(100) NOT NULL UNIQUE,
    descripcion TEXT,
    peso DECIMAL(10,3) DEFAULT 0,
    estado_actual VARCHAR(50),
    almacen_id INT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS t_tracking (
    id INT AUTO_INCREMENT PRIMARY KEY,
    paquete_id INT NOT NULL,
    usuario_id INT NULL,
    fecha DATETIME DEFAULT CURRENT_TIMESTAMP,
    tipo_movimiento VARCHAR(50),
    descripcion TEXT,
    FOREIGN KEY (paquete_id) REFERENCES t_paquete(id) ON DELETE CASCADE,
    FOREIGN KEY (usuario_id) REFERENCES t_usuarios(id) ON DELETE SET NULL
);