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
        'parallel_group',
        'has_open_revision',   // ← BARU: flag apakah stage ini punya revision task terbuka
    ];

    protected $casts = [
        'assigned_at'          => 'datetime',
        'started_at'           => 'datetime',
        'completed_at'         => 'datetime',
        'revision_requested_at'=> 'datetime',
        'visited_at'           => 'datetime',
        'needs_revision'       => 'boolean',
        'is_manual_added'      => 'boolean',
        'has_open_revision'    => 'boolean',   // ← BARU
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

    /**
     * Semua revision task yang DIKIRIM DARI stage ini (stage Bimo).
     * Bimo adalah "from_stage".
     */
    public function sentRevisionTasks()
    {
        return $this->hasMany(ContractRevisionTask::class, 'from_stage_id');
    }

    /**
     * Semua revision task yang DITERIMA oleh stage ini (stage Bunga/Sari).
     * Bunga/Sari adalah "to_stage".
     */
    public function receivedRevisionTasks()
    {
        return $this->hasMany(ContractRevisionTask::class, 'to_stage_id');
    }

    /**
     * Shortcut: revision tasks aktif yang dikirim oleh stage ini.
     */
    public function activeRevisionTasks()
    {
        return $this->hasMany(ContractRevisionTask::class, 'from_stage_id')
                    ->whereIn('status', ['pending', 'in_progress', 'submitted', 're_requested']);
    }

    /**
     * Shortcut: revision tasks aktif yang diterima oleh stage ini.
     */
    public function pendingReceivedTasks()
    {
        return $this->hasMany(ContractRevisionTask::class, 'to_stage_id')
                    ->whereIn('status', ['pending', 'in_progress', 're_requested']);
    }

    /**
     * Revision tasks yang sudah di-submit ke stage ini (menunggu keputusan Bimo).
     */
    public function submittedRevisionTasks()
    {
        return $this->hasMany(ContractRevisionTask::class, 'from_stage_id')
                    ->where('status', 'submitted');
    }

    /* =====================================================
     | STAGE STATE HELPERS
     |=====================================================*/

    public function isActive(): bool
    {
        // PARALLEL → aktif kalau status in_progress atau assigned
        if (!is_null($this->parallel_group)) {
            return in_array($this->status, ['in_progress', 'assigned']);
        }

        // SEQUENTIAL → harus sesuai current_stage + status aktif
        return $this->contract
            && (int) $this->contract->current_stage === (int) $this->sequence
            && in_array($this->status, ['in_progress', 'assigned']);
    }

    public function isParallel(): bool
    {
        return !is_null($this->parallel_group);
    }

    public function isLastStage(): bool
    {
        // Parallel stage tidak pernah dianggap "last stage"
        if (!is_null($this->parallel_group)) {
            return false;
        }

        $hasNextSequential = ContractReviewStage::where('contract_id', $this->contract_id)
            ->whereNull('parallel_group')
            ->where('sequence', '>', $this->sequence)
            ->whereNotIn('status', ['completed', 'rejected'])
            ->exists();

        if ($hasNextSequential) {
            return false;
        }

        $hasPendingParallel = ContractReviewStage::where('contract_id', $this->contract_id)
            ->whereNotNull('parallel_group')
            ->whereIn('status', ['pending', 'assigned'])
            ->exists();

        if ($hasPendingParallel) {
            return false;
        }

        return true;
    }


    public function markAsRejected(string $notes = null)
    {
        $this->update([
            'status' => 'rejected',
            'notes' => $notes,
            'completed_at' => now(),
        ]);

        // Optional: trigger contract sync
        $this->contract?->update([
            'status' => 'rejected'
        ]);
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

    /**
     * Apakah stage ini bisa Approve / lanjut ke stage berikutnya.
     *
     * Bimo tidak boleh approve stage utama jika:
     * - Masih ada revision task yang belum selesai (submitted menunggu keputusan, atau pending/in_progress)
     *
     * NOTE: Ini adalah REKOMENDASI soft-block. Controller boleh override dengan force_approve.
     */
    public function canApproveStage(): bool
    {
        if (!$this->has_open_revision) {
            return true;
        }

        // Cek real-time ke tabel revision_tasks
        $openCount = ContractRevisionTask::where('from_stage_id', $this->id)
            ->whereIn('status', ['pending', 'in_progress', 're_requested'])
            ->count();

        return $openCount === 0;
    }

    /**
     * Berapa revision task yang menunggu keputusan Bimo (status submitted).
     */
    public function pendingDecisionCount(): int
    {
        return ContractRevisionTask::where('from_stage_id', $this->id)
            ->where('status', 'submitted')
            ->count();
    }

    /**
     * Berapa revision task yang masih dikerjakan penerima.
     */
    public function openRevisionCount(): int
    {
        return ContractRevisionTask::where('from_stage_id', $this->id)
            ->whereIn('status', ['pending', 'in_progress', 're_requested'])
            ->count();
    }

    public function getDisplayStatusAttribute(): string
    {
        if ($this->relationLoaded('contractDepartment')) {
            $contractDept = $this->contractDepartment;
        } else {
            $contractDept = \App\Models\ContractDepartment::where(...)
                ->first();
        }

        if ($contractDept && $contractDept->status === 'declined') {
            return 'declined';
        }

        return $this->status;
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
        // 1. Assigned user selalu bisa akses
        if ((int) $this->assigned_user_id === (int) $user->id_user) {
            return true;
        }

        // 2. Admin selalu bisa akses
        if ($user->hasRole('admin')) {
            return true;
        }

        // 3. PARALLEL → cek apakah parallel group sudah diaktifkan
        if ($this->isParallel()) {
            $parallelActivated = ContractReviewStage::where('contract_id', $this->contract_id)
                ->where('parallel_group', $this->parallel_group)
                ->where('id', '!=', $this->id)
                ->whereIn('status', ['in_progress', 'completed', 'assigned'])
                ->exists();

            return $parallelActivated;
        }

        // 4. SEQUENTIAL → previous stage harus completed
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