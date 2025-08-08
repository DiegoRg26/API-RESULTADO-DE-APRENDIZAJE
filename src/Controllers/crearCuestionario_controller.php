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


class crearCuestionario_controller extends BaseController{
    private $jwtSecret;

    public function __construct(ContainerInterface $c)
	{
		parent::__construct($c);
		$this->jwtSecret = $_ENV['JWT_SECRET'];
	}

    /**
	 * Obtiene los programas disponibles para el docente
	 * GET /api/crearCuestionario/programas-disponibles
	 * 
	 * @param Request $request
	 * @param Response $response
	 * @param array $args
	 * @return Response
	 */
    public function getProgramasDisponibles(Request $request, Response $response, array $args): Response{
        $stmt_programas = null;
        $db = null;
        try{
            $inputData = $this->getJsonInput($request);
            $db = $this->container->get('db');
            if(!$inputData){
                return $this->errorResponse($response, 'Datos JSON inválidos', 400);
            }
            if(isset($inputData['programa_id'])){
                $programa_id = $this->sanitizeInput($inputData['programa_id']);
                $query_programas = "SELECT p.id, p.nombre, n.nombre as nivel_nombre, n.puntaje_maximo as nivel_puntaje_maximo, cam.nombre as campus_nombre 
                            FROM programa p 
                            JOIN nivel n ON p.id_nivel = n.id 
                            JOIN campus cam ON p.id_campus = cam.id
                            WHERE p.id = :programa_id";
                $stmt_programas = $db->prepare($query_programas);
                $stmt_programas->bindParam(':programa_id', $programa_id);
                $stmt_programas->execute();
                $programas = $stmt_programas->fetchAll(PDO::FETCH_ASSOC);
                return $this->successResponse($response, 'Programas disponibles obtenidos exitosamente', [
                    'programas' => $programas
                ]);
            }else{
                $query_programas = "SELECT p.id, p.nombre, n.nombre as nivel_nombre, n.puntaje_maximo as nivel_puntaje_maximo, cam.nombre as campus_nombre 
                            FROM programa p 
                            JOIN nivel n ON p.id_nivel = n.id 
                            JOIN campus cam ON p.id_campus = cam.id";
                $stmt_programas = $db->prepare($query_programas);
                $stmt_programas->execute();
                $programas = $stmt_programas->fetchAll(PDO::FETCH_ASSOC);

                return $this->successResponse($response, 'Programas disponibles obtenidos exitosamente', [
                    'programas' => $programas
                ]);
            }
        }catch(Exception $e){
            error_log("Error en getProgramasDisponibles: " . $e->getMessage());
            return $this->errorResponse($response, 'Error interno del servidor', 500);
        }finally{
            if($stmt_programas !== null){
                $stmt_programas = null;
            }
            if($db !== null){
                $db = null;
            }
        }
    }
    /**
     * Crea un nuevo cuestionario
     * POST /api/crearCuestionario
     * 
     * @param Request $request
     * @param Response $response
     * @param array $args
     * @return Response
     */

