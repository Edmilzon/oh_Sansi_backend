# oh_Sansi_backend

Backend desarrollado en **PHP** usando el framework **Laravel** para la gestión de productos y futuras APIs del proyecto oh_Sansi.

## 📋 Prerrequisitos

Antes de empezar, asegúrate de tener instalado el siguiente software y extensiones en tu entorno de desarrollo:

- **PHP**: `^8.2` (según `composer.json`)
- **Laravel**: `^10.0` (o la versión que estés usando)
- **Extensiones PHP**: `mbstring`, `xml`, `ctype`, `json`, `openssl`, etc. (las requeridas por Laravel)
- **Composer**: Instrucciones de instalación
- **Base de datos**: MySQL, PostgreSQL, etc.

## 🚀 Instalación y Configuración

Sigue estos pasos para poner en marcha el proyecto en tu máquina local.

1.  **Clonar el repositorio** (si aún no lo has hecho):
    ```bash
    git clone <URL_DEL_REPOSITORIO>
    cd oh_Sansi_backend
    ```

2.  **Instalar dependencias de PHP** con Composer:
    ```bash
    composer install
    ```

3.  **Crear el archivo de entorno**:
    Copia el archivo de ejemplo `.env.example` para crear tu propio archivo de configuración local.
    ```bash
    cp .env.example .env
    ```

4.  **Generar la clave de la aplicación**:
    Este comando es crucial para la seguridad de tu aplicación Laravel.
    ```bash
    php artisan key:generate
    ```

5.  **Configurar la base de datos**:
    Abre el archivo `.env` y configura las variables de conexión a tu base de datos (nombre de la base de datos, usuario, contraseña).
    ```ini
    DB_CONNECTION=mysql
    DB_HOST=127.0.0.1
    DB_PORT=3306
    DB_DATABASE=nombre_de_tu_bd
    DB_USERNAME=tu_usuario
    DB_PASSWORD=tu_contraseña
    ```

6.  **Ejecutar las migraciones y seeders**:
    Las migraciones crearán la estructura de tablas en tu base de datos y los seeders la poblarán con datos iniciales.
    ```bash
    php artisan migrate --seed
    ```
    Si necesitas reiniciar la base de datos y volver a ejecutar las migraciones y seeders:
    ```bash
    php artisan migrate:fresh --seed
    ```

## ▶️ Iniciar el Servidor de Desarrollo

Una vez configurado, puedes iniciar el servidor local de Laravel con el siguiente comando:
```bash
php artisan serve
```

Configura tu base de datos en el archivo `.env`.

## Uso

```bash
php artisan serve
php artisan migrate
php artisan migrate:fresh

```

## Reniciar el servidor 

```bash
php artisan config:clear
php artisan cache:clear
composer dump-autoload
```

## Endpoints principales

- `/api/productos` - Gestión de productos (listar, crear, editar, eliminar)
- (Agrega aquí más endpoints según vayas creando nuevas APIs)

## Tecnologías

- PHP
- Laravel
- MySQL o la base de datos que uses


php artisan route:clear

php artisan migrate:fresh --seed


## Autor

Edmilzon
