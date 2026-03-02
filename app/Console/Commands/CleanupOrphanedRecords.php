<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\Contract;
use App\Models\ContractReviewStage;
use App\Models\ContractDepartment;

class CleanupOrphanedRecords extends Command
{
    protected $signature = 'cleanup:orphaned-records {--force : Force cleanup without confirmation}';
    protected $description = 'Clean up orphaned records from database after contracts deletion';

    public function handle()
    {
        $this->info('🔍 Scanning for orphaned records...');
        $this->newLine();

        $validContractIds = DB::table('contracts')
            ->whereNull('deleted_at')
            ->pluck('id')
            ->toArray();

        $this->info('✓ Found ' . count($validContractIds) . ' valid contracts');
        $this->newLine();

        // ===================================
        // 1. CONTRACT REVIEW STAGES
        // ===================================
        $this->info('📋 Checking contract_review_stages...');
        
        $orphanedStages = DB::table('contract_review_stages')
            ->whereNotIn('contract_id', $validContractIds)
            ->get();

        if ($orphanedStages->count() > 0) {
            $this->warn("   Found {$orphanedStages->count()} orphaned review stages");
            
            if ($this->option('force') || $this->confirm('   Delete these orphaned review stages?', true)) {
                $deleted = DB::table('contract_review_stages')
                    ->whereNotIn('contract_id', $validContractIds)
                    ->delete();
                
                $this->info("   ✓ Deleted {$deleted} orphaned review stages");
            } else {
                $this->comment('   Skipped');
            }
        } else {
            $this->info('   ✓ No orphaned review stages found');
        }

        $this->newLine();

        // ===================================
        // 2. CONTRACT DEPARTMENTS
        // ===================================
        $this->info('📋 Checking contract_departments...');
        
        $orphanedDepts = DB::table('contract_departments')
            ->whereNotIn('contract_id', $validContractIds)
            ->get();

        if ($orphanedDepts->count() > 0) {
            $this->warn("   Found {$orphanedDepts->count()} orphaned contract departments");
            
            if ($this->option('force') || $this->confirm('   Delete these orphaned contract departments?', true)) {
                $deleted = DB::table('contract_departments')
                    ->whereNotIn('contract_id', $validContractIds)
                    ->delete();
                
                $this->info("   ✓ Deleted {$deleted} orphaned contract departments");
            } else {
                $this->comment('   Skipped');
            }
        } else {
            $this->info('   ✓ No orphaned contract departments found');
        }

        $this->newLine();

        // ===================================
        // 3. CONTRACT DOCUMENTS
        // ===================================
        if (DB::getSchemaBuilder()->hasTable('contract_documents')) {
            $this->info('📋 Checking contract_documents...');
            
            $orphanedDocs = DB::table('contract_documents')
                ->whereNotIn('contract_id', $validContractIds)
                ->get();

            if ($orphanedDocs->count() > 0) {
                $this->warn("   Found {$orphanedDocs->count()} orphaned contract documents");
                
                if ($this->option('force') || $this->confirm('   Delete these orphaned contract documents?', true)) {
                    $deleted = DB::table('contract_documents')
                        ->whereNotIn('contract_id', $validContractIds)
                        ->delete();
                    
                    $this->info("   ✓ Deleted {$deleted} orphaned contract documents");
                } else {
                    $this->comment('   Skipped');
                }
            } else {
                $this->info('   ✓ No orphaned contract documents found');
            }

            $this->newLine();
        }

        // ===================================
        // 4. CONTRACT VERSIONS
        // ===================================
        if (DB::getSchemaBuilder()->hasTable('contract_versions')) {
            $this->info('📋 Checking contract_versions...');
            
            $orphanedVersions = DB::table('contract_versions')
                ->whereNotIn('contract_id', $validContractIds)
                ->get();

            if ($orphanedVersions->count() > 0) {
                $this->warn("   Found {$orphanedVersions->count()} orphaned contract versions");
                
                if ($this->option('force') || $this->confirm('   Delete these orphaned contract versions?', true)) {
                    $deleted = DB::table('contract_versions')
                        ->whereNotIn('contract_id', $validContractIds)
                        ->delete();
                    
                    $this->info("   ✓ Deleted {$deleted} orphaned contract versions");
                } else {
                    $this->comment('   Skipped');
                }
            } else {
                $this->info('   ✓ No orphaned contract versions found');
            }

            $this->newLine();
        }

        // ===================================
        // 5. CONTRACT REVIEW LOGS
        // ===================================
        if (DB::getSchemaBuilder()->hasTable('contract_review_logs')) {
            $this->info('📋 Checking contract_review_logs...');
            
            $orphanedLogs = DB::table('contract_review_logs')
                ->whereNotIn('contract_id', $validContractIds)
                ->get();

            if ($orphanedLogs->count() > 0) {
                $this->warn("   Found {$orphanedLogs->count()} orphaned contract review logs");
                
                if ($this->option('force') || $this->confirm('   Delete these orphaned contract review logs?', true)) {
                    $deleted = DB::table('contract_review_logs')
                        ->whereNotIn('contract_id', $validContractIds)
                        ->delete();
                    
                    $this->info("   ✓ Deleted {$deleted} orphaned contract review logs");
                } else {
                    $this->comment('   Skipped');
                }
            } else {
                $this->info('   ✓ No orphaned contract review logs found');
            }

            $this->newLine();
        }

        // ===================================
        // 6. CONTRACT COMMENTS
        // ===================================
        if (DB::getSchemaBuilder()->hasTable('contract_comments')) {
            $this->info('📋 Checking contract_comments...');
            
            $orphanedComments = DB::table('contract_comments')
                ->whereNotIn('contract_id', $validContractIds)
                ->get();

            if ($orphanedComments->count() > 0) {
                $this->warn("   Found {$orphanedComments->count()} orphaned contract comments");
                
                if ($this->option('force') || $this->confirm('   Delete these orphaned contract comments?', true)) {
                    $deleted = DB::table('contract_comments')
                        ->whereNotIn('contract_id', $validContractIds)
                        ->delete();
                    
                    $this->info("   ✓ Deleted {$deleted} orphaned contract comments");
                } else {
                    $this->comment('   Skipped');
                }
            } else {
                $this->info('   ✓ No orphaned contract comments found');
            }

            $this->newLine();
        }

        // ===================================
        // 7. CONTRACT HISTORIES
        // ===================================
        if (DB::getSchemaBuilder()->hasTable('contract_histories')) {
            $this->info('📋 Checking contract_histories...');
            
            $orphanedHistories = DB::table('contract_histories')
                ->whereNotIn('contract_id', $validContractIds)
                ->get();

            if ($orphanedHistories->count() > 0) {
                $this->warn("   Found {$orphanedHistories->count()} orphaned contract histories");
                
                if ($this->option('force') || $this->confirm('   Delete these orphaned contract histories?', true)) {
                    $deleted = DB::table('contract_histories')
                        ->whereNotIn('contract_id', $validContractIds)
                        ->delete();
                    
                    $this->info("   ✓ Deleted {$deleted} orphaned contract histories");
                } else {
                    $this->comment('   Skipped');
                }
            } else {
                $this->info('   ✓ No orphaned contract histories found');
            }

            $this->newLine();
        }

        // ===================================
        // SUMMARY
        // ===================================
        $this->newLine();
        $this->info('═══════════════════════════════════════════');
        $this->info('✓ Cleanup completed!');
        $this->info('═══════════════════════════════════════════');
        
        $this->newLine();
        $this->comment('Run this command regularly to maintain database integrity:');
        $this->line('  php artisan cleanup:orphaned-records');
        $this->line('  php artisan cleanup:orphaned-records --force');
        
        return 0;
    }
}