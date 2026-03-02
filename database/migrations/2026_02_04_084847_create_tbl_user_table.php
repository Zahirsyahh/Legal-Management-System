<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // ⛔ jangan create kalau sudah ada (karena ini tabel import)
        if (Schema::hasTable('tbl_user')) {
            return;
        }

        Schema::create('tbl_user', function (Blueprint $table) {
            $table->unsignedInteger('id_user')->primary();

            $table->string('username', 50)->nullable();
            $table->string('password', 100)->nullable();
            $table->string('nama_user', 100)->nullable();
            $table->char('nip', 11)->nullable();
            $table->tinyInteger('hak_akses')->nullable()->comment('1 pak muknis, 2 user umum, dst');
            $table->string('jabatan', 100);
            $table->date('tgl_masuk_karyawan')->nullable();
            $table->date('tgl_resign')->nullable();

            $table->enum('status_karyawan', ['AKTIF', 'TIDAK AKTIF', ''])->default('');
            $table->integer('kode_status_kepegawaian');

            $table->string('id_atasan_1', 20)->nullable();
            $table->string('id_atasan_2', 20)->nullable();
            $table->char('kode_perusahaan', 2)->nullable();
            $table->string('kode_department', 5)->nullable();

            $table->string('no_ktp', 18)->nullable();
            $table->string('no_hp', 16)->nullable();
            $table->string('photo_user', 100)->nullable();
            $table->tinyInteger('kode_lokasi_kerja')->nullable();

            $table->string('tempat_lahir', 100)->nullable();
            $table->date('tgl_lahir')->nullable();
            $table->enum('jenkel', ['Laki-Laki', 'Perempuan', ''])->nullable();
            $table->string('gol_darah', 3)->nullable();

            $table->string('alamat_karyawan', 200)->nullable();
            $table->string('agama', 50)->nullable();
            $table->enum('status_kawin', ['KAWIN', 'BELUM KAWIN', 'TIDAK KAWIN', '-', ''])->nullable();

            $table->string('kewarganegaraan', 100)->nullable();
            $table->string('nama_ibu_kandung', 100)->nullable();
            $table->string('ptkp', 10)->nullable();
            $table->string('npwp', 30)->nullable();

            $table->string('email', 100)->nullable();
            $table->string('pendidikan', 50)->nullable();
            $table->string('poh', 50)->nullable();
            $table->string('kelurahan', 100)->nullable();
            $table->string('kecamatan', 100)->nullable();
            $table->string('kab_kota', 100)->nullable();

            $table->string('rekomendasi', 100)->nullable();
            $table->string('nama_pasangan', 100)->nullable();
            $table->string('tempat_lahir_pasangan', 100)->nullable();
            $table->date('tgl_lahir_pasangan')->nullable();
            $table->enum('jenkel_pasangan', ['Laki-Laki', 'Perempuan', ''])->nullable();

            $table->integer('hari')->nullable();
            $table->string('nama_bank', 50)->nullable();
            $table->string('no_rek', 50)->nullable();
            $table->string('nama_di_rekening', 100)->nullable();
            $table->integer('group_payroll')->nullable();

            $table->string('alasan_resign', 300)->nullable();
            $table->string('user_input', 20)->nullable();
            $table->string('no_ext', 6)->nullable();
            $table->string('id_absensi', 20)->nullable();

            // 🔐 penting untuk auth
            $table->rememberToken();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tbl_user');
    }
};
