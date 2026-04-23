<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * ContractRejectedNotification
 *
 * Dikirim ke:
 *  - Document owner
 *  - Semua reviewer yang pernah terlibat di workflow (completed/in_progress/assigned)
 *
 * Constructor:
 *   $contract    — model Contract
 *   $stage       — ContractReviewStage tempat reject dilakukan
 *   $reason      — string alasan penolakan
 *   $rejectedBy  — TblUser yang melakukan reject
 *   $recipientRole — 'owner' | 'reviewer' untuk menyesuaikan isi email
 */
class ContractRejectedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $contract;
    protected $stage;
    protected string $reason;
    protected $rejectedBy;
    protected string $recipientRole;

    public function __construct(
        $contract,
        $stage,
        string $reason,
        $rejectedBy    = null,
        string $recipientRole = 'owner'
    ) {
        $this->contract      = $contract;
        $this->stage         = $stage;
        $this->reason        = $reason;
        $this->rejectedBy    = $rejectedBy;
        $this->recipientRole = $recipientRole;
    }

    public function via($notifiable): array
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

    public function toMail($notifiable): MailMessage
    {
        $type      = $this->contract->contract_type ?? 'contract';
        $url       = $this->resolveUrl();
        $stageName = $this->stage
            ? str_replace('_', ' ', $this->stage->stage_name)
            : 'review';

        $badgeBg    = $type === 'surat' ? '#e6f9f0' : '#e8f1ff';
        $badgeText  = $type === 'surat' ? '#0f9d58' : '#2563eb';
        $badgeLabel = strtoupper($type);

        $isOwner = $this->recipientRole === 'owner';

        $intro = $isOwner
            ? 'Unfortunately, your document has been rejected during the review process.'
            : 'A document you were involved in reviewing has been rejected.';

        $nextStep = $isOwner
            ? "<div style='margin-top:16px;padding:12px 16px;background:#fef2f2;
                           border-left:4px solid #b91c1c;border-radius:4px;
                           font-size:13px;color:#7f1d1d;'>
                   <strong>Next Step:</strong> Please review the rejection reason.
                   You may revise and resubmit the document after consulting with Legal.
               </div>"
            : "<div style='margin-top:16px;padding:12px 16px;background:#f9fafb;
                           border-left:4px solid #9ca3af;border-radius:4px;
                           font-size:13px;color:#6b7280;'>
                   No further action is required from you.
                   The document owner has been notified separately.
               </div>";

        $content = "
        <div style='font-family:Arial,Helvetica,sans-serif;background:#f4f6f9;padding:40px 20px;'>
            <div style='max-width:640px;'>
                <div style='background:#ffffff;padding:32px;border-radius:8px;'>

                    <h2 style='margin:0 0 20px 0;font-size:20px;color:#b91c1c;'>
                        ❌ Document Rejected
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

                    <p><strong>Rejected At Stage</strong><br>" . ucfirst($stageName) . "</p>

                    <p><strong>Rejected By</strong><br>"
                        . ($this->rejectedBy?->nama_user ?? 'System') . "</p>

                    <p><strong>Date</strong><br>" . now()->format('d M Y H:i') . "</p>

                    <p><strong>Reason for Rejection</strong><br>"
                        . nl2br(e($this->reason)) . "</p>

                    {$nextStep}

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
            ->subject("[{$badgeLabel}] Document Rejected — {$this->contract->title}")
            ->view('emails.review-assignment', ['content' => $content]);
    }

    public function toArray($notifiable): array
    {
        $stageName = $this->stage
            ? str_replace('_', ' ', $this->stage->stage_name)
            : 'review';

        return [
            'type'            => 'document_rejected',
            'title'           => 'Document Rejected',
            'message'         => 'Document "' . $this->contract->title
                . '" was rejected at ' . ucfirst($stageName) . ' stage.',
            'contract_id'     => $this->contract->id,
            'contract_title'  => $this->contract->title,
            'contract_number' => $this->contract->contract_number ?? 'N/A',
            'stage'           => ucfirst($stageName),
            'reason'          => $this->reason,
            'rejected_by'     => $this->rejectedBy?->nama_user ?? 'System',
            'recipient_role'  => $this->recipientRole,
            'rejected_at'     => now()->toDateTimeString(),
            'action_url'      => $this->resolveUrl(),
            'icon'            => 'fa-times-circle',
            'color'           => 'red',
        ];
    }

    public function toBroadcast($notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'id'             => $this->id,
            'type'           => 'document_rejected',
            'title'          => 'Document Rejected',
            'message'        => 'Document "' . $this->contract->title . '" was rejected.',
            'contract_id'    => $this->contract->id,
            'contract_title' => $this->contract->title,
            'stage'          => $this->stage
                ? str_replace('_', ' ', $this->stage->stage_name)
                : 'review',
            'rejected_by'    => $this->rejectedBy?->nama_user ?? 'System',
            'timestamp'      => now()->toDateTimeString(),
            'icon'           => 'fa-times-circle',
            'color'          => 'red',
        ]);
    }
}