- 1. ESTRUCTURA Y CREACIÓN DE LA BASE DE DATOS
DROP DATABASE IF EXISTS veterinaria;
CREATE DATABASE IF NOT EXISTS veterinaria;
USE veterinaria;

-- 1.1 Catálogo de Roles
CREATE TABLE IF NOT EXISTS rol (
    id_rol INT AUTO_INCREMENT PRIMARY KEY,
    nombre_rol VARCHAR(45) NOT NULL
);

-- 1.2 Tabla Central de Usuarios 
CREATE TABLE IF NOT EXISTS usuario (
    id_usuario INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    documento VARCHAR(20) NOT NULL UNIQUE,
    telefono VARCHAR(20) NOT NULL,
    correo VARCHAR(100) NOT NULL UNIQUE,
    direccion VARCHAR(100) NOT NULL,
    contrasena VARCHAR(255) NOT NULL,
    rol_id_rol INT NOT NULL,
    CONSTRAINT fk_usuario_rol FOREIGN KEY (rol_id_rol) REFERENCES rol(id_rol)
);

-- Perfiles Específicos por Rol
CREATE TABLE IF NOT EXISTS cliente (
    idCliente INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id_usuario INT NOT NULL UNIQUE,
    CONSTRAINT fk_cliente_usuario FOREIGN KEY (usuario_id_usuario) REFERENCES usuario(id_usuario)
);

CREATE TABLE IF NOT EXISTS veterinario (
    idveterinario INT AUTO_INCREMENT PRIMARY KEY,
    especialidad VARCHAR(45) NOT NULL,
    usuario_id_usuario INT NOT NULL UNIQUE,
    CONSTRAINT fk_veterinario_usuario FOREIGN KEY (usuario_id_usuario) REFERENCES usuario(id_usuario)
);

CREATE TABLE IF NOT EXISTS administrador (
    id_administrador INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id_usuario INT NOT NULL UNIQUE,
    CONSTRAINT fk_administrador_usuario FOREIGN KEY (usuario_id_usuario) REFERENCES usuario(id_usuario)
);

CREATE TABLE IF NOT EXISTS recepcionista (
    idRecepcionista INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id_usuario INT NOT NULL UNIQUE,
    CONSTRAINT fk_recepcionista_usuario FOREIGN KEY (usuario_id_usuario) REFERENCES usuario(id_usuario)
);

-- 1.4 Tablas Independientes para Especie y Raza
CREATE TABLE IF NOT EXISTS especie (
    idespecie INT AUTO_INCREMENT PRIMARY KEY,
    nombre_especie VARCHAR(45) NOT NULL -- Ej: Canino, Felino
);

CREATE TABLE IF NOT EXISTS raza (
    idraza INT AUTO_INCREMENT PRIMARY KEY,
    nombre_raza VARCHAR(45) NOT NULL, -- Ej: Labrador, Siamés, Poodle
    especie_idespecie INT NOT NULL,
    CONSTRAINT fk_raza_especie FOREIGN KEY (especie_idespecie) REFERENCES especie(idespecie)
);

-- 1.5 Tabla Mascota (Conectada a Raza en lugar de texto plano)
CREATE TABLE IF NOT EXISTS mascota (
    idmascota INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(45) NOT NULL,
    raza VARCHAR(45) NOT NULL,
    especie VARCHAR(45) NOT NULL,
    sexo VARCHAR(45) NOT NULL,
    peso DECIMAL(5,2) NOT NULL,
    observaciones VARCHAR(45) NOT NULL,
    datos_extra TEXT NULL,
    Cliente_idCliente INT NOT NULL,
    CONSTRAINT fk_mascota_cliente FOREIGN KEY (Cliente_idCliente) REFERENCES cliente(idCliente)
);

-- 1.6 Tabla Cita (Con tipo de servicio y veterinario opcional)
CREATE TABLE IF NOT EXISTS cita (
    idcita INT AUTO_INCREMENT PRIMARY KEY,
    fecha_hora DATETIME NOT NULL,
    tipo_servicio ENUM('medico', 'peluqueria') NOT NULL,
    estado VARCHAR(45) NOT NULL DEFAULT 'Solicitada',
    motivo VARCHAR(100) NULL,
    mascota_idmascota INT NOT NULL,
    veterinario_idveterinario INT NULL,
    CONSTRAINT fk_cita_mascota FOREIGN KEY (mascota_idmascota) REFERENCES mascota(idmascota),
    CONSTRAINT fk_cita_veterinario FOREIGN KEY (veterinario_idveterinario) REFERENCES veterinario(idveterinario)
);

