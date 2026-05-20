<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\DB;
use App\Models\TblUser;

class Contract extends Model
{
    use HasFactory, SoftDeletes;

    // ====================
    // STATUS CONSTANTS
    // ====================
    const STATUS_DRAFT = 'draft';
    const STATUS_SUBMITTED = 'submitted';
    const STATUS_AWAITING_DOCUMENT_UPLOAD = 'awaiting_document_upload';
    const STATUS_DOCUMENT_UPLOADED = 'document_uploaded';
    const STATUS_USER_REVIEWING = 'user_reviewing';
    const STATUS_USER_REVIEW_COMPLETE = 'user_review_complete';
    const STATUS_LEGAL_REVIEWING_FEEDBACK = 'legal_reviewing_feedback';
    const STATUS_LEGAL_REVIEWING = 'legal_reviewing';
    const STATUS_UNDER_REVIEW = 'under_review';
    const STATUS_LEGAL_APPROVED = 'legal_approved';
    const STATUS_FINAL_APPROVED = 'final_approved';
    const STATUS_NUMBER_ISSUED = 'number_issued';
    const STATUS_RELEASED = 'released';
    const STATUS_EXECUTED = 'executed';
    const STATUS_ARCHIVED = 'archived';
    const STATUS_REVISION_NEEDED = 'revision_needed';
    const STATUS_DECLINED = 'declined';
    const STATUS_CANCELLED = 'cancelled';

    // ====================
    // DEPARTMENT STATUS CONSTANTS
    // ====================
    const STATUS_FINANCE_REVIEWING = 'finance_reviewing';
    const STATUS_FINANCE_APPROVED = 'finance_approved';
    const STATUS_ACCOUNTING_REVIEWING = 'accounting_reviewing';
    const STATUS_ACCOUNTING_APPROVED = 'accounting_approved';
    const STATUS_TAX_REVIEWING = 'tax_reviewing';
    const STATUS_TAX_APPROVED = 'tax_approved';

    // ====================
    // REVIEW FLOW STATUS CONSTANTS
    // PENTING: sesuaikan dengan enum di DB ('rejected' lowercase)
    // ====================
    const REVIEW_FLOW_PENDING_ASSIGNMENT = 'pending_assignment';
    const REVIEW_FLOW_IN_REVIEW = 'in_review';
    const REVIEW_FLOW_COMPLETED = 'completed';
    const REVIEW_FLOW_REVISION_REQUESTED = 'revision_requested';
    const REVIEW_FLOW_REJECTED = 'rejected'; // ← FIX: lowercase, sesuai DB enum

    // ====================
    // LEGAL STATUS CONSTANTS
    // ====================
    const LEGAL_STATUS_PENDING = 'pending';
    const LEGAL_STATUS_ASSIGNED = 'assigned';
    const LEGAL_STATUS_UNDER_REVIEW = 'under_review';
    const LEGAL_STATUS_COMPLETED = 'completed';
    const LEGAL_STATUS_REVISION_REQUESTED = 'revision_requested';

    // ====================
    // TABLE COLUMNS
    // ====================
    protected $fillable = [
        'contract_number',
        'title',
        'contract_type',
        'description',
        'counterparty_name',
        'counterparty_email',
        'counterparty_phone',
        'effective_date',
        'expiry_date',
        'drafting_deadline',
        'contract_value',
        'currency',
        'additional_notes',
        'purpose',
        'status',
        'synology_folder_path',
        'document_uploaded_at',
        'department_code',
        'document_uploaded_by',
        'user_review_started_at',
        'user_review_completed_at',
        'user_feedback',
        'finance_review_started_at',
        'finance_review_completed_at',
        'finance_feedback',
        'accounting_review_started_at',
        'accounting_review_completed_at',
        'accounting_feedback',
        'tax_review_started_at',
        'tax_review_completed_at',
        'tax_feedback',
        'legal_approved_at',
        'legal_approved_by',
        'final_approved_at',
        'final_approved_by',
        'released_at',
        'number_issued_at',
        'executed_at',
        'executed_by',
        'archived_at',
        'archived_by',
        'user_id',
        'legal_assigned_id',
        'finance_assigned_id',
        'accounting_assigned_id',
        'tax_assigned_id',
        'current_stage',
        'current_stage_id',
        'review_flow_status',
        'legal_review_started_at',
        'legal_review_completed_at',
        'legal_notes',
        'legal_status',
        'legal_feedback',
        'submitted_at',
        'legal_reviewed_at',
        'selected_departments',
        'allow_stage_addition',
        'workflow_type',
        'multi_department_status',
        'surat_file_path',
        'surat_file_size',
        'surat_file_mime',
        // ── FIX: Tambahkan field executing & archiving stage ID ──────────
        'executing_stage_id',
        'archiving_stage_id',
        'deadline_reminder_3d_sent_at',
        'deadline_reminder_1d_sent_at',
        'company_code',
    ];

