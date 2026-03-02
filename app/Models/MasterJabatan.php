<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MasterJabatan extends Model
{
    protected $table = 'tbl_jabatan';
    protected $primaryKey = 'no_jabatan';

    // PK integer auto increment
    public $incrementing = true;
    protected $keyType = 'int';

    // ❗ Penting: tabel ini TIDAK punya timestamps
    public $timestamps = false;

    // ❌ Tidak ada mass assignment (karena tidak CRUD)
    protected $guarded = [];

    /* =====================================================
     | READ-ONLY SAFETY (OPTIONAL TAPI DISARANKAN)
     ===================================================== */

    // Blok insert
    public static function booted()
    {
        static::creating(function () {
            return false;
        });

        static::updating(function () {
            return false;
        });

        static::deleting(function () {
            return false;
        });
    }

    /* =====================================================
     | ACCESSORS (BIAR ENAK DIPAKAI DI VIEW)
     ===================================================== */

    public function getIdAttribute()
    {
        return $this->no_jabatan;
    }

    public function getNameAttribute()
    {
        return trim($this->nama_jabatan);
    }

    public function getDisplayNameAttribute()
    {
        return trim($this->nama_jabatan);
    }

    // supaya konsisten kalau disatuin dengan department
    public function getIsActiveAttribute()
    {
        return true;
    }
}
