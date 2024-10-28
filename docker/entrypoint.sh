#!/bin/bash
# Crear directorios necesarios y establecer permisos
mkdir -p /var/www/html/storage/framework/views
chown -R www-data:www-data /var/www/html/storage
chmod -R 775 /var/www/html/storage

# Lista de IPs del servidor MySQL
MYSQL_HOSTS=("44.226.145.213" "54.187.200.255" "34.213.214.55" "35.164.95.156" "44.230.95.183" "44.229.200.200","https://geopagos.onrender.com")

# Espera a que MySQL esté disponible con un tiempo de espera máximo de 1 minuto
MAX_WAIT=60  # 1 minuto
WAIT_INTERVAL=5
WAIT_TIME=0
MYSQL_AVAILABLE=false

for HOST in "${MYSQL_HOSTS[@]}"; do
    until mysql -h "$HOST" -u laravel -psecret -e "SELECT 1" &>/dev/null; do
        if [ $WAIT_TIME -ge $MAX_WAIT ]; then
            echo "Advertencia: Tiempo de espera máximo alcanzado para $HOST. Intentando con la siguiente IP..."
            break
        fi
        echo "Esperando a que MySQL esté disponible en $HOST..."
        sleep $WAIT_INTERVAL
        WAIT_TIME=$((WAIT_TIME + WAIT_INTERVAL))
    done

    if mysql -h "$HOST" -u laravel -psecret -e "SELECT 1" &>/dev/null; then
        MYSQL_AVAILABLE=true
        echo "MySQL está disponible en $HOST. Ejecutando migraciones..."
        break
    fi
done

if [ "$MYSQL_AVAILABLE" = false ]; then
    echo "Advertencia: No se pudo conectar a MySQL en ninguna de las IPs proporcionadas. Continuando con el resto de las instrucciones."
fi

# Verificación adicional para asegurarse de que el script ha pasado la comprobación
cd /var/www/html || { echo "Error: No se pudo cambiar al directorio /var/www/html"; exit 1; }
php artisan migrate:fresh --seed || { echo "Error: Fallo al ejecutar las migraciones"; exit 1; }

echo "Migraciones y seeders ejecutados correctamente."

# Iniciar Apache
exec apache2-foreground