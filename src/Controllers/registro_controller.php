<?php
namespace App\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\Controllers\BaseController;
use Psr\Container\ContainerInterface;
use PDO;
use Exception;
use DateTime;

class registro_controller extends BaseController{

    public function __construct(ContainerInterface $c)
	{
		parent::__construct($c);
	}

    public function registrar(Request $request, Response $response, array $args): Response{
        $db = null;
        $stmt = null;
        $stmt_insertar = null;
        try{
            $db = $this->container->get('db');
            $inputData = $this->getJsonInput($request);
            if(!$inputData){return $this->errorResponse($response, 'Datos JSON inválidos', 400);}
            $nombre = $this->sanitizeInput($inputData['nombre']);
            $email = $this->sanitizeInput($inputData['email']);
            $identificacion = $this->sanitizeInput($inputData['identificacion']);
            $password = $this->sanitizeInput($inputData['password']);
            $confirmar_password = $this->sanitizeInput($inputData['confirmar_password']);
            $transversal = $inputData['transversal'];
            if($transversal == true){
                $programa_id = null;
            }else{
                $programa_id = $this->sanitizeInput($inputData['programa_id']);
            }
            if($password !== $confirmar_password){
                return $this->errorResponse($response, 'Las contraseñas no coinciden', 400);
            } elseif(strlen($password) < 8){
                return $this->errorResponse($response, 'La contraseña debe tener al menos 8 caracteres', 400);
            }
            
            $query_verificar = "SELECT id FROM docente WHERE identificacion = :identificacion";
            $stmt = $db->prepare($query_verificar);
            $stmt->bindParam(':identificacion', $identificacion);
            $stmt->execute();
            if($stmt->rowCount() > 0){
                return $this->errorResponse($response, 'El identificación ya está registrada', 400);
            }
            $password_hash = password_hash($password, PASSWORD_DEFAULT);
            $query_insertar = "INSERT INTO docente (nombre, email, identificacion, password, programa_id) VALUES (:nombre, :email, :identificacion, :password, :programa_id)";
            $stmt_insertar = $db->prepare($query_insertar);
            $stmt_insertar->bindParam(':nombre', $nombre);
            $stmt_insertar->bindParam(':email', $email);
            $stmt_insertar->bindParam(':identificacion', $identificacion);
            $stmt_insertar->bindParam(':password', $password_hash);
            $stmt_insertar->bindParam(':programa_id', $programa_id);
            $stmt_insertar->execute();
            return $this->successResponse($response, 'Usuario registrado exitosamente', [
                'user' => [
                    'nombre' => $nombre,
                    'email' => $email,
                    'identificacion' => $identificacion,
                    'programa_id' => $programa_id
                ]
            ]);
        }catch(Exception $e){
            return $this->errorResponse($response, 'Error al registrar el usuario', 500);
        }finally{
            if($stmt !== null){$stmt = null;}
            if($db !== null){$db = null;}
            if($stmt_insertar !== null){$stmt_insertar = null;}
        }
    }
}