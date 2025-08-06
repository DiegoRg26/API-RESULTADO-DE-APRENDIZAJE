<?php
use Slim\Routing\RouteCollectorProxy;

$app->group('/aperturas', function(RouteCollectorProxy $group){
    
    // Obtener cuestionarios disponibles para apertura (sin aperturas activas) ✅
    $group->get('/cuestionarios-disponibles', 'App\Controllers\apertura_controller:getCuestionariosDisponibles');
    
    // Obtener periodos activos para asignar ✅
    $group->get('/periodos-activos', 'App\Controllers\apertura_controller:getPeriodosActivos');
    
    // Obtener aperturas activas del usuario ✅
    $group->get('/', 'App\Controllers\apertura_controller:getAperturas');
    
    // Crear nueva apertura (asignar cuestionario a periodo)
    $group->post('/crear', 'App\Controllers\apertura_controller:create');
    
    // Desactivar apertura
    $group->delete('/{id}', 'App\Controllers\apertura_controller:deactivate');

});
