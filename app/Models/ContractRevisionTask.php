<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContractRevisionTask extends Model
{
    protected $fillable = [
        'contract_id',
        'from_stage_id',
        'to_stage_id',
        'requested_by',
        'assigned_to',
        'revision_notes',
        'response_notes',
        'status',
        'loop_count',
        'sent_at',
        'started_at',
        'submitted_at',
        'approved_at',
        'cancelled_at',
        'parent_task_id',
    ];

    protected $casts = [
        'sent_at'       => 'datetime',
        'started_at'    => 'datetime',
        'submitted_at'  => 'datetime',
        'approved_at'   => 'datetime',
        'cancelled_at'  => 'datetime',
        'loop_count'    => 'integer',
    ];

    // =====================================================
    // RELATIONSHIPS
    // =====================================================

    public function contract()
    {
        return $this->belongsTo(Contract::class);
    }

    /** Stage of the revision requester (Bimo) */
    public function fromStage()
    {
        return $this->belongsTo(ContractReviewStage::class, 'from_stage_id');
    }

    /** Stage of the revision receiver (Bunga / Sari) */
    public function toStage()
    {
        return $this->belongsTo(ContractReviewStage::class, 'to_stage_id');
    }

    /** User who requested the revision */
    public function requester()
    {
        return $this->belongsTo(TblUser::class, 'requested_by', 'id_user');
    }

    /** User who is assigned to handle the revision */
    public function assignee()
    {
        return $this->belongsTo(TblUser::class, 'assigned_to', 'id_user');
    }

    /** Parent task (if this is a re-request) */
    public function parentTask()
    {
        return $this->belongsTo(self::class, 'parent_task_id');
    }

    /** Child tasks created from this task (re-requests) */
    public function childTasks()
    {
        return $this->hasMany(self::class, 'parent_task_id');
    }

    // =====================================================
    // SCOPES
    // =====================================================

    public function scopeActive($query)
    {
        return $query->whereIn('status', ['pending', 'in_progress', 'submitted', 're_requested']);
    }

    public function scopePendingFor($query, int $userId)
    {
        return $query->where('assigned_to', $userId)
                     ->whereIn('status', ['pending', 'in_progress', 're_requested']);
    }

    public function scopeSubmittedTo($query, int $userId)
    {
        return $query->where('requested_by', $userId)
                     ->where('status', 'submitted');
    }

    public function scopeForStage($query, int $stageId)
    {
        return $query->where('from_stage_id', $stageId);
    }

    // =====================================================
    // HELPERS
    // =====================================================

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isInProgress(): bool
    {
        return $this->status === 'in_progress';
    }

    public function isSubmitted(): bool
    {
        return $this->status === 'submitted';
    }

    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    public function isReRequested(): bool
    {
        return $this->status === 're_requested';
    }

    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }

    /** Task still requires action from the assignee */
    public function needsActionFromAssignee(): bool
    {
        return in_array($this->status, ['pending', 'in_progress', 're_requested']);
    }

    /** Task has been submitted and is waiting for requester decision */
    public function awaitingRequesterDecision(): bool
    {
        return $this->status === 'submitted';
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'pending'      => 'Pending',
            'in_progress'  => 'In Progress',
            'submitted'    => 'Submitted',
            'approved'     => 'Approved',
            're_requested' => 'Re-Requested',
            'cancelled'    => 'Cancelled',
            default        => ucfirst($this->status),
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'pending'      => 'text-yellow-400 bg-yellow-500/10 border-yellow-500/30',
            'in_progress'  => 'text-blue-400 bg-blue-500/10 border-blue-500/30',
            'submitted'    => 'text-purple-400 bg-purple-500/10 border-purple-500/30',
            'approved'     => 'text-green-400 bg-green-500/10 border-green-500/30',
            're_requested' => 'text-orange-400 bg-orange-500/10 border-orange-500/30',
            'cancelled'    => 'text-gray-400 bg-gray-500/10 border-gray-500/30',
            default        => 'text-gray-400 bg-gray-500/10 border-gray-500/30',
        };
    }
}
