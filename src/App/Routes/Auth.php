<?php
use Slim\Routing\RouteCollectorProxy;


$group->group('/auth', function(RouteCollectorProxy $subgroup){

// Login de usuario
$subgroup->post('', 'App\Controllers\login_controller:authenticate');

// Verificar token JWT ✅
$subgroup->get('/verify', 'App\Controllers\login_controller:verifyToken');

// Refrescar token JWT ✅
$subgroup->post('/refresh', 'App\Controllers\login_controller:refreshToken');

// Obtener información del usuario autenticado
$subgroup->get('/me', 'App\Controllers\login_controller:getCurrentUser');

// Logout (cerrar sesión)
$subgroup->post('/logout', 'App\Controllers\login_controller:logout');

});