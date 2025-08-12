<?php
use Slim\Routing\RouteCollectorProxy;

$group->group('/cuestionario', function(RouteCollectorProxy $subgroup){

    // Obtener cuestionarios creados por el usuario
    $subgroup->get('', 'App\Controllers\MenuCuestionario_controller:getMisCuestionarios');
    
    // Obtener cuestionarios abiertos con estado
    $subgroup->get('/abiertos', 'App\Controllers\MenuCuestionario_controller:getCuestionariosAbiertos');
    
    //Obtener informacion de un cuestionario
    $subgroup->get('/{id}', 'App\Controllers\MenuCuestionario_controller:getCuestInfo');

    // Obtener programas disponibles para crear cuestionario
    // $subgroup->post('/programas-disponibles', 'App\Controllers\crearCuestionario_controller:getProgramasDisponibles');
    
    // Crear cuestionario
    $subgroup->post('/crear', 'App\Controllers\crearCuestionario_controller:crearCuestionario');
    
    // Anexar preguntas a cuestionario
    $subgroup->post('/{id}/anexar-preguntas', 'App\Controllers\crearCuestionario_controller:anexarPreguntasAndOpciones');
    
    // Obtener preguntas y opciones de un cuestionario
    $subgroup->get('/{id}/preguntas-opciones', 'App\Controllers\resolver_controller:obtenerPreguntasyOpciones');
    
    // Guardar intento de resolver cuestionario
    $subgroup->post('/{id}/guardar-intento', 'App\Controllers\resolver_controller:guardarIntento');
});
