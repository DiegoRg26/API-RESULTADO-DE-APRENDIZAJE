<?php
use Slim\Routing\RouteCollectorProxy;

// Cargar el middleware manualmente
require_once __DIR__ . '/../Middleware/middlewareAuth.php';
use App\Middleware\middlewareAuth;

$group->group('/estudiante', function(RouteCollectorProxy $subgroup){

    // Obtener estudiantes de un programa
    $subgroup->get('', 'App\Controllers\estudiante_controller:getEstudiantes')->add(new middlewareAuth());

    // Obtener cuestionarios asignados
    $subgroup->get('/cuestionarios', 'App\Controllers\estudiante_controller:getCuestionariosAsignados')->add(new middlewareAuth());

    // Obtener cuestionarios completados
    $subgroup->get('/cuestionarios/completados', 'App\Controllers\estudiante_controller:getCuestionariosCompletados')->add(new middlewareAuth());

    // Obtener cuestionarios programados
    $subgroup->get('/cuestionarios/programados', 'App\Controllers\estudiante_controller:getCuestionariosProgramados')->add(new middlewareAuth());

    // Obtener cuestionarios expirados
    $subgroup->get('/cuestionarios/expirados', 'App\Controllers\estudiante_controller:getCuestionariosExpirados')->add(new middlewareAuth());

    // Logout estudiante
    $subgroup->get('/logout', 'App\Controllers\estudiantes_login_controller:logoutStudent')->add(new middlewareAuth());

    // Obtener informacion de un estudiante
    $subgroup->get('/{estudiante_id}', 'App\Controllers\estudiante_controller:getEstInfo')->add(new middlewareAuth());
    
    // Agregar estudiante
    $subgroup->post('/agregar', 'App\Controllers\estudiante_controller:agregarEstudiante')->add(new middlewareAuth())   ;
    
    // Login estudiante
    $subgroup->post('/login', 'App\Controllers\estudiantes_login_controller:authenticate');
    
    // Verificar token estudiante
    $subgroup->post('/verify', 'App\Controllers\estudiantes_login_controller:verifyStudentToken')->add(new middlewareAuth());
    
    // Habilitar estudiante
    $subgroup->put('/habilitar', 'App\Controllers\estudiante_controller:habilitarEstudiante')->add(new middlewareAuth());
    
    // Deshabilitar estudiante 
    $subgroup->put('/deshabilitar', 'App\Controllers\estudiante_controller:deshabilitarEstudiante')->add(new middlewareAuth());

});