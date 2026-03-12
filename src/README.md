# Aplicacion del Tiempo - Francisco Javier

Aplicacion web desarrollada en PHP que permite consultar el tiempo atmosferico de cualquier ciudad del mundo utilizando la API de OpenWeatherMap. Desplegada en AWS con Docker y accesible desde internet mediante HTTPS.

**URL de acceso:** https://fjpereirab.duckdns.org  
**Repositorio:** https://github.com/fjpereirab01/aplicacion-tiempo-franciscojavier

---

## Introduccion

Esta aplicacion ha sido desarrollada como practica de la asignatura IAPLW del ciclo ASIR2. El objetivo es crear una aplicacion web en PHP que consulte datos meteorologicos a traves de una API externa, los almacene en una base de datos MariaDB y los muestre al usuario de forma clara.

La aplicacion sigue el patron de diseno **MVC (Modelo Vista Controlador)**, separando la logica de negocio, el acceso a datos y la presentacion en capas independientes. El despliegue se realiza mediante **Docker** en una instancia **EC2 de AWS**, con **Nginx** como proxy inverso y certificado **SSL gratuito** obtenido con Certbot y DuckDNS.

---

## Estructura del proyecto
```
aplicacion_tiempo/
├── Dockerfile                  # Imagen Docker de PHP + Apache
├── docker-compose.yml          # Orquestacion de contenedores
├── apache/
│   └── 000-default.conf        # Configuracion de Apache
├── nginx/
│   └── nginx.conf              # Configuracion de Nginx (proxy inverso + HTTPS)
├── sql/
│   └── init.sql                # Script de creacion de la base de datos
├── imagenes/                   # Capturas de pantalla de la aplicacion
└── src/
    ├── index.php               # Punto de entrada de la aplicacion
    ├── configuracion/
    │   └── config.php          # API key y configuracion de la base de datos
    ├── modelos/
    │   └── Modelo.php          # Acceso a la base de datos (ciudades y consultas)
    ├── controladores/
    │   └── TiempoControlador.php # Logica de negocio y llamadas a la API
    └── vistas/
        ├── paginas.php         # Enrutador de vistas
        └── vistas.php          # Todas las paginas HTML de la aplicacion
```

---

## Explicacion de archivos

### Dockerfile
Define la imagen Docker que se usa para el contenedor PHP + Apache.
```dockerfile
FROM php:8.2-apache
# Imagen base oficial de PHP 8.2 con Apache incluido

RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    # Instalamos las dependencias necesarias para trabajar con imagenes
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd pdo pdo_mysql mysqli
    # Instalamos extensiones PHP:
    # - gd: para generar graficas
    # - pdo, pdo_mysql: para conectar con MariaDB usando PDO
    # - mysqli: conexion alternativa con MariaDB

RUN a2enmod rewrite
# Activamos el modulo rewrite de Apache para permitir redirecciones

COPY apache/000-default.conf /etc/apache2/sites-enabled/000-default.conf
# Copiamos nuestra configuracion personalizada de Apache

EXPOSE 80
# El contenedor escucha en el puerto 80
```

---

### docker-compose.yml
Orquesta los cuatro contenedores de la aplicacion.
```yaml
services:
  php:
    build: .
    # Construye la imagen usando el Dockerfile
    container_name: aplicacion_tiempo
    volumes:
      - ./src:/var/www/html
      # Monta la carpeta src como raiz del servidor web
    depends_on:
      - db
      # Espera a que la base de datos arranque primero
    environment:
      DB_HOST: db
      DB_NAME: weatherapp
      DB_USER: weatheruser
      DB_PASS: weatherpass
      # Variables de entorno para conectar con la base de datos

  db:
    image: mariadb:10.11
    # Imagen oficial de MariaDB version 10.11
    container_name: base_datos_tiempo
    restart: always
    # Se reinicia automaticamente si falla
    volumes:
      - ./sql/init.sql:/docker-entrypoint-initdb.d/init.sql
      # Script SQL que se ejecuta al crear la base de datos
      - db_data:/var/lib/mysql
      # Volumen para que los datos persistan

  nginx:
    image: nginx:alpine
    container_name: nginx_tiempo
    ports:
      - "80:80"
      - "443:443"
      # Expone los puertos HTTP y HTTPS
    volumes:
      - ./nginx/nginx.conf:/etc/nginx/conf.d/default.conf
      - ./certbot/conf:/etc/letsencrypt
      # Certificados SSL generados por Certbot
      - ./certbot/www:/var/www/certbot
      # Directorio de verificacion de Certbot

  certbot:
    image: certbot/certbot
    # Gestiona el certificado SSL gratuito de Let's Encrypt
    volumes:
      - ./certbot/conf:/etc/letsencrypt
      - ./certbot/www:/var/www/certbot
```

