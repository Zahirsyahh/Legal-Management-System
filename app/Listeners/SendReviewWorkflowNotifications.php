<?php

namespace App\Listeners;

use App\Events\ReviewWorkflowStarted;
use App\Models\TblUser;
use App\Notifications\ReviewWorkflowStartedNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendReviewWorkflowNotifications implements ShouldQueue
{
    use InteractsWithQueue;

    public function handle(ReviewWorkflowStarted $event): void
    {
        \Log::info('SendReviewWorkflowNotifications triggered');

        $contract = $event->contract;
        $stages = $event->stages;
        $startedBy = $event->startedBy;

        /*
        |--------------------------------------------------------------------------
        | 1. Notify Admin (hak_akses tertentu)
        |--------------------------------------------------------------------------
        | Sesuaikan hak_akses admin kamu
        | contoh: 1 = pak muknis
        */

        $adminUsers = TblUser::where('hak_akses', 1)->get();

        foreach ($adminUsers as $admin) {
            $admin->notify(
                new ReviewWorkflowStartedNotification($contract, $stages, $startedBy)
            );
        }

        /*
        |--------------------------------------------------------------------------
        | 2. Notify Creator (contracts.user_id)
        |--------------------------------------------------------------------------
        */

        if ($contract->user_id) {
            $creator = TblUser::where('id_user', $contract->user_id)->first();

            if ($creator) {
                $creator->notify(
                    new ReviewWorkflowStartedNotification($contract, $stages, $startedBy)
                );
            }
        }

        /*
        |--------------------------------------------------------------------------
        | 3. Notify Assigned Reviewers
        |--------------------------------------------------------------------------
        */

        $reviewerIds = collect($stages)
            ->pluck('assigned_user_id')
            ->filter()
            ->unique()
            ->toArray();

        $reviewers = TblUser::whereIn('id_user', $reviewerIds)->get();

        foreach ($reviewers as $reviewer) {
            $reviewer->notify(
                new ReviewWorkflowStartedNotification($contract, $stages, $startedBy)
            );
        }

        /*
        |--------------------------------------------------------------------------
        | 4. Activity Log
        |--------------------------------------------------------------------------
        */

        activity()
            ->causedBy($startedBy)
            ->performedOn($contract)
            ->withProperties([
                'stages_count' => count($stages),
                'review_team' => $reviewerIds,
            ])
            ->log('started_review_workflow');
    }
}