    protected $casts = [
        'effective_date'                => 'date',
        'expiry_date'                   => 'date',
        'drafting_deadline'             => 'date',
        'contract_value'                => 'decimal:2',
        'status'                        => 'string',
        'document_uploaded_at'          => 'datetime',
        'user_review_started_at'        => 'datetime',
        'user_review_completed_at'      => 'datetime',
        'finance_review_started_at'     => 'datetime',
        'finance_review_completed_at'   => 'datetime',
        'accounting_review_started_at'  => 'datetime',
        'accounting_review_completed_at'=> 'datetime',
        'tax_review_started_at'         => 'datetime',
        'tax_review_completed_at'       => 'datetime',
        'legal_approved_at'             => 'datetime',
        'final_approved_at'             => 'datetime',
        'number_issued_at'              => 'datetime',
        'executed_at'                   => 'datetime',
        'archived_at'                   => 'datetime',
        'released_at'                   => 'datetime',
        'legal_review_started_at'       => 'datetime',
        'legal_review_completed_at'     => 'datetime',
        'submitted_at'                  => 'datetime',
        'legal_reviewed_at'             => 'datetime',
        'current_stage'                 => 'integer',
        'selected_departments'          => 'array',
        'allow_stage_addition'          => 'boolean',
        'deadline_reminder_3d_sent_at' => 'datetime',
        'deadline_reminder_1d_sent_at' => 'datetime',
    ];

    protected $attributes = [
        'status'             => self::STATUS_DRAFT,
        'review_flow_status' => self::REVIEW_FLOW_PENDING_ASSIGNMENT,
        'current_stage'      => 1,
        'legal_status'       => self::LEGAL_STATUS_PENDING,
    ];

    // ====================
    // BOOT METHOD
    // ====================
    protected static function booted()
    {
        static::updating(function ($contract) {
            $originalStatus = $contract->getOriginal('status');
            $newStatus      = $contract->status;

            if ($originalStatus !== $newStatus) {
                $timestampMap = [
                    self::STATUS_SUBMITTED      => ['submitted_at'      => now()],
                    self::STATUS_LEGAL_APPROVED => ['legal_approved_at' => now()],
                    self::STATUS_FINAL_APPROVED => ['final_approved_at' => now()],
                    self::STATUS_EXECUTED       => ['executed_at'       => now()],
                    self::STATUS_ARCHIVED       => ['archived_at'       => now()],
                ];

                if (isset($timestampMap[$newStatus])) {
                    foreach ($timestampMap[$newStatus] as $key => $value) {
                        $contract->$key = $value;
                    }
                }
            }
        });
    }

    // ====================
    // RELATIONSHIPS
    // ====================
    public function user()
    {
        return $this->belongsTo(TblUser::class, 'user_id', 'id_user');
    }

    public function legalComments()
    {
        return $this->hasMany(LegalContractComment::class, 'contract_id');
    }

    public function legalAssigned()
    {
        return $this->belongsTo(TblUser::class, 'legal_assigned_id', 'id_user');
    }

    public function financeAssigned()
    {
        return $this->belongsTo(TblUser::class, 'finance_assigned_id', 'id_user');
    }

    public function accountingAssigned()
    {
        return $this->belongsTo(TblUser::class, 'accounting_assigned_id', 'id_user');
    }

    public function taxAssigned()
    {
        return $this->belongsTo(TblUser::class, 'tax_assigned_id', 'id_user');
    }

    public function documentUploadedBy()
    {
        return $this->belongsTo(TblUser::class, 'document_uploaded_by', 'id_user');
    }

    public function legalApprovedBy()
    {
        return $this->belongsTo(TblUser::class, 'legal_approved_by', 'id_user');
    }

    public function finalApprovedBy()
    {
        return $this->belongsTo(TblUser::class, 'final_approved_by', 'id_user');
    }

    public function executedBy()
    {
        return $this->belongsTo(TblUser::class, 'executed_by', 'id_user');
    }