---

### sql/init.sql
Script SQL que crea las tablas de la base de datos automaticamente al arrancar el contenedor.
```sql
USE weatherapp;

-- Tabla ciudades: guarda las ciudades buscadas
CREATE TABLE IF NOT EXISTS ciudades (
    id INT AUTO_INCREMENT PRIMARY KEY,  -- Identificador unico
    nombre VARCHAR(100) NOT NULL,       -- Nombre de la ciudad
    pais VARCHAR(10) NOT NULL,          -- Codigo del pais (ej: ES)
    lat DECIMAL(9,6) NOT NULL,          -- Latitud
    lon DECIMAL(9,6) NOT NULL,          -- Longitud
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP -- Fecha de busqueda
);

-- Tabla consultas: guarda cada consulta meteorologica
CREATE TABLE IF NOT EXISTS consultas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ciudad_id INT NOT NULL,             -- Ciudad consultada
    tipo ENUM('actual','horas','semana') NOT NULL, -- Tipo de consulta
    respuesta_json LONGTEXT,            -- Respuesta completa de la API
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (ciudad_id) REFERENCES ciudades(id)
);
```

---

### src/configuracion/config.php
Contiene la API key de OpenWeatherMap y la configuracion de conexion a la base de datos.
```php
define('API_KEY', 'TU_API_KEY');
// Clave de acceso a la API de OpenWeatherMap

define('API_BASE_URL', 'https://api.openweathermap.org');
// URL base de la API

define('API_LANG', 'es');
// Idioma de los resultados en español

define('API_UNITS', 'metric');
// Unidades de temperatura en Celsius

define('DB_HOST', getenv('DB_HOST') ?: 'db');
// Host de la base de datos, lee la variable de entorno del docker-compose

define('DB_NAME', getenv('DB_NAME') ?: 'weatherapp');
define('DB_USER', getenv('DB_USER') ?: 'weatheruser');
define('DB_PASS', getenv('DB_PASS') ?: 'weatherpass');
```

---

### src/modelos/Modelo.php
Clase DAO que gestiona todas las operaciones con la base de datos.
```php
class Modelo {
    private $conexion;
    // Conexion PDO con la base de datos

    public function __construct() {
        $this->conexion = new PDO(...);
        // Establece la conexion al instanciar la clase
    }

    public function guardarCiudad($ciudad) {
        // Comprueba si la ciudad ya existe en la BD
        // Si no existe la inserta y devuelve el nuevo id
        // Si existe devuelve el id existente
    }

    public function guardarConsulta($ciudadId, $tipo, $respuestaJson) {
        // Inserta una nueva consulta meteorologica en la BD
        // Guarda el JSON completo de la respuesta de la API
    }

    public function obtenerTodasConsultas() {
        // Devuelve todas las consultas ordenadas por fecha descendente
        // Hace un JOIN con la tabla ciudades para obtener el nombre
    }
}
```

---

### src/controladores/TiempoControlador.php
Controlador principal que gestiona la logica de negocio y las llamadas a la API.
```php
class TiempoControlador {
    private $modelo;
    // Instancia del modelo para acceder a la base de datos

    public function buscarCiudades($nombreCiudad) {
        // Llama a la API de geocodificacion de OpenWeatherMap
        // Devuelve hasta 5 ciudades con ese nombre
        // Traduce los codigos de pais al español
    }

    public function guardarCiudad($ciudad) {
        // Delega en el modelo para guardar la ciudad en la BD
    }

    public function obtenerTiempoActual($ciudadId, $lat, $lon) {
        // Llama al endpoint /data/2.5/weather de la API
        // Guarda la consulta en la BD
        // Devuelve los datos formateados para la vista
    }

    public function obtenerPrevisionHoras($ciudadId, $lat, $lon) {
        // Llama al endpoint /data/2.5/forecast con cnt=8
        // Devuelve previsiones cada 3 horas para el dia actual
    }

    public function obtenerPrevisionSemanal($ciudadId, $lat, $lon) {
        // Llama al endpoint /data/2.5/forecast
        // Agrupa los datos por dia calculando maximas y minimas
        // Traduce los dias de la semana al español
    }

    public function obtenerHistorial() {
        // Delega en el modelo para obtener todas las consultas
    }

    private function llamarApi($url) {
        // Metodo privado que hace las peticiones HTTP a la API
        // Usa file_get_contents con un timeout de 10 segundos
    }
}
```

