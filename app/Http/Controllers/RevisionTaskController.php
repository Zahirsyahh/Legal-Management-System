<?php

namespace App\Http\Controllers;

use App\Models\Contract;
use App\Models\ContractReviewStage;
use App\Models\ContractRevisionTask;
use App\Models\ContractReviewLog;
use App\Models\TblUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Notifications\StageAssignedNotification;

class RevisionTaskController extends Controller
{
    // =====================================================================
    // 1. SEND REVISION TASKS
    //    Dipanggil dari stage Bimo (pemberi revisi).
    //    Bimo tetap in_progress. Bunga & Sari masing-masing dapat task.
    // =====================================================================

    public function sendRevision(Request $request, Contract $contract, ContractReviewStage $stage)
    {
        $request->validate([
            'revision_notes'   => 'required|string|min:10|max:2000',
            'to_stage_ids'     => 'required|array|min:1',
            'to_stage_ids.*'   => 'exists:contract_review_stages,id',
        ]);

        $currentUser = TblUser::find(Auth::id());

        // Authorization: hanya assigned user atau admin
        if ($stage->assigned_user_id !== $currentUser->id_user && !$currentUser->hasRole('admin')) {
            abort(403, 'Unauthorized.');
        }

        // Stage harus sedang in_progress
        if ($stage->status !== 'in_progress') {
            return back()->with('error', 'Only stages that are currently in progress can send revisions.');
        }

        try {
            DB::beginTransaction();

            $createdTasks = [];
            $targetNames  = [];

            foreach ($request->to_stage_ids as $toStageId) {
                $toStage = ContractReviewStage::with('assignedUser')->find($toStageId);

                if (!$toStage || !$toStage->assigned_user_id) {
                    continue; // skip jika stage belum ada assignee
                }

                // Batalkan task lama yang masih aktif ke stage yang sama
                // (untuk menghindari duplikat jika re-send)
                ContractRevisionTask::where('contract_id', $contract->id)
                    ->where('from_stage_id', $stage->id)
                    ->where('to_stage_id', $toStage->id)
                    ->whereIn('status', ['pending', 'in_progress', 're_requested'])
                    ->update([
                        'status'       => 'cancelled',
                        'cancelled_at' => now(),
                    ]);

                // Buat revision task baru
                $task = ContractRevisionTask::create([
                    'contract_id'    => $contract->id,
                    'from_stage_id'  => $stage->id,
                    'to_stage_id'    => $toStage->id,
                    'requested_by'   => $currentUser->id_user,
                    'assigned_to'    => $toStage->assigned_user_id,
                    'revision_notes' => $request->revision_notes,
                    'status'         => 'pending',
                    'loop_count'     => 0,
                    'sent_at'        => now(),
                ]);

                // Update to_stage supaya penerima tahu ada task masuk
                // Status stage penerima tetap seperti sebelumnya (tidak dipaksa berubah),
                // hanya kita flag needs_revision = true
                $toStage->update([
                    'needs_revision'        => true,
                    'revision_feedback'     => $request->revision_notes,
                    'revision_requested_by' => $currentUser->nama_user,
                    'revision_requested_at' => now(),
                ]);

                $createdTasks[] = $task;
                $targetNames[]  = $toStage->assignedUser->nama_user ?? $toStage->stage_name;
            }

            if (empty($createdTasks)) {
                DB::rollBack();
                return back()->with('error', 'No valid revision recipients found (please ensure the stages have reviewers assigned).');
            }

            // ── Bimo TETAP in_progress, tidak berubah ──
            // Kita cukup tandai stage Bimo sedang punya open revision tasks
            $stage->update([
                'has_open_revision' => true, // kolom tambahan (lihat migration addons)
            ]);

            // Log ke review history
            ContractReviewLog::create([
                'contract_id' => $contract->id,
                'stage_id'    => $stage->id,
                'user_id'     => $currentUser->id_user,
                'action'      => 'revision_tasks_sent',
                'description' => 'Revisi dikirim ke: ' . implode(', ', $targetNames),
                'notes'       => $request->revision_notes,
                'metadata'    => [
                    'from_stage_id'  => $stage->id,
                    'from_stage'     => $stage->stage_name,
                    'to_stages'      => collect($createdTasks)->map(fn($t) => [
                        'task_id'      => $t->id,
                        'to_stage_id'  => $t->to_stage_id,
                        'assigned_to'  => $t->assigned_to,
                    ])->toArray(),
                    'target_names'   => $targetNames,
                    'sent_by'        => $currentUser->nama_user,
                ],
            ]);

            DB::commit();

            // Kirim notifikasi ke masing-masing penerima
            foreach ($createdTasks as $task) {
                try {
                    $assignee = TblUser::find($task->assigned_to);
                    if ($assignee) {
                        $assignee->notify(
                            new \App\Notifications\RevisionTaskAssignedNotification(
                                $contract,
                                $task,
                                $currentUser
                            )
                        );
                    }
                } catch (\Exception $e) {
                    Log::error('Failed to notify revision task assignee: ' . $e->getMessage());
                }
            }

            $msg = count($createdTasks) . ' revision requests successfully sent to: ' . implode(', ', $targetNames) . '. '
                 . 'You remain active in this stage and can monitor the progress of each task.';

            return redirect()->route('review-stages.show', [$contract, $stage])
                ->with('success', $msg);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to send revision tasks: ' . $e->getMessage(), [
                'contract_id' => $contract->id,
                'stage_id'    => $stage->id,
                'trace'       => $e->getTraceAsString(),
            ]);

