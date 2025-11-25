<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ProjectController;
use App\Http\Controllers\Api\GuardController;
use App\Http\Controllers\Api\ShiftController;
use App\Http\Controllers\Api\AttendanceController;
use App\Http\Controllers\Api\PatrolController;
use App\Http\Controllers\Api\IncidentController;

// صفحة افتراضية (اختياري)
use Illuminate\Support\Facades\Artisan;

Route::get('/run-migrate', function () {
    try {
        Artisan::call('migrate', ['--force' => true]);
        return 'Migrations done!';
    } catch (\Throwable $e) {
        return $e->getMessage();
    }
});


  
