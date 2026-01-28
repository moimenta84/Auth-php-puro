 Sistema de Autenticación Web

Sistema completo de autenticación basado en sesiones para un **miniframework PHP personalizado**, implementando el ciclo completo de registro, login, mantenimiento de sesión y logout.

##  Descripción

Este proyecto implementa un sistema de autenticación web que replica de forma simplificada el funcionamiento de frameworks modernos como Laravel. El sistema gestiona todo el ciclo de autenticación de usuarios de manera segura y eficiente.

> **Nota**: Este proyecto está desarrollado sobre un **miniframework PHP personalizado** creado específicamente para fines educativos. No es PHP puro ni utiliza frameworks externos como Laravel o Symfony, sino una arquitectura propia que simula su funcionamiento.

##  Objetivos

- Implementar un sistema completo de autenticación basado en sesiones
- Comprender el ciclo completo de autenticación web
- Gestionar de forma segura las contraseñas mediante hashing
- Proteger rutas mediante middlewares
- Mantener el estado de autenticación entre peticiones

##  Ciclo de Autenticación

### 1. Register (Registro)
- Validación de credenciales (email y password)
- Almacenamiento seguro mediante hash de contraseña
- Autenticación automática tras el registro
- Redirección a zona privada

### 2. Login (Inicio de sesión)
- Verificación de credenciales contra la base de datos
- Establecimiento del usuario autenticado en memoria
- Asociación del usuario con la sesión activa
- Regeneración del ID de sesión para prevenir ataques

### 3. Peticiones Autenticadas
- Recuperación automática del usuario desde la sesión
- Mantenimiento del estado sin reenvío de credenciales
- Usuario disponible durante toda la petición vía `Auth::user()`

### 4. Logout (Cierre de sesión)
- Eliminación del usuario autenticado de memoria
- Destrucción completa de la sesión
- Limpieza del estado de autenticación

##  Arquitectura

### Componentes Principales

#### Modelo Autenticable
```
App\Core\Auth\AuthenticatableModel
├── Implementa: Authenticatable (interfaz)
└── Usa: Authenticatable (trait)
```

#### Sistema de Autenticación
```
App\Core\Auth\Auth
├── Inicialización del sistema
├── Resolución del usuario autenticado
├── Gestión de login/logout
└── Punto de acceso global al usuario
```

#### Controladores
```
App\Http\Controllers\AuthController
├── showRegistrationForm()
├── register()
├── showLoginForm()
├── login()
└── logout()
```

#### Protección de Rutas
```
App\Http\Middlewares\AuthMiddleware
└── Verifica autenticación antes de acceder a rutas protegidas
```

##  Estructura de Archivos
```
project/
├── app/
│   ├── Core/
│   │   ├── Auth/
│   │   │   ├── Auth.php
│   │   │   └── AuthenticatableModel.php
│   │   ├── Contracts/
│   │   │   └── Authenticatable.php
│   │   ├── Traits/
│   │   │   └── Authenticatable.php
│   │   └── Session.php
│   ├── Http/
│   │   ├── Controllers/
│   │   │   └── AuthController.php
│   │   ├── Middlewares/
│   │   │   └── AuthMiddleware.php
│   │   └── Requests/
│   │       ├── LoginRequest.php
│   │       └── RegisterRequest.php
│   └── Models/
│       └── Usuario.php
├── bootstrap/
│   └── bootstrap.php
└── public/
    └── web/
        ├── login.php
        ├── register.php
        └── logout.php
```

##  Implementación

### 1. Clase Auth (Núcleo del Sistema)

La clase `Auth` es el componente central que:

- **Inicializa** el sistema de autenticación al inicio de cada petición
- **Resuelve** automáticamente el usuario desde la sesión
- **Expone** el usuario mediante métodos estáticos
- **Gestiona** las operaciones de login y logout
```php
// Métodos principales
Auth::init(Usuario::class);        // Inicialización
Auth::user();                       // Usuario autenticado
Auth::check();                      // ¿Usuario autenticado?
Auth::attempt($credentials);        // Intento de login
Auth::login($user);                 // Establecer login
Auth::logout();                     // Cerrar sesión
```

### 2. Modelo Usuario

Hereda de `AuthenticatableModel` y define:

- Atributos: `id`, `nombre`, `correo`, `clave`
- Sobrescritura de campos de autenticación (correo/clave)
- Método `setClave()` para almacenamiento seguro con hash
- Campo `clave` oculto para evitar exposición del hash

### 3. Interfaz y Trait Authenticatable

**Interfaz**: Define el contrato para modelos autenticables
```php
getAuthIdentifierName()    // Nombre del campo ID
getAuthIdentifier()        // Valor del ID
getAuthLoginName()         // Nombre del campo de login
getAuthLogin()             // Valor del campo de login
getAuthPasswordName()      // Nombre del campo de password
getAuthPassword()          // Hash de la contraseña
```

