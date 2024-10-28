#!/bin/bash
# Crear directorios necesarios y establecer permisos
mkdir -p /var/www/html/storage/framework/views
chown -R www-data:www-data /var/www/html/storage
chmod -R 775 /var/www/html/storage

# Verificar conectividad inicial a MySQL
if ! mysql -h db -u laravel -psecret -e "SELECT 1"; then
    echo "Error: No se pudo conectar a MySQL. Verifique las credenciales y la disponibilidad del servidor."
fi

# Espera a que MySQL esté disponible con un tiempo de espera máximo
MAX_WAIT=300  # 5 minutos
WAIT_INTERVAL=5
WAIT_TIME=0

until mysql -h db -u laravel -psecret -e "SELECT 1" &>/dev/null; do
    if [ $WAIT_TIME -ge $MAX_WAIT ]; then
        echo "Error: Tiempo de espera máximo alcanzado. MySQL no está disponible."
        exit 1
    fi
    echo "Esperando a que MySQL esté disponible..."
    sleep $WAIT_INTERVAL
    WAIT_TIME=$((WAIT_TIME + WAIT_INTERVAL))
done

# Verificación adicional para asegurarse de que el script ha pasado la comprobación
echo "MySQL está disponible. Ejecutando migraciones..."
cd /var/www/html || { echo "Error: No se pudo cambiar al directorio /var/www/html"; exit 1; }
php artisan migrate:fresh --seed || { echo "Error: Fallo al ejecutar las migraciones"; exit 1; }

echo "Migraciones y seeders ejecutados correctamente."

# Iniciar Apache
exec apache2-foreground