<?php

use App\Http\Controllers\HostAgentController;
use App\Http\Controllers\MetricController;
use Illuminate\Support\Facades\Route;

Route::post(
    '/agent/report',
    [HostAgentController::class, 'report']
)->name('agent.report');

Route::post(
    '/agent/metrics',
    [HostAgentController::class, 'metrics']
)->name('agent.metrics');

Route::get(
    '/metrics',
    [MetricController::class, 'index']
)->name('metrics.index');

Route::post(
    '/metrics',
    [MetricController::class, 'store']
)->name('metrics.store');

Route::get(
    '/hosts/{host}/metrics',
    [MetricController::class, 'hostMetrics']
)->name('hosts.metrics');

Route::get(
    '/devices/{device}/metrics',
    [MetricController::class, 'deviceMetrics']
)->name('devices.metrics');