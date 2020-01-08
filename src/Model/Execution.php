<?php
namespace App\Model;

use Illuminate\Database\Capsule\Manager as DB;

class Execution {

    public static function getAll($periodInDays = 30) {
        return DB::select("
        SELECT 
            `id`, 
            `filename`, 
            `ref`, 
            `start_date`, 
            `end_date`, 
            `duration`, 
            `version`, 
            `suites`, 
            `tests`, 
            `skipped`, 
            `passes`, 
            `failures`,
            `pending` 
        FROM `execution` 
        WHERE start_date > DATE_ADD(NOW(), INTERVAL -:days DAY)
        ORDER BY DATE(start_date) DESC;", ['days' => $periodInDays]);
    }

    public static function getGraphData($period, $version) {
        switch ($period) {
            case 'last_two_months':
                $period_sql = 60;
            case 'last_year':
                $period_sql = 365;
            default:
                $period_sql = 30;
        }

        return DB::select("
        SELECT 
            `id`, 
            `start_date`, 
            `end_date`, 
            `version`, 
            `suites`, 
            `tests`, 
            `skipped`, 
            `passes`, 
            `failures`,
            `pending` 
        FROM `execution` 
        WHERE start_date > DATE_ADD(NOW(), INTERVAL -:days DAY)
        AND version = :version
        ORDER BY DATE(start_date) DESC;", ['days' => $period_sql, 'version' => $version]);
    }
}
