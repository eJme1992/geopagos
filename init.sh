#!/bin/bash
# Crear directorios necesarios y establecer permisos
mkdir -p /var/www/html/storage/framework/views
chown -R www-data:www-data /var/www/html/storage
chmod -R 775 /var/www/html/storage

# Espera a que MySQL esté disponible
until mysql -h db -u laravel -psecret -e "SELECT 1" &>/dev/null; do
    echo "Esperando a que MySQL esté disponible..."
    sleep 2
done

# Verificación adicional para asegurarse de que el script ha pasado la comprobación
echo "MySQL está disponible. Ejecutando migraciones..."
cd /var/www/html || { echo "Error: No se pudo cambiar al directorio /var/www/html"; exit 1; }
php artisan migrate:fresh --seed || { echo "Error: Fallo al ejecutar las migraciones"; exit 1; }

echo "Migraciones y seeders ejecutados correctamente."

# Iniciar Apache
exec apache2-foreground