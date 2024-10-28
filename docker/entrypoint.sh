#!/bin/bash
# Crear directorios necesarios y establecer permisos
mkdir -p /var/www/html/storage/framework/views
chown -R www-data:www-data /var/www/html/storage
chmod -R 775 /var/www/html/storage

# Verificación adicional para asegurarse de que el script ha pasado la comprobación
cd /var/www/html || { echo "Error: No se pudo cambiar al directorio /var/www/html"; exit 1; }
# Iniciar Apache
exec apache2-foreground