<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inspector_project', function (Blueprint $table) {
            $table->id();

            $table->foreignId('inspector_id')
                ->constrained('inspectors')
                ->cascadeOnDelete();

            $table->foreignId('project_id')
                ->constrained('projects')
                ->cascadeOnDelete();

            $table->date('assigned_from')->nullable();
            $table->date('assigned_to')->nullable();

            $table->timestamps();

            $table->unique(['inspector_id', 'project_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inspector_project');
    }
};
