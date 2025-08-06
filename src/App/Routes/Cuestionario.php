<?php
use Slim\Routing\RouteCollectorProxy;

$app->group('/cuestionarios', function(RouteCollectorProxy $group){

    // Obtener cuestionarios creados por el usuario
    $group->get('/', 'App\Controllers\MenuCuestionario_controller:getMisCuestionarios');
    
    // Obtener cuestionarios abiertos con estado
    $group->get('/abiertos', 'App\Controllers\MenuCuestionario_controller:getCuestionariosAbiertos');
    
    // Obtener programas disponibles para crear cuestionario
    $group->post('/programas-disponibles', 'App\Controllers\crearCuestionario_controller:getProgramasDisponibles');
    
    // Crear cuestionario
    $group->post('/crear', 'App\Controllers\crearCuestionario_controller:crearCuestionario');
    
    // Anexar preguntas a cuestionario
    $group->post('/{id}/anexar-preguntas', 'App\Controllers\crearCuestionario_controller:anexarPreguntasAndOpciones');
    
    // Obtener preguntas y opciones de un cuestionario
    $group->get('/{id}/preguntas-opciones', 'App\Controllers\resolver_controller:obtenerPreguntasyOpciones');
    
    // Guardar intento de resolver cuestionario
    $group->post('/{id}/guardar-intento', 'App\Controllers\resolver_controller:guardarIntento');
});