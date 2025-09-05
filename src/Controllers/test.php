<?php
namespace App\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\Controllers\BaseController;
use Psr\Container\ContainerInterface;
use Exception;


class test extends BaseController{

    public function getTesteo(Request $request, Response $response, $args){
        
        try{
            //tabla de multiplicar del 7 del 1 al 10
            $numero = 7;
            $mensage = "";
            for($i = 1; $i <= 10; $i++){
                $mensage .= $numero . " x " . $i . " = " . ($numero * $i) . "\n";
            }
            return $this->successResponse($response, $mensage);
        }catch(Exception $e){
            return $this->errorResponse($response, $e->getMessage(), 500);
        }
    }
}