            return back()->with('error', 'Failed to send revisions: ' . $e->getMessage());
        }
    }

    // =====================================================================
    // 2. ASSIGNEE: START WORKING ON REVISION TASK
    //    Bunga/Sari klik "Mulai Kerjakan"
    // =====================================================================

    public function startTask(Request $request, ContractRevisionTask $task)
    {
        $currentUser = TblUser::find(Auth::id());

        if ($task->assigned_to !== $currentUser->id_user && !$currentUser->hasRole('admin')) {
            abort(403, 'Unauthorized.');
        }

        if (!in_array($task->status, ['pending', 're_requested'])) {
            return back()->with('error', 'This task can no longer be started (status: ' . $task->status . ').');
        }

        $task->update([
            'status'     => 'in_progress',
            'started_at' => now(),
        ]);

        ContractReviewLog::create([
            'contract_id' => $task->contract_id,
            'stage_id'    => $task->to_stage_id,
            'user_id'     => $currentUser->id_user,
            'action'      => 'revision_task_started',
            'description' => $currentUser->nama_user . ' start working on revisions (Task #' . $task->id . ')',
            'metadata'    => ['task_id' => $task->id, 'loop_count' => $task->loop_count],
        ]);

        return redirect()->route('revision-tasks.show', $task)
            ->with('success', 'Revisions have begun. You can work on them and submit the results.');
    }

    // =====================================================================
    // 3. ASSIGNEE: SUBMIT REVISION BACK TO REQUESTER
    //    Bunga/Sari submit hasil revisi → langsung masuk ke Bimo
    // =====================================================================

    public function submitTask(Request $request, ContractRevisionTask $task)
    {
        $request->validate([
            'response_notes' => 'required|string|min:5|max:2000',
        ]);

        $currentUser = TblUser::find(Auth::id());

        if ($task->assigned_to !== $currentUser->id_user && !$currentUser->hasRole('admin')) {
            abort(403, 'Unauthorized.');
        }

        if (!in_array($task->status, ['pending', 'in_progress', 're_requested'])) {
            return back()->with('error', 'This task can no longer be submitted.');
        }

        try {
            DB::beginTransaction();

            $task->update([
                'status'         => 'submitted',
                'response_notes' => $request->response_notes,
                'submitted_at'   => now(),
            ]);

            // Bersihkan flag di stage penerima
            $toStage = $task->toStage;
            if ($toStage) {
                // Cek apakah masih ada task aktif lain di stage ini dari stage yang sama
                $stillHasActiveTask = ContractRevisionTask::where('to_stage_id', $toStage->id)
                    ->where('id', '!=', $task->id)
                    ->whereIn('status', ['pending', 'in_progress', 're_requested'])
                    ->exists();

                if (!$stillHasActiveTask) {
                    $toStage->update([
                        'needs_revision' => false,
                    ]);
                }
            }

            ContractReviewLog::create([
                'contract_id' => $task->contract_id,
                'stage_id'    => $task->to_stage_id,
                'user_id'     => $currentUser->id_user,
                'action'      => 'revision_task_submitted',
                'description' => $currentUser->nama_user . ' submitted revision results (Task #' . $task->id . ')',
                'notes'       => $request->response_notes,
                'metadata'    => [
                    'task_id'        => $task->id,
                    'from_stage_id'  => $task->from_stage_id,
                    'to_stage_id'    => $task->to_stage_id,
                    'loop_count'     => $task->loop_count,
                    'response_by'    => $currentUser->nama_user,
                ],
            ]);

            DB::commit();

            // Notifikasi ke Bimo bahwa Bunga sudah submit
            try {
                $requester = TblUser::find($task->requested_by);
                if ($requester) {
                    $requester->notify(
                        new \App\Notifications\RevisionTaskSubmittedNotification(
                            $task->contract,
                            $task,
                            $currentUser
                        )
                    );
                }
            } catch (\Exception $e) {
                Log::error('Failed to notify requester: ' . $e->getMessage());
            }

            return redirect()->route('contracts.show', $task->contract_id)
                ->with('success', 'Revision results submitted successfully. Awaiting decision from ' . ($task->requester->nama_user ?? 'reviewer'));

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to submit revision task: ' . $e->getMessage());
            return back()->with('error', 'Gagal submit revisi: ' . $e->getMessage());
        }
    }

    // =====================================================================
    // 4. REQUESTER (BIMO): APPROVE TASK
    //    Bimo approve hasil revisi Bunga. Task selesai.
    //    Jika semua task approved → baru Bimo bisa Approve Stage utama.
    // =====================================================================

    public function approveTask(Request $request, ContractRevisionTask $task)
    {
        $request->validate([
            'approval_notes' => 'nullable|string|max:1000',
        ]);

        $currentUser = TblUser::find(Auth::id());

        if ($task->requested_by !== $currentUser->id_user && !$currentUser->hasRole('admin')) {
            abort(403, 'Unauthorized.');
        }

        if ($task->status !== 'submitted') {
            return back()->with('error', 'Only tasks that have been submitted can be approved.');
        }

        try {
            DB::beginTransaction();

            $task->update([
                'status'      => 'approved',
                'approved_at' => now(),
            ]);

            ContractReviewLog::create([
                'contract_id' => $task->contract_id,
                'stage_id'    => $task->from_stage_id,
                'user_id'     => $currentUser->id_user,
                'action'      => 'revision_task_approved',
                'description' => $currentUser->nama_user . ' approve the revised results of ' . ($task->assignee->nama_user ?? 'reviewer') . ' (Task #' . $task->id . ')',
                'notes'       => $request->approval_notes,
                'metadata'    => [
                    'task_id'     => $task->id,
                    'approved_by' => $currentUser->nama_user,
                    'assignee'    => $task->assignee->nama_user ?? null,
                ],
            ]);

            // Cek apakah masih ada task aktif lain dari stage Bimo
            $pendingTasksCount = ContractRevisionTask::where('contract_id', $task->contract_id)
                ->where('from_stage_id', $task->from_stage_id)
                ->whereIn('status', ['pending', 'in_progress', 'submitted', 're_requested'])
                ->count();

            // Update flag has_open_revision di stage Bimo
            if ($pendingTasksCount === 0) {
                $fromStage = $task->fromStage;
                if ($fromStage) {
                    $fromStage->update(['has_open_revision' => false]);
                }
            }

            DB::commit();

            // Notifikasi ke Bunga bahwa hasil revisinya disetujui
            try {
                $assignee = TblUser::find($task->assigned_to);
                if ($assignee) {
                    $assignee->notify(
                        new \App\Notifications\RevisionTaskApprovedNotification(
                            $task->contract,
                            $task,
                            $currentUser
                        )
                    );
                }
            } catch (\Exception $e) {
                Log::error('Failed to notify assignee of approval: ' . $e->getMessage());
            }

            $remaining = ContractRevisionTask::where('contract_id', $task->contract_id)
                ->where('from_stage_id', $task->from_stage_id)
                ->whereIn('status', ['pending', 'in_progress', 'submitted', 're_requested'])
                ->count();

            $msg = 'The revised results of ' . ($task->assignee->nama_user ?? 'reviewer') . ' have been approved. ';
            if ($remaining > 0) {
                $msg .= $remaining . ' other reviewers are still in process.';
            } else {
                $msg .= 'All revisions are complete. You can now approve this stage to proceed to the next phase.';
            }

            return back()->with('success', $msg);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to approve revision task: ' . $e->getMessage());
            return back()->with('error', 'Failed to approve task: ' . $e->getMessage());
        }
    }

    // =====================================================================
    // 5. REQUESTER (BIMO): RE-REQUEST REVISION TO SAME PERSON
    //    Bimo merasa hasil Bunga masih kurang → kirim balik ke Bunga lagi
    // =====================================================================

    public function reRequestTask(Request $request, ContractRevisionTask $task)
    {
        $request->validate([
            'revision_notes' => 'required|string|min:10|max:2000',
        ]);

        $currentUser = TblUser::find(Auth::id());

        if ($task->requested_by !== $currentUser->id_user && !$currentUser->hasRole('admin')) {
            abort(403, 'Unauthorized.');
        }

        if ($task->status !== 'submitted') {
            return back()->with('error', 'Re-requests can only be made on tasks that have been submitted.');
        }

        try {
            DB::beginTransaction();

            // Tandai task lama sebagai re_requested (bukan approved)
            $task->update(['status' => 're_requested']);

            // Buat task baru sebagai child — loop baru
            $newTask = ContractRevisionTask::create([
                'contract_id'    => $task->contract_id,
                'from_stage_id'  => $task->from_stage_id,
                'to_stage_id'    => $task->to_stage_id,
                'requested_by'   => $currentUser->id_user,
                'assigned_to'    => $task->assigned_to,
                'revision_notes' => $request->revision_notes,
                'status'         => 'pending',
                'loop_count'     => $task->loop_count + 1,
                'sent_at'        => now(),
                'parent_task_id' => $task->id,
            ]);

            // Re-flag stage penerima
            $toStage = $task->toStage;
            if ($toStage) {
                $toStage->update([
                    'needs_revision'        => true,
                    'revision_feedback'     => $request->revision_notes,
                    'revision_requested_by' => $currentUser->nama_user,
                    'revision_requested_at' => now(),
                ]);
            }

            ContractReviewLog::create([
                'contract_id' => $task->contract_id,
                'stage_id'    => $task->from_stage_id,
                'user_id'     => $currentUser->id_user,
                'action'      => 'revision_re_requested',
                'description' => $currentUser->nama_user . ' resend revision to ' . ($task->assignee->nama_user ?? 'reviewer') . ' (Loop #' . ($task->loop_count + 1) . ')',
                'notes'       => $request->revision_notes,
                'metadata'    => [
                    'original_task_id' => $task->id,
                    'new_task_id'      => $newTask->id,
                    'loop_count'       => $newTask->loop_count,
                    'to_stage_id'      => $task->to_stage_id,
                    'assignee'         => $task->assignee->nama_user ?? null,
                ],
            ]);

            DB::commit();

            // Notifikasi ke Bunga
            try {
                $assignee = TblUser::find($task->assigned_to);
                if ($assignee) {
                    $assignee->notify(
                        new \App\Notifications\RevisionTaskAssignedNotification(
                            $task->contract,
                            $newTask,
                            $currentUser
                        )
                    );
                }
            } catch (\Exception $e) {
                Log::error('Failed to notify re-request: ' . $e->getMessage());
            }

            return back()->with('warning', 'Revisions sent to ' . ($task->assignee->nama_user ?? 'reviewer') . ' (Loop ke-' . $newTask->loop_count . ').');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to re-request revision task: ' . $e->getMessage());
            return back()->with('error', 'Gagal kirim ulang revisi: ' . $e->getMessage());
        }
    }

    // =====================================================================
    // 6. SHOW: Detail task untuk penerima (Bunga/Sari)
    // =====================================================================

    public function show(ContractRevisionTask $task)
    {
        $currentUser = TblUser::find(Auth::id());

        // Bisa diakses oleh: assignee, requester, admin
        $canAccess = $task->assigned_to === $currentUser->id_user
            || $task->requested_by === $currentUser->id_user
            || $currentUser->hasRole('admin');

        if (!$canAccess) {
            abort(403, 'Unauthorized.');
        }

        $task->load(['contract', 'fromStage.assignedUser', 'toStage.assignedUser', 'requester', 'assignee', 'parentTask']);

        // History loop untuk task ini dan leluhurnya
        $taskHistory = $this->buildTaskHistory($task);

        return view('revision-tasks.show', compact('task', 'taskHistory', 'currentUser'));
    }

    // =====================================================================
    // 7. HELPER: Build task history chain
    // =====================================================================

    private function buildTaskHistory(ContractRevisionTask $task): array
    {
        $history = [];
        $current = $task;

        // Naik ke atas sampai root task
        while ($current->parent_task_id) {
            $current = $current->parentTask;
        }

        // Turun dari root ke current
        $history[] = $current;
        $child = ContractRevisionTask::where('parent_task_id', $current->id)->first();
        while ($child) {
            $history[] = $child;
            $child = ContractRevisionTask::where('parent_task_id', $child->id)->first();
        }

        return $history;
    }

    // =====================================================================
    // 8. CANCEL TASK (oleh requester atau admin)
    // =====================================================================
    public function cancelTask(Request $request, ContractRevisionTask $task)
    {
        $currentUser = TblUser::find(Auth::id());
    
        if ($task->requested_by !== $currentUser->id_user && !$currentUser->hasRole('admin')) {
            abort(403, 'Unauthorized.');
        }
    
        if (in_array($task->status, ['approved', 'cancelled'])) {
            return back()->with('error', 'Task ini sudah tidak bisa dibatalkan.');
        }
    
        $task->update([
            'status'       => 'cancelled',
            'cancelled_at' => now(),
        ]);
    
        ContractReviewLog::create([
            'contract_id' => $task->contract_id,
            'stage_id'    => $task->from_stage_id,
            'user_id'     => $currentUser->id_user,
            'action'      => 'revision_task_cancelled',
            'description' => 'Revision task dibatalkan oleh ' . $currentUser->nama_user . ' (Task #' . $task->id . ')',
            'metadata'    => ['task_id' => $task->id, 'cancelled_by' => $currentUser->nama_user],
        ]);
    
        // Cek apakah masih ada task aktif dari stage yang sama
        $stillHasActive = ContractRevisionTask::where('contract_id', $task->contract_id)
            ->where('from_stage_id', $task->from_stage_id)
            ->whereIn('status', ['pending', 'in_progress', 'submitted', 're_requested'])
            ->exists();
    
        if (!$stillHasActive) {
            $task->fromStage?->update(['has_open_revision' => false]);
        }
    
        // Notifikasi ke assignee (reviewer yang task-nya dibatalkan)
        try {
            $assignee = TblUser::find($task->assigned_to);
            if ($assignee) {
                $assignee->notify(
                    new \App\Notifications\RevisionTaskCancelledNotification(
                        $task->contract,
                        $task,
                        $currentUser
                    )
                );
            }
        } catch (\Exception $e) {
            Log::error('Failed to notify assignee of task cancellation: ' . $e->getMessage());
        }
    
        return back()->with('success', 'Revision task has been cancelled. '
            . ($task->assignee?->nama_user ?? 'Reviewer') . ' has been notified.');
    }
}