    public function archivedBy()
    {
        return $this->belongsTo(TblUser::class, 'archived_by', 'id_user');
    }

    public function contractDepartments()
    {
        return $this->hasMany(\App\Models\ContractDepartment::class);
    }

    public function departments()
    {
        return $this->belongsToMany(\App\Models\Department::class, 'contract_departments')
                    ->withPivot(['status', 'assigned_admin_id', 'assigned_at', 'completed_at']);
    }

    public function departmentReviews()
    {
        return $this->hasMany(\App\Models\ContractDepartment::class, 'contract_id');
    }

    public function pendingDepartmentReviews()
    {
        return $this->hasMany(\App\Models\ContractDepartment::class, 'contract_id')
                    ->where('status', 'pending_assignment');
    }

    public function financeDepartmentReview()
    {
        return $this->hasOne(\App\Models\ContractDepartment::class, 'contract_id')
                    ->whereHas('department', fn($q) => $q->where('code', 'FIN'));
    }

    public function accountingDepartmentReview()
    {
        return $this->hasOne(\App\Models\ContractDepartment::class, 'contract_id')
                    ->whereHas('department', fn($q) => $q->where('code', 'ACC'));
    }

    public function taxDepartmentReview()
    {
        return $this->hasOne(\App\Models\ContractDepartment::class, 'contract_id')
                    ->whereHas('department', fn($q) => $q->where('code', 'TAX'));
    }

    // ── Review Stage relationships ──────────────────────────────
    public function reviewStages()
    {
        return $this->hasMany(ContractReviewStage::class)->orderBy('sequence');
    }

    public function currentStage()
    {
        return $this->hasOne(ContractReviewStage::class, 'contract_id')
                    ->where('sequence', $this->current_stage);
    }

    /**
     * Relasi ke executing stage (shortcut via FK)
     */
    public function executingStage()
    {
        return $this->belongsTo(ContractReviewStage::class, 'executing_stage_id');
    }

    /**
     * Relasi ke archiving stage (shortcut via FK)
     */
    public function archivingStage()
    {
        return $this->belongsTo(ContractReviewStage::class, 'archiving_stage_id');
    }

    public function reviewJumps()
    {
        return $this->hasManyThrough(
            ContractReviewJump::class,
            ContractReviewStage::class,
            'contract_id',
            'from_stage_id',
            'id',
            'id'
        );
    }

    public function reviewers()
    {
        return $this->hasManyThrough(
            TblUser::class,
            ContractReviewStage::class,
            'contract_id',
            'id_user',
            'id',
            'assigned_user_id'
        );
    }

    public function reviewLogs()
    {
        return $this->hasMany(ContractReviewLog::class)
            ->with(['user', 'stage'])
            ->orderBy('created_at', 'asc');
    }

    public function workflowLogs()
    {
        return $this->hasMany(ContractReviewLog::class)
            ->whereNull('stage_id')
            ->orderBy('created_at', 'asc');
    }

    public function stageLogs()
    {
        return $this->hasMany(ContractReviewLog::class)
            ->whereNotNull('stage_id')
            ->with(['stage', 'user'])
            ->orderBy('created_at', 'asc');
    }

    // ====================
    // SCOPES
    // ====================
    public function scopeDraft($query)
    {
        return $query->where('status', self::STATUS_DRAFT);
    }


    public function scopeVisibleTo($query, $user)
    {
        if ($user->hasAnyRole(['admin', 'legal'])) {
            return $query;
        }

        return $query->where(function ($q) use ($user) {
            $q->where('user_id', $user->id)
            ->orWhereHas('reviewStages', function ($q2) use ($user) {
                $q2->where('assigned_user_id', $user->id);
            });
        });
    }

    public function scopeLegalReviewQueue($query)
    {
        return $query
            ->whereNotIn('status', [
                self::STATUS_DECLINED,
                self::STATUS_CANCELLED,
                self::STATUS_FINAL_APPROVED,
                self::STATUS_RELEASED,
            ])
            ->where(function ($q) {
                $q->where(function ($sub) {
                    $sub->where('status', self::STATUS_SUBMITTED)
                        ->where('review_flow_status', self::REVIEW_FLOW_PENDING_ASSIGNMENT);
                })
                ->orWhere(function ($sub) {
                    $sub->whereIn('status', [
                            self::STATUS_UNDER_REVIEW,
                            self::STATUS_LEGAL_REVIEWING_FEEDBACK,
                        ])
                        ->whereIn('legal_status', [
                            self::LEGAL_STATUS_PENDING,
                            self::LEGAL_STATUS_ASSIGNED,
                            self::LEGAL_STATUS_UNDER_REVIEW,
                        ]);
                });
            });
    }