---

### src/vistas/paginas.php
Enrutador de vistas. Recibe el parametro `vista` de la URL y llama a la funcion correspondiente.
```php
$vista = $_GET['vista'] ?? 'actual';
// Lee el parametro vista de la URL

switch ($vista) {
    case 'actual':  vistaActual($ciudad, $datos);  break;
    case 'horas':   vistaHoras($ciudad, $datos);   break;
    case 'semana':  vistaSemana($ciudad, $datos);  break;
    case 'historial': vistaHistorial($datos);      break;
}
// Segun el valor del parametro muestra una pagina u otra
```

---

### src/vistas/vistas.php
Contiene todas las funciones de presentacion HTML de la aplicacion.
```php
function mostrarCabecera($titulo) { ... }
// Genera el HTML comun de todas las paginas (head, estilos, header)

function mostrarPiePagina() { ... }
// Cierra las etiquetas HTML

function vistaActual($ciudad, $datos) { ... }
// Muestra los datos meteorologicos actuales en una tabla

function vistaHoras($ciudad, $horas) { ... }
// Muestra la prevision por horas en una tabla

function vistaSemana($ciudad, $dias) { ... }
// Muestra la prevision semanal en una tabla con grafica de temperaturas

function vistaHistorial($consultas) { ... }
// Muestra todas las consultas realizadas con badges de colores
```

---

### src/index.php
Punto de entrada de la aplicacion. Gestiona el formulario de busqueda y la sesion del usuario.
```php
session_start();
// Inicia la sesion para guardar los datos de la ciudad seleccionada

// Si el usuario selecciona una ciudad de la lista:
// - Guarda los datos en sesion
// - Redirige con header() para evitar reenvio del formulario

// Si el formulario se envia:
// - Llama al controlador para buscar ciudades
// - Si hay una sola ciudad la selecciona automaticamente
// - Si hay varias muestra una lista para que el usuario elija
// - Si no se encuentra muestra un mensaje de error
```

---

## Tecnologias utilizadas

- **PHP 8.2** con Apache — backend y logica de la aplicacion
- **MariaDB 10.11** — base de datos para guardar ciudades y consultas
- **Docker + Docker Compose** — contenedores para el despliegue
- **Nginx** — proxy inverso con soporte HTTPS
- **Certbot + Let's Encrypt** — certificado SSL gratuito
- **DuckDNS** — dominio gratuito para acceso desde internet
- **AWS EC2 t3.micro** — servidor en la nube
- **OpenWeatherMap API** — datos meteorologicos
- **Chart.js** — grafica de temperaturas semanales
- **Patron MVC** — arquitectura de la aplicacion

---

## Instalacion en local

1. Clona el repositorio:
```bash
git clone https://github.com/fjpereirab01/aplicacion-tiempo-franciscojavier.git
cd aplicacion-tiempo-franciscojavier
```

2. Copia el archivo de configuracion y añade tu API key:
```bash
cp src/configuracion/config.example.php src/configuracion/config.php
```

3. Edita `src/configuracion/config.php` y añade tu API key de OpenWeatherMap.

4. Arranca los contenedores:
```bash
docker-compose up -d --build
```

5. Accede en el navegador: `http://localhost:8080`

---

## Comprobacion

### Pagina principal
![Pagina principal](imagenes/captura_entrada.png)

### Seleccion de ciudad
![Seleccion de ciudad](imagenes/opciones.png)

### Tiempo actual
![Tiempo actual](imagenes/tiempo_actual.png)

### Prevision por horas
![Prevision por horas](imagenes/prevision_horas.png)

### Prevision semanal con grafica
![Prevision semanal](imagenes/prevision_semanal.png)

### Historial de consultas
![Historial](imagenes/historial.png)

---

## Conclusion

La aplicacion cumple con todos los requisitos de la practica:

- Busqueda de ciudades con seleccion multiple cuando hay varias ciudades con el mismo nombre
- Consulta del tiempo actual, por horas y semanal usando la API de OpenWeatherMap
- Almacenamiento de todas las consultas en una base de datos MariaDB usando el patron DAO
- Grafica de temperaturas semanales con Chart.js
- Historial de todas las consultas realizadas
- Despliegue en AWS EC2 con Docker accesible desde internet mediante HTTPS
- Arquitectura MVC para una mejor organizacion del codigo
- Dominio gratuito con DuckDNS y certificado SSL con Let's Encrypt

**URL de acceso:** https://fjpereirab.duckdns.org  
**Repositorio GitHub:** https://github.com/fjpereirab01/aplicacion-tiempo-franciscojavier