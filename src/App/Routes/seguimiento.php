<?php
use Slim\Routing\RouteCollectorProxy;

$group->group('/seguimiento', function(RouteCollectorProxy $subgroup){

    $subgroup->get('/info/{id}','App\Controllers\seguimiento_controller:getCuestionarioInfo');
    $subgroup->get('/estudiantes/{id}','App\Controllers\seguimiento_controller:getSeguiEstudiantes');
    $subgroup->get('/detalle','App\Controllers\seguimiento_controller:getSeguiCuestEstudiantes');
    
});