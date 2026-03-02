<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MasterDepartment extends Model
{
    /**
     * ==============================
     * TABLE CONFIGURATION
     * ==============================
     */
    protected $table = 'tbl_department';
    protected $primaryKey = 'kode_departemen';
    
    // ✅ BENAR: PK adalah integer & auto increment
    public $incrementing = true;
    protected $keyType = 'int';
    
    // ❌ SALAH: Tabel TIDAK punya timestamps!
    // public $timestamps = true; ← INI SALAH!
    public $timestamps = false; // ✅ PERBAIKAN!

    // ✅ BENAR: Hanya 2 field yang bisa diisi
    protected $fillable = [
        'kode_pendek',
        'nama_departemen',
        // kode_departemen auto increment
        // TIDAK ADA created_at & updated_at
    ];

    /* =====================================================
     | ACCESSORS (VIRTUAL - TIDAK ADA DI DATABASE)
     ===================================================== */
    public function getCodeAttribute()
    {
        return $this->kode_pendek;
    }

    public function getNameAttribute()
    {
        return $this->nama_departemen;
    }

    // ✅ VIRTUAL FIELD - untuk display di view saja
    public function getDisplayNameAttribute()
    {
        return "{$this->nama_departemen} ({$this->kode_pendek})";
    }
    
    // ✅ VIRTUAL FIELD - default selalu active
    public function getIsActiveAttribute()
    {
        return true;
    }

    /* =====================================================
     | SCOPES
     ===================================================== */
    public function scopeByCode($query, $code)
    {
        return $query->where('kode_pendek', strtoupper($code));
    }

    public function scopeSearch($query, $keyword)
    {
        return $query->where('nama_departemen', 'like', "%{$keyword}%")
                     ->orWhere('kode_pendek', 'like', "%{$keyword}%");
    }
}