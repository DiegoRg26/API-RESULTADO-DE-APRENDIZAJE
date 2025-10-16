# API de Validación de Cuestionarios Completados

## Descripción
Este endpoint valida si un estudiante ha completado todos los cuestionarios asignados para un programa y periodo específico antes de generar el informe de resultados de aprendizaje.

## Endpoint

**POST** `/api/informes/validar-cuestionarios`

## Autenticación
Requiere token JWT válido en el header `Authorization: Bearer {token}`

## Request Body

```json
{
	"estudiante_id": 1,
	"programa_id": 14,
	"periodo_id": 2
}
```

### Parámetros

| Campo | Tipo | Requerido | Descripción |
|-------|------|-----------|-------------|
| `estudiante_id` | integer | Sí | ID del estudiante |
| `programa_id` | integer | Sí | ID del programa académico |
| `periodo_id` | integer | Sí | ID del periodo académico |

## Respuestas

### Caso 1: Todos los cuestionarios completados

**Status:** `200 OK`

```json
{
	"success": true,
	"message": "El estudiante ha completado todos los cuestionarios. Puede generar el informe.",
	"data": {
		"puede_generar_informe": true,
		"total_cuestionarios": 5,
		"completados": 5,
		"pendientes": 0,
		"cuestionarios_pendientes": [],
		"cuestionarios_completados": [
			{
				"cuestionario_id": 1,
				"titulo": "Cuestionario de Matemáticas",
				"descripcion": "Evaluación de competencias matemáticas",
				"fecha_completado": "2025-10-15 14:30:00",
				"puntaje_total": "85.50"
			},
			{
				"cuestionario_id": 2,
				"titulo": "Cuestionario de Física",
				"descripcion": "Evaluación de competencias en física",
				"fecha_completado": "2025-10-14 10:20:00",
				"puntaje_total": "92.00"
			}
		]
	}
}
```

### Caso 2: Cuestionarios pendientes

**Status:** `200 OK`

```json
{
	"success": true,
	"message": "El estudiante tiene cuestionarios pendientes. No puede generar el informe hasta completarlos.",
	"data": {
		"puede_generar_informe": false,
		"total_cuestionarios": 5,
		"completados": 3,
		"pendientes": 2,
		"cuestionarios_pendientes": [
			{
				"cuestionario_id": 3,
				"titulo": "Cuestionario de Química",
				"descripcion": "Evaluación de competencias en química",
				"apertura_id": 15
			},
			{
				"cuestionario_id": 4,
				"titulo": "Cuestionario de Biología",
				"descripcion": "Evaluación de competencias en biología",
				"apertura_id": 16
			}
		],
		"cuestionarios_completados": [
			{
				"cuestionario_id": 1,
				"titulo": "Cuestionario de Matemáticas",
				"descripcion": "Evaluación de competencias matemáticas",
				"fecha_completado": "2025-10-15 14:30:00",
				"puntaje_total": "85.50"
			}
		]
	}
}
```

### Caso 3: Sin cuestionarios asignados

**Status:** `200 OK`

```json
{
	"success": true,
	"message": "No hay cuestionarios asignados para este estudiante en el programa y periodo especificados",
	"data": {
		"puede_generar_informe": false,
		"total_cuestionarios": 0,
		"completados": 0,
		"pendientes": 0,
		"cuestionarios_pendientes": [],
		"cuestionarios_completados": []
	}
}
```

### Caso 4: Error de validación

**Status:** `400 Bad Request`

```json
{
	"success": false,
	"message": "Faltan campos requeridos: estudiante_id, programa_id, periodo_id"
}
```

### Caso 5: Usuario no autenticado

**Status:** `401 Unauthorized`

```json
{
	"success": false,
	"message": "Usuario no autenticado"
}
```

### Caso 6: Error del servidor

**Status:** `500 Internal Server Error`

```json
{
	"success": false,
	"message": "Error al validar cuestionarios: [detalle del error]"
}
```

## Lógica de Validación

El endpoint realiza las siguientes validaciones:

1. **Obtiene cuestionarios asignados**: Consulta todos los cuestionarios que han sido asignados al estudiante para el programa y periodo específico a través de la tabla `asignacion`.

2. **Obtiene cuestionarios completados**: Consulta todos los cuestionarios que el estudiante ha completado (campo `completado = 1` en `intento_cuestionario`).

3. **Compara y determina pendientes**: Compara ambas listas para identificar qué cuestionarios faltan por completar.

4. **Retorna resultado**: Devuelve un objeto con:
   - `puede_generar_informe`: `true` si todos están completados, `false` si hay pendientes
   - Detalles de cuestionarios completados y pendientes
   - Contadores de totales

## Uso en el Frontend

### Ejemplo con Fetch API

```javascript
async function validarCuestionarios(estudianteId, programaId, periodoId) {
	try {
		const response = await fetch('http://localhost/api/informes/validar-cuestionarios', {
			method: 'POST',
			headers: {
				'Content-Type': 'application/json',
				'Authorization': `Bearer ${token}`
			},
			body: JSON.stringify({
				estudiante_id: estudianteId,
				programa_id: programaId,
				periodo_id: periodoId
			})
		});

		const data = await response.json();

		if (data.success && data.data.puede_generar_informe) {
			// Permitir generar el informe
			console.log('Puede generar el informe');
			generarInforme();
		} else {
			// Mostrar mensaje con cuestionarios pendientes
			console.log('Cuestionarios pendientes:', data.data.cuestionarios_pendientes);
			mostrarAlertaCuestionariosPendientes(data.data.cuestionarios_pendientes);
		}
	} catch (error) {
		console.error('Error al validar cuestionarios:', error);
	}
}
```

### Ejemplo con Axios

```javascript
import axios from 'axios';

async function validarCuestionarios(estudianteId, programaId, periodoId) {
	try {
		const { data } = await axios.post(
			'/api/informes/validar-cuestionarios',
			{
				estudiante_id: estudianteId,
				programa_id: programaId,
				periodo_id: periodoId
			},
			{
				headers: {
					'Authorization': `Bearer ${token}`
				}
			}
		);

		if (data.data.puede_generar_informe) {
			// Habilitar botón de generar informe
			return true;
		} else {
			// Mostrar cuestionarios pendientes
			return {
				pendientes: data.data.cuestionarios_pendientes,
				mensaje: data.message
			};
		}
	} catch (error) {
		console.error('Error:', error);
		throw error;
	}
}
```

## Flujo Recomendado

1. **Antes de mostrar el botón de generar informe**: Llamar a este endpoint para verificar si el estudiante puede generar el informe.

2. **Si `puede_generar_informe = true`**: Habilitar el botón de generar informe.

3. **Si `puede_generar_informe = false`**: 
   - Deshabilitar el botón de generar informe
   - Mostrar un mensaje indicando los cuestionarios pendientes
   - Opcionalmente, mostrar enlaces directos a los cuestionarios pendientes usando el `apertura_id`

4. **Actualización en tiempo real**: Después de que el estudiante complete un cuestionario, volver a llamar este endpoint para actualizar el estado.

## Notas Técnicas

- El endpoint solo considera cuestionarios con `apertura.activo = 1`
- Un cuestionario se considera completado cuando `intento_cuestionario.completado = 1`
- La validación se hace por programa y periodo específico
- El endpoint es independiente y no modifica ningún dato, solo consulta
