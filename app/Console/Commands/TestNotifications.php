<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Contract;
use App\Models\Department;
use App\Models\ContractReviewStage;

class TestNotifications extends Command
{
    protected $signature = 'notifications:test {user_id} {type}';
    protected $description = 'Test notification delivery';

    public function handle()
    {
        $userId = $this->argument('user_id');
        $type = $this->argument('type');
        
        $user = User::find($userId);
        
        if (!$user) {
            $this->error('User not found');
            return;
        }
        
        // Get test data
        $contract = Contract::first();
        $stage = ContractReviewStage::first();
        $department = Department::first();
        
        if (!$contract) {
            $this->error('No contracts found');
            return;
        }
        
        switch ($type) {
            case 'stage_assigned':
                if ($stage) {
                    $user->notify(new \App\Notifications\StageAssignedNotification(
                        $contract, $stage, $user
                    ));
                    $this->info("StageAssignedNotification sent to {$user->email}");
                }
                break;
                
            case 'department_assigned':
                if ($department) {
                    $user->notify(new \App\Notifications\DepartmentAssignmentNotification(
                        $contract, $department, $user
                    ));
                    $this->info("DepartmentAssignmentNotification sent to {$user->email}");
                }
                break;
                
            case 'staff_assigned':
                if ($department) {
                    $user->notify(new \App\Notifications\StaffAssignedNotification(
                        $contract, $department, $user, 'Test notes'
                    ));
                    $this->info("StaffAssignedNotification sent to {$user->email}");
                }
                break;
                
            case 'revision':
                if ($stage && $stage2 = ContractReviewStage::skip(1)->first()) {
                    $user->notify(new \App\Notifications\RevisionRequestedNotification(
                        $contract, $stage, $stage2, 'Test revision notes', $user
                    ));
                    $this->info("RevisionRequestedNotification sent to {$user->email}");
                }
                break;
        }
        
        // Check database
        $notificationCount = $user->notifications()->count();
        $this->info("Total notifications in database: {$notificationCount}");
        
        // Show recent notifications
        $notifications = $user->notifications()->latest()->limit(5)->get();
        foreach ($notifications as $notification) {
            $this->line("Type: {$notification->type}, Created: {$notification->created_at}");
        }
    }
}