    public function scopeSubmitted($query)
    {
        return $query->where('status', self::STATUS_SUBMITTED);
    }

    public function scopeForLegalReview($query)
    {
        return $query->whereIn('status', [
            self::STATUS_SUBMITTED,
            self::STATUS_AWAITING_DOCUMENT_UPLOAD,
            self::STATUS_DOCUMENT_UPLOADED,
            self::STATUS_USER_REVIEW_COMPLETE,
            self::STATUS_LEGAL_REVIEWING_FEEDBACK,
            self::STATUS_REVISION_NEEDED,
            self::STATUS_UNDER_REVIEW,
        ]);
    }

    public function scopeWithActiveReview($query)
    {
        return $query->where('review_flow_status', self::REVIEW_FLOW_IN_REVIEW)
            ->whereNotNull('current_stage');
    }

    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeAssignedToLegal($query, $legalId)
    {
        return $query
            ->where('legal_assigned_id', $legalId)
            ->where('review_flow_status', self::REVIEW_FLOW_IN_REVIEW)
            ->whereIn('legal_status', [
                self::LEGAL_STATUS_ASSIGNED,
                self::LEGAL_STATUS_UNDER_REVIEW,
            ]);
    }

    public function scopePendingLegal($query)
    {
        return $query
            ->where('status', self::STATUS_SUBMITTED)
            ->where('review_flow_status', self::REVIEW_FLOW_PENDING_ASSIGNMENT)
            ->whereNull('legal_assigned_id');
    }

    // ====================
    // HELPER METHODS — STATUS
    // ====================
    public function isOwnedBy(TblUser $user): bool
    {
        return (int) $this->user_id === (int) $user->id_user;
    }

    public function canBeExecuted(TblUser $user): bool
    {
        // Executing stage sudah aktif (assigned/in_progress) dan user adalah owner
        $executingStage = $this->reviewStages()
            ->where('stage_type', 'executing')
            ->whereIn('status', ['assigned', 'in_progress'])
            ->first();

        return $executingStage !== null
            && (int) $this->user_id === (int) $user->id_user;
    }

    public function canBeArchived(TblUser $user): bool
    {
        $archivingStage = $this->reviewStages()
            ->where('stage_type', 'archiving')
            ->whereIn('status', ['assigned', 'in_progress'])
            ->first();

        return $archivingStage !== null
            && $user->hasAnyRole(['legal', 'admin']);
    }

    public function isExecuted(): bool
    {
        return $this->status === self::STATUS_EXECUTED;
    }

    public function isArchived(): bool
    {
        return $this->status === self::STATUS_ARCHIVED;
    }

    public function canGenerateNumber(): bool
    {
        // Bisa generate jika ada stage legal aktif DAN belum punya nomor
        return !empty($this->contract_number) === false
            && $this->reviewStages()
                ->where('stage_type', 'legal')
                ->whereIn('status', ['in_progress', 'assigned'])
                ->exists();
    }

    public function isSurat(): bool
    {
        return $this->contract_type === 'surat';
    }

    public function isSuratRequest(): bool
    {
        return $this->contract_type === 'surat' && $this->workflow_type === 'static';
    }

    public function isContractReview(): bool
    {
        return !$this->isSuratRequest();
    }

    public function getRouteKeyName()
    {
        return 'id';
    }

