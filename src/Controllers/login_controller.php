<?php
namespace App\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\Controllers\BaseController;
use Psr\Container\ContainerInterface;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use PDO;
use Exception;
// use Dotenv;


/**
 * Controlador de autenticación con JWT para Slim Framework
 * Maneja el login de usuarios mediante email/password 
 * y genera tokens JWT para sesiones seguras
 */
class login_controller extends BaseController
{
    private $jwtSecret;
    private $jwtExpiration;

    
    public function __construct(ContainerInterface $c)
    {
        parent::__construct($c);
        $this->jwtSecret = $_ENV['JWT_SECRET'];
        $this->jwtExpiration = 3600; // 1 hora en segundos
    }
    
    /**
     * Autentica usuario con email y password
     * Recibe datos JSON desde el frontend
     * 
     * @param Request $request
     * @param Response $response
     * @param array $args
     * @return Response
     */
    public function authenticate(Request $request, Response $response, array $args): Response{
        $db = null;
        try {
            // Obtener conexión a la base de datos
            $db = $this->container->get('db');
            
            // Obtener y validar datos JSON del request
            $inputData = $this->getJsonInput($request);
            
            if (!$inputData) {
                return $this->errorResponse($response, 'Datos JSON inválidos', 400);
            }
            
            // Validar campos requeridos
            $validation = $this->validateLoginData($inputData);
            if (!$validation['valid']) {
                return $this->errorResponse($response, $validation['message'], 400);
            }
            
            $email = $inputData['email'];
            $password = $inputData['password'];
            
            // Buscar usuario en base de datos
            $user = $this->findUserByEmail($db, $email);
            
            if (!$user) {
                return $this->errorResponse($response, 'Credenciales incorrectas', 401);
            }
            
            // Verificar contraseña
            if (!$this->verifyPassword($password, $user['password'])) {
                return $this->errorResponse($response, 'Credenciales incorrectas', 401);
            }
            
            // Generar token JWT
            $token = $this->generateJwtToken($user);
            
            // Respuesta exitosa
            return $this->successResponse($response, 'Login exitoso', [
                'token' => $token,
                'user' => $this->formatUserData($user),
                'expires_in' => $this->jwtExpiration
            ]);
            
        } catch (Exception $e) {
            error_log("Error en authenticate: " . $e->getMessage());
            return $this->errorResponse($response, 'Error interno del servidor', 500);
        }finally{
            if($db !== null){
                $db = null;
            }
        }
    }
    
    /**
     * Verifica si un token JWT es válido
     * 
     * @param Request $request
     * @param Response $response
     * @param array $args
     * @return Response
     */
    public function verifyToken(Request $request, Response $response, array $args): Response
    {
        try {
            $token = $this->getBearerToken($request);
            
            if (!$token) {
                return $this->errorResponse($response, 'Token no proporcionado', 401);
            }
            
            $decoded = JWT::decode($token, new Key($this->jwtSecret, 'HS256'));
            
            // Convertir objeto a array
            $userData = (array) $decoded;
            
            return $this->successResponse($response, 'Token válido', [
                'user' => [
                    'id' => $userData['user_id'],
                    'nombre' => $userData['nombre'],
                    'email' => $userData['email'],
                    'programa_id' => $userData['programa_id'] ?? null
                ],
                'expires_at' => date('Y-m-d H:i:s', $userData['exp'])
            ]);
            
        } catch (Exception $e) {
            error_log("Error en verifyToken: " . $e->getMessage());
            return $this->errorResponse($response, 'Token inválido o expirado', 401);
        }
    }
    
    /**
     * Refresca un token JWT válido
     * 
     * @param Request $request
     * @param Response $response
     * @param array $args
     * @return Response
     */
    public function refreshToken(Request $request, Response $response, array $args): Response
    {
        try {
            $db = $this->container->get('db');
            $token = $this->getBearerToken($request);
            
            if (!$token) {
                return $this->errorResponse($response, 'Token no proporcionado', 401);
            }
            
            // Verificar token actual
            $decoded = JWT::decode($token, new Key($this->jwtSecret, 'HS256'));
            $userData = (array) $decoded;
            
            // Buscar usuario actualizado en BD
            $user = $this->findUserByEmail($db, $userData['email']);
            
            if (!$user) {
                return $this->errorResponse($response, 'Usuario no encontrado', 404);
            }
            
            // Generar nuevo token
            $newToken = $this->generateJwtToken($user);
            
            return $this->successResponse($response, 'Token refrescado', [
                'token' => $newToken,
                'user' => $this->formatUserData($user),
                'expires_in' => $this->jwtExpiration
            ]);
            
        } catch (Exception $e) {
            error_log("Error en refreshToken: " . $e->getMessage());
            return $this->errorResponse($response, 'Error al refrescar token', 500);
        }
    }
    
