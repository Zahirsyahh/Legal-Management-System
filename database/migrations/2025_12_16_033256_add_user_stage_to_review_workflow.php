<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\ContractReviewStage;

return new class extends Migration
{
    public function up()
    {
        // Tambahkan field baru jika diperlukan
        Schema::table('contract_review_stages', function (Blueprint $table) {
        if (!Schema::hasColumn('contract_review_stages', 'is_user_stage')) {
            $table->boolean('is_user_stage')
                ->default(false)
                ->after('stage_type');
            }
        });
    }

    public function down()
    {
        Schema::table('contract_review_stages', function (Blueprint $table) {
            $table->dropColumn(['is_user_stage', 'revision_requested_by']);
        });
    }
};