    public static function getStatuses()
    {
        return [
            self::STATUS_DRAFT                     => 'Draft',
            self::STATUS_SUBMITTED                 => 'Submitted',
            self::STATUS_AWAITING_DOCUMENT_UPLOAD  => 'Awaiting Document Upload',
            self::STATUS_DOCUMENT_UPLOADED         => 'Document Uploaded to Synology',
            self::STATUS_USER_REVIEWING            => 'User Reviewing in Synology',
            self::STATUS_USER_REVIEW_COMPLETE      => 'User Review Complete',
            self::STATUS_LEGAL_REVIEWING_FEEDBACK  => 'Legal Reviewing Feedback',
            self::STATUS_UNDER_REVIEW              => 'Under Review',
            self::STATUS_LEGAL_APPROVED            => 'Legal Approved',
            self::STATUS_FINANCE_REVIEWING         => 'Finance Reviewing',
            self::STATUS_FINANCE_APPROVED          => 'Finance Approved',
            self::STATUS_ACCOUNTING_REVIEWING      => 'Accounting Reviewing',
            self::STATUS_ACCOUNTING_APPROVED       => 'Accounting Approved',
            self::STATUS_TAX_REVIEWING             => 'Tax Reviewing',
            self::STATUS_TAX_APPROVED              => 'Tax Approved',
            self::STATUS_FINAL_APPROVED            => 'Approved',
            self::STATUS_NUMBER_ISSUED             => 'Number Issued',
            self::STATUS_RELEASED                  => 'Completed',
            self::STATUS_EXECUTED                  => 'Executed',
            self::STATUS_ARCHIVED                  => 'Archived',
            self::STATUS_REVISION_NEEDED           => 'Revision Needed',
            self::STATUS_DECLINED                  => 'Declined',
            self::STATUS_CANCELLED                 => 'Cancelled',
        ];
    }

    public static function getReviewFlowStatuses()
    {
        return [
            self::REVIEW_FLOW_PENDING_ASSIGNMENT => 'Pending Assignment',
            self::REVIEW_FLOW_IN_REVIEW          => 'In Review',
            self::REVIEW_FLOW_COMPLETED          => 'Completed',
            self::REVIEW_FLOW_REVISION_REQUESTED => 'Revision Requested',
            self::REVIEW_FLOW_REJECTED           => 'Declined',
        ];
    }

    public static function getLegalStatuses()
    {
        return [
            self::LEGAL_STATUS_PENDING            => 'Pending',
            self::LEGAL_STATUS_ASSIGNED           => 'Assigned',
            self::LEGAL_STATUS_UNDER_REVIEW       => 'Under Review',
            self::LEGAL_STATUS_COMPLETED          => 'Completed',
            self::LEGAL_STATUS_REVISION_REQUESTED => 'Revision Requested',
        ];
    }

    public function getSelectedDepartmentsArray(): array
    {
        if (is_array($this->selected_departments)) {
            return $this->selected_departments;
        }
        return $this->selected_departments ? json_decode($this->selected_departments, true) ?? [] : [];
    }

    public function hasDepartmentSelected($departmentCode): bool
    {
        return in_array($departmentCode, $this->getSelectedDepartmentsArray());
    }

    public function canAddStage(TblUser $user): bool
    {
        if (!$this->isInReview()) return false;
        if (!$this->allow_stage_addition) return false;

        return $user->hasAnyRole(['legal', 'admin', 'admin_fin', 'admin_acc', 'admin_tax']);
    }

    public function getNextSequenceNumber()
    {
        $lastStage = $this->reviewStages()->orderBy('sequence', 'desc')->first();
        return $lastStage ? $lastStage->sequence + 1 : 1;
    }

    public function isUserInvolved(TblUser $user): bool
    {
        if ($this->isOwnedBy($user)) return true;

        if (in_array((int) $user->id_user, array_filter([
            (int) $this->legal_assigned_id,
            (int) $this->finance_assigned_id,
            (int) $this->accounting_assigned_id,
            (int) $this->tax_assigned_id,
        ]), true)) {
            return true;
        }

        if ($this->reviewStages()->where('assigned_user_id', (int) $user->id_user)->exists()) {
            return true;
        }

        return $user->hasRole('admin') || $user->can('contract_view_all');
    }

    // ====================
    // ACCESSORS
    // ====================
    public function getStatusLabelAttribute()
    {
        return self::getStatuses()[$this->status] ?? $this->status;
    }

    public function getReviewFlowStatusLabelAttribute()
    {
        return self::getReviewFlowStatuses()[$this->review_flow_status] ?? $this->review_flow_status;
    }

    public function getLegalStatusLabelAttribute()
    {
        return self::getLegalStatuses()[$this->legal_status] ?? $this->legal_status;
    }

