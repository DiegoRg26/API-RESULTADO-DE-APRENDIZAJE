<?php
use Slim\Routing\RouteCollectorProxy;

$group->group('/informes', function(RouteCollectorProxy $subgroup){

    $subgroup->post('/periodo', 'App\Controllers\genInformes_controller:getInformesByPeriodo');
    $subgroup->post('/anio', 'App\Controllers\genInformes_controller:getInformesByAnio');
    
});