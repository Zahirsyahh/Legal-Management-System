<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\BroadcastMessage;

class RevisionTaskApprovedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $contract;
    protected $task;
    protected $approvedBy;

    public function __construct($contract, $task, $approvedBy = null)
    {
        $this->contract   = $contract;
        $this->task       = $task;
        $this->approvedBy = $approvedBy;
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
        $loopCount  = $this->task->loop_count ?? 0;

        $badgeBg    = $type === 'surat' ? '#e6f9f0' : '#e8f1ff';
        $badgeText  = $type === 'surat' ? '#0f9d58' : '#2563eb';
        $badgeLabel = strtoupper($type);

        $loopNote = $loopCount > 0
            ? "<p style='color:#6b7280;font-size:13px;'>This was revision loop #{$loopCount}.</p>"
            : '';

        $content = "
        <div style='font-family:Arial,Helvetica,sans-serif;background:#f4f6f9;padding:40px 20px;'>
            <div style='max-width:640px;'>
                <div style='background:#ffffff;padding:32px;border-radius:8px;'>

                    <h2 style='margin:0 0 20px 0;font-size:20px;color:#059669;'>
                        ✅ Your Revision Has Been Approved
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

                    <p>Great news! Your revision submission has been reviewed and approved.</p>

                    {$loopNote}

                    <hr style='border:none;border-top:1px solid #e5e7eb;margin:20px 0;'>

                    <p><strong>Document Title</strong><br>{$this->contract->title}</p>

                    <p><strong>Document Number</strong><br>"
                        . ($this->contract->contract_number ?? 'Not yet assigned') . "</p>

                    <p><strong>Approved By</strong><br>"
                        . ($this->approvedBy->nama_user ?? 'System') . "</p>

                    <p><strong>Approval Notes</strong><br>"
                        . nl2br(e($this->task->approval_notes ?? '-')) . "</p>

                    <p><strong>Date</strong><br>" . now()->format('d M Y H:i') . "</p>

                    <div style='margin-top:28px;'>
                        <a href='{$url}'
                           style='
                                display:inline-block;
                                padding:10px 18px;
                                background:#059669;
                                color:#ffffff;
                                text-decoration:none;
                                border-radius:6px;
                                font-size:14px;
                           '>
                            View Document
                        </a>
                    </div>

                </div>

                <div style='margin-top:20px;font-size:12px;color:#94a3b8;'>
                    No further action needed for this revision task.<br>
                    Legal Management System
                </div>
            </div>
        </div>
        ";

        return (new MailMessage)
            ->subject("[{$badgeLabel}] Revision Approved — {$this->contract->title}")
            ->view('emails.review-assignment', ['content' => $content]);
    }

    public function toArray($notifiable)
    {
        return [
            'type'            => 'revision_task_approved',
            'title'           => 'Revision Approved',
            'message'         => 'Your revision for "' . $this->contract->title . '" has been approved by '
                . ($this->approvedBy->nama_user ?? 'reviewer') . '.',
            'contract_id'     => $this->contract->id,
            'contract_title'  => $this->contract->title,
            'contract_number' => $this->contract->contract_number ?? 'N/A',
            'contract_type'   => $this->contract->contract_type,
            'task_id'         => $this->task->id,
            'loop_count'      => $this->task->loop_count ?? 0,
            'approved_by'     => $this->approvedBy->nama_user ?? 'System',
            'approved_at'     => now()->toDateTimeString(),
            'action_url'      => $this->resolveUrl(),
            'icon'            => 'fa-check-circle',
            'color'           => 'green',
        ];
    }

    public function toBroadcast($notifiable)
    {
        return new \Illuminate\Notifications\Messages\BroadcastMessage([
            'id' => $this->id,
            'type' => 'revision_task_approved',
            'title' => 'Revision Approved',
            'message' => 'Your revision for "' . $this->contract->title . '" has been approved by '
                . ($this->approvedBy->nama_user ?? 'reviewer') . '.',
            'contract_id' => $this->contract->id,
            'contract_title' => $this->contract->title,
            'contract_number' => $this->contract->contract_number ?? 'N/A',
            'contract_type' => $this->contract->contract_type,
            'task_id' => $this->task->id,
            'loop_count' => $this->task->loop_count ?? 0,
            'approved_by' => $this->approvedBy->nama_user ?? 'System',
            'approved_at' => now()->toDateTimeString(),
            'action_url' => $this->resolveUrl(),
            'icon' => 'fa-check-circle',
            'color' => 'green',
            'timestamp' => now()->toDateTimeString(),
        ]);
    }
}