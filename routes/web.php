<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DeviceController;
use App\Http\Controllers\HostController;
use App\Http\Controllers\PortController;
use App\Http\Controllers\ReportController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get(
    '/dashboard',
    [DashboardController::class, 'index']
)->name('dashboard');

Route::resource('devices', DeviceController::class);

Route::post(
    '/devices/{device}/check-snmp',
    [DeviceController::class, 'checkSnmp']
)->name('devices.check-snmp');

Route::delete(
    '/devices/{device}/ports/{port}',
    [PortController::class, 'destroy']
)->name('devices.ports.destroy');

Route::resource('hosts', HostController::class);

Route::resource('reports', ReportController::class);

Route::get(
    '/reports/{report}/download',
    [ReportController::class, 'download']
)->name('reports.download');