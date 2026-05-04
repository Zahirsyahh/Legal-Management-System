<?php

namespace App\Http\Controllers\Legal;

use App\Http\Controllers\Controller;
use App\Models\Contract;
use App\Models\ContractReviewStage;
use App\Models\ContractReviewLog;
use App\Models\ContractDepartment;
use App\Models\Department;
use App\Models\TblUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Notifications\WorkflowUpdatedNotification;
use App\Notifications\StageRemovedNotification;

class EditWorkflowController extends Controller
{
    public function edit(Contract $contract)
    {
        if (!Auth::user()->hasAnyRole(['legal', 'admin'])) {
            abort(403);
        }

        // ✅ FIX: Eager-load contractDepartment supaya tersedia saat buildDisplayOrder
        $stages = $contract->reviewStages()
            ->with([
                'assignedUser:id_user,nama_user,email,jabatan',
            ])
            ->orderBy('sequence')
            ->get();

        // ✅ Manual mapping: ambil semua ContractDepartment contract ini sekali
        $contractDepartments = ContractDepartment::where('contract_id', $contract->id)->get();

        // ✅ Inject contract_department_id ke setiap stage
        $stages = $stages->map(function ($stage) use ($contractDepartments) {
            $dept = $contractDepartments->firstWhere('department_id', $stage->department_id);
            $stage->contract_department_id = $dept?->id;
            return $stage;
        });

        $users = TblUser::where('status_karyawan', 'AKTIF')
            ->select('id_user', 'nama_user', 'email', 'jabatan')
            ->orderBy('nama_user')
            ->get();

        $staffByDept = [
            'FIN'   => TblUser::whereHas('roles', fn($q) => $q->whereIn('name', ['staff_fin', 'admin_fin']))
                            ->where('status_karyawan', 'AKTIF')->orderBy('nama_user')->get(['id_user','nama_user','jabatan']),
            'ACC'   => TblUser::whereHas('roles', fn($q) => $q->whereIn('name', ['staff_acc', 'admin_acc']))
                            ->where('status_karyawan', 'AKTIF')->orderBy('nama_user')->get(['id_user','nama_user','jabatan']),
            'TAX'   => TblUser::whereHas('roles', fn($q) => $q->whereIn('name', ['staff_tax', 'admin_tax']))
                            ->where('status_karyawan', 'AKTIF')->orderBy('nama_user')->get(['id_user','nama_user','jabatan']),
            'LEGAL' => TblUser::whereHas('roles', fn($q) => $q->where('name', 'legal'))
                            ->where('status_karyawan', 'AKTIF')->orderBy('nama_user')->get(['id_user','nama_user','jabatan']),
        ];

        $selectedDeptCodes = json_decode($contract->selected_departments ?? '[]', true);
        $parallelStages    = $stages->whereNotNull('parallel_group')->groupBy('parallel_group');
        $sequentialStages  = $stages->whereNull('parallel_group');
        $displayOrder      = $this->buildDisplayOrder($stages);

        return view('contracts.workflow-edit', compact(
            'contract', 'stages', 'users',
            'staffByDept', 'selectedDeptCodes',
            'parallelStages', 'sequentialStages', 'displayOrder'
        ));
    }