    public function crearCuestionario(Request $request, Response $response, Array $args): Response{
        $db = null;
        $stmt_insert_cuestionario = null;
        $stmt_insert_relacion = null;
        try{
            $inputData = $this->getJsonInput($request);
            $db = $this->container->get('db');
            if(!$inputData){
                return $this->errorResponse($response, 'Datos JSON inválidos', 400);
            }
            $validation = $this->validateCuestionarioData($inputData);
            if(!$validation['valid']){
                return $this->errorResponse($response, $validation['message'], 400);
            }
            $db->beginTransaction();
            $titulo = $this->sanitizeInput($inputData['titulo']);
            $descripcion = $this->sanitizeInput($inputData['descripcion']);
            $tiempo_limite = $inputData['tiempo_limite'];
            // $tiempo_limite = $tiempo_limite * 60;
            $docenteData = $this->getUserDataFromToken($request);
            $docente_id = $docenteData['user_id'];
            $docente_programa_id = $docenteData['programa_id'];

            // 1. Insertar en la tabla cuestionario
            $query_insert_cuestionario = "INSERT INTO cuestionario (titulo, descripcion, tiempo_limite) VALUES (:titulo, :descripcion, :tiempo_limite)";
            $stmt_insert_cuestionario = $db->prepare($query_insert_cuestionario);
            $stmt_insert_cuestionario->bindParam(':titulo', $titulo);
            $stmt_insert_cuestionario->bindParam(':descripcion', $descripcion);
            $stmt_insert_cuestionario->bindParam(':tiempo_limite', $tiempo_limite);
            if(!$stmt_insert_cuestionario->execute()){
                return $this->errorResponse($response, 'Error al crear el cuestionario', 500);
            }

            $cuestionario_id = $db->lastInsertId();

            //Si el docente no tiene un programa asignado, se usa el programa que venga en el JSON
            if($docente_programa_id == null){
                $docente_programa_id = $inputData['programa_id'];
            }

            //2. Crear relacion cuestionario-programa
            $query_insert_relacion = "INSERT INTO relacion_cuestionario_programa (id_cuestionario, id_programa, id_docente, activo) 
                            VALUES (:id_cuestionario, :id_programa, :id_docente, 1)";
            $stmt_insert_relacion = $db->prepare($query_insert_relacion);
            $stmt_insert_relacion->bindParam(':id_cuestionario', $cuestionario_id);
            $stmt_insert_relacion->bindParam(':id_programa', $docente_programa_id);
            $stmt_insert_relacion->bindParam(':id_docente', $docente_id);
            if(!$stmt_insert_relacion->execute()){
                return $this->errorResponse($response, 'Error al crear la relacion cuestionario-programa', 500);
            }
            $relacion_id = $db->lastInsertId();
            $db->commit();
            return $this->successResponse($response, 'Cuestionario creado correctamente', [
                'cuestionario_id' => $cuestionario_id,
                'relacion_id' => $relacion_id
            ]);
        }catch(Exception $e){
            error_log("Error en crearCuestionario: " . $e->getMessage());
            $db->rollBack();
            return $this->errorResponse($response, 'Error interno del servidor', 500);
        }finally{
            if($stmt_insert_cuestionario !== null){
                $stmt_insert_cuestionario = null;
            }
            if($stmt_insert_relacion !== null){
                $stmt_insert_relacion = null;
            }
            if($db !== null){
                $db = null;
            }
        }
    }

