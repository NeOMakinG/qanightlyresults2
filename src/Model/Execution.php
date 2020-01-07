<?php
namespace App\Model;

use Illuminate\Database\Capsule\Manager as DB;

class Execution {

    public static function getAll() {
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
        WHERE start_date > DATE_ADD(NOW(), INTERVAL -30 DAY)
        ORDER BY DATE(start_date) DESC");
    }
}
