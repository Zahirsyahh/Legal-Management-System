<?php

namespace App\Http\Controllers;

use App\Models\Contract;
use App\Models\TblUser;
use App\Models\Department;
use App\Models\ContractDepartment;
use App\Models\ContractReviewStage;
use App\Models\ContractReviewJump;
use App\Models\ContractReviewLog;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Events\ReviewWorkflowStarted;
use App\Notifications\StageAssignedNotification;
use App\Notifications\ContractReviewStartedNotification;
use App\Notifications\RevisionRequestedNotification;
use App\Notifications\StageJumpedNotification;
use App\Notifications\ContractRejectedNotification;
use App\Services\ContractNumberService;
use Illuminate\Validation\Rule;


class ReviewStageController extends Controller
{
    // ============================================
    // 1. DYNAMIC REVIEW WORKFLOW INITIATION (NEW)
    // ============================================

    /**
     * Show dynamic form untuk start review dengan tombol "Add Stage"
     */
    public function showStartReviewDynamic(Contract $contract){
        // Authorization
        if (!Auth::user()->hasAnyRole(['legal', 'admin'])) {
            abort(403, 'Only Legal or Admin can start review process.');
        }
        
        if (!$contract->canStartReview()) {
            return redirect()->route('contracts.show', $contract)
                ->with('error', 'Contract cannot start review at this time. Status must be "submitted".');
        }
        
        // Get Legal department
        $legalDept = Department::where('code', 'LEGAL')->first();
        
        // Get active legal officers - PERBAIKAN: pilih field yang benar
        $legalOfficers = TblUser::role('legal')
            ->where('status_karyawan', 'AKTIF')
            ->orderBy('nama_user')
            ->get(['id_user', 'nama_user', 'email']); // ✅ HAPUS 'username', GANTI 'nama_user'
        
        // Get other active departments untuk checklist
        $otherDepartments = Department::where('code', '!=', 'LEGAL')
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
        
        return view('contracts.start-review-dynamic', compact(
            'contract',
            'legalDept',
            'legalOfficers',
            'otherDepartments'
        ));
    }

    /**
     * Process dynamic form dengan multiple stages
     */
        public function processStartReviewDynamic(Request $request, Contract $contract)
        {
            // ✅ LOG RAW REQUEST - TAMBAHKAN DI SINI
            \Log::info('=== RAW REQUEST RECEIVED ===', [
                'contract_id' => $contract->id,
                'user_id' => Auth::id(),
                'method' => $request->method(),
                'url' => $request->fullUrl(),
                'all_input' => $request->all(),
                'has_reviewers' => $request->has('reviewers'),
                'reviewers_count' => count($request->input('reviewers', [])),
                'reviewers_raw' => $request->input('reviewers'),
                'selected_departments' => $request->input('selected_departments'),
                'synology_link' => $request->input('synology_folder_path'),
                'notes' => $request->input('notes'),
            ]);

            // Authorization
            if (!Auth::user()->hasRole(['legal', 'admin'])) {
                abort(403, 'Only Legal or Admin can start review process.');
            }
            
            if (!$contract->canStartReview()) {
                return redirect()->back()
                    ->with('error', 'Contract cannot start review at this time.');
            }
            
            // ✅ LOG BEFORE VALIDATION
            \Log::info('Starting validation...');
            
            // ✅ FIXED: Validation rules
            $validated = $request->validate([
                'reviewers' => 'required|array|min:1',
                'reviewers.*.stage_name' => 'required|string|max:255',
                'reviewers.*.user_id' => [
                    'required',
                    'integer',
                    Rule::exists('tbl_user', 'id_user')
                        ->where('status_karyawan', 'AKTIF')
                ],
                'synology_folder_path' => 'nullable|string|max:500',
                'selected_departments' => 'nullable|array',
                'selected_departments.*' => 'in:FIN,ACC,TAX',
                'notes' => 'nullable|string|max:1000',
            ]);
            
            \Log::info('✅ Validation passed', [
                'validated_data' => $validated,
            ]);
            
            // ✅ FIXED: Check duplicate users
            $legalUserIds = collect($validated['reviewers'])->pluck('user_id');
            if ($legalUserIds->count() !== $legalUserIds->unique()->count()) {
                return redirect()->back()
                    ->with('error', 'One user cannot be assigned to multiple stages!')
                    ->withInput();
            }
            
            try {
                \Log::info('Starting database transaction...');
                DB::beginTransaction();
                
                $legalDept = Department::where('code', 'LEGAL')->first();
                
                // ============================================
                // 1. CREATE USER STAGE (auto)
                // ============================================
                \Log::info('Creating user stage...');
                ContractReviewLog::create([
                    'contract_id' => $contract->id,
                    'stage_id' => null,
                    'user_id' => Auth::id(),
                    'action' => 'workflow_started',
                    'description' => 'Dynamic review workflow initiated',
                    'metadata' => [
                        'legal_stages_count' => count($validated['reviewers']),
                        'departments_count' => count($validated['selected_departments'] ?? []),
                    ]
                ]);
                
                ContractReviewStage::create([
                    'contract_id' => $contract->id,
                    'department_id' => $legalDept->id,
                    'stage_name' => 'User Submission',
                    'stage_type' => 'user',
                    'assigned_user_id' => $contract->user_id,
                    'is_user_stage' => true,
                    'sequence' => 1,
                    'status' => 'completed',
                    'notes' => 'Contract submitted by user',
                    'assigned_at' => now(),
                    'completed_at' => now(),
                ]);
                
                \Log::info('✅ User stage created');
                
                // ============================================
                // 2. CREATE LEGAL STAGES (dynamic dari form)
                // ============================================
                \Log::info('Creating legal stages...', [
                    'stages_count' => count($validated['reviewers']),
                ]);
                
                $sequence = 2;
                $createdStages = [];
                
                foreach ($validated['reviewers'] as $index => $stageData) {
                    $isManual = $index >= 2;
                    
                    $stage = ContractReviewStage::create([
                        'contract_id' => $contract->id,
                        'department_id' => $legalDept->id,
                        'stage_name' => $stageData['stage_name'],
                        'stage_type' => 'legal',
                        'assigned_user_id' => $stageData['user_id'],
                        'sequence' => $sequence,
                        'status' => $sequence === 2 ? 'assigned' : 'pending',
                        'notes' => $isManual ? 'Manually added during review setup' : null,
                        'assigned_at' => $sequence === 2 ? now() : null,
                        'created_by' => Auth::id(),
                        'is_manual_added' => $isManual,
                        'add_reason' => $isManual ? 'Additional review stage needed' : null,
                    ]);
                    
                    $createdStages[] = $stage;
                    $sequence++;
                    
                    \Log::info("✅ Stage {$index} created", [
                        'stage_name' => $stageData['stage_name'],
                        'assigned_to' => $stageData['user_id'],
                    ]);
                }
                
                \Log::info('✅ All legal stages created', [
                    'total_stages' => count($createdStages),
                ]);
                
                // ============================================
                // 3. CREATE ENTRIES FOR OTHER DEPARTMENTS
                // ============================================
                // ✅ DEFINISIKAN VARIABLE $selectedDepartments DI SINI
                $selectedDepartments = $validated['selected_departments'] ?? [];
                
                \Log::info('Processing departments...', [
                    'selected_departments' => $selectedDepartments,
                    'count' => count($selectedDepartments),
                ]);
                
                if (!empty($selectedDepartments)) {
                    foreach ($selectedDepartments as $deptCode) {
                        $department = Department::where('code', $deptCode)->first();
                        
                        if ($department) {
                            // Find admin for this department
                            $adminRole = 'admin_' . strtolower($deptCode);
                            $adminUser = TblUser::role($adminRole)->first();
                            
                            ContractDepartment::create([
                                'contract_id' => $contract->id,
                                'department_id' => $department->id,
                                'status' => 'pending_assignment',
                                'assigned_admin_id' => $adminUser->id_user ?? null,
                                'assigned_at' => $adminUser ? now() : null,
                            ]);
                            
                            \Log::info("✅ Department {$deptCode} assigned", [
                                'department_id' => $department->id,
                                'admin_user' => $adminUser ? $adminUser->email : 'No admin found',
                            ]);
                        } else {
                            \Log::warning("Department {$deptCode} not found in database");
                        }
                    }
                    
                    // Update contract dengan selected departments
                    $contract->update([
                        'selected_departments' => json_encode($selectedDepartments),
                    ]);
                    
                    \Log::info('✅ Contract departments updated', [
                        'selected_departments' => $selectedDepartments,
                    ]);
                } else {
                    \Log::info('No additional departments selected');
                }
                
                // ============================================
                // 4. UPDATE CONTRACT STATUS
                // ============================================
                \Log::info('Updating contract status...');
                
                $contract->update([
                    'status' => Contract::STATUS_UNDER_REVIEW,
                    'review_flow_status' => Contract::REVIEW_FLOW_IN_REVIEW,
                    'current_stage' => 2,
                    'legal_assigned_id' => $validated['reviewers'][0]['user_id'],
                    'legal_review_started_at' => now(),
                    'allow_stage_addition' => true,
                    'legal_notes' => $validated['notes'] ?? null,
                    'workflow_type' => 'dynamic',
                ]);
                
                \Log::info('✅ Contract status updated', [
                    'new_status' => Contract::STATUS_UNDER_REVIEW,
                    'legal_assigned_id' => $validated['reviewers'][0]['user_id'],
                ]);
                
                // ============================================
                // 5. SAVE SYNOLOGY FOLDER PATH
                // ============================================
                if ($request->filled('synology_folder_path')) {
                    $contract->update([
                        'synology_folder_path' => $request->synology_folder_path
                    ]);
                    \Log::info('✅ Synology path saved', [
                        'path' => $request->synology_folder_path,
                    ]);
                }
                
                DB::commit();
                
                \Log::info('✅ Database transaction committed');
                
                // ============================================
                // 6. SEND NOTIFICATIONS
                // ============================================
                \Log::info('Sending notifications...');
                $this->sendStartReviewNotifications($contract, $validated['reviewers'], $selectedDepartments);
                
                // ============================================
                // 7. LOG ACTION
                // ============================================
                ContractReviewLog::create([
                    'contract_id' => $contract->id,
                    'stage_id' => $createdStages[0]->id ?? null,
                    'user_id' => Auth::id(),
                    'action' => 'stage_created',
                    'description' => 'Review workflow started with ' . count($validated['reviewers']) . 
                                ' legal stage(s)' . 
                                (empty($selectedDepartments) ? '' : ' and ' . count($selectedDepartments) . 
                                ' other department(s)'),
                    'metadata' => [
                        'stages' => collect($createdStages)->pluck('stage_name'),
                        'users' => collect($createdStages)->pluck('assigned_user_id'),
                    ]
                ]);
                
                // ✅ LOG SUCCESS - SEKARANG $selectedDepartments SUDAH TERDEFINISI
                \Log::info('🎉 Dynamic review workflow started successfully!', [
                    'contract_id' => $contract->id,
                    'contract_title' => $contract->title,
                    'legal_stages_count' => count($validated['reviewers']),
                    'departments_count' => count($selectedDepartments), // ✅ VARIABLE SUDAH ADA
                    'first_reviewer' => $validated['reviewers'][0]['user_id'],
                    'total_stages' => $sequence - 1,
                    'execution_time' => microtime(true) - LARAVEL_START,
                ]);
                
                return redirect()->route('contracts.show', $contract)
                    ->with('success', 'Review workflow started successfully! ' . 
                        count($validated['reviewers']) . ' legal stage(s) created.' . 
                        (empty($selectedDepartments) ? '' : ' ' . count($selectedDepartments) . 
                        ' other department(s) notified.'));
                        
            } catch (\Exception $e) {
                DB::rollBack();
                \Log::error('❌ Failed to start dynamic review: ' . $e->getMessage());
                \Log::error('Error trace: ', $e->getTrace());
                
                return redirect()->back()
                    ->with('error', 'Failed to start review: ' . $e->getMessage())
                    ->withInput();
            }
        }