    public function anexarPreguntasAndOpciones(Request $request, Response $response, Array $args): Response{
        $db = null;
        $stmt_insert_pregunta = null;
        $stmt_insert_opcion = null;
        try{
            $inputData = $this->getJsonInput($request);
            $db = $this->container->get('db');
            if(!$inputData){return $this->errorResponse($response, 'Datos JSON inválidos', 400);}
            $cuestionario_id = $args['id'];
            $preguntas = $inputData['preguntas'];
            $db->beginTransaction();
            foreach($preguntas as $index => $pregunta_data){
                if(!empty($pregunta_data['texto_pregunta'])){
                    $peso_pregunta = isset($pregunta_data['peso']) ? floatval($pregunta_data['peso']) : 1.00;

                    //Procesar imagen de la pregunta

                    $imagen_pregunta = null;
                    $nombre_imagen_pregunta = null;

                    if(isset($_FILES['preguntas']['name'][$index]['imagen']) && !empty($_FILES['preguntas']['name'][$index]['imagen'])){
                        $file_name = $_FILES['preguntas']['name'][$index]['imagen'];
                        $file_tmp = $_FILES['preguntas']['tmp_name'][$index]['imagen'];
                        $file_type = $_FILES['preguntas']['type'][$index]['imagen'];
                        $file_size = $_FILES['preguntas']['size'][$index]['imagen'];
                        if(strpos($file_type, 'image/') === 0){
                            $imagen_pregunta = file_get_contents($file_tmp);
                            $nombre_imagen_pregunta = $file_name;
                        }     
                    }
                    $query_insert_pregunta = "INSERT INTO preguntas (id_cuestionario, texto_pregunta, orden_pregunta, peso_pregunta, imagen_pregunta, nombre_imagen_pregunta) 
                                                VALUES (:id_cuestionario, :texto_pregunta, :orden_pregunta, :peso_pregunta, :imagen_pregunta, :nombre_imagen_pregunta)";
                    $stmt_insert_pregunta = $db->prepare($query_insert_pregunta);
                    $stmt_insert_pregunta->bindParam(':id_cuestionario', $cuestionario_id);
                    $stmt_insert_pregunta->bindParam(':texto_pregunta', $pregunta_data['texto_pregunta']);
                    $stmt_insert_pregunta->bindParam(':orden_pregunta', $index);
                    $stmt_insert_pregunta->bindParam(':peso_pregunta', $peso_pregunta);
                    $stmt_insert_pregunta->bindParam(':imagen_pregunta', $imagen_pregunta, PDO::PARAM_LOB);
                    $stmt_insert_pregunta->bindParam(':nombre_imagen_pregunta', $nombre_imagen_pregunta);
                    if(!$stmt_insert_pregunta->execute()){
                        $db->rollBack();
                        return $this->errorResponse($response, 'Error al insertar la pregunta', 500);
                    }

                    $pregunta_id = $db->lastInsertId();

                    //Insertar opciones
                    if(!empty($pregunta_data['opciones'])){
                        foreach($pregunta_data['opciones'] as $opcion_index => $opcion_data){
                            if(!empty($opcion_data['texto'])){
                                $es_correcta = (isset($pregunta_data['correcta']) && $pregunta_data['correcta'] == $opcion_index) ? 1 : 0;
                                //procesar imagen de la opcion
                                $imagen_opcion = null;
                                $nombre_imagen_opcion = null;
                                if(isset($_FILES['preguntas']['name'][$index]['opciones'][$opcion_index]['imagen']) && !empty($_FILES['preguntas']['name'][$index]['opciones'][$opcion_index]['imagen'])){
                                    $file_name = $_FILES['preguntas']['name'][$index]['opciones'][$opcion_index]['imagen'];
                                    $file_tmp = $_FILES['preguntas']['tmp_name'][$index]['opciones'][$opcion_index]['imagen'];
                                    $file_type = $_FILES['preguntas']['type'][$index]['opciones'][$opcion_index]['imagen'];
                                    //validar que sea una imagen
                                    if(strpos($file_type, 'image/') === 0){
                                        $imagen_opcion = file_get_contents($file_tmp);
                                        $nombre_imagen_opcion = $file_name;
                                    }
                                }
                                $query_insert_opcion = "INSERT INTO opcion_respuesta (id_pregunta, texto_opcion, opcion_correcta, orden, imagen_opcion, nombre_imagen_opcion) 
                                            VALUES (:id_pregunta, :texto_opcion, :opcion_correcta, :orden, :imagen_opcion, :nombre_imagen_opcion)";
                                $stmt_insert_opcion = $db->prepare($query_insert_opcion);
                                $stmt_insert_opcion->bindParam(':id_pregunta', $pregunta_id);
                                $stmt_insert_opcion->bindParam(':texto_opcion', $opcion_data['texto']);
                                $stmt_insert_opcion->bindParam(':opcion_correcta', $es_correcta);
                                $stmt_insert_opcion->bindParam(':orden', $opcion_index);
                                $stmt_insert_opcion->bindParam(':imagen_opcion', $imagen_opcion, PDO::PARAM_LOB);
                                $stmt_insert_opcion->bindParam(':nombre_imagen_opcion', $nombre_imagen_opcion);
                                if(!$stmt_insert_opcion->execute()){
                                    $db->rollBack();
                                    return $this->errorResponse($response, 'Error al insertar la opcion', 500);
                                }
                            }
                        }                   
                    }
                }
            }
            $db->commit();
            return $this->successResponse($response, 'Preguntas y opciones anexadas correctamente', [
                'cuestionario_id' => $cuestionario_id
            ]);
        }catch(Exception $e){
            error_log("Error en anexarPreguntas: " . $e->getMessage());
            $error_message = $e->getMessage();
            return $this->errorResponse($response, $error_message, 500);
        }finally{
            if($stmt_insert_pregunta !== null){
                $stmt_insert_pregunta = null;
            }
            if($stmt_insert_opcion !== null){
                $stmt_insert_opcion = null;
            }
            if($db !== null){
                $db = null;
            }
        }
    }


    private function validateCuestionarioData(array $data): array{
        if(!isset($data['titulo']) || empty(trim($data['titulo']))){
            return ['valid' => false, 'message' => 'El titulo del cuestionario es requerido'];
        }
        if(!isset($data['descripcion']) || empty(trim($data['descripcion']))){
            return ['valid' => false, 'message' => 'La descripcion del cuestionario es requerida'];
        }
        if(!isset($data['programa_id']) || empty(trim($data['programa_id']))){
            return ['valid' => false, 'message' => 'El programa del cuestionario es requerido'];
        }
        return ['valid' => true, 'message' => 'Datos validos'];
    }
}