# 🚀 OhSansi Backend API

<p align="center">
  <img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo">
</p>

<p align="center">
  <strong>API Backend construida con Laravel 11</strong><br>
  <em>Una API robusta y escalable para el proyecto OhSansi</em>
</p>

---

## 📋 **TABLA DE CONTENIDOS**

- [🚀 Características](#-características)
- [🛠️ Tecnologías](#️-tecnologías)
- [📦 Instalación](#-instalación)
- [⚙️ Configuración](#️-configuración)
- [🚀 Comandos Básicos](#-comandos-básicos)
- [🔍 Laravel Telescope](#-laravel-telescope)
- [🧪 PHPUnit Testing](#-phpunit-testing)
- [🔐 Autenticación y Permisos](#-autenticación-y-permisos)
- [🗄️ Base de Datos](#️-base-de-datos)
- [📁 Estructura del Proyecto](#-estructura-del-proyecto)
- [🎯 Ejemplos de Uso](#-ejemplos-de-uso)
- [🤝 Contribución](#-contribución)

---

## 🚀 **CARACTERÍSTICAS**

- **🔧 API RESTful** - Endpoints bien estructurados y documentados
- **🔐 Autenticación JWT** - Con Laravel Sanctum
- **👥 Sistema de Roles** - Permisos granulares con Laravel Permission
- **📊 Monitoreo en Tiempo Real** - Con Laravel Telescope
- **🧪 Testing Automatizado** - Con PHPUnit
- **🗄️ Base de Datos PostgreSQL** - Conectada a Neon
- **📝 Logs Detallados** - Para debugging y auditoría
- **⚡ Performance Optimizada** - Sin frontend innecesario

---

## 🛠️ **TECNOLOGÍAS**

| Herramienta | Versión | Propósito |
|-------------|---------|-----------|
| **Laravel** | 11.x | Framework PHP principal |
| **PHP** | 8.4+ | Lenguaje de programación |
| **PostgreSQL** | 15+ | Base de datos principal |
| **Laravel Sanctum** | 4.x | Autenticación API |
| **Laravel Permission** | 6.x | Roles y permisos |
| **Laravel Telescope** | 5.x | Debugging y monitoreo |
| **PHPUnit** | 11.x | Framework de testing |

---

## 📦 **INSTALACIÓN**

### **Prerrequisitos:**
- PHP 8.4+
- Composer 2.x
- PostgreSQL (o acceso a Neon)
- Git

### **Pasos de instalación:**

```bash
# 1. Clonar el repositorio
git clone https://github.com/Edmilzon/oh_Sansi_backend.git
cd oh_Sansi_backend

# 2. Instalar dependencias
composer install

# 3. Copiar archivo de configuración
cp .env.example .env

# 4. Configurar variables de entorno (ver sección configuración)

# 5. Generar clave de aplicación
php artisan key:generate

# 6. Ejecutar migraciones
php artisan migrate

# 7. Ejecutar seeders (opcional)
php artisan db:seed
```

---

## ⚙️ **CONFIGURACIÓN**

### **Archivo .env principal:**

```env
APP_NAME=OhSansiBackend
APP_ENV=local
APP_KEY=base64:tu_clave_aqui
APP_DEBUG=true
APP_URL=http://localhost:8000

# Base de datos PostgreSQL
DB_CONNECTION=pgsql
DB_HOST=tu_host_postgresql
DB_PORT=5432
DB_DATABASE=tu_base_de_datos
DB_USERNAME=tu_usuario
DB_PASSWORD=tu_password
DB_SSLMODE=require

# Telescope (Debugging)
TELESCOPE_ENABLED=true
TELESCOPE_DRIVER=database

# Cache y sesiones
CACHE_STORE=file
SESSION_DRIVER=file
QUEUE_CONNECTION=sync
```

### **Configuración de base de datos:**
- **Host:** Tu servidor PostgreSQL o Neon
- **Puerto:** 5432 (por defecto)
- **Base de datos:** Nombre de tu base de datos
- **Usuario:** Usuario con permisos de escritura
- **Password:** Contraseña del usuario

---

## 🚀 **COMANDOS BÁSICOS**

### **Ejecutar el proyecto:**
```bash
# Servidor de desarrollo (recomendado para APIs)
php -S localhost:8000 -t . vendor/laravel/framework/src/Illuminate/Foundation/resources/server.php

# Alternativa con artisan (requiere directorio public/)
php artisan serve
```

### **Ejecutar tests:**
```bash
# Todos los tests
php artisan test

# Tests específicos
php artisan test tests/Feature/ApiTest.php

# Con más detalles
php artisan test --verbose

# Con coverage (si está configurado)
php artisan test --coverage
```

### **Comandos de base de datos:**
```bash
# Ejecutar migraciones
php artisan migrate

# Revertir migraciones
php artisan migrate:rollback

# Ver estado de migraciones
php artisan migrate:status

# Crear nueva migración
php artisan make:migration nombre_de_la_migracion
```

---

## 🔍 **LARAVEL TELESCOPE**

### **¿Qué es Telescope?**
Laravel Telescope es una herramienta de depuración que registra **TODAS** las actividades de tu aplicación en tiempo real.

### **¿Qué registra Telescope?**
- **🔐 Requests HTTP** - Todas las peticiones a tu API
- **🗄️ Queries SQL** - Consultas a la base de datos
- **📧 Emails** - Envío de correos
- **🚀 Jobs** - Trabajos en cola
- **❌ Exceptions** - Errores y excepciones
- **📝 Logs** - Registros del sistema
- **🔑 Auth** - Intentos de autenticación
- **📊 Cache** - Operaciones de caché

### **¿Cómo funciona?**
1. **Intercepta** todas las operaciones de Laravel
2. **Registra** en la base de datos
3. **Muestra** en una interfaz web bonita
4. **Permite** filtrar y buscar eventos

### **URL de acceso:**
```
http://localhost:8000/telescope
```

### **Configuración en .env:**
```env
TELESCOPE_ENABLED=true
TELESCOPE_DRIVER=database
```

### **Beneficios:**
- **🐛 Debugging** más fácil
- **📈 Performance** - ves qué queries son lentas
- **🔍 Troubleshooting** - encuentras problemas rápido
- **📊 Analytics** - métricas de uso de tu API

---

## 🧪 **PHPUNIT TESTING**

### **¿Qué es PHPUnit?**
Es el framework estándar de testing para PHP que Laravel usa por defecto.

### **¿Cómo funciona?**
1. **Ejecuta** tests automatizados
2. **Verifica** que tu código funcione correctamente
3. **Previene** errores al hacer cambios
4. **Garantiza** calidad del código

### **Estructura de tests:**
```
tests/
├── Feature/          # Tests de funcionalidades completas
│   └── ApiTest.php   # Test de tu API
├── Unit/             # Tests de unidades individuales
├── TestCase.php      # Clase base para todos los tests
└── CreatesApplication.php  # Trait para crear la app
```

### **Comandos de testing:**
```bash
# Ejecutar todos los tests
php artisan test

# Ejecutar tests específicos
php artisan test tests/Feature/ApiTest.php

# Ejecutar con más detalles
php artisan test --verbose

# Ejecutar tests en paralelo
php artisan test --parallel
```

### **Ejemplo de test (ApiTest.php):**
```php
<?php

namespace Tests\Feature;

use Tests\TestCase;

class ApiTest extends TestCase
{
    /**
     * Test that the API test endpoint returns a successful response.
     */
    public function test_api_test_endpoint_returns_success(): void
    {
        $response = $this->get('/api/test');

        $response->assertStatus(200)
                ->assertJson([
                    'message' => '¡OhSansi Backend API funcionando correctamente!',
                    'status' => 'success'
                ])
                ->assertJsonStructure([
                    'message',
                    'status',
                    'timestamp'
                ]);
    }
}
```

### **Tipos de tests que puedes crear:**

#### **Feature Tests (Funcionalidades):**
```php
// Test de autenticación
public function test_user_can_login()
public function test_user_cannot_access_protected_route()

// Test de CRUD
public function test_can_create_user()
public function test_can_update_user()
public function test_can_delete_user()

// Test de validación
public function test_validation_requires_email()
public function test_validation_requires_password()
```

#### **Unit Tests (Unidades):**
```php
// Test de modelos
public function test_user_has_email()
public function test_user_can_have_roles()

// Test de servicios
public function test_email_service_sends_email()
public function test_payment_service_processes_payment()

// Test de helpers
public function test_helper_function_formats_date()
```

---

## 🔐 **AUTENTICACIÓN Y PERMISOS**

### **Laravel Sanctum:**
- **Autenticación JWT** para APIs
- **Tokens de acceso** seguros
- **Middleware de autenticación** integrado
- **Gestión de sesiones** para SPAs

### **Laravel Permission:**
- **Sistema de roles** jerárquico
- **Permisos granulares** por acción
- **Middleware de autorización** automático
- **Cache de permisos** para performance

### **Ejemplo de uso:**
```php
// En tu controlador
public function index()
{
    // Verificar si tiene permiso
    if (auth()->user()->can('view-users')) {
        return User::all();
    }
    
    return response()->json(['message' => 'No autorizado'], 403);
}

// En tus rutas
Route::middleware(['auth:sanctum', 'permission:view-users'])->group(function () {
    Route::get('/users', [UserController::class, 'index']);
});
```

---

## 🗄️ **BASE DE DATOS**

### **PostgreSQL:**
- **Base de datos principal** del proyecto
- **Migraciones** para estructura de tablas
- **Seeders** para datos de prueba
- **Conexión SSL** para seguridad

### **Migraciones principales:**
- `users` - Usuarios del sistema
- `personal_access_tokens` - Tokens de Sanctum
- `permissions` - Permisos del sistema
- `roles` - Roles de usuario
- `telescope_entries` - Logs de Telescope

### **Ejecutar migraciones:**
```bash
# Ejecutar todas las migraciones
php artisan migrate

# Revertir última migración
php artisan migrate:rollback

# Revertir todas las migraciones
php artisan migrate:reset

# Ejecutar y revertir (para testing)
php artisan migrate:fresh
```

---

## 📁 **ESTRUCTURA DEL PROYECTO**

```
oh_Sansi_backend/
├── app/                          # Lógica de la aplicación
│   ├── Http/                     # Controllers y Middleware
│   │   └── Controllers/          # Controladores de la API
│   ├── Models/                   # Modelos Eloquent
│   └── Providers/                # Service Providers
├── config/                       # Archivos de configuración
├── database/                     # Migraciones y seeders
│   └── migrations/               # Estructura de base de datos
├── routes/                       # Definición de rutas
│   ├── api.php                   # Rutas de la API
│   └── web.php                   # Rutas web (si las hay)
├── storage/                      # Archivos generados
├── tests/                        # Tests automatizados
│   ├── Feature/                  # Tests de funcionalidades
│   └── Unit/                     # Tests unitarios
├── vendor/                       # Dependencias de Composer
├── .env                          # Variables de entorno
├── composer.json                 # Dependencias PHP
├── index.php                     # Punto de entrada
└── README.md                     # Este archivo
```

---

## 🎯 **EJEMPLOS DE USO**

### **1. Ejecutar la API:**
```bash
# Terminal 1: Ejecutar servidor
php -S localhost:8000 -t . vendor/laravel/framework/src/Illuminate/Foundation/resources/server.php

# Terminal 2: Probar endpoints
curl http://localhost:8000/api/test
curl http://localhost:8000/api/users
```

### **2. Monitorear con Telescope:**
1. **Abrir** `http://localhost:8000/telescope`
2. **Ver** todas las peticiones HTTP
3. **Analizar** queries SQL
4. **Revisar** errores y excepciones

### **3. Ejecutar tests:**
```bash
# Ejecutar todos los tests
php artisan test

# Ver coverage (si está configurado)
php artisan test --coverage

# Ejecutar tests específicos
php artisan test --filter=ApiTest
```

### **4. Crear nuevo endpoint:**
```bash
# Crear controlador
php artisan make:controller UserController

# Crear modelo
php artisan make:model User

# Crear migración
php artisan make:migration create_users_table

# Crear test
php artisan make:test UserTest
```

---

## 🚀 **DESPLIEGUE**

### **Requisitos del servidor:**
- **PHP** 8.4+
- **PostgreSQL** 15+
- **Composer** 2.x
- **Git**

### **Pasos de despliegue:**
```bash
# 1. Clonar en servidor
git clone https://github.com/Edmilzon/oh_Sansi_backend.git

# 2. Instalar dependencias
composer install --optimize-autoloader --no-dev

# 3. Configurar .env para producción
APP_ENV=production
APP_DEBUG=false
TELESCOPE_ENABLED=false

# 4. Ejecutar migraciones
php artisan migrate --force

# 5. Configurar servidor web (Nginx/Apache)
# 6. Configurar supervisor para colas (si se usan)
```

---

## 🤝 **CONTRIBUCIÓN**

### **Cómo contribuir:**
1. **Fork** el proyecto
2. **Crea** una rama para tu feature (`git checkout -b feature/AmazingFeature`)
3. **Commit** tus cambios (`git commit -m 'Add some AmazingFeature'`)
4. **Push** a la rama (`git push origin feature/AmazingFeature`)
5. **Abre** un Pull Request

### **Estándares de código:**
- **PSR-12** para estilo de código
- **Tests** para todas las nuevas funcionalidades
- **Documentación** actualizada
- **Commits** descriptivos

---

## 📞 **SOPORTE**

### **Canales de soporte:**
- **Issues de GitHub** - Para bugs y feature requests
- **Discussions** - Para preguntas y discusiones
- **Wiki** - Para documentación adicional

### **Contacto:**
- **Desarrollador:** Edmilzon
- **Email:** [Tu email aquí]
- **GitHub:** [@Edmilzon](https://github.com/Edmilzon)

---

## 📄 **LICENCIA**

Este proyecto está bajo la licencia **MIT**. Ver el archivo `LICENSE` para más detalles.

---

## 🙏 **AGRADECIMIENTOS**

- **Laravel Team** - Por el increíble framework
- **Spatie** - Por Laravel Permission
- **Comunidad Laravel** - Por el apoyo continuo

---

<div align="center">
  <p><strong>Hecho con ❤️ usando Laravel</strong></p>
  <p><em>OhSansi Backend API - 2025</em></p>
</div> 