    public function getStatusColorAttribute()
    {
        $colors = [
            'draft'                    => 'bg-gray-700 text-gray-300',
            'submitted'                => 'bg-yellow-500/20 text-yellow-300',
            'awaiting_document_upload' => 'bg-orange-500/20 text-orange-300',
            'document_uploaded'        => 'bg-cyan-500/20 text-cyan-300',
            'user_reviewing'           => 'bg-orange-500/20 text-orange-300',
            'user_review_complete'     => 'bg-teal-500/20 text-teal-300',
            'legal_reviewing_feedback' => 'bg-blue-500/20 text-blue-300',
            'under_review'             => 'bg-blue-500/20 text-blue-300',
            'legal_approved'           => 'bg-green-500/20 text-green-300',
            'finance_reviewing'        => 'bg-purple-500/20 text-purple-300',
            'finance_approved'         => 'bg-purple-500/30 text-purple-400',
            'accounting_reviewing'     => 'bg-indigo-500/20 text-indigo-300',
            'accounting_approved'      => 'bg-indigo-500/30 text-indigo-400',
            'tax_reviewing'            => 'bg-pink-500/20 text-pink-300',
            'tax_approved'             => 'bg-pink-500/30 text-pink-400',
            'executed'                 => 'bg-blue-700 text-blue-200',
            'archived'                 => 'bg-slate-700 text-slate-300',
            'final_approved'           => 'bg-green-700 text-green-300',
            'released'                 => 'bg-green-700 text-green-300',
            'revision_needed'          => 'bg-red-500/20 text-red-300',
            'declined'                 => 'bg-red-700 text-red-300',
            'cancelled'                => 'bg-gray-800 text-gray-400',
        ];

        return $colors[$this->status] ?? 'bg-gray-700 text-gray-300';
    }

    public function getReviewProgressAttribute()
    {
        $totalStages     = $this->reviewStages()->count();
        $completedStages = $this->reviewStages()->where('status', 'completed')->count();

        return $totalStages > 0 ? ($completedStages / $totalStages) * 100 : 0;
    }

    public function getProgressWithLabelAttribute()
    {
        $total     = $this->reviewStages()->count();
        $completed = $this->reviewStages()->where('status', 'completed')->count();
        return "{$completed}/{$total} Stages Completed";
    }

    public function getDisplayStatusAttribute(): string
    {
        if ($this->status === self::STATUS_UNDER_REVIEW) {
            $stage = $this->currentReviewStage();
            if ($stage && $stage->assignedUser) {
                return 'Under Review: ' . $stage->assignedUser->nama_user;
            }
            return 'Under Review';
        }

        if ($this->status === self::STATUS_REVISION_NEEDED) {
            $stage = $this->currentReviewStage();
            if ($stage && $stage->assignedUser) {
                return 'Revision Needed: ' . $stage->assignedUser->nama_user;
            }
            return 'Revision Needed';
        }

        return self::getStatuses()[$this->status] ?? ucfirst(str_replace('_', ' ', $this->status));
    }

    public function getSuratFileSizeHumanAttribute(): string
    {
        if (!$this->surat_file_size) return '-';

        $bytes = $this->surat_file_size;
        $units = ['B', 'KB', 'MB', 'GB'];

        for ($i = 0; $bytes > 1024; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, 2) . ' ' . $units[$i];
    }

    // ====================
    // VALIDATION METHODS
    // ====================
    public function canBeSubmitted(): bool
    {
        return $this->status === self::STATUS_DRAFT;
    }

    public function canBeEdited(): bool
    {
        return $this->status === self::STATUS_DRAFT;
    }

    public function canBeCancelled(): bool
    {
        return in_array($this->status, [
            self::STATUS_DRAFT,
            self::STATUS_SUBMITTED,
            self::STATUS_AWAITING_DOCUMENT_UPLOAD,
        ]);
    }

    public function canStartReview(): bool
    {
        return $this->status === self::STATUS_SUBMITTED
            && ($this->review_flow_status === self::REVIEW_FLOW_PENDING_ASSIGNMENT
                || $this->review_flow_status === null);
    }

    public function isInReview(): bool
    {
        return in_array($this->status, [
            self::STATUS_UNDER_REVIEW,
            self::STATUS_REVISION_NEEDED,
            self::STATUS_FINAL_APPROVED, // masih in review (menunggu executing)
            self::STATUS_NUMBER_ISSUED,
            self::STATUS_EXECUTED,       // menunggu archiving
        ]);
    }

    public function isInReviewStageSystem(): bool
    {
        if (!$this->reviewStages()->exists()) return false;

        $validContractStatuses = [
            self::STATUS_UNDER_REVIEW,
            self::STATUS_REVISION_NEEDED,
            self::STATUS_FINAL_APPROVED,
            self::STATUS_NUMBER_ISSUED,
            self::STATUS_EXECUTED,
            self::STATUS_DECLINED
        ];

        $validFlowStatuses = [
            self::REVIEW_FLOW_IN_REVIEW,
            self::REVIEW_FLOW_REVISION_REQUESTED,
            self::REVIEW_FLOW_PENDING_ASSIGNMENT,
            self::REVIEW_FLOW_COMPLETED,
        ];

        return in_array($this->status, $validContractStatuses)
            || in_array($this->review_flow_status, $validFlowStatuses);
    }

