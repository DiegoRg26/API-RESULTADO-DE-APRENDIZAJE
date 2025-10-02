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

- `login_controller.php`: Gestión de autenticación y tokens JWT para docentes/administradores
- `estudiantes_login_controller.php`: Gestión de autenticación específica para estudiantes con JWT
- `crearCuestionario_controller.php`: Creación y administración de cuestionarios
- `asignacion_controller.php`: Asignación de cuestionarios a estudiantes
- `resolver_controller.php`: Resolución de cuestionarios por parte de estudiantes
- `resultado_controller.php`: Generación de resultados y estadísticas
- `estudiante_controller.php`: Gestión de estudiantes
- `periodo_controller.php`: Gestión de períodos académicos
- `apertura_controller.php`: Gestión de aperturas de cuestionarios
- `MenuCuestionario_controller.php`: Menú de navegación de cuestionarios
- `seguimiento_controller.php`: Seguimiento de progreso de estudiantes
- `registro_controller.php`: Registro de nuevos usuarios
- `raNiveles_controller.php`: Gestión de niveles de resultados de aprendizaje (RA)
- `AutoProcessController.php`: Procesamiento automático de períodos expirados y creación de intentos faltantes
- `genInformes_controller.php`: Generación de informes y estadísticas avanzadas (en desarrollo)

## Funcionalidades Adicionales No Documentadas

### Procesos Automáticos (AutoProcess)
- **Procesamiento de períodos expirados**: Sistema automático que identifica períodos académicos finalizados y genera intentos faltantes para estudiantes
- **Creación automática de intentos**: Cuando un período expira, el sistema crea automáticamente los intentos de cuestionarios que los estudiantes no completaron

### Sistema de Generación de Informes (GenInformes)
- **Módulo en desarrollo** para generación de informes avanzados y estadísticas detalladas
- **Módulo preparado para futuras funcionalidades de reporting**

### Características de Seguridad No Documentadas

#### Sistema Avanzado de Gestión de Sesiones
- **Múltiples sesiones por estudiante**: Los estudiantes pueden mantener múltiples sesiones activas simultáneamente desde diferentes dispositivos
- **Seguimiento detallado de sesiones**: Registro completo de metadatos de sesión incluyendo:
  - Dirección IP del cliente (`ip_address`)
  - User-Agent del navegador (`user_agent`)
  - Timestamps de creación y última actividad (`fecha_creacion`, `fecha_ultima_actividad`)
  - Token único de sesión (`session_token`) y JTI del JWT (`jwt_jti`)
- **Control de sesiones concurrentes**: Límites automáticos en el número de sesiones activas por estudiante
- **Renovación automática de sesiones**: Sistema inteligente que actualiza automáticamente las sesiones basándose en la actividad reciente del usuario
- **Invalidación automática**: Sesiones inactivas se marcan automáticamente como inactivas después de períodos de inactividad

#### Características de Seguridad Adicionales
- **Validación estricta de tokens**: Verificación completa de estructura y validez de tokens JWT
- **Gestión de ciclo de vida de sesiones**: Control automático del ciclo completo de vida de las sesiones
- **Auditoría de acceso**: Registro detallado de todas las actividades de autenticación y acceso

### Sistema de Progreso de Cuestionarios
- **Seguimiento detallado**: Registro completo del progreso de cada estudiante en cada cuestionario
- **Tiempo de respuesta**: Control preciso del tiempo utilizado por pregunta y total
- **Progreso guardado**: Los estudiantes pueden pausar y continuar cuestionarios sin perder progreso
- **Recuperación automática**: En caso de cierre inesperado del navegador, el progreso se mantiene

### Características Avanzadas de Preguntas
- **Soporte de imágenes**: Las preguntas y opciones pueden incluir imágenes en formato base64
- **Orientación configurable**: Cada pregunta puede tener orientación específica (1-4)
- **Sistema de peso**: Cada pregunta tiene un peso específico que afecta la calificación final
- **Tiempo límite por cuestionario**: Control granular del tiempo disponible para cada cuestionario

### Entidades del Sistema No Documentadas
- **Campus**: Gestión de múltiples campus universitarios con asignación específica
- **Niveles educativos**: Sistema jerárquico de niveles académicos (pregrado, posgrado, etc.)
- **Relación programa-estudiante**: Asignación específica de estudiantes a programas académicos
- **Sesiones avanzadas**: Tabla dedicada para gestión robusta de sesiones JWT con metadatos

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

