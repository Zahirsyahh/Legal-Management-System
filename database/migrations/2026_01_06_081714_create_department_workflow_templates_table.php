// database/migrations/2024_01_02_create_department_workflow_templates_table.php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('department_workflow_templates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('department_id')->constrained();
            $table->string('name'); // "Standard Legal Review"
            $table->json('stages_config'); // JSON config
            $table->boolean('is_default')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('department_workflow_templates');
    }
};