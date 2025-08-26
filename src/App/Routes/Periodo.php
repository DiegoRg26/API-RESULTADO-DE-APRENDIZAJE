<?php
use Slim\Routing\RouteCollectorProxy;

// Cargar el middleware manualmente
require_once __DIR__ . '/../Middleware/middlewareAuth.php';
use App\Middleware\middlewareAuth;

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
    $subgroup->get('/{periodo_id}', 'App\Controllers\periodo_controller:getById');
    
    // Desactivar periodo
    $subgroup->delete('/{periodo_id}', 'App\Controllers\periodo_controller:deactivate');
    
    // Reactivar periodo
    $subgroup->put('/{periodo_id}/activate', 'App\Controllers\periodo_controller:activate');
    
})->add(new middlewareAuth());