    // ─────────────────────────────────────────────────────────
    // ✅ FIX UTAMA: buildDisplayOrder sekarang menyertakan
    //    contract_department_id di setiap stage parallel,
    //    dan TIDAK memfilter stage 'declined'
    // ─────────────────────────────────────────────────────────
    private function buildDisplayOrder($stages): array
    {
        $items                   = [];
        $processedParallelGroups = [];

        foreach ($stages->sortBy('sequence') as $stage) {
            if ($stage->parallel_group !== null) {
                $groupId = $stage->parallel_group;
                if (!in_array($groupId, $processedParallelGroups)) {
                    $processedParallelGroups[] = $groupId;
                    $groupStages = $stages->where('parallel_group', $groupId)->values();

                    $groupStagesMapped = $groupStages->map(function ($s) {
                        return [
                            'id'                     => $s->id,
                            'stage_name'             => $s->stage_name,
                            'stage_type'             => $s->stage_type,
                            'assigned_user_id'       => $s->assigned_user_id,
                            'status'                 => $s->status,
                            'sequence'               => $s->sequence,
                            'department_id'          => $s->department_id,
                            'parallel_group'         => $s->parallel_group,
                            // ✅ FIX: pakai property hasil inject, BUKAN relationship
                            'contract_department_id' => $s->contract_department_id,
                        ];
                    })->values();

                    $items[] = [
                        'type'     => 'parallel',
                        'group_id' => $groupId,
                        'sequence' => $stage->sequence,
                        'stages'   => $groupStagesMapped,
                    ];
                }
            } else {
                $items[] = ['type' => 'sequential', 'stage' => $stage];
            }
        }

        return $items;
    }

