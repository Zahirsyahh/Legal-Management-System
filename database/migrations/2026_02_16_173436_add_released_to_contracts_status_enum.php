<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        // Get current ENUM values
        $enumValues = DB::select("SHOW COLUMNS FROM contracts WHERE Field = 'status'")[0]->Type;
        
        // Extract values from enum('draft','submitted',...)
        preg_match("/^enum\(\'(.*)\'\)$/", $enumValues, $matches);
        $currentValues = explode("','", $matches[1]);
        
        // Add 'number_issued' and 'released' if not exist
        if (!in_array('number_issued', $currentValues)) {
            $currentValues[] = 'number_issued';
        }
        if (!in_array('released', $currentValues)) {
            $currentValues[] = 'released';
        }
        
        // Update ENUM
        $newEnum = "'" . implode("','", $currentValues) . "'";
        DB::statement("ALTER TABLE contracts MODIFY COLUMN status ENUM($newEnum) NOT NULL DEFAULT 'draft'");
    }

    public function down()
    {
        // Revert if needed
        DB::statement("ALTER TABLE contracts MODIFY COLUMN status ENUM('draft','submitted','awaiting_document_upload','document_uploaded','user_reviewing','user_review_complete','legal_reviewing_feedback','legal_reviewing','under_review','legal_approved','final_approved','revision_needed','declined','cancelled') NOT NULL DEFAULT 'draft'");
    }
};