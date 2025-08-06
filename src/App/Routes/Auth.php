<?php
use Slim\Routing\RouteCollectorProxy;

$app->group('/auth', function(RouteCollectorProxy $group){

// Login de usuario
$group->post('/', 'App\Controllers\login_controller:authenticate');

// Verificar token JWT ✅
$group->get('/verify', 'App\Controllers\login_controller:verifyToken');

// Refrescar token JWT ✅
$group->post('/refresh', 'App\Controllers\login_controller:refreshToken');

// Obtener información del usuario autenticado
$group->post('/me', 'App\Controllers\login_controller:getCurrentUser');

// Logout (cerrar sesión)
$group->post('/logout', 'App\Controllers\login_controller:logout');
});