-- 1.7 Historial Médico General
CREATE TABLE IF NOT EXISTS Historial_Medico (
    idHistorial_Medico INT AUTO_INCREMENT PRIMARY KEY,
    fecha_hora DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    receta_cuidados TEXT NOT NULL,
    vacunas_desparasitantes TEXT NULL,
    veterinario_idveterinario INT NOT NULL,
    cita_idcita INT NOT NULL,
    CONSTRAINT fk_historial_veterinario FOREIGN KEY (veterinario_idveterinario) REFERENCES veterinario(idveterinario),
    CONSTRAINT fk_historial_cita FOREIGN KEY (cita_idcita) REFERENCES cita(idcita)
);

-- 1.8 Disponibilidad Horaria (Gestionada por el Administrador)
CREATE TABLE IF NOT EXISTS disponibilidad_horarios (
    id_horario INT AUTO_INCREMENT PRIMARY KEY,
    fecha_hora DATETIME NOT NULL,
    disponible TINYINT(1) DEFAULT 1
);



-- 2. INSERCIÓN DE DATOS DE PRUEBA


-- 2.1 Roles del Sistema
INSERT INTO rol (id_rol, nombre_rol) VALUES 
(1, 'Administrador'), 
(2, 'Recepcionista'), 
(3, 'Veterinario'), 
(4, 'Cliente');

-- 2.2 Usuarios Centralizados
INSERT INTO usuario (id_usuario, nombre, documento, telefono, correo, direccion, contrasena, rol_id_rol) VALUES
(1, 'Carlos Rodríguez', '10010001', '3001000001', 'carlos.rodriguez@gmail.com', 'Calle 10 #12-15', '123456', 1),
(2, 'Laura Martínez', '10010002', '3001000002', 'laura.martinez@gmail.com', 'Carrera 15 #20-30', '123456', 1),
(3, 'Ana Gómez', '10010003', '3001000003', 'ana.gomez@gmail.com', 'Calle 25 #10-20', '123456', 2),
(4, 'Sofía Torres', '10010004', '3001000004', 'sofia.torres@gmail.com', 'Carrera 8 #15-25', '123456', 2),
(5, 'Juan Pérez', '10010005', '3001000005', 'juan.perez@gmail.com', 'Calle 30 #20-10', '123456', 3),
(6, 'Andrés López', '10010006', '3001000006', 'andres.lopez@gmail.com', 'Carrera 12 #18-40', '123456', 3),
(7, 'Camila Rojas', '10010007', '3001000007', 'calle 40 #15-12', 'Calle 40 #15-12', '123456', 3),
(8, 'Carlos Gómez', '20010001', '3102000001', 'carlos.gomez@gmail.com', 'Calle 10 #10-10', '123456', 4),
(9, 'María Rodríguez', '20010002', '3102000002', 'maria.rodriguez@gmail.com', 'Calle 11 #11-11', '123456', 4),
(10, 'Andrés Martínez', '20010003', '3102000003', 'andres.martinez@gmail.com', 'Calle 12 #12-12', '123456', 4);

-- 2.3 Perfiles Específicos
INSERT INTO administrador (usuario_id_usuario) VALUES (1), (2);
INSERT INTO recepcionista (usuario_id_usuario) VALUES (3), (4);
INSERT INTO veterinario (especialidad, usuario_id_usuario) VALUES
('Medicina general', 5),
('Cirugía veterinaria', 6),
('Peluquería', 7);
INSERT INTO cliente (usuario_id_usuario) VALUES (8), (9), (10);

-- 2.4 Especies y Razas (Tablas Independientes)
INSERT INTO especie (idespecie, nombre_especie) VALUES (1, 'Canino'), (2, 'Felino');
INSERT INTO raza (idraza, nombre_raza, especie_idespecie) VALUES 
(1, 'Criollo', 1), 
(2, 'Siamés', 2), 
(3, 'Persa', 2),
(4, 'Labrador', 1);

-- 2.5 Mascotas
INSERT INTO mascota (nombre, sexo, observaciones, raza_idraza, Cliente_idCliente) VALUES
('Michi', 'Hembra', 'Saludable', 2, 1),
('Luna', 'Hembra', 'Tranquila', 3, 2),
('Simba', 'Macho', 'Buen estado', 1, 3);