    // ─────────────────────────────────────────────────────────
    // UPDATE — menerima JSON payload dari JS
    // ─────────────────────────────────────────────────────────
    public function update(Request $request, Contract $contract)
    {
        if (!Auth::user()->hasAnyRole(['legal', 'admin'])) {
            abort(403);
        }

        $request->validate([
            'order'                => 'required|string',
            'synology_folder_path' => 'nullable|string|max:500',
        ]);

        $lockedStatuses = ['in_progress', 'completed', 'revision_requested'];
        $currentUser    = TblUser::find(Auth::id());
        $changes        = [];

        $orderData = json_decode($request->input('order'), true);
        if (!$orderData || !is_array($orderData)) {
            return back()->with('error', 'Invalid workflow order data.');
        }

        DB::beginTransaction();
        try {
            $legalDept   = Department::where('code', 'LEGAL')->first();
            $keepStageIds = [];
            $seqCounter  = 2;

            $currentActiveStage = $contract->reviewStages()
                ->whereNull('parallel_group')
                ->whereIn('status', ['assigned', 'in_progress'])
                ->orderBy('sequence')
                ->first();

            foreach ($orderData as $item) {

                // ── SEQUENTIAL STAGE ──────────────────────────────────────
                if ($item['type'] === 'sequential') {
                    $stageId   = !empty($item['stage_id']) ? (int) $item['stage_id'] : null;
                    $stageName = $item['stage_name'] ?? 'Legal Review';
                    $userId    = !empty($item['user_id']) ? (int) $item['user_id'] : null;
                    $status    = $item['status'] ?? 'pending';

                    if ($stageId) {
                        $stage = ContractReviewStage::find($stageId);
                        if ($stage && $stage->contract_id === $contract->id) {
                            $keepStageIds[] = $stageId;

                            if (in_array($stage->status, $lockedStatuses)) {
                                $stage->update(['sequence' => $seqCounter]);

                            // ✅ FIX: executing & archiving → JANGAN split, langsung update reviewer
                            } elseif (in_array($stage->stage_type, ['executing', 'archiving'])) {
                                $stage->update([
                                    'assigned_user_id' => $userId,
                                    'sequence'         => $seqCounter,
                                    'stage_name'       => $stageName,
                                ]);
                                if ($userId && $userId !== (int) $stage->getOriginal('assigned_user_id')) {
                                    $oldU = TblUser::find($stage->getOriginal('assigned_user_id'));
                                    $newU = TblUser::find($userId);
                                    $changes[] = "Reviewer '{$stageName}': " . ($oldU->nama_user ?? 'Unassigned') . " → " . ($newU->nama_user ?? '?');
                                }

                            } elseif ($currentActiveStage
                                && $stage->id === $currentActiveStage->id
                                && $userId && $userId !== (int) $stage->assigned_user_id) {
                                $this->splitActiveStage($contract, $stage, [
                                    'stage_name'       => $stageName,
                                    'assigned_user_id' => $userId,
                                    'sequence'         => $seqCounter,
                                ]);
                                $changes[] = "Reviewer aktif diganti di: {$stage->stage_name}";

                            } else {
                                $old = clone $stage;
                                $stage->update([
                                    'stage_name'       => $stageName,
                                    'assigned_user_id' => $userId,
                                    'sequence'         => $seqCounter,
                                    'status'           => $status,
                                    'parallel_group'   => null,
                                ]);
                                if ($old->assigned_user_id != $stage->assigned_user_id) {
                                    $oldU = TblUser::find($old->assigned_user_id);
                                    $newU = TblUser::find($userId);
                                    $changes[] = "Reviewer '{$stageName}': " . ($oldU->nama_user ?? '?') . " → " . ($newU->nama_user ?? '?');
                                }
                            }
                        }
                    } else {
                        if (!$userId) {
                            $seqCounter++;
                            continue;
                        }
                        $newStage = ContractReviewStage::create([
                            'contract_id'      => $contract->id,
                            'department_id'    => $legalDept?->id,
                            'stage_name'       => $stageName,
                            'stage_type'       => 'legal',
                            'assigned_user_id' => $userId,
                            'sequence'         => $seqCounter,
                            'parallel_group'   => null,
                            'status'           => $status ?: 'pending',
                            'created_by'       => $currentUser->id_user,
                            'is_manual_added'  => true,
                        ]);
                        $keepStageIds[] = $newStage->id;
                        $u = TblUser::find($userId);
                        $changes[] = "Stage baru: {$stageName} → " . ($u->nama_user ?? '?');
                    }

                    $seqCounter++;
                }

                // ── PARALLEL GROUP ────────────────────────────────────────
                elseif ($item['type'] === 'parallel') {
                    $groupId     = (int) ($item['group_id'] ?? 1);
                    $departments = $item['departments'] ?? [];
                    $activeDeptCodes = [];

                    $stageTypeMap = [
                        'FIN'   => 'finance',
                        'ACC'   => 'accounting',
                        'TAX'   => 'tax',
                        'LEGAL' => 'legal',
                    ];

                    foreach ($departments as $deptData) {
                        $deptCode = strtoupper($deptData['code'] ?? '');
                        if (!$deptCode) continue;

                        $dept = Department::where('code', $deptCode)->first();
                        if (!$dept) continue;

                        $deptSlots = $deptData['stages'] ?? [];
                        $stageType = $stageTypeMap[$deptCode] ?? strtolower($deptCode);

                        if (empty($deptSlots)) continue;

                        $activeDeptCodes[] = $deptCode;

                        foreach ($deptSlots as $ds) {
                            $dsId     = !empty($ds['id']) ? (int) $ds['id'] : null;
                            $dsUserId = !empty($ds['user_id']) ? (int) $ds['user_id'] : null;
                            $dsName   = $ds['stage_name'] ?? ($dept->name . ' Review');
                            $dsStatus = $ds['status'] ?? 'pending';

                            if ($dsId) {
                                $pStage = ContractReviewStage::find($dsId);
                                if ($pStage && $pStage->contract_id === $contract->id) {
                                    $keepStageIds[] = $dsId;

                                    if (!in_array($pStage->status, $lockedStatuses)) {
                                        $oldUserId = $pStage->assigned_user_id;
                                        $pStage->update([
                                            'stage_name'       => $dsName,
                                            'assigned_user_id' => $dsUserId,
                                            'sequence'         => $seqCounter,
                                            'parallel_group'   => $groupId,
                                            'department_id'    => $dept->id,
                                            'stage_type'       => $stageType,
                                        ]);
                                        if ($oldUserId != $dsUserId) {
                                            $oldU = $oldUserId ? TblUser::find($oldUserId)?->nama_user : 'Unassigned';
                                            $newU = $dsUserId  ? TblUser::find($dsUserId)?->nama_user  : 'Unassigned';
                                            $changes[] = "Reviewer parallel [{$deptCode}]: {$oldU} → {$newU}";
                                        }
                                    } else {
                                        $pStage->update([
                                            'sequence'       => $seqCounter,
                                            'parallel_group' => $groupId,
                                        ]);
                                    }
                                }
                            } else {
                                $existingPlaceholder = ContractReviewStage::where('contract_id', $contract->id)
                                    ->where('department_id', $dept->id)
                                    ->where('parallel_group', $groupId)
                                    ->whereNull('assigned_user_id')
                                    ->first();

                                if ($dsUserId) {
                                    $newPStage = ContractReviewStage::create([
                                        'contract_id'      => $contract->id,
                                        'department_id'    => $dept->id,
                                        'stage_name'       => $dsName,
                                        'stage_type'       => $stageType,
                                        'assigned_user_id' => $dsUserId,
                                        'sequence'         => $seqCounter,
                                        'parallel_group'   => $groupId,
                                        'status'           => 'pending',
                                        'created_by'       => $currentUser->id_user,
                                    ]);
                                    $keepStageIds[] = $newPStage->id;
                                    $u = TblUser::find($dsUserId)?->nama_user ?? '?';
                                    $changes[] = "Reviewer baru di parallel [{$deptCode}]: {$dsName} → {$u}";

                                    $this->upsertContractDepartment($contract, $dept, $deptCode, $dsUserId);

                                } elseif (!$existingPlaceholder) {
                                    $newPStage = ContractReviewStage::create([
                                        'contract_id'      => $contract->id,
                                        'department_id'    => $dept->id,
                                        'stage_name'       => $dsName,
                                        'stage_type'       => $stageType,
                                        'assigned_user_id' => null,
                                        'sequence'         => $seqCounter,
                                        'parallel_group'   => $groupId,
                                        'status'           => 'pending',
                                        'created_by'       => $currentUser->id_user,
                                        'notes'            => 'Awaiting staff assignment from department admin.',
                                    ]);
                                    $keepStageIds[] = $newPStage->id;
                                    $changes[] = "Dept [{$deptCode}] ditambahkan ke substantial review (menunggu reviewer)";

                                    $this->upsertContractDepartment($contract, $dept, $deptCode, null);
                                } else {
                                    $keepStageIds[] = $existingPlaceholder->id;
                                    $existingPlaceholder->update([
                                        'sequence'       => $seqCounter,
                                        'parallel_group' => $groupId,
                                    ]);
                                }
                            }
                        }
                    }

                    $contract->update([
                        'selected_departments'    => json_encode($activeDeptCodes),
                        'multi_department_status' => count($activeDeptCodes) >= 2 ? 'multi_department' : 'single_department',
                    ]);

                    $seqCounter++;
                }
            }

            // ✅ FIX: Jangan hapus stage 'declined' — stage declined harus dipertahankan
            // supaya tombol re-invite tetap bisa muncul dan data tidak hilang
            $stagesToDelete = $contract->reviewStages()
                ->whereNotIn('id', $keepStageIds)
                ->whereNotIn('status', array_merge($lockedStatuses, ['declined'])) // ← TAMBAH 'declined'
                ->where(function ($q) {
                    $q->where('is_user_stage', false)->orWhereNull('is_user_stage');
                })
                ->where('stage_type', '!=', 'user')
                ->get();

            foreach ($stagesToDelete as $del) {
                $changes[] = "Stage dihapus: {$del->stage_name}";
                $del->delete();
            }

            $firstActive = $contract->reviewStages()
                ->whereIn('status', ['assigned', 'in_progress'])
                ->orderBy('sequence')
                ->first();
            if ($firstActive) {
                $contract->update(['current_stage' => $firstActive->sequence]);
            }

            if ($request->filled('synology_folder_path')) {
                $contract->update(['synology_folder_path' => $request->synology_folder_path]);
            }

            if (!empty($changes)) {
                ContractReviewLog::create([
                    'contract_id' => $contract->id,
                    'stage_id'    => null,
                    'user_id'     => $currentUser->id_user,
                    'action'      => 'workflow_updated',
                    'description' => 'Workflow diedit oleh ' . $currentUser->nama_user,
                    'metadata'    => ['changes' => $changes],
                ]);
            }

            DB::commit();

            try {
                if ($contract->user) {
                    $contract->user->notify(
                        new WorkflowUpdatedNotification($contract, $currentUser, $changes, 'owner')
                    );
                }
            } catch (\Exception $e) {
                Log::error('WorkflowUpdatedNotification (owner) failed: ' . $e->getMessage());
            }

            try {
                $notifiedReviewerIds = [];
                foreach ($orderData as $item) {
                    if ($item['type'] === 'sequential' && empty($item['stage_id'])) {
                        $newUserId = !empty($item['user_id']) ? (int) $item['user_id'] : null;
                        if ($newUserId && !in_array($newUserId, $notifiedReviewerIds)) {
                            $notifiedReviewerIds[] = $newUserId;
                            $newReviewer = TblUser::find($newUserId);
                            if ($newReviewer) {
                                $newReviewer->notify(
                                    new WorkflowUpdatedNotification(
                                        $contract, $currentUser, [], 'reviewer',
                                        $item['stage_name'] ?? 'Legal Review'
                                    )
                                );
                            }
                        }
                    }
                }
            } catch (\Exception $e) {
                Log::error('WorkflowUpdatedNotification (reviewer) failed: ' . $e->getMessage());
            }

            return redirect()
                ->route('contracts.show', $contract)
                ->with('success', 'Workflow berhasil diperbarui! ' . count($changes) . ' perubahan disimpan.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Workflow update failed: ' . $e->getMessage(), [
                'contract_id' => $contract->id,
                'trace'       => $e->getTraceAsString(),
            ]);
            return back()->withInput()->with('error', 'Gagal menyimpan: ' . $e->getMessage());
        }
    }

