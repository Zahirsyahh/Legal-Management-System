<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\TblUser;

class ContractReviewStage extends Model
{
    protected $fillable = [
        'contract_id',
        'stage_name',
        'stage_type',
        'assigned_user_id',
        'sequence',
        'status',
        'notes',
        'assigned_at',
        'started_at',
        'completed_at',
        'jump_to_stage',
        'parent_stage_id',
        'jump_to_stage_id',
        'needs_revision',
        'revision_feedback',
        'revision_requested_at',
        'revision_requested_by',
        'department_id',
        'created_by',
        'is_manual_added',
        'add_reason',
        'original_sequence',
        'visited_at',
        'visited_by',
    ];

    protected $casts = [
        'assigned_at' => 'datetime',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'revision_requested_at' => 'datetime',
        'visited_at' => 'datetime',
        'needs_revision' => 'boolean',
        'is_manual_added' => 'boolean',
    ];

    /* =====================================================
     | RELATIONSHIPS
     |=====================================================*/

    public function contract()
    {
        return $this->belongsTo(Contract::class);
    }

    public function assignedUser()
    {
        return $this->belongsTo(TblUser::class, 'assigned_user_id', 'id_user');
    }

    public function creator()
    {
        return $this->belongsTo(TblUser::class, 'created_by', 'id_user');
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    /* =====================================================
     | STAGE STATE HELPERS
     |=====================================================*/

    public function isActive(): bool
    {
        return $this->contract
            && (int) $this->contract->current_stage === (int) $this->sequence;
    }

    public function isLastStage(): bool
    {
        return !$this->contract
            ->reviewStages()
            ->where('sequence', '>', $this->sequence)
            ->exists();
    }

    public function isManualAdded(): bool
    {
        return (bool) $this->is_manual_added;
    }

    public function getIsUserStageAttribute(): bool
    {
        return $this->stage_type === 'user'
            || $this->stage_name === 'user_review';
    }

    /* =====================================================
     | ACCESS & PERMISSION HELPERS
     |=====================================================*/

    public function isAssignedTo(TblUser $user): bool
    {
        return (int) $this->assigned_user_id === (int) $user->id;
    }

    public function canBeAccessedBy(TblUser $user): bool
    {
        // 1. Assigned user
        if ($this->isAssignedTo($user)) {
            return true;
        }

        // 2. Admin always allowed
        if ($user->hasRole('admin')) {
            return true;
        }

        // 3. Previous stage completed (except first stage)
        if ($this->sequence > 1) {
            return $this->contract
                ->reviewStages()
                ->where('sequence', $this->sequence - 1)
                ->where('status', 'completed')
                ->exists();
        }

        return false;
    }

    /* =====================================================
     | VISIT & TRACKING
     |=====================================================*/

    public function markVisited(): void
    {
        if ($this->visited_at === null && auth()->check()) {
            $this->update([
                'visited_at' => now(),
                'visited_by' => auth()->user()->id_user,
            ]);
        }
    }

    /* =====================================================
     | SCOPES
     |=====================================================*/

    public function scopeAvailableForJump($query, int $currentStageId)
    {
        return $query
            ->where('id', '!=', $currentStageId)
            ->where('status', '!=', 'completed')
            ->orderBy('sequence');
    }

    /* =====================================================
     | MISC
     |=====================================================*/

    public function getDepartmentCode(): ?string
    {
        return $this->department?->code;
    }
}
