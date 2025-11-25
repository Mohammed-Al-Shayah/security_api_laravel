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
Route::get('/', function () {
    return 'Security Guard API is running';
});

  
