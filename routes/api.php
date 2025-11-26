<?php

use Illuminate\Support\Facades\Route;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

/*
|--------------------------------------------------------------------------
| TEST ROUTES ONLY (مؤقت)
|--------------------------------------------------------------------------
*/

Route::get('health', function () {
    return 'OK from TEST api.php';
});

Route::get('create-admin', function () {
    try {
        $user = User::updateOrCreate(
            ['email' => 'admin@security.com'],
            [
                'name'     => 'Admin',
                'password' => Hash::make('password'),
                // لو عندك أعمدة إلزامية ثانية في جدول users ضيفها هون
                // مثال:
                // 'type' => 'admin',
            ]
        );

        return response()->json([
            'status' => 'ok',
            'user'   => $user,
        ]);
    } catch (\Throwable $e) {
        return response()->json([
            'status'  => 'error',
            'message' => $e->getMessage(),
            'file'    => $e->getFile(),
            'line'    => $e->getLine(),
        ], 500);
    }
});
