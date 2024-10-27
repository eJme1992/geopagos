FROM php:8.2-apache

# Instalar dependencias necesarias
RUN apt-get update && apt-get install -y \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    libpng-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) gd \
    && docker-php-ext-install pdo_mysql

# Copiar el código de la aplicación
COPY . /var/www/html

# Copiar el script de inicialización desde la carpeta docker
COPY docker/init.sh /usr/local/bin/init.sh
RUN chmod +x /usr/local/bin/init.sh

# Crear directorios necesarios y establecer permisos correctos
RUN mkdir -p /var/www/html/storage/framework/{sessions,views,cache} \
    && mkdir -p /var/www/html/storage/logs \
    && mkdir -p /var/www/html/bootstrap/cache \
    && chown -R www-data:www-data /var/www/html/storage \
    && chown -R www-data:www-data /var/www/html/bootstrap/cache \
    && chmod -R 775 /var/www/html/storage \
    && chmod -R 775 /var/www/html/bootstrap/cache

# Habilitar el módulo de reescritura de Apache
RUN a2enmod rewrite

# Configurar FROM php:8.2-apache

# Instalar dependencias necesarias
RUN apt-get update && apt-get install -y \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    libpng-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) gd \
    && docker-php-ext-install pdo_mysql

# Copiar el código de la aplicación
COPY . /var/www/html

# Copiar el script de inicialización desde la carpeta docker
COPY docker/init.sh /usr/local/bin/init.sh
RUN chmod +x /usr/local/bin/init.sh

# Crear directorios necesarios y establecer permisos correctos
RUN mkdir -p /var/www/html/storage/framework/{sessions,views,cache} \
    && mkdir -p /var/www/html/storage/logs \
    && mkdir -p /var/www/html/bootstrap/cache \
    && chown -R www-data:www-data /var/www/html/storage \
    && chown -R www-data:www-data /var/www/html/bootstrap/cache \
    && chmod -R 775 /var/www/html/storage \
    && chmod -R 775 /var/www/html/bootstrap/cache

# Habilitar el módulo de reescritura de Apache
RUN a2enmod rewrite

# Configurar Apache
COPY docker/apache-config.conf /etc/apache2/sites-available/000-default.conf

# Limpiar caché de vistas de Laravel al construir la imagen
RUN php artisan view:clear
COPY apache-config.conf /etc/apache2/sites-available/000-default.conf

# Limpiar caché de vistas de Laravel al construir la imagen
RUN php artisan view:clear