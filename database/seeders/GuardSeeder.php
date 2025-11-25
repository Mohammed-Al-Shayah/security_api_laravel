<?php

namespace Database\Seeders;

use App\Models\Guard;
use App\Models\User;
use Illuminate\Database\Seeder;

class GuardSeeder extends Seeder
{
    public function run(): void
    {
        // كل يوزر دوره GUARD
        $guardUsers = User::where('role', 'GUARD')->get();

        $counter = 1;

        foreach ($guardUsers as $user) {

            // قبل الإنشاء: تأكد إنو هذا اليوزر مش عامل Guard قبل
            if (Guard::where('user_id', $user->id)->exists()) {
                continue; // Skip
            }

            Guard::create([
                'user_id'      => $user->id,
                'national_id'  => fake()->unique()->numerify('4023######'),
                'badge_number' => 'BDG-' . str_pad($counter, 3, '0', STR_PAD_LEFT),
                'status'       => 'ACTIVE',
            ]);

            $counter++;
        }
    }
}
