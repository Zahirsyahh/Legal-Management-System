<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * StageAssignedNotification
 *
 * Dikirim ke reviewer yang baru di-assign ke sebuah stage.
 * Menampilkan nama stage, catatan dari reviewer sebelumnya (jika ada),
 * dan ringkasan seluruh catatan dari semua stage sebelumnya (opsional).
 *
 * Constructor:
 *   $contract    — model Contract
 *   $stage       — ContractReviewStage yang baru di-assign (WAJIB)
 *   $assignedBy  — TblUser yang melakukan assign (opsional)
 *   $fromStage   — ContractReviewStage sebelumnya untuk ambil notes (opsional)
 *   $allPrevNotes — array [ ['stage_name'=>'...', 'reviewer'=>'...', 'notes'=>'...'] ]
 *                   untuk menampilkan seluruh riwayat catatan (opsional)
 *
 * Urutan parameter dibuat agar backward-compatible dengan pemanggilan lama:
 *   new StageAssignedNotification($contract, $stage, $assignedBy)
 */
class StageAssignedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $contract;
    protected $stage;
    protected $assignedBy;
    protected $fromStage;
    protected array $allPrevNotes;

    public function __construct(
        $contract,
        $stage,
        $assignedBy   = null,
        $fromStage    = null,
        array $allPrevNotes = []
    ) {
        $this->contract     = $contract;
        $this->stage        = $stage;
        $this->assignedBy   = $assignedBy;
        $this->fromStage    = $fromStage;
        $this->allPrevNotes = $allPrevNotes;
    }

    public function via(object $notifiable): array
    {
        return ['mail', 'database', 'broadcast'];
    }

    protected function resolveUrl(): string
    {
        return route(
            $this->contract->contract_type === 'surat'
                ? 'surat.show'
                : 'contracts.show',
            $this->contract
        );
    }

    public function toMail(object $notifiable): MailMessage
    {
        $type       = $this->contract->contract_type ?? 'contract';
        $url        = $this->resolveUrl();
        $stageName  = $this->stage
            ? str_replace('_', ' ', $this->stage->stage_name)
            : 'review';

        $badgeBg    = $type === 'surat' ? '#e6f9f0' : '#e8f1ff';
        $badgeText  = $type === 'surat' ? '#0f9d58' : '#2563eb';
        $badgeLabel = strtoupper($type);

        // ── Catatan reviewer sebelumnya (fromStage) ──────────────────────
        $prevNoteSection = '';
        $singlePrevNote  = $this->fromStage->notes ?? null;

        if (!empty($singlePrevNote)) {
            $prevNoteSection = "
                <div style='margin-top:20px;padding:14px 16px;background:#f8fafc;
                            border-left:4px solid #6366f1;border-radius:6px;
                            font-size:13px;line-height:1.65;'>
                    <strong style='color:#4f46e5;'>Previous Reviewer Notes</strong>
                    <p style='margin:8px 0 0;color:#374151;'>"
                    . nl2br(e($singlePrevNote))
                    . "</p>
                </div>";
        }

        // ── Riwayat semua catatan sebelumnya ─────────────────────────────
        $allNotesSection = '';
        if (!empty($this->allPrevNotes)) {
            $rows = '';
            foreach ($this->allPrevNotes as $entry) {
                if (empty($entry['notes'])) continue;
                $rows .= "
                    <div style='border-bottom:1px solid #f1f5f9;padding:10px 0;'>
                        <p style='margin:0 0 4px;font-size:12px;font-weight:600;color:#6366f1;'>
                            " . e($entry['stage_name'] ?? '—') . "
                            <span style='font-weight:400;color:#94a3b8;'>
                                — " . e($entry['reviewer'] ?? '') . "
                            </span>
                        </p>
                        <p style='margin:0;font-size:13px;color:#374151;line-height:1.6;'>"
                            . nl2br(e($entry['notes']))
                        . "</p>
                    </div>";
            }
            if ($rows) {
                $allNotesSection = "
                    <div style='margin-top:20px;padding:14px 16px;background:#fafafa;
                                border:1px solid #e5e7eb;border-radius:8px;'>
                        <p style='margin:0 0 10px;font-size:12px;font-weight:700;
                                  text-transform:uppercase;letter-spacing:.05em;color:#64748b;'>
                            Review History — All Notes
                        </p>
                        {$rows}
                    </div>";
            }
        }

        $content = "
        <div style='font-family:Arial,Helvetica,sans-serif;background:#f4f6f9;padding:40px 20px;'>
            <div style='max-width:640px;'>
                <div style='background:#ffffff;padding:32px;border-radius:8px;'>

                    <h2 style='margin:0 0 20px 0;font-size:20px;color:#111827;'>
                        📋 New Review Assignment
                    </h2>

                    <span style='display:inline-block;padding:6px 12px;font-size:12px;
                                 font-weight:600;border-radius:20px;
                                 background:{$badgeBg};color:{$badgeText};margin-bottom:20px;'>
                        {$badgeLabel}
                    </span>

                    <p>Hello <strong>{$notifiable->nama_user}</strong>,</p>
                    <p>You have been assigned to review the following document.</p>

                    <hr style='border:none;border-top:1px solid #e5e7eb;margin:20px 0;'>

                    <p><strong>Document Title</strong><br>{$this->contract->title}</p>

                    <p><strong>Document Number</strong><br>"
                        . ($this->contract->contract_number ?? 'Not yet assigned') . "</p>

                    <p><strong>Your Stage</strong><br>"
                        . ucfirst($stageName) . "</p>

                    <p><strong>Assigned By</strong><br>"
                        . ($this->assignedBy?->nama_user ?? 'System') . "</p>

                    <p><strong>Assigned At</strong><br>"
                        . now()->format('d M Y H:i') . "</p>

                    {$prevNoteSection}
                    {$allNotesSection}

                    <div style='margin-top:28px;'>
                        <a href='{$url}'
                           style='display:inline-block;padding:10px 18px;
                                  background:#111827;color:#ffffff;
                                  text-decoration:none;border-radius:6px;font-size:14px;'>
                            Start Review
                        </a>
                    </div>

                </div>
                <div style='margin-top:20px;font-size:12px;color:#94a3b8;'>
                    Legal Management System
                </div>
            </div>
        </div>";

        return (new MailMessage)
            ->subject("[{$badgeLabel}] Review Assignment — {$this->contract->title}")
            ->view('emails.review-assignment', ['content' => $content]);
    }

    public function toArray(object $notifiable): array
    {
        $stageName = $this->stage
            ? str_replace('_', ' ', $this->stage->stage_name)
            : 'review';

        return [
            'type'            => 'stage_assigned',
            'title'           => 'New Review Assignment',
            'message'         => 'You have been assigned to review: ' . ucfirst($stageName),
            'contract_id'     => $this->contract->id,
            'contract_title'  => $this->contract->title,
            'contract_number' => $this->contract->contract_number ?? 'N/A',
            'stage_id'        => $this->stage->id ?? null,
            'stage_name'      => ucfirst($stageName),
            'assigned_by'     => $this->assignedBy?->nama_user ?? 'System',
            'assigned_at'     => now()->toDateTimeString(),
            'action_url'      => $this->resolveUrl(),
            'icon'            => 'fa-tasks',
            'color'           => 'indigo',
        ];
    }

    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage($this->toArray($notifiable));
    }
}