    private function upsertContractDepartment(Contract $contract, $dept, string $deptCode, ?int $userId): void
    {
        $adminRoleMap = [
            'FIN'   => 'admin_fin',
            'ACC'   => 'admin_acc',
            'TAX'   => 'admin_tax',
            'LEGAL' => 'legal',
        ];
        $adminRole = $adminRoleMap[$deptCode] ?? null;
        $adminUser = $adminRole ? TblUser::role($adminRole)->first() : null;

        ContractDepartment::updateOrCreate(
            ['contract_id' => $contract->id, 'department_id' => $dept->id],
            [
                'status'            => $userId ? 'assigned' : 'pending_assignment',
                'assigned_admin_id' => $adminUser?->id_user,
                'assigned_at'       => $adminUser ? now() : null,
            ]
        );
    }

    public function deleteReviewer(Contract $contract, ContractReviewStage $stage)
    {
        if (!Auth::user()->hasAnyRole(['legal', 'admin'])) {
            abort(403);
        }

        if ($stage->contract_id !== $contract->id) {
            abort(404);
        }

        $lockedStatuses = ['assigned', 'in_progress', 'completed', 'revision_requested'];
        if (in_array($stage->status, $lockedStatuses)) {
            return back()->with('error', 'Stage aktif atau sudah selesai tidak bisa dihapus.');
        }

        DB::transaction(function () use ($contract, $stage) {
            $stageName = $stage->stage_name;
            $reviewer  = $stage->assignedUser?->nama_user;
            $stage->delete();

            ContractReviewLog::create([
                'contract_id' => $contract->id,
                'stage_id'    => null,
                'user_id'     => Auth::user()->id_user,
                'action'      => 'stage_deleted',
                'description' => 'Stage dihapus dari workflow',
                'metadata'    => [
                    'stage_name' => $stageName,
                    'reviewer'   => $reviewer,
                    'deleted_by' => Auth::user()->nama_user,
                ],
            ]);
        });

        return back()->with('success', 'Stage berhasil dihapus.');
    }