**Trait**: Proporciona implementación por defecto
- Valores predeterminados: `email` y `password`
- Lectura dinámica de atributos
- Reutilizable en cualquier modelo

### 4. Controlador de Autenticación

**Register**
1. Recibe datos (nombre, correo, clave)
2. Valida unicidad del correo
3. Crea usuario con contraseña hasheada
4. Autentica automáticamente
5. Redirige a zona privada

**Login**
1. Recibe credenciales
2. Delega verificación a `Auth::attempt()`
3. Regenera ID de sesión si es exitoso
4. Redirige según resultado

**Logout**
1. Llama a `Auth::logout()`
2. Invalida sesión completamente
3. Redirige al formulario de login

### 5. Validación de Datos

**RegisterRequest**
- Valida: nombre, correo, clave
- Reglas específicas para registro

**LoginRequest**
- Valida: correo, clave
- Reglas específicas para login

### 6. Middleware de Protección

`AuthMiddleware` verifica:
- Si existe usuario autenticado (`Auth::check()`)
- Redirige a login si no está autenticado
- Permite acceso si está autenticado

##  Seguridad

### Almacenamiento de Contraseñas
```php
// Hashing seguro al crear usuario
$usuario->setClave($plainPassword);  // Internamente usa password_hash()

// Verificación en login
password_verify($plain, $hash);
```

### Protección contra Session Fixation
```php
// Regenerar ID tras login exitoso
session()->regenerate();
```

### Invalidación de Sesión
```php
// Destrucción completa en logout
session()->invalidate();  // session_unset() + session_destroy()
```

### Campos Ocultos
```php
// Evitar exposición del hash
protected $hidden = ['clave'];
```

## 🚀 Uso

### Inicialización del Sistema
```php
// En bootstrap/bootstrap.php
Auth::init(Usuario::class);
```

### Proteger Rutas
```php
// En scripts públicos
(new AuthMiddleware())->handle();
```

### Acceder al Usuario Autenticado
```php
// En controladores o vistas
$usuario = Auth::user();
$esAutenticado = Auth::check();
$userId = Auth::id();
```

### Registro de Usuario
```php
POST /register
{
    "nombre": "Juan Pérez",
    "correo": "juan@example.com",
    "clave": "password123"
}
```

### Login
```php
POST /login
{
    "correo": "juan@example.com",
    "clave": "password123"
}
```

### Logout
```php
POST /logout
```

##  Tareas Completadas

- [x] Implementación de clases del núcleo (`App\Core`)
- [x] Modelo `Usuario` con integración de autenticación
- [x] Controlador `AuthController` con acciones completas
- [x] Requests de validación (`LoginRequest`, `RegisterRequest`)
- [x] Middleware `AuthMiddleware` para protección de rutas
- [x] Protección de rutas del recurso productos
- [x] Visualización del usuario en cabecera de vistas

## 🛡️ Rutas Protegidas

Todas las rutas de productos requieren autenticación **excepto**:
- `index` (listar productos)
- `show` (ver detalle de producto)

La ruta `logout` también requiere autenticación.

## 📝 Notas Importantes

### Sesión vs Autenticación
- **Sesión**: Identifica al navegador
- **Autenticación**: Identifica al usuario
- El login no crea la sesión, solo la vincula a un usuario

### Regeneración de Sesión
Se regenera el ID de sesión tras el login para evitar ataques de fijación de sesión (session fixation).

### Usuario en Memoria
El usuario se carga una vez al inicio de la petición y permanece en memoria, evitando consultas repetidas a la base de datos.

### Limpieza en Logout
Aunque la redirección finaliza la petición naturalmente, se elimina explícitamente el usuario de memoria por ortodoxia conceptual.

##  Conceptos Aprendidos

- Sistema de autenticación basado en sesiones
- Patrón de diseño para modelos autenticables
- Hashing seguro de contraseñas con `password_hash()`
- Verificación de contraseñas con `password_verify()`
- Protección contra session fixation
- Middlewares para protección de rutas
- Separación de responsabilidades (SoC)
- Inyección de dependencias
- Uso de interfaces y traits
- Gestión del ciclo de vida de la sesión

##  Tecnologías

- **Miniframework PHP personalizado** (arquitectura MVC)
- PHP 8.x
- Sesiones nativas de PHP
- Password Hashing API de PHP
- Patrón MVC
- Arquitectura de Middlewares
- OOP avanzado (Interfaces, Traits, Herencia)
- Autoload PSR-4

## Autor
Iker Martínez Velasco

---

**Curso**: 2025-2026  
**Práctica**: 6.3 - Autenticación Web
