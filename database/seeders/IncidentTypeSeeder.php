<?php

namespace Database\Seeders;

use App\Models\IncidentType;
use Illuminate\Database\Seeder;

class IncidentTypeSeeder extends Seeder
{
    public function run(): void
    {
        $types = [
            'THEFT',
            'FIGHT',
            'SUSPICIOUS_ACTIVITY',
            'OTHER',
        ];

        foreach ($types as $name) {
            IncidentType::firstOrCreate(['name' => $name]);
        }
    }
}
