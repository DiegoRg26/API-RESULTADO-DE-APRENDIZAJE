<?php
use Slim\Routing\RouteCollectorProxy;

$group->group('/cuestionario', function(RouteCollectorProxy $subgroup){

    // Obtener cuestionarios creados por el usuario
    $subgroup->get('', 'App\Controllers\MenuCuestionario_controller:getMisCuestionarios');
    
    // Obtener cuestionarios abiertos con estado
    $subgroup->get('/abiertos', 'App\Controllers\MenuCuestionario_controller:getCuestionariosAbiertos');
    
    //Obtener informacion de un cuestionario
    $subgroup->get('/{cuestionario_id}', 'App\Controllers\MenuCuestionario_controller:getCuestInfo');

    // Verificar cuestionario
    $subgroup->get('/{cuestionario_id}/verificar', 'App\Controllers\resolver_controller:verificarResolucion');

    // Obtener programas disponibles para crear cuestionario
    // $subgroup->post('/programas-disponibles', 'App\Controllers\crearCuestionario_controller:getProgramasDisponibles');
    
    // Crear cuestionario
    $subgroup->post('/crear', 'App\Controllers\crearCuestionario_controller:crearCuestionario');
    
    // Anexar preguntas a cuestionario
    $subgroup->post('/{cuestionario_id}/anexar-preguntas', 'App\Controllers\crearCuestionario_controller:anexarPreguntasAndOpciones');
    
    // Obtener preguntas y opciones de un cuestionario
    $subgroup->get('/{cuestionario_id}/preguntas-opciones', 'App\Controllers\resolver_controller:obtenerPreguntasyOpciones');
    
    // Guardar intento de resolver cuestionario
    $subgroup->post('/{cuestionario_id}/guardar-intento', 'App\Controllers\resolver_controller:guardarIntento');

    // Guardar estado del cuestionario (proceso de realizado)
    $subgroup->post('/resolver/guardar-estado', 'App\Controllers\resolver_controller:updateEstado');
});
