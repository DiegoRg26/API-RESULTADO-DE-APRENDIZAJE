<?php
use Slim\Routing\RouteCollectorProxy;

$app->group('/periodos', function(RouteCollectorProxy $group){

    // Listar periodos activos
    $group->get('/', 'App\Controllers\periodo_controller:getActive');

    // Obtener periodo específico por ID
    $group->get('/{id}', 'App\Controllers\periodo_controller:getById');

    // Crear nuevo periodo
    $group->post('/create', 'App\Controllers\periodo_controller:create');

    // Actualizar periodo existente
    // $group->put('/periodos/{id}', 'App\Controllers\periodo_controller:update');
    
    // Desactivar periodo
    $group->delete('/{id}', 'App\Controllers\periodo_controller:deactivate');
    
    // Reactivar periodo
    $group->post('/{id}/activate', 'App\Controllers\periodo_controller:activate');
    
    // Listar periodos inactivos
    $group->get('/inactive', 'App\Controllers\periodo_controller:getInactive');
    
});
