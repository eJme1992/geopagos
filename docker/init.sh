#!/bin/bash

# Ejecutar el script de inicialización
/usr/local/bin/init.sh

# Iniciar Apache en primer plano
exec apache2-foreground