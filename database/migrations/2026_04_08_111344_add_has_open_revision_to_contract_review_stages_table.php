<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Menambahkan kolom has_open_revision ke tabel contract_review_stages.
     *
     * Kolom ini adalah flag ringan untuk menandai apakah stage Bimo
     * (pemberi revisi) sedang memiliki revision task yang masih terbuka.
     * Digunakan untuk:
     *   - Menampilkan badge/flag di progress bar & stage card
     *   - Memblokir tombol "Approve Stage" jika masih ada task terbuka (opsional)
     *   - Query cepat tanpa harus JOIN ke contract_revision_tasks
     */
    public function up(): void
    {
        Schema::table('contract_review_stages', function (Blueprint $table) {
            $table->boolean('has_open_revision')->default(false)->after('is_manual_added');
        });
    }

    public function down(): void
    {
        Schema::table('contract_review_stages', function (Blueprint $table) {
            $table->dropColumn('has_open_revision');
        });
    }
};