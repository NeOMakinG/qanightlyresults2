<?php
namespace App\Controller;

use Slim\Psr7\Request;
use Slim\Psr7\Response;

class IndexController extends BaseController {
    public function index(Request $request, Response $response):Response {
        return $response;
    }
}
