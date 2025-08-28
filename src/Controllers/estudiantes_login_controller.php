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


class estudiantes_login_controller extends BaseController
{
    private $jwtSecret;
    private $jwtExpiration;
    private $sessionExpiration;

    public function __construct(ContainerInterface $c)
    {
        parent::__construct($c);
        $this->jwtSecret = $_ENV['JWT_SECRET'];
        $this->jwtExpiration = 3600; // 1 hora en segundos
        $this->sessionExpiration = 3600; // 1 hora para estudiantes
    }

    /**
     * Autentica estudiante con email e identificación
     * Implementa sesiones únicas por estudiante
     */
    public function authenticate(Request $request, Response $response, array $args): Response
    {
        $db = null;
        try {
            $db = $this->container->get('db');

            // Obtener y validar datos JSON del request
            $inputData = $this->getJsonInput($request);

            if (!$inputData) {
                return $this->errorResponse($response, 'Datos JSON inválidos', 400);
            }

            // Validar campos requeridos
            $validation = $this->validateStudentLoginData($inputData);
            if (!$validation['valid']) {
                return $this->errorResponse($response, $validation['message'], 400);
            }

            $email = trim($inputData['email']);
            $identificacion = trim($inputData['identificacion']);

            // Buscar estudiante en base de datos
            $student = $this->findStudentByEmailAndId($db, $email, $identificacion);

            if (!$student) {
                return $this->errorResponse($response, 'Credenciales incorrectas', 401);
            }

            // agregar rol al estudiante
            $student['rol'] = 2;

            // Verificar si el estudiante está activo
            if (!$student['estado']) {
                return $this->errorResponse($response, 'Cuenta de estudiante inactiva', 403);
            }

            // Obtener información del cliente
            $clientInfo = $this->getClientInfo($request);

            // Verificar y manejar sesión existente
            $sessionCheck = $this->handleExistingSession($db, $student['id'], $clientInfo);
            if ($sessionCheck['action'] === 'block') {
                return $this->errorResponse($response, $sessionCheck['message'], 409);
            }

            // Generar nuevo token JWT con JTI único
            $jwtId = $this->generateJwtId();
            $token = $this->generateStudentJwtToken($student, $jwtId);

            $sql_delete_logs = "DELETE FROM sesion_estudiante 
                                WHERE fecha_creacion < DATE_SUB(NOW(), INTERVAL 3 MINUTE) AND id_estudiante = :estudiante_id";
            $stmt = $db->prepare($sql_delete_logs);
            $stmt->bindParam(':estudiante_id', $student['id']);
            $stmt->execute();
            if (!$stmt->execute()) {
                return $this->errorResponse($response, 'Error al eliminar sesiones anteriores', 500);
            }

            // Crear nueva sesión
            $sessionResult = $this->createStudentSession($db, $student['id'], $jwtId, $clientInfo);

            if (!$sessionResult) {
                return $this->errorResponse($response, 'Error al crear sesión', 500);
            }

            // Respuesta exitosa
            return $this->successResponse($response, 'Login exitoso', [
                'token' => $token,
                'student' => $this->formatStudentData($student),
                'expires_in' => $this->jwtExpiration,
                'session_id' => $jwtId
            ]);
        } catch (Exception $e) {
            error_log("Error en student authenticate: " . $e->getMessage());
            return $this->errorResponse($response, 'Error interno del servidor' . $e->getMessage(), 500);
        } finally {
            if ($db !== null) {
                $db = null;
            }
        }
    }

    /**
     * Verifica token de estudiante y actualiza última actividad
     */
    public function verifyStudentToken(Request $request, Response $response, array $args): Response
    {
        try {
            $db = $this->container->get('db');
            $token = $this->getBearerToken($request);

            if (!$token) {
                return $this->errorResponse($response, 'Token no proporcionado', 401);
            }

            $decoded = JWT::decode($token, new Key($this->jwtSecret, 'HS256'));
            $userData = (array) $decoded;

            // Verificar que es un token de estudiante
            if (!isset($userData['user_type']) || $userData['user_type'] !== 'student') {
                return $this->errorResponse($response, 'Token inválido', 401);
            }

            // Verificar que la sesión sigue activa
            $sessionValid = $this->verifyStudentSession($db, $userData['jti'], $userData['user_id']);

            if (!$sessionValid) {
                return $this->errorResponse($response, 'Sesión expirada o inválida', 401);
            }

            // Actualizar última actividad
            $this->updateSessionActivity($db, $userData['jti']);

            return $this->successResponse($response, 'Token válido', [
                'student' => [
                    'id' => $userData['user_id'],
                    'nombre' => $userData['nombre'],
                    'email' => $userData['email'],
                    'programa_id' => $userData['programa_id']
                ],
                'expires_at' => date('Y-m-d H:i:s', $userData['exp'])
            ]);
        } catch (Exception $e) {
            error_log("Error en verifyStudentToken: " . $e->getMessage());
            return $this->errorResponse($response, 'Token inválido o expirado', 401);
        }
    }

