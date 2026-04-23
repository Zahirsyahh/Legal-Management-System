<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\BroadcastMessage;

class WorkflowUpdatedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $contract;
    protected $updatedBy;
    protected $changes;
    // 'owner' = notif ke pemilik dokumen, 'reviewer' = notif ke reviewer baru
    protected string $recipientRole;
    protected ?string $stageName;

    /**
     * @param $contract
     * @param $updatedBy        TblUser yang melakukan edit workflow
     * @param array  $changes   Array deskripsi perubahan (opsional, untuk owner)
     * @param string $recipientRole  'owner' | 'reviewer'
     * @param string|null $stageName Nama stage yang di-assign (untuk reviewer)
     */
    public function __construct(
        $contract,
        $updatedBy,
        array $changes = [],
        string $recipientRole = 'owner',
        ?string $stageName = null
    ) {
        $this->contract       = $contract;
        $this->updatedBy      = $updatedBy;
        $this->changes        = $changes;
        $this->recipientRole  = $recipientRole;
        $this->stageName      = $stageName;
    }

    public function via($notifiable)
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

    public function toMail($notifiable)
    {
        $type       = $this->contract->contract_type ?? 'contract';
        $url        = $this->resolveUrl();
        $isOwner    = $this->recipientRole === 'owner';

        $badgeBg    = $type === 'surat' ? '#e6f9f0' : '#e8f1ff';
        $badgeText  = $type === 'surat' ? '#0f9d58' : '#2563eb';
        $badgeLabel = strtoupper($type);

        if ($isOwner) {
            $headline      = 'Review Workflow Updated';
            $headlineColor = '#d97706';
            $intro         = 'The review workflow for your document has been updated by '
                . '<strong>' . ($this->updatedBy->nama_user ?? 'Legal/Admin') . '</strong>.';

            // Build changes list
            $changesHtml = '';
            if (!empty($this->changes)) {
                $changesHtml = "<p><strong>Changes Made:</strong></p><ul style='padding-left:20px;color:#374151;font-size:14px;'>";
                foreach ($this->changes as $change) {
                    $changesHtml .= '<li style="margin-bottom:6px;">' . e($change) . '</li>';
                }
                $changesHtml .= '</ul>';
            }

            $bodyExtra = $changesHtml;
            $ctaLabel  = 'View Document';
            $ctaColor  = '#d97706';

        } else {
            // reviewer baru
            $headline      = 'You Have Been Added to a Review Stage';
            $headlineColor = '#1d4ed8';
            $intro         = 'You have been assigned as a reviewer for the document below.';

            $stageInfo = $this->stageName
                ? "<p><strong>Your Stage</strong><br>" . e($this->stageName) . "</p>"
                : '';
            $bodyExtra = $stageInfo;
            $ctaLabel  = 'View & Start Review';
            $ctaColor  = '#1d4ed8';
        }

        $content = "
        <div style='font-family:Arial,Helvetica,sans-serif;background:#f4f6f9;padding:40px 20px;'>
            <div style='max-width:640px;'>
                <div style='background:#ffffff;padding:32px;border-radius:8px;'>

                    <h2 style='margin:0 0 20px 0;font-size:20px;color:{$headlineColor};'>
                        {$headline}
                    </h2>

                    <span style='
                        display:inline-block;
                        padding:6px 12px;
                        font-size:12px;
                        font-weight:600;
                        border-radius:20px;
                        background:{$badgeBg};
                        color:{$badgeText};
                        margin-bottom:20px;
                    '>
                        {$badgeLabel}
                    </span>

                    <p>Hello <strong>{$notifiable->nama_user}</strong>,</p>
                    <p>{$intro}</p>

                    <hr style='border:none;border-top:1px solid #e5e7eb;margin:20px 0;'>

                    <p><strong>Document Title</strong><br>{$this->contract->title}</p>

                    <p><strong>Document Number</strong><br>"
                        . ($this->contract->contract_number ?? 'Not yet assigned') . "</p>

                    <p><strong>Updated By</strong><br>"
                        . ($this->updatedBy->nama_user ?? 'System') . "</p>

                    <p><strong>Date</strong><br>" . now()->format('d M Y H:i') . "</p>

                    {$bodyExtra}

                    <div style='margin-top:28px;'>
                        <a href='{$url}'
                           style='
                                display:inline-block;
                                padding:10px 18px;
                                background:{$ctaColor};
                                color:#ffffff;
                                text-decoration:none;
                                border-radius:6px;
                                font-size:14px;
                           '>
                            {$ctaLabel}
                        </a>
                    </div>

                </div>

                <div style='margin-top:20px;font-size:12px;color:#94a3b8;'>
                    Legal Management System
                </div>
            </div>
        </div>
        ";

        $subject = $isOwner
            ? "[{$badgeLabel}] Workflow Updated — {$this->contract->title}"
            : "[{$badgeLabel}] You Are Added as Reviewer — {$this->contract->title}";

        return (new MailMessage)
            ->subject($subject)
            ->view('emails.review-assignment', ['content' => $content]);
    }

    public function toArray($notifiable)
    {
        $isOwner = $this->recipientRole === 'owner';

        return [
            'type'            => 'workflow_updated',
            'title'           => $isOwner
                ? 'Review Workflow Updated'
                : 'You Have Been Added as a Reviewer',
            'message'         => $isOwner
                ? 'The review workflow for "' . $this->contract->title . '" has been updated by '
                    . ($this->updatedBy->nama_user ?? 'Legal/Admin') . '.'
                : 'You have been assigned to review: ' . $this->contract->title
                    . ($this->stageName ? ' (' . $this->stageName . ')' : ''),
            'contract_id'     => $this->contract->id,
            'contract_title'  => $this->contract->title,
            'contract_number' => $this->contract->contract_number ?? 'N/A',
            'contract_type'   => $this->contract->contract_type,
            'updated_by'      => $this->updatedBy->nama_user ?? 'System',
            'changes'         => $this->changes,
            'recipient_role'  => $this->recipientRole,
            'stage_name'      => $this->stageName,
            'updated_at'      => now()->toDateTimeString(),
            'action_url'      => $this->resolveUrl(),
            'icon'            => $isOwner ? 'fa-sync-alt' : 'fa-user-plus',
            'color'           => $isOwner ? 'yellow' : 'blue',
        ];
    }

    public function toBroadcast($notifiable)
    {
        $isOwner = $this->recipientRole === 'owner';

        return new BroadcastMessage([
            'id' => $this->id,
            'type' => 'workflow_updated',
            'title' => $isOwner
                ? 'Review Workflow Updated'
                : 'You Have Been Added as a Reviewer',
            'message' => $isOwner
                ? 'The review workflow for "' . $this->contract->title . '" has been updated by '
                    . ($this->updatedBy->nama_user ?? 'Legal/Admin') . '.'
                : 'You have been assigned to review: ' . $this->contract->title
                    . ($this->stageName ? ' (' . $this->stageName . ')' : ''),
            'contract_id' => $this->contract->id,
            'contract_title' => $this->contract->title,
            'contract_number' => $this->contract->contract_number ?? 'N/A',
            'contract_type' => $this->contract->contract_type,
            'updated_by' => $this->updatedBy->nama_user ?? 'System',
            'changes' => $this->changes,
            'recipient_role' => $this->recipientRole,
            'stage_name' => $this->stageName,
            'updated_at' => now()->toDateTimeString(),
            'action_url' => $this->resolveUrl(),
            'icon' => $isOwner ? 'fa-sync-alt' : 'fa-user-plus',
            'color' => $isOwner ? 'yellow' : 'blue',
            'timestamp' => now()->toDateTimeString(),
        ]);
    }
}