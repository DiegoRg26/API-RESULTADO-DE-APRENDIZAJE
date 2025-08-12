<?php
use Slim\Routing\RouteCollectorProxy;

$group->group('/estudiante', function(RouteCollectorProxy $subgroup){

    // Obtener estudiantes de un programa
    $subgroup->get('', 'App\Controllers\estudiante_controller:getEstudiantes');

    // Obtener informacion de un estudiante
    $subgroup->get('/{id}', 'App\Controllers\estudiante_controller:getEstInfo');
    
    // Agregar estudiante
    $subgroup->post('/agregar', 'App\Controllers\estudiante_controller:agregarEstudiante');
    
    // Deshabilitar estudiante
    $subgroup->put('/deshabilitar', 'App\Controllers\estudiante_controller:deshabilitarEstudiante');
    
    // Habilitar estudiante
    $subgroup->put('/habilitar', 'App\Controllers\estudiante_controller:habilitarEstudiante');
    
});