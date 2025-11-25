<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            GuardSeeder::class,
            InspectorSeeder::class,
            ProjectSeeder::class,
            AssignGuardsToProjectsSeeder::class,
            ShiftSeeder::class,
            IncidentTypeSeeder::class
        ]);
    }
}
