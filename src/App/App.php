<?php
use Slim\Factory\AppFactory;

require __DIR__ . '/../../vendor/autoload.php';

$aux = new \DI\Container();
AppFactory::setContainer($aux);
$app = AppFactory::create();
// $app->addErrorMiddleware(true, true, true);
$container = $app->getContainer();
$app->add(function($request, $handler){
    $response = $handler->handle($request);
    return $response
    ->withHeader('Access-Control-Allow-Origin','*')
            ->withHeader('Access-Control-Allow-Headers', 'Content-Type, Authorization')
            ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
});


require __DIR__ . '/Routes.php';
require __DIR__ . '/Config.php';
require __DIR__ . '/Dependencies.php';

$app->run();