    private function splitActiveStage(Contract $contract, ContractReviewStage $activeStage, array $payload): void
    {
        $cloned           = $activeStage->replicate(['started_at', 'completed_at', 'notes']);
        $cloned->sequence = $payload['sequence'] + 1;
        $cloned->status   = 'pending';
        $cloned->save();

        $activeStage->update([
            'status'       => 'skipped',
            'notes'        => 'Reviewer diganti melalui workflow edit.',
            'completed_at' => now(),
        ]);

        ContractReviewStage::create([
            'contract_id'      => $contract->id,
            'department_id'    => $activeStage->department_id,
            'stage_name'       => $payload['stage_name'],
            'stage_type'       => $activeStage->stage_type,
            'assigned_user_id' => $payload['assigned_user_id'],
            'sequence'         => $payload['sequence'],
            'parallel_group'   => null,
            'status'           => 'assigned',
            'created_by'       => Auth::id(),
        ]);

        $contract->update(['current_stage' => $payload['sequence']]);
    }

    public function getReviewerCandidates(Request $request)
    {
        $search     = $request->get('q');
        $contractId = $request->get('contract_id');

        $contract = Contract::find($contractId);

        if (!$contract) {
            return response()->json(['results' => []]);
        }

        $allowedRoles = [
            'legal', 'admin_fin', 'admin_tax', 'admin_acc',
            'staff_fin', 'staff_acc', 'staff_tax',
        ];

        $users = TblUser::select('id_user', 'nama_user', 'jabatan')
            ->where('status_karyawan', 'AKTIF')
            ->where(function ($query) use ($allowedRoles, $contract) {
                $query->whereHas('roles', function ($q) use ($allowedRoles) {
                    $q->whereIn('name', $allowedRoles);
                })
                ->orWhere('id_user', $contract->user_id);
            })
            ->when($search, function ($query, $search) {
                $query->where('nama_user', 'like', '%' . $search . '%');
            })
            ->orderBy('nama_user')
            ->limit(20)
            ->get();

        $results = $users->map(function ($user) use ($contract) {
            return [
                'id'   => $user->id_user,
                'text' => $user->nama_user
                    . ($user->jabatan ? ' · ' . $user->jabatan : '')
                    . ($user->id_user == $contract->user_id ? ' — Owner' : ''),
            ];
        });

        return response()->json(['results' => $results]);
    }

