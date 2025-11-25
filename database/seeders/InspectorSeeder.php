<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Inspector;
use App\Models\User;

class InspectorSeeder extends Seeder
{
    public function run(): void
    {
        // عدد المفتشين اللي بدك تنشئهم
        $count = 5;

        for ($i = 1; $i <= $count; $i++) {

            Inspector::create([
                'user_id'       => User::inRandomOrder()->first()->id, // أو عدّلها حسب نظامك
                'employee_code' => 'INS-' . str_pad($i, 3, '0', STR_PAD_LEFT),
                // INS-001, INS-002, INS-003, ...
                'status'        => 'ACTIVE',
            ]);
        }
    }
}
