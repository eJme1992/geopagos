#!/bin/bash

# Espera a que MySQL esté disponible
until mysql -h db -u laravel -psecret -e "SELECT 1" &>/dev/null; do
    echo "Esperando a que MySQL esté disponible..."
    sleep 2
done

# Verificación adicional para asegurarse de que el script ha pasado la comprobación
echo "MySQL está disponible. Ejecutando migraciones..."

# Ejecuta las migraciones y seeder
cd /var/www/html || { echo "Error: No se pudo cambiar al directorio /var/www/html"; exit 1; }
php artisan migrate:fresh --seed || { echo "Error: Fallo al ejecutar las migraciones"; exit 1; }

echo "Migraciones y seeders ejecutados correctamente."

# Mantener el contenedor en ejecución
tail -f /dev/null