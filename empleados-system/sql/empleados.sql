CREATE DATABASE IF NOT EXISTS empleados_db;
USE empleados_db;


-- Insertar en Empleados los Usuarios que no sean Admins
INSERT INTO empleados (usuario_id, nombre, telefono, correo, fecha_nacimiento, rfc, estatus)
SELECT u.id, u.nombre, '', u.correo, NULL, NULL, 'activo'
FROM usuarios u
LEFT JOIN empleados e ON u.id = e.usuario_id
WHERE e.usuario_id IS NULL AND u.tipo_usuario = 'empleado';

-- Trigger para insertar automáticamente empleados cuando se crea un usuario de tipo empleado
DELIMITER //
CREATE TRIGGER after_user_insert
AFTER INSERT ON usuarios
FOR EACH ROW
BEGIN
    IF NEW.tipo_usuario = 'empleado' THEN
        INSERT INTO empleados (usuario_id, nombre, correo, estatus)
        VALUES (NEW.id, NEW.nombre, NEW.correo, 'activo');
    END IF;
END;
//
DELIMITER ;

-- Consultas de verificación
SELECT * FROM empleados WHERE estatus = 'activo';
SELECT * FROM historial_empleados;
SELECT u.nombre AS usuario, r.nombre AS rol 
FROM usuario_roles ur
JOIN usuarios u ON ur.usuario_id = u.id
JOIN roles r ON ur.rol_id = r.id;
