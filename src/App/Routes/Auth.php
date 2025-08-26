<?php
use Slim\Routing\RouteCollectorProxy;

// Cargar el middleware manualmente
require_once __DIR__ . '/../Middleware/middlewareAuth.php';
use App\Middleware\middlewareAuth;

$group->group('/auth', function(RouteCollectorProxy $subgroup){

// Login de usuario
$subgroup->post('', 'App\Controllers\login_controller:authenticate');

// Verificar token JWT ✅
$subgroup->get('/verify', 'App\Controllers\login_controller:verifyToken')->add(new middlewareAuth());

// Refrescar token JWT ✅
$subgroup->post('/refresh', 'App\Controllers\login_controller:refreshToken')->add(new middlewareAuth());

// Obtener información del usuario autenticado
$subgroup->get('/me', 'App\Controllers\login_controller:getCurrentUser')->add(new middlewareAuth());

// Logout (cerrar sesión)
$subgroup->post('/logout', 'App\Controllers\login_controller:logout');

// Registro de usuario ✅
$subgroup->post('/register', 'App\Controllers\registro_controller:registrar')->add(new middlewareAuth());

$subgroup->get('/register/programas', 'App\Controllers\registro_controller:getProgramaUnTokenRegistro');
});