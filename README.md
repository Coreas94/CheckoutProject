<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

# Proyecto de pago con Paypal en Laravel 10

Este proyecto es una aplicación que simula un checkout con Laravel 10, que utiliza JWT para la autenticación de usuarios y PayPal Sandbox para el procesamiento de pagos. A continuación, se detallan las instrucciones para configurar y ejecutar el proyecto.

## Tabla de Contenidos

- [Requisitos Previos](#requisitos-previos)
- [Instalación](#instalación)
- [Configuración](#configuración)
- [Migraciones y Seeders](#migraciones-y-seeders)
- [Ejecución del Proyecto](#ejecución-del-proyecto)
- [Pruebas con Postman](#pruebas-con-postman)
  - [Autenticación con JWT](#autenticación-con-jwt)
  - [Proceso de Checkout](#proceso-de-checkout)
  - [Verificación del Pago](#verificación-del-pago)

## Requisitos Previos

Antes de comenzar, Asegurarse de tener instalados los siguientes componentes en tu sistema:

- PHP >= 8.1
- Composer
- MySQL o cualquier otra base de datos compatible
- Postman para pruebas de API

## Instalación

Sigue estos pasos para instalar el proyecto en tu entorno local:

1. **Clonar el repositorio:**

   ```bash
   git clone https://github.com/Coreas94/CheckoutProject.git
   cd CheckoutProject
   ```

2. **Instalar las dependencias de PHP:**

   ```bash
   composer install
   ```

3. **Crear la base de datos MySQL:**

   Se debe crear una base de datos en MySQL llamada `checkout_project` antes de continuar con la configuración.

   ```sql
   CREATE DATABASE checkout_project;

## Configuración

1. **Configurar el archivo `.env`:**

   Se debe copiar el archivo `.env.example` y renombrar a `.env`. Luego, actualizar las siguientes variables en el archivo `.env`:

   ```
    env
    APP_NAME=Laravel
    APP_ENV=local
    APP_KEY=base64:...
    APP_DEBUG=true
    APP_URL=http://localhost

    DB_CONNECTION=mysql
    DB_HOST=127.0.0.1
    DB_PORT=3306
    DB_DATABASE=checkout_project
    DB_USERNAME=usuario-base-de-datos
    DB_PASSWORD=contraseña-base-de-datos

    JWT_SECRET=tu_jwt_secret

    PAYPAL_MODE=sandbox
    PAYPAL_SANDBOX_CLIENT_ID=AQLvixvO-RKMtdh-tZp1C3FS9O3JNN_T3rcLcE-Mmg5CmsemgWHzfrNJbEjUqbeKXUUVE6FekD6JucSn
    PAYPAL_SANDBOX_CLIENT_SECRET=EJfWJ4JlZkfl4d7uVuyDEvuXxEqIzCB05LoXTNCJheWPtgf7SJR9aEmojIJKNnh3CpALGjSXsiP2VsEe
    PAYPAL_CURRENCY=USD
   ```

   Asegurarse de configurar correctamente la base de datos y las credenciales de PayPal.

2. **Generar la clave de la aplicación:**

   ```bash
   php artisan key:generate
   ```

3. **Generar la clave JWT:**

   ```bash
   php artisan jwt:secret
   ```

## Migraciones y Seeders

1. **Ejecutar las migraciones:**

   ```bash
   php artisan migrate
   ```

2. **Ejecutar el seeder de usuario:**

   Para crear un usuario de prueba en la base de datos, ejecuta el siguiente comando:

   ```bash
   php artisan db:seed --class=UserSeeder
   ```

   Esto creará un usuario con los siguientes datos:

   - **Email:** `usuario@test.com`
   - **Contraseña:** `Usuario12345`

   Se puede utilizar este usuario para iniciar sesión y probar la funcionalidad de la aplicación.

## Ejecución del Proyecto

1. **Inicia el servidor de desarrollo:**

   ```bash
   php artisan serve
   ```

## Pruebas con Postman

### Autenticación con JWT

1. **Login:**

   - **Método:** `POST`
   - **URL:** `http://localhost:8000/api/auth`
   - **Headers:**
     - `Content-Type: application/json`
   - **Body (raw JSON):**
     ```json
     {
       "email": "usuario@test.com",
       "password": "Usuario12345"
     }
     ```
   - **Respuesta esperada:**
     - Un token JWT que se deberá usar en las siguientes solicitudes.

### Proceso de Checkout

2. **Datos de inicio de sesión para PayPal Sandbox:**

   Antes de realizar el proceso de checkout, utilizar los siguientes datos para aprobar los pagos en PayPal Sandbox:

   - **Sandbox URL:** [https://sandbox.paypal.com](https://sandbox.paypal.com)
   - **Email:** `sb-uwpqu32019392@personal.example.com`
   - **Password:** `5b,S<OJP`

   **Nota:** Al realizar el pago y recibir la URL de aprobación (`approval_url`), abrir una nueva ventana en el navegador (preferiblemente en modo incógnito), iniciar sesión en PayPal con los datos anteriores, y luego carga la URL proporcionada por Postman.

3. **Crear un Pedido (Checkout):**

   - **Método:** `POST`
   - **URL:** `http://localhost:8000/api/checkout`
   - **Headers:**
     - `Content-Type: application/json`
     - `Authorization: Bearer <token_jwt>`
   - **Body (raw JSON):**
     ```json
     {
       "amount": "20.99"
     }
     ```
   - **Respuesta esperada:**
     - Una URL de aprobación de PayPal (`approval_url`). Abrir esa URL en el navegador para aprobar el pago con la cuenta de PayPal Sandbox brindada en el paso anterior.

### Verificación del Pago

4. **Verificar el Pago:**

   - **Método:** `GET`
   - **URL:** `http://localhost:8000/api/payment/success?token=<token-paypal>`
   - **Headers:**
     - `Authorization: Bearer <tu_token_jwt>`
   - **Respuesta esperada:**
     - Un mensaje confirmando que el pago se completó con éxito y los detalles del pedido.

    **Nota:** El ```token-paypal``` es el token que se puede visualizar al final del approval_url.


**Creado por Josué Coreas**
