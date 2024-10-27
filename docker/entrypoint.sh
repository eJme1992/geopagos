#!/bin/bash

# Esperar a que MySQL esté listo
until mysql -h "${DB_HOST}" -u "${DB_USERNAME}" -p"${DB_PASSWORD}" -e "SELECT 1" &> /dev/null; do
    echo "Esperando a que MySQL esté disponible..."
    sleep 3
done

# Crear la base de datos si no existe
echo "Creando base de datos si no existe..."
mysql -h "${DB_HOST}" -u "${DB_USERNAME}" -p"${DB_PASSWORD}" -e "CREATE DATABASE IF NOT EXISTS \`${DB_DATABASE}\` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

# Ejecutar migraciones y seeders
echo "Ejecutando migraciones y seeders..."
php artisan migrate --seed --force

# Iniciar Apache
apache2-foreground
