<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ContractDepartment extends Model
{
    use HasFactory;

    protected $table = 'contract_departments';

    protected $fillable = [
        'contract_id',
        'department_id',
        'status',
        'assigned_admin_id',
        'assigned_at',
        'started_at',
        'completed_at',
        'notes',
    ];

    protected $casts = [
        'assigned_at' => 'datetime',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function contract()
    {
        return $this->belongsTo(Contract::class);
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function assignedAdmin()
    {
        return $this->belongsTo(TblUser::class, 'assigned_admin_id');
    }

    public function isPendingAssignment()
    {
        return $this->status === 'pending_assignment';
    }

    public function isInProgress()
    {
        return $this->status === 'in_progress';
    }

    public function isCompleted()
    {
        return $this->status === 'completed';
    }

    public function markAsAssigned($adminId)
    {
        $this->update([
            'status' => 'assigned',
            'assigned_admin_id' => $adminId,
            'assigned_at' => now(),
        ]);
    }

    public function markAsInProgress()
    {
        $this->update([
            'status' => 'in_progress',
            'started_at' => now(),
        ]);
    }

    public function markAsCompleted()
    {
        $this->update([
            'status' => 'completed',
            'completed_at' => now(),
        ]);
    }
}