<?php
namespace App\Controllers;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Exception;
use PDO;
use Dotenv;

class BaseController{

    protected $container;
    private $dotenv;
    private $jwtSecret;
    public function __construct(ContainerInterface $c){
        $this->container = $c;
        $this->dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../../');
        $this->dotenv->load();
        $this->jwtSecret = $_ENV['JWT_SECRET'];
    }
        /**
     * Obtiene datos JSON del request body
     * 
     * @param Request $request
     * @return array|null Datos decodificados o null si hay error
     */
    public function getJsonInput(Request $request): ?array{
        $body = $request->getBody()->getContents();
        if(empty($body)){
            return null;
        }
        $input = json_decode($body, true);
        if(json_last_error() !== JSON_ERROR_NONE){
            return null;
        }
        return $input;
    }
    /**
	 * Sanitizar entrada de datos
	 * 
	 * @param string $input
	 * @return string
	 */
	public function sanitizeInput(string $input): string
	{
		return htmlspecialchars(strip_tags(trim($input)));
	}

    /**
     * Genera respuesta de éxito en formato JSON
     * 
     * @param Response $response
     * @param string $message Mensaje de éxito
     * @param array $data Datos adicionales
     * @return Response
     */
    public function successResponse(Response $response, string $message, array $data = []): Response
    {
        $responseData = [
            'success' => true,
            'message' => $message,
            'data' => $data,
            'timestamp' => date('Y-m-d H:i:s')
        ];
        
        $response->getBody()->write(json_encode($responseData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        
        return $response
        // ->withHeader('Access-Control-Allow-Origin', '*')
        // ->withHeader('Access-Control-Allow-Origin', 'http://localhost:3000')
            ->withHeader('Content-Type', 'application/json; charset=utf-8')
            ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS')
            ->withHeader('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With')
            ->withHeader('Access-Control-Allow-Credentials', 'true')
            ->withStatus(200);
    }
    
    /**
     * Genera respuesta de error en formato JSON
     * 
     * @param Response $response
     * @param string $message Mensaje de error
     * @param int $statusCode Código de estado HTTP
     * @return Response
     */
    public function errorResponse(Response $response, string $message, int $statusCode = 400): Response
    {
        $responseData = [
            'success' => false,
            'message' => $message,
            'error' => true,
            'status_code' => $statusCode,
            'timestamp' => date('Y-m-d H:i:s')
        ];
        
        $response->getBody()->write(json_encode($responseData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        
        return $response
        // ->withHeader('Access-Control-Allow-Origin', '*')
        // ->withHeader('Access-Control-Allow-Origin', 'http://localhost:3000')
            ->withHeader('Content-Type', 'application/json; charset=utf-8')
            ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS')
            ->withHeader('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With')
            ->withHeader('Access-Control-Allow-Credentials', 'true')
            ->withStatus($statusCode);
    }


    /**
     * Obtiene datos del usuario desde el token
     * 
     * @param Request $request
     * @return array|null Datos del usuario o null si no existe
     */
    public function getUserDataFromToken(Request $request): ?array{
        try{
            $token = $this->getBearerToken($request);
            if(!$token){return null;}
            $decoded = JWT::decode($token, new Key($this->jwtSecret, 'HS256'));
            $userData = (array) $decoded;
            return $userData;
        }catch(Exception $e){
            error_log("Error al obtener datos del usuario desde el token: " . $e->getMessage());
            return null;
        }
    }
    
    public function getUserIdFromToken(Request $request): ?int
	{
		try {
			$token = $this->getBearerToken($request);
			
			if (!$token) {
				return null;
			}
			
			$decoded = JWT::decode($token, new Key($this->jwtSecret, 'HS256'));
			$userData = (array) $decoded;
			
			return isset($userData['user_id']) ? (int) $userData['user_id'] : null;
			
		} catch (Exception $e) {
			error_log("Error al decodificar token: " . $e->getMessage());
			return null;
		}
	}
	
	/**
	 * Obtiene el token Bearer del header Authorization
	 * 
	 * @param Request $request
	 * @return string|null Token o null si no existe
	 */
	public function getBearerToken(Request $request): ?string
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

    public function getProgramas(Request $request, Response $response, array $args): Response{
        try{
            $db = $this->container->get('db');
            $user_id = $this->getUserIdFromToken($request);
            if(!$user_id){
                return $this->errorResponse($response, 'Usuario no autenticado', 401);
            }
            $userData = $this->getUserDataFromToken($request);
            if(isset($userData['programa_id'])){
                $programa_id = $userData['programa_id'];
                $sql_get_programas = "SELECT id, nombre FROM programa WHERE id = :programa_id ORDER BY nombre";
                $stmt = $db->prepare($sql_get_programas);
                $stmt->bindParam(':programa_id', $programa_id, PDO::PARAM_INT);
            }else{
                $sql_get_programas = "SELECT id, nombre FROM programa ORDER BY nombre";
                $stmt = $db->prepare($sql_get_programas);
            }
            if($stmt->execute()){
                $programas = $stmt->fetchAll(PDO::FETCH_ASSOC);
                if(count($programas) > 1){
                    return $this->successResponse($response, 'Programas obtenidos correctamente', [
                        'programas' => $programas,
                    ]);
                }else{
                    return $this->successResponse($response, 'Programa obtenido', [
                        'programa' => $programas
                    ]);
                }
            }else{
                return $this->errorResponse($response, 'Error al obtener los programas', 500);
            }
        }catch(Exception $e){
            return $this->errorResponse($response, 'Error al obtener los programas: ' . $e->getMessage(), 500);
        }
    }
}