    /**
     * Send notifications to assigned reviewers and department admins
     */
    protected function sendStartReviewNotifications($contract, $legalReviewers, $selectedDepartments)
    {
        // ✅ FIXED: Use TblUser::find() for compatibility
        $currentUser = TblUser::find(Auth::id());
        
        if (!$currentUser) {
            \Log::error('Current user not found in tbl_user', ['auth_id' => Auth::id()]);
            return;
        }

        /**
         * 1️⃣ Notify FIRST LEGAL REVIEWER (sequence = 2)
         */
        if (!empty($legalReviewers) && !empty($legalReviewers[0]['user_id'])) {
            $firstReviewer = TblUser::find($legalReviewers[0]['user_id']);

            if ($firstReviewer) {
                $firstStage = $contract->reviewStages()
                    ->where('assigned_user_id', $firstReviewer->id_user)
                    ->where('sequence', 2)
                    ->first();

                if ($firstStage) {
                    try {
                        $firstReviewer->notify(
                            new StageAssignedNotification(
                                $contract,
                                $firstStage,
                                $currentUser
                            )
                        );
                        
                        \Log::info('Stage assignment notification sent', [
                            'reviewer' => $firstReviewer->email,
                            'stage' => $firstStage->stage_name,
                            'contract' => $contract->title
                        ]);
                    } catch (\Exception $e) {
                        \Log::error('Failed to send stage notification: ' . $e->getMessage());
                    }
                }
            }
        }

        /**
         * 2️⃣ Notify ALL LEGAL REVIEWERS dengan ContractReviewStartedNotification
         */
        $reviewTeam = collect($legalReviewers)->map(function ($reviewer) {
            $user = TblUser::find($reviewer['user_id']);
            return $user ? [
                'id' => $user->id_user,
                'name' => $user->nama_user,
                'email' => $user->email,
                'stage' => $reviewer['stage_name']
            ] : null;
        })->filter()->toArray();

        // Notify all legal reviewers
        foreach ($legalReviewers as $reviewerData) {
            $reviewer = TblUser::find($reviewerData['user_id']);
            if ($reviewer) {
                try {
                    $reviewer->notify(new ContractReviewStartedNotification(
                        $contract,
                        $currentUser,
                        $reviewTeam
                    ));
                } catch (\Exception $e) {
                    \Log::error('Failed to send review start notification: ' . $e->getMessage());
                }
            }
        }

        /**
         * 3️⃣ Notify CONTRACT OWNER
         */
        if ($contract->user && $contract->user->id_user !== $currentUser->id_user) {
            try {
                $contract->user->notify(new ContractReviewStartedNotification(
                    $contract,
                    $currentUser,
                    $reviewTeam
                ));
            } catch (\Exception $e) {
                \Log::error('Failed to notify contract owner: ' . $e->getMessage());
            }
        }

        /**
         * 4️⃣ Notify DEPARTMENT ADMINS
         */
        if (!empty($selectedDepartments)) {
            foreach ($selectedDepartments as $deptCode) {
                $department = Department::where('code', $deptCode)->first();
                
                if ($department) {
                    $adminRole = 'admin_' . strtolower($deptCode);
                    $adminUsers = TblUser::role($adminRole)->get();
                    
                    foreach ($adminUsers as $admin) {
                        try {
                            $admin->notify(new \App\Notifications\DepartmentAssignmentNotification(
                                $contract,
                                $department,
                                $currentUser
                            ));
                            
                            \Log::info('Department assignment notification sent', [
                                'department' => $deptCode,
                                'admin' => $admin->email,
                                'contract' => $contract->title,
                            ]);
                        } catch (\Exception $e) {
                            \Log::error('Failed to send department notification: ' . $e->getMessage());
                        }
                    }
                }
            }
        }

        /**
         * 5️⃣ Log semua notifikasi yang dikirim
         */
        \Log::info('Notifications sent for contract review start', [
            'contract_id' => $contract->id,
            'legal_reviewers_count' => count($legalReviewers),
            'departments_count' => count($selectedDepartments),
            'reviewers' => collect($legalReviewers)->pluck('user_id')->toArray(),
            'departments' => $selectedDepartments,
            'sent_by' => $currentUser->email
        ]);
    }

