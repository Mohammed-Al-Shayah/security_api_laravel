<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('patrols', function (Blueprint $table) {
            $table->id();

            // 👈 هون نعرّف العمود + المفتاح الأجنبي، وبنخليه nullable
            $table->foreignId('inspector_id')
                ->nullable()
                ->constrained('inspectors')
                ->nullOnDelete();

            $table->foreignId('project_id')
                ->constrained('projects')
                ->cascadeOnDelete();

            $table->foreignId('guard_id')
                ->nullable()
                ->constrained('guards')
                ->nullOnDelete();

            $table->timestamp('start_time');
            $table->timestamp('end_time')->nullable();

            $table->unsignedTinyInteger('rating')->nullable(); // 1-5
            $table->text('notes')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('patrols');
    }
};
