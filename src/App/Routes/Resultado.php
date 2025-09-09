<?php
use Slim\Routing\RouteCollectorProxy;

$group->group('/resultados', function(RouteCollectorProxy $subgroup){

    // Obtener resultados de un intento
    $subgroup->get('/obtener/{intento_id}', 'App\Controllers\resultado_controller:obtenerResultado');

    // Obtener detalles de un intento
    $subgroup->get('/detalles/{intento_id}', 'App\Controllers\resultado_controller:obtenerDetalles');

    $subgroup->get('/estudiantes/{apertura_id}', 'App\Controllers\resultado_controller:obtResulEstudiantes');
});
