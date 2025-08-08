<?php
namespace App\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\Controllers\BaseController;
use Psr\Container\ContainerInterface;

use PDO;


class test extends BaseController{

    public function getTesteo(Request $request, Response $response, $args){
        
        $PDO = $this->container->get('db');
        $query = "SELECT * FROM estudiante";
        $stmt = $PDO->prepare($query);
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $response->getBody()->write(json_encode($result));
        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus(200);
    }
}






