// database/migrations/xxxx_create_contract_review_jumps_table.php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('contract_review_jumps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contract_id')->constrained()->onDelete('cascade');
            $table->foreignId('from_stage_id')->constrained('contract_review_stages');
            $table->foreignId('to_stage_id')->constrained('contract_review_stages');
            $table->foreignId('jumped_by')->constrained('users');
            $table->text('reason')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('contract_review_jumps');
    }
};