<?php
use Slim\Routing\RouteCollectorProxy;

$group->group('/aperturas', function(RouteCollectorProxy $subgroup){

    // Obtener cuestionarios disponibles para apertura (sin aperturas activas) ✅
    $subgroup->get('/cuestionarios-disponibles', 'App\Controllers\apertura_controller:getCuestionariosDisponibles');
    
    // Obtener periodos activos para asignar ✅
    $subgroup->get('/periodos-activos', 'App\Controllers\apertura_controller:getPeriodosActivos');
    
    // Obtener aperturas activas del usuario ✅
    $subgroup->get('', 'App\Controllers\apertura_controller:getAperturas');
    
    // Crear nueva apertura (asignar cuestionario a periodo)
    $subgroup->post('/crear', 'App\Controllers\apertura_controller:create');
    
    // Desactivar apertura
    $subgroup->delete('/{id}', 'App\Controllers\apertura_controller:deactivate');
    
});
