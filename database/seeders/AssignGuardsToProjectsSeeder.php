<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Project;
use App\Models\Guard;

class AssignGuardsToProjectsSeeder extends Seeder
{
    public function run(): void
    {
        $projects = Project::all();
        $guards   = Guard::all();

        foreach ($projects as $project) {
            $assignedGuards = $guards->random(4);

            foreach ($assignedGuards as $guard) {
                $project->guards()->syncWithoutDetaching([
                    $guard->id => [
                        'assigned_from' => now()->subDays(rand(3, 10)),
                        'assigned_to'   => null,
                    ]
                ]);
            }
        }
    }
}
