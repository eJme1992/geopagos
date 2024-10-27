#!/bin/sh

# Establecer permisos correctos
chown -R www-data:www-data /var/www/html/storage
chown -R www-data:www-data /var/www/html/bootstrap/cache
chmod -R 775 /var/www/html/storage
chmod -R 775 /var/www/html/bootstrap/cache

# Ejecutar el comando original para mantener el contenedor en ejecución
exec apache2-foreground