<?php

namespace App\Console\Commands;

use App\Models\Contract;
use App\Models\TblUser;
use App\Notifications\DeadlineReminderNotification;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

/**
 * SendDeadlineReminders
 *
 * Dijalankan via scheduler setiap hari (pagi).
 * Mencari contract yang deadlinenya tepat 3 atau 1 hari dari sekarang.
 *
 * Menggunakan kolom tracking (deadline_reminder_3d_sent_at /
 * deadline_reminder_1d_sent_at) untuk mencegah duplikasi notifikasi
 * bila command dijalankan lebih dari sekali dalam sehari.
 *
 * Usage:
 *   php artisan contracts:send-deadline-reminders
 *   php artisan contracts:send-deadline-reminders --dry-run
 *   php artisan contracts:send-deadline-reminders --force   (abaikan guard, kirim ulang)
 */
class SendDeadlineReminders extends Command
{
    protected $signature = 'contracts:send-deadline-reminders
                            {--dry-run : Show what would be sent without actually sending}
                            {--force   : Ignore sent-at guard and resend}';

    protected $description = 'Send deadline reminder notifications to reviewers (3 days and 1 day before drafting_deadline)';

    protected array $activeStatuses = [
        Contract::STATUS_SUBMITTED,
        Contract::STATUS_UNDER_REVIEW,
        Contract::STATUS_REVISION_NEEDED,
        Contract::STATUS_LEGAL_REVIEWING,
        Contract::STATUS_FINANCE_REVIEWING,
        Contract::STATUS_ACCOUNTING_REVIEWING,
        Contract::STATUS_TAX_REVIEWING,
        Contract::STATUS_LEGAL_APPROVED,
        Contract::STATUS_FINANCE_APPROVED,
        Contract::STATUS_ACCOUNTING_APPROVED,
        Contract::STATUS_TAX_APPROVED,
        'document_uploaded',
        'user_reviewing',
        'user_review_complete',
        'legal_reviewing_feedback',
    ];

    // Map: daysLeft => kolom tracking di tabel contracts
    protected array $trackingColumns = [
        3 => 'deadline_reminder_3d_sent_at',
        1 => 'deadline_reminder_1d_sent_at',
    ];

    public function handle(): int
    {
        $isDryRun = $this->option('dry-run');
        $isForce  = $this->option('force');
        $today    = Carbon::today();

        $this->info('=== Deadline Reminder Command ===');
        $this->info("Date  : {$today->toDateString()}");
        $this->info('Mode  : ' . ($isDryRun ? 'DRY RUN' : ($isForce ? 'FORCE SEND' : 'NORMAL')));
        $this->newLine();

        $totalSent = 0;

        foreach ([3, 1] as $daysLeft) {
            $targetDate  = $today->copy()->addDays($daysLeft);
            $trackingCol = $this->trackingColumns[$daysLeft];

            $this->info("Checking deadline = {$targetDate->toDateString()} ({$daysLeft} day(s) ahead)...");

            $query = Contract::whereDate('drafting_deadline', $targetDate->toDateString())
                ->whereIn('status', $this->activeStatuses)
                ->whereNotNull('drafting_deadline')
                ->with([
                    'user:id_user,nama_user,email',
                    'legalAssigned:id_user,nama_user,email',
                    'reviewStages' => function ($q) {
                        $q->with('assignedUser:id_user,nama_user,email')
                          ->whereIn('status', ['pending', 'assigned', 'in_progress'])
                          ->whereNotIn('stage_type', ['user']);
                    },
                ]);

            // Guard: skip yang sudah dikirim hari ini (kecuali --force)
            if (!$isForce) {
                $query->where(function ($q) use ($trackingCol, $today) {
                    $q->whereNull($trackingCol)
                      ->orWhereDate($trackingCol, '!=', $today->toDateString());
                });
            }

            $contracts = $query->get();

            $this->line("  Found {$contracts->count()} contract(s) to process.");

            foreach ($contracts as $contract) {
                $recipients = $this->resolveRecipients($contract);

                if ($recipients->isEmpty()) {
                    $this->line("  [SKIP] #{$contract->id} \"{$contract->title}\" — no reviewers found.");
                    continue;
                }

                $names = $recipients->pluck('nama_user')->join(', ');
                $this->line("  [SEND] #{$contract->id} \"{$contract->title}\" → {$recipients->count()} recipient(s): {$names}");

                if (!$isDryRun) {
                    try {
                        foreach ($recipients as $recipient) {
                            $recipient->notify(
                                new DeadlineReminderNotification($contract, $daysLeft)
                            );
                        }

                        // Tandai sudah dikirim hari ini
                        $contract->update([$trackingCol => now()]);

                        Log::info('DeadlineReminder sent', [
                            'contract_id'     => $contract->id,
                            'contract_title'  => $contract->title,
                            'days_left'       => $daysLeft,
                            'deadline'        => $contract->drafting_deadline,
                            'recipient_ids'   => $recipients->pluck('id_user')->toArray(),
                            'recipient_names' => $recipients->pluck('nama_user')->toArray(),
                        ]);

                        $totalSent += $recipients->count();

                    } catch (\Exception $e) {
                        $this->error("  [ERROR] Contract #{$contract->id}: {$e->getMessage()}");

                        Log::error('DeadlineReminder failed', [
                            'contract_id' => $contract->id,
                            'error'       => $e->getMessage(),
                        ]);
                    }
                } else {
                    $totalSent += $recipients->count();
                }
            }

            $this->newLine();
        }

        $verb = $isDryRun ? 'would be sent' : 'sent';
        $this->info("=== Done. Total notifications {$verb}: {$totalSent} ===");

        return Command::SUCCESS;
    }

    /**
     * Kumpulkan semua penerima notifikasi untuk satu contract.
     *
     * Termasuk:
     *   - Legal / Finance / Accounting / Tax yang di-assign
     *   - Semua assigned_user_id di review stages yang masih aktif
     *
     * Di-exclude:
     *   - Document owner (user_id) — mereka sudah tahu deadline sendiri
     */
    protected function resolveRecipients(Contract $contract): \Illuminate\Support\Collection
    {
        $recipientIds = collect();

        foreach (['legal_assigned_id', 'finance_assigned_id', 'accounting_assigned_id', 'tax_assigned_id'] as $field) {
            if ($contract->$field) {
                $recipientIds->push($contract->$field);
            }
        }

        foreach ($contract->reviewStages as $stage) {
            if ($stage->assigned_user_id) {
                $recipientIds->push($stage->assigned_user_id);
            }
        }

        $recipientIds = $recipientIds
            ->unique()
            ->filter(fn($id) => (int) $id !== (int) $contract->user_id)
            ->values();

        if ($recipientIds->isEmpty()) {
            return collect();
        }

        return TblUser::whereIn('id_user', $recipientIds)->get();
    }
}