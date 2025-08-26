<?php
use Slim\Routing\RouteCollectorProxy;

// Cargar el middleware manualmente
require_once __DIR__ . '/../Middleware/middlewareAuth.php';
use App\Middleware\middlewareAuth;

$group->group('/asignacion', function(RouteCollectorProxy $subgroup){

    // Crear asignación
    $subgroup->post('/crear', 'App\Controllers\asignacion_controller:crearAsignacion');
    
    // Obtener asignaciones
    $subgroup->get('/obtener/{docente_id}', 'App\Controllers\asignacion_controller:getAsignaciones');
    
    // Eliminar asignación
    $subgroup->delete('/eliminar/{id_asignacion}', 'App\Controllers\asignacion_controller:deleteAsignacion');
    
    // Obtener aperturas con asignaciones
    $subgroup->get('/aperturas', 'App\Controllers\asignacion_controller:getAsignacionesByApertura');
    
})->add(new middlewareAuth());