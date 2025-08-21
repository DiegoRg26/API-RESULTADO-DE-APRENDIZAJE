<?php
use Slim\Routing\RouteCollectorProxy;

$group->group('/estudiante', function(RouteCollectorProxy $subgroup){

    // Obtener estudiantes de un programa
    $subgroup->get('', 'App\Controllers\estudiante_controller:getEstudiantes');

    // Obtener cuestionarios asignados
    $subgroup->get('/cuestionarios', 'App\Controllers\estudiante_controller:getCuestionariosAsignados');

    // Obtener cuestionarios completados
    $subgroup->get('/cuestionarios/completados', 'App\Controllers\estudiante_controller:getCuestionariosCompletados');

    // Obtener cuestionarios programados
    $subgroup->get('/cuestionarios/programados', 'App\Controllers\estudiante_controller:getCuestionariosProgramados');

    // Obtener cuestionarios expirados
    $subgroup->get('/cuestionarios/expirados', 'App\Controllers\estudiante_controller:getCuestionariosExpirados');

    // Obtener informacion de un estudiante
    $subgroup->get('/{estudiante_id}', 'App\Controllers\estudiante_controller:getEstInfo');
    
    // Agregar estudiante
    $subgroup->post('/agregar', 'App\Controllers\estudiante_controller:agregarEstudiante');
    
    // Login estudiante
    $subgroup->post('/login', 'App\Controllers\estudiantes_login_controller:authenticate');
    
    // Logout estudiante
    $subgroup->post('/logout', 'App\Controllers\estudiantes_login_controller:logoutStudent');
    
    // Verificar token estudiante
    $subgroup->post('/verify', 'App\Controllers\estudiantes_login_controller:verifyStudentToken');
    
    // Habilitar estudiante
    $subgroup->put('/habilitar', 'App\Controllers\estudiante_controller:habilitarEstudiante');
    
    // Deshabilitar estudiante 
    $subgroup->put('/deshabilitar', 'App\Controllers\estudiante_controller:deshabilitarEstudiante');

});