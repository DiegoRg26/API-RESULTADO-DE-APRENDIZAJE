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
// $app->group('/api', function(RouteCollectorProxy $group){
// });
$app->group('/api', function(RouteCollectorProxy $group){
    
    $group->get('/test', 'App\Controllers\test:getTesteo');
    //===========================[Rutas de Programas]=========================
    @include __DIR__ . '/Routes/Programas.php';
    
    //===========================[Rutas de Autenticación]=========================
    @include __DIR__ . '/Routes/Auth.php';
    
    //===========================[Rutas de Cuestionarios]=========================
    @include __DIR__ . '/Routes/Cuestionario.php';
    
    //==============================[Rutas de Periodos]===========================
    @include __DIR__ . '/Routes/Periodo.php';
    
    //==============================[Rutas de Aperturas]==========================
    @include __DIR__ . '/Routes/Apertura.php';
    
    //==============================[Rutas de Asignación]==========================
    @include __DIR__ . '/Routes/Asignacion.php';
    
    //==============================[Rutas de Estudiantes]==========================
    @include __DIR__ . '/Routes/Estudiantes.php';
});