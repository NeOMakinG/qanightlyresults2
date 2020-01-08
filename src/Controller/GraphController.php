<?php
namespace App\Controller;

use App\Model\Execution;
use DI\NotFoundException;
use Illuminate\Database\Capsule\Manager;
use Slim\Psr7\Request;
use Slim\Psr7\Response;

class GraphController extends BaseController {

    public function index(Request $request, Response $response):Response {
        //possible values
        $period_possible_values = ['last_month', 'last_two_months', 'last_year'];
        $versions_possible_values = ['develop'];
        $versions_possible_values_from_base = Manager::table('execution')
            ->select('version')
            ->groupBy('version')
            ->get();
        if($versions_possible_values_from_base) {
            foreach($versions_possible_values_from_base as $v) {
                if (!isset($versions_possible_values[$v->version])) {
                    $versions_possible_values[] = $v->version;
                }
            }
        }
        //default values
        $period = $period_possible_values[0];
        $version = $versions_possible_values[0];
        //check GET values
        $get_query_params = $request->getQueryParams();
        if (isset($get_query_params['period']) && in_array($get_query_params['period'], $period_possible_values)) {
            $period = $get_query_params['period'];
        }
        if (isset($get_query_params['version']) && in_array($get_query_params['version'], $versions_possible_values)) {
            $version = $get_query_params['version'];
        }
        //get the data

        return $response;
    }
}
