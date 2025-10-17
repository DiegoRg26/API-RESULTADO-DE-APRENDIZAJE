<?php
use Slim\Routing\RouteCollectorProxy;

$group->group('/informes', function(RouteCollectorProxy $subgroup){

    $subgroup->post('/periodo', 'App\Controllers\genInformes_controller:getInformesByPeriodo');
    $subgroup->post('/anio', 'App\Controllers\genInformes_controller:getInformesByAnio');
    $subgroup->post('/validar-cuestionarios', 'App\Controllers\genInformes_controller:validarCuestionariosCompletados');
    $subgroup->post('/promedio-estudiante', 'App\Controllers\genInformes_controller:calcularPromedioEstudiante');
    
});