-- 2.6 Horarios Disponibles
INSERT INTO disponibilidad_horarios (fecha_hora, disponible) VALUES
('2026-10-10 08:00:00', 1),
('2026-10-10 09:00:00', 1),
('2026-10-10 10:00:00', 1),
('2026-10-10 11:00:00', 1),
('2026-10-11 14:00:00', 1);

-- 2.7 Citas de Prueba
INSERT INTO cita (fecha_hora, tipo_servicio, estado, motivo, mascota_idmascota, veterinario_idveterinario) VALUES
('2026-08-01 08:00:00', 'medico', 'Atendida', 'Control general', 1, 1),
('2026-08-01 09:00:00', 'medico', 'Atendida', 'Chequeo rutinario', 2, 2),
('2026-08-02 14:00:00', 'peluqueria', 'Completada', 'Baño y corte', 3, NULL);

-- 2.8 Historial Médico
INSERT INTO Historial_Medico (fecha_hora, receta_cuidados, vacunas_desparasitantes, veterinario_idveterinario, cita_idcita) VALUES
('2026-08-01 08:30:00', 'Amoxicilina 250mg cada 12h por 7 días.', 'Revisión general', 1, 1),
('2026-08-01 09:30:00', 'Limpieza ótica diaria con solución antiséptica.', 'Ninguna', 2, 2);



-- 3. TRIGGERS (DISPADORES) DE INTEGRIDAD

DELIMITER //

-- Evitar citas duplicadas en el mismo horario
DROP TRIGGER IF EXISTS trg_prevenir_cita_duplicada //
CREATE TRIGGER trg_prevenir_cita_duplicada
BEFORE INSERT ON cita
FOR EACH ROW
BEGIN
    DECLARE v_conteo INT;
    SELECT COUNT(*) INTO v_conteo
    FROM cita
    WHERE fecha_hora = NEW.fecha_hora 
      AND estado IN ('Solicitada', 'Atendida');

    IF v_conteo > 0 THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Error: Ya existe una cita registrada en ese horario.';
    END IF;
END //

-- Liberar horario automáticamente al cancelar cita
DROP TRIGGER IF EXISTS trg_liberar_horario_cancelacion //
CREATE TRIGGER trg_liberar_horario_cancelacion
AFTER UPDATE ON cita
FOR EACH ROW
BEGIN
    IF NEW.estado = 'Cancelada' AND OLD.estado != 'Cancelada' THEN
        UPDATE disponibilidad_horarios
        SET disponible = 1
        WHERE fecha_hora = NEW.fecha_hora;
    END IF;
END //

-- Marcar horario como no disponible al insertar cita
DROP TRIGGER IF EXISTS trg_actualizar_disponibilidad_al_insertar //
CREATE TRIGGER trg_actualizar_disponibilidad_al_insertar
AFTER INSERT ON cita
FOR EACH ROW
BEGIN
    UPDATE disponibilidad_horarios
    SET disponible = 0
    WHERE fecha_hora = NEW.fecha_hora;
END //

DELIMITER ;



-- 4. PROCEDIMIENTOS ALMACENADOS (STORED PROCEDURES)

DELIMITER //

-- Solicitar Cita (Portal del Cliente)
DROP PROCEDURE IF EXISTS sp_solicitar_cita //
CREATE PROCEDURE sp_solicitar_cita(
    IN p_id_mascota INT,
    IN p_tipo_servicio ENUM('medico', 'peluqueria'),
    IN p_fecha_hora DATETIME
)
BEGIN
    SET SQL_SAFE_UPDATES = 0;
    INSERT INTO cita (fecha_hora, tipo_servicio, estado, motivo, mascota_idmascota, veterinario_idveterinario)
    VALUES (p_fecha_hora, p_tipo_servicio, 'Solicitada', CONCAT('Servicio de ', p_tipo_servicio), p_id_mascota, NULL);
    
    UPDATE disponibilidad_horarios SET disponible = 0 WHERE fecha_hora = p_fecha_hora;
    SET SQL_SAFE_UPDATES = 1;
END //