### Endpoints

#### Auth (Docentes/Administradores)

- `POST /auth` - Autenticación de usuarios
  - Body JSON:
    ```json
    {
		"email": "string",
		"password": "string"
    }
    ```
  
- `GET /auth/verify` - Verificar token JWT
  - Headers: `Authorization: Bearer <token>`

- `POST /auth/refresh` - Refrescar token JWT
  - Headers: `Authorization: Bearer <token>`

- `GET /auth/me` - Obtener información del usuario autenticado
  - Headers: `Authorization: Bearer <token>`

#### Auth Estudiantes

- `POST /auth/estudiantes` - Autenticación de estudiantes
  - Body JSON:
    ```json
    {
      "email": "correo@institucion.edu.co",
      "identificacion": "1234567890"
    }
    ```
  - Retorna token JWT para el estudiante autenticado

- `GET /auth/estudiantes/verify` - Verificar token JWT de estudiante
  - Headers: `Authorization: Bearer <token>`

- `POST /auth/estudiantes/refresh` - Refrescar token JWT de estudiante
  - Headers: `Authorization: Bearer <token>`

- `GET /auth/estudiantes/me` - Obtener información del estudiante autenticado
  - Headers: `Authorization: Bearer <token>`

#### Registro de Usuarios

- `POST /auth/register` - Registro de nuevo usuario (docente/administrador)

#### Cuestionario

- `GET /cuestionario` - Obtener cuestionarios creados por el usuario
  - Headers: `Authorization: Bearer <token>`

- `GET /cuestionario/abiertos` - Obtener cuestionarios abiertos con estado
  - Headers: `Authorization: Bearer <token>`

- `POST /cuestionario/crear` - Crear cuestionario
  - Headers: `Authorization: Bearer <token>`
  - Body JSON: `{ "titulo": string, "descripcion": string, "tiempo_limite": number, "programa_id": number }`
  
- `POST /cuestionario/{id}/anexar-preguntas` - Anexar preguntas a cuestionario
  - Path: `cuestionario_id` (number)
  - Body JSON (estructura):
 
    ```json
    {
		"preguntas": [
			{
				"texto_pregunta": "¿Capital de Francia?",
				"peso": 1,
				"orientacion": 1,
				"imagen_base64": "data:image/jpeg;base64,....",
				"nombre_imagen_pregunta": "pregunta_1.jpg",
				"correcta": 1,
				"opciones": [
					{ "texto": "Madrid" },
					{ "texto": "París", "imagen_base64": "data:image/png;base64,....", "nombre_imagen_opcion": "op2.png" },
					{ "texto": "Roma" }
				]
			}
		]
    }
    ```
  - Content-Type: `application/json`

- `GET /cuestionario/{cuestionario_id}/preguntas-opciones` - Obtener preguntas y opciones de un cuestionario
  - Path: `cuestionario_id` (number)

- `POST /cuestionario/{cuestionario_id}/guardar-intento` - Guardar intento de resolver cuestionario
  - Path: `cuestionario_id` (number)
  - Body JSON:
    ```json
    {
		"estudiante_id": 1,
		"respuestas": [{ "pregunta_id": 12, "opcion_id": 3 }],
		"tiempo_utilizado": 003500
    }
    ```
    - Nota: `estudiante_id` es temporal para testing; en producción se usará el token JWT

- `GET /cuestionario/{cuestionario_id}` - Obtener cuestionario específico por ID
  - Path: `cuestionario_id` (number)
  - Headers: `Authorization: Bearer <token>`

#### Administración de Cuestionarios (Solo Administradores)

- `GET /cuestionario/admin` - Obtener todos los cuestionarios creados por todos los docentes
  - Headers: `Authorization: Bearer <token>`
  - Descripción: Endpoint administrativo que obtiene todos los cuestionarios del sistema, incluyendo información del docente creador y programa asociado

- `GET /cuestionario/admin/abiertos` - Obtener todos los cuestionarios abiertos del sistema
  - Headers: `Authorization: Bearer <token>`
  - Descripción: Endpoint administrativo que obtiene todos los cuestionarios abiertos con sus aperturas activas, incluyendo información del período, docente creador y programa asociado

#### Asignación

- `POST /asignacion/crear` - Crear asignación
  - Headers: `Authorization: Bearer <token>`
  - Body JSON:
    ```json
    {
		"id_apertura": 123,
		"id_estudiante": [1, 2, 3]
    }
    ```
  - Notas: `id_apertura` numérico > 0. `id_estudiante` debe ser un array no vacío de IDs numéricos.

