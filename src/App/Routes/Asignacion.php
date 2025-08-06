<?php
use Slim\Routing\RouteCollectorProxy;

$app->group('/asignaciones', function(RouteCollectorProxy $group){
    
    // Crear asignación
    $group->post('/crear', 'App\Controllers\asignacion_controller:crearAsignacion');
    
    // Obtener asignaciones
    $group->get('/obtener/{docente_id}', 'App\Controllers\asignacion_controller:getAsignaciones');
    
    // Eliminar asignación
    $group->delete('/eliminar/{id_asignacion}', 'App\Controllers\asignacion_controller:deleteAsignacion');
    
    // Obtener aperturas con asignaciones
    $group->get('/aperturas', 'App\Controllers\asignacion_controller:getAsignacionesByApertura');
});