<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'name'      => 'Admin User',
            'email'     => 'admin@security.com',
            'phone'     => '0599000000',
            'role'      => 'ADMIN',
            'is_active' => true,
            'password'  => Hash::make('password'),
        ]);

        User::create([
            'name'      => 'Project Manager',
            'email'     => 'manager@security.com',
            'phone'     => '0599000001',
            'role'      => 'MANAGER',
            'is_active' => true,
            'password'  => Hash::make('password'),
        ]);

        for ($i = 1; $i <= 5; $i++) {
            User::create([
                'name'      => "Inspector $i",
                'email'     => "inspector$i@security.com",
                'phone'     => "05999000$i",
                'role'      => 'INSPECTOR',
                'is_active' => true,
                'password'  => Hash::make('password'),
            ]);
        }

        for ($i = 1; $i <= 10; $i++) {
            User::create([
                'name'      => "Guard $i",
                'email'     => "guard$i@security.com",
                'phone'     => "05999100$i",
                'role'      => 'GUARD',
                'is_active' => true,
                'password'  => Hash::make('password'),
            ]);
        }
    }
}
