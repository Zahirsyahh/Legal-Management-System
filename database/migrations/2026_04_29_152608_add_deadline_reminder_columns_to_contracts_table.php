<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration: add deadline reminder tracking columns to contracts table
 *
 * Tujuan:
 *   Mencegah pengiriman notifikasi duplikat jika scheduler
 *   dijalankan lebih dari sekali dalam sehari (misalnya karena
 *   error restart atau manual re-run).
 *
 * Kolom yang ditambah:
 *   - deadline_reminder_3d_sent_at  → timestamp notif "3 hari" sudah dikirim
 *   - deadline_reminder_1d_sent_at  → timestamp notif "1 hari" sudah dikirim
 *
 * Usage:
 *   php artisan migrate
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('contracts', function (Blueprint $table) {
            $table->timestamp('deadline_reminder_3d_sent_at')
                  ->nullable()
                  ->after('drafting_deadline')
                  ->comment('Timestamp when 3-day deadline reminder was last sent');

            $table->timestamp('deadline_reminder_1d_sent_at')
                  ->nullable()
                  ->after('deadline_reminder_3d_sent_at')
                  ->comment('Timestamp when 1-day deadline reminder was last sent');
        });
    }

    public function down(): void
    {
        Schema::table('contracts', function (Blueprint $table) {
            $table->dropColumn([
                'deadline_reminder_3d_sent_at',
                'deadline_reminder_1d_sent_at',
            ]);
        });
    }
};