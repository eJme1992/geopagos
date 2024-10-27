# Usa la imagen base oficial de PHP con Apache
FROM php:8.2-apache

# Instala dependencias del sistema
RUN apt-get update && apt-get install -y \
    zip \
    unzip \
    libpq-dev \
    libjpeg-dev \
    libpng-dev \
    libfreetype6-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd pdo pdo_mysql

# Configura la carpeta raíz del proyecto en Apache
RUN a2enmod rewrite

# Configura ServerName en Apache para evitar advertencias
RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf


# Copia el archivo de configuración de PHP
COPY docker/php.ini /usr/local/etc/php/php.ini

# Instala Composer
COPY --from=composer:2.2 /usr/bin/composer /usr/bin/composer

# Establece el directorio de trabajo
WORKDIR /var/www/html

# Copia todos los archivos del proyecto al contenedor desde la raíz del proyecto
COPY ../ /var/www/html/

# Crea las carpetas necesarias y asigna permisos
RUN mkdir -p /var/www/html/storage/framework/{sessions,views,cache} \
    && mkdir -p /var/www/html/bootstrap/cache \
    && chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache \
    && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache 

# Instala el cliente MySQL
RUN apt-get update && apt-get install -y default-mysql-client


# Copia el script init.sh y establece los permisos
COPY docker/init.sh /usr/local/bin/init.sh
RUN chmod +x /usr/local/bin/init.sh

# Instala las dependencias de Composer
RUN composer install

# Copia el script de entrada
COPY docker/entrypoint.sh /usr/local/bin/
RUN chmod +x /usr/local/bin/entrypoint.sh

# Establece el script de entrada y el comando por defecto
ENTRYPOINT ["entrypoint.sh"]
CMD ["apache2-foreground"]
