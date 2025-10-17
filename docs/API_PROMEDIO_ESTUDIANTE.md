# API de Cálculo de Promedio de Estudiante

## Descripción
Este endpoint calcula el promedio general de un estudiante y lo compara con el promedio de todos los estudiantes del programa. Proporciona estadísticas detalladas, posición en el ranking y análisis de rendimiento relativo.

## Endpoint

**POST** `/api/informes/promedio-estudiante`

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
| `periodo_id` | integer | No | ID del periodo académico (opcional, si no se proporciona calcula sobre todos los periodos) |

## Respuestas

### Caso 1: Cálculo exitoso

**Status:** `200 OK`

```json
{
	"success": true,
	"message": "Promedio calculado exitosamente",
	"data": {
		"estudiante": {
			"estudiante_id": 1,
			"nombre": "Juan Pérez",
			"identificacion": "1234567890",
			"promedio": 85.50,
			"puntaje_minimo": 70.00,
			"puntaje_maximo": 95.00,
			"total_cuestionarios_completados": 5
		},
		"programa": {
			"programa_id": 14,
			"promedio_general": 78.25,
			"puntaje_minimo": 45.00,
			"puntaje_maximo": 98.00,
			"desviacion_estandar": 12.45,
			"total_estudiantes": 45
		},
		"comparacion": {
			"diferencia": 7.25,
			"diferencia_porcentual": 9.27,
			"posicion": 8,
			"total_estudiantes": 45,
			"percentil": 84.44,
			"rendimiento": "superior"
		}
	}
}
```

### Caso 2: Estudiante sin cuestionarios completados

**Status:** `200 OK`

```json
{
	"success": true,
	"message": "El estudiante no tiene cuestionarios completados en este programa",
	"data": {
		"estudiante": {
			"estudiante_id": 1,
			"promedio": 0,
			"total_cuestionarios": 0
		},
		"programa": {
			"promedio_general": 0,
			"total_estudiantes": 0
		},
		"comparacion": {
			"diferencia": 0,
			"posicion": null,
			"percentil": 0
		}
	}
}
```

### Caso 3: Error de validación

**Status:** `400 Bad Request`

```json
{
	"success": false,
	"message": "Faltan campos requeridos: estudiante_id, programa_id"
}
```

### Caso 4: Usuario no autenticado

**Status:** `401 Unauthorized`

```json
{
	"success": false,
	"message": "Usuario no autenticado"
}
```

### Caso 5: Error del servidor

**Status:** `500 Internal Server Error`

```json
{
	"success": false,
	"message": "Error al calcular promedio: [detalle del error]"
}
```

## Descripción de Campos de Respuesta

### Sección `estudiante`

| Campo | Tipo | Descripción |
|-------|------|-------------|
| `estudiante_id` | integer | ID del estudiante |
| `nombre` | string | Nombre completo del estudiante |
| `identificacion` | string | Número de identificación del estudiante |
| `promedio` | float | Promedio general del estudiante (redondeado a 2 decimales) |
| `puntaje_minimo` | float | Puntaje más bajo obtenido por el estudiante |
| `puntaje_maximo` | float | Puntaje más alto obtenido por el estudiante |
| `total_cuestionarios_completados` | integer | Cantidad de cuestionarios completados |

### Sección `programa`

| Campo | Tipo | Descripción |
|-------|------|-------------|
| `programa_id` | integer | ID del programa académico |
| `promedio_general` | float | Promedio general de todos los estudiantes del programa |
| `puntaje_minimo` | float | Puntaje más bajo en el programa |
| `puntaje_maximo` | float | Puntaje más alto en el programa |
| `desviacion_estandar` | float | Desviación estándar de los puntajes del programa |
| `total_estudiantes` | integer | Cantidad total de estudiantes en el programa |

### Sección `comparacion`

| Campo | Tipo | Descripción |
|-------|------|-------------|
| `diferencia` | float | Diferencia entre el promedio del estudiante y el promedio del programa (positivo = superior, negativo = inferior) |
| `diferencia_porcentual` | float | Diferencia expresada en porcentaje |
| `posicion` | integer | Posición del estudiante en el ranking del programa (1 = mejor) |
| `total_estudiantes` | integer | Total de estudiantes en el ranking |
| `percentil` | float | Percentil del estudiante (0-100, donde 100 es el mejor) |
| `rendimiento` | string | Clasificación del rendimiento: `sobresaliente`, `superior`, `promedio`, `inferior`, `bajo` |

### Clasificación de Rendimiento

- **sobresaliente**: Diferencia > 10 puntos por encima del promedio
- **superior**: Diferencia > 0 y ≤ 10 puntos por encima del promedio
- **promedio**: Diferencia = 0
- **inferior**: Diferencia < 0 y ≥ -10 puntos por debajo del promedio
- **bajo**: Diferencia < -10 puntos por debajo del promedio

## Lógica de Cálculo

1. **Promedio del estudiante**: Calcula el promedio de todos los cuestionarios completados por el estudiante en el programa especificado.

2. **Promedio del programa**: Calcula el promedio de todos los cuestionarios completados por todos los estudiantes del programa.

3. **Ranking**: Ordena a todos los estudiantes por su promedio de mayor a menor y determina la posición del estudiante.

4. **Percentil**: Calcula en qué percentil se encuentra el estudiante respecto al total de estudiantes.

5. **Desviación estándar**: Mide la dispersión de los puntajes en el programa.

## Uso en el Frontend

