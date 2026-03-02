<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ContractReviewJump extends Model
{
    use HasFactory;

    protected $fillable = [
        'contract_id',
        'stage_name',
        'stage_type',
        'is_user_stage',
        'assigned_user_id',
        'sequence',
        'status',
        'notes',
        'assigned_at',
        'started_at',
        'completed_at',
        'parent_stage_id',
        'jump_to_stage_id',
        'visited_at',
        'visited_by',
        'from_stage_id',
        'to_stage_id',
        'jumped_by',
        'reason',
    ];

    /**
     * Mark stage as visited when opened
     */
    public function markVisited()
    {
        // hanya set sekali
        if ($this->visited_at === null) {
            $this->update([
                'visited_at' => now(),
                'visited_by' => Auth::id(),
            ]);
        }
    }

    /**
     * Authorization helper
     */
        public function canBeAccessedBy($user)
        {
            return $this->assigned_user_id === $user->id
                || $user->hasRole('admin');
        }
    

    // Relationships
    public function contract()
    {
        return $this->belongsTo(Contract::class);
    }

    public function fromStage()
    {
        return $this->belongsTo(ContractReviewStage::class, 'from_stage_id');
    }

    public function toStage()
    {
        return $this->belongsTo(ContractReviewStage::class, 'to_stage_id');
    }

    public function jumper()
    {
        return $this->belongsTo(TblUser::class, 'jumped_by');
    }

    // Helper Methods
    public function getJumpTypeAttribute()
    {
        if ($this->fromStage->sequence < $this->toStage->sequence) {
            return 'forward';
        } elseif ($this->fromStage->sequence > $this->toStage->sequence) {
            return 'backward';
        }
        return 'lateral';
    }
}