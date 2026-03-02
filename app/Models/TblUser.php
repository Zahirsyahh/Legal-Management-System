<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\Permission\Traits\HasRoles;

class TblUser extends Authenticatable
{
    use HasFactory, Notifiable, HasRoles;

    // ===============================
    // TABLE CONFIG
    // ===============================
    protected $table = 'tbl_user';
    protected $primaryKey = 'id_user';
    protected $keyType = 'int';
    public $incrementing = true; // ✅ UBAH KE true jika auto-increment
    public $timestamps = false;

    // ===============================
    // SPATIE CONFIG
    // ===============================
    protected $guard_name = 'web';

    // ===============================
    // MASS ASSIGNMENT
    // ===============================
    protected $fillable = [
        'id_user', // ✅ TAMBAHKAN ini agar bisa di-set manual
        'username',
        'password',
        'nama_user',
        'email',
        'kode_department',
        'jabatan',
        'status_karyawan',
        'no_hp',
        'kode_status_kepegawaian', // ✅ TAMBAHKAN ini
    ];

    protected $hidden = [
        'password',
    ];

    // ===============================
    // AUTH CONFIG (WAJIB UNTUK PK CUSTOM)
    // ===============================
    public function getAuthIdentifierName()
    {
        return 'id_user';
    }

    public function getKeyName()
    {
        return 'id_user';
    }

    public function getRolesAttribute()
    {
        return $this->roles()->get();
    }

    public function getAuthIdentifier()
    {
        return $this->id_user;
    }

    public function getAuthPassword()
    {
        return $this->password;
    }

    // ===============================
    // ATTRIBUTE ALIASES (LARAVEL FRIENDLY)
    // ===============================

    /**
     * Supaya $user->id tetap jalan
     */
    public function getIdAttribute()
    {
        return $this->id_user;
    }

    /**
     * Supaya $user->name tetap jalan
     */
    public function getNameAttribute()
    {
        return $this->nama_user;
    }

    /**
     * Status aktif user
     */
    public function getIsActiveAttribute(): bool
    {
        return $this->status_karyawan === 'AKTIF';
    }

    /**
     * 🔥 SATU-SATUNYA SUMBER department code
     * Role > DB value
     */
    public function getDepartmentCodeAttribute(): ?string
    {
        if ($this->hasAnyRole(['admin_fin', 'staff_fin'])) return 'FIN';
        if ($this->hasAnyRole(['admin_acc', 'staff_acc'])) return 'ACC';
        if ($this->hasAnyRole(['admin_tax', 'staff_tax'])) return 'TAX';
        if ($this->hasRole('legal')) return 'LEGAL';
        if ($this->hasRole('admin')) return 'ADMIN'; // ✅ TAMBAHKAN ini

        return $this->kode_department;
    }

    public function department()
    {
        return $this->belongsTo(
            MasterDepartment::class,
            'kode_department',   // FK di tbl_user
            'kode_pendek'        // PK di tbl_department
        );
    }

    // ===============================
    // RELATIONSHIPS
    // ===============================
    public function contracts()
    {
        return $this->hasMany(Contract::class, 'user_id', 'id_user');
    }

    public function contractsLegalAssigned()
    {
        return $this->hasMany(Contract::class, 'legal_assigned_id', 'id_user');
    }

    public function reviewStages()
    {
        return $this->hasMany(ContractReviewStage::class, 'assigned_user_id', 'id_user');
    }

    // ===============================
    // NOTIFICATION ROUTING
    // ===============================
    public function routeNotificationForMail($notification = null)
    {
        return $this->email;
    }

    // ===============================
    // ROLE HELPERS (READABLE & AMAN)
    // ===============================
    public function isLegalOfficer(): bool
    {
        return $this->hasRole('legal');
    }

    public function isRegularUser(): bool
    {
        return $this->hasRole('user');
    }

    public function isAdmin(): bool
    {
        return $this->hasRole('admin');
    }

    public function isDepartmentAdmin(): bool
    {
        return $this->hasAnyRole(['admin_fin', 'admin_acc', 'admin_tax']);
    }

    public function isDepartmentStaff(): bool
    {
        return $this->hasAnyRole(['staff_fin', 'staff_acc', 'staff_tax']);
    }

    public function hasDepartmentRole(): bool
    {
        return $this->isDepartmentAdmin() || $this->isDepartmentStaff();
    }
    
    /**
     * ✅ HELPER BARU: Cek apakah user bisa approve
     */
    public function canApproveDepartment(): bool
    {
        return $this->isDepartmentAdmin() || $this->isAdmin();
    }

    /**
     * Helper untuk kompatibilitas dengan Auth::user()
     */
    public static function auth()
    {
        return static::find(Auth::id());
    }

    /**
     * Check if user can access admin functions
     */
    public function canStartReview(): bool
    {
        return $this->hasAnyRole(['legal', 'admin']);
    }

    // ===============================
    // SCOPES
    // ===============================
    public function scopeActive($query)
    {
        return $query->where('status_karyawan', 'AKTIF');
    }

    public function scopeByDepartment($query, string $code)
    {
        return $query->where('kode_department', $code);
    }
    
    /**
     * ✅ SCOPE BARU: Filter by role
     */
    public function scopeWithRole($query, string $roleName)
    {
        return $query->whereHas('roles', function($q) use ($roleName) {
            $q->where('name', $roleName);
        });
    }
}