<?php

use Illuminate\Database\Capsule\Manager as Capsule;

$capsule = new Capsule;
$capsule->addConnection([
    'driver'    => 'mysql',
    'host'      => getenv('QANB_DB_HOST') !== false ? getenv('QANB_DB_HOST') : 'localhost',
    'database'  => getenv('QANB_DB_NAME') !== false ? getenv('QANB_DB_NAME') : 'qanightlyresults',
    'username'  => getenv('QANB_DB_USERNAME') !== false ? getenv('QANB_DB_USERNAME') : 'root',
    'password'  => getenv('QANB_DB_PASSWORD') !== false ? getenv('QANB_DB_PASSWORD') : '',
    'charset'   => 'utf8',
    'collation' => 'utf8_unicode_ci',
    'prefix'    => '',
]);
$capsule->setAsGlobal();
$capsule->bootEloquent();
