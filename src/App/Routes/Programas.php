<?php
use Slim\Routing\RouteCollectorProxy;

$group->group('/programas', function(RouteCollectorProxy $subgroup){

    // Login de usuario
    $subgroup->get('', 'App\Controllers\BaseController:getProgramas');


    $subgroup->get('/{programa_id}', 'App\Controllers\BaseController:getProgramaById');
    
});