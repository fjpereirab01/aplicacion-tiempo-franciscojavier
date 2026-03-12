<?php
// ---- CONFIGURACIÓN DE LA API DE OPENWEATHERMAP ----

// Tu clave de acceso a la API de OpenWeatherMap
define('API_KEY', '75bb138e4de09340a8c42cdebfb9ada8');

// URL base de la API
define('API_BASE_URL', 'https://api.openweathermap.org');

// Idioma de los resultados (es = español)
define('API_LANG', 'es');

// Unidades de temperatura (metric = Celsius)
define('API_UNITS', 'metric');


// ---- CONFIGURACIÓN DE LA BASE DE DATOS ----

// Host de la base de datos
// getenv() lee la variable de entorno del docker-compose.yml
// Si no existe la variable de entorno, usa el valor por defecto
define('DB_HOST', getenv('DB_HOST') ?: 'db');

// Nombre de la base de datos
define('DB_NAME', getenv('DB_NAME') ?: 'weatherapp');

// Usuario de la base de datos
define('DB_USER', getenv('DB_USER') ?: 'weatheruser');

// Contraseña de la base de datos
define('DB_PASS', getenv('DB_PASS') ?: 'weatherpass');