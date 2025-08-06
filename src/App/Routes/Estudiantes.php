<?php
use Slim\Routing\RouteCollectorProxy;

$app->group('/estudiantes', function(RouteCollectorProxy $group){

    // Obtener estudiantes de un programa
    $group->get('/', 'App\Controllers\estudiante_controller:getEstudiantes');
    
    // Agregar estudiante
    $group->post('/agregar', 'App\Controllers\estudiante_controller:agregarEstudiante');
    
    // Deshabilitar estudiante
    $group->put('/deshabilitar', 'App\Controllers\estudiante_controller:deshabilitarEstudiante');
    
    // Habilitar estudiante
    $group->put('/habilitar', 'App\Controllers\estudiante_controller:habilitarEstudiante');
});