- `GET /asignacion/obtener/{docente_id}` - Obtener asignaciones
  - Headers: `Authorization: Bearer <token>`
  - Path: `docente_id` (number)

- `DELETE /asignacion/eliminar/{id_asignacion}` - Eliminar asignación
  - Headers: `Authorization: Bearer <token>`
  - Path: `id_asignacion` (number)

- `GET /asignacion/aperturas` - Obtener aperturas con asignaciones
  - Headers: `Authorization: Bearer <token>`

#### Estudiante

- `GET /estudiante` - Obtener estudiantes de un programa
  - Headers: `Authorization: Bearer <token>`

- `GET /estudiante/{estudiante_id}` - Obtener información de un estudiante
  - Path: `estudiante_id` (number)

- `POST /estudiante/agregar` - Agregar estudiante
  - Headers: `Authorization: Bearer <token>`
  - Body JSON:
    ```json
    {
		"nombre": "Juan Pérez",
		"email": "juan@example.com",
		"identificacion": "123456789",
		"programa_id": 10
    }
    ```
  - Notas: Si el token contiene `programa_id`, no es necesario enviarlo en el body; de lo contrario es requerido.

- `PUT /estudiante/deshabilitar` - Deshabilitar estudiante
  - Body JSON:
    ```json
    {
		"estudiante_id": 25,
		"identificacion": "123456789"
    }
    ```

- `PUT /estudiante/habilitar` - Habilitar estudiante
  - Body JSON:
    ```json
    {
		"estudiante_id": 25,
		"identificacion": "123456789"
    }
    ```

- `POST /estudiante/login` - Login de estudiante
  - Body JSON:
    ```json
    {
		"email": "estudiante@example.com",
		"identificacion": "123456789"
    }
    ```

- `POST /estudiante/logout` - Logout de estudiante
  - Headers: `Authorization: Bearer <token>` (opcional)

- `POST /estudiante/verify` - Verificar token de estudiante
  - Headers: `Authorization: Bearer <token>`

- `GET /estudiante/cuestionarios` - Obtener cuestionarios asignados al estudiante autenticado
  - Headers: `Authorization: Bearer <token>`

- `GET /estudiante/cuestionarios/completados` - Obtener cuestionarios completados por el estudiante
  - Headers: `Authorization: Bearer <token>`

- `GET /estudiante/cuestionarios/programados` - Obtener cuestionarios programados (aún no inician)
  - Headers: `Authorization: Bearer <token>`

- `GET /estudiante/cuestionarios/expirados` - Obtener cuestionarios expirados sin intentos registrados
  - Headers: `Authorization: Bearer <token>`

#### Periodo

- `GET /periodo` - Listar periodos activos
  - Headers: `Authorization: Bearer <token>` (según configuración de seguridad)

- `GET /periodo/inactive` - Listar periodos inactivos
  - Headers: `Authorization: Bearer <token>` (según configuración de seguridad)

- `POST /periodo/create` - Crear nuevo periodo
  - Headers: `Content-Type: application/json`
  - Body JSON:
    ```json
	{
		"nombre": "Periodo 2025-1",
		"fecha_inicio": "2025-01-01",
		"fecha_fin": "2025-06-30"
	}
    ```
  - Notas: `fecha_inicio` y `fecha_fin` en formato `YYYY-MM-DD`. La fecha de inicio no puede ser mayor a la de fin.

- `GET /periodo/{periodo_id}` - Obtener periodo específico por ID
  - Path: `periodo_id` (number)

- `DELETE /periodo/{periodo_id}` - Desactivar periodo
  - Path: `periodo_id` (number)
  - Notas: No permite desactivar si existen aperturas activas asociadas al periodo.

- `PUT /periodo/{periodo_id}/activate` - Reactivar periodo
  - Path: `periodo_id` (number)

#### Aperturas

- `GET /aperturas/cuestionarios-disponibles` - Obtener cuestionarios disponibles para apertura
  - Headers: `Authorization: Bearer <token>`

- `GET /aperturas/periodos-activos` - Obtener periodos activos para asignar
  - Headers: `Authorization: Bearer <token>`

- `GET /aperturas` - Obtener aperturas activas del usuario
  - Headers: `Authorization: Bearer <token>`

