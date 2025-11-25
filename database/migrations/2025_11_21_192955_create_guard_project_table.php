<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('guard_project', function (Blueprint $table) {
            $table->id();

            $table->foreignId('guard_id')
                ->constrained('guards')
                ->cascadeOnDelete();

            $table->foreignId('project_id')
                ->constrained('projects')
                ->cascadeOnDelete();

            $table->date('assigned_from')->nullable();
            $table->date('assigned_to')->nullable();

            $table->timestamps();

            $table->unique(['guard_id', 'project_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('guard_project');
    }
};
