<?php
namespace App\Controller;

use App\Model\Execution;
use DI\NotFoundException;
use Illuminate\Database\Capsule\Manager;
use Slim\Psr7\Request;
use Slim\Psr7\Response;

class GraphController extends BaseController {

    public function index(Request $request, Response $response):Response {
        return $response;
    }
}
