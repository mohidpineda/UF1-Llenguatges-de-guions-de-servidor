CREATE DATABASE IF NOT EXISTS gestor_tareas;

CREATE USER 'mohid'@'localhost' IDENTIFIED BY '0000';

GRANT ALL PRIVILEGES ON gestor_tareas.* TO 'mohid'@'localhost';

FLUSH PRIVILEGES;

USE gestor_tareas;

CREATE TABLE IF NOT EXISTS tareas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(255) NOT NULL,
    descripcion TEXT,
    estado ENUM('pendiente', 'en progreso', 'por mejorar', 'completada') DEFAULT 'pendiente'
);