<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Project;
use App\Models\User;

class ProjectSeeder extends Seeder
{
    public function run(): void
    {
        // نحاول نجيب أي Manager، ولو مش موجود ناخد أول مستخدم
        $manager = User::where('role', 'MANAGER')->first()
            ?? User::first();

        if (! $manager) {
            // لو ما في يوزرز أصلاً بنطلع بهدوء
            return;
        }

        Project::insert([
            [
                'name'       => 'Mall Main Entrance Security',
                'location'   => 'City Center Mall - Gate A',
                'lat'        => 31.5000,
                'lng'        => 34.4660,
                'manager_id' => $manager->id,
                'status'     => 'ACTIVE',
                'start_date' => now()->toDateString(),
                'end_date'   => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name'       => 'Hospital Night Shift Security',
                'location'   => 'Al-Rahma Hospital',
                'lat'        => 31.5020,
                'lng'        => 34.4700,
                'manager_id' => $manager->id,
                'status'     => 'ACTIVE',
                'start_date' => now()->toDateString(),
                'end_date'   => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name'       => 'Warehouse Perimeter Guards',
                'location'   => 'Industrial Zone - Warehouse 5',
                'lat'        => 31.4980,
                'lng'        => 34.4600,
                'manager_id' => $manager->id,
                'status'     => 'ACTIVE',
                'start_date' => now()->toDateString(),
                'end_date'   => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
