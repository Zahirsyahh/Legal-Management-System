<?php

namespace App\Console\Commands;

use App\Models\ContractReviewLog;
use App\Models\ContractReviewStage;
use Illuminate\Console\Command;

class CleanupReviewLogs extends Command
{
    protected $signature = 'logs:cleanup';
    protected $description = 'Clean up corrupted review logs';

    public function handle()
    {
        $this->info('Cleaning up review logs...');
        
        // 1. Find logs with invalid stage_id
        $invalidStageLogs = ContractReviewLog::whereNotNull('stage_id')
            ->whereNotIn('stage_id', ContractReviewStage::pluck('id'))
            ->get();
            
        $this->info("Found {$invalidStageLogs->count()} logs with invalid stage_id");
        
        if ($invalidStageLogs->count() > 0) {
            if ($this->confirm('Do you want to set stage_id to NULL for these logs?')) {
                ContractReviewLog::whereNotNull('stage_id')
                    ->whereNotIn('stage_id', ContractReviewStage::pluck('id'))
                    ->update(['stage_id' => null]);
                $this->info('Fixed invalid stage_id references.');
            }
        }
        
        // 2. Find logs without user
        $logsWithoutUser = ContractReviewLog::whereDoesntHave('user')->get();
        $this->info("Found {$logsWithoutUser->count()} logs without user");
        
        if ($logsWithoutUser->count() > 0) {
            $this->warn('Consider deleting these logs or assigning a default user.');
        }
        
        // 3. Show summary
        $totalLogs = ContractReviewLog::count();
        $validLogs = ContractReviewLog::whereNotNull('stage_id')
            ->whereIn('stage_id', ContractReviewStage::pluck('id'))
            ->count();
            
        $this->info("\nSummary:");
        $this->info("Total logs: {$totalLogs}");
        $this->info("Valid logs (with existing stage): {$validLogs}");
        $this->info("Invalid logs: " . ($totalLogs - $validLogs));
        
        return Command::SUCCESS;
    }
}