    public function isWaitingForRevision(): bool
    {
        return $this->status === self::STATUS_REVISION_NEEDED
            && $this->review_flow_status === self::REVIEW_FLOW_REVISION_REQUESTED;
    }

    public function canBeRevisedBy($user): bool
    {
        if ((int) $this->user_id !== (int) $user->id_user) return false;
        if ($this->status !== self::STATUS_REVISION_NEEDED) return false;

        return !empty($this->legal_feedback)
            || !empty($this->finance_feedback)
            || !empty($this->accounting_feedback)
            || !empty($this->tax_feedback);
    }

    // ====================
    // STAGE NAVIGATION METHODS
    // ====================
    public function activeStage(): ?\App\Models\ContractReviewStage
    {
        $userId = auth()->id();

        // 1. Parallel stage milik user yang login
        if ($userId) {
            $myParallelStage = $this->reviewStages()
                ->whereNotNull('parallel_group')
                ->where('assigned_user_id', $userId)
                ->whereIn('status', ['in_progress', 'assigned', 'pending'])
                ->orderBy('sequence')
                ->first();

            if ($myParallelStage) {
                $parallelActivated = ContractReviewStage::where('contract_id', $this->id)
                    ->where('parallel_group', $myParallelStage->parallel_group)
                    ->where('id', '!=', $myParallelStage->id)
                    ->whereIn('status', ['in_progress', 'completed', 'assigned'])
                    ->exists();

                if ($parallelActivated) return $myParallelStage;
            }
        }

        // 2. Sequential in_progress milik user
        if ($userId) {
            $myInProgress = $this->reviewStages()
                ->whereNull('parallel_group')
                ->where('assigned_user_id', $userId)
                ->where('status', 'in_progress')
                ->first();

            if ($myInProgress) return $myInProgress;
        }

        // 3. Global fallback: in_progress pertama
        $inProgress = $this->reviewStages()
            ->where('status', 'in_progress')
            ->orderBy('sequence')
            ->first();

        if ($inProgress) return $inProgress;

        // 4. Revision needed: stage assigned milik user
        if ($this->status === self::STATUS_REVISION_NEEDED && $userId) {
            $myAssigned = $this->reviewStages()
                ->where('assigned_user_id', $userId)
                ->where('status', 'assigned')
                ->first();

            if ($myAssigned) return $myAssigned;
        }

        // 5. Berdasarkan current_stage
        if ($this->current_stage) {
            $current = $this->reviewStages()
                ->whereNull('parallel_group')
                ->where('sequence', $this->current_stage)
                ->whereNotIn('status', ['completed', 'rejected'])
                ->first();

            if ($current) return $current;
        }

        // 6. Fallback: assigned pertama
        return $this->reviewStages()
            ->where('status', 'assigned')
            ->orderBy('sequence')
            ->first();
    }

    public function currentReviewStage()
    {
        return $this->reviewStages()
            ->whereIn('status', ['assigned', 'in_progress'])
            ->orderBy('sequence')
            ->first();
    }

    public function hasActiveStage(): bool
    {
        return $this->reviewStages()
            ->where('status', 'in_progress')
            ->exists();
    }

    public function getNextStage()
    {
        return $this->reviewStages()
            ->where('sequence', '>', $this->current_stage)
            ->orderBy('sequence')
            ->first();
    }

    public function getPreviousStage()
    {
        return $this->reviewStages()
            ->where('sequence', '<', $this->current_stage)
            ->orderByDesc('sequence')
            ->first();
    }

    public function getAvailableJumpStages($currentStage)
    {
        $allStages = $this->reviewStages()
            ->with('assignedUser:id_user,nama_user,email')
            ->orderBy('sequence')
            ->get();

        $availableStages = $allStages->filter(function ($stage) use ($currentStage) {
            if ($stage->id === $currentStage->id) return false;
            if ($stage->is_user_stage || $stage->stage_type === 'user') return false;
            // Jangan tampilkan executing/archiving sebagai jump target
            if (in_array($stage->stage_type, ['executing', 'archiving'])) return false;
            return true;
        });

        return $availableStages->map(function ($stage) {
            return [
                'id'                 => $stage->id,
                'stage_name'         => $stage->stage_name,
                'stage_type'         => $stage->stage_type,
                'is_user_stage'      => $stage->is_user_stage ?? false,
                'assigned_user_id'   => $stage->assigned_user_id,
                'assigned_user_name' => $stage->assignedUser->nama_user ?? 'Unassigned',
                'sequence'           => $stage->sequence,
                'status'             => $stage->status,
                'is_completed'       => $stage->status === 'completed',
            ];
        })->values();
    }

