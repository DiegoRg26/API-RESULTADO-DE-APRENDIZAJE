<?php
use Slim\Routing\RouteCollectorProxy;


$group->group('/ver', function(RouteCollectorProxy $subgroup){

    // Obtener la informacion de un cuestionaro, detalles y preguntas, en base a la ID del cuestionario.
    $subgroup->get('/{id}','App\Controllers\ver_controller:getPreguntasRespuestas');

    // Obtener el intento mas reciente de un estudiante para un cuestionario.
    $subgroup->post('/intento','App\Controllers\ver_controller:getLastTry');

    // Obtener las respuestas de un estudiante para un cuestionario.
    $subgroup->get('/respuestas/{id}','App\Controllers\ver_controller:getRespDet');
});