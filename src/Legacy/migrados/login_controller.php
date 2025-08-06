<?php

use Firebase\JWT\JWT;
require_once __DIR__ . '/../model/conection.php';

class LoginController
{
    private $database;
    private $db;
    
    public function __construct()
    {
        $this->database = new Database();
        $this->db = $this->database->connect();
    }
    
    /**
     * Autentica usuario y devuelve JWT token
     */
    public function authenticate($email, $password)
    {
        try {
            // Sanitizar email
            $email = $this->sanitizeInput($email);
            
            // Buscar usuario en la base de datos
            $query = "SELECT id, nombre, email, password, programa_id FROM docente WHERE email = :email";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':email', $email);
            $stmt->execute();
            
            if ($stmt->rowCount() !== 1) {
                return [
                    'success' => false,
                    'message' => 'Usuario no encontrado',
                    'status' => 'error'
                ];
            }
            
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Verificar contraseña
            if (!password_verify($password, $user['password'])) {
                return [
                    'success' => false,
                    'message' => 'Credenciales incorrectas',
                    'status' => 'error'
                ];
            }
            
            // Generar JWT token
            $token = $this->generateJWT($user);
            
            return [
                'success' => true,
                'message' => 'Autenticación exitosa',
                'status' => 'success',
                'token' => $token,
                'user' => [
                    'id' => $user['id'],
                    'nombre' => $user['nombre'],
                    'email' => $user['email'],
                    'programa_id' => $user['programa_id']
                ]
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Error interno del servidor',
                'status' => 'error',
                'debug' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Generar JWT token
     */
    private function generateJWT($user)
    {
        $key = $_ENV['JWT_SECRET'] ?? 'default-secret-key';
        $now = time();
        
        $payload = [
            'iss' => 'cuestionario-backend',
            'aud' => 'cuestionario-frontend',
            'iat' => $now,
            'exp' => $now + 3600, // 1 hora
            'id' => $user['id'],
            'nombre' => $user['nombre'],
            'email' => $user['email'],
            'programa_id' => $user['programa_id'],
        ];
        
        return JWT::encode($payload, $key, 'HS256');
    }
    
    /**
     * Sanitizar entrada de datos
     */
    private function sanitizeInput($input)
    {
        return htmlspecialchars(strip_tags(trim($input)));
    }
    
    /**
     * Validar email
     */
    private function validateEmail($email)
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL);
    }
    
    /**
     * Cerrar sesión
     */
    public function logout()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        session_destroy();
        
        return [
            'success' => true,
            'message' => 'Sesión cerrada correctamente'
        ];
    }
}