    // ============================================
    // 2. MID-REVIEW STAGE MANAGEMENT (NEW)
    // ============================================

    /**
     * Show form to add stage mid-review
     */
    public function showAddStageForm(Contract $contract)
    {
        if (!Auth::user()->hasAnyRole(['legal', 'admin'])) {
            abort(403);
        }
        
        if (!$contract->allow_stage_addition) {
            abort(403, 'Stage addition not allowed for this contract.');
        }
        
        $legalOfficers = TblUser::role('legal')
            ->where('status_karyawan', 'AKTIF')
            ->orderBy('nama_user')
            ->get(['id_user', 'nama_user', 'email']);
            
        $currentStage = $contract->reviewStages()
            ->where('sequence', $contract->current_stage)
            ->first();
            
        return view('reviews.add-stage-form', compact(
            'contract', 
            'legalOfficers',
            'currentStage'
        ));
    }

    /**
     * Add stage mid-review (untuk Admin/Legal)
     */
    public function addStageMidReview(Request $request, Contract $contract)
    {
        if (!Auth::user()->hasAnyRole(['legal', 'admin'])) {
            abort(403);
        }
        
        $request->validate([
            'stage_name' => 'required|string|max:255',
            'user_id' => 'required|exists:tbl_user,id_user',
            'reason' => 'required|string|max:500',
            'position' => 'required|in:before_current,after_current,end',
        ]);
        
        try {
            DB::beginTransaction();
            
            // Get current stage
            $currentStage = $contract->reviewStages()
                ->where('sequence', $contract->current_stage)
                ->first();
                
            if (!$currentStage) {
                throw new \Exception('Current stage not found');
            }
            
            $legalDept = Department::where('code', 'LEGAL')->first();
            $newSequence = null;
            
            // Tentukan sequence berdasarkan position
            switch ($request->position) {
                case 'before_current':
                    $newSequence = $currentStage->sequence;
                    // Geser semua stage setelahnya
                    $contract->reviewStages()
                        ->where('sequence', '>=', $currentStage->sequence)
                        ->increment('sequence');
                    break;
                    
                case 'after_current':
                    $newSequence = $currentStage->sequence + 1;
                    $contract->reviewStages()
                        ->where('sequence', '>', $currentStage->sequence)
                        ->increment('sequence');
                    break;
                    
                case 'end':
                    $maxSequence = $contract->reviewStages()->max('sequence');
                    $newSequence = $maxSequence + 1;
                    break;
            }
            
            // Create new stage
            $newStage = ContractReviewStage::create([
                'contract_id' => $contract->id,
                'department_id' => $legalDept->id,
                'stage_name' => $request->stage_name,
                'stage_type' => 'legal',
                'assigned_user_id' => $request->user_id,
                'sequence' => $newSequence,
                'status' => 'pending',
                'notes' => $request->reason,
                'created_by' => Auth::id(),
                'is_manual_added' => true,
                'add_reason' => $request->reason,
                'created_at' => now(),
            ]);
            
            // Update contract jika perlu
            if ($request->position === 'before_current') {
                $contract->update([
                    'current_stage' => $newSequence,
                ]);
            }
            
            // Log action
            ContractReviewLog::create([
                'contract_id' => $contract->id,
                'stage_id' => $newStage->id,
                'user_id' => Auth::id(),
                'action' => 'stage_added_mid_review',
                'description' => 'New stage added: ' . $request->stage_name,
                'metadata' => [
                    'position' => $request->position,
                    'reason' => $request->reason,
                    'assigned_to' => $request->user_id,
                ]
            ]);
            
            DB::commit();
            
            // Notify assigned user
            $assignedUser = TblUser::find($request->user_id);
            if ($assignedUser) {
                // $assignedUser->notify(new StageAddedNotification($contract, $newStage, Auth::user()));
            }
            
            return redirect()->route('contracts.show', $contract)
                ->with('success', 'Stage added successfully: ' . $request->stage_name);
                
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Failed to add stage: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Failed to add stage: ' . $e->getMessage());
        }
    }

