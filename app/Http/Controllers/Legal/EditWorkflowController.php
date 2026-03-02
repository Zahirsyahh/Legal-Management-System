<?php

namespace App\Http\Controllers\Legal;

use App\Http\Controllers\Controller;
use App\Models\Contract;
use App\Models\ContractReviewStage;
use App\Models\ContractReviewLog;
use App\Models\TblUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class EditWorkflowController extends Controller
{
    /**
     * Show edit workflow page
     */
    public function edit(Contract $contract)
    {
        $stages = $contract->reviewStages()
            ->with('assignedUser:id_user,nama_user,email')
            ->orderBy('sequence')
            ->get();

        $users = TblUser::where('status_karyawan', 'AKTIF')
            ->select('id_user', 'nama_user', 'email')
            ->orderBy('nama_user')
            ->get();

        return view('contracts.workflow-edit', compact(
            'contract',
            'stages',
            'users'
        ));
    }

        /**
         * Update workflow (FULL CONTROL BY LEGAL)
         */
        public function update(Request $request, Contract $contract)
    {
        $this->validatePayload($request);

        DB::transaction(function () use ($request, $contract) {

            $currentStage = $this->getCurrentStage($contract);

            $changes = []; // 🔥 TRACK PERUBAHAN

            foreach ($request->stages as $payload) {

                // ============================
                // EXISTING STAGE
                // ============================
                if (!empty($payload['id'])) {

                    $stage = ContractReviewStage::findOrFail($payload['id']);

                    $original = $stage->replicate(); // clone untuk banding

                    // 🚨 ACTIVE STAGE PROTECTION
                    if ($currentStage && $stage->id === $currentStage->id) {

                        if ((int)$payload['assigned_user_id'] !== (int)$stage->assigned_user_id) {
                            $this->splitActiveStage($contract, $stage, $payload);

                            $changes[] = [
                                'type' => 'reviewer_changed_active',
                                'stage_name' => $stage->stage_name,
                                'old_reviewer' => $stage->assignedUser?->nama_user,
                            ];
                        }

                        $stage->update([
                            'status' => $payload['status']
                        ]);

                        continue;
                    }

                    // ============================
                    // ✅ NORMAL UPDATE + LOG DETECTION
                    // ============================

                    $originalSequence = $stage->sequence;
                    $originalReviewer = $stage->assigned_user_id;
                    $originalName     = $stage->stage_name;

                    $stage->update([
                        'stage_name' => $payload['stage_name'],
                        'assigned_user_id' => $payload['assigned_user_id'],
                        'sequence' => $payload['sequence'],
                        'status' => $payload['status'],
                    ]);

                    // Detect perubahan
                    $changes = [];

                    if ($originalSequence != $payload['sequence']) {
                        $changes[] = "Sequence changed from {$originalSequence} → {$payload['sequence']}";
                    }

                    if ($originalReviewer != $payload['assigned_user_id']) {
                        $oldUser = TblUser::find($originalReviewer);
                        $newUser = TblUser::find($payload['assigned_user_id']);

                        $changes[] = "Reviewer changed from "
                            . ($oldUser->nama_user ?? 'Unknown')
                            . " → "
                            . ($newUser->nama_user ?? 'Unknown');
                    }

                    if ($originalName != $payload['stage_name']) {
                        $changes[] = "Stage renamed from '{$originalName}' → '{$payload['stage_name']}'";
                    }

                    // Jika ada perubahan → buat log
                    if (!empty($changes)) {
                        ContractReviewLog::create([
                            'contract_id' => $contract->id,
                            'user_id'     => Auth::user()->id_user,
                            'stage_id'    => $stage->id,
                            'action'      => 'workflow_updated',
                            'description' => 'Workflow stage updated',
                            'metadata'    => [
                                'changes' => $changes,
                            ],
                        ]);
                    }

                    if ($original->assigned_user_id != $stage->assigned_user_id) {
                        $oldUser = TblUser::find($original->assigned_user_id);
                        $newUser = TblUser::find($stage->assigned_user_id);

                        $changes[] = [
                            'type' => 'reviewer_changed',
                            'stage_name' => $stage->stage_name,
                            'old_reviewer' => $oldUser?->nama_user,
                            'new_reviewer' => $newUser?->nama_user,
                        ];
                    }
                }

                // ============================
                // NEW STAGE
                // ============================
                else {

                    $newStage = ContractReviewStage::create([
                        'contract_id' => $contract->id,
                        'assigned_user_id' => $payload['assigned_user_id'],
                        'sequence' => $payload['sequence'],
                        'status' => $payload['status'] ?? 'pending',
                        'stage_name' => $payload['stage_name'],
                        'stage_type' => 'manual'
                    ]);

                    $user = TblUser::find($payload['assigned_user_id']);

                    $changes[] = [
                        'type' => 'stage_added',
                        'stage_name' => $newStage->stage_name,
                        'sequence' => $newStage->sequence,
                        'reviewer' => $user?->nama_user,
                    ];

                    // ✅ BUAT LOG KE DATABASE
                    ContractReviewLog::create([
                        'contract_id' => $contract->id,
                        'user_id'     => Auth::user()->id_user,
                        'stage_id'    => $newStage->id,
                        'action'      => 'stage_added',
                        'description' => 'New review stage added to workflow',
                        'metadata'    => [
                            'stage_name' => $newStage->stage_name,
                            'sequence'   => $newStage->sequence,
                            'reviewer_name' => $user?->nama_user,
                            'reviewer_email' => $user?->email,
                            'reviewer_id' => $user?->id_user,
                            'status'     => $newStage->status,
                            'added_by'   => Auth::user()->email,
                            'added_at'   => now()->toDateTimeString(),
                        ],
                    ]);
                }
            }

            // ============================
            // RESEQUENCE
            // ============================
            $this->resequenceStages($contract);
        });

        return redirect()
            ->route('contracts.show', $contract)
            ->with('success', 'Workflow Updated!');
    }


    public function deleteReviewer(Contract $contract, ContractReviewStage $stage)
{
    // Pastikan stage milik contract yang benar
    if ($stage->contract_id !== $contract->id) {
        abort(404);
    }

    // 🚨 Jangan boleh hapus stage aktif
    if (in_array($stage->status, ['assigned', 'in_progress'])) {
        return back()->with('error', 'Active reviewer cannot be deleted.');
    }

    DB::transaction(function () use ($contract, $stage) {

        $stageName = $stage->stage_name;
        $sequence  = $stage->sequence;
        $reviewer  = $stage->assignedUser?->nama_user;

        // Delete stage
        $stage->delete();

        // Resequence ulang biar rapih
        $this->resequenceStages($contract);

        // Log
        ContractReviewLog::create([
            'contract_id' => $contract->id,
            'stage_id'    => null,
            'user_id'     => Auth::user()->id_user,
            'action'      => 'stage_deleted',
            'description' => 'Reviewer removed from workflow',
            'metadata'    => [
                'stage_name' => $stageName,
                'sequence'   => $sequence,
                'reviewer'   => $reviewer,
                'deleted_by' => Auth::user()->email,
                'deleted_at' => now(),
            ],
        ]);
    });

    return back()->with('success', 'Reviewer removed successfully.');
}


    // ======================================================
    // 🔥 CORE LOGIC
    // ======================================================

    private function getCurrentStage(Contract $contract)
    {
        return $contract->reviewStages()
            ->whereIn('status', ['assigned', 'in_progress'])
            ->orderBy('sequence')
            ->first();
    }

    /**
     * Handle reorder when active stage reviewer is changed
     */
    private function splitActiveStage(
        Contract $contract,
        ContractReviewStage $activeStage,
        array $payload
    ) {
        // 1️⃣ Clone active reviewer to next position
        $cloned = $activeStage->replicate([
        'started_at',
        'completed_at',
        'notes'
    ]);

    $cloned->stage_type = $activeStage->stage_type; // ✅
    $cloned->sequence = $payload['sequence'] + 1;
    $cloned->status = 'assigned';
    $cloned->save();

        // 2️⃣ Skip original active stage
        $activeStage->update([
            'status' => 'skipped',
            'notes' => 'Workflow reordered by Legal',
            'completed_at' => now(),
        ]);

        // 3️⃣ Insert new reviewer at requested position
        ContractReviewStage::create([
            'contract_id' => $contract->id,
            'assigned_user_id' => $payload['assigned_user_id'],
            'sequence' => $payload['sequence'],
            'status' => 'assigned',
            'stage_name' => 'Inserted Review',
            'stage_type' => 'manual', // ✅
        ]);

        // 4️⃣ Update contract current stage
        $contract->update([
            'current_stage' => $payload['sequence']
        ]);
    }

    /**
     * Normalize sequence numbers
     */
    private function resequenceStages(Contract $contract)
    {
        $stages = $contract->reviewStages()
            ->orderBy('sequence')
            ->get();

        foreach ($stages as $index => $stage) {
            $stage->update([
                'sequence' => $index + 1
            ]);
        }
    }

    private function detectWorkflowChanges(array $before, array $after): array{
        $changes = [];

        foreach ($after as $newStage) {

            $oldStage = collect($before)->firstWhere('id', $newStage['id']);

            // Stage baru
            if (!$oldStage) {
                $changes[] = [
                    'type' => 'added',
                    'stage_name' => $newStage['stage_name'],
                    'sequence' => $newStage['sequence'],
                ];
                continue;
            }

            // Reviewer berubah
            if ($oldStage['assigned_user_id'] != $newStage['assigned_user_id']) {
                $changes[] = [
                    'type' => 'reviewer_changed',
                    'stage_name' => $newStage['stage_name'],
                    'from' => $oldStage['assigned_user_id'],
                    'to' => $newStage['assigned_user_id'],
                ];
            }

            // Sequence berubah
            if ($oldStage['sequence'] != $newStage['sequence']) {
                $changes[] = [
                    'type' => 'sequence_changed',
                    'stage_name' => $newStage['stage_name'],
                    'from' => $oldStage['sequence'],
                    'to' => $newStage['sequence'],
                ];
            }
        }

        return $changes;
    }


    /**
     * Payload validation
     */
    private function validatePayload(Request $request)
    {
        $request->validate([
            'stages' => 'required|array|min:1',
            'stages.*.assigned_user_id' => 'required|exists:tbl_user,id_user',
            'stages.*.sequence' => 'required|integer|min:1',
            'stages.*.status' => 'required|string',
        ]);
    }
}