- `POST /aperturas/crear` - Crear nueva apertura
  - Headers: `Authorization: Bearer <token>`
  - Body JSON:
    ```json
	{
		"cuestionario_id": 123,
		"periodo_id": 45
	}
    ```
  - Notas: `cuestionario_id` corresponde al ID de `relacion_cuestionario_programa` del docente. No se permite crear una apertura si ya existe una activa para ese cuestionario.

- `DELETE /aperturas/{apertura_id}` - Desactivar apertura
  - Headers: `Authorization: Bearer <token>`
  - Path: `apertura_id` (number)

#### Programas

- `GET /programas` - Obtener programas
  - Headers: `Authorization: Bearer <token>`
  - Notas: Si el token incluye `programa_id`, se retornará solo ese programa; de lo contrario, se listarán todos.

- `GET /programas/{programa_id}` - Obtener programa específico por ID
  - Headers: `Authorization: Bearer <token>`
  - Path: `programa_id` (number)

#### Seguimiento

- `GET /seguimiento/info/{apertura_id}` - Obtener información de un cuestionario
  - Headers: `Authorization: Bearer <token>`
  - Path: `apertura_id` (number) - ID de apertura

- `GET /seguimiento/estudiantes/{apertura_id}` - Obtener estudiantes de un cuestionario
  - Headers: `Authorization: Bearer <token>`
  - Path: `apertura_id` (number) - ID de apertura

- `GET /seguimiento/detalle` - Obtener detalle de un cuestionario
  - Headers: `Authorization: Bearer <token>`

- `GET /seguimiento/allquiz` - Obtener todos los cuestionarios del docente con detalles
  - Headers: `Authorization: Bearer <token>`

#### Ver

- `GET /ver/{cuestionario_id}` - Obtener información de un cuestionario (detalles y preguntas)
  - Path: `cuestionario_id` (number) - ID del cuestionario

- `POST /ver/intento` - Obtener el intento más reciente de un estudiante para un cuestionario
  - Headers: `Content-Type: application/json`
  - Body JSON:
    ```json
	{
		"estudiante_id": 456,
		"cuestionario_id": 123
	}
    ```

- `GET /ver/respuestas/{intento_id}` - Obtener respuestas de un estudiante para un cuestionario
  - Path: `intento_id` (number) - ID de intento

#### Resultados de Aprendizaje (RA)

- `GET /cuestionario/ra/{cuestionario_id}/get` - Obtener niveles de resultados de aprendizaje de un cuestionario
  - Headers: `Authorization: Bearer <token>`
  - Path: `cuestionario_id` (number) - ID del cuestionario

- `PUT /cuestionario/update/nivel` - Actualizar niveles de resultados de aprendizaje para un cuestionario
  - Headers: `Authorization: Bearer <token>` y `Content-Type: application/json`
  - Body JSON:
    ```json
    {
      "cuestionario_id": 123,
      "abreviatura": "RA-01",
      "descripcion": "Descripción actualizada del resultado de aprendizaje",
      "niveles": [
        {
          "puntaje_min": 0,
          "puntaje_max": 20,
          "indicadores": "Indicadores actualizados para este nivel",
          "nivel": 0
        },
        {
          "puntaje_min": 21,
          "puntaje_max": 40,
          "indicadores": "Indicadores actualizados para este nivel",
          "nivel": 1
        }
      ]
    }
    ```
- `POST /cuestionario/create/nivel` - Crear niveles de resultados de aprendizaje para un cuestionario
  - Headers: `Authorization: Bearer <token>` y `Content-Type: application/json`
  - Body JSON:
    ```json
    {
      "cuestionario_id": 123,
      "abreviatura": "RA-01",
      "descripcion": "Descripción del resultado de aprendizaje",
      "niveles": [
        {
          "puntaje_min": 0,
          "puntaje_max": 20,
          "indicadores": "Indicadores para este nivel"
        },
        {
          "puntaje_min": 21,
          "puntaje_max": 40,
          "indicadores": "Indicadores para este nivel"
        }
      ]
    }
    ```

- `GET /autoprocess/expired-periods` - Procesar períodos expirados y crear intentos faltantes
  - Headers: `Authorization: Bearer <token>`
  - Descripción: Sistema automático que identifica períodos académicos finalizados y genera intentos faltantes para estudiantes

#### Informes

- `GET /informes/*` - Sistema de generación de informes (en desarrollo)
  - Headers: `Authorization: Bearer <token>`
  - Descripción: Módulo preparado para futuras funcionalidades de reporting y estadísticas avanzadas

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
