// database/migrations/2024_01_01_create_departments_table.php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('departments', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Legal, Finance, Accounting, Tax
            $table->string('code')->unique(); // LEGAL, FIN, ACC, TAX
            $table->text('description')->nullable();
            $table->foreignId('head_user_id')->nullable()->constrained('users');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('departments');
    }
};