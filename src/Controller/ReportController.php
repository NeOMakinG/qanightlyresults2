<?php
namespace App\Controller;

use App\Model\Execution;
use DI\NotFoundException;
use Illuminate\Database\Capsule\Manager;
use Slim\Psr7\Request;
use Slim\Psr7\Response;

class ReportController extends BaseController {

    public function index(Request $request, Response $response):Response {
        //get all data from GCP
        $GCP_files_list = [];
        $gcp_url = getenv('QANB_GCPURL');
        $GCPcallresult = file_get_contents($gcp_url);
        if ($GCPcallresult) {
            $xml = new \SimpleXMLElement($GCPcallresult);
            foreach ($xml->Contents as $content) {
                $build_name = (string)$content->Key;
                if (strpos($build_name, '.zip') !== false) {
                    //get version and date
                    preg_match('/([0-9]{4}-[0-9]{2}-[0-9]{2})-([A-z0-9\.]*)-prestashop_(.*)\.zip/', $build_name, $matches_filename);
                    if (count($matches_filename) == 4) {
                        $date = $matches_filename[1];
                        $version = $matches_filename[2];
                        $GCP_files_list[$date][$version] = $build_name;
                    }

                }
            }
        }
        //get all data from executions
        $execution = new Execution();
        $executions = $execution->getAll();

        $full_list = [];
        foreach($executions as $execution) {
            $download = null;
            if (isset($GCP_files_list[date('Y-m-d', strtotime($execution->start_date))][$execution->version])) {
                $download = $gcp_url.$GCP_files_list[date('Y-m-d', strtotime($execution->start_date))][$execution->version];
            }
            $full_list[] = [
                'id' => $execution->id,
                'date' => date('Y-m-d', strtotime($execution->start_date)),
                'version' => $execution->version,
                'start_date' => $execution->start_date,
                'end_date' => $execution->end_date,
                'duration' => $execution->duration,
                'suites' => $execution->suites,
                'tests' => [
                    'total' => ($execution->tests),
                    'passed' => $execution->passes,
                    'failed' => $execution->failures,
                    'pending' => $execution->pending,
                    'skipped' => $execution->skipped,
                ],
                'download' => $download
            ];
        }
        $response->getBody()->write(json_encode($full_list));
        return $response;
    }

    public function report(Request $request, Response $response):Response {
        $route = $request->getAttribute('route');
        $report_id = $route->getArgument('report');
        //get all the data for this report
        $execution = Manager::table('execution')->find($report_id);

        $execution_data = [
            'id' => $execution->id,
            'date' => date('Y-m-d', strtotime($execution->start_date)),
            'version' => $execution->version,
            'start_date' => $execution->start_date,
            'end_date' => $execution->end_date,
            'duration' => $execution->duration,
            'suites' => $execution->suites,
            'tests' => $execution->tests,
            'skipped' => $execution->skipped,
            'pending' => $execution->pending,
            'passes' => $execution->passes,
            'failures' => $execution->failures
        ];

        if (!$execution) {
            //error
            throw new NotFoundException('Report not found');
        }
        //get suite data
        $suites = Manager::table('suite')
            ->where('execution_id', '=', $report_id)
            ->orderBy('id')
            ->get();
        //get tests data
        $tests = Manager::table('test')
            ->join('suite', 'test.suite_id', '=', 'suite.id')
            ->where('suite.execution_id', '=', $report_id)
            ->select('test.*')
            ->get();
        $tests_data = [];
        foreach($tests as $test) {
            $tests_data[$test->suite_id][] = $test;
        }
        //find the first suite ID
        $first_id = null;
        foreach($suites as $suite) {
            if ($suite->parent_id == null) {
                $first_id = $suite->id;
            }
        }
        //build the recursive tree
        $suites = $this->buildTree($suites, $tests_data, $first_id);
        //put suites data into the final object
        $execution_data['suites_data'] = $suites;
        $response->getBody()->write(json_encode($execution_data));
        return $response;
    }

    public function suite(Request $request, Response $response):Response {
        $route = $request->getAttribute('route');
        $report_id = $route->getArgument('report');
        $suite_id = $route->getArgument('suite');

        $suites = Manager::table('suite')
            ->where('execution_id', '=', $report_id)
            ->orderBy('id')
            ->get();
        //get tests data
        $tests = Manager::table('test')
            ->join('suite', 'test.suite_id', '=', 'suite.id')
            ->where('suite.execution_id', '=', $report_id)
            ->select('test.*')
            ->get();
        $tests_data = [];
        foreach($tests as $test) {
            $tests_data[$test->suite_id][] = $test;
        }
        //build the recursive tree
        $suites = $this->buildTree($suites, $tests_data, $suite_id);
        //put suites data into the final object
        $execution_data['suites_data'] = $suites;
        $response->getBody()->write(json_encode($execution_data));
        return $response;
    }

    private function buildTree($suites, $tests_data, $parent_id = null) {
        $branch = array();
        foreach ($suites as &$suite) {
            if ($suite->hasTests == 1 && isset($tests_data[$suite->id])) {
                $suite->tests = $tests_data[$suite->id];
            }
            if ($suite->parent_id == $parent_id) {
                $children = $this->buildTree($suites, $tests_data, $suite->id);
                if ($children) {
                    $suite->suites = $children;
                }
                $branch[$suite->id] = $suite;
                unset($suite);
            }
        }
        return $branch;
    }
}
