# Prueba Técnica Edwin José Backend GeoPagos

## Instrucciones para ejecutar el proyecto Laravel

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

## Ejecución y documentación

Para acceder a la documentación del proyecto, debes ejecutar los siguientes comandos desde la raíz:

```bash
php artisan serve
```
### Tienda nube metodos

1. Registra un usuario Administrador en la pestaña Auth con el método register.
2. Inicia sesión con el usuario administrador en la pestaña Auth usando el método login con los datos del método 1.

Al hacer login, el método responde con el token de autenticación requerido.



# Merchant Transactions API CASOS DE USO

## Objective

Your assignment is to build an API to manage payments transactions of our merchants. Implement the assignment
using any programming language or framework of your choice. Feel free to use the tools and
technologies you are most comfortable with.

### Provided Resources

- We have provided an API using [json-server](https://github.com/typicode/json-server) that already includes endpoints
  to manage transactions and receivables. You
  can also use json-server as a database for development purposes. **If you're unfamiliar with json-server, please take
  some time to explore its functionalities before proceeding with the assignment**.

### Task 1: Create Merchant Transactions API

The primary objective is to create a new API that processes transactions for a particular merchant.

A transaction must include:

- The total transaction amount, formatted as a decimal string.
- A description of the transaction, for example, "T-Shirt Black M".
- Payment method: **debit_card** or **credit_card**.
- The card number (only the last 4 digits should be stored and returned, as it is sensitive information).
- The name of the cardholder.
- Card expiration date in MM/YY format.
- Card CVV.

When creating a transaction, **a merchant receivable must also be created**, a receivable represents the amount
of the transaction which goes to the merchant after deducting the applicable fee.

El objetivo principal es crear una nueva API que procese transacciones para un comerciante en particular.

Una transacción debe incluir:

El monto total de la transacción, formateado como una cadena decimal.
Una descripción de la transacción, por ejemplo, "Camiseta Negra M".
Método de pago: tarjeta_de_débito o tarjeta_de_crédito.
El número de la tarjeta (solo se deben almacenar y devolver los últimos 4 dígitos, ya que es información sensible).
El nombre del titular de la tarjeta.
Fecha de vencimiento de la tarjeta en formato MM/AA.
CVV de la tarjeta.
Al crear una transacción, también debe crearse una cuenta por cobrar del comerciante, que representa el monto de la transacción que va al comerciante después de deducir la tarifa aplicable.

#### Rules for Creating Receivables

| Transaction Type | Receivable Status | Payment Date                     | Fee |
|------------------|-------------------|----------------------------------|-----|
| **Debit Card**   | `paid`            | Same as creation date (D + 0)    | 2%  |
| **Credit Card**  | `waiting_funds`   | Creation date + 30 days (D + 30) | 4%  |

**Example**: If a receivable is created with a value of ARS 100.00 from a transaction with a **credit_card**, the
merchant will receive ARS 96.00.

### Task 2: Calculate Total Receivables per Period

Create an endpoint that returns the merchant's total receivables per period for a given merchant. The response should
include:

- Total amount of receivables.
- Amount receivable in the future.
- Total fee charged.

### Task 3: List All Merchant Transactions

Create an endpoint that returns all transactions for a given merchant.

## Setup

### Start provided services

```
docker compose up
```

This will expose in http://0.0.0.0:8080/ the API for managing transactions and receivables.

## API Services Overview

### Transactions

| Endpoint           | Method   | Description                                  | Request Body                                                                                                                                                                                       |
|--------------------|----------|----------------------------------------------|----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|
| `transactions`     | `GET`    | List all transactions.                       | -                                                                                                                                                                                                  |
| `transactions/:id` | `GET`    | Get details of a specific transaction by ID. | -                                                                                                                                                                                                  |
| `transactions`     | `POST`   | Create a new transaction.                    | `{ "id": "1", "value": "250.00", "description": "T-Shirt", "method": "credit_card", "cardNumber": "2222", "cardHolderName": "Simplenube Store", "cardExpirationDate": "04/28", "cardCvv": "222" }` |
| `transactions/:id` | `DELETE` | Delete a transaction by ID.                  | -                                                                                                                                                                                                  |

### Receivables

| Endpoint          | Method   | Description                                 | Request Body                                                                                                                              |
|-------------------|----------|---------------------------------------------|-------------------------------------------------------------------------------------------------------------------------------------------|
| `receivables`     | `GET`    | List all receivables.                       | -                                                                                                                                         |
| `receivables/:id` | `GET`    | Get details of a specific receivable by ID. | -                                                                                                                                         |
| `receivables`     | `POST`   | Create a new receivable.                    | `{ "id": "2", "status": "waiting_funds", "create_date": "2022-05-20T19:20:14.576-03:00", "subtotal": 240, "discount": 10, "total": 230 }` |
| `receivables/:id` | `DELETE` | Delete a receivable by ID.                  | -                                                                                                                                         |






