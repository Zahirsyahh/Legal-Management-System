<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MigrateHrmsLetters extends Command
{
    protected $signature   = 'migrate:hrms-letters
                                {--dry-run : Simulasi tanpa menyimpan}
                                {--id=     : Test satu record saja}';
    protected $description = 'Migrasi data dari tbl_surat_keluar_legal ke contracts';

    public function handle(): int
    {
        $isDryRun = $this->option('dry-run');
        $singleId = $this->option('id');

        if ($isDryRun) {
            $this->warn('=== DRY RUN — tidak ada yang disimpan ===');
        }

        $query = DB::table('tbl_surat_keluar_legal')->orderBy('id');
        if ($singleId) {
            $query->where('id', $singleId);
        }

        $records = $query->get();
        $this->info("Total data HRMS: {$records->count()}");

        $success = 0;
        $skipped = 0;
        $failed  = 0;
        $errors  = [];

        $bar = $this->output->createProgressBar($records->count());
        $bar->start();

        foreach ($records as $hrms) {
            try {
                // Skip jika sudah pernah dimigrasi
                $exists = DB::table('contracts')
                    ->where('hrms_reference_id', $hrms->id)
                    ->exists();

                if ($exists) {
                    $skipped++;
                    $bar->advance();
                    continue;
                }

                // Resolve user_id dari created_by
                $userId = DB::table('tbl_user')
                    ->where('id_user', $hrms->created_by)
                    ->value('id_user');

                if (!$userId) {
                    $errors[] = "HRMS ID {$hrms->id}: created_by={$hrms->created_by} tidak ada di tbl_user";
                    $failed++;
                    $bar->advance();
                    continue;
                }

                // Resolve approved_by
                $approvedBy = null;
                if ($hrms->approved_by) {
                    $approvedBy = DB::table('tbl_user')
                        ->where('id_user', $hrms->approved_by)
                        ->value('id_user');
                }

                $contractData = [
                    'hrms_reference_id'       => $hrms->id,
                    'title'                   => strtoupper($hrms->title),
                    'department_code'         => strtoupper(trim($hrms->department)),
                    'contract_number'         => $hrms->nomor_surat_keluar ?: null,
                    'description'             => $hrms->kualifikasi_surat,
                    'effective_date'          => $hrms->tanggal_surat,
                    'contract_type'           => 'surat',
                    'workflow_type'           => 'static',
                    'allow_stage_addition'    => false,
                    'current_stage'           => 0,
                    'status'                  => $this->mapStatus($hrms->status_surat, $hrms->nomor_surat_keluar),
                    'review_flow_status'      => $this->mapReviewFlowStatus($hrms->status_surat),
                    'multi_department_status' => 'single_department',
                    'surat_file_path'         => $hrms->file ?: null,
                    'user_id'                 => $userId,
                    'final_approved_by'       => $approvedBy,
                    'final_approved_at'       => $hrms->approved_at,
                    'number_issued_at'        => $hrms->approved_at,
                    'submitted_at'            => $hrms->created_at,
                    'legal_notes'             => $hrms->reject_reason,
                    'created_at'              => $hrms->created_at,
                    'updated_at'              => now(),
                ];

                if ($isDryRun) {
                    if ($success < 5) {
                        $this->newLine();
                        $this->line("Preview HRMS ID {$hrms->id}:");
                        $this->line("  title       : {$contractData['title']}");
                        $this->line("  dept_code   : {$contractData['department_code']}");
                        $this->line("  status      : {$contractData['status']}");
                        $this->line("  user_id     : {$contractData['user_id']}");
                        $this->line("  nomor_surat : {$contractData['contract_number']}");
                    }
                } else {
                    DB::beginTransaction();

                    $contractId = DB::table('contracts')->insertGetId($contractData);

                    DB::table('contract_review_logs')->insert([
                        'contract_id' => $contractId,
                        'user_id'     => $userId,
                        'action'      => 'migrated_from_hrms',
                        'description' => "Dimigrasi dari HRMS (ID asal: {$hrms->id})",
                        'notes'       => "Status HRMS: {$hrms->status_surat}",
                        'metadata'    => json_encode([
                            'hrms_id'          => $hrms->id,
                            'hrms_status'      => $hrms->status_surat,
                            'nomor_surat_lama' => $hrms->nomor_surat_keluar,
                            'migrated_at'      => now()->toISOString(),
                        ]),
                        'created_at'  => now(),
                        'updated_at'  => now(),
                    ]);

                    DB::commit();
                }

                $success++;

            } catch (\Exception $e) {
                DB::rollBack();
                $failed++;
                $errors[] = "HRMS ID {$hrms->id}: " . $e->getMessage();
                Log::error('MigrateHrmsLetters error', [
                    'hrms_id' => $hrms->id,
                    'error'   => $e->getMessage(),
                ]);
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);
        $this->info("✅ Berhasil : {$success}");
        $this->warn("⏭️  Di-skip  : {$skipped}");
        if ($failed > 0) {
            $this->error("❌ Gagal    : {$failed}");
            foreach ($errors as $e) {
                $this->line("  - {$e}");
            }
        }

        return 0;
    }

    private function mapStatus(string $hrmsStatus, ?string $nomor): string
    {
        return match($hrmsStatus) {
            'DRAFT'     => 'draft',
            'APPROVED'  => $nomor ? 'number_issued' : 'final_approved',
            'REJECTED'  => 'declined',
            'COMPLETED' => 'released',
            default     => 'draft',
        };
    }

    private function mapReviewFlowStatus(string $hrmsStatus): string
    {
        return match($hrmsStatus) {
            'APPROVED', 'COMPLETED' => 'completed',
            'REJECTED'              => 'rejected',
            default                 => 'pending_assignment',
        };
    }
}