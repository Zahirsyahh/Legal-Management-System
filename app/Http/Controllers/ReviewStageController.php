<?php

namespace App\Http\Controllers;

use App\Models\Contract;
use App\Models\TblUser;
use App\Models\Department;
use App\Models\ContractDepartment;
use App\Models\ContractReviewStage;
use App\Models\ContractReviewJump;
use App\Models\ContractReviewLog;
use App\Models\ContractRevisionTask;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Notifications\StageAssignedNotification;
use App\Notifications\ContractReviewStartedNotification;
use App\Notifications\StageJumpedNotification;
use App\Services\ContractNumberService;
use Illuminate\Validation\Rule;
use App\Notifications\ContractNumberGeneratedNotification;
use App\Notifications\ContractRejectedNotification;
use App\Notifications\FinishedReviewNotification;

class ReviewStageController extends Controller
{
    // ============================================================
    // 1. DYNAMIC REVIEW WORKFLOW INITIATION
    // ============================================================

    public function showStartReviewDynamic(Contract $contract)
    {
        if (!Auth::user()->hasAnyRole(['legal', 'admin'])) {
            abort(403, 'Only Legal or Admin can start review process.');
        }
        if (!$contract->canStartReview()) {
            return redirect()->route('contracts.show', $contract)
                ->with('error', 'Contract cannot start review at this time. Status must be "submitted".');
        }

        $legalOfficers = TblUser::role('legal')
            ->where('status_karyawan', 'AKTIF')
            ->orderBy('nama_user')
            ->get(['id_user', 'nama_user', 'email']);

        $otherDepartments = Department::where('code', '!=', 'LEGAL')
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('contracts.start-review-dynamic', compact(
            'contract', 'legalOfficers', 'otherDepartments'
        ));
    }

    public function processStartReviewDynamic(Request $request, Contract $contract)
    {
        if (!Auth::user()->hasAnyRole(['legal', 'admin'])) {
            abort(403, 'Only Legal or Admin can start review process.');
        }
        if (!$contract->canStartReview()) {
            return redirect()->back()->with('error', 'Contract cannot start review at this time.');
        }

        if ($request->filled('workflow_items')) {
            return $this->processStartReviewFromJson($request, $contract);
        }
        return $this->processStartReviewLegacy($request, $contract);
    }

