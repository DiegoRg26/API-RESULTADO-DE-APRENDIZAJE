<?php
use Slim\Routing\RouteCollectorProxy;

$group->group('/autoprocess', function(RouteCollectorProxy $subgroup){

    // Procesar períodos expirados y crear intentos faltantes
    $subgroup->get('/expired-periods', 'App\Controllers\AutoProcessController:processExpiredPeriods');

});
