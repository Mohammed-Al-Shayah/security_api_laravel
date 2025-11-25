<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Inspector;
use App\Models\User;

class InspectorSeeder extends Seeder
{
    public function run(): void
    {
        $inspectors = User::where('role', 'INSPECTOR')->get();

        foreach ($inspectors as $user) {
            Inspector::create([
                'user_id'       => $user->id,
                'employee_code' => 'INS-' . rand(100, 999),
                'status'        => 'ACTIVE',
            ]);
        }
    }
}

