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

# Configura Apache para escuchar en el puerto 8080
RUN sed -i 's/Listen 80/Listen 8080/' /etc/apache2/ports.conf
RUN sed -i 's/:80/:8080/' /etc/apache2/sites-available/000-default.conf

# Instala Composer
COPY --from=composer:2.2 /usr/bin/composer /usr/bin/composer

# Establece el directorio de trabajo
WORKDIR /var/www/html

# Copia todos los archivos del proyecto al contenedor desde la raíz del proyecto
COPY . /var/www/html/

# Copia el archivo php.ini al contenedor
COPY php.ini /usr/local/etc/php/

# Crea las carpetas necesarias y asigna permisos
RUN mkdir -p /var/www/html/storage/framework/{sessions,views,cache} \
    && mkdir -p /var/www/html/bootstrap/cache \
    && chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache \
    && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache 

# Instala el cliente MySQL
RUN apt-get update && apt-get install -y default-mysql-client

# Copia el script init.sh y establece los permisos
COPY init.sh /usr/local/bin/init.sh
RUN chmod +x /usr/local/bin/init.sh

# Instala las dependencias de Composer
RUN composer install

# Copia el script de entrada
COPY entrypoint.sh /usr/local/bin/
RUN chmod +x /usr/local/bin/entrypoint.sh

# Exponer el puerto 8080
EXPOSE 8080

# Establece el script de entrada y el comando por defecto
ENTRYPOINT ["entrypoint.sh"]
CMD ["apache2-foreground"]