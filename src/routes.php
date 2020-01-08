<?php

use App\Controller\GraphController;
use App\Controller\IndexController;
use App\Controller\ReportController;

$app->get('/', [IndexController::class, 'index']);

$app->get('/api/reports', [ReportController::class, 'index']);
$app->get('/api/reports/{report:[0-9]+}', [ReportController::class, 'report']);
$app->get('/api/reports/{report:[0-9]+}/suite/{suite:[0-9]+}', [ReportController::class, 'suite']);

$app->get('/api/graph', [GraphController::class, 'index']);

