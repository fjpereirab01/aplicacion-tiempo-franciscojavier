# Imagen base oficial de PHP 8.2 con Apache incluido
FROM php:8.2-apache

# Actualizamos los paquetes e instalamos las dependencias necesarias
# - libpng-dev, libjpeg-dev, libfreetype6-dev → para trabajar con imágenes y gráficas
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    # Instalamos extensiones de PHP necesarias:
    # - gd → para generar gráficas
    # - pdo, pdo_mysql → para conectar con MariaDB usando PDO
    # - mysqli → para conectar con MariaDB usando mysqli
    && docker-php-ext-install gd pdo pdo_mysql mysqli

# Activamos el módulo rewrite de Apache
# Necesario para URLs limpias y redirecciones
RUN a2enmod rewrite

# Copiamos nuestra configuración personalizada de Apache al contenedor
COPY apache/000-default.conf /etc/apache2/sites-enabled/000-default.conf

# Indicamos que el contenedor escucha en el puerto 80
EXPOSE 80