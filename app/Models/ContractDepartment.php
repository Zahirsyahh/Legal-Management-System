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
        'declined_at',   // new
        'declined_by',   // new
        'notes',
    ];

    protected $casts = [
        'assigned_at'  => 'datetime',
        'started_at'   => 'datetime',
        'completed_at' => 'datetime',
        'declined_at'  => 'datetime', // new
    ];

    // ── Relationships ────────────────────────────────────────────
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

    public function declinedBy()
    {
        return $this->belongsTo(TblUser::class, 'declined_by');
    }

    // ── Status helpers ───────────────────────────────────────────
    public function isPendingAssignment(): bool
    {
        return $this->status === 'pending_assignment';
    }

    public function isAssigned(): bool
    {
        return $this->status === 'assigned';
    }

    public function isInProgress(): bool
    {
        return $this->status === 'in_progress';
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function isDeclined(): bool
    {
        return $this->status === 'declined';
    }

    // ── Mutators ─────────────────────────────────────────────────
    public function markAsAssigned($adminId): void
    {
        $this->update([
            'status'            => 'assigned',
            'assigned_admin_id' => $adminId,
            'assigned_at'       => now(),
        ]);
    }

    public function markAsInProgress(): void
    {
        $this->update([
            'status'     => 'in_progress',
            'started_at' => now(),
        ]);
    }

    public function markAsCompleted(): void
    {
        $this->update([
            'status'       => 'completed',
            'completed_at' => now(),
        ]);
    }

    public function markAsDeclined($userId): void
    {
        $this->update([
            'status'      => 'declined',
            'declined_at' => now(),
            'declined_by' => $userId,
        ]);
    }
}