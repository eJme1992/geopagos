#!/bin/bash

# Crear directorios necesarios y establecer permisos
mkdir -p /var/www/html/storage/framework/views
chown -R www-data:www-data /var/www/html/storage
chmod -R 775 /var/www/html/storage

# Llamar al comando principal
exec "$@"
