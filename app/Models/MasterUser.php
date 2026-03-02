<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Permission\Traits\HasRoles;

class MasterUser extends models
{
    /**
     * ==============================
     * BASIC CONFIG
     * ==============================
     */
    use HasRoles;

    protected $table = 'tbl_user';
    protected $primaryKey = 'id_user';

    // PENTING: Set ke false karena id_user bukan auto-increment
    public $incrementing = false;
    protected $keyType = 'int';

    public $timestamps = false;

    protected $guard_name = 'web';
    /**
     * ==============================
     * MASS ASSIGNMENT
     * ==============================
     */
    protected $fillable = [
        'id_user',           // ✅ TAMBAHKAN INI - Penting karena bukan auto-increment
        'username',
        'password',
        'nama_user',
        'nip',
        'hak_akses',
        'jabatan',
        'tgl_masuk_karyawan',
        'tgl_resign',
        'status_karyawan',
        'kode_status_kepegawaian',
        'id_atasan_1',
        'id_atasan_2',
        'kode_perusahaan',
        'kode_department',
        'no_ktp',
        'no_hp',
        'email',
        'tempat_lahir',
        'tgl_lahir',
        'jenkel',
        'gol_darah',
        'alamat_karyawan',
        'agama',
        'status_kawin',
        'kewarganegaraan',
        'nama_ibu_kandung',
        'ptkp',
        'npwp',
        'pendidikan',
        'poh',
        'kelurahan',
        'kecamatan',
        'kab_kota',
        'rekomendasi',
        'nama_pasangan',
        'tempat_lahir_pasangan',
        'tgl_lahir_pasangan',
        'jenkel_pasangan',
        'hari',
        'nama_bank',
        'no_rek',
        'nama_di_rekening',
        'group_payroll',
        'alasan_resign',
        'user_input',
        'no_ext',
        'id_absensi',
        'photo_user',
        'kode_lokasi_kerja',
    ];

    /**
     * ==============================
     * HIDDEN ATTRIBUTES
     * ==============================
     */
    protected $hidden = [
        'password',
    ];

    /**
     * ==============================
     * CASTS
     * ==============================
     */
    protected $casts = [
        'tgl_masuk_karyawan' => 'date',
        'tgl_resign'         => 'date',
        'tgl_lahir'          => 'date',
        'tgl_lahir_pasangan' => 'date',
        'hak_akses'          => 'integer',
        'kode_status_kepegawaian' => 'integer',
        'kode_lokasi_kerja'  => 'integer',
        'hari'               => 'integer',
        'group_payroll'      => 'integer',
    ];

    /**
     * ==============================
     * SCOPES
     * ==============================
     */
    public function scopeSearch($query, $search)
    {
        return $query->where(function($q) use ($search) {
            $q->where('nama_user', 'like', "%{$search}%")
              ->orWhere('username', 'like', "%{$search}%")
              ->orWhere('email', 'like', "%{$search}%")
              ->orWhere('nip', 'like', "%{$search}%");
        });
    }

    public function scopeByDepartment($query, $departmentCode)
    {
        return $query->where('kode_department', $departmentCode);
    }

    public function scopeActive($query)
    {
        return $query->where('status_karyawan', 'AKTIF');
    }

    /**
     * ==============================
     * ACCESSORS
     * ==============================
     */
    public function getDisplayNameAttribute()
    {
        return $this->nama_user ?? $this->username;
    }

    public function getIsActiveAttribute()
    {
        return $this->status_karyawan === 'AKTIF';
    }

    /**
     * ==============================
     * MUTATORS
     * ==============================
     */
    public function setPasswordAttribute($value)
    {
        if (!empty($value)) {
            $this->attributes['password'] = bcrypt($value);
        }
    }

    /**
     * ==============================
     * RELATIONS
     * ==============================
     */
    public function department()
    {
        return $this->belongsTo(
            MasterDepartment::class,
            'kode_department',
            'kode_pendek'
        );
    }

    /**
     * ==============================
     * HELPER METHODS
     * ==============================
     */
    
    /**
     * Generate ID User berikutnya
     */
    public static function getNextId()
    {
        $lastUser = self::orderBy('id_user', 'desc')->first();
        return $lastUser ? $lastUser->id_user + 1 : 1000;
    }
}