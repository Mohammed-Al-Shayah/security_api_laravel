<?php

use Illuminate\Support\Facades\Route;

// Auth
use App\Http\Controllers\Api\AuthController;

// Core resources
use App\Http\Controllers\Api\ProjectController;
use App\Http\Controllers\Api\GuardController;
use App\Http\Controllers\Api\ShiftController;
use App\Http\Controllers\Api\AttendanceController;
use App\Http\Controllers\Api\PatrolController;

// Incidents + Types + Attachments
use App\Http\Controllers\Api\IncidentController;
use App\Http\Controllers\Api\IncidentTypeController;
use App\Http\Controllers\Api\IncidentAttachmentController;

// Dashboard + Activity Logs + Notifications
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\ActivityLogController;
use App\Http\Controllers\Api\NotificationController;

// Guard mobile
use App\Http\Controllers\Api\GuardMobileController;
use App\Http\Controllers\Api\GuardIncidentController;
use App\Http\Controllers\Api\GuardPatrolController;

// Inspector mobile
use App\Http\Controllers\Api\InspectorMobileController;

/*
|--------------------------------------------------------------------------
| Public API Routes (بدون توكن)
|--------------------------------------------------------------------------
*/

use Illuminate\Support\Facades\Artisan;

Route::get('/run-migrations-EL3b2_iv7', function () {
    Artisan::call('migrate', ['--force' => true]);

    return '✅ Migrations run successfully';
});


Route::get('/health', function () {
    return 'OK from Laravel ' . app()->version();
});

// ✅ Login
Route::post('auth/login', [AuthController::class, 'login']);

/*
|--------------------------------------------------------------------------
| Protected API Routes (بعد الـ Login)
|--------------------------------------------------------------------------
*/


Route::middleware('auth:sanctum')->group(function () {

    // ✅ Auth
    Route::get('auth/me',      [AuthController::class, 'me']);
    Route::post('auth/logout', [AuthController::class, 'logout']);

    /*
    |---------------------- Projects ----------------------
    */
    Route::get('projects',               [ProjectController::class, 'index']);
    Route::post('projects',              [ProjectController::class, 'store']);
    Route::get('projects/{project}',     [ProjectController::class, 'show']);
    Route::put('projects/{project}',     [ProjectController::class, 'update']);
    Route::delete('projects/{project}',  [ProjectController::class, 'destroy']);

    // Assign guards to project + listing
    Route::post('projects/{project}/assign-guards', [ProjectController::class, 'assignGuards']);
    Route::get('projects/{project}/guards',         [ProjectController::class, 'guards']);
    Route::get('projects/{project}/shifts',         [ProjectController::class, 'shifts']);

    /*
    |---------------------- Guards ------------------------
    */
    Route::get('guards',                 [GuardController::class, 'index']);
    Route::post('guards',                [GuardController::class, 'storeWithUser']); // guard + user
    Route::get('guards/{guard}',         [GuardController::class, 'show']);
    Route::put('guards/{guard}',         [GuardController::class, 'update']);
    Route::delete('guards/{guard}',      [GuardController::class, 'deactivate']); // deactivate guard

    // Shifts per guard
    Route::get('guards/{guard}/shifts',  [ShiftController::class, 'forGuard']);

    /*
    |---------------------- Shifts ------------------------
    */
    Route::post('shifts',            [ShiftController::class, 'store']);
    Route::get('shifts/{shift}',     [ShiftController::class, 'show']);
    Route::put('shifts/{shift}',     [ShiftController::class, 'update']);

    /*
    |---------------------- Attendance (لوحة التحكم) -------
    | هذه للإدارة / الإنسبكتور من لوحة التحكم
    */
    Route::post('attendance/check-in',  [AttendanceController::class, 'checkIn']);
    Route::post('attendance/check-out', [AttendanceController::class, 'checkOut']);

    /*
    |---------------------- Patrols (لوحة التحكم) ----------
    | يستخدمها الأدمن/الإنسبكتور لإدارة الدوريات
    */
    Route::get('patrols',          [PatrolController::class, 'index']);
    Route::post('patrols',         [PatrolController::class, 'store']);
    Route::get('patrols/{patrol}', [PatrolController::class, 'show']);

    /*
    |---------------------- Incidents ---------------------
    */
    Route::get('incidents',            [IncidentController::class, 'index']);
    Route::post('incidents',           [IncidentController::class, 'store']);
    Route::get('incidents/{incident}', [IncidentController::class, 'show']);
    Route::put('incidents/{incident}', [IncidentController::class, 'update']);

    // Incident types للفلاتر
    Route::get('incident-types', [IncidentTypeController::class, 'index']);

    // Attachments
    Route::post('incidents/{incident}/attachments', [IncidentAttachmentController::class, 'store']);
    Route::get('incidents/{incident}/attachments',  [IncidentAttachmentController::class, 'index']);
    Route::delete('incident-attachments/{attachment}', [IncidentAttachmentController::class, 'destroy']);

    /*
    |---------------------- Notifications -----------------
    */
    Route::get('notifications',                      [NotificationController::class, 'index']);
    Route::post('notifications/{notification}/read', [NotificationController::class, 'markAsRead']);

    /*
    |---------------------- Activity Logs -----------------
    */
    Route::get('activity-logs', [ActivityLogController::class, 'index']);

    /*
    |---------------------- Dashboard ---------------------
    */
    Route::get('dashboard/summary', [DashboardController::class, 'summary']);

    /*
    |---------------------- Guard Mobile API --------------
    | كل شيء يخص تطبيق الحارس
    */
    
    /*
    |---------------------- Inspector Mobile API ----------
    | كل شيء يخص تطبيق المفتش
    */
    Route::prefix('inspector')->group(function () {

        // معلومات المفتش + إحصائيات سريعة
        Route::get('home',     [InspectorMobileController::class, 'home']);

        // دوريات المفتش
        Route::get('patrols',  [InspectorMobileController::class, 'patrols']);

        // البلاغات التي أنشأها المفتش
        Route::get('incidents', [InspectorMobileController::class, 'incidents']);

        // المشاريع المكلّف فيها
        Route::get('projects', [InspectorMobileController::class, 'projects']);
    });

Route::prefix('guard')->group(function () {

        // معلومات الحارس الأساسية
        Route::get('me',         [GuardMobileController::class, 'me']);
        Route::get('shifts',     [GuardMobileController::class, 'shifts']);
        Route::get('attendance', [GuardMobileController::class, 'attendance']);
        Route::get('incidents',  [GuardMobileController::class, 'incidents']);

        // إنشاء حادث + مرفقات من تطبيق الحارس
        Route::post('incidents',                        [GuardIncidentController::class, 'store']);
        Route::post('incidents/{incident}/attachments', [GuardIncidentController::class, 'uploadAttachment']);

        // Attendance من طرف الحارس (باستخدام الـ guard_id من التوكن)
        // URI النهائي: /guard/attendance/check-in | /guard/attendance/check-out
        Route::post('attendance/check-in',  [AttendanceController::class, 'guardCheckIn']);
        Route::post('attendance/check-out', [AttendanceController::class, 'guardCheckOut']);

        // ✅ دوريات الحارس
        Route::get('patrols',               [GuardPatrolController::class, 'index']);
        Route::post('patrols',              [GuardPatrolController::class, 'store']);
        Route::post('patrols/{patrol}/end', [GuardPatrolController::class, 'end']);
    });



    
});
