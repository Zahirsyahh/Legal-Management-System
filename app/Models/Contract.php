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
    const STATUS_REVISION_NEEDED = 'revision_needed';
    const STATUS_DECLINED = 'declined';
    const STATUS_CANCELLED = 'cancelled';

    // ====================
    // DEPARTMENT STATUS CONSTANTS (untuk Finance, Accounting, Tax)
    // ====================
    const STATUS_FINANCE_REVIEWING = 'finance_reviewing';
    const STATUS_FINANCE_APPROVED = 'finance_approved';
    const STATUS_ACCOUNTING_REVIEWING = 'accounting_reviewing';
    const STATUS_ACCOUNTING_APPROVED = 'accounting_approved';
    const STATUS_TAX_REVIEWING = 'tax_reviewing';
    const STATUS_TAX_APPROVED = 'tax_approved';

    // ====================
    // REVIEW FLOW STATUS CONSTANTS
    // ====================
    const REVIEW_FLOW_PENDING_ASSIGNMENT = 'pending_assignment';
    const REVIEW_FLOW_IN_REVIEW = 'in_review';
    const REVIEW_FLOW_COMPLETED = 'completed';
    const REVIEW_FLOW_REVISION_REQUESTED = 'revision_requested';
    const REVIEW_FLOW_REJECTED = 'rejected';

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
        'department_code',      // Dari dropdown tbl_department
        'document_uploaded_by',
        'user_review_started_at',
        'user_review_completed_at',
        'user_feedback',
        'finance_review_started_at',        // 🔄 Ganti fat_review dengan finance_review
        'finance_review_completed_at',      // 🔄 Ganti fat_review dengan finance_review
        'finance_feedback',                 // 🔄 Ganti fat_feedback dengan finance_feedback
        'accounting_review_started_at',     // 🔄 Tambah untuk accounting
        'accounting_review_completed_at',   // 🔄 Tambah untuk accounting
        'accounting_feedback',              // 🔄 Tambah untuk accounting
        'tax_review_started_at',            // 🔄 Tambah untuk tax
        'tax_review_completed_at',          // 🔄 Tambah untuk tax
        'tax_feedback',                     // 🔄 Tambah untuk tax
        'legal_approved_at',
        'legal_approved_by',
        'final_approved_at',
        'final_approved_by',
        'released_at',           
        'number_issued_at', 
        'user_id',
        'legal_assigned_id',
        'finance_assigned_id',              // 🔄 Ganti fat_assigned dengan finance_assigned
        'accounting_assigned_id',           // 🔄 Tambah untuk accounting
        'tax_assigned_id',                  // 🔄 Tambah untuk tax
        'current_stage',
        'review_flow_status',
        'legal_review_started_at',
        'legal_review_completed_at',
        'legal_notes',
        'legal_status',
        'submitted_at',
        'legal_reviewed_at',
        'legal_feedback',
        'selected_departments',             // 🔥 Penting untuk dynamic workflow
        'allow_stage_addition',             // 🔥 Penting untuk dynamic workflow
        'workflow_type',                    // 🔥 Penting untuk dynamic workflow
        'surat_file_path',
        'surat_file_size',
    ];

    protected $casts = [
        'effective_date' => 'date',
        'expiry_date' => 'date',
        'contract_value' => 'decimal:2',
        'status' => 'string',
        'drafting_deadline' => 'date',
        'document_uploaded_at' => 'datetime',
        'user_review_started_at' => 'datetime',
        'user_review_completed_at' => 'datetime',
        'finance_review_started_at' => 'datetime',      // 🔄 Ganti fat
        'finance_review_completed_at' => 'datetime',    // 🔄 Ganti fat
        'accounting_review_started_at' => 'datetime',   // 🔄 Tambah
        'accounting_review_completed_at' => 'datetime', // 🔄 Tambah
        'tax_review_started_at' => 'datetime',          // 🔄 Tambah
        'tax_review_completed_at' => 'datetime',        // 🔄 Tambah
        'legal_approved_at' => 'datetime',
        'final_approved_at' => 'datetime',
        'number_issued_at' => 'datetime',
        'released_at' => 'datetime',
        'current_stage' => 'integer',
        'legal_review_started_at' => 'datetime',
        'legal_review_completed_at' => 'datetime',
        'submitted_at' => 'datetime',
        'legal_reviewed_at' => 'datetime',
        'selected_departments' => 'array',              // 🔥 Untuk dynamic workflow
        'allow_stage_addition' => 'boolean',            // 🔥 Untuk dynamic workflow
    ];

    protected $attributes = [
        'status' => self::STATUS_DRAFT,
        'review_flow_status' => self::REVIEW_FLOW_PENDING_ASSIGNMENT,
        'current_stage' => 1,
        'legal_status' => self::LEGAL_STATUS_PENDING,
    ];

    // ====================
    // BOOT METHOD
    // ====================
    protected static function booted(){
        static::updating(function ($contract) {
            // Auto-update timestamps based on status changes
            $originalStatus = $contract->getOriginal('status');
            $newStatus = $contract->status;

            if ($originalStatus !== $newStatus) {
                $timestampMap = [
                    self::STATUS_SUBMITTED => ['submitted_at' => now()],
                    self::STATUS_LEGAL_APPROVED => ['legal_approved_at' => now()],
                    self::STATUS_FINAL_APPROVED => ['final_approved_at' => now()],
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

    public function contractDepartments()
    {
        return $this->hasMany(\App\Models\ContractDepartment::class);
    }

    public function departments()
    {
        return $this->belongsToMany(\App\Models\Department::class, 'contract_departments')
                    ->withPivot(['status', 'assigned_admin_id', 'assigned_at', 'completed_at']);
    }

    // TAMBAHKAN relasi ini (tidak ada di kode Anda):
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
                    ->whereHas('department', function($q) {
                        $q->where('code', 'FIN');
                    });
    }

    public function accountingDepartmentReview()
    {
        return $this->hasOne(\App\Models\ContractDepartment::class, 'contract_id')
                    ->whereHas('department', function($q) {
                        $q->where('code', 'ACC');
                    });
    }

    public function taxDepartmentReview()
    {
        return $this->hasOne(\App\Models\ContractDepartment::class, 'contract_id')
                    ->whereHas('department', function($q) {
                        $q->where('code', 'TAX');
                    });
    }

    // NEW RELATIONSHIPS FOR REVIEW STAGE SYSTEM
    public function reviewStages()
    {
        return $this->hasMany(ContractReviewStage::class)->orderBy('sequence');
    }

    public function currentStage()
    {
        return $this->hasOne(ContractReviewStage::class, 'contract_id')
                    ->where('sequence', $this->current_stage);
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

    public function reviewLogs(){
    return $this->hasMany(ContractReviewLog::class)
        ->with(['user', 'stage'])
        ->orderBy('created_at', 'asc');
    }

    // ===============================
    // LOG TANPA STAGE (WORKFLOW / SYSTEM)
    // ===============================
    public function workflowLogs()
    {
        return $this->hasMany(ContractReviewLog::class)
            ->whereNull('stage_id')
            ->orderBy('created_at', 'asc');
    }

    // ===============================
    // LOG DENGAN STAGE (REVIEW STAGE)
    // ===============================
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
            // Belum di-assign (baru disubmit)
            $q->where(function ($sub) {
                $sub->where('status', self::STATUS_SUBMITTED)
                    ->where('review_flow_status', self::REVIEW_FLOW_PENDING_ASSIGNMENT);
            })

            // ATAU sudah masuk proses legal
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

    public function scopeAwaitingDocumentUpload($query)
    {
        return $query->where('status', self::STATUS_AWAITING_DOCUMENT_UPLOAD);
    }

    public function scopeForLegalReview($query){
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

    public function scopeWithActiveReview($query){
        return $query->where('review_flow_status', self::REVIEW_FLOW_IN_REVIEW)
            ->whereNotNull('current_stage');
    }

    public function scopeForFinanceReview($query)
    {
        return $query->whereIn('status', [
            self::STATUS_LEGAL_APPROVED,
            self::STATUS_FINANCE_REVIEWING,
            self::STATUS_UNDER_REVIEW,
            self::STATUS_REVISION_NEEDED,
        ]);
    }

    public function scopeForAccountingReview($query)
    {
        return $query->whereIn('status', [
            self::STATUS_FINANCE_APPROVED,
            self::STATUS_ACCOUNTING_REVIEWING,
            self::STATUS_UNDER_REVIEW,
            self::STATUS_REVISION_NEEDED,
        ]);
    }

    public function scopeForTaxReview($query)
    {
        return $query->whereIn('status', [
            self::STATUS_ACCOUNTING_APPROVED,
            self::STATUS_TAX_REVIEWING,
            self::STATUS_UNDER_REVIEW,
            self::STATUS_REVISION_NEEDED,
        ]);
    }

    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeNeedsLegalReview($query)
    {
        return $query->where('status', self::STATUS_SUBMITTED)
                     ->orWhere('status', self::STATUS_DOCUMENT_UPLOADED);
    }

    public function scopeAssignedToUserForReview($query, $userId)
    {
        return $query->whereHas('reviewStages', function ($q) use ($userId) {
            $q->where('assigned_user_id', $userId)
              ->where('status', 'assigned');
        });
    }

    public function scopeAssignedToLegal($query, $legalId){
        return $query
            ->where('legal_assigned_id', $legalId)
            ->where('review_flow_status', self::REVIEW_FLOW_IN_REVIEW)
            ->whereIn('legal_status', [
                self::LEGAL_STATUS_ASSIGNED,
                self::LEGAL_STATUS_UNDER_REVIEW,
            ]);
    }

    public function scopePendingLegal($query){
        return $query
            ->where('status', self::STATUS_SUBMITTED)
            ->where('review_flow_status', self::REVIEW_FLOW_PENDING_ASSIGNMENT)
            ->whereNull('legal_assigned_id');
    }

    public function scopeNeedsUserReview($query, $userId)
    {
        return $query->where('review_flow_status', self::REVIEW_FLOW_IN_REVIEW)
                     ->whereHas('reviewStages', function ($q) use ($userId) {
                         $q->where('assigned_user_id', $userId)
                           ->where('status', 'assigned');
                     });
    }

    // ====================
    // HELPER METHODS - STATUS
    // ====================

    public function isOwnedBy(TblUser $user): bool{
        return (int) $this->user_id === (int) $user->id;
    }

    // Contract.php
    /**
     * Check if contract can have number generated
     */
    public function canGenerateNumber(): bool
    {
        return $this->status === self::STATUS_FINAL_APPROVED 
            && empty($this->contract_number);
    }

    /**
     * Cek apakah ini surat keluar
     */
    public function isSurat(): bool
    {
        return $this->contract_type === 'surat';
    }

    /**
     * Cek apakah ini Surat Request Nomor (static workflow)
     * Digunakan untuk redirect ke show-surat.blade.php
     */
    public function isSuratRequest(): bool
    {
        return $this->contract_type === 'surat' 
            && $this->workflow_type === 'static';
    }

    public function isContractReview(): bool
    {
        return !$this->isSuratRequest();
    }

    /**
     * Route binding: otomatis arahkan ke view yang sesuai
     */
    public function getRouteKeyName()
    {
        return 'id'; // tetap pakai id untuk route binding
    }
        
    /**
     * Cek apakah status DRAFT
     */
    public function isSuratDraft(): bool
    {
        return $this->isSurat() && $this->status === self::STATUS_DRAFT;
    }
    
    /**
     * Cek apakah status SUBMITTED
     */
    public function isSuratSubmitted(): bool
    {
        return $this->isSurat() && $this->status === self::STATUS_SUBMITTED;
    }
    
    /**
     * Cek apakah status LEGAL_APPROVED
     */
    public function isSuratApproved(): bool
    {
        return $this->isSurat() && $this->status === self::STATUS_LEGAL_APPROVED;
    }
    
    /**
     * Cek apakah status FINAL_APPROVED (nomor digenerate)
     */
    public function isSuratNumberGenerated(): bool
    {
        return $this->isSurat() && 
               $this->status === self::STATUS_FINAL_APPROVED && 
               !empty($this->contract_number);
    }
    
    /**
     * Cek apakah status RELEASED (executed)
     */
    public function isSuratExecuted(): bool
    {
        return $this->isSurat() && $this->status === self::STATUS_RELEASED;
    }
    
    /**
     * Cek apakah punya file surat
     */
    public function hasSuratFile(): bool
    {
        return $this->isSurat() && !is_null($this->surat_file_path);
    }
    
    /**
     * Get file size dalam format human readable
     */
    public function getSuratFileSizeHumanAttribute(): string
    {
        if (!$this->surat_file_size) {
            return '-';
        }
        
        $bytes = $this->surat_file_size;
        $units = ['B', 'KB', 'MB', 'GB'];
        
        for ($i = 0; $bytes > 1024; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }

    /**
     * Get who can generate number for this contract
     */
    public function allowedNumberGenerators(): array
    {
        return ['admin', 'legal']; // Roles yang diperbolehkan
    }
    
    public static function getStatuses(){ //nama status
    return [
            self::STATUS_DRAFT => 'Draft',
            self::STATUS_SUBMITTED => 'Submitted',
            self::STATUS_AWAITING_DOCUMENT_UPLOAD => 'Awaiting Document Upload',
            self::STATUS_DOCUMENT_UPLOADED => 'Document Uploaded to Synology',
            self::STATUS_USER_REVIEWING => 'User Reviewing in Synology',
            self::STATUS_USER_REVIEW_COMPLETE => 'User Review Complete',
            self::STATUS_LEGAL_REVIEWING_FEEDBACK => 'Legal Reviewing Feedback',
            self::STATUS_UNDER_REVIEW => 'Under Review',
            self::STATUS_LEGAL_APPROVED => 'Legal Approved',
            self::STATUS_FINANCE_REVIEWING => 'Finance Reviewing',
            self::STATUS_FINANCE_APPROVED => 'Finance Approved',
            self::STATUS_ACCOUNTING_REVIEWING => 'Accounting Reviewing',
            self::STATUS_ACCOUNTING_APPROVED => 'Accounting Approved',
            self::STATUS_TAX_REVIEWING => 'Tax Reviewing',
            self::STATUS_TAX_APPROVED => 'Tax Approved',
            self::STATUS_FINAL_APPROVED => 'Approved',
            self::STATUS_NUMBER_ISSUED => 'Number Issued',
            self::STATUS_RELEASED => 'Completed',
            self::STATUS_REVISION_NEEDED => 'Revision Needed',
            self::STATUS_DECLINED => 'Declined',
            self::STATUS_CANCELLED => 'Cancelled',
        ];
    }

    public static function getReviewFlowStatuses()
    {
        return [
            self::REVIEW_FLOW_PENDING_ASSIGNMENT => 'Pending Assignment',
            self::REVIEW_FLOW_IN_REVIEW => 'In Review',
            self::REVIEW_FLOW_COMPLETED => 'Completed',
            self::REVIEW_FLOW_REVISION_REQUESTED => 'Revision Requested',
            self::REVIEW_FLOW_REJECTED => 'Rejected',
        ];
    }

    public static function getLegalStatuses()
    {
        return [
            self::LEGAL_STATUS_PENDING => 'Pending',
            self::LEGAL_STATUS_ASSIGNED => 'Assigned',
            self::LEGAL_STATUS_UNDER_REVIEW => 'Under Review',
            self::LEGAL_STATUS_COMPLETED => 'Completed',
            self::LEGAL_STATUS_REVISION_REQUESTED => 'Revision Requested',
        ];
    }

    public function getSelectedDepartmentsArray()
    {
        return $this->selected_departments ? json_decode($this->selected_departments, true) : [];
    }

    public function hasDepartmentSelected($departmentCode)
    {
        $selected = $this->getSelectedDepartmentsArray();
        return in_array($departmentCode, $selected);
    }

    public function canAddStage(TblUser $user): bool{
        if (!$this->isInReview()) {
            return false;
        }

        if (!$this->allow_stage_addition) {
            return false;
        }

        return $user->hasAnyRole([
            'legal',
            'admin',
            'admin_fin',
            'admin_acc',
            'admin_tax',
        ]);
    }


    public function getNextSequenceNumber()
    {
        $lastStage = $this->reviewStages()->orderBy('sequence', 'desc')->first();
        return $lastStage ? $lastStage->sequence + 1 : 1;
    }

    public function isUserInvolved(TblUser $user): bool{
        if ($this->isOwnedBy($user)) {
            return true;
        }

        if (in_array((int) $user->id, [
            (int) $this->legal_assigned_id,
            (int) $this->finance_assigned_id,
            (int) $this->accounting_assigned_id,
            (int) $this->tax_assigned_id,
        ], true)) {
            return true;
        }

        if ($this->reviewStages()
            ->where('assigned_user_id', (int) $user->id)
            ->exists()
        ) {
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

    public function getStatusColorAttribute(){
        $colors = [
            'draft' => 'bg-gray-700 text-gray-300',
            'submitted' => 'bg-yellow-500/20 text-yellow-300',
            'awaiting_document_upload' => 'bg-orange-500/20 text-orange-300',
            'document_uploaded' => 'bg-cyan-500/20 text-cyan-300',
            'user_reviewing' => 'bg-orange-500/20 text-orange-300',
            'user_review_complete' => 'bg-teal-500/20 text-teal-300',
            'legal_reviewing_feedback' => 'bg-blue-500/20 text-blue-300',
            'under_review' => 'bg-blue-500/20 text-blue-300',
            'legal_approved' => 'bg-green-500/20 text-green-300',
            'finance_reviewing' => 'bg-purple-500/20 text-purple-300',
            'finance_approved' => 'bg-purple-500/30 text-purple-400',
            'accounting_reviewing' => 'bg-indigo-500/20 text-indigo-300',
            'accounting_approved' => 'bg-indigo-500/30 text-indigo-400',
            'tax_reviewing' => 'bg-pink-500/20 text-pink-300',
            'tax_approved' => 'bg-pink-500/30 text-pink-400',
            'final_approved' => 'bg-green-700 text-green-300',
            'released' => 'bg-green-700 text-green-300',
            'revision_needed' => 'bg-red-500/20 text-red-300',
            'declined' => 'bg-red-700 text-red-300',
            'cancelled' => 'bg-gray-800 text-gray-400',
        ];

    return $colors[$this->status] ?? 'bg-gray-700 text-gray-300';
    }

    public function getReviewProgressAttribute()
    {
        $totalStages = $this->reviewStages()->count();
        $completedStages = $this->reviewStages()->where('status', 'completed')->count();
        
        return $totalStages > 0 ? ($completedStages / $totalStages) * 100 : 0;
    }

    public function getProgressWithLabelAttribute()
    {
        $total = $this->reviewStages()->count();
        $completed = $this->reviewStages()->where('status', 'completed')->count();
        return "{$completed}/{$total} Stages Completed";
    }

    public function getEstimatedReviewTimeAttribute()
    {
        $stageCount = $this->reviewStages->count();
        return $stageCount * 2; // 2 hari per stage
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
            self::STATUS_AWAITING_DOCUMENT_UPLOAD
        ]);
    }

    public function canLegalUploadDocument()
    {
        return $this->status === self::STATUS_AWAITING_DOCUMENT_UPLOAD;
    }

    public function canUserReview()
    {
        return $this->status === self::STATUS_DOCUMENT_UPLOADED;
    }

    public function canFinanceReview()
    {
        return $this->status === self::STATUS_LEGAL_APPROVED;
    }

    public function canAccountingReview()
    {
        return $this->status === self::STATUS_FINANCE_APPROVED;
    }

    public function canTaxReview()
    {
        return $this->status === self::STATUS_ACCOUNTING_APPROVED;
    }

    public function canStartReview(){
    return $this->status === self::STATUS_SUBMITTED 
        && ($this->review_flow_status === self::REVIEW_FLOW_PENDING_ASSIGNMENT 
            || $this->review_flow_status === null);
    }


    public function canAssignReviewers(): bool
    {
        return $this->status === self::STATUS_SUBMITTED &&
               ($this->review_flow_status === null ||
                $this->review_flow_status === self::REVIEW_FLOW_PENDING_ASSIGNMENT);
    }

    public function isInReview(): bool
    {
        return $this->status === self::STATUS_UNDER_REVIEW || 
               $this->status === self::STATUS_REVISION_NEEDED;
    }

    // 🔥 METHOD PENTING UNTUK DYNAMIC WORKFLOW 🔥
    public function isInReviewStageSystem(): bool
    {
        // Kontrak dalam sistem review jika:
        // 1. Ada review stages aktif
        // 2. Status adalah 'under_review' atau 'revision_needed'
        // 3. Atau review_flow_status adalah 'in_review' atau 'revision_requested'
        
        if (!$this->reviewStages()->exists()) {
            return false;
        }
        
        // Status kontrak yang termasuk dalam review system
        $validContractStatuses = [
            self::STATUS_UNDER_REVIEW,
            self::STATUS_REVISION_NEEDED,
            self::STATUS_FINAL_APPROVED,      // ✅ TAMBAHKAN
            self::STATUS_DECLINED,            // ✅ TAMBAHKAN
        ];
        
        // Review flow status yang valid
        $validFlowStatuses = [
            self::REVIEW_FLOW_IN_REVIEW,
            self::REVIEW_FLOW_REVISION_REQUESTED,
            self::REVIEW_FLOW_PENDING_ASSIGNMENT,
            self::REVIEW_FLOW_COMPLETED,
        ];
        
        // Logika: kontrak dalam review system jika:
        // 1. Status kontrak valid, ATAU
        // 2. Review flow status valid
        return (
            in_array($this->status, $validContractStatuses) 
            || in_array($this->review_flow_status, $validFlowStatuses)
        );
    }

    public function isWaitingForRevision(): bool
    {
        return $this->status === self::STATUS_REVISION_NEEDED 
            && $this->review_flow_status === self::REVIEW_FLOW_REVISION_REQUESTED;
    }

    public function getRevisionTargetStage()
    {
        // Cari stage yang bertanggung jawab untuk revisi
        // Prioritas: stage dengan status 'assigned' yang baru direvisi
        return $this->reviewStages()
            ->where('status', 'assigned')
            ->whereNotNull('revision_requested_by')
            ->orderBy('revision_requested_at', 'desc')
            ->first();
    }

    // ====================
    // STAGE NAVIGATION METHODS
    // ====================
    public function activeStage()
    {
        // 1. Cari stage yang 'in_progress'
        $inProgress = $this->reviewStages()
            ->where('status', 'in_progress')
            ->orderBy('sequence')
            ->first();
        
        if ($inProgress) {
            return $inProgress;
        }

        // 2. 🔥 UNTUK SEMUA STATUS 'revision_needed', cari stage 'assigned' (semua role)
        if ($this->status === self::STATUS_REVISION_NEEDED) {
            // Cari stage yang sedang 'assigned' (siapa pun role-nya)
            $assignedStage = $this->reviewStages()
                ->where('status', 'assigned')
                ->orderByRaw("
                    CASE 
                        WHEN stage_type = 'user' THEN 1
                        WHEN stage_type = 'legal' THEN 2
                        WHEN stage_type = 'finance' THEN 3
                        WHEN stage_type = 'accounting' THEN 4
                        WHEN stage_type = 'tax' THEN 5
                        WHEN stage_type = 'admin_fin' THEN 6
                        WHEN stage_type = 'admin_acc' THEN 7
                        WHEN stage_type = 'admin_tax' THEN 8
                        WHEN stage_type = 'admin_legal' THEN 9
                        ELSE 10
                    END
                ")
                ->first();
            
            if ($assignedStage) {
                return $assignedStage;
            }
        }

        // 3. Cari berdasarkan current_stage
        if ($this->current_stage) {
            $current = $this->reviewStages()
                ->where('sequence', $this->current_stage)
                ->first();
            
            if ($current && !in_array($current->status, ['completed', 'rejected'])) {
                return $current;
            }
        }

        // 4. Fallback: stage 'assigned' pertama
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

        // Untuk status lainnya, ambil dari getStatuses() yang sudah ada
        return self::getStatuses()[$this->status] ?? ucfirst(str_replace('_', ' ', $this->status));
    }
    
    public function hasActiveStage(): bool
    {
        return $this->reviewStages()
            ->where('status', 'in_progress')
            ->exists();
    }

    public function canUserAccessStage(TblUser $user, $stage): bool{
        if ((int) $stage->assigned_user_id === (int) $user->id) {
            return true;
        }

        if ($this->isOwnedBy($user)) {
            return true;
        }

        return $user->hasRole('admin');
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
        // Ambil semua review stages untuk contract ini
        $allStages = $this->reviewStages()
            ->with('assignedUser:id_user,nama_user,email')
            ->orderBy('sequence')
            ->get();
        
        // Filter stages yang available untuk jump
        $availableStages = $allStages->filter(function ($stage) use ($currentStage) {
            // ❌ Jangan tampilkan stage yang sama
            if ($stage->id === $currentStage->id) {
                return false;
            }
            
            // ❌ Jangan tampilkan user stage (kecuali untuk revision)
            // User stage hanya muncul di "Request Revision" form
            if ($stage->is_user_stage || $stage->stage_type === 'user') {
                return false;
            }
            
            // ✅ Tampilkan semua stage lainnya
            return true;
        });
        
        // Map ke format yang dibutuhkan
        return $availableStages->map(function ($stage) {
            return [
                'id' => $stage->id,
                'stage_name' => $stage->stage_name,
                'stage_type' => $stage->stage_type,
                'is_user_stage' => $stage->is_user_stage ?? false,
                'assigned_user_id' => $stage->assigned_user_id,
                'assigned_user_name' => $stage->assignedUser->name ?? 'Unassigned',
                'sequence' => $stage->sequence,
                'status' => $stage->status,
                'is_completed' => $stage->status === 'completed',
            ];
        })->values();
    }

    private function getStageDisplayName($stage)
    {
        $typeMap = [
            'legal' => 'Legal Review',
            'finance' => 'Finance Review',
            'accounting' => 'Accounting Review', 
            'tax' => 'Tax Review',
            'admin_fin' => 'Admin Finance',
            'admin_acc' => 'Admin Accounting',
            'admin_tax' => 'Admin Tax',
            'admin_legal' => 'Admin Legal',
            'user' => 'User'
        ];
        
        return $typeMap[$stage->stage_type] ?? ucfirst(str_replace('_', ' ', $stage->stage_name));
    }

    // ====================
    // USER ACTIONS
    // ====================
    public function getAvailableActions(TblUser $user): array{
        $actions = [];

        if ($this->isOwnedBy($user)) {
            if ($this->canBeEdited()) {
                $actions[] = 'edit';
            }
            if ($this->canBeSubmitted()) {
                $actions[] = 'submit';
            }
            if ($this->canBeCancelled()) {
                $actions[] = 'cancel';
            }
            if ($this->canBeRevisedBy($user)) {
                $actions[] = 'revise';
            }
        }

        if ($user->hasAnyRole(['legal', 'admin']) && $this->canStartReview()) {
            $actions[] = 'start_review';
        }

        return $actions;
    }



    public function canBeRevisedBy($user): bool{
    // Validasi ownership (AMAN)
    if ((int) $this->user_id !== (int) $user->id) {
        return false;
    }

    // Status harus revision_needed
    if ($this->status !== self::STATUS_REVISION_NEEDED) {
        return false;
    }

    // Harus ada feedback
    return !empty($this->legal_feedback)
        || !empty($this->finance_feedback)
        || !empty($this->accounting_feedback)
        || !empty($this->tax_feedback);
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
            'status' => self::STATUS_SUBMITTED,
            'submitted_at' => now(),
        ]);
    }

    public function assignToLegal($legalUserId)
    {
        $this->update([
            'legal_assigned_id' => $legalUserId,
            'status' => self::STATUS_UNDER_REVIEW,
            'review_flow_status' => self::REVIEW_FLOW_IN_REVIEW,
            'legal_status' => self::LEGAL_STATUS_ASSIGNED,
            'legal_review_started_at' => now(),
        ]);
    }

    public function legalApprove()
    {
        $this->update([
            'status' => self::STATUS_LEGAL_APPROVED,
            'legal_reviewed_at' => now(),
        ]);
    }

    public function legalRequestRevision($feedback)
    {
        $this->update([
            'status' => self::STATUS_LEGAL_REVIEWING_FEEDBACK,
            'legal_feedback' => $feedback,
        ]);
    }

    // ====================
    // UTILITY METHODS
    // ====================
    

    // ====================
    // DYNAMIC WORKFLOW METHODS
    // ====================

    /**
     * Start dynamic review workflow dengan multiple stages
     */
    public function startDynamicReview($legalStages, $selectedDepartments = [], $notes = null)
    {
        return DB::transaction(function () use ($legalStages, $selectedDepartments, $notes) {
            
            $legalDept = \App\Models\Department::where('code', 'LEGAL')->first();
            
            // 1. Create USER stage (auto)
            \App\Models\ContractReviewStage::create([
                'contract_id' => $this->id,
                'department_id' => $legalDept->id,
                'stage_name' => 'User Submission',
                'stage_type' => 'user',
                'assigned_user_id' => $this->user_id,
                'sequence' => 1,
                'status' => 'completed',
                'notes' => 'Contract submitted by user',
                'assigned_at' => now(),
                'completed_at' => now(),
            ]);
            
            // 2. Create LEGAL stages (dynamic dari form)
            $sequence = 2;
            foreach ($legalStages as $index => $stageData) {
                \App\Models\ContractReviewStage::create([
                    'contract_id' => $this->id,
                    'department_id' => $legalDept->id,
                    'stage_name' => $stageData['name'],
                    'stage_type' => 'legal',
                    'assigned_user_id' => $stageData['user_id'],
                    'sequence' => $sequence,
                    'status' => $sequence === 2 ? 'assigned' : 'pending',
                    'notes' => $index >= 2 ? 'Manually added during review setup' : null,
                    'assigned_at' => $sequence === 2 ? now() : null,
                    'is_manual_added' => $index >= 2,
                    'add_reason' => $index >= 2 ? 'Additional review stage needed' : null,
                ]);
                $sequence++;
            }
            
            // 3. Update contract status
            $this->update([
                'status' => self::STATUS_UNDER_REVIEW,
                'review_flow_status' => self::REVIEW_FLOW_IN_REVIEW,
                'current_stage' => 2, // Stage pertama setelah user
                'legal_assigned_id' => $legalStages[0]['user_id'],
                'legal_review_started_at' => now(),
                'allow_stage_addition' => true,
                'legal_notes' => $notes,
                'workflow_type' => 'dynamic',
                'selected_departments' => !empty($selectedDepartments) ? json_encode($selectedDepartments) : null,
            ]);
            
            return true;
        });
    }

    /**
     * Add stage mid-review untuk dynamic workflow
     */
    public function addStageMidReview($stageName, $userId, $reason, $position = 'end')
    {
        return DB::transaction(function () use ($stageName, $userId, $reason, $position) {
            
            $legalDept = \App\Models\Department::where('code', 'LEGAL')->first();
            
            // Get current stage
            $currentStage = $this->reviewStages()
                ->where('sequence', $this->current_stage)
                ->first();
                
            if (!$currentStage) {
                throw new \Exception('Current stage not found');
            }
            
            $newSequence = null;
            
            // Tentukan sequence berdasarkan position
            switch ($position) {
                case 'before_current':
                    $newSequence = $currentStage->sequence;
                    // Geser semua stage setelahnya
                    $this->reviewStages()
                        ->where('sequence', '>=', $currentStage->sequence)
                        ->increment('sequence');
                    break;
                    
                case 'after_current':
                    $newSequence = $currentStage->sequence + 1;
                    $this->reviewStages()
                        ->where('sequence', '>', $currentStage->sequence)
                        ->increment('sequence');
                    break;
                    
                case 'end':
                    $maxSequence = $this->reviewStages()->max('sequence');
                    $newSequence = $maxSequence + 1;
                    break;
            }
            
            // Create new stage
            $newStage = \App\Models\ContractReviewStage::create([
                'contract_id' => $this->id,
                'department_id' => $legalDept->id,
                'stage_name' => $stageName,
                'stage_type' => 'legal',
                'assigned_user_id' => $userId,
                'sequence' => $newSequence,
                'status' => 'pending',
                'notes' => $reason,
                'is_manual_added' => true,
                'add_reason' => $reason,
                'added_at' => now(),
            ]);
            
            // Update contract jika perlu
            if ($position === 'before_current') {
                $this->update([
                    'current_stage' => $newSequence,
                ]);
            }
            
            return $newStage;
        });
    }

    // FINISH REVIEW
    public function finishReview()
    {
        $lastStage = $this->reviewStages()
            ->orderByDesc('sequence')
            ->first();

        if (!$lastStage || $lastStage->status !== 'completed') {
            throw new \Exception('Cannot finish review before last stage is completed.');
        }

        $this->update([
            'review_flow_status' => self::REVIEW_FLOW_COMPLETED,
            'status' => self::STATUS_FINAL_APPROVED,
            'current_stage' => $lastStage->id,
            'final_approved_at' => now(),
            'final_approved_by' => auth()->id(),
        ]);

        \Log::info('Contract review finished', [
            'contract_id' => $this->id,
            'user_id' => auth()->id(),
        ]);
    }

}
