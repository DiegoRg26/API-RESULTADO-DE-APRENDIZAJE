<?php
use Slim\Routing\RouteCollectorProxy;

// Middleware para CORS - aplicar a todas las rutas ANTES de las rutas
$app->options('/{routes:.+}', function ($request, $response, $args) {
    return $response
        ->withHeader('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, Accept, Origin, Authorization')
        ->withHeader('Access-Control-Allow-Credentials', 'true')
        ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, PATCH, OPTIONS');
});

// Middleware para añadir headers CORS a todas las respuestas
$app->add(function ($request, $handler) {
    $response = $handler->handle($request);
    return $response
        ->withHeader('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, Accept, Origin, Authorization')
        ->withHeader('Access-Control-Allow-Credentials', 'true')
        ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, PATCH, OPTIONS')
        ->withHeader('Access-Control-Allow-Credentials', 'true');
});
// Grupo de rutas de testing adicional
$app->group('/api', function(RouteCollectorProxy $group){
    $group->get('/test', 'App\Controllers\test:getTesteo');
});
$app->group('/api', function(RouteCollectorProxy $group){

    //===========================[Rutas de Programas]=========================
    $group->get('/programas/get', 'App\Controllers\BaseController:getProgramas');
    
    //===========================[Rutas de Autenticación]=========================
    require __DIR__ . '/Routes/Auth.php';
    //===========================[Rutas de Cuestionarios]=========================
    require __DIR__ . '/Routes/Cuestionario.php';
    //==============================[Rutas de Periodos]===========================
    require __DIR__ . '/Routes/Periodo.php';
    //==============================[Rutas de Aperturas]==========================
    require __DIR__ . '/Routes/Apertura.php';
    //==============================[Rutas de Asignación]==========================
    require __DIR__ . '/Routes/Asignacion.php';
    //==============================[Rutas de Estudiantes]==========================
    require __DIR__ . '/Routes/Estudiantes.php';
});