    /**
     * Obtiene información del usuario autenticado
     * 
     * @param Request $request
     * @param Response $response
     * @param array $args
     * @return Response
     */
    public function getCurrentUser(Request $request, Response $response, array $args): Response{
        try {
            $db = $this->container->get('db');
            $token = $this->getBearerToken($request);
            
            if (!$token) {
                return $this->errorResponse($response, 'Token no proporcionado', 401);
            }
            
            $decoded = JWT::decode($token, new Key($this->jwtSecret, 'HS256'));
            $userData = (array) $decoded;
            
            // Obtener datos actualizados del usuario
            $user = $this->findUserByEmail($db, $userData['email']);
            
            if (!$user) {
                return $this->errorResponse($response, 'Usuario no encontrado', 404);
            }
            
            return $this->successResponse($response, 'Usuario encontrado', [
                'user' => $this->formatUserData($user)
            ]);
            
        } catch (Exception $e) {
            error_log("Error en getCurrentUser: " . $e->getMessage());
            return $this->errorResponse($response, 'Error al obtener usuario', 500);
        }
    }
    
    /**
     * Cerrar sesión (invalida token del lado cliente)
     * 
     * @param Request $request
     * @param Response $response
     * @param array $args
     * @return Response
     */
    public function logout(Request $request, Response $response, array $args): Response
    {
        // En JWT stateless, el logout se maneja del lado del cliente
        // removiendo el token del almacenamiento local
        return $this->successResponse($response, 'Sesión cerrada correctamente', [
            'message' => 'Elimina el token del almacenamiento local del cliente'
        ]);
    }
    
    /**
     * Busca un usuario por email en la base de datos
     * 
     * @param PDO $db Conexión a la base de datos
     * @param string $email Email del usuario
     * @return array|false Datos del usuario o false si no existe
     */
    private function findUserByEmail(PDO $db, string $email){
        try {
            $query = "SELECT id, nombre, email, identificacion, password, programa_id, fecha_registro 
                        FROM docente 
                        WHERE email = :email 
                        LIMIT 1";
                    
            $stmt = $db->prepare($query);
            $stmt->bindParam(':email', $email, PDO::PARAM_STR);
            $stmt->execute();
            
            return $stmt->fetch(PDO::FETCH_ASSOC);
            
        } catch (Exception $e) {
            error_log("Error en findUserByEmail: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Verifica la contraseña contra el hash almacenado
     * 
     * @param string $password Contraseña en texto plano
     * @param string $hashedPassword Hash de la contraseña
     * @return bool
     */
    private function verifyPassword(string $password, string $hashedPassword): bool{
        return password_verify($password, $hashedPassword);
    }
    
    /**
     * Genera un token JWT para el usuario
     * 
     * @param array $user Datos del usuario
     * @return string Token JWT
     */
    private function generateJwtToken(array $user): string{
        $now = time();
        $expiration = $now + $this->jwtExpiration;
        
        $payload = [
            'iss' => 'cuestionario-backend',           // Issuer
            'aud' => 'cuestionario-frontend',          // Audience  
            'iat' => $now,                             // Issued at
            'exp' => $expiration,                      // Expiration
            'user_id' => (int) $user['id'],
            'nombre' => $user['nombre'],
            'email' => $user['email'],
            'programa_id' => $user['programa_id'] ? (int) $user['programa_id'] : null,
            'identificacion' => $user['identificacion']
        ];
        
        return JWT::encode($payload, $this->jwtSecret, 'HS256');
    }
    
    /**
     * Valida los datos de login recibidos
     * 
     * @param array $data Datos del request
     * @return array Resultado de validación
     */
    private function validateLoginData(array $data): array{
        if (!isset($data['email']) || empty(trim($data['email']))) {
            return ['valid' => false, 'message' => 'El email es requerido'];
        }
        
        if (!isset($data['password']) || empty(trim($data['password']))) {
            return ['valid' => false, 'message' => 'La contraseña es requerida'];
        }
        
        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            return ['valid' => false, 'message' => 'El email no tiene un formato válido'];
        }
        
        if (strlen($data['password']) < 3) {
            return ['valid' => false, 'message' => 'La contraseña debe tener al menos 3 caracteres'];
        }
        
        return ['valid' => true, 'message' => 'Datos válidos'];
    }
    /**
     * Formatea los datos del usuario para la respuesta
     * 
     * @param array $user Datos del usuario
     * @return array Datos formateados
     */
    private function formatUserData(array $user): array{
        return [
            'id' => (int) $user['id'],
            'nombre' => $user['nombre'],
            'email' => $user['email'],
            'identificacion' => $user['identificacion'],
            'programa_id' => $user['programa_id'] ? (int) $user['programa_id'] : null,
            'fecha_registro' => $user['fecha_registro']
        ];
    }
    
    
}