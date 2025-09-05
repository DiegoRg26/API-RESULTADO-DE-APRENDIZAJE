<?php
use Slim\Routing\RouteCollectorProxy;

$group->group('/auth', function(RouteCollectorProxy $subgroup){

// Login de usuario
$subgroup->post('', 'App\Controllers\login_controller:authenticate');

// Verificar token JWT ✅
$subgroup->get('/verify', 'App\Controllers\login_controller:verifyToken');

// Refrescar token JWT ✅
$subgroup->get('/refresh', 'App\Controllers\login_controller:refreshToken');

// Obtener información del usuario autenticado
$subgroup->get('/me', 'App\Controllers\login_controller:getCurrentUser');

// Logout (cerrar sesión)
$subgroup->post('/logout', 'App\Controllers\login_controller:logout');

// Registro de usuario ✅
$subgroup->post('/register', 'App\Controllers\registro_controller:registrar');

$subgroup->get('/register/programas', 'App\Controllers\registro_controller:getProgramaUnTokenRegistro');
});