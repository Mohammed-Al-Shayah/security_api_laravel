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
                // لو عندك أعمدة إلزامية ثانية ضيفها هنا لاحقًا
            ]
        );

        return response()->json([
            'status' => 'ok',
            'user'   => $user,
        ]);
    } catch (\Throwable $e) {
        dd(
            $e->getMessage(),
            $e->getFile(),
            $e->getLine()
        );
    }
});