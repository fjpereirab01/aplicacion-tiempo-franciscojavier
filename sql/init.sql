-- Seleccionamos la base de datos que vamos a usar
USE weatherapp;

-- ---- TABLA CIUDADES ----
-- Guarda las ciudades que se han buscado en la aplicación
CREATE TABLE IF NOT EXISTS ciudades (
    id INT AUTO_INCREMENT PRIMARY KEY,  -- Identificador único de la ciudad
    nombre VARCHAR(100) NOT NULL,       -- Nombre de la ciudad (ej: Badajoz)
    pais VARCHAR(10) NOT NULL,          -- Código del país (ej: ES)
    lat DECIMAL(9,6) NOT NULL,          -- Latitud obtenida de la API de geolocalización
    lon DECIMAL(9,6) NOT NULL,          -- Longitud obtenida de la API de geolocalización
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP -- Fecha y hora en que se buscó
);

-- ---- TABLA CONSULTAS ----
-- Guarda cada consulta meteorológica realizada por el usuario
CREATE TABLE IF NOT EXISTS consultas (
    id INT AUTO_INCREMENT PRIMARY KEY,  -- Identificador único de la consulta
    ciudad_id INT NOT NULL,             -- Ciudad a la que pertenece la consulta
    tipo ENUM('actual','horas','semana') NOT NULL, -- Tipo de consulta realizada
    respuesta_json LONGTEXT,            -- Respuesta completa de la API en formato JSON
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, -- Fecha y hora de la consulta
    FOREIGN KEY (ciudad_id) REFERENCES ciudades(id) -- Relación con la tabla ciudades
);