<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * FinishedReviewNotification
 *
 * Dikirim ke seluruh reviewer yang terlibat + contract owner
 * ketika dokumen mencapai status 'archived' (tahap final).
 *
 * Constructor:
 *   $contract    — model Contract
 *   $archivedBy  — TblUser legal yang melakukan archiving
 *   $recipientRole — 'owner' | 'reviewer' (untuk menyesuaikan isi email)
 */
class FinishedReviewNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $contract;
    protected $archivedBy;
    protected string $recipientRole;

    public function __construct($contract, $archivedBy = null, string $recipientRole = 'owner')
    {
        $this->contract      = $contract;
        $this->archivedBy    = $archivedBy;
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
        $type       = $this->contract->contract_type ?? 'contract';
        $url        = $this->resolveUrl();
        $isOwner    = $this->recipientRole === 'owner';

        $badgeBg    = $type === 'surat' ? '#e6f9f0' : '#e8f1ff';
        $badgeText  = $type === 'surat' ? '#0f9d58' : '#2563eb';
        $badgeLabel = strtoupper($type);

        $intro = $isOwner
            ? 'Congratulations! Your document has completed the entire review process and has been officially archived.'
            : 'The document you were involved in reviewing has completed the entire process and has been officially archived.';

        $ownerNote = $isOwner
            ? "<div style='margin-top:16px;padding:12px 16px;background:#f0fdf4;
                           border-left:4px solid #16a34a;border-radius:4px;
                           font-size:13px;color:#166534;'>
                   <strong>What this means:</strong> Your document now has an official number
                   and is safely stored in Legal archive. No further action is required.
               </div>"
            : "<div style='margin-top:16px;padding:12px 16px;background:#f8fafc;
                           border-left:4px solid #94a3b8;border-radius:4px;
                           font-size:13px;color:#64748b;'>
                   Thank you for your contribution to this review process.
                   No further action is required from you.
               </div>";

        $content = "
        <div style='font-family:Arial,Helvetica,sans-serif;background:#f4f6f9;padding:40px 20px;'>
            <div style='max-width:640px;'>
                <div style='background:#ffffff;padding:32px;border-radius:8px;'>

                    <h2 style='margin:0 0 20px 0;font-size:20px;color:#16a34a;'>
                        🎉 Review Process Completed
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

                    <p><strong>Official Document Number</strong><br>
                        <span style='font-family:monospace;font-weight:bold;font-size:15px;color:#16a34a;'>
                            " . ($this->contract->contract_number ?? 'N/A') . "
                        </span>
                    </p>

                    <p><strong>Archived By</strong><br>"
                        . ($this->archivedBy?->nama_user ?? 'Legal Team') . "</p>

                    <p><strong>Archived At</strong><br>"
                        . now()->format('d M Y H:i') . "</p>

                    {$ownerNote}

                </div>
                <div style='margin-top:20px;font-size:12px;color:#94a3b8;'>
                    This document has been officially archived and the review process is complete.<br>
                    Legal Management System
                </div>
            </div>
        </div>";

        $subject = $isOwner
            ? "[{$badgeLabel}] ✅ Your Document is Archived — {$this->contract->title}"
            : "[{$badgeLabel}] Review Complete & Archived — {$this->contract->title}";

        return (new MailMessage)
            ->subject($subject)
            ->view('emails.review-assignment', ['content' => $content]);
    }

    public function toArray($notifiable): array
    {
        return [
            'type'            => 'review_finished',
            'title'           => 'Review Process Completed',
            'message'         => 'Document "' . $this->contract->title
                . '" (' . ($this->contract->contract_number ?? 'N/A')
                . ') has been archived. All review processes are complete.',
            'contract_id'     => $this->contract->id,
            'contract_title'  => $this->contract->title,
            'contract_number' => $this->contract->contract_number ?? 'N/A',
            'contract_type'   => $this->contract->contract_type,
            'archived_by'     => $this->archivedBy?->nama_user ?? 'Legal Team',
            'recipient_role'  => $this->recipientRole,
            'archived_at'     => now()->toDateTimeString(),
            'action_url'      => $this->resolveUrl(),
            'icon'            => 'fa-archive',
            'color'           => 'green',
        ];
    }

    public function toBroadcast($notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'id'              => $this->id,
            'type'            => 'review_finished',
            'title'           => 'Review Process Completed',
            'message'         => '"' . $this->contract->title . '" has been archived.',
            'contract_id'     => $this->contract->id,
            'contract_title'  => $this->contract->title,
            'contract_number' => $this->contract->contract_number ?? 'N/A',
            'archived_by'     => $this->archivedBy?->nama_user ?? 'Legal Team',
            'timestamp'       => now()->toDateTimeString(),
            'icon'            => 'fa-archive',
            'color'           => 'green',
            'action_url'      => $this->resolveUrl(),
        ]);
    }
}