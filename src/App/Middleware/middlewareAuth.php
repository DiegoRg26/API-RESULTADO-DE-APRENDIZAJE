<?php
namespace App\Middleware;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Psr\Http\Server\MiddlewareInterface;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Exception;
use Dotenv;
use Slim\Psr7\Response as SlimResponse;

class middlewareAuth implements MiddlewareInterface
{
	private $dotenv;
	private $jwtSecret;
	private $publicEndpoints;

	public function __construct(){
		// Cargar variables de entorno
		$this->dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../../../');
		$this->dotenv->load();
		$this->jwtSecret = $_ENV['JWT_SECRET'];

		// Endpoints públicos que no requieren autenticación
		$this->publicEndpoints = [
			'/api/auth',
			'/api/auth/register/programas', 
			'/api/estudiante/login',
			'/api/test',
			'/',
			'/test'
		];
	}

	/**
	 * Procesa el middleware de autenticación (método requerido por MiddlewareInterface)
	 */
	public function process(Request $request, RequestHandler $handler): Response
	{
		$uri = $request->getUri()->getPath();
		$method = $request->getMethod();

		// Permitir OPTIONS requests (CORS preflight)
		if ($method === 'OPTIONS') {
			return $this->createCorsResponse();
		}

		// Verificar si es un endpoint público
		if ($this->isPublicEndpoint($uri)) {
			return $handler->handle($request);
		}

		// Validar token JWT
		$token = $this->getBearerToken($request);
		
		if (!$token) {
			return $this->createErrorResponse('Token de autenticación requerido', 401);
		}

		try {
			// Decodificar y validar el token
			$decoded = JWT::decode($token, new Key($this->jwtSecret, 'HS256'));
			$userData = (array) $decoded;

			// Verificar que el token contenga los datos necesarios
			if (!isset($userData['user_id'])) {
				return $this->createErrorResponse('Token inválido: datos de usuario faltantes', 401);
			}

			// Agregar los datos del usuario al request para uso posterior
			$request = $request->withAttribute('user_data', $userData);
			$request = $request->withAttribute('user_id', $userData['user_id']);

			// Continuar con el siguiente middleware/controlador
			return $handler->handle($request);

		} catch (Exception $e) {
			error_log("Error en middleware de autenticación: " . $e->getMessage());
			return $this->createErrorResponse('Token inválido o expirado', 401);
		}
	}

	/**
	 * Obtiene el token Bearer del header Authorization
	 */
	private function getBearerToken(Request $request): ?string
	{
		$authHeader = $request->getHeaderLine('Authorization');
		
		if (empty($authHeader)) {
			return null;
		}
		
		if (strpos($authHeader, 'Bearer ') !== 0) {
			return null;
		}
		
		return substr($authHeader, 7); // Remover 'Bearer '
	}

	/**
	 * Verifica si el endpoint es público (no requiere autenticación)
	 */
	private function isPublicEndpoint(string $uri): bool
	{
		foreach ($this->publicEndpoints as $endpoint) {
			if ($uri === $endpoint || strpos($uri, $endpoint) === 0) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Crea una respuesta de error en formato JSON
	 */
	private function createErrorResponse(string $message, int $statusCode = 401): Response
	{
		$responseData = [
			'success' => false,
			'message' => $message,
			'error' => true,
			'status_code' => $statusCode,
			'timestamp' => date('Y-m-d H:i:s')
		];

		$response = new SlimResponse();
		$response->getBody()->write(json_encode($responseData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
		
		return $response
			->withHeader('Content-Type', 'application/json; charset=utf-8')
			->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS')
			->withHeader('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With')
			->withHeader('Access-Control-Allow-Credentials', 'true')
			->withStatus($statusCode);
	}

	/**
	 * Crea una respuesta CORS para requests OPTIONS
	 */
	private function createCorsResponse(): Response
	{
		$response = new SlimResponse();
		
		return $response
			->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS')
			->withHeader('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With')
			->withHeader('Access-Control-Allow-Credentials', 'true')
			->withStatus(200);
	}

	/**
	 * Agrega endpoints públicos adicionales
	 */
	public function addPublicEndpoint(string $endpoint): void
	{
		if (!in_array($endpoint, $this->publicEndpoints)) {
			$this->publicEndpoints[] = $endpoint;
		}
	}

	/**
	 * Remueve un endpoint de la lista de endpoints públicos
	 */
	public function removePublicEndpoint(string $endpoint): void
	{
		$key = array_search($endpoint, $this->publicEndpoints);
		if ($key !== false) {
			unset($this->publicEndpoints[$key]);
		}
	}

	/**
	 * Obtiene la lista de endpoints públicos
	 */
	public function getPublicEndpoints(): array
	{
		return $this->publicEndpoints;
	}
}