<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * StageJumpedNotification
 *
 * Dikirim ke document owner / pihak terkait ketika dokumen berpindah stage.
 * Mencakup semua jenis transisi:
 *  - Legal stage → Legal stage berikutnya
 *  - Legal stage → Substantial (Parallel) Review dimulai
 *  - Substantial Review selesai → stage berikutnya
 *  - Stage terakhir selesai → Executing aktif
 *  - Executing selesai → Archiving aktif
 *
 * Constructor:
 *   $contract        — model Contract
 *   $fromStage       — ContractReviewStage asal (opsional)
 *   $toStage         — ContractReviewStage tujuan (opsional)
 *   $jumpedBy        — TblUser yang melakukan transisi
 *   $transitionType  — 'sequential'|'parallel_started'|'parallel_completed'
 *                      |'executing_activated'|'archiving_activated'
 *                      (default: 'sequential')
 *   $contextNote     — catatan tambahan untuk isi email (opsional)
 */
class StageJumpedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $contract;
    protected $fromStage;
    protected $toStage;
    protected $jumpedBy;
    protected string $transitionType;
    protected string $contextNote;

    public function __construct(
        $contract,
        $fromStage,
        $toStage,
        $jumpedBy,
        string $transitionType = 'sequential',
        string $contextNote    = ''
    ) {
        $this->contract       = $contract;
        $this->fromStage      = $fromStage;
        $this->toStage        = $toStage;
        $this->jumpedBy       = $jumpedBy;
        $this->transitionType = $transitionType;
        $this->contextNote    = $contextNote;
    }

    public function via(object $notifiable): array
    {
        return ['database', 'broadcast', 'mail'];
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

    // ── Helpers ────────────────────────────────────────────────────────────

    private function headline(): string
    {
        return match ($this->transitionType) {
            'parallel_started'    => '⚡ Substantial Review Started',
            'parallel_completed'  => '✅ Substantial Review Completed',
            'executing_activated' => '✍️ Document Ready for Signing',
            'archiving_activated' => '🗂️ Archiving Process Started',
            default               => '📋 Review Stage Updated',
        };
    }

    private function headlineColor(): string
    {
        return match ($this->transitionType) {
            'parallel_started'    => '#7c3aed',
            'parallel_completed'  => '#059669',
            'executing_activated' => '#1d4ed8',
            'archiving_activated' => '#0891b2',
            default               => '#0891b2',
        };
    }

    private function introLine(string $notifiableName): string
    {
        $from = $this->fromStage
            ? ucfirst(str_replace('_', ' ', $this->fromStage->stage_name))
            : 'a previous stage';

        $to = $this->toStage
            ? ucfirst(str_replace('_', ' ', $this->toStage->stage_name))
            : 'the next stage';

        return match ($this->transitionType) {
            'parallel_started'    =>
                "The substantial review (parallel) phase has begun for your document.
                 All selected departments are now reviewing simultaneously.",
            'parallel_completed'  =>
                "All departments have completed their substantial review.
                 The document is now moving to the next stage.",
            'executing_activated' =>
                "All reviews are complete. The document is now ready for
                 official signing (<strong>{$to}</strong>).",
            'archiving_activated' =>
                "The document has been executed and is now entering the archiving phase
                 (<strong>{$to}</strong>).",
            default               =>
                "Your document has moved from <strong>{$from}</strong>
                 to <strong>{$to}</strong>.",
        };
    }

    // ── Mail ──────────────────────────────────────────────────────────────

    public function toMail(object $notifiable): MailMessage
    {
        $type    = $this->contract->contract_type ?? 'contract';
        $url     = $this->resolveUrl();

        $badgeBg    = $type === 'surat' ? '#e6f9f0' : '#e8f1ff';
        $badgeText  = $type === 'surat' ? '#0f9d58' : '#2563eb';
        $badgeLabel = strtoupper($type);

        $headline      = $this->headline();
        $headlineColor = $this->headlineColor();
        $intro         = $this->introLine($notifiable->nama_user);

        $fromName = $this->fromStage
            ? ucfirst(str_replace('_', ' ', $this->fromStage->stage_name))
            : '—';

        $toName = $this->toStage
            ? ucfirst(str_replace('_', ' ', $this->toStage->stage_name))
            : '—';

        // ── Previous reviewer notes ───────────────────────────────────────
        $prevNoteSection = '';
        $prevNote        = $this->fromStage?->notes ?? null;
        if (!empty($prevNote)) {
            $prevNoteSection = "
                <div style='margin-top:20px;padding:14px 16px;background:#f8fafc;
                            border-left:4px solid #0891b2;border-radius:6px;
                            font-size:13px;line-height:1.65;'>
                    <strong style='color:#0891b2;'>Reviewer Notes</strong>
                    <p style='margin:8px 0 0;color:#374151;'>"
                    . nl2br(e($prevNote))
                . "</p>
                </div>";
        }

        // ── Extra context note ────────────────────────────────────────────
        $contextSection = '';
        if (!empty($this->contextNote)) {
            $contextSection = "
                <div style='margin-top:16px;padding:12px 16px;background:#eff6ff;
                            border-left:4px solid #3b82f6;border-radius:4px;
                            font-size:13px;color:#1e40af;'>
                    " . nl2br(e($this->contextNote)) . "
                </div>";
        }

        $content = "
        <div style='font-family:Arial,Helvetica,sans-serif;background:#f4f6f9;padding:40px 20px;'>
            <div style='max-width:640px;'>
                <div style='background:#ffffff;padding:32px;border-radius:8px;'>

                    <h2 style='margin:0 0 20px 0;font-size:20px;color:{$headlineColor};'>
                        {$headline}
                    </h2>

                    <span style='display:inline-block;padding:6px 12px;font-size:12px;
                                 font-weight:600;border-radius:20px;
                                 background:{$badgeBg};color:{$badgeText};margin-bottom:20px;'>
                        {$badgeLabel}
                    </span>

                    <p>Hello <strong>{$notifiable->nama_user}</strong>,</p>
                    <p>{$intro}</p>

                    <hr style='border:none;border-top:1px solid #e5e7eb;margin:20px 0;'>

                    <p><strong>Document Title</strong><br>{$this->contract->title}</p>

                    <p><strong>Document Number</strong><br>"
                        . ($this->contract->contract_number ?? 'Not yet assigned') . "</p>

                    <p><strong>Previous Stage</strong><br>{$fromName}</p>

                    <p><strong>Current Stage</strong><br>{$toName}</p>

                    <p><strong>Processed By</strong><br>{$this->jumpedBy->nama_user}</p>

                    <p><strong>Date</strong><br>" . now()->format('d M Y H:i') . "</p>

                    {$prevNoteSection}
                    {$contextSection}

                    <div style='margin-top:28px;'>
                        <a href='{$url}'
                           style='display:inline-block;padding:10px 18px;
                                  background:#111827;color:#ffffff;
                                  text-decoration:none;border-radius:6px;font-size:14px;'>
                            View Document
                        </a>
                    </div>

                </div>
                <div style='margin-top:20px;font-size:12px;color:#94a3b8;'>
                    Legal Management System
                </div>
            </div>
        </div>";

        return (new MailMessage)
            ->subject("[{$badgeLabel}] {$this->headline()} — {$this->contract->title}")
            ->view('emails.review-assignment', ['content' => $content]);
    }

    public function toArray(object $notifiable): array
    {
        $fromName = $this->fromStage
            ? str_replace('_', ' ', $this->fromStage->stage_name)
            : 'previous';

        $toName = $this->toStage
            ? str_replace('_', ' ', $this->toStage->stage_name)
            : 'next';

        return [
            'type'            => 'stage_jumped',
            'title'           => $this->headline(),
            'message'         => 'Document moved from ' . ucfirst($fromName)
                . ' to ' . ucfirst($toName) . '.',
            'contract_id'     => $this->contract->id,
            'contract_title'  => $this->contract->title,
            'contract_number' => $this->contract->contract_number ?? 'N/A',
            'from_stage'      => ucfirst($fromName),
            'to_stage'        => ucfirst($toName),
            'transition_type' => $this->transitionType,
            'jumped_by'       => $this->jumpedBy->nama_user,
            'jumped_at'       => now()->toDateTimeString(),
            'action_url'      => $this->resolveUrl(),
            'icon'            => match ($this->transitionType) {
                'parallel_started'    => 'fa-bolt',
                'parallel_completed'  => 'fa-check-double',
                'executing_activated' => 'fa-pen',
                'archiving_activated' => 'fa-archive',
                default               => 'fa-arrow-right',
            },
            'color' => match ($this->transitionType) {
                'parallel_started'    => 'purple',
                'parallel_completed'  => 'green',
                'executing_activated' => 'blue',
                'archiving_activated' => 'cyan',
                default               => 'cyan',
            },
        ];
    }

    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        $toName = $this->toStage
            ? str_replace('_', ' ', $this->toStage->stage_name)
            : 'next';

        return new BroadcastMessage([
            'id'              => $this->id,
            'type'            => 'stage_jumped',
            'title'           => $this->headline(),
            'message'         => 'Document moved to ' . ucfirst($toName),
            'contract_id'     => $this->contract->id,
            'contract_title'  => $this->contract->title,
            'to_stage'        => ucfirst($toName),
            'transition_type' => $this->transitionType,
            'jumped_by'       => $this->jumpedBy->nama_user,
            'timestamp'       => now()->toDateTimeString(),
            'icon'            => 'fa-arrow-right',
            'color'           => 'cyan',
        ]);
    }
}