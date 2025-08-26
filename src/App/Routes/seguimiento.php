<?php
use Slim\Routing\RouteCollectorProxy;

// Cargar el middleware manualmente
require_once __DIR__ . '/../Middleware/middlewareAuth.php';
use App\Middleware\middlewareAuth;

$group->group('/seguimiento', function(RouteCollectorProxy $subgroup){

    // Solicitar la informacion de un cuestionario, en base a un ID de apertura.
    $subgroup->get('/info/{apertura_id}','App\Controllers\seguimiento_controller:getCuestionarioInfo');
    
    // Solicitar la informacion de los estudiantes asignados a un cuestionario, en base a un ID de apertura.
    $subgroup->get('/estudiantes/{apertura_id}','App\Controllers\seguimiento_controller:getSeguiEstudiantes');
    
    // Solicitar los distintos cuestionarios y sus respectivos detalles de aquellos cuestionarios asignados y activos.
    $subgroup->get('/detalle','App\Controllers\seguimiento_controller:getSeguiCuestEstudiantes');

    $subgroup->get('/allquiz','App\Controllers\seguimiento_controller:getAllDocQuiz');
})->add(new middlewareAuth() );