    // ====================
    // USER ACTIONS
    // ====================
    public function getAvailableActions(TblUser $user): array
    {
        $actions = [];

        if ($this->isOwnedBy($user)) {
            if ($this->canBeEdited())    $actions[] = 'edit';
            if ($this->canBeSubmitted()) $actions[] = 'submit';
            if ($this->canBeCancelled()) $actions[] = 'cancel';
            if ($this->canBeRevisedBy($user)) $actions[] = 'revise';
        }

        if ($user->hasAnyRole(['legal', 'admin']) && $this->canStartReview()) {
            $actions[] = 'start_review';
        }

        return $actions;
    }

    // ====================
    // WORKFLOW METHODS
    // ====================
    public function submitForReview()
    {
        if ($this->status !== self::STATUS_DRAFT) {
            throw new \Exception('Only draft contracts can be submitted');
        }

        $this->update([
            'status'       => self::STATUS_SUBMITTED,
            'submitted_at' => now(),
        ]);
    }

    public function assignToLegal($legalUserId)
    {
        $this->update([
            'legal_assigned_id'       => $legalUserId,
            'status'                  => self::STATUS_UNDER_REVIEW,
            'review_flow_status'      => self::REVIEW_FLOW_IN_REVIEW,
            'legal_status'            => self::LEGAL_STATUS_ASSIGNED,
            'legal_review_started_at' => now(),
        ]);
    }

    /**
     * finishReview() — HANYA dipanggil setelah archiving selesai.
     * Untuk alur baru: executing → archiving → finishReview().
     * Tidak dipanggil langsung dari approveWithJump().
     */
    public function finishReview()
    {
        $this->update([
            'review_flow_status' => self::REVIEW_FLOW_COMPLETED,
            'status'             => self::STATUS_ARCHIVED,
            'archived_at'        => now(),
        ]);

        Log::info('Contract review fully finished (archived)', [
            'contract_id' => $this->id,
            'user_id'     => auth()->id(),
        ]);
    }

    /**
     * startDynamicReview() — helper untuk membuat workflow dari model
     */
    public function startDynamicReview($legalStages, $selectedDepartments = [], $notes = null)
    {
        return DB::transaction(function () use ($legalStages, $selectedDepartments, $notes) {
            $legalDept = \App\Models\Department::where('code', 'LEGAL')->first();

            ContractReviewStage::create([
                'contract_id'      => $this->id,
                'department_id'    => $legalDept->id,
                'stage_name'       => 'User Submission',
                'stage_type'       => 'user',
                'assigned_user_id' => $this->user_id,
                'sequence'         => 1,
                'status'           => 'completed',
                'notes'            => 'Contract submitted by user',
                'assigned_at'      => now(),
                'completed_at'     => now(),
            ]);

            $sequence = 2;
            foreach ($legalStages as $index => $stageData) {
                ContractReviewStage::create([
                    'contract_id'      => $this->id,
                    'department_id'    => $legalDept->id,
                    'stage_name'       => $stageData['name'],
                    'stage_type'       => 'legal',
                    'assigned_user_id' => $stageData['user_id'],
                    'sequence'         => $sequence,
                    'status'           => $sequence === 2 ? 'assigned' : 'pending',
                    'assigned_at'      => $sequence === 2 ? now() : null,
                    'is_manual_added'  => $index >= 2,
                ]);
                $sequence++;
            }

            $this->update([
                'status'                  => self::STATUS_UNDER_REVIEW,
                'review_flow_status'      => self::REVIEW_FLOW_IN_REVIEW,
                'current_stage'           => 2,
                'legal_assigned_id'       => $legalStages[0]['user_id'],
                'legal_review_started_at' => now(),
                'allow_stage_addition'    => true,
                'legal_notes'             => $notes,
                'workflow_type'           => 'dynamic',
                'selected_departments'    => !empty($selectedDepartments) ? json_encode($selectedDepartments) : null,
            ]);

            return true;
        });
    }
}