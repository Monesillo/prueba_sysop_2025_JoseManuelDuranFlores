CREATE DATABASE IF NOT EXISTS empleados_db;
USE empleados_db;

CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    correo VARCHAR(100) UNIQUE NOT NULL,
    contrasena VARCHAR(255) NOT NULL,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    tipo_usuario ENUM('admin', 'empleado') DEFAULT 'empleado'
);

CREATE TABLE empleados (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT,
    nombre VARCHAR(100) NOT NULL,
    telefono VARCHAR(15),
    correo VARCHAR(100) UNIQUE,
    fecha_nacimiento DATE,
    rfc VARCHAR(13) UNIQUE,
    estatus ENUM('activo', 'inactivo') DEFAULT 'activo',
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE SET NULL
);

CREATE TABLE historial_empleados (
    id INT AUTO_INCREMENT PRIMARY KEY,
    empleado_id INT,
    accion VARCHAR(50),
    descripcion TEXT,
    fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (empleado_id) REFERENCES empleados(id) ON DELETE CASCADE
);

CREATE TABLE roles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(50) UNIQUE NOT NULL
);

CREATE TABLE usuario_roles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT,
    rol_id INT,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (rol_id) REFERENCES roles(id) ON DELETE CASCADE
);

INSERT INTO roles (nombre) VALUES ('admin'), ('empleado');

INSERT INTO usuarios (nombre, correo, contrasena, tipo_usuario)
VALUES 
('Admin', 'admin@empresa.com', SHA2('admin123', 256), 'admin'),
('Empleado1', 'empleado1@empresa.com', SHA2('empleado123', 256), 'empleado');

INSERT INTO empleados (usuario_id, nombre, telefono, correo, fecha_nacimiento, rfc, estatus)
VALUES 
(2, 'Juan Pérez', '1234567890', 'juan.perez@empresa.com', '1990-05-15', 'JUAP900515XYZ', 'activo');

SELECT * FROM empleados WHERE estatus = 'activo';

SELECT * FROM historial_empleados;

SELECT u.nombre AS usuario, r.nombre AS rol 
FROM usuario_roles ur
JOIN usuarios u ON ur.usuario_id = u.id
JOIN roles r ON ur.rol_id = r.id;
