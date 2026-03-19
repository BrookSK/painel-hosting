<?php

declare(strict_types=1);

use LRV\App\Controllers\Api\SaudeController;
use LRV\App\Controllers\Api\JobsTesteController;
use LRV\App\Controllers\Api\AlertasTesteController;
use LRV\App\Controllers\Api\MetricsController;
use LRV\App\Controllers\Api\WorkerController;
use LRV\Core\Middlewares;

$roteador->get('/api/saude', [SaudeController::class, 'status']);

$roteador->post('/api/jobs/teste/enfileirar', [JobsTesteController::class, 'enfileirar'], [Middlewares::exigirLoginEquipe()]);

$roteador->post('/api/alertas/teste/enfileirar', [AlertasTesteController::class, 'enfileirar'], [Middlewares::exigirLoginEquipe()]);

$roteador->post('/api/metrics/servers', [MetricsController::class, 'registrarServidor']);

$roteador->post('/api/worker/run-once', [WorkerController::class, 'runOnce']);
$roteador->get('/api/worker/run-once', [WorkerController::class, 'runOnce']);