    /**
     * Cierra sesión de estudiante invalidando la sesión
     */
    public function logoutStudent(Request $request, Response $response, array $args): Response
    {
        try {
            $db = $this->container->get('db');
            $token = $this->getBearerToken($request);

            if (!$token) {
                return $this->successResponse($response, 'Sesión cerrada', []);
            }

            try {
                $decoded = JWT::decode($token, new Key($this->jwtSecret, 'HS256'));
                $userData = (array) $decoded;

                if (isset($userData['jti'])) {
                    $this->invalidateStudentSession($db, $userData['jti']);
                }
            } catch (Exception $e) {
                // Token ya inválido, no hay problema
            }

            return $this->successResponse($response, 'Sesión cerrada correctamente', []);
        } catch (Exception $e) {
            error_log("Error en logoutStudent: " . $e->getMessage());
            return $this->successResponse($response, 'Sesión cerrada', []);
        }
    }

    /**
     * Refrescar token del estudiante
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

            // Verificar que es un token de estudiante
            if (!isset($userData['user_type']) || $userData['user_type'] !== 'student') {
                return $this->errorResponse($response, 'Token inválido', 401);
            }

            // Verificar que la sesión sigue activa
            $sessionValid = $this->verifyStudentSession($db, $userData['jti'], $userData['user_id']);

            if (!$sessionValid) {
                return $this->errorResponse($response, 'Sesión expirada o inválida', 401);
            }

            // Buscar estudiante actualizado en BD
            $student = $this->findStudentByEmailAndId($db, $userData['email'], $userData['identificacion']);

            if (!$student) {
                return $this->errorResponse($response, 'Estudiante no encontrado', 404);
            }

            // Verificar si el estudiante sigue activo
            if (!$student['estado']) {
                return $this->errorResponse($response, 'Cuenta de estudiante inactiva', 403);
            }

            // Agregar rol al estudiante
            $student['rol'] = 2;

            // Generar nuevo token con el mismo JTI para mantener la sesión
            $newToken = $this->generateStudentJwtToken($student, $userData['jti']);

            // Actualizar última actividad de la sesión
            $this->updateSessionActivity($db, $userData['jti']);

            return $this->successResponse($response, 'Token refrescado', [
                'token' => $newToken,
                'student' => $this->formatStudentData($student),
                'expires_in' => $this->jwtExpiration,
                'session_id' => $userData['jti']
            ]);
        } catch (Exception $e) {
            error_log("Error en refreshToken: " . $e->getMessage());
            return $this->errorResponse($response, 'Error al refrescar token', 500);
        }
    }

    /**
     * Busca estudiante por email e identificación
     */
    private function findStudentByEmailAndId(PDO $db, string $email, string $identificacion)
    {
        try {
            $query = "SELECT e.id, e.email, e.identificacion, e.nombre, e.id_programa, e.estado,
                            p.nombre as programa_nombre, c.nombre as campus_nombre
                        FROM estudiante e 
                        INNER JOIN programa p ON e.id_programa = p.id
                        INNER JOIN campus c ON p.id_campus = c.id
                        WHERE e.email = :email AND e.identificacion = :identificacion
                        LIMIT 1";

            $stmt = $db->prepare($query);
            $stmt->bindParam(':email', $email, PDO::PARAM_STR);
            $stmt->bindParam(':identificacion', $identificacion, PDO::PARAM_STR);
            $stmt->execute();

            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error en findStudentByEmailAndId: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Maneja sesiones existentes del estudiante
     */
    private function handleExistingSession(PDO $db, int $studentId, array $clientInfo): array
    {
        try {
            // Buscar sesión activa existente
            $query = "SELECT id, session_token, ip_address, user_agent, fecha_ultima_actividad 
                        FROM sesion_estudiante 
                        WHERE id_estudiante = :student_id AND activa = 1 
                        LIMIT 1";

            $stmt = $db->prepare($query);
            $stmt->bindParam(':student_id', $studentId, PDO::PARAM_INT);
            $stmt->execute();

            $existingSession = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($existingSession) {
                // Verificar si es el mismo dispositivo/navegador
                $sameDevice = $this->isSameDevice($existingSession, $clientInfo);

                if ($sameDevice) {
                    // Mismo dispositivo, invalidar sesión anterior y permitir nueva
                    $this->invalidateStudentSessionById($db, $existingSession['id']);
                    return ['action' => 'allow', 'message' => 'Renovando sesión en mismo dispositivo'];
                } else {
                    // Verificar si la sesión está realmente activa (menos de 8 horas)
                    $lastActivity = strtotime($existingSession['fecha_ultima_actividad']);
                    $now = time();

                    if (($now - $lastActivity) > $this->sessionExpiration) {
                        // Sesión expirada, permitir nueva
                        $this->invalidateStudentSessionById($db, $existingSession['id']);
                        return ['action' => 'allow', 'message' => 'Sesión anterior expirada'];
                    } else {
                        // Sesión activa en otro dispositivo
                        return [
                            'action' => 'block',
                            'message' => 'Ya existe una sesión activa desde otro dispositivo. Cierra sesión primero.'
                        ];
                    }
                }
            }

            return ['action' => 'allow', 'message' => 'Sin sesiones existentes'];
        } catch (Exception $e) {
            error_log("Error en handleExistingSession: " . $e->getMessage());
            return ['action' => 'allow', 'message' => 'Error verificando sesión'];
        }
    }

    /**
     * Crea una nueva sesión de estudiante
     */
    private function createStudentSession(PDO $db, int $studentId, string $jwtId, array $clientInfo): bool
    {
        try {
            $sessionToken = $this->generateSessionToken();

            $query = "INSERT INTO sesion_estudiante 
                        (id_estudiante, session_token, jwt_jti, ip_address, user_agent) 
                        VALUES (:student_id, :session_token, :jwt_jti, :ip_address, :user_agent)";

            $stmt = $db->prepare($query);
            $stmt->bindParam(':student_id', $studentId, PDO::PARAM_INT);
            $stmt->bindParam(':session_token', $sessionToken, PDO::PARAM_STR);
            $stmt->bindParam(':jwt_jti', $jwtId, PDO::PARAM_STR);
            $stmt->bindParam(':ip_address', $clientInfo['ip'], PDO::PARAM_STR);
            $stmt->bindParam(':user_agent', $clientInfo['user_agent'], PDO::PARAM_STR);

            return $stmt->execute();
        } catch (Exception $e) {
            error_log("Error en createStudentSession: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Verifica si una sesión de estudiante es válida
     */
    private function verifyStudentSession(PDO $db, string $jwtId, int $studentId): bool
    {
        try {
            $query = "SELECT id FROM sesion_estudiante 
                    WHERE jwt_jti = :jti AND id_estudiante = :student_id AND activa = 1 
                    AND fecha_ultima_actividad > DATE_SUB(NOW(), INTERVAL 8 HOUR)
                    LIMIT 1";

            $stmt = $db->prepare($query);
            $stmt->bindParam(':jti', $jwtId, PDO::PARAM_STR);
            $stmt->bindParam(':student_id', $studentId, PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->rowCount() > 0;
        } catch (Exception $e) {
            error_log("Error en verifyStudentSession: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Actualiza la última actividad de una sesión
     */
    private function updateSessionActivity(PDO $db, string $jwtId): void
    {
        try {
            $query = "UPDATE sesion_estudiante 
                    SET fecha_ultima_actividad = CURRENT_TIMESTAMP 
                    WHERE jwt_jti = :jti AND activa = 1";

            $stmt = $db->prepare($query);
            $stmt->bindParam(':jti', $jwtId, PDO::PARAM_STR);
            $stmt->execute();
        } catch (Exception $e) {
            error_log("Error en updateSessionActivity: " . $e->getMessage());
        }
    }

    /**
     * Invalida una sesión por JTI
     */
    private function invalidateStudentSession(PDO $db, string $jwtId): void
    {
        try {
            $query = "UPDATE sesion_estudiante 
                    SET activa = 0 
                    WHERE jwt_jti = :jti";

            $stmt = $db->prepare($query);
            $stmt->bindParam(':jti', $jwtId, PDO::PARAM_STR);
            $stmt->execute();
        } catch (Exception $e) {
            error_log("Error en invalidateStudentSession: " . $e->getMessage());
        }
    }

    /**
     * Invalida una sesión por ID
     */
    private function invalidateStudentSessionById(PDO $db, int $sessionId): void
    {
        try {
            $query = "UPDATE sesion_estudiante 
                    SET activa = 0 
                    WHERE id = :id";

            $stmt = $db->prepare($query);
            $stmt->bindParam(':id', $sessionId, PDO::PARAM_INT);
            $stmt->execute();
        } catch (Exception $e) {
            error_log("Error en invalidateStudentSessionById: " . $e->getMessage());
        }
    }

    /**
     * Genera token JWT para estudiante
     */
    private function generateStudentJwtToken(array $student, string $jwtId): string
    {
        $now = time();
        $expiration = $now + $this->jwtExpiration;

        $payload = [
            'iss' => 'cuestionario-backend',
            'aud' => 'cuestionario-frontend',
            'iat' => $now,
            'exp' => $expiration,
            'jti' => $jwtId, // JWT ID único para rastrear sesión
            'user_type' => 'student',
            'user_id' => (int) $student['id'],
            'rol_user' => $student['rol'],
            'nombre' => $student['nombre'],
            'email' => $student['email'],
            'identificacion' => $student['identificacion'],
            'programa_id' => (int) $student['id_programa'],
            'programa_nombre' => $student['programa_nombre'],
            'campus_nombre' => $student['campus_nombre']
        ];

        return JWT::encode($payload, $this->jwtSecret, 'HS256');
    }

    /**
     * Valida datos de login de estudiante
     */
    private function validateStudentLoginData(array $data): array
    {
        if (!isset($data['email']) || empty(trim($data['email']))) {
            return ['valid' => false, 'message' => 'El email es requerido'];
        }

        if (!isset($data['identificacion']) || empty(trim($data['identificacion']))) {
            return ['valid' => false, 'message' => 'La identificación es requerida'];
        }

        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            return ['valid' => false, 'message' => 'El email no tiene un formato válido'];
        }

        return ['valid' => true, 'message' => 'Datos válidos'];
    }

    /**
     * Formatea datos del estudiante para respuesta
     */
    private function formatStudentData(array $student): array
    {
        return [
            'id' => (int) $student['id'],
            'rol' => $student['rol'],
            'nombre' => $student['nombre'],
            'email' => $student['email'],
            'identificacion' => $student['identificacion'],
            'programa_id' => (int) $student['id_programa'],
            'programa_nombre' => $student['programa_nombre'],
            'campus_nombre' => $student['campus_nombre'],
            'estado' => (bool) $student['estado']
        ];
    }

    /**
     * Obtiene información del cliente
     */
    private function getClientInfo(Request $request): array
    {
        return [
            'ip' => $this->getClientIpAddress($request),
            'user_agent' => $request->getHeaderLine('User-Agent') ?: 'Unknown'
        ];
    }

    /**
     * Obtiene la IP del cliente
     */
    private function getClientIpAddress(Request $request): string
    {
        $serverParams = $request->getServerParams();

        $ipKeys = [
            'HTTP_CF_CONNECTING_IP',
            'HTTP_X_FORWARDED_FOR',
            'HTTP_X_FORWARDED',
            'HTTP_X_CLUSTER_CLIENT_IP',
            'HTTP_FORWARDED_FOR',
            'HTTP_FORWARDED',
            'REMOTE_ADDR'
        ];

        foreach ($ipKeys as $key) {
            if (array_key_exists($key, $serverParams) === true) {
                $ip = $serverParams[$key];
                if (strpos($ip, ',') !== false) {
                    $ip = trim(explode(',', $ip)[0]);
                }
                if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                    return $ip;
                }
            }
        }

        return $serverParams['REMOTE_ADDR'] ?? 'unknown';
    }

    /**
     * Verifica si es el mismo dispositivo
     */
    private function isSameDevice(array $existingSession, array $clientInfo): bool
    {
        return $existingSession['ip_address'] === $clientInfo['ip'] &&
            $existingSession['user_agent'] === $clientInfo['user_agent'];
    }

    /**
     * Genera ID único para JWT
     */
    private function generateJwtId(): string
    {
        return uniqid('jwt_', true) . '_' . bin2hex(random_bytes(8));
    }

    /**
     * Genera token de sesión único
     */
    private function generateSessionToken(): string
    {
        return bin2hex(random_bytes(32));
    }

    /**
     * Limpia sesiones expiradas (método para ejecutar periódicamente)
     */
    public function cleanExpiredSessions(PDO $db): int
    {
        try {
            $query = "UPDATE sesion_estudiante 
                        SET activa = 0 
                        WHERE activa = 1 AND fecha_ultima_actividad < DATE_SUB(NOW(), INTERVAL 8 HOUR)";

            $stmt = $db->prepare($query);
            $stmt->execute();

            return $stmt->rowCount();
        } catch (Exception $e) {
            error_log("Error en cleanExpiredSessions: " . $e->getMessage());
            return 0;
        }
    }
}