    /**
     * Remove stage from workflow (AJAX)
     */
    public function removeStage(Request $request, ContractReviewStage $reviewStage)
    {
        $user = TblUser::find(Auth::id());
        $contract = $reviewStage->contract;
        
        // Authorization check
        if ($user->hasRole('user') || 
            !($user->hasAnyRole(['legal', 'accounting', 'tax', 'admin_legal', 'admin']) || 
              $user->hasRole('finance'))) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to remove stages'
            ], 403);
        }
        
        // Validasi: kontrak harus dalam status review
        if (!$contract->isInReviewStageSystem()) {
            return response()->json([
                'success' => false,
                'message' => 'Contract is not in review stage system'
            ], 400);
        }
        
        // Validasi: hanya boleh remove stage yang belum started
        if (in_array($reviewStage->status, ['in_progress', 'completed', 'revision_requested'])) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot remove a stage that has already started'
            ], 400);
        }
        
        // Validasi: hanya untuk manually added stages
        if (!$reviewStage->is_manual_added) {
            return response()->json([
                'success' => false,
                'message' => 'Only manually added stages can be removed'
            ], 400);
        }
        
        // Validasi: jangan hapus user stage
        if ($reviewStage->is_user_stage || $reviewStage->stage_type === 'user') {
            return response()->json([
                'success' => false,
                'message' => 'Cannot remove user stage'
            ], 400);
        }
        
        // Validasi: minimal harus ada 1 reviewer stage tersisa
        $remainingReviewerStages = $contract->reviewStages()
            ->where('id', '!=', $reviewStage->id)
            ->where(function($query) {
                $query->where('is_user_stage', false)
                      ->where('stage_type', '!=', 'user');
            })
            ->count();
            
        if ($remainingReviewerStages < 1) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot remove all reviewer stages. Minimum 1 reviewer required'
            ], 400);
        }
        
        try {
            DB::beginTransaction();
            
            $stageName = $reviewStage->stage_name;
            $stageSequence = $reviewStage->sequence;
            
            // Delete the stage
            $reviewStage->delete();
            
            // Reorder remaining stages
            $remainingStages = $contract->reviewStages()
                ->orderBy('sequence')
                ->get();
                
            $sequence = 1;
            foreach ($remainingStages as $stage) {
                $stage->update(['sequence' => $sequence]);
                $sequence++;
            }
            
            // Update contract current_stage jika stage yang dihapus adalah current stage
            if ($contract->current_stage == $stageSequence) {
                // Cari stage berikutnya
                $nextStage = $contract->reviewStages()
                    ->where('sequence', '>=', $stageSequence)
                    ->orderBy('sequence')
                    ->first();
                    
                if ($nextStage) {
                    $contract->update(['current_stage' => $nextStage->sequence]);
                } else {
                    // Kembali ke stage sebelumnya
                    $prevStage = $contract->reviewStages()
                        ->where('sequence', '<', $stageSequence)
                        ->orderBy('sequence', 'desc')
                        ->first();
                        
                    if ($prevStage) {
                        $contract->update(['current_stage' => $prevStage->sequence]);
                    }
                }
            }
            
            // Log the removal
            ContractReviewLog::create([
                'contract_id' => $contract->id,
                'stage_id' => $reviewStage->id,
                'user_id' => $user->id_user,
                'action' => 'stage_removed',
                'description' => 'Stage removed: ' . $stageName,
                'metadata' => [
                    'stage_name' => $stageName,
                    'stage_type' => $reviewStage->stage_type,
                    'assigned_user_id' => $reviewStage->assigned_user_id,
                    'sequence' => $stageSequence,
                    'removed_by' => $user->nama_user,
                    'removed_by_role' => $user->roles->first()->name ?? 'unknown'
                ]
            ]);
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Stage removed successfully',
                'remaining_count' => $remainingStages->count(),
                'new_current_stage' => $contract->current_stage
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Failed to remove stage: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to remove stage: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Reorder stages (AJAX)
     */
    public function reorderStages(Request $request, Contract $contract)
    {
        $user = TblUser::find(Auth::id());
        
        // Authorization check
        if ($user->hasRole('user') || 
            !($user->hasAnyRole(['legal', 'accounting', 'tax', 'admin_legal', 'admin']) || 
              $user->hasRole('finance'))) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to edit workflow'
            ], 403);
        }
        
        // Validasi: kontrak harus dalam status review
        if (!$contract->isInReviewStageSystem()) {
            return response()->json([
                'success' => false,
                'message' => 'Contract is not in review stage system'
            ], 400);
        }
        
        // Validasi: tidak boleh edit jika sudah ada stage yang started/completed
        $hasStartedStages = $contract->reviewStages()
            ->whereIn('status', ['in_progress', 'completed', 'revision_requested'])
            ->exists();
            
        if ($hasStartedStages) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot reorder workflow after stages have started'
            ], 400);
        }
        
        $request->validate([
            'stage_order' => 'required|array',
            'stage_order.*' => 'exists:contract_review_stages,id',
        ]);
        
        try {
            DB::beginTransaction();
            
            // Simpan order lama untuk log
            $oldOrder = $contract->reviewStages()
                ->orderBy('sequence')
                ->pluck('id')
                ->toArray();
            
            // Update sequence for each stage (exclude user stage yang tetap di sequence 1)
            $userStage = $contract->reviewStages()
                ->where('is_user_stage', true)
                ->first();
            
            // Update sequence mulai dari 2 (karena sequence 1 adalah user stage)
            $sequence = 2;
            foreach ($request->stage_order as $stageId) {
                // Skip user stage jika ada di array
                if ($userStage && $stageId == $userStage->id) continue;
                
                ContractReviewStage::where('id', $stageId)
                    ->update(['sequence' => $sequence]);
                $sequence++;
            }
            
            // Log the change
            ContractReviewLog::create([
                'contract_id' => $contract->id,
                'user_id' => $user->id_user,
                'action' => 'workflow_reordered',
                'description' => 'Workflow stages reordered',
                'metadata' => [
                    'old_order' => $oldOrder,
                    'new_order' => $request->stage_order,
                    'reordered_by' => $user->nama_user,
                    'reordered_by_role' => $user->roles->first()->name ?? 'unknown'
                ]
            ]);
            
            DB::commit();
            
            // Ambil data stages terbaru
            $updatedStages = $contract->reviewStages()
                ->orderBy('sequence')
                ->with('assignedUser:id_user,nama_user')
                ->get();
            
            return response()->json([
                'success' => true,
                'message' => 'Workflow order updated successfully',
                'stages' => $updatedStages,
                'new_order' => $updatedStages->pluck('id')->toArray()
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Failed to reorder stages: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to reorder stages: ' . $e->getMessage()
            ], 500);
        }
    }

    // ============================================
    // 3. REVIEW DASHBOARD & MY REVIEWS (UPDATED)
    // ============================================

    /**
     * Show dashboard for reviewers
     */
    public function myReviews()
    {
        $user = TblUser::find(Auth::id());
        
        // Active stages assigned to user
        $assignedStages = ContractReviewStage::where('assigned_user_id', $user->id_user)
            ->whereIn('status', ['assigned', 'in_progress'])
            ->with(['contract' => function($query) {
                $query->select('id', 'title', 'contract_number', 'status', 'counterparty_name');
            }])
            ->orderBy('sequence')
            ->get();
        
        // Recently completed stages
        $completedStages = ContractReviewStage::where('assigned_user_id', $user->id_user)
            ->where('status', 'completed')
            ->with(['contract' => function($query) {
                $query->select('id', 'title', 'contract_number', 'status');
            }])
            ->orderBy('completed_at', 'desc')
            ->limit(10)
            ->get();
        
        // Department assignments
        $departmentAssignments = ContractDepartment::where('assigned_admin_id', $user->id_user)
            ->whereIn('status', ['pending_assignment', 'in_review'])
            ->with(['contract' => function($query) {
                $query->select('id', 'title', 'contract_number', 'status');
            }, 'department'])
            ->get();
        
        // Stats for dashboard
        $stats = [
            'active_count' => $assignedStages->count(),
            'completed_count' => $completedStages->count(),
            'department_count' => $departmentAssignments->count(),
            'total_assigned' => ContractReviewStage::where('assigned_user_id', $user->id_user)->count(),
        ];
        
        return view('reviews.my-reviews', compact(
            'assignedStages', 
            'completedStages',
            'departmentAssignments',
            'stats'
        ));
    }

    // ============================================
    // 4. STAGE REVIEW INTERFACE (UPDATED FOR DYNAMIC)
    // ============================================

    /**
     * Show stage review interface
     */
    public function show(Contract $contract, ContractReviewStage $stage)
    {
        $user = TblUser::find(Auth::id());
        
        if (!$stage->canBeAccessedBy($user)) {
            abort(403);
        }

        if ($stage->contract_id !== $contract->id) {
            abort(404);
        }

        $stage->markVisited();

        // Get review logs
        $reviewLogs = $contract->reviewLogs()
            ->with([
                'user:id_user,nama_user,email',
                'stage:id,stage_name,stage_type,is_user_stage'
            ])
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($log) use ($contract) {
                if (empty($log->notes) && env('APP_DEBUG')) {
                    $notes = $this->quickGenerateNotes($log);
                    $log->notes = $notes;
                    $log->metadata = $log->metadata ?? [
                        'auto_generated' => true,
                        'notes' => $notes,
                        'action' => $log->action
                    ];
                }
                return $log;
            });

        \Log::info('Stage page accessed', [
            'contract' => $contract->title,
            'stage' => $stage->stage_name,
            'user' => $user->nama_user,
            'logs_count' => $reviewLogs->count(),
            'logs_with_notes' => $reviewLogs->whereNotNull('notes')->count()
        ]);

        // Get available stages for jump
        $availableStages = $contract->getAvailableJumpStages($stage)
            ->filter(fn ($s) => !$s['is_user_stage'])
            ->map(function ($s) {
                $stageModel = ContractReviewStage::with('assignedUser')->find($s['id']);
                return $stageModel ? [
                    'id' => $stageModel->id,
                    'stage_name' => $stageModel->stage_name,
                    'stage_type' => $stageModel->stage_type,
                    'is_user_stage' => $stageModel->is_user_stage,
                    'assigned_user_name' => $stageModel->assignedUser->nama_user ?? 'Unassigned',
                    'sequence' => $stageModel->sequence,
                ] : null;
            })
            ->filter()
            ->values();

        return view('reviews.stage', compact('contract', 'stage', 'availableStages', 'reviewLogs'));
    }

    private function quickGenerateNotes($log): string
    {
        $actions = [
            'workflow_started' => 'Workflow started for contract review',
            'stage_created' => 'New review stage created',
            'stage_started' => 'Review stage started',
            'approve_jump' => 'Stage approved, moving to next',
            'request_revision' => 'Revision requested',
            'revision_requested' => 'Contract revision needed',
            'user_response' => 'User responded to feedback',
            'final_approve' => 'Final approval granted',
            'stage_completed' => 'Stage completed successfully',
        ];
        
        return $actions[$log->action] ?? "Action: {$log->action}";
    }

    /**
     * Start reviewing a stage
     */
    public function startReview(Contract $contract, ContractReviewStage $stage)
    {
        $user = TblUser::find(Auth::id());
        
        // Authorization: only assigned user or admin
        if ($stage->assigned_user_id !== $user->id_user && !$user->hasRole('admin')) {
            abort(403, 'Unauthorized action.');
        }
        
        // Check if stage can be started
        if (!in_array($stage->status, ['assigned', 'revision_requested'])) {
            return redirect()->back()
                ->with('error', 'Stage cannot be started at this time.');
        }
        
        // Update stage status
        $stage->update([
            'status' => 'in_progress',
            'started_at' => now(),
        ]);
        
        // Log action
        ContractReviewLog::create([
            'contract_id' => $contract->id,
            'stage_id' => $stage->id,
            'user_id' => $user->id_user,
            'action' => 'stage_started',
            'description' => 'Started reviewing stage: ' . $stage->stage_name,
        ]);
        
        return redirect()->route('review-stages.show', [$contract, $stage])
            ->with('success', 'Review started successfully.');
    }

    // ============================================
    // 5. STAGE ACTIONS (UPDATED FOR DYNAMIC WORKFLOW)
    // ============================================

    /**
     * Approve and jump to another stage
     */
    public function approveWithJump(Request $request, Contract $contract, ContractReviewStage $stage)
    {
        $user = TblUser::find(Auth::id());
        
        // ✅ CEK DULU: Jika stage terakhir, handle khusus
        if ($stage->isLastStage()) {
            // ✅ FINAL STAGE: Validation berbeda (jump_to_stage_id TIDAK required)
            $request->validate([
                'notes' => 'nullable|string|max:1000',
            ]);

            // Authorization
            if ($stage->assigned_user_id !== $user->id_user && !$user->hasRole('admin')) {
                abort(403, 'Unauthorized action.');
            }
            
            try {
                DB::beginTransaction();
                
                // Complete current stage
                $stage->update([
                    'status' => 'completed',
                    'notes' => $request->notes,
                    'completed_at' => now(),
                ]);

                // ✅ LOG ACTION - TANPA $jumpToStage karena ini final
                ContractReviewLog::create([
                    'contract_id' => $contract->id,
                    'stage_id' => $stage->id,
                    'user_id' => $user->id_user,
                    'action' => 'final_approve',
                    'description' => 'Final stage approved - Contract review completed',
                    'notes' => $request->notes,
                    'metadata' => [
                        'is_final_stage' => true,
                        'stage_name' => $stage->stage_name,
                        'notes' => $request->notes,
                        'approved_by' => $user->nama_user,
                        'approved_by_email' => $user->email,
                    ]
                ]);
                
                // ✅ FINISH CONTRACT REVIEW
                $contract->finishReview();
                
                DB::commit();
                
                \Log::info('Final stage approved and contract finished', [
                    'stage' => $stage->stage_name,
                    'contract_id' => $contract->id,
                    'user_id' => $user->id_user,
                    'status' => $contract->status,
                    'notes_saved' => !empty($request->notes)
                ]);
                
            } catch (\Exception $e) {
                DB::rollBack();
                \Log::error('Failed to approve final stage: ' . $e->getMessage(), [
                    'contract_id' => $contract->id,
                    'stage_id' => $stage->id,
                    'trace' => $e->getTraceAsString()
                ]);
                
                return redirect()->back()
                    ->with('error', 'Failed to finish contract: ' . $e->getMessage());
            }
            
            return redirect()->route('contracts.show', $contract)
                ->with('success', '✅ Contract review completed and approved successfully! ' .
                       'Contract number can now be generated manually.');
        }
        
        // ====================================================================
        // ✅ NON-FINAL STAGE: Normal approve & jump logic
        // ====================================================================
        
        $request->validate([
            'jump_to_stage_id' => 'required|exists:contract_review_stages,id',
            'notes' => 'nullable|string|max:1000',
        ]);
        
        $jumpToStage = ContractReviewStage::findOrFail($request->jump_to_stage_id);
        
        // Authorization
        if ($stage->assigned_user_id !== $user->id_user && !$user->hasRole('admin')) {
            abort(403, 'Unauthorized action.');
        }
        
        // Validation: can't jump to same stage
        if ($stage->id === $jumpToStage->id) {
            return redirect()->back()
                ->with('error', 'Cannot jump to the same stage.');
        }
        
        try {
            DB::beginTransaction();
            
            // Complete current stage
            $stage->update([
                'status' => 'completed',
                'notes' => $request->notes,
                'jump_to_stage_id' => $jumpToStage->id,
                'completed_at' => now(),
            ]);
            
            // Record the jump
            ContractReviewJump::create([
                'contract_id' => $contract->id,
                'from_stage_id' => $stage->id,
                'to_stage_id' => $jumpToStage->id,
                'jumped_by' => $user->id_user,
                'reason' => $request->notes ? "Approved with notes: {$request->notes}" : "Approved",
            ]);
            
            // ✅ LOG APPROVAL ACTION
            ContractReviewLog::create([
                'contract_id' => $contract->id,
                'stage_id' => $stage->id,
                'user_id' => $user->id_user,
                'action' => 'approve_jump',
                'description' => 'Approved and jumped to ' . $jumpToStage->stage_name,
                'notes' => $request->notes,
                'metadata' => [
                    'from_stage_id' => $stage->id,
                    'from_stage_name' => $stage->stage_name,
                    'to_stage_id' => $jumpToStage->id,
                    'to_stage_name' => $jumpToStage->stage_name,
                    'to_reviewer_name' => $jumpToStage->assignedUser->nama_user ?? 'Unassigned',
                    'to_reviewer_email' => $jumpToStage->assignedUser->email ?? null,
                    'notes' => $request->notes,
                    'jump_reason' => $request->notes ? "Approved with notes" : "Approved"
                ]
            ]);
            
            // Activate target stage
            $jumpToStage->update([
                'status' => 'in_progress',
                'assigned_at' => now(),
                'started_at' => now(),
            ]);
            
            // Update contract current stage
            $contract->update([
                'current_stage' => $jumpToStage->sequence,
            ]);
            
            DB::commit();
            
            // Notify next reviewer
            $this->notifyNextReviewer($contract, $stage, $jumpToStage);
            
            \Log::info('Approved and jumped', [
                'from_stage' => $stage->stage_name,
                'to_stage' => $jumpToStage->stage_name,
                'notes_saved' => !empty($request->notes),
                'contract_id' => $contract->id
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Failed to approve and jump: ' . $e->getMessage(), [
                'contract_id' => $contract->id,
                'stage_id' => $stage->id,
                'jump_to_stage_id' => $request->jump_to_stage_id,
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->back()
                ->with('error', 'Failed to process approval. Please try again.');
        }
        
        return redirect()->route('contracts.show', $contract)
            ->with('success', 'Approved and moved to ' . $jumpToStage->stage_name . ' successfully! 👍');
    }

/**
 * Notify next reviewer about assignment
 */
private function notifyNextReviewer($contract, $fromStage, $toStage)
{
    try {
        $actor = auth()->user();

        // =========================
        // 1️⃣ Notify Assigned Reviewer
        // =========================
        if ($toStage && $toStage->assignedUser) {

            $reviewer = $toStage->assignedUser;

            // Hindari kirim ke diri sendiri
            if ((int) $reviewer->id_user !== (int) $actor->id_user) {

                $reviewer->notify(
                    new StageAssignedNotification(
                        $contract,
                        $toStage,
                        $actor
                    )
                );

                \Log::info('Stage assignment notification sent', [
                    'contract_id' => $contract->id,
                    'from_stage' => $fromStage?->stage_name,
                    'to_stage' => $toStage->stage_name,
                    'assigned_to' => $reviewer->email,
                    'actor' => $actor->email,
                ]);
            }
        }

        // =========================
        // 2️⃣ Notify Contract Owner (Stage Jump Info)
        // =========================
        if ($contract->user) {

            $owner = $contract->user;

            if ((int) $owner->id_user !== (int) $actor->id_user) {

                $owner->notify(
                    new StageJumpedNotification(
                        $contract,
                        $fromStage,
                        $toStage,
                        $actor
                    )
                );

                \Log::info('Owner notified about stage jump', [
                    'contract_id' => $contract->id,
                    'owner' => $owner->email,
                    'actor' => $actor->email,
                ]);
            }
        }

    } catch (\Throwable $e) {

        \Log::error('Failed to send notification after jump', [
            'contract_id' => $contract->id ?? null,
            'error' => $e->getMessage(),
        ]);
    }
}

    /**
     * Request revision and jump back to selected stage
     */
    public function requestRevisionJump(Request $request, Contract $contract, ContractReviewStage $stage)
    {
        $request->validate([
            'revision_notes' => 'required|string|min:10|max:2000',
            'jump_to_stage_id' => 'nullable|exists:contract_review_stages,id',
        ]);
        
        // Authorization
        $currentUser = TblUser::find(Auth::id());
        if ($stage->assigned_user_id !== $currentUser->id_user && !$currentUser->hasRole('admin')) {
            abort(403, 'Unauthorized action.');
        }
        
        try {
            DB::beginTransaction();
            
            /**
             * 1️⃣ TENTUKAN TARGET STAGE
             */
            if ($request->filled('jump_to_stage_id')) {
                $jumpToStage = ContractReviewStage::find($request->jump_to_stage_id);
            } else {
                // Default: USER stage
                $jumpToStage = $contract->reviewStages()
                    ->where('is_user_stage', true)
                    ->first();
            }
            
            if (!$jumpToStage) {
                throw new \Exception('Target stage not found.');
            }
            
            if ($jumpToStage->id === $stage->id) {
                throw new \Exception('Cannot jump to the same stage.');
            }
            
            /**
             * 2️⃣ UPDATE CURRENT STAGE (REQUEST REVISION)
             */
            $stage->update([
                'status' => 'revision_requested',
                'notes' => $request->revision_notes,
                'revision_requested_by' => $currentUser->nama_user,
                'jump_to_stage_id' => $jumpToStage->id,
                'completed_at' => now(),
            ]);
            
            /**
             * 3️⃣ RESET TARGET STAGE
             */
            $jumpToStage->update([
                'status' => 'assigned',
                'assigned_at' => now(),
                'revision_requested_by' => $currentUser->nama_user,
                'started_at' => null,
                'completed_at' => null,
                'notes' => null,
            ]);
            
            /**
             * 4️⃣ NONAKTIFKAN SEMUA STAGE LAIN (KECUALI TARGET)
             */
            ContractReviewStage::where('contract_id', $contract->id)
                ->where('id', '!=', $jumpToStage->id)
                ->whereIn('status', ['assigned', 'in_progress'])
                ->update([
                    'status' => 'pending',
                ]);
            
            /**
             * 5️⃣ UPDATE CONTRACT STATUS
             */
            $contract->update([
                'current_stage' => $jumpToStage->sequence,
                'review_flow_status' => $jumpToStage->is_user_stage
                    ? Contract::REVIEW_FLOW_REVISION_REQUESTED
                    : Contract::REVIEW_FLOW_IN_REVIEW,
                'status' => $jumpToStage->is_user_stage
                    ? Contract::STATUS_REVISION_NEEDED
                    : Contract::STATUS_UNDER_REVIEW,
            ]);
            
            /**
             * 6️⃣ LOG ACTION & JUMP RECORD
             */
            $log = ContractReviewLog::create([
                'contract_id' => $contract->id,
                'stage_id' => $stage->id,
                'user_id' => $currentUser->id_user,
                'action' => 'revision_requested',
                'description' => 'Revision requested to ' . $jumpToStage->stage_name,
                'notes' => $request->revision_notes,
                'metadata' => [
                    'from_stage_id' => $stage->id,
                    'from_stage_name' => $stage->stage_name,
                    'to_stage_id' => $jumpToStage->id,
                    'to_stage_name' => $jumpToStage->stage_name,
                    'to_reviewer_name' => $jumpToStage->assignedUser->nama_user ?? 'Unassigned',
                    'revision_notes' => $request->revision_notes,
                    'requested_by' => $currentUser->nama_user,
                    'requested_by_email' => $currentUser->email,
                ]
            ]);
            
            ContractReviewJump::create([
                'contract_id' => $contract->id,
                'from_stage_id' => $stage->id,
                'to_stage_id' => $jumpToStage->id,
                'jumped_by' => $currentUser->id_user,
                'reason' => 'Revision requested: ' . $request->revision_notes,
                'metadata' => [
                    'revision_notes' => $request->revision_notes,
                    'log_id' => $log->id,
                ]
            ]);
            
            DB::commit();
            
            \Log::info('Revision requested successfully', [
                'contract_id' => $contract->id,
                'contract_title' => $contract->title,
                'from_stage' => $stage->stage_name,
                'to_stage' => $jumpToStage->stage_name,
                'to_stage_is_user' => $jumpToStage->is_user_stage,
                'revision_notes_length' => strlen($request->revision_notes),
                'requested_by' => $currentUser->email,
                'target_user' => $jumpToStage->assignedUser->email ?? null,
            ]);
            
            /**
             * 7️⃣ SEND NOTIFICATIONS
             */
            try {
                // Notify target user
                if ($jumpToStage->assignedUser) {
                    $notification = new \App\Notifications\RevisionRequestedNotification(
                        $contract,
                        $stage,
                        $jumpToStage,
                        $request->revision_notes,
                        $currentUser
                    );
                    
                    $jumpToStage->assignedUser->notify($notification);
                    
                    \Log::info('Revision notification sent', [
                        'to_user' => $jumpToStage->assignedUser->email,
                        'notification_type' => 'RevisionRequestedNotification',
                    ]);
                }
                
                // Notify contract owner jika berbeda dengan target
                if ($contract->user && $contract->user->id_user !== $jumpToStage->assigned_user_id) {
                    $contract->user->notify(new \App\Notifications\RevisionRequestedNotification(
                        $contract,
                        $stage,
                        $jumpToStage,
                        $request->revision_notes,
                        $currentUser
                    ));
                }
                
                // Notify all admins
                $admins = TblUser::role('admin')->where('status_karyawan', 'AKTIF')->get();
                foreach ($admins as $admin) {
                    $admin->notify(new \App\Notifications\RevisionRequestedNotification(
                        $contract,
                        $stage,
                        $jumpToStage,
                        $request->revision_notes,
                        $currentUser
                    ));
                }
                
            } catch (\Exception $e) {
                \Log::error('Notification failed but revision was processed', [
                    'error' => $e->getMessage(),
                    'contract_id' => $contract->id,
                ]);
            }
            
            /**
             * 8️⃣ RESPONSE SUCCESS
             */
            $stageType = $jumpToStage->is_user_stage ? 'user' : 'reviewer';
            $message = 'Revision requested. Contract moved to ' . 
                       $jumpToStage->stage_name . ' (' . $stageType . ')';
            
            return redirect()
                ->route('contracts.show', $contract)
                ->with('warning', $message);
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            \Log::error('Failed to request revision: ' . $e->getMessage(), [
                'contract_id' => $contract->id,
                'stage_id' => $stage->id,
                'user_id' => Auth::id(),
                'request_data' => $request->except('_token'),
                'trace' => $e->getTraceAsString()
            ]);
            
            return back()
                ->withInput()
                ->with('error', 'Failed to request revision: ' . $e->getMessage());
        }
    }

    /**
     * User continue review from USER stage back to reviewer
     */
    public function userContinueReview(Request $request, Contract $contract, ContractReviewStage $stage)
    {
        $request->validate([
            'jump_to_stage_id' => 'required|exists:contract_review_stages,id',
            'user_response' => 'required|string|min:10|max:2000',
        ]);
        
        $jumpToStage = ContractReviewStage::findOrFail($request->jump_to_stage_id);
        
        $user = TblUser::find(Auth::id());
        
        // Authorization: only assigned user (contract owner) or admin
        if ($stage->assigned_user_id !== $user->id_user && !$user->hasRole('admin')) {
            abort(403, 'Unauthorized action.');
        }
        
        // Validation: must be user stage
        if (!$stage->is_user_stage) {
            return redirect()->back()
                ->with('error', 'This action can only be performed from USER stage.');
        }
        
        try {
            DB::beginTransaction();
            
            // Complete USER stage
            $stage->update([
                'status' => 'completed',
                'notes' => $request->user_response,
                'jump_to_stage_id' => $jumpToStage->id,
                'completed_at' => now(),
            ]);
            
            // Log user response
            ContractReviewLog::create([
                'contract_id' => $contract->id,
                'stage_id' => $stage->id,
                'user_id' => $user->id_user,
                'action' => 'user_response',
                'description' => 'User submitted revision response',
                'metadata' => ['response' => $request->user_response]
            ]);
            
            // Record the jump
            ContractReviewJump::create([
                'contract_id' => $contract->id,
                'from_stage_id' => $stage->id,
                'to_stage_id' => $jumpToStage->id,
                'jumped_by' => $user->id_user,
                'reason' => "User responded: " . $request->user_response,
            ]);
            
            // Activate target reviewer stage
            $jumpToStage->update([
                'status' => 'in_progress',
                'assigned_at' => now(),
                'started_at' => now(),
            ]);
            
            // Update contract
            $contract->update([
                'current_stage' => $jumpToStage->sequence,
                'review_flow_status' => Contract::REVIEW_FLOW_IN_REVIEW,
                'status' => Contract::STATUS_UNDER_REVIEW,
                'revision_needed' => false,
            ]);
            
            DB::commit();
            
            //Notify the reviewer
            if ($jumpToStage->assignedUser) {
                $jumpToStage->assignedUser->notify(
                    new StageAssignedNotification($contract, $jumpToStage, $user)
                );
            }
            
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Failed to continue review: ' . $e->getMessage());
            
            return redirect()->back()
                ->with('error', 'Failed to continue review. Please try again.');
        }
        
        return redirect()->route('contracts.show', $contract)
            ->with('success', 'Response submitted. Review continued to ' . $jumpToStage->stage_name);
    }

    /**
     * Reject contract entirely
     */
    public function reject(Request $request, Contract $contract, ContractReviewStage $stage)
    {
        $request->validate([
            'rejection_reason' => 'required|string|min:10|max:2000',
        ]);
        
        $user = TblUser::find(Auth::id());
        
        // Authorization: only assigned user or admin
        if ($stage->assigned_user_id !== $user->id_user && !$user->hasRole('admin')) {
            abort(403, 'Unauthorized action.');
        }
        
        try {
            DB::beginTransaction();
            
            // Update stage
            $stage->update([
                'status' => 'rejected',
                'notes' => $request->rejection_reason,
                'completed_at' => now(),
            ]);
            
            // Log rejection
            ContractReviewLog::create([
                'contract_id' => $contract->id,
                'stage_id' => $stage->id,
                'user_id' => $user->id_user,
                'action' => 'reject',
                'description' => 'Contract rejected',
                'metadata' => ['reason' => $request->rejection_reason]
            ]);
            
            // Update contract
            $contract->update([
                'review_flow_status' => Contract::REVIEW_FLOW_REJECTED,
                'status' => 'declined',
            ]);
            
            // Mark all other stages as cancelled
            ContractReviewStage::where('contract_id', $contract->id)
                ->whereIn('status', ['assigned', 'in_progress'])
                ->update([
                    'status' => 'completed',
                    'completed_at' => now(),
                    'notes' => 'Cancelled due to contract rejection'
                ]);
            
            DB::commit();
            
            // Notify contract owner
            // $contract->user->notify(new ContractRejectedNotification($contract, $stage, $request->rejection_reason));
            
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Failed to reject contract: ' . $e->getMessage());
            
            return redirect()->back()
                ->with('error', 'Failed to reject contract. Please try again.');
        }
        
        return redirect()->route('contracts.show', $contract)
            ->with('error', 'Contract has been rejected.');
    }

    // ============================================
    // 6. HELPER METHODS
    // ============================================

    /**
     * Save notes for a stage
     */
    public function saveNotes(Request $request, Contract $contract, ContractReviewStage $stage)
    {
        $request->validate([
            'notes' => 'nullable|string|max:5000',
        ]);
        
        $user = TblUser::find(Auth::id());
        
        // Authorization: only assigned user or admin
        if ($stage->assigned_user_id !== $user->id_user && !$user->hasRole('admin')) {
            abort(403, 'Unauthorized action.');
        }
        
        $stage->update(['notes' => $request->notes]);
        
        // Log notes save
        ContractReviewLog::create([
            'contract_id' => $contract->id,
            'stage_id' => $stage->id,
            'user_id' => $user->id_user,
            'action' => 'notes_saved',
            'description' => 'Notes updated for stage: ' . $stage->stage_name,
        ]);
        
        return redirect()->back()
            ->with('success', 'Notes saved successfully.');
    }

    /**
     * Show user stage interface (special for user stage)
     */
    public function showUserStage(Contract $contract, ContractReviewStage $stage)
    {
        $user = TblUser::find(Auth::id());
        
        // Authorization: only contract owner or admin can access user stage
        if ($stage->assigned_user_id !== $user->id_user && !$user->hasRole('admin')) {
            abort(403, 'You do not have access to this user review stage.');
        }
        
        // Mark stage as visited
        $stage->markVisited();
        
        // Get available stages for jump (exclude user stages)
        $availableStages = $contract->getAvailableJumpStages($stage)
            ->filter(function ($availableStage) {
                return !$availableStage['is_user_stage'];
            })
            ->values();
        
        return view('reviews.stage-user', compact('contract', 'stage', 'availableStages'));
    }

    // ============================================
    // 7. API ENDPOINTS
    // ============================================
    
    /**
     * API: Get available users by role (for AJAX)
     */
    public function getUsersByRole($role)
    {
        if (!Auth::check()) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        
        $users = TblUser::role($role)
            ->where('status_karyawan', 'AKTIF')
            ->select('id_user', 'nama_user', 'email')
            ->orderBy('nama_user')
            ->get()
            ->map(function ($user) {
                return [
                    'id' => $user->id_user,
                    'name' => $user->nama_user,
                    'email' => $user->email,
                ];
            });
        
        return response()->json($users);
    }
    
    /**
     * Generate contract number (manual trigger via button)
     */
    public function generateNumber(Contract $contract, ContractNumberService $service)
    {   
        Log::debug('=== DEBUG GENERATE NUMBER ===', [
            'contract_id' => $contract->id,
            'contract_title' => $contract->title,
            'department_code_raw' => $contract->department_code,
            'department_code_type' => gettype($contract->department_code),
            'department_code_empty' => empty($contract->department_code),
            'department_code_is_null' => is_null($contract->department_code),
            'department_code_string' => (string) $contract->department_code,
            'user_id' => $contract->user_id,
        ]);
        
        // ===============================
        // 1. AUTHORIZATION CHECK
        // ===============================
        $user = TblUser::find(Auth::id());
        
        if (!$user->hasRole(['admin', 'legal'])) {
            abort(403, 'Only admin or legal can generate contract numbers.');
        }

        // ===============================
        // 1b. ENSURE DEPARTMENT CODE EXISTS (EMAIL-BASED RESOLVE)
        // ===============================
        if (empty($contract->department_code)) {
            $resolvedDepartment = $service->resolveDepartmentCode($contract);

            if (!empty($resolvedDepartment) && $resolvedDepartment !== 'GEN') {
                // ✅ SET + SAVE
                $contract->department_code = $resolvedDepartment;
                $contract->save();

                // 🔥 WAJIB: sync ulang object Eloquent
                $contract->refresh();

                Log::info('Department code backfilled before number generation', [
                    'contract_id' => $contract->id,
                    'department_code' => $contract->department_code,
                ]);

            } else {
                return redirect()
                    ->route('contracts.show', $contract)
                    ->with('error', 'Contract missing department code. Please ensure user email exists in HRMS (tbl_user).');
            }

            Log::debug('POST-DEPARTMENT SYNC CHECK', [
                'contract_id' => $contract->id,
                'department_code' => $contract->department_code,
                'is_empty' => empty($contract->department_code),
            ]);
        }

        // ===============================
        // 2. VALIDATION CHECKS
        // ===============================
        
        // Check jika sudah ada nomor
        if ($contract->contract_number) {
            return redirect()
                ->route('contracts.show', $contract)
                ->with('error', 'Contract number already exists: ' . $contract->contract_number);
        }

        // Check status harus FINAL_APPROVED
        if ($contract->status !== Contract::STATUS_FINAL_APPROVED) {
            return redirect()
                ->route('contracts.show', $contract)
                ->with('error', 'Contract must be FINAL APPROVED before generating number. Current status: ' . $contract->status_label);
        }

        // Check jika ada review stages yang belum selesai
        $incompleteStages = $contract->reviewStages()
            ->whereNotIn('status', ['completed', 'skipped'])
            ->count();
            
        if ($incompleteStages > 0) {
            return redirect()
                ->route('contracts.show', $contract)
                ->with('error', "Cannot generate number: {$incompleteStages} review stage(s) still incomplete.");
        }

        // ===============================
        // 3. GENERATE NUMBER
        // ===============================
        try {
            $contractNumber = $service->generateForContract($contract);
            $contract->contract_number = $contractNumber;
            $contract->save();
            
            // Log success
            Log::info('Contract number generated via button', [
                'contract_id' => $contract->id,
                'contract_number' => $contractNumber,
                'user_id' => $user->id_user,
                'user_email' => $user->email,
                'generated_at' => now(),
            ]);

            // ===============================
            // 4. SEND NOTIFICATIONS (optional)
            // ===============================
            // Notify contract owner
            if ($contract->user_id !== $user->id_user) {
                $contract->user->notify(
                    new \App\Notifications\ContractNumberGeneratedNotification(
                        $contract,
                        $contractNumber,
                        $user
                    )
                );
            }

            // Notify all reviewers who approved
            $approvedReviewers = $contract->reviewStages()
                ->where('status', 'completed')
                ->whereNotNull('assigned_user_id')
                ->with('assignedUser')
                ->get()
                ->pluck('assignedUser')
                ->unique('id_user')
                ->filter();

            foreach ($approvedReviewers as $reviewer) {
                if ($reviewer->id_user !== $user->id_user) {
                    $reviewer->notify(
                        new \App\Notifications\ContractNumberGeneratedNotification(
                            $contract,
                            $contractNumber,
                            $user
                        )
                    );
                }
            }

            // ===============================
            // 5. SUCCESS RESPONSE
            // ===============================
            return redirect()
                ->route('contracts.show', $contract);

        } catch (\Exception $e) {
            // ===============================
            // 6. ERROR HANDLING
            // ===============================
            Log::error('Failed to generate contract number', [
                'contract_id' => $contract->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => $user->id_user,
            ]);

            return redirect()
                ->route('contracts.show', $contract)
                ->with('error', 'Failed to generate contract number: ' . $e->getMessage());
        }
    }

    /**
     * 🔥 NEW: Preview contract number (for testing/debugging)
     */
    public function previewNumber(Contract $contract, ContractNumberService $service)
    {
        $user = TblUser::find(Auth::id());
        
        if (!$user->can('view', $contract)) {
            abort(403);
        }

        try {
            $preview = $service->previewNumber($contract);
            
            return response()->json([
                'success' => true,
                'preview_number' => $preview,
                'can_generate' => $service->canGenerate($contract),
                'contract_status' => $contract->status,
                'has_number' => !empty($contract->contract_number),
                'current_number' => $contract->contract_number,
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * API: Update stage notes (AJAX)
     */
    public function updateStageNotes(Request $request, ContractReviewStage $stage)
    {
        if (!Auth::check()) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        
        $user = TblUser::find(Auth::id());
        
        // Authorization
        if ($stage->assigned_user_id !== $user->id_user && !$user->hasRole('admin')) {
            return response()->json(['error' => 'Forbidden'], 403);
        }
        
        $request->validate([
            'notes' => 'nullable|string|max:5000',
        ]);
        
        $stage->update(['notes' => $request->notes]);
        
        return response()->json([
            'success' => true,
            'message' => 'Notes updated successfully',
            'updated_at' => now()->format('Y-m-d H:i:s')
        ]);
    }

    // ============================================
    // 8. ADMIN MANAGEMENT
    // ============================================

    /**
     * Admin: View all review workflows
     */
    public function adminWorkflows(Request $request)
    {
        $user = TblUser::find(Auth::id());
        
        if (!$user->hasRole('admin')) {
            abort(403, 'Unauthorized action.');
        }
        
        $status = $request->get('status', 'all');
        $workflowType = $request->get('workflow_type', 'all');
        
        $query = Contract::whereHas('reviewStages');
        
        if ($status !== 'all') {
            $query->where('review_flow_status', $status);
        }
        
        if ($workflowType !== 'all') {
            $query->where('workflow_type', $workflowType);
        }
        
        $contracts = $query->with(['reviewStages', 'user'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);
        
        $stats = [
            'total' => Contract::whereHas('reviewStages')->count(),
            'in_review' => Contract::where('review_flow_status', Contract::REVIEW_FLOW_IN_REVIEW)->count(),
            'completed' => Contract::where('review_flow_status', Contract::REVIEW_FLOW_COMPLETED)->count(),
            'revision_requested' => Contract::where('review_flow_status', Contract::REVIEW_FLOW_REVISION_REQUESTED)->count(),
            'dynamic_workflows' => Contract::where('workflow_type', 'dynamic')->count(),
        ];
        
        return view('admin.review-workflows', compact('contracts', 'stats', 'status', 'workflowType'));
    }

    /**
     * Admin: View workflow details
     */
    public function adminWorkflowDetail(Contract $contract)
    {
        $user = TblUser::find(Auth::id());
        
        if (!$user->hasRole('admin')) {
            abort(403);
        }
        
        $stages = $contract->reviewStages()
            ->with('assignedUser:id_user,nama_user,email')
            ->orderBy('sequence')
            ->get();
            
        $reviewLogs = $contract->reviewLogs()
            ->with('user:id_user,nama_user')
            ->orderBy('created_at', 'desc')
            ->limit(50)
            ->get();
            
        $departmentReviews = $contract->departmentReviews()
            ->with('department')
            ->get();
        
        return view('admin.workflow-detail', compact(
            'contract', 
            'stages',
            'reviewLogs',
            'departmentReviews'
        ));
    }
}