    private function processStartReviewFromJson(Request $request, Contract $contract)
    {
        $request->validate([
            'workflow_items'       => 'required|string',
            'synology_folder_path' => 'nullable|string|max:500',
            'notes'                => 'nullable|string|max:1000',
        ]);

        $items = json_decode($request->input('workflow_items'), true);
        if (!$items || !is_array($items) || count($items) === 0) {
            return redirect()->back()->with('error', 'Workflow items tidak valid.')->withInput();
        }

        $legalItems    = array_filter($items, fn($i) => ($i['type'] ?? '') === 'legal');
        $parallelItems = array_filter($items, fn($i) => ($i['type'] ?? '') === 'parallel');

        if (count($legalItems) < 1) {
            return redirect()->back()->with('error', 'Minimal 1 Legal Review Stage diperlukan.')->withInput();
        }
        if (count($parallelItems) > 1) {
            return redirect()->back()->with('error', 'Hanya boleh ada satu blok Substantial Review.')->withInput();
        }

        $parallelData  = !empty($parallelItems) ? array_values($parallelItems)[0] : null;
        $selectedDepts = $parallelData['depts'] ?? [];
        $deptCount     = count($selectedDepts);
        $parallelGroup = $deptCount >= 2 ? 1 : null;

        $stageTypeMap = ['FIN' => 'finance', 'ACC' => 'accounting', 'TAX' => 'tax', 'LEGAL' => 'legal'];

        try {
            DB::beginTransaction();

            $legalDept     = Department::where('code', 'LEGAL')->first();
            $createdStages = [];
            $seqCounter    = 1;

            // ── Stage 1: User Submission (always) ─────────────────────────
            ContractReviewStage::create([
                'contract_id'      => $contract->id,
                'department_id'    => $legalDept?->id,
                'stage_name'       => 'User Submission',
                'stage_type'       => 'user',
                'assigned_user_id' => $contract->user_id,
                'is_user_stage'    => true,
                'sequence'         => $seqCounter++,
                'parallel_group'   => null,
                'status'           => 'completed',
                'notes'            => 'Contract submitted by user',
                'assigned_at'      => now(),
                'completed_at'     => now(),
            ]);

            $firstLegalStageId = null;
            $firstLegalSeq     = null;
            $legalIndex        = 0;

            // ── Process workflow items in order ────────────────────────────
            foreach ($items as $item) {
                $type = $item['type'] ?? '';

                if ($type === 'legal') {
                    $userId    = (int) ($item['user_id'] ?? 0);
                    $stageName = $item['stage_name'] ?? 'Legal Review';
                    $isFirst   = ($legalIndex === 0);

                    $stage = ContractReviewStage::create([
                        'contract_id'      => $contract->id,
                        'department_id'    => $legalDept?->id,
                        'stage_name'       => $stageName,
                        'stage_type'       => 'legal',
                        'assigned_user_id' => $userId,
                        'sequence'         => $seqCounter,
                        'parallel_group'   => null,
                        'status'           => $isFirst ? 'assigned' : 'pending',
                        'assigned_at'      => $isFirst ? now() : null,
                        'created_by'       => Auth::id(),
                        'is_manual_added'  => $legalIndex >= 2,
                    ]);

                    if ($isFirst) {
                        $firstLegalStageId = $stage->id;
                        $firstLegalSeq     = $seqCounter;
                    }

                    $createdStages[] = $stage;
                    $legalIndex++;
                    $seqCounter++;

                } elseif ($type === 'parallel' && !empty($selectedDepts)) {
                    $legalReviewerId = !empty($item['legal_reviewer_id']) ? (int) $item['legal_reviewer_id'] : null;

                    foreach ($selectedDepts as $deptCode) {
                        $department = Department::where('code', $deptCode)->first();
                        if (!$department) continue;

                        $assignedUserId = $deptCode === 'LEGAL' ? $legalReviewerId : null;
                        $stageName      = $deptCode === 'LEGAL' ? 'Legal Review' : $department->name . ' Review';

                        ContractReviewStage::create([
                            'contract_id'      => $contract->id,
                            'department_id'    => $department->id,
                            'stage_name'       => $stageName,
                            'stage_type'       => $stageTypeMap[$deptCode] ?? strtolower($deptCode),
                            'assigned_user_id' => $assignedUserId,
                            'sequence'         => $seqCounter,
                            'parallel_group'   => $parallelGroup,
                            'status'           => 'pending',
                            'notes'            => $deptCode === 'LEGAL'
                                ? 'Legal reviewer included in parallel review.'
                                : 'Awaiting staff assignment from department admin.',
                            'created_by'       => Auth::id(),
                        ]);
                    }

                    foreach ($selectedDepts as $deptCode) {
                        $department = Department::where('code', $deptCode)->first();
                        if (!$department) continue;

                        $adminRoleMap = ['FIN' => 'admin_fin', 'ACC' => 'admin_acc', 'TAX' => 'admin_tax', 'LEGAL' => 'legal'];
                        $adminRole    = $adminRoleMap[$deptCode] ?? null;
                        $adminUser    = $adminRole ? TblUser::role($adminRole)->first() : null;

                        ContractDepartment::updateOrCreate(
                            ['contract_id' => $contract->id, 'department_id' => $department->id],
                            [
                                'status'            => ($deptCode === 'LEGAL' && $legalReviewerId) ? 'assigned' : 'pending_assignment',
                                'assigned_admin_id' => $adminUser?->id_user,
                                'assigned_at'       => $adminUser ? now() : null,
                            ]
                        );
                    }

                    $seqCounter++;
                }
            }

            // ── Update contract ────────────────────────────────────────────
            $contract->update([
                'status'                  => Contract::STATUS_UNDER_REVIEW,
                'review_flow_status'      => Contract::REVIEW_FLOW_IN_REVIEW,
                'current_stage'           => $firstLegalSeq ?? 2,
                'legal_assigned_id'       => $legalUserIds[0] ?? null,
                'legal_review_started_at' => now(),
                'allow_stage_addition'    => true,
                'legal_notes'             => $request->input('notes'),
                'workflow_type'           => 'dynamic',
                'selected_departments'    => !empty($selectedDepts) ? json_encode($selectedDepts) : null,
                'multi_department_status' => match(true) {
                    $deptCount >= 2  => 'multi_department',
                    default          => 'single_department',
                },
            ]);

            if ($request->filled('synology_folder_path')) {
                $contract->update(['synology_folder_path' => $request->synology_folder_path]);
            }

            ContractReviewLog::create([
                'contract_id' => $contract->id,
                'stage_id'    => $firstLegalStageId,
                'user_id'     => Auth::id(),
                'action'      => 'workflow_started',
                'description' => 'Dynamic review workflow dimulai',
                'metadata'    => [
                    'legal_stages_count' => count($legalItems),
                    'departments'        => $selectedDepts,
                    'is_parallel'        => $parallelGroup !== null,
                    'workflow_order'     => array_map(fn($i) => $i['type'], $items),
                ],
            ]);

            DB::commit();

            $this->sendStartReviewNotifications(
                $contract,
                array_values(array_map(fn($s) => [
                    'user_id'    => $s->assigned_user_id,
                    'stage_name' => $s->stage_name,
                ], array_filter($createdStages, fn($s) => $s->stage_type === 'legal'))),
                $selectedDepts
            );

            return redirect()->route('contracts.show', $contract)
                ->with('success', 'Document Review Workflow has Started!');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to start review: ' . $e->getMessage(), [
                'contract_id' => $contract->id, 'trace' => $e->getTraceAsString(),
            ]);
            return redirect()->back()->with('error', 'Gagal memulai review: ' . $e->getMessage())->withInput();
        }
    }

    private function processStartReviewLegacy(Request $request, Contract $contract)
    {
        $validated = $request->validate([
            'reviewers'              => 'required|array|min:1',
            'reviewers.*.stage_name' => 'required|string|max:255',
            'reviewers.*.user_id'    => ['required', 'integer', Rule::exists('tbl_user', 'id_user')->where('status_karyawan', 'AKTIF')],
            'synology_folder_path'   => 'nullable|string|max:500',
            'selected_departments'   => 'nullable|array',
            'selected_departments.*' => 'in:FIN,ACC,TAX,LEGAL',
            'notes'                  => 'nullable|string|max:1000',
        ]);

        $selectedDepartments = $validated['selected_departments'] ?? [];
        $jsonItems = [];
        foreach ($validated['reviewers'] as $r) {
            $jsonItems[] = ['type' => 'legal', 'user_id' => (int) $r['user_id'], 'stage_name' => $r['stage_name']];
        }
        if (!empty($selectedDepartments)) {
            $jsonItems[] = ['type' => 'parallel', 'depts' => $selectedDepartments, 'legal_reviewer_id' => $validated['reviewers'][0]['user_id'] ?? null];
        }

        $request->merge(['workflow_items' => json_encode($jsonItems)]);
        return $this->processStartReviewFromJson($request, $contract);
    }

    protected function sendStartReviewNotifications($contract, $legalReviewers, $selectedDepartments)
    {
        $currentUser = TblUser::find(Auth::id());
        if (!$currentUser) return;
    
        // ── 1. StageAssigned → first reviewer (yang langsung aktif) ──────────
        if (!empty($legalReviewers[0]['user_id'])) {
            $firstReviewer = TblUser::find($legalReviewers[0]['user_id']);
            if ($firstReviewer) {
                $firstStage = $contract->reviewStages()
                    ->where('assigned_user_id', $firstReviewer->id_user)
                    ->where('sequence', 2)
                    ->first();
                if ($firstStage) {
                    try {
                        $firstReviewer->notify(
                            new StageAssignedNotification($contract, $firstStage, $currentUser)
                        );
                    } catch (\Exception $e) {
                        Log::error('StageAssigned notify failed: ' . $e->getMessage());
                    }
                }
            }
        }
    
        // ── 2. ContractReviewStarted → SEMUA reviewer + owner ────────────────
        $reviewTeam = collect($legalReviewers)->map(function ($r) {
            $u = TblUser::find($r['user_id']);
            return $u
                ? ['id' => $u->id_user, 'name' => $u->nama_user,
                'email' => $u->email, 'stage' => $r['stage_name']]
                : null;
        })->filter()->values()->toArray();
    
        // Notify SEMUA reviewer (bukan hanya pertama)
        foreach ($legalReviewers as $r) {
            $reviewer = TblUser::find($r['user_id']);
            if ($reviewer) {
                try {
                    $reviewer->notify(
                        new ContractReviewStartedNotification($contract, $currentUser, $reviewTeam)
                    );
                } catch (\Exception $e) {
                    Log::error('ReviewStarted notify failed: ' . $e->getMessage());
                }
            }
        }
    
        // Notify owner (jika bukan yang memulai review)
        if ($contract->user && (int) $contract->user->id_user !== (int) $currentUser->id_user) {
            try {
                $contract->user->notify(
                    new ContractReviewStartedNotification($contract, $currentUser, $reviewTeam)
                );
            } catch (\Exception $e) {
                Log::error('ReviewStarted owner notify failed: ' . $e->getMessage());
            }
        }
    
        // ── 3. DepartmentAssignment → ADMIN department (bukan staff) ─────────
        $adminRoleMap = [
            'FIN' => 'admin_fin',
            'ACC' => 'admin_acc',
            'TAX' => 'admin_tax',
            // LEGAL sengaja tidak dimasukkan:
            // Legal yang inisiasi review, tidak perlu dapat notif department assignment.
        ];
 
        foreach ($selectedDepartments as $deptCode) {
            // Skip LEGAL — mereka yang memulai proses, bukan penerima assignment
            if ($deptCode === 'LEGAL') continue;
 
            $department = Department::where('code', $deptCode)->first();
            if (!$department) continue;
 
            $adminRole = $adminRoleMap[$deptCode] ?? null;
            if (!$adminRole) continue;
 
            $admins = TblUser::role($adminRole)
                ->where('status_karyawan', 'AKTIF')
                ->get();
 
            foreach ($admins as $admin) {
                try {
                    $admin->notify(
                        new \App\Notifications\DepartmentAssignmentNotification(
                            $contract, $department, $currentUser
                        )
                    );
                } catch (\Exception $e) {
                    Log::error('DeptAssignment notify failed: ' . $e->getMessage());
                }
            }
        }
    }

    // ============================================================
    // 2. GENERATE NUMBER
    //    - Bisa dipanggil dari legal stage mana saja yang in_progress/assigned
    //    - Stage legal TETAP in_progress setelah generate
    //    - Membuat stage executing (pending) + archiving (pending)
    // ============================================================

        public function generateNumber(Contract $contract, ContractNumberService $service)
    {
        $user = TblUser::find(Auth::id());
    
        if (!$user->hasAnyRole(['admin', 'legal'])) {
            if (request()->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
            }
            abort(403, 'Only admin or legal can generate contract numbers.');
        }
    
        if ($contract->contract_number) {
            if (request()->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Nomor kontrak sudah ada: ' . $contract->contract_number,
                ], 422);
            }
            return redirect()->route('contracts.show', $contract)
                ->with('error', 'Nomor kontrak sudah ada: ' . $contract->contract_number);
        }
    
        // Cari stage legal aktif milik user ini
        $myActiveStage = $contract->reviewStages()
            ->where('stage_type', 'legal')
            ->where('assigned_user_id', $user->id_user)
            ->whereIn('status', ['in_progress', 'assigned'])
            ->orderBy('sequence')
            ->first();
    
        if (!$myActiveStage && !$user->hasRole('admin')) {
            if (request()->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Kamu tidak memiliki stage legal aktif di kontrak ini.',
                ], 422);
            }
            return redirect()->route('contracts.show', $contract)
                ->with('error', 'Kamu tidak memiliki stage legal aktif di kontrak ini.');
        }
    
        // Resolve department_code jika belum ada
        if (empty($contract->department_code)) {
            $resolved = $service->resolveDepartmentCode($contract);
            if ($resolved && $resolved !== 'GEN') {
                $contract->update(['department_code' => $resolved]);
                $contract->refresh();
            } else {
                if (request()->expectsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Contract missing department code. Hubungi admin.',
                    ], 422);
                }
                return redirect()->route('contracts.show', $contract)
                    ->with('error', 'Contract missing department code. Hubungi admin.');
            }
        }
    
        // Guard: semua stage lain harus completed
        $incompleteOthers = $contract->reviewStages()
            ->whereNotIn('status', ['completed', 'skipped'])
            ->whereNotIn('stage_type', ['executing', 'archiving'])
            ->when($myActiveStage, fn($q) => $q->where('id', '!=', $myActiveStage->id))
            ->count();
    
        if ($incompleteOthers > 0) {
            $msg = "There are still {$incompleteOthers} other stages that are not completed.";
            if (request()->expectsJson()) {
                return response()->json(['success' => false, 'message' => $msg], 422);
            }
            return redirect()->route('contracts.show', $contract)->with('error', $msg);
        }
    
        // Cek apakah executing/archiving stage sudah pernah dibuat
        $existingExecuting = $contract->reviewStages()
            ->where('stage_type', 'executing')
            ->exists();
    
        try {
            DB::beginTransaction();
    
            $contractNumber = $service->generateForContract($contract);
            $legalDept      = Department::where('code', 'LEGAL')->first();
    
            // Update contract: NUMBER_ISSUED
            $contract->update([
                'contract_number'   => $contractNumber,
                'status'            => Contract::STATUS_NUMBER_ISSUED,
                'number_issued_at'  => now(),
                'final_approved_at' => now(),
                'final_approved_by' => $user->id_user,
            ]);
    
            // Buat executing + archiving stage (hanya jika belum ada)
            if (!$existingExecuting) {
                $maxSeq = $contract->reviewStages()->max('sequence') ?? 0;
    
                $executingStage = ContractReviewStage::create([
                    'contract_id'      => $contract->id,
                    'department_id'    => $legalDept?->id,
                    'stage_name'       => 'Executing — Document Signing',
                    'stage_type'       => 'executing',
                    'assigned_user_id' => $contract->user_id,
                    'sequence'         => $maxSeq + 1,
                    'parallel_group'   => null,
                    'status'           => 'pending',
                    'notes'            => 'Waiting for the active legal stage to be completed.',
                    'created_by'       => $user->id_user,
                ]);
    
                $archivingStage = ContractReviewStage::create([
                    'contract_id'      => $contract->id,
                    'department_id'    => $legalDept?->id,
                    'stage_name'       => 'Archiving — Final Document Storage',
                    'stage_type'       => 'archiving',
                    'assigned_user_id' => $user->id_user,
                    'sequence'         => $maxSeq + 2,
                    'parallel_group'   => null,
                    'status'           => 'pending',
                    'notes'            => 'Waiting for the executing process to be completed.',
                    'created_by'       => $user->id_user,
                ]);
    
                $contract->update([
                    'executing_stage_id' => $executingStage->id,
                    'archiving_stage_id' => $archivingStage->id,
                ]);
            }
    
            ContractReviewLog::create([
                'contract_id' => $contract->id,
                'stage_id'    => $myActiveStage?->id,
                'user_id'     => $user->id_user,
                'action'      => 'number_generated',
                'description' => 'Nomor kontrak digenerate: ' . $contractNumber,
                'notes'       => 'Status kontrak diubah menjadi Number Issued.',
                'metadata'    => [
                    'contract_number'    => $contractNumber,
                    'generated_by'       => $user->nama_user,
                    'legal_stage_status' => 'still_active',
                ],
            ]);
    
            DB::commit();
    
            // Kirim notifikasi (non-blocking)
            try {
                $recipients = collect();
    
                if ($contract->user && (int) $contract->user->id_user !== (int) $user->id_user) {
                    $recipients->push($contract->user);
                }
    
                $legalReviewerIds = $contract->reviewStages()
                    ->where('stage_type', 'legal')
                    ->whereNotNull('assigned_user_id')
                    ->pluck('assigned_user_id')
                    ->unique();
    
                foreach ($legalReviewerIds as $reviewerId) {
                    if ((int) $reviewerId !== (int) $user->id_user) {
                        $reviewer = TblUser::find($reviewerId);
                        if ($reviewer) $recipients->push($reviewer);
                    }
                }
    
                $contract->refresh();
                $executingUser = $contract->executingStage?->assignedUser ?? TblUser::find($contract->user_id);
                $archivingUser = $contract->archivingStage?->assignedUser ?? $user;
    
                foreach ($recipients->unique('id_user') as $recipient) {
                    $recipient->notify(
                        new ContractNumberGeneratedNotification(
                            $contract, $contractNumber, $user, $executingUser, $archivingUser
                        )
                    );
                }
            } catch (\Exception $e) {
                Log::error('ContractNumberGeneratedNotification failed: ' . $e->getMessage());
            }
    
            // ── RETURN: JSON untuk AJAX, redirect untuk form biasa ──────────
            if (request()->expectsJson()) {
                return response()->json([
                    'success'         => true,
                    'contract_number' => $contractNumber,
                    'message'         => 'Nomor kontrak berhasil digenerate: ' . $contractNumber,
                ]);
            }
    
            return redirect()->route('contracts.show', $contract)
                ->with('success',
                    '✅ Nomor kontrak digenerate: <strong>' . $contractNumber . '</strong>. ' .
                    'Approve stage kamu untuk mengaktifkan proses Executing.'
                );
    
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('generateNumber failed: ' . $e->getMessage(), ['contract_id' => $contract->id]);
    
            if (request()->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal generate nomor: ' . $e->getMessage(),
                ], 500);
            }
    
            return redirect()->route('contracts.show', $contract)
                ->with('error', 'Gagal generate nomor: ' . $e->getMessage());
        }
    }

    // ============================================================
    // 3. EXECUTE CONTRACT
    //    Dipanggil oleh contract owner dari executing stage.
    // ============================================================

    public function executeContract(Request $request, Contract $contract, ContractReviewStage $stage)
    {
        $user = TblUser::find(Auth::id());
    
        if ($stage->stage_type !== 'executing') {
            return back()->with('error', 'Aksi ini hanya bisa dilakukan di stage Executing.');
        }
    
        if ((int) $contract->user_id !== (int) $user->id_user && !$user->hasRole('admin')) {
            return back()->with('error', 'Hanya pemilik dokumen yang bisa menandai sebagai Executed.');
        }
    
        if (!in_array($stage->status, ['assigned', 'in_progress'])) {
            return back()->with('error', 'Stage executing sudah tidak bisa diproses (status: ' . $stage->status . ').');
        }
    
        $request->validate([
            'execution_notes' => 'required|string|min:5|max:2000',
            'execution_date'  => 'nullable|date|before_or_equal:today',
        ]);
    
        try {
            DB::beginTransaction();
    
            // Selesaikan executing stage
            $stage->update([
                'status'       => 'completed',
                'completed_at' => now(),
                'notes'        => $request->execution_notes,
            ]);
    
            // Update contract status ke EXECUTED
            $contract->update([
                'status'             => Contract::STATUS_EXECUTED,   // 'executed'
                'review_flow_status' => Contract::REVIEW_FLOW_IN_REVIEW,  // tetap 'in_review' (enum valid)
                'executed_at'        => $request->execution_date ?? now(),
                'executed_by'        => $user->id_user,
            ]);
    
            // Cari dan aktifkan archiving stage
            $archivingStage = null;
    
            // Coba via FK dulu
            if ($contract->archiving_stage_id) {
                $archivingStage = ContractReviewStage::find($contract->archiving_stage_id);
                if ($archivingStage && $archivingStage->status !== 'pending') {
                    $archivingStage = null; // sudah aktif / completed, skip
                }
            }
    
            // Fallback: cari berdasarkan stage_type
            if (!$archivingStage) {
                $archivingStage = $contract->reviewStages()
                    ->where('stage_type', 'archiving')
                    ->where('status', 'pending')
                    ->orderBy('sequence')
                    ->first();
            }
    
            if ($archivingStage) {
                $archivingStage->update([
                    'status'      => 'assigned',    // ← FIX: pending → assigned
                    'assigned_at' => now(),
                    'notes'       => 'Dokumen sudah di-execute. Silakan lakukan archiving.',
                ]);
    
                $contract->update([
                    'current_stage' => $archivingStage->sequence,
                ]);
    
                // Notify legal yang bertanggung jawab archiving
                try {
                    if ($archivingStage->assignedUser) {
                        $archivingStage->assignedUser->notify(
                            new StageAssignedNotification($contract, $archivingStage, $user)
                        );
                    }
                } catch (\Exception $e) {
                    Log::error('Notify archiver failed: ' . $e->getMessage());
                }
            } else {
                Log::warning('Archiving stage not found after execute', [
                    'contract_id'        => $contract->id,
                    'archiving_stage_id' => $contract->archiving_stage_id,
                ]);
            }
    
            ContractReviewLog::create([
                'contract_id' => $contract->id,
                'stage_id'    => $stage->id,
                'user_id'     => $user->id_user,
                'action'      => 'contract_executed',
                'description' => 'Document marked as Executed by ' . $user->nama_user,
                'notes'       => $request->execution_notes,
                'metadata'    => [
                    'executed_by'    => $user->nama_user,
                    'execution_date' => $request->execution_date ?? now()->toDateString(),
                    'archiving_activated' => $archivingStage ? true : false,
                ],
            ]);
    
            DB::commit();

            // ── Notify owner: archiving aktif ─────────────────────────────
            try {
                if ($archivingStage && $contract->user) {
                    $contract->user->notify(
                        new StageJumpedNotification(
                            $contract,
                            $stage,         // dari executing stage
                            $archivingStage,
                            $user,
                            'archiving_activated',
                            'Document signing is complete. The Legal team will now handle final archiving.'
                        )
                    );
                }
            } catch (\Exception $e) {
                Log::error('StageJumped archiving_activated notify failed: ' . $e->getMessage());
            }

            return redirect()->route('contracts.show', $contract)
                ->with('success', '✅ The document has been successfully executed! The legal team will archive it immediately..');
    
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('executeContract failed: ' . $e->getMessage(), ['contract_id' => $contract->id]);
            return back()->with('error', 'Gagal execute dokumen: ' . $e->getMessage());
        }
    }

    // ============================================================
    // 4. ARCHIVE CONTRACT
    //    Dipanggil legal dari archiving stage. Tahap TERAKHIR.
    // ============================================================

    public function archiveContract(Request $request, Contract $contract, ContractReviewStage $stage)
    {
        $user = TblUser::find(Auth::id());
    
        if (!$user->hasAnyRole(['legal', 'admin'])) {
            abort(403, 'Hanya Legal atau Admin yang bisa melakukan archiving.');
        }
    
        if ($stage->stage_type !== 'archiving') {
            return back()->with('error', 'Aksi ini hanya bisa dilakukan di stage Archiving.');
        }
    
        if ((int) $stage->assigned_user_id !== (int) $user->id_user && !$user->hasRole('admin')) {
            abort(403, 'Kamu tidak di-assign untuk stage archiving ini.');
        }
    
        if (!in_array($stage->status, ['assigned', 'in_progress'])) {
            return back()->with('error', 'Stage ini sudah tidak bisa diproses (status: ' . $stage->status . ').');
        }
    
        $request->validate([
            'archive_notes' => 'required|string|min:5|max:2000',
        ]);
    
        try {
            DB::beginTransaction();
    
            // Selesaikan archiving stage
            $stage->update([
                'status'       => 'completed',
                'completed_at' => now(),
                'notes'        => $request->archive_notes,
            ]);
    
            // Update contract ke ARCHIVED — status final
            $contract->update([
                'status'             => Contract::STATUS_ARCHIVED,      // 'archived'
                'review_flow_status' => Contract::REVIEW_FLOW_COMPLETED, // 'completed' (enum valid)
                'archived_at'        => now(),
                'archived_by'        => $user->id_user,
            ]);
    
            ContractReviewLog::create([
                'contract_id' => $contract->id,
                'stage_id'    => $stage->id,
                'user_id'     => $user->id_user,
                'action'      => 'contract_archived',
                'description' => 'Document archived by ' . $user->nama_user . '. All workflow completed.',
                'notes'       => $request->archive_notes,
                'metadata'    => ['archived_by' => $user->nama_user],
            ]);
    
            DB::commit();
            // ── Notify contract owner ─────────────────────────────────────
            try {
                if ($contract->user) {
                    $contract->user->notify(
                        new FinishedReviewNotification($contract, $user, 'owner')
                    );
                }
            } catch (\Exception $e) {
                Log::error('FinishedReviewNotification (owner) failed: ' . $e->getMessage());
            }
 
            // ── Notify semua reviewer yang pernah terlibat ───────────────
            try {
                $involvedUserIds = $contract->reviewStages()
                    ->whereNotNull('assigned_user_id')
                    ->whereNotIn('stage_type', ['user', 'executing'])
                    ->pluck('assigned_user_id')
                    ->unique();
 
                foreach ($involvedUserIds as $reviewerId) {
                    // Skip owner (sudah dapat notif di atas) dan archiver itu sendiri
                    if ((int) $reviewerId === (int) $contract->user_id) continue;
                    if ((int) $reviewerId === (int) $user->id_user) continue;
 
                    $reviewer = TblUser::find($reviewerId);
                    if ($reviewer) {
                        $reviewer->notify(
                            new FinishedReviewNotification($contract, $user, 'reviewer')
                        );
                    }
                }
            } catch (\Exception $e) {
                Log::error('FinishedReviewNotification (reviewers) failed: ' . $e->getMessage());
            }
            
            return redirect()->route('contracts.show', $contract)
                ->with('success', '🎉 Document successfully archived! All review processes have been completed.');
    
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('archiveContract failed: ' . $e->getMessage(), ['contract_id' => $contract->id]);
            return back()->with('error', 'failed to archive document: ' . $e->getMessage());
        }
    }

    // ============================================================
    // 5. MID-REVIEW STAGE MANAGEMENT
    // ============================================================

    public function showAddStageForm(Contract $contract)
    {
        if (!Auth::user()->hasAnyRole(['legal', 'admin'])) abort(403);
        if (!$contract->allow_stage_addition) abort(403, 'Stage addition not allowed for this contract.');

        $legalOfficers = TblUser::role('legal')->where('status_karyawan', 'AKTIF')->orderBy('nama_user')->get(['id_user', 'nama_user', 'email']);
        $currentStage  = $contract->reviewStages()->where('sequence', $contract->current_stage)->first();

        return view('reviews.add-stage-form', compact('contract', 'legalOfficers', 'currentStage'));
    }

    public function addStageMidReview(Request $request, Contract $contract)
    {
        if (!Auth::user()->hasAnyRole(['legal', 'admin'])) abort(403);

        $request->validate([
            'stage_name' => 'required|string|max:255',
            'user_id'    => 'required|exists:tbl_user,id_user',
            'reason'     => 'required|string|max:500',
            'position'   => 'required|in:before_current,after_current,end',
        ]);

        try {
            DB::beginTransaction();

            $currentStage = $contract->reviewStages()->where('sequence', $contract->current_stage)->first();
            if (!$currentStage) throw new \Exception('Current stage not found');

            $legalDept   = Department::where('code', 'LEGAL')->first();
            $newSequence = null;

            switch ($request->position) {
                case 'before_current':
                    $newSequence = $currentStage->sequence;
                    // Geser semua stage (kecuali executing & archiving) ke atas
                    $contract->reviewStages()
                        ->where('sequence', '>=', $currentStage->sequence)
                        ->whereNotIn('stage_type', ['executing', 'archiving'])
                        ->increment('sequence');
                    break;
                case 'after_current':
                    $newSequence = $currentStage->sequence + 1;
                    $contract->reviewStages()
                        ->where('sequence', '>', $currentStage->sequence)
                        ->whereNotIn('stage_type', ['executing', 'archiving'])
                        ->increment('sequence');
                    break;
                case 'end':
                    // Insert before executing/archiving if they exist
                    $executingStage = $contract->reviewStages()
                        ->whereIn('stage_type', ['executing', 'archiving'])
                        ->orderBy('sequence')
                        ->first();
                    if ($executingStage) {
                        $newSequence = $executingStage->sequence;
                        $contract->reviewStages()
                            ->where('sequence', '>=', $executingStage->sequence)
                            ->increment('sequence');
                    } else {
                        $newSequence = $contract->reviewStages()->max('sequence') + 1;
                    }
                    break;
            }

            $newStage = ContractReviewStage::create([
                'contract_id'      => $contract->id,
                'department_id'    => $legalDept?->id,
                'stage_name'       => $request->stage_name,
                'stage_type'       => 'legal',
                'assigned_user_id' => $request->user_id,
                'sequence'         => $newSequence,
                'status'           => 'pending',
                'notes'            => $request->reason,
                'created_by'       => Auth::id(),
                'is_manual_added'  => true,
                'add_reason'       => $request->reason,
            ]);

            if ($request->position === 'before_current') {
                $contract->update(['current_stage' => $newSequence]);
            }

            ContractReviewLog::create([
                'contract_id' => $contract->id, 'stage_id' => $newStage->id, 'user_id' => Auth::id(),
                'action' => 'stage_added_mid_review', 'description' => 'New stage added: ' . $request->stage_name,
                'metadata' => ['position' => $request->position, 'reason' => $request->reason, 'assigned_to' => $request->user_id],
            ]);

            DB::commit();
            return redirect()->route('contracts.show', $contract)->with('success', 'Stage added: ' . $request->stage_name);

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Failed to add stage: ' . $e->getMessage());
        }
    }

    public function removeStage(Request $request, ContractReviewStage $reviewStage)
    {
        $user     = TblUser::find(Auth::id());
        $contract = $reviewStage->contract;

        if (!$user->hasAnyRole(['legal', 'accounting', 'tax', 'admin', 'finance'])) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        // Tidak boleh hapus stage yang sudah berjalan atau sudah selesai
        $lockedStatuses = ['assigned', 'in_progress', 'completed', 'revision_requested'];
        if (in_array($reviewStage->status, $lockedStatuses)) {
            return response()->json(['success' => false, 'message' => 'Cannot remove a stage that has already started or been assigned'], 400);
        }

        if (!$reviewStage->is_manual_added) {
            return response()->json(['success' => false, 'message' => 'Only manually added stages can be removed'], 400);
        }

        if ($reviewStage->is_user_stage || $reviewStage->stage_type === 'user') {
            return response()->json(['success' => false, 'message' => 'Cannot remove user stage'], 400);
        }

        if (in_array($reviewStage->stage_type, ['executing', 'archiving'])) {
            return response()->json(['success' => false, 'message' => 'Cannot remove executing or archiving stages'], 400);
        }

        try {
            DB::beginTransaction();
            $stageName     = $reviewStage->stage_name;
            $stageSequence = $reviewStage->sequence;
            $reviewStage->delete();

            // Re-sequence remaining stages
            $remainingStages = $contract->reviewStages()->orderBy('sequence')->get();
            $sequence = 1;
            foreach ($remainingStages as $s) {
                $s->update(['sequence' => $sequence++]);
            }

            if ($contract->current_stage == $stageSequence) {
                $next = $contract->reviewStages()->where('sequence', '>=', $stageSequence)->orderBy('sequence')->first();
                if ($next) $contract->update(['current_stage' => $next->sequence]);
            }

            ContractReviewLog::create([
                'contract_id' => $contract->id, 'stage_id' => $reviewStage->id, 'user_id' => $user->id_user,
                'action' => 'stage_removed', 'description' => 'Stage removed: ' . $stageName,
                'metadata' => ['stage_name' => $stageName, 'removed_by' => $user->nama_user],
            ]);

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Stage removed successfully']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Failed: ' . $e->getMessage()], 500);
        }
    }

    // ============================================================
    // 6. MY REVIEWS
    // ============================================================

    public function myReviews()
    {
        $user = Auth::user();

        $departmentCode = null;
        if ($user->hasRole('staff_acc'))     $departmentCode = 'ACC';
        elseif ($user->hasRole('staff_fin')) $departmentCode = 'FIN';
        elseif ($user->hasRole('staff_tax')) $departmentCode = 'TAX';
        elseif ($user->hasRole('legal'))     $departmentCode = 'LEG';

        $emptyView = fn() => view('reviews.my-reviews', [
            'assignedStages' => collect([]), 'underReviewStages' => collect([]), 'completedStages' => collect([]),
            'totalAssignedCount' => 0, 'activeReviewCount' => 0, 'pendingReviewCount' => 0, 'completedReviewCount' => 0,
        ]);

        if (!$departmentCode) return $emptyView();

        $department = Department::where('code', $departmentCode)->first();
        if (!$department) return $emptyView();

        try {
            $assignedStages = ContractReviewStage::query()
                ->whereHas('contract', fn($q) => $q->whereNull('deleted_at'))
                ->with(['contract.user', 'department', 'assignedUser'])
                ->where('assigned_user_id', $user->id_user)
                ->where('department_id', $department->id)
                ->orderBy('created_at', 'desc')
                ->get();

            $underReviewStages = $assignedStages->filter(fn($s) =>
                $s->contract && $s->contract->status === 'under_review'
                && in_array($s->status, ['pending', 'assigned', 'in_progress', 'revision_requested'])
            );

            $receivedRevisionTasks = ContractRevisionTask::with(['contract', 'fromStage.assignedUser', 'requester'])
                ->where('assigned_to', $user->id_user)
                ->whereIn('status', ['pending', 'in_progress', 're_requested'])
                ->get();

            return view('reviews.my-reviews', [
                'assignedStages'        => $assignedStages,
                'underReviewStages'     => $underReviewStages,
                'completedStages'       => $assignedStages->where('status', 'completed'),
                'totalAssignedCount'    => $assignedStages->count(),
                'activeReviewCount'     => $underReviewStages->count(),
                'pendingReviewCount'    => $assignedStages->filter(fn($s) => in_array($s->status, ['pending', 'assigned']))->count(),
                'completedReviewCount'  => $assignedStages->where('status', 'completed')->count(),
                'receivedRevisionTasks' => $receivedRevisionTasks,
                'department'            => $department,
                'departmentName'        => $department->name,
                'departmentCode'        => $department->code,
                'unreadNotifications'   => $user->unreadNotifications()->count(),
            ]);

        } catch (\Exception $e) {
            Log::error('Error in myReviews: ' . $e->getMessage());
            return $emptyView();
        }
    }

    // ============================================================
    // 7. STAGE REVIEW INTERFACE
    // ============================================================

    public function show(Contract $contract, ContractReviewStage $stage)
    {
        $user = TblUser::find(Auth::id());

        if (!$stage->canBeAccessedBy($user)) abort(403);
        if ($stage->contract_id !== $contract->id) abort(404);

        $stage->markVisited();

        $reviewLogs = $contract->reviewLogs()
            ->with(['user:id_user,nama_user,email', 'stage:id,stage_name,stage_type,is_user_stage'])
            ->orderBy('created_at', 'desc')
            ->get();

        $availableStages = $contract->getAvailableJumpStages($stage)
            ->filter(fn($s) => !$s['is_user_stage'])
            ->map(function ($s) {
                $stageModel = ContractReviewStage::with('assignedUser')->find($s['id']);
                return $stageModel ? [
                    'id'                 => $stageModel->id,
                    'stage_name'         => $stageModel->stage_name,
                    'stage_type'         => $stageModel->stage_type,
                    'is_user_stage'      => $stageModel->is_user_stage,
                    'assigned_user_name' => $stageModel->assignedUser->nama_user ?? 'Unassigned',
                    'sequence'           => $stageModel->sequence,
                ] : null;
            })
            ->filter()
            ->values();

        $sentRevisionTasks = ContractRevisionTask::with(['toStage.assignedUser', 'assignee'])
            ->where('from_stage_id', $stage->id)
            ->whereIn('status', ['pending', 'in_progress', 'submitted', 're_requested', 'approved'])
            ->orderBy('created_at', 'desc')
            ->get();

        $receivedRevisionTasks = ContractRevisionTask::with(['fromStage.assignedUser', 'requester'])
            ->where('to_stage_id', $stage->id)
            ->whereIn('status', ['pending', 'in_progress', 're_requested'])
            ->orderBy('created_at', 'desc')
            ->get();

        $revisionTargetStages = $contract->reviewStages()
            ->with('assignedUser:id_user,nama_user')
            ->where('id', '!=', $stage->id)
            ->whereNotNull('assigned_user_id')
            ->orderBy('sequence')
            ->get();

            $jumpOptions = collect();

            $seenParallelGroups = [];
            foreach ($contract->reviewStages->sortBy('sequence') as $s) {
                // Skip diri sendiri, user stage, executing/archiving
                if ($s->id === $stage->id) continue;
                if ($s->is_user_stage || $s->stage_type === 'user') continue;
                if (in_array($s->stage_type, ['executing', 'archiving'])) continue;
                // Hanya stage yang lebih jauh ke depan
                if ($s->sequence <= $stage->sequence) continue;

                if (!is_null($s->parallel_group)) {
                    // Parallel group — tampilkan sekali saja sebagai satu opsi
                    if (in_array($s->parallel_group, $seenParallelGroups)) continue;
                    $seenParallelGroups[] = $s->parallel_group;

                    $groupStages = $contract->reviewStages
                        ->where('parallel_group', $s->parallel_group)
                        ->whereNotNull('assigned_user_id');

                    $label = 'Substantial Review (' 
                        . $groupStages->pluck('stage_name')->unique()->join(', ') . ')';

                    $jumpOptions->push([
                        'id'          => $s->id,
                        'label'       => $label,
                        'is_parallel' => true,
                    ]);
                } else {
                    // Sequential stage biasa
                    $assigneeName = $s->assignedUser->nama_user ?? 'Unassigned';
                    $jumpOptions->push([
                        'id'          => $s->id,
                        'label'       => 'Stage ' . $s->sequence . ' — ' . $assigneeName . ' — ' . $s->stage_name,
                        'is_parallel' => false,
                    ]);
                }
            }

            return view('reviews.stage', compact(
                'contract', 'stage', 'availableStages', 'reviewLogs',
                'sentRevisionTasks', 'receivedRevisionTasks', 'revisionTargetStages',
                'jumpOptions'
            ));
    }

    // ============================================================
    // 8. START REVIEW
    // ============================================================

    public function startReview(Contract $contract, ContractReviewStage $stage)
    {
        $user = TblUser::find(Auth::id());

        if ($stage->assigned_user_id !== $user->id_user && !$user->hasRole('admin')) abort(403);
        if (!in_array($stage->status, ['assigned', 'revision_requested'])) {
            return redirect()->back()->with('error', 'Stage cannot be started at this time.');
        }

        $stage->update(['status' => 'in_progress', 'started_at' => now()]);

        if ($contract->status === Contract::STATUS_REVISION_NEEDED && !$stage->is_user_stage) {
            $contract->update(['status' => Contract::STATUS_UNDER_REVIEW, 'review_flow_status' => Contract::REVIEW_FLOW_IN_REVIEW]);
        }

        ContractReviewLog::create([
            'contract_id' => $contract->id, 'stage_id' => $stage->id, 'user_id' => $user->id_user,
            'action' => 'stage_started', 'description' => 'Started reviewing stage: ' . $stage->stage_name,
        ]);

        return redirect()->route('review-stages.show', [$contract, $stage])->with('success', 'Review started successfully.');
    }

    // ============================================================
    // 9. START EXECUTING STAGE
    //    Dipanggil ketika owner mulai proses executing.
    // ============================================================

    public function startExecuting(Contract $contract, ContractReviewStage $stage)
    {
        $user = TblUser::find(Auth::id());

        if ($stage->stage_type !== 'executing') {
            return redirect()->back()->with('error', 'This is not an executing stage.');
        }
        if ($stage->assigned_user_id !== $user->id_user && !$user->hasRole('admin')) {
            abort(403);
        }
        if ($stage->status !== 'assigned') {
            return redirect()->back()->with('error', 'Executing stage belum bisa dimulai.');
        }

        $stage->update(['status' => 'in_progress', 'started_at' => now()]);

        ContractReviewLog::create([
            'contract_id' => $contract->id, 'stage_id' => $stage->id, 'user_id' => $user->id_user,
            'action' => 'executing_started', 'description' => 'Proses executing dimulai oleh pemilik dokumen.',
        ]);

        return redirect()->route('review-stages.show', [$contract, $stage])
            ->with('success', 'Proses executing dimulai.');
    }

    // ============================================================
    // 10. START ARCHIVING STAGE
    //     Dipanggil ketika legal mulai proses archiving.
    // ============================================================

    public function startArchiving(Contract $contract, ContractReviewStage $stage)
    {
        $user = TblUser::find(Auth::id());

        if ($stage->stage_type !== 'archiving') {
            return redirect()->back()->with('error', 'This is not an archiving stage.');
        }
        if (!$user->hasAnyRole(['legal', 'admin'])) {
            abort(403);
        }
        if ($stage->status !== 'assigned') {
            return redirect()->back()->with('error', 'Archiving stage belum bisa dimulai.');
        }

        $stage->update(['status' => 'in_progress', 'started_at' => now()]);

        ContractReviewLog::create([
            'contract_id' => $contract->id, 'stage_id' => $stage->id, 'user_id' => $user->id_user,
            'action' => 'archiving_started', 'description' => 'Proses archiving dimulai.',
        ]);

        return redirect()->route('review-stages.show', [$contract, $stage])
            ->with('success', 'Proses archiving dimulai.');
    }

    // ============================================================
    // 11. APPROVE WITH JUMP
    //     Perubahan utama: last stage TIDAK lagi panggil finishReview().
    //     Jika nomor sudah ada → aktifkan executing stage.
    // ============================================================

    public function approveWithJump(Request $request, Contract $contract, ContractReviewStage $stage)
    {
        $user = TblUser::find(Auth::id());

        if ($stage->assigned_user_id !== $user->id_user && !$user->hasRole('admin')) abort(403);

        // ── Guard: revision tasks terbuka ───────────────────────────────
        if ($stage->has_open_revision && !$request->boolean('force_approve') && !$user->hasRole('admin')) {
            $openCount      = $stage->openRevisionCount();
            $submittedCount = $stage->pendingDecisionCount();

            if ($openCount > 0) {
                return back()->with('error',
                    "Tidak bisa approve stage: masih ada {$openCount} penerima revisi yang belum mengumpulkan hasil."
                );
            }
            if ($submittedCount > 0) {
                return back()->with('warning',
                    "Ada {$submittedCount} hasil revisi yang sudah dikumpulkan dan belum kamu putuskan."
                );
            }
        }

        // ── Auto-cancel revision tasks terbuka ──────────────────────────
        if ($stage->has_open_revision) {
            ContractRevisionTask::where('from_stage_id', $stage->id)
                ->whereIn('status', ['pending', 'in_progress', 're_requested', 'submitted'])
                ->update(['status' => 'cancelled', 'cancelled_at' => now()]);
            $stage->update(['has_open_revision' => false]);
        }

        // ── Parallel stage ───────────────────────────────────────────────
        if (!is_null($stage->parallel_group)) {
            return $this->approveParallelStage($request, $contract, $stage, $user);
        }

        // ── Cek apakah ada parallel stages berikutnya yang perlu diaktifkan
        $nextParallelStages = ContractReviewStage::where('contract_id', $contract->id)
            ->whereNotNull('parallel_group')
            ->whereIn('status', ['pending', 'assigned'])
            ->orderBy('sequence')
            ->get();

        if ($nextParallelStages->isNotEmpty()) {
            return $this->activateParallelStages($request, $contract, $stage, $nextParallelStages, $user);
        }

        // ── Sequential dept stage (non-legal) ───────────────────────────
        $nextSequentialDeptStage = ContractReviewStage::where('contract_id', $contract->id)
            ->whereNull('parallel_group')
            ->where('sequence', '>', $stage->sequence)
            ->whereIn('stage_type', ['finance', 'accounting', 'tax'])
            ->whereNotIn('status', ['completed', 'rejected'])
            ->orderBy('sequence')
            ->first();

        if ($nextSequentialDeptStage) {
            $request->validate(['notes' => 'nullable|string|max:1000']);
            DB::beginTransaction();
            try {
                $stage->update(['status' => 'completed', 'notes' => $request->notes, 'completed_at' => now()]);
                $nextSequentialDeptStage->update(['status' => 'in_progress', 'assigned_at' => now(), 'started_at' => now()]);
                $contract->update(['current_stage' => $nextSequentialDeptStage->sequence, 'status' => Contract::STATUS_UNDER_REVIEW, 'review_flow_status' => Contract::REVIEW_FLOW_IN_REVIEW]);
                ContractReviewLog::create([
                    'contract_id' => $contract->id, 'stage_id' => $stage->id, 'user_id' => $user->id_user,
                    'action' => 'approve_jump', 'description' => 'Approved, moved to: ' . $nextSequentialDeptStage->stage_name, 'notes' => $request->notes,
                ]);
                DB::commit();
                if ($nextSequentialDeptStage->assignedUser) {
                    try { $nextSequentialDeptStage->assignedUser->notify(new StageAssignedNotification($contract, $nextSequentialDeptStage, $user)); }
                    catch (\Exception $e) { Log::error('Notify failed: ' . $e->getMessage()); }
                }
            } catch (\Exception $e) {
                DB::rollBack();
                return back()->with('error', 'Failed to approve: ' . $e->getMessage());
            }
            return redirect()->route('contracts.show', $contract)->with('success', 'Approved! Moved to ' . $nextSequentialDeptStage->stage_name . '.');
        }

        // ── LAST LEGAL/REVIEW STAGE ──────────────────────────────────────
        // Cek apakah ini benar-benar stage terakhir review (sebelum executing)
        $isLastReviewStage = !ContractReviewStage::where('contract_id', $contract->id)
            ->whereNull('parallel_group')
            ->where('sequence', '>', $stage->sequence)
            ->whereNotIn('stage_type', ['executing', 'archiving'])
            ->whereNotIn('status', ['completed', 'rejected', 'skipped'])
            ->exists();
 
        if ($isLastReviewStage) {
            $request->validate(['notes' => 'nullable|string|max:1000']);
 
            // ── BLOCK: Jika nomor belum ada, tolak approve ────────────────
            // Legal HARUS generate number dulu sebelum approve last stage.
            // Ini mencegah stage jadi 'completed' tanpa executing stage aktif.
            if (!$contract->contract_number) {
                return redirect()->back()->with('error',
                    '⚠️ You must generate the contract number first before approving this final stage. ' .
                    'Please use the "Generate Document Number" button above.'
                );
            }
 
            try {
                DB::beginTransaction();
 
                // Selesaikan stage ini
                $stage->update([
                    'status'       => 'completed',
                    'notes'        => $request->notes,
                    'completed_at' => now(),
                ]);
 
                // ── Refresh contract dari DB agar executing_stage_id up-to-date ──
                $contract->refresh();
 
                // Nomor sudah ada → aktifkan executing stage
                $executingStage = null;
 
                // Coba via FK dulu (sudah di-refresh)
                if ($contract->executing_stage_id) {
                    $executingStage = ContractReviewStage::find($contract->executing_stage_id);
                    // Hanya null-kan jika status BUKAN pending
                    if ($executingStage && $executingStage->status !== 'pending') {
                        $executingStage = null;
                    }
                }
 
                // Fallback via stage_type jika FK tidak ditemukan
                if (!$executingStage) {
                    $executingStage = $contract->reviewStages()
                        ->where('stage_type', 'executing')
                        ->where('status', 'pending')
                        ->orderBy('sequence')
                        ->first();
                }
 
                if ($executingStage) {
                    $executingStage->update([
                        'status'      => 'assigned',
                        'assigned_at' => now(),
                        'notes'       => 'All reviews complete. Please proceed with document signing.',
                    ]);
 
                    $contract->update([
                        'current_stage'      => $executingStage->sequence,
                        'review_flow_status' => Contract::REVIEW_FLOW_IN_REVIEW,
                    ]);
 
                    ContractReviewLog::create([
                        'contract_id' => $contract->id,
                        'stage_id'    => $stage->id,
                        'user_id'     => $user->id_user,
                        'action'      => 'stage_approved',
                        'description' => 'Stage completed. Activating executing process.',
                        'notes'       => $request->notes,
                    ]);
 
                    try {
                        if ($executingStage->assignedUser) {
                            $executingStage->assignedUser->notify(
                                new StageAssignedNotification($contract, $executingStage, $user)
                            );
                        }
                    } catch (\Exception $e) {
                        Log::error('Notify owner for executing failed: ' . $e->getMessage());
                    }
 
                    DB::commit();
 
                    // Notify owner bahwa executing sudah aktif
                    try {
                        if ($contract->user) {
                            $contract->user->notify(
                                new StageJumpedNotification(
                                    $contract,
                                    $stage,
                                    $executingStage,
                                    $user,
                                    'executing_activated',
                                    'All reviews are complete. Please proceed with document signing.'
                                )
                            );
                        }
                    } catch (\Exception $e) {
                        Log::error('StageJumped executing_activated notify failed: ' . $e->getMessage());
                    }
 
                    return redirect()->route('contracts.show', $contract)
                        ->with('success', '✅ Review complete! The document owner is notified for the execution process.');
                }
 
                // Executing stage tidak ditemukan — ini kondisi abnormal,
                // seharusnya tidak terjadi karena nomor sudah ada berarti
                // executing stage sudah dibuat via generateNumber()
                ContractReviewLog::create([
                    'contract_id' => $contract->id,
                    'stage_id'    => $stage->id,
                    'user_id'     => $user->id_user,
                    'action'      => 'stage_approved',
                    'description' => 'Stage completed. Executing stage not found — needs to be checked.',
                    'notes'       => $request->notes,
                ]);
 
                DB::commit();
                return redirect()->route('contracts.show', $contract)
                    ->with('warning', '⚠️ Stage finished, but the executing stage was not found. Please contact admin.');
 
            } catch (\Exception $e) {
                DB::rollBack();
                return redirect()->back()->with('error', 'Failed to complete stage: ' . $e->getMessage());
            }
        }
    

        // ── Sequential jump ke stage legal/review berikutnya ─────────────
        $request->validate([
            'jump_to_stage_id' => 'required|exists:contract_review_stages,id',
            'notes'            => 'nullable|string|max:1000',
        ]);

        $jumpToStage = ContractReviewStage::findOrFail($request->jump_to_stage_id);
        if ($stage->id === $jumpToStage->id) {
            return redirect()->back()->with('error', 'Cannot jump to the same stage.');
        }

        try {
            DB::beginTransaction();

            $stage->update(['status' => 'completed', 'notes' => $request->notes, 'jump_to_stage_id' => $jumpToStage->id, 'completed_at' => now()]);

            ContractReviewJump::create([
                'contract_id' => $contract->id, 'from_stage_id' => $stage->id, 'to_stage_id' => $jumpToStage->id,
                'jumped_by' => $user->id_user, 'reason' => $request->notes ? "Approved with notes: {$request->notes}" : 'Approved',
            ]);

            ContractReviewLog::create([
                'contract_id' => $contract->id, 'stage_id' => $stage->id, 'user_id' => $user->id_user,
                'action' => 'approve_jump', 'description' => 'Approved and jumped to ' . $jumpToStage->stage_name, 'notes' => $request->notes,
                'metadata' => ['from_stage_id' => $stage->id, 'to_stage_id' => $jumpToStage->id],
            ]);

            $jumpToStage->update(['status' => 'in_progress', 'assigned_at' => now(), 'started_at' => now()]);
            $contract->update(['current_stage' => $jumpToStage->sequence, 'status' => Contract::STATUS_UNDER_REVIEW, 'review_flow_status' => Contract::REVIEW_FLOW_IN_REVIEW]);

            DB::commit();
            $this->notifyNextReviewer($contract, $stage, $jumpToStage);

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Failed to process approval. Please try again.');
        }

        return redirect()->route('contracts.show', $contract)->with('success', 'Approved and moved to ' . $jumpToStage->stage_name . '!');
    }

    /**
     * Helper: temukan executing stage yang masih pending
     */
    private function findPendingExecutingStage(Contract $contract): ?ContractReviewStage
    {
        $stage = $contract->reviewStages()
            ->where('stage_type', 'executing')
            ->where('status', 'pending')
            ->orderBy('sequence')
            ->first();

        if (!$stage && $contract->executing_stage_id) {
            $stage = ContractReviewStage::find($contract->executing_stage_id);
            if ($stage?->status !== 'pending') $stage = null;
        }

        return $stage;
    }

    // ============================================================
    // 12. PARALLEL STAGE HELPERS
    // ============================================================

    private function approveParallelStage(Request $request, Contract $contract, ContractReviewStage $stage, $user)
    {
        $request->validate(['notes' => 'nullable|string|max:1000']);

        DB::beginTransaction();
        try {
            $stage->update([
                'status' => 'completed', 'notes' => $request->notes, 'completed_at' => now(),
                'needs_revision' => false, 'revision_feedback' => null,
                'revision_requested_at' => null, 'revision_requested_by' => null,
            ]);

            ContractReviewLog::create([
                'contract_id' => $contract->id, 'stage_id' => $stage->id, 'user_id' => $user->id_user,
                'action' => 'parallel_stage_approved', 'description' => $stage->stage_name . ' approved (parallel)', 'notes' => $request->notes,
            ]);

            $grp = ContractReviewStage::where('contract_id', $contract->id)->where('parallel_group', $stage->parallel_group)->get();

            foreach ($grp->groupBy(fn($s) => $s->stage_type . '_' . $s->department_id) as $typeStages) {
                $assignedInType = $typeStages->whereNotNull('assigned_user_id');
                if ($assignedInType->isEmpty()) continue;
                if (!$assignedInType->every(fn($s) => $s->status === 'completed')) {
                    DB::commit();
                    $remaining = $assignedInType->where('status', '!=', 'completed')->count();
                    return redirect()->route('contracts.show', $contract)
                        ->with('success', "✓ {$stage->stage_name} approved. Waiting for {$remaining} other reviewers.");
                }
            }

            $unassignedCount = $grp->whereNull('assigned_user_id')->count();
            $pendingCount    = $grp->whereNotNull('assigned_user_id')->where('id', '!=', $stage->id)->where('status', '!=', 'completed')->count();

            DB::commit();

            if ($pendingCount === 0 && $unassignedCount === 0) {
                $this->activateNextAfterParallel($contract, $stage->parallel_group);
                return redirect()->route('contracts.show', $contract)->with('success', '🎉 All parallel reviews are complete!');
            }

            $waitMsg = '';
            if ($pendingCount > 0)    $waitMsg .= "{$pendingCount} other reviewers haven't approved yet. ";
            if ($unassignedCount > 0) $waitMsg .= "{$unassignedCount} departments haven't assigned staff.";

            return redirect()->route('contracts.show', $contract)->with('success', "✓ {$stage->stage_name} approved. Waiting: {$waitMsg}");

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to approve: ' . $e->getMessage());
        }
    }

    private function activateParallelStages(Request $request, Contract $contract, ContractReviewStage $stage, $parallelStages, $user)
    {
        $request->validate(['notes' => 'nullable|string|max:1000']);

        DB::beginTransaction();
        try {
            $stage->update(['status' => 'completed', 'notes' => $request->notes, 'completed_at' => now()]);

            foreach ($parallelStages as $ps) {
                if ($ps->assigned_user_id !== null) {
                    $ps->update(['status' => 'in_progress', 'assigned_at' => now(), 'started_at' => now()]);
                }
            }

            $contract->update([
                'current_stage'           => $parallelStages->first()->sequence,
                'status'                  => Contract::STATUS_UNDER_REVIEW,
                'review_flow_status'      => Contract::REVIEW_FLOW_IN_REVIEW,
                'multi_department_status' => 'multi_department',
            ]);

            ContractReviewLog::create([
                'contract_id' => $contract->id, 'stage_id' => $stage->id, 'user_id' => $user->id_user,
                'action' => 'parallel_stages_activated', 'description' => $parallelStages->count() . ' parallel stages enabled', 'notes' => $request->notes,
                'metadata' => ['activated_stages' => $parallelStages->pluck('stage_name')->toArray()],
            ]);

            DB::commit();

            foreach ($parallelStages->whereNotNull('assigned_user_id') as $ps) {
                if ($ps->assignedUser) {
                    try { $ps->assignedUser->notify(new StageAssignedNotification($contract, $ps, $user)); }
                    catch (\Exception $e) { Log::error('Notify parallel failed: ' . $e->getMessage()); }
                }
            }

            $stageNames = $parallelStages->pluck('stage_name')->unique()->join(', ');
            // ── Notify owner: substantial review dimulai ──────────────────
            try {
                if ($contract->user) {
                    $firstParStage = $parallelStages->first();
                    $contract->user->notify(
                        new StageJumpedNotification(
                            $contract,
                            $stage,
                            $firstParStage,
                            $user,
                            'parallel_started',
                            count($parallelStages) . ' departments are now reviewing simultaneously: '
                                . $parallelStages->pluck('stage_name')->unique()->join(', ') . '.'
                        )
                    );
                }
            } catch (\Exception $e) {
                Log::error('StageJumped parallel_started notify failed: ' . $e->getMessage());
            }

            return redirect()->route('contracts.show', $contract)
                ->with('success', "✅ {$parallelStages->count()} substantial reviews have started: {$stageNames}.");

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to activate parallel reviews: ' . $e->getMessage());
        }
    }

    private function activateNextAfterParallel(Contract $contract, int $parallelGroup): void
    {
        $parIds = ContractReviewStage::where('contract_id', $contract->id)
            ->where('parallel_group', $parallelGroup)
            ->pluck('id')
            ->toArray();

        $nextStage = ContractReviewStage::where('contract_id', $contract->id)
            ->whereNull('parallel_group')
            ->where('status', 'pending')
            ->where('is_user_stage', false)
            ->whereNotIn('stage_type', ['executing', 'archiving'])
            ->whereNotIn('id', $parIds)
            ->orderBy('sequence')
            ->first();

        if (!$nextStage) {
            // Parallel selesai, tidak ada stage berikutnya
            // Legal perlu generate nomor terlebih dahulu
            ContractReviewLog::create([
                'contract_id' => $contract->id,
                'stage_id'    => null,
                'user_id'     => auth()->id(),
                'action'      => 'parallel_completed_all_done',
                'description' => 'All parallel reviews are complete. Legal needs to generate a number to proceed.',
            ]);
            return;
        }

        $nextStage->update(['status' => 'assigned', 'assigned_at' => now()]);
        $contract->update([
            'current_stage'      => $nextStage->sequence,
            'status'             => Contract::STATUS_UNDER_REVIEW,
            'review_flow_status' => Contract::REVIEW_FLOW_IN_REVIEW,
        ]);

        ContractReviewLog::create([
            'contract_id' => $contract->id,
            'stage_id'    => $nextStage->id,
            'user_id'     => auth()->id(),
            'action'      => 'parallel_completed_next_activated',
            'description' => 'Next stage activated: ' . $nextStage->stage_name,
        ]);

        // Notify reviewer stage berikutnya
        if ($nextStage->assignedUser) {
            try {
                $nextStage->assignedUser->notify(
                new StageAssignedNotification(
                    $contract,
                    $nextStage,
                    auth()->user(),
                    null   // tidak ada single fromStage setelah parallel; notes paralel
                           // bisa ditambahkan via $allPrevNotes jika diperlukan
                )
            );
            } catch (\Exception $e) {
                Log::error('Notify next after parallel failed: ' . $e->getMessage());
            }
        }

        // Notify owner: substantial review selesai, stage berikutnya aktif
        try {
            if ($contract->user) {
                $contract->user->notify(
                    new StageJumpedNotification(
                        $contract,
                        null,
                        $nextStage,
                        auth()->user(),
                        'parallel_completed',
                        'All parallel (substantial) reviews are complete. '
                            . 'The workflow is now moving to the next stage: ' . $nextStage->stage_name . '.'
                    )
                );
            }
        } catch (\Exception $e) {
            Log::error('StageJumped parallel_completed notify failed: ' . $e->getMessage());
        }
    }

    private function notifyNextReviewer($contract, $fromStage, $toStage)
    {
        try {
            $actor = auth()->user();

            // 🔥 NOTIFY NEXT REVIEWER
            if ($toStage && $toStage->assignedUser) {
                $toStage->assignedUser->notify(
                    new StageAssignedNotification(
                        $contract,
                        $toStage,
                        $actor,
                        $fromStage // untuk ambil notes sebelumnya
                    )
                );
            }

            // 🔥 NOTIFY DOCUMENT OWNER (JIKA BUKAN ACTOR)
            if ($contract->user && (int) $contract->user->id_user !== (int) $actor->id_user) {
                $contract->user->notify(
                    new StageJumpedNotification(
                        $contract,
                        $fromStage,
                        $toStage,
                        $actor
                    )
                );
            }

        } catch (\Throwable $e) {
            Log::error('Notify after jump failed', [
                'error' => $e->getMessage(),
                'contract_id' => $contract->id ?? null,
                'to_stage' => $toStage->id ?? null,
            ]);
        }
    }


    // ============================================================
    // 13. REJECT
    // ============================================================

    public function reject(Request $request, Contract $contract, ContractReviewStage $stage)
    {
        $request->validate(['rejection_reason' => 'required|string|min:10|max:2000']);
        $user = TblUser::find(Auth::id());

        if ($stage->assigned_user_id !== $user->id_user && !$user->hasRole('admin')) abort(403);

        try {
            DB::beginTransaction();

            $stage->update(['status' => 'rejected', 'notes' => $request->rejection_reason, 'completed_at' => now()]);
            ContractReviewLog::create([
                'contract_id' => $contract->id, 'stage_id' => $stage->id, 'user_id' => $user->id_user,
                'action' => 'reject', 'description' => 'Contract rejected', 'notes' => $request->rejection_reason,
            ]);

            // Gunakan 'rejected' (lowercase) sesuai enum di DB
            $contract->update(['review_flow_status' => 'rejected', 'status' => 'declined']);

            ContractReviewStage::where('contract_id', $contract->id)
                ->whereIn('status', ['assigned', 'in_progress'])
                ->update(['status' => 'completed', 'completed_at' => now(), 'notes' => 'Cancelled due to contract rejection']);

            ContractRevisionTask::where('contract_id', $contract->id)
                ->whereIn('status', ['pending', 'in_progress', 'submitted', 're_requested'])
                ->update(['status' => 'cancelled', 'cancelled_at' => now()]);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Failed to reject contract. Please try again.');
        }

        // ✅ NOTIFIKASI: Notify owner dokumen bahwa kontrak ditolak
        try {
            if ($contract->user) {
                $contract->user->notify(
                    new ContractRejectedNotification(
                        $contract, $stage, $request->rejection_reason, $user, 'owner'
                    )
                );
            }
        } catch (\Exception $e) {
            Log::error('ContractRejectedNotification (owner) failed: ' . $e->getMessage());
        }
 
        // ── Notify semua reviewer yang pernah terlibat ───────────────────
        try {
            $involvedUserIds = $contract->reviewStages()
                ->whereNotNull('assigned_user_id')
                ->whereNotIn('stage_type', ['user'])
                ->pluck('assigned_user_id')
                ->unique();
 
            foreach ($involvedUserIds as $reviewerId) {
                // Skip owner (sudah dapat notif di atas) dan yang melakukan reject
                if ((int) $reviewerId === (int) $contract->user_id) continue;
                if ((int) $reviewerId === (int) $user->id_user) continue;
 
                $reviewer = TblUser::find($reviewerId);
                if ($reviewer) {
                    $reviewer->notify(
                        new ContractRejectedNotification(
                            $contract, $stage, $request->rejection_reason, $user, 'reviewer'
                        )
                    );
                }
            }
        } catch (\Exception $e) {
            Log::error('ContractRejectedNotification (reviewers) failed: ' . $e->getMessage());
        }

        return redirect()->route('contracts.show', $contract)->with('error', 'Contract has been rejected.');
    }

    // ============================================================
    // 14. LEGACY / DEPRECATED
    // ============================================================

    public function requestRevisionJump(Request $request, Contract $contract, ContractReviewStage $stage)
    {
        if ($stage->is_user_stage) {
            return $this->handleUserStageRevision($request, $contract, $stage);
        }
        return redirect()->route('revision-tasks.send', [$contract, $stage])
            ->with('info', 'Sistem revisi telah diperbarui. Silakan gunakan form baru.');
    }

    private function handleUserStageRevision(Request $request, Contract $contract, ContractReviewStage $stage)
    {
        $request->validate([
            'revision_notes'      => 'required|string|min:10|max:2000',
            'jump_to_stage_ids'   => 'required|array|min:1',
            'jump_to_stage_ids.*' => 'exists:contract_review_stages,id',
        ]);

        $currentUser = TblUser::find(Auth::id());

        try {
            DB::beginTransaction();

            $stage->update([
                'status' => 'revision_requested', 'notes' => $request->revision_notes,
                'revision_requested_by' => $currentUser->nama_user, 'completed_at' => now(),
            ]);

            $targetNames  = [];
            $targetStages = [];

            foreach ($request->jump_to_stage_ids as $targetId) {
                $targetStage = ContractReviewStage::find($targetId);
                if (!$targetStage) continue;

                $targetStage->update([
                    'status'                => 'revision_requested',
                    'needs_revision'        => true,
                    'revision_feedback'     => $request->revision_notes,
                    'revision_requested_by' => $currentUser->nama_user,
                    'revision_requested_at' => now(),
                    'started_at'            => null,
                    'completed_at'          => null,
                ]);

                $targetNames[]  = $targetStage->assignedUser->nama_user ?? $targetStage->stage_name;
                $targetStages[] = $targetStage;

                ContractReviewJump::create([
                    'contract_id' => $contract->id, 'from_stage_id' => $stage->id, 'to_stage_id' => $targetStage->id,
                    'jumped_by' => $currentUser->id_user, 'reason' => 'Revision requested: ' . $request->revision_notes,
                ]);
            }

            $firstTarget = $targetStages[0] ?? null;
            $contract->update([
                'current_stage'      => $firstTarget ? $firstTarget->sequence : $contract->current_stage,
                'review_flow_status' => Contract::REVIEW_FLOW_REVISION_REQUESTED,
                'status'             => Contract::STATUS_REVISION_NEEDED,
            ]);

            ContractReviewLog::create([
                'contract_id' => $contract->id, 'stage_id' => $stage->id, 'user_id' => $currentUser->id_user,
                'action' => 'revision_requested', 'description' => 'Revision requested ke: ' . implode(', ', $targetNames), 'notes' => $request->revision_notes,
            ]);

            DB::commit();
            return redirect()->route('contracts.show', $contract)->with('warning', 'Revision requested ke: ' . implode(', ', $targetNames));

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to request revision: ' . $e->getMessage());
        }
    }

    public function userContinueReview(Request $request, Contract $contract, ContractReviewStage $stage)
    {
        $request->validate([
            'jump_to_stage_id' => 'required|exists:contract_review_stages,id',
            'user_response'    => 'required|string|min:10|max:2000',
        ]);

        $jumpToStage = ContractReviewStage::findOrFail($request->jump_to_stage_id);
        $user        = TblUser::find(Auth::id());

        if ($stage->assigned_user_id !== $user->id_user && !$user->hasRole('admin')) abort(403);
        if (!$stage->is_user_stage) return redirect()->back()->with('error', 'Aksi ini hanya bisa dilakukan dari user stage.');

        try {
            DB::beginTransaction();
            $stage->update(['status' => 'completed', 'notes' => $request->user_response, 'completed_at' => now()]);
            ContractReviewJump::create([
                'contract_id' => $contract->id, 'from_stage_id' => $stage->id, 'to_stage_id' => $jumpToStage->id,
                'jumped_by' => $user->id_user, 'reason' => 'User responded: ' . $request->user_response,
            ]);
            $jumpToStage->update([
                'status' => 'in_progress', 'assigned_at' => now(), 'started_at' => now(),
                'needs_revision' => false, 'revision_feedback' => null,
            ]);
            $contract->update([
                'current_stage'      => $jumpToStage->sequence,
                'review_flow_status' => Contract::REVIEW_FLOW_IN_REVIEW,
                'status'             => Contract::STATUS_UNDER_REVIEW,
            ]);
            DB::commit();

            if ($jumpToStage->assignedUser) {
                try { $jumpToStage->assignedUser->notify(new StageAssignedNotification($contract, $jumpToStage, $user)); }
                catch (\Exception $e) { Log::error('Notify failed: ' . $e->getMessage()); }
            }
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal: ' . $e->getMessage());
        }

        return redirect()->route('contracts.show', $contract)->with('success', 'Revision sent to ' . $jumpToStage->stage_name . '.');
    }

    // ============================================================
    // 15. HELPER METHODS
    // ============================================================

    public function saveNotes(Request $request, Contract $contract, ContractReviewStage $stage)
    {
        $request->validate(['notes' => 'nullable|string|max:5000']);
        $user = TblUser::find(Auth::id());

        if ($stage->assigned_user_id !== $user->id_user && !$user->hasRole('admin')) abort(403);

        $stage->update(['notes' => $request->notes]);
        ContractReviewLog::create([
            'contract_id' => $contract->id, 'stage_id' => $stage->id, 'user_id' => $user->id_user,
            'action' => 'notes_saved', 'description' => 'Notes updated',
        ]);

        return redirect()->back()->with('success', 'Notes saved successfully.');
    }

    public function showUserStage(Contract $contract, ContractReviewStage $stage)
    {
        $user = TblUser::find(Auth::id());
        if ($stage->assigned_user_id !== $user->id_user && !$user->hasRole('admin')) abort(403);
        $stage->markVisited();
        $availableStages = $contract->getAvailableJumpStages($stage)->filter(fn($s) => !$s['is_user_stage'])->values();
        return view('reviews.stage-user', compact('contract', 'stage', 'availableStages'));
    }

    // ============================================================
    // 16. API ENDPOINTS
    // ============================================================

    public function getUsersByRole($role)
    {
        if (!Auth::check()) return response()->json(['error' => 'Unauthorized'], 401);

        $users = TblUser::role($role)->where('status_karyawan', 'AKTIF')
            ->select('id_user', 'nama_user', 'email')->orderBy('nama_user')->get()
            ->map(fn($u) => ['id' => $u->id_user, 'name' => $u->nama_user, 'email' => $u->email]);

        return response()->json($users);
    }

    public function previewNumber(Contract $contract, ContractNumberService $service)
    {
        $user = TblUser::find(Auth::id());
        if (!$user->can('view', $contract)) abort(403);

        try {
            return response()->json([
                'success'        => true,
                'preview_number' => $service->previewNumber($contract),
                'can_generate'   => $service->canGenerate($contract),
                'contract_status'=> $contract->status,
                'has_number'     => !empty($contract->contract_number),
                'current_number' => $contract->contract_number,
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    public function updateStageNotes(Request $request, ContractReviewStage $stage)
    {
        if (!Auth::check()) return response()->json(['error' => 'Unauthorized'], 401);
        $user = TblUser::find(Auth::id());
        if ($stage->assigned_user_id !== $user->id_user && !$user->hasRole('admin')) return response()->json(['error' => 'Forbidden'], 403);

        $request->validate(['notes' => 'nullable|string|max:5000']);
        $stage->update(['notes' => $request->notes]);

        return response()->json(['success' => true, 'updated_at' => now()->format('Y-m-d H:i:s')]);
    }

    // ============================================================
    // 17. ADMIN MANAGEMENT
    // ============================================================

    public function adminWorkflows(Request $request)
    {
        $user = TblUser::find(Auth::id());
        if (!$user->hasRole('admin')) abort(403);

        $status       = $request->get('status', 'all');
        $workflowType = $request->get('workflow_type', 'all');
        $query        = Contract::whereHas('reviewStages');
        if ($status !== 'all')       $query->where('review_flow_status', $status);
        if ($workflowType !== 'all') $query->where('workflow_type', $workflowType);

        $contracts = $query->with(['reviewStages', 'user'])->orderBy('created_at', 'desc')->paginate(20);
        $stats = [
            'total'              => Contract::whereHas('reviewStages')->count(),
            'in_review'          => Contract::where('review_flow_status', Contract::REVIEW_FLOW_IN_REVIEW)->count(),
            'completed'          => Contract::where('review_flow_status', Contract::REVIEW_FLOW_COMPLETED)->count(),
            'revision_requested' => Contract::where('review_flow_status', Contract::REVIEW_FLOW_REVISION_REQUESTED)->count(),
            'dynamic_workflows'  => Contract::where('workflow_type', 'dynamic')->count(),
        ];

        return view('admin.review-workflows', compact('contracts', 'stats', 'status', 'workflowType'));
    }

    public function adminWorkflowDetail(Contract $contract)
    {
        $user = TblUser::find(Auth::id());
        if (!$user->hasRole('admin')) abort(403);

        $stages            = $contract->reviewStages()->with('assignedUser:id_user,nama_user,email')->orderBy('sequence')->get();
        $reviewLogs        = $contract->reviewLogs()->with('user:id_user,nama_user')->orderBy('created_at', 'desc')->limit(50)->get();
        $departmentReviews = $contract->departmentReviews()->with('department')->get();

        return view('admin.workflow-detail', compact('contract', 'stages', 'reviewLogs', 'departmentReviews'));
    }
}