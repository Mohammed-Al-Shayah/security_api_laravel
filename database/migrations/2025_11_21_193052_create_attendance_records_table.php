<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendance_records', function (Blueprint $table) {
            $table->id();

            $table->foreignId('shift_id')
                ->constrained('shifts')
                ->cascadeOnDelete();

            $table->foreignId('guard_id')
                ->constrained('guards')
                ->cascadeOnDelete();

            // CHECK IN
            $table->timestamp('check_in_time')->nullable();
            $table->decimal('check_in_lat', 10, 6)->nullable();
            $table->decimal('check_in_lng', 10, 6)->nullable();

            // CHECK OUT
            $table->timestamp('check_out_time')->nullable();
            $table->decimal('check_out_lat', 10, 6)->nullable();
            $table->decimal('check_out_lng', 10, 6)->nullable();

            // Status: ON_TIME, LATE, LEFT_EARLY
            $table->string('status')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendance_records');
    }
};
