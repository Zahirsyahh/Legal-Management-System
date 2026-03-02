<?php

namespace App\Listeners;

use App\Events\ReviewLogCreated;
use App\Models\TblUser;
use App\Notifications\ContractActionNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Models\ContractReviewStage;


class SendReviewLogNotification implements ShouldQueue
{
    public function handle(ReviewLogCreated $event): void
{
    \Log::info('=== SendReviewLogNotification START ===');

    $log = $event->log;
    $contract = $log->contract;
    $action = strtolower($log->action);
    $metadata = $log->metadata ?? [];

    $targetUsers = collect();

    /*
    |--------------------------------------------------------------------------
    | WORKFLOW STARTED
    |--------------------------------------------------------------------------
    */
    if ($action === 'workflow_started') {

        if ($contract && $contract->user) {
            $targetUsers->push($contract->user);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | STAGE CREATED / ADDED
    |--------------------------------------------------------------------------
    */
    elseif (in_array($action, ['stage_created', 'stage_added'])) {

        if ($log->stage && $log->stage->assigned_user_id) {
            $assigned = TblUser::where('id_user', $log->stage->assigned_user_id)->first();
            if ($assigned) {
                $targetUsers->push($assigned);
            }
        }

        if ($contract && $contract->user) {
            $targetUsers->push($contract->user);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | STAGE STARTED
    |--------------------------------------------------------------------------
    */
    elseif ($action === 'stage_started') {

        if ($log->stage && $log->stage->assigned_user_id) {
            $assigned = TblUser::where('id_user', $log->stage->assigned_user_id)->first();
            if ($assigned) {
                $targetUsers->push($assigned);
            }
        }
    }

    /*
    |--------------------------------------------------------------------------
    | STAFF ASSIGNED
    |--------------------------------------------------------------------------
    */
    elseif ($action === 'staff_assigned') {

        if (!empty($metadata['user_id'])) {
            $staff = TblUser::where('id_user', $metadata['user_id'])->first();
            if ($staff) {
                $targetUsers->push($staff);
            }
        }
    }

    /*
    |--------------------------------------------------------------------------
    | APPROVE JUMP
    |--------------------------------------------------------------------------
    */
    elseif ($action === 'approve_jump') {

        // Creator
        if ($contract && $contract->user) {
            $targetUsers->push($contract->user);
        }

        // Reviewer di stage tujuan
        if (!empty($metadata['to_stage_id'])) {

            $nextStage = \App\Models\ContractReviewStage::find($metadata['to_stage_id']);

            if ($nextStage && $nextStage->assigned_user_id) {
                $nextUser = TblUser::where('id_user', $nextStage->assigned_user_id)->first();
                if ($nextUser) {
                    $targetUsers->push($nextUser);
                }
            }
        }
    }

    /*
    |--------------------------------------------------------------------------
    | FINAL APPROVE
    |--------------------------------------------------------------------------
    */
    elseif ($action === 'final_approve') {

        if ($contract && $contract->user) {
            $targetUsers->push($contract->user);
        }

        $reviewerIds = $contract->reviewStages()
            ->whereNotNull('assigned_user_id')
            ->pluck('assigned_user_id')
            ->unique()
            ->toArray();

        $reviewers = TblUser::whereIn('id_user', $reviewerIds)->get();
        $targetUsers = $targetUsers->merge($reviewers);
    }

    /*
    |--------------------------------------------------------------------------
    | REVISION REQUESTED
    |--------------------------------------------------------------------------
    */
    elseif ($action === 'revision_requested') {

        if (!empty($metadata['target_user_ids'])) {
            $users = TblUser::whereIn('id_user', $metadata['target_user_ids'])->get();
            $targetUsers = $targetUsers->merge($users);
        }

        if ($contract && $contract->user) {
            $targetUsers->push($contract->user);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | WORKFLOW UPDATED / STAGE DELETED
    |--------------------------------------------------------------------------
    */
    elseif (in_array($action, ['workflow_updated', 'stage_deleted'])) {

        if ($contract && $contract->user) {
            $targetUsers->push($contract->user);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | FINAL SEND
    |--------------------------------------------------------------------------
    */

    $targetUsers = $targetUsers
        ->filter()
        ->unique('id_user');

    \Log::info('TOTAL TARGET USERS: ' . $targetUsers->count());

    foreach ($targetUsers as $user) {
        $user->notify(new ContractActionNotification($log));
    }

    \Log::info('=== SendReviewLogNotification END ===');
}

}
