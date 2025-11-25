<?php

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;

Route::get('/run-migrate', function () {
    // شغّل كل الـ migrations
    Artisan::call('migrate', ['--force' => true]);

    // لو عندك seeders (مثلاً AdminUserSeeder)
    // Artisan::call('db:seed', ['--force' => true]);

    return 'Migrations ran successfully!';
});


Route::get('/run-seed', function () {
    Artisan::call('db:seed', ['--force' => true]);

    return 'Seeders ran successfully!';
});
