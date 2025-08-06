# ![Logo](https://axis.uninunez.edu.co/images/uninunez/vm/logotxtteal.svg)

# API de Resultados de Aprendizaje

Backend API para la gestión de cuestionarios y resultados de aprendizaje.

## Descripción

Este proyecto es una API RESTful desarrollada en Slim framework de PHP que permite la gestión completa de cuestionarios educativos, incluyendo:

- Autenticación de usuarios mediante JWT
- Creación y administración de cuestionarios
- Asignación de cuestionarios a estudiantes
- Resolución de cuestionarios
- Generación de resultados y estadísticas de aprendizaje

## Tecnologías

- PHP 7.4+
- Slim Framework 4.x (Microframework)
- JWT para autenticación
- PDO para la gestión de base de datos
- Composer para la gestión de dependencias

## Dependencias Principales

- `slim/slim`: ^4.x - Microframework para PHP
- `slim/psr7`: ^1.7 - Implementación de PSR-7 para Slim
- `php-di/php-di`: ^7.0 - Contenedor de inyección de dependencias
- `firebase/php-jwt`: ^6.11 - Librería para generación y verificación de tokens JWT
- `vlucas/phpdotenv`: ^5.6 - Carga de variables de entorno desde archivos .env

## Estructura del Proyecto

```
src/
├── App/
├── Controllers/
├── Models/
└── Legacy/

public/
```

## Controladores Principales

- `login_controller.php`: Gestión de autenticación y tokens JWT
- `crearCuestionario_controller.php`: Creación y administración de cuestionarios
- `asignacion_controller.php`: Asignación de cuestionarios a estudiantes
- `resolver_controller.php`: Resolución de cuestionarios por parte de estudiantes
- `resultado_controller.php`: Generación de resultados y estadísticas
- `estudiante_controller.php`: Gestión de estudiantes
- `periodo_controller.php`: Gestión de períodos académicos
- `apertura_controller.php`: Gestión de aperturas de cuestionarios
- `MenuCuestionario_controller.php`: Menú de navegación de cuestionarios

## Instalación

1. Clonar el repositorio:
   ```bash
   git clone [URL_DEL_REPOSITORIO]
   ```

2. Instalar dependencias:
   ```bash
   composer install
   ```

3. Configurar variables de entorno:
   ```bash
   cp .env.dis .env
   # Editar .env con los valores correspondientes
   ```

4. Configurar el servidor web para apuntar al directorio `public/`

## Configuración

Es necesario configurar las variables de entorno en el archivo `.env`:

```
DB_HOST=
DB_NAME=
DB_USER=
DB_PASS=
JWT_SECRET=
```

## Uso de la API

### Autenticación

La mayoría de endpoints requieren autenticación mediante token JWT:

```
Authorization: Bearer <token>
```

### Endpoints Principales

- `POST /login` - Autenticación de usuarios
- `POST /cuestionario/crear` - Crear nuevo cuestionario
- `GET /cuestionario/listar` - Listar cuestionarios
- `POST /asignacion/crear` - Asignar cuestionario a estudiante
- `POST /resolver/iniciar` - Iniciar resolución de cuestionario
- `GET /resultado/obtener` - Obtener resultados de intentos

## Base de Datos

El proyecto requiere una base de datos MySQL/MariaDB. El esquema de la base de datos se encuentra en el archivo `testv2.sql`.

## Contribución

1. Crear un fork del repositorio
2. Crear una rama para la nueva funcionalidad (`git checkout -b feature/nueva-funcionalidad`)
3. Realizar los cambios necesarios
4. Commit de los cambios (`git commit -am 'Añadir nueva funcionalidad'`)
5. Push a la rama (`git push origin feature/nueva-funcionalidad`)
6. Crear un nuevo Pull Request

## Licencia

Este proyecto es de uso privado, por lo que no se puede distribuir, todos los derechos reservados para la Universidad Rafael Nuñez.

## Contacto

Para más información, contactar con el equipo de desarrollo de la Universidad Rafael Nuñez.