### Ejemplo con Fetch API

```javascript
async function calcularPromedioEstudiante(estudianteId, programaId, periodoId = null) {
	try {
		const body = {
			estudiante_id: estudianteId,
			programa_id: programaId
		};
		
		if (periodoId) {
			body.periodo_id = periodoId;
		}

		const response = await fetch('http://localhost/api/informes/promedio-estudiante', {
			method: 'POST',
			headers: {
				'Content-Type': 'application/json',
				'Authorization': `Bearer ${token}`
			},
			body: JSON.stringify(body)
		});

		const data = await response.json();

		if (data.success) {
			const { estudiante, programa, comparacion } = data.data;
			
			console.log(`Promedio del estudiante: ${estudiante.promedio}`);
			console.log(`Promedio del programa: ${programa.promedio_general}`);
			console.log(`Posición: ${comparacion.posicion} de ${comparacion.total_estudiantes}`);
			console.log(`Rendimiento: ${comparacion.rendimiento}`);
			
			return data.data;
		}
	} catch (error) {
		console.error('Error al calcular promedio:', error);
	}
}
```

### Ejemplo con Axios

```javascript
import axios from 'axios';

async function obtenerEstadisticasEstudiante(estudianteId, programaId, periodoId = null) {
	try {
		const payload = {
			estudiante_id: estudianteId,
			programa_id: programaId
		};
		
		if (periodoId) {
			payload.periodo_id = periodoId;
		}

		const { data } = await axios.post(
			'/api/informes/promedio-estudiante',
			payload,
			{
				headers: {
					'Authorization': `Bearer ${token}`
				}
			}
		);

		return {
			promedioEstudiante: data.data.estudiante.promedio,
			promedioPrograma: data.data.programa.promedio_general,
			posicion: data.data.comparacion.posicion,
			percentil: data.data.comparacion.percentil,
			rendimiento: data.data.comparacion.rendimiento,
			estadisticas: data.data
		};
	} catch (error) {
		console.error('Error:', error);
		throw error;
	}
}
```

### Ejemplo de Visualización en React

```jsx
import React, { useState, useEffect } from 'react';

function EstadisticasEstudiante({ estudianteId, programaId, periodoId }) {
	const [stats, setStats] = useState(null);
	const [loading, setLoading] = useState(true);

	useEffect(() => {
		async function cargarEstadisticas() {
			try {
				const data = await calcularPromedioEstudiante(estudianteId, programaId, periodoId);
				setStats(data);
			} catch (error) {
				console.error(error);
			} finally {
				setLoading(false);
			}
		}
		cargarEstadisticas();
	}, [estudianteId, programaId, periodoId]);

	if (loading) return <div>Cargando...</div>;
	if (!stats) return <div>No hay datos disponibles</div>;

	const { estudiante, programa, comparacion } = stats;

	return (
		<div className="estadisticas-container">
			<h2>Estadísticas de {estudiante.nombre}</h2>
			
			<div className="card">
				<h3>Tu Promedio</h3>
				<p className="promedio-grande">{estudiante.promedio}</p>
				<p>Rango: {estudiante.puntaje_minimo} - {estudiante.puntaje_maximo}</p>
			</div>

			<div className="card">
				<h3>Promedio del Programa</h3>
				<p className="promedio-grande">{programa.promedio_general}</p>
				<p>{programa.total_estudiantes} estudiantes</p>
			</div>

			<div className="card">
				<h3>Tu Posición</h3>
				<p className="posicion">
					#{comparacion.posicion} de {comparacion.total_estudiantes}
				</p>
				<p>Percentil: {comparacion.percentil}%</p>
				<p className={`rendimiento ${comparacion.rendimiento}`}>
					Rendimiento: {comparacion.rendimiento}
				</p>
			</div>

			<div className="card">
				<h3>Comparación</h3>
				<p>
					{comparacion.diferencia > 0 ? '+' : ''}{comparacion.diferencia} puntos
					({comparacion.diferencia_porcentual}%)
					{comparacion.diferencia > 0 ? ' por encima' : ' por debajo'} del promedio
				</p>
			</div>
		</div>
	);
}
```

## Casos de Uso

### 1. Dashboard del Estudiante
Mostrar al estudiante su rendimiento comparado con sus compañeros de programa.

### 2. Informe de Progreso
Generar informes periódicos que muestren la evolución del estudiante respecto al grupo.

### 3. Alertas de Rendimiento
Identificar estudiantes con rendimiento bajo para intervenciones tempranas.

### 4. Análisis de Cohorte
Comparar el rendimiento de diferentes cohortes o periodos.

### 5. Gamificación
Mostrar rankings y posiciones para motivar a los estudiantes.

## Notas Técnicas

- Solo se consideran cuestionarios con `intento_cuestionario.completado = 1`
- El cálculo se basa en el campo `puntaje_total` de la tabla `intento_cuestionario`
- Si no se proporciona `periodo_id`, se calculan estadísticas sobre todos los periodos
- El percentil se calcula como: `((total_estudiantes - posicion + 1) / total_estudiantes) * 100`
- La desviación estándar ayuda a entender la dispersión de los puntajes en el programa
- Todos los valores numéricos se redondean a 2 decimales para mejor legibilidad

## Consideraciones de Rendimiento

- El endpoint realiza 3 consultas a la base de datos
- Para programas con muchos estudiantes (>1000), considerar implementar caché
- El cálculo del ranking puede ser costoso; considerar pre-calcular rankings periódicamente
