<?php
namespace App\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\Controllers\BaseController;
use Psr\Container\ContainerInterface;
use PDO;
use Exception;

class raNiveles_controller extends BaseController{
    public function __construct(ContainerInterface $c){
        parent::__construct($c);
    }

    public function createRa(Request $request, Response $response, Array $args): Response{
        $db = null;
        $stmt = null;
        try{
            $user_id = $this->getUserIdFromToken($request);
            if(!$user_id){return $this->errorResponse($response, 'Usuario no autenticado', 401);}
            $inputData = $this->getJsonInput($request);
            $db = $this->container->get('db');
            if(!$inputData){return $this->errorResponse($response, 'Datos JSON inválidos', 400);}

            $programa_id = $this->getUserDataFromToken($request)['programa_id'];
            $cuestionario_id = $inputData['cuestionario_id'];
            $abreviatura = $inputData['abreviatura'];
            $descripcion = $inputData['descripcion'];
            $contador_nivel = 0; // Inicializamos el contador en 1
            foreach($inputData['niveles'] as $nivel){
                $nivel_puntaje_minimo = $nivel['puntaje_min'];
                $nivel_puntaje_maximo = $nivel['puntaje_max'];
                $nivel_indicadores = $nivel['indicadores'];

                $sql_ra = "INSERT INTO ra_niveles_indicadores (cuestionario_id, abreviatura, descripcion, programa_id, puntaje_min, puntaje_max, nivel, indicadores) 
                            VALUES (:cuestionario_id, :abreviatura, :descripcion, :programa_id, :puntaje_min, :puntaje_max, :nivel, :indicadores)";
                $stmt = $db->prepare($sql_ra);
                $stmt->bindParam(':cuestionario_id', $cuestionario_id);
                $stmt->bindParam(':abreviatura', $abreviatura);
                $stmt->bindParam(':descripcion', $descripcion);
                $stmt->bindParam(':programa_id', $programa_id);
                $stmt->bindParam(':puntaje_min', $nivel_puntaje_minimo);
                $stmt->bindParam(':puntaje_max', $nivel_puntaje_maximo);
                $stmt->bindParam(':nivel', $contador_nivel);
                $contador_nivel++; // Incrementamos el contador para la siguiente iteración
                $stmt->bindParam(':indicadores', $nivel_indicadores);
                $stmt->execute();
            }
            return $this->successResponse($response, 'Niveles creados correctamente');
            
        }catch(Exception $e){
            return $this->errorResponse($response, 'Error al crear los niveles' . $e->getMessage(), 500);
        }finally{
            if($db !== null){$db = null;}
            if($stmt !== null){$stmt = null;}
        }
    }

    public function getRa(Request $request, Response $response, Array $args): Response{
        $db = null;
        $stmt = null;
        try{
            $user_id = $this->getUserIdFromToken($request);
            if(!$user_id){return $this->errorResponse($response, 'Usuario no autenticado', 401);}
            $cuestionario_id = $args['cuestionario_id'];
            // $inputData = $this->getJsonInput($request);
            // if(!$inputData){return $this->errorResponse($response, 'Datos JSON inválidos', 400);}
            $db = $this->container->get('db');
            $sql_get_ra = "SELECT * FROM ra_niveles_indicadores WHERE cuestionario_id = :cuestionario_id";
            $stmt = $db->prepare($sql_get_ra);
            $stmt->bindParam(':cuestionario_id', $cuestionario_id);
            $stmt->execute();
            $ra = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if(count($ra) > 0){
                return $this->successResponse($response, 'Ra obtenida correctamente', [
                    'ra' => $ra
                ]);
            }else{
                return $this->errorResponse($response, 'No se encontro ra', 404);
            }
        }catch(Exception $e){
            return $this->errorResponse($response, 'Error al obtener el ra' . $e->getMessage(), 500);
        }finally{
            if($db !== null){$db = null;}
            if($stmt !== null){$stmt = null;}
        }
    }

    public function updateRa(Request $request, Response $response, Array $args): Response{
        $db = null;
        $stmt = null;
        try{
            $user_id = $this->getUserIdFromToken($request);
            if(!$user_id){return $this->errorResponse($response, 'Usuario no autenticado', 401);}
            $inputData = $this->getJsonInput($request);
            if(!$inputData){return $this->errorResponse($response, 'Datos JSON inválidos', 400);}
            $db = $this->container->get('db');
            $programa_id = $this->getUserDataFromToken($request)['programa_id'];
            $cuestionario_id = $inputData['cuestionario_id'];
            $abreviatura = $inputData['abreviatura'];
            $descripcion = $inputData['descripcion'];
            foreach($inputData['niveles'] as $nivel){
                $nivel_puntaje_minimo = $nivel['puntaje_min'];
                $nivel_puntaje_maximo = $nivel['puntaje_max'];
                $nivel_indicadores = $nivel['indicadores'];
                
                $sql_ra = "UPDATE ra_niveles_indicadores 
                            SET abreviatura = :abreviatura, descripcion = :descripcion, puntaje_min = :puntaje_min, puntaje_max = :puntaje_max, indicadores = :indicadores 
                            WHERE cuestionario_id = :cuestionario_id AND nivel = :nivel";
                $stmt = $db->prepare($sql_ra);
                $stmt->bindParam(':abreviatura', $abreviatura);
                $stmt->bindParam(':descripcion', $descripcion);
                $stmt->bindParam(':puntaje_min', $nivel_puntaje_minimo);
                $stmt->bindParam(':puntaje_max', $nivel_puntaje_maximo);
                $stmt->bindParam(':indicadores', $nivel_indicadores);
                $stmt->bindParam(':cuestionario_id', $cuestionario_id);
                $stmt->bindParam(':nivel', $nivel['nivel']);
                $stmt->execute();
            }
            return $this->successResponse($response, 'RA actualizado correctamente');
        }catch(Exception $e){
            return $this->errorResponse($response, 'Error al actualizar el RA' . $e->getMessage(), 500);
        }finally{
            if($db !== null){$db = null;}
            if($stmt !== null){$stmt = null;}
        }
    }

}