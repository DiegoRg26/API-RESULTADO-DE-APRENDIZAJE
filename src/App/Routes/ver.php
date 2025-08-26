<?php
use Slim\Routing\RouteCollectorProxy;
use App\Middleware\middlewareAuth;

$group->group('/ver', function(RouteCollectorProxy $subgroup){

    // Obtener la informacion de un cuestionaro, detalles y preguntas, en base a la ID del cuestionario.
    $subgroup->get('/{cuestionario_id}','App\Controllers\ver_controller:getPreguntasRespuestas');

    // Obtener el intento mas reciente de un estudiante para un cuestionario.
    $subgroup->post('/intento','App\Controllers\ver_controller:getLastTry');

    // Obtener las respuestas de un estudiante para un cuestionario.
    $subgroup->get('/respuestas/{intento_id}','App\Controllers\ver_controller:getRespDet');
})->add(new middlewareAuth());