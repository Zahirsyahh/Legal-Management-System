<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use App\Models\TblUser;

class Department extends Model
{
    protected $fillable = ['name', 'code', 'description', 'is_active'];
    
    /**
     * Relasi staff berdasarkan role department
     */
    public function staff()
    {
        $staffRole = 'staff_' . strtolower($this->code);

        return TblUser::whereHas('roles', function ($q) use ($staffRole) {
            $q->where('name', $staffRole);
        });
    }

    // ✅ PERBAIKAN: Map department code ke role name yang benar
    public function staffMembers()
    {
        // Mapping department code ke role staff
        $roleMapping = [
            'FIN' => 'staff_fin',
            'ACC' => 'staff_acc',
            'TAX' => 'staff_tax',
        ];

        $roleName = $roleMapping[strtoupper($this->code)]
            ?? 'staff_' . strtolower($this->code);

        return TblUser::whereHas('roles', function ($query) use ($roleName) {
            $query->where('name', $roleName);
        });
    }
    
    /**
     * Get active staff members
     */
    public function activeStaff()
    {
        return $this->staff()->where('status_karyawan', 'AKTIF');
    }
    
    /**
     * Get admin users for this department
     */
    public function adminUsers()
    {
        $adminRole = 'admin_' . strtolower($this->code);

        return TblUser::whereHas('roles', function ($q) use ($adminRole) {
            $q->where('name', $adminRole);
        })
        ->where('status_karyawan', 'AKTIF');
    }
    
    /**
     * Get contract departments assigned to this department
     */
    public function contractDepartments()
    {
        return $this->hasMany(ContractDepartment::class);
    }
    
    /**
     * Get pending assignments count
     */
    public function pendingAssignmentsCount()
    {
        return $this->contractDepartments()
            ->where('status', 'pending_assignment')
            ->count();
    }
    
    /**
     * Get active reviews count
     */
    public function activeReviewsCount()
    {
        return $this->contractDepartments()
            ->whereIn('status', ['assigned', 'in_progress'])
            ->count();
    }
    
    /**
     * Get completed reviews count
     */
    public function completedReviewsCount()
    {
        return $this->contractDepartments()
            ->where('status', 'completed')
            ->count();
    }

    public function getAdminRoleName()
    {
        return 'admin_' . strtolower($this->code);
    }
    
    public function getReviewerRoleName(){
    // Mapping department code ke STAFF role name
    $roleMapping = [
        'FIN' => 'staff_fin',
        'ACC' => 'staff_acc', 
        'TAX' => 'staff_tax'
    ];
    
    return $roleMapping[strtoupper($this->code)] ?? 'staff_' . strtolower($this->code);
    }
    
    /**
     * Get display name with code
     */
    public function getDisplayNameAttribute()
    {
        return "{$this->name} ({$this->code})";
    }
    
    /**
     * Scope: Active departments only
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
    
    /**
     * Scope: By department code
     */
    public function scopeByCode($query, $code)
    {
        return $query->where('code', strtoupper($code));
    }
}