<?php
use Slim\Routing\RouteCollectorProxy;

$group->group('/periodo', function(RouteCollectorProxy $subgroup){

    // Listar periodos activos
    $subgroup->get('', 'App\Controllers\periodo_controller:getActive');

    // Listar periodos inactivos
    $subgroup->get('/inactive', 'App\Controllers\periodo_controller:getInactive');

    // Crear nuevo periodo
    $subgroup->post('/create', 'App\Controllers\periodo_controller:create');

    // Actualizar periodo existente
    // $subgroup->put('/periodos/{id}', 'App\Controllers\periodo_controller:update');
    
    // Obtener periodo específico por ID
    $subgroup->get('/{id}', 'App\Controllers\periodo_controller:getById');
    
    // Desactivar periodo
    $subgroup->delete('/{id}', 'App\Controllers\periodo_controller:deactivate');
    
    // Reactivar periodo
    $subgroup->put('/{id}/activate', 'App\Controllers\periodo_controller:activate');
    
});