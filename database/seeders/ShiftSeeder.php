<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Shift;
use App\Models\Project;

class ShiftSeeder extends Seeder
{
    public function run(): void
    {
        $projects = Project::with('guards')->get();

        foreach ($projects as $project) {
            foreach ($project->guards as $guard) {
                Shift::create([
                    'project_id' => $project->id,
                    'guard_id'   => $guard->id,
                    'date'       => now()->format('Y-m-d'),
                    'start_time' => '08:00',
                    'end_time'   => '16:00',
                    'status'     => 'PLANNED',
                ]);
            }
        }
    }
}
