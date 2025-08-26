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

class genInformes_controller extends BaseController{
    private $jwtSecret;
    public function __construct(ContainerInterface $c){
        parent::__construct($c);
        $this->jwtSecret = $_ENV['JWT_SECRET'];
    }

    
}