    // ─────────────────────────────────────────────────────────
    // ✅ FIX reInvite: cari placeholder dengan status 'declined'
    //    (bukan 'skipped'), karena declineInvitation() menyimpan
    //    stage dengan status 'declined', bukan 'skipped'
    // ─────────────────────────────────────────────────────────
    public function reInvite(Request $request, Contract $contract, ContractDepartment $contractDepartment)
    {
        if (!Auth::user()->hasAnyRole(['legal', 'admin'])) {
            abort(403);
        }

        if ($contractDepartment->contract_id !== $contract->id) {
            abort(404);
        }

        if ($contractDepartment->status !== 'declined') {
            return back()->with('error', 'This department has not declined the invitation — no re-invite needed.');
        }

        $currentUser = TblUser::find(Auth::id());
        $department  = $contractDepartment->department;

        DB::beginTransaction();
        try {
            // 1. Reset ContractDepartment back to pending_assignment
            $contractDepartment->update([
                'status'      => 'pending_assignment',
                'declined_at' => null,
                'declined_by' => null,
                'notes'       => 'Re-invited by ' . $currentUser->nama_user . ' on ' . now()->format('d M Y H:i'),
            ]);

            // ✅ FIX: Cari stage 'declined' (bukan 'skipped')
            // DepartmentAdminController::declineInvitation() set status → 'declined'
            $placeholderStage = ContractReviewStage::where('contract_id', $contract->id)
                ->where('department_id', $contractDepartment->department_id)
                ->where('status', 'declined')   // ← FIX: was 'skipped'
                ->first();                       // ← hapus filter whereNull('assigned_user_id')
                                                 //   karena stage declined bisa punya user_id atau tidak

            if ($placeholderStage) {
                // 2a. Restore stage yang declined → pending
                $placeholderStage->update([
                    'status'           => 'pending',
                    'assigned_user_id' => null, // reset reviewer, dept admin akan assign ulang
                    'notes'            => 'Stage restored after re-invite by ' . $currentUser->nama_user,
                ]);
            } else {
                // 2b. Tidak ada stage declined → buat placeholder baru
                $stageTypeMap = ['FIN' => 'finance', 'ACC' => 'accounting', 'TAX' => 'tax', 'LEGAL' => 'legal'];
                $deptCode     = $department->code ?? 'FIN';
                $stageType    = $stageTypeMap[$deptCode] ?? strtolower($deptCode);

                // Cari posisi sequence dari group parallel yang ada
                $parallelStage = ContractReviewStage::where('contract_id', $contract->id)
                    ->whereNotNull('parallel_group')
                    ->where('department_id', '!=', $contractDepartment->department_id)
                    ->orderBy('sequence')
                    ->first();

                $sequence      = $parallelStage ? $parallelStage->sequence : (($contract->reviewStages()->max('sequence') ?? 1) + 1);
                $parallelGroup = $parallelStage ? $parallelStage->parallel_group : null;

                $placeholderStage = ContractReviewStage::create([
                    'contract_id'      => $contract->id,
                    'department_id'    => $contractDepartment->department_id,
                    'stage_name'       => $department->name . ' Review',
                    'stage_type'       => $stageType,
                    'assigned_user_id' => null,
                    'sequence'         => $sequence,
                    'parallel_group'   => $parallelGroup,
                    'status'           => 'pending',
                    'notes'            => 'Awaiting staff assignment. Re-invited by ' . $currentUser->nama_user,
                    'created_by'       => $currentUser->id_user,
                ]);
            }

            // 3. Log the re-invite action
            ContractReviewLog::create([
                'contract_id' => $contract->id,
                'stage_id'    => $placeholderStage?->id,
                'user_id'     => $currentUser->id_user,
                'action'      => 'department_reinvited',
                'description' => $currentUser->nama_user . ' re-invited ' . $department->name . ' to the review workflow.',
                'metadata'    => [
                    'department_id'   => $contractDepartment->department_id,
                    'department_name' => $department->name,
                    'reinvited_by'    => $currentUser->nama_user,
                ],
            ]);

            DB::commit();

            // 4. Notify department admin (non-blocking)
            try {
                $adminRoleMap = ['FIN' => 'admin_fin', 'ACC' => 'admin_acc', 'TAX' => 'admin_tax'];
                $adminRole    = $adminRoleMap[$department->code] ?? null;

                if ($adminRole) {
                    $admins = TblUser::role($adminRole)->where('status_karyawan', 'AKTIF')->get();
                    foreach ($admins as $admin) {
                        // Pastikan DepartmentAssignmentNotification sudah ada di project kamu
                        if (class_exists(\App\Notifications\DepartmentAssignmentNotification::class)) {
                            $admin->notify(new \App\Notifications\DepartmentAssignmentNotification($contract, $department, $currentUser));
                        }
                    }
                }
            } catch (\Exception $e) {
                Log::error('Re-invite notification failed: ' . $e->getMessage());
            }

            return redirect()
                ->route('legal.workflow.edit', $contract)
                ->with('success', $department->name . ' has been re-invited to the review workflow.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('reInvite failed: ' . $e->getMessage(), ['contract_id' => $contract->id]);
            return back()->with('error', 'Failed to re-invite department: ' . $e->getMessage());
        }
    }
}