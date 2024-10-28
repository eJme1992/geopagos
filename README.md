# Prueba Técnica Edwin José Backend GeoPagos

# App corriendo deeployada http://ejme.byethost24.com/public/ 

## Instrucciones para ejecutar el proyecto Laravel en local usando Docker

### Requisitos previos
- Tener Docker y Docker Compose instalados en tu equipo.

### Configuración del entorno
1. Clona el repositorio del proyecto en tu máquina local.
2. Accede a la carpeta del proyecto:

   ```bash
   cd ruta/al/proyecto
   ```

3. Asegúrate de que el archivo `docker-compose.yml` esté en la raíz.

### Construcción y ejecución de contenedores
1. Ejecuta el siguiente comando para construir y ejecutar los contenedores:

   ```bash
   docker-compose up -d --build
   ```

   Esto levantará los contenedores en segundo plano y realizará automáticamente las migraciones de la base de datos si la configuración del archivo `.env` de Laravel está correcta.

   Archivo env 

    APP_NAME=Laravel
    
    APP_ENV=local
    
    APP_KEY=base64:YOUR_APP_KEY
    
    APP_DEBUG=true
    
    APP_URL=http://localhost

    LOG_CHANNEL=stack

    DB_CONNECTION=mysql
    
    DB_HOST=db
    
    DB_PORT=3306
    
    DB_DATABASE=laravel
    
    DB_USERNAME=laravel
   
    DB_PASSWORD=secret


### Acceso al contenedor de la aplicación
1. Una vez que los contenedores estén en ejecución, accede al contenedor de la aplicación con el siguiente comando:

   ```bash
   docker exec -it nombre_del_contenedor_php bash
   ```

   Asegúrate de reemplazar `nombre_del_contenedor_php` por el nombre real del contenedor de PHP que definiste en tu `docker-compose.yml`.

### Acceso a la base de datos. 
1. Al ejecutar el contenedor de docker ademas de hacer las migraciones crea una instancia de phpmyadmin para entra debe:

   ```plaintext
     http://localhost:8081/
   ```

User: laravel
Pass secret

### Prueba de conexión a la base de datos
1. Para verificar que la conexión a la base de datos sea correcta, puedes ejecutar los tests unitarios de conexión:

   ```bash
   php artisan test --filter testDatabaseConnection
   ```

   Si el test es exitoso, deberías ver un mensaje similar a este:

   ```plaintext
      PASS  Tests\Unit\ConnectionTest
     ✓ database connection                                                                                                                    0.11s  

     Tests:    1 passed (2 assertions)
     Duration: 0.15s
   ```

### Ejecución de tests unitarios
1. Para ejecutar todos los tests unitarios, utiliza el siguiente comando:

   ```bash
   vendor/bin/phpunit 
   ```

### Ejecución y documentación
1. Para acceder a la documentación del proyecto, debes  puedes acceder a la documentación en tu navegador en `http://localhost:8000`.


## ################################################################################ Instrucciones para ejecutar el proyecto Laravel en local sin Docker (OPCIONAL)  ################################################################################

### Requisitos previos
- PHP 7.1 o superior instalado en tu equipo.
- Un servidor web Apache y MySQL (o similar como MariaDB).
- [Composer](https://getcomposer.org/) instalado en tu equipo.

### Extensiones de PHP requeridas
Asegúrate de tener las siguientes extensiones de PHP habilitadas en tu archivo `php.ini`:

```ini
extension=bcmath.so
extension=bz2.so
extension=calendar.so
extension=ctype.so
extension=curl.so
extension=dba.so
extension=dom.so
extension=enchant.so
extension=exif.so
extension=fileinfo.so
extension=ftp.so
extension=gd.so
extension=gettext.so
extension=gmp.so
extension=iconv.so
extension=imap.so
extension=intl.so
extension=ldap.so
extension=mbstring.so
extension=mysqli.so
extension=oci8.so ; (si estás utilizando Oracle)
extension=odbc.so
extension=openssl.so
extension=pdo.so
extension=pdo_mysql.so
extension=pdo_pgsql.so
extension=pdo_sqlite.so
extension=pgsql.so
extension=shmop.so
extension=soap.so
extension=sockets.so
extension=sodium.so
extension=sqlite3.so
extension=sysvmsg.so
extension=sysvsem.so
extension=sysvshm.so
extension=tidy.so
extension=tokenizer.so
extension=wddx.so
extension=xml.so
extension=xmlreader.so
extension=xmlrpc.so
extension=xmlwriter.so
extension=xsl.so
extension=zip.so
```

### Configuración de la base de datos
1. En tu gestor de MySQL, crea una base de datos vacía.
2. Copia el archivo `.env.example` y renómbralo como `.env` en la raíz de tu proyecto Laravel.
3. Configura las siguientes variables en tu archivo `.env` con los detalles de tu base de datos:

```env
DB_HOST=nombre_del_host
DB_PORT=puerto
DB_DATABASE=nombre_de_la_base_de_datos
DB_USERNAME=nombre_de_usuario
DB_PASSWORD=contraseña
```

### Instalación de dependencias
1. Abre una terminal en la carpeta raíz de tu proyecto Laravel.
2. Ejecuta el comando `composer install` para instalar todas las dependencias del proyecto.

### Prueba de conexión a la base de datos
1. Abre una terminal en la carpeta raíz de tu proyecto Laravel.
2. Ejecuta el siguiente comando para ejecutar el test unitario de conexión y asegurarte de que la configuración de la base de datos sea correcta:

```bash
php artisan test --filter testDatabaseConnection
```

Luego, ejecuta el comando:

```bash
php artisan key:generate
```

3. Si el test es exitoso, significa que la configuración de la base de datos es correcta.

```plaintext
   PASS  Tests\Unit\ConnectionTest
  ✓ database connection                                                                                                                    0.11s  

  Tests:    1 passed (2 assertions)
  Duration: 0.15s
```

Estas instrucciones te ayudarán a configurar y ejecutar el proyecto Laravel correctamente. Si la base de datos está funcional, ejecuta desde la raíz el comando:

```bash
php artisan migrate:fresh --seed
```