-- Registrar Atención Médica y Cierre de Cita (Módulo Veterinario)
DROP PROCEDURE IF EXISTS sp_registrar_atencion_medica //
CREATE PROCEDURE sp_registrar_atencion_medica(
    IN p_id_cita INT,
    IN p_id_veterinario INT,
    IN p_receta_cuidados TEXT,
    IN p_vacunas_desparasitantes TEXT
)
BEGIN
    SET SQL_SAFE_UPDATES = 0;
    UPDATE cita 
    SET estado = 'Atendida', veterinario_idveterinario = p_id_veterinario 
    WHERE idcita = p_id_cita;

    INSERT INTO Historial_Medico (fecha_hora, receta_cuidados, vacunas_desparasitantes, veterinario_idveterinario, cita_idcita) 
    VALUES (NOW(), p_receta_cuidados, p_vacunas_desparasitantes, p_id_veterinario, p_id_cita);
    SET SQL_SAFE_UPDATES = 1;
END //

-- Cancelar Cita
DROP PROCEDURE IF EXISTS sp_cancelar_cita //
CREATE PROCEDURE sp_cancelar_cita(IN p_id_cita INT)
BEGIN
    DECLARE v_fecha_hora DATETIME;
    SET SQL_SAFE_UPDATES = 0;
    SELECT fecha_hora INTO v_fecha_hora FROM cita WHERE idcita = p_id_cita;
    UPDATE cita SET estado = 'Cancelada' WHERE idcita = p_id_cita;
    UPDATE disponibilidad_horarios SET disponible = 1 WHERE fecha_hora = v_fecha_hora;
    SET SQL_SAFE_UPDATES = 1;
END //

-- Agregar Horario Disponible (Módulo Administrador)
DROP PROCEDURE IF EXISTS sp_agregar_horario_disponible //
CREATE PROCEDURE sp_agregar_horario_disponible(IN p_fecha_hora DATETIME)
BEGIN
    INSERT INTO disponibilidad_horarios (fecha_hora, disponible) VALUES (p_fecha_hora, 1);
END //

DELIMITER ;



-- 5. FUNCIONES ALMACENADAS

DELIMITER //

DROP FUNCTION IF EXISTS fn_cantidad_mascotas_cliente //
CREATE FUNCTION fn_cantidad_mascotas_cliente(p_id_cliente INT) 
RETURNS INT
DETERMINISTIC
READS SQL DATA
BEGIN
    DECLARE v_cantidad INT DEFAULT 0;
    SELECT COUNT(*) INTO v_cantidad FROM mascota WHERE Cliente_idCliente = p_id_cliente;
    RETURN v_cantidad;
END //

DELIMITER ;



-- 6. CONSULTAS CON INNER JOIN


-- Agenda de Citas Completa 
SELECT 
    c.idcita,
    c.fecha_hora,
    c.tipo_servicio,
    c.estado,
    m.nombre AS nombre_mascota,
    esp.nombre_especie,
    raz.nombre_raza,
    u_cli.nombre AS nombre_cliente,
    u_cli.telefono AS telefono_cliente,
    IFNULL(u_vet.nombre, 'Sin asignar') AS nombre_veterinario
FROM cita c
JOIN mascota m ON c.mascota_idmascota = m.idmascota
JOIN raza raz ON m.raza_idraza = raz.idraza
JOIN especie esp ON raz.especie_idespecie = esp.idespecie
JOIN cliente cli ON m.Cliente_idCliente = cli.idCliente
JOIN usuario u_cli ON cli.usuario_id_usuario = u_cli.id_usuario
LEFT JOIN veterinario v ON c.veterinario_idveterinario = v.idveterinario
LEFT JOIN usuario u_vet ON v.usuario_id_usuario = u_vet.id_usuario
ORDER BY c.fecha_hora DESC;

-- Historial Médico Detallado por Mascota
SELECT 
    h.idHistorial_Medico,
    h.fecha_hora AS fecha_atencion,
    m.nombre AS nombre_mascota,
    esp.nombre_especie,
    raz.nombre_raza,
    u_vet.nombre AS medico_tratante,
    v.especialidad,
    h.receta_cuidados,
    h.vacunas_desparasitantes
FROM Historial_Medico h
JOIN cita c ON h.cita_idcita = c.idcita
JOIN mascota m ON c.mascota_idmascota = m.idmascota
JOIN raza raz ON m.raza_idraza = raz.idraza
JOIN especie esp ON raz.especie_idespecie = esp.idespecie
JOIN veterinario v ON h.veterinario_idveterinario = v.idveterinario
JOIN usuario u_vet ON v.usuario_id_usuario = u_vet.id_usuario
WHERE m.idmascota = 1
ORDER BY h.fecha_hora DESC;

ALTER TABLE mascota ADD COLUMN datos_extra TEXT NULL;

ALTER TABLE mascota ADD COLUMN peso DECIMAL(5,2) NULL;