<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

class RevisionTaskCancelledNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $contract;
    protected $task;
    protected $cancelledBy;

    public function __construct($contract, $task, $cancelledBy = null)
    {
        $this->contract    = $contract;
        $this->task        = $task;
        $this->cancelledBy = $cancelledBy;
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
        $loopCount  = $this->task->loop_count ?? 0;

        $badgeBg    = $type === 'surat' ? '#fef9e7' : '#fff3cd';
        $badgeText  = $type === 'surat' ? '#d97706' : '#92400e';
        $badgeLabel = strtoupper($type);

        $loopNote = $loopCount > 0
            ? "<p style='color:#6b7280;font-size:13px;'>This was revision loop #{$loopCount}.</p>"
            : '';

        $revisionNotesHtml = $this->task->revision_notes
            ? "<p><strong>Original Revision Notes</strong><br>" . nl2br(e($this->task->revision_notes)) . "</p>"
            : '';

        $content = "
        <div style='font-family:Arial,Helvetica,sans-serif;background:#f4f6f9;padding:40px 20px;'>
            <div style='max-width:640px;'>
                <div style='background:#ffffff;padding:32px;border-radius:8px;'>

                    <h2 style='margin:0 0 20px 0;font-size:20px;color:#dc2626;'>
                        🚫 Your Revision Task Has Been Cancelled
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

                    <p>
                        The revision task assigned to you has been <strong style='color:#dc2626;'>cancelled</strong>
                        by <strong>" . e($this->cancelledBy->nama_user ?? 'the reviewer') . "</strong>.
                        You no longer need to work on this revision.
                    </p>

                    {$loopNote}

                    <hr style='border:none;border-top:1px solid #e5e7eb;margin:20px 0;'>

                    <p><strong>Document Title</strong><br>" . e($this->contract->title) . "</p>

                    <p><strong>Document Number</strong><br>"
                        . e($this->contract->contract_number ?? 'Not yet assigned') . "</p>

                    <p><strong>Cancelled By</strong><br>"
                        . e($this->cancelledBy->nama_user ?? 'System') . "</p>

                    {$revisionNotesHtml}

                    <p><strong>Cancelled At</strong><br>" . now()->format('d M Y H:i') . "</p>

                    <div style='
                        background:#fef2f2;
                        border-left:4px solid #dc2626;
                        padding:12px 16px;
                        border-radius:4px;
                        margin:20px 0;
                    '>
                        <p style='margin:0;font-size:13px;color:#7f1d1d;'>
                            No further action is required from you for this revision task.
                            If you have questions, please contact the reviewer directly.
                        </p>
                    </div>

                    <div style='margin-top:28px;'>
                        <a href='{$url}'
                           style='
                                display:inline-block;
                                padding:10px 18px;
                                background:#dc2626;
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
                    This is an automated notification — no reply needed.<br>
                    Legal Management System
                </div>
            </div>
        </div>
        ";

        return (new MailMessage)
            ->subject("[{$badgeLabel}] Revision Cancelled — " . $this->contract->title)
            ->view('emails.review-assignment', ['content' => $content]);
    }

    public function toArray($notifiable): array
    {
        return [
            'type'            => 'revision_task_cancelled',
            'title'           => 'Revision Task Cancelled',
            'message'         => 'Your revision task for "' . $this->contract->title . '" has been cancelled by '
                . ($this->cancelledBy->nama_user ?? 'reviewer') . '. No further action needed.',
            'contract_id'     => $this->contract->id,
            'contract_title'  => $this->contract->title,
            'contract_number' => $this->contract->contract_number ?? 'N/A',
            'contract_type'   => $this->contract->contract_type,
            'task_id'         => $this->task->id,
            'loop_count'      => $this->task->loop_count ?? 0,
            'cancelled_by'    => $this->cancelledBy->nama_user ?? 'System',
            'cancelled_at'    => now()->toDateTimeString(),
            'action_url'      => $this->resolveUrl(),
            'icon'            => 'fa-ban',
            'color'           => 'red',
        ];
    }

    public function toBroadcast($notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'id'              => $this->id,
            'type'            => 'revision_task_cancelled',
            'title'           => 'Revision Task Cancelled',
            'message'         => 'Your revision task for "' . $this->contract->title . '" has been cancelled by '
                . ($this->cancelledBy->nama_user ?? 'reviewer') . '. No further action needed.',
            'contract_id'     => $this->contract->id,
            'contract_title'  => $this->contract->title,
            'contract_number' => $this->contract->contract_number ?? 'N/A',
            'contract_type'   => $this->contract->contract_type,
            'task_id'         => $this->task->id,
            'loop_count'      => $this->task->loop_count ?? 0,
            'cancelled_by'    => $this->cancelledBy->nama_user ?? 'System',
            'cancelled_at'    => now()->toDateTimeString(),
            'action_url'      => $this->resolveUrl(),
            'icon'            => 'fa-ban',
            'color'           => 'red',
            'timestamp'       => now()->toDateTimeString(),
        ]);
    }
}