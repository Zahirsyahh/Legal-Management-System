<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\BroadcastMessage;

class RevisionTaskAssignedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $contract;
    protected $task;
    protected $requestedBy;

    public function __construct($contract, $task, $requestedBy = null)
    {
        $this->contract    = $contract;
        $this->task        = $task;
        $this->requestedBy = $requestedBy;
    }

    public function via($notifiable)
    {
        return ['mail', 'database', 'broadcast'];
    }

    protected function resolveUrl(): string
    {
        return route('revision-tasks.show', $this->task);
    }

    public function toMail($notifiable)
    {
        $type       = $this->contract->contract_type ?? 'contract';
        $url        = $this->resolveUrl();
        $loopCount  = $this->task->loop_count ?? 0;
        $isReRequest = $loopCount > 0;

        $badgeBg    = $type === 'surat' ? '#e6f9f0' : '#e8f1ff';
        $badgeText  = $type === 'surat' ? '#0f9d58' : '#2563eb';
        $badgeLabel = strtoupper($type);

        $headline   = $isReRequest
            ? 'Revision Re-Requested (Loop #' . $loopCount . ')'
            : 'Revision Task Assigned';

        $headlineColor = $isReRequest ? '#b45309' : '#1d4ed8';

        $loopBadge = $isReRequest
            ? "<span style='display:inline-block;padding:4px 10px;font-size:11px;font-weight:700;
                border-radius:20px;background:#fef3c7;color:#b45309;margin-bottom:12px;'>
                🔁 Re-request — Loop ke-{$loopCount}
               </span><br>"
            : '';

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

                    {$loopBadge}

                    <p>Hello <strong>{$notifiable->nama_user}</strong>,</p>

                    <p>You have a revision task that needs your attention.</p>

                    <hr style='border:none;border-top:1px solid #e5e7eb;margin:20px 0;'>

                    <p><strong>Document Title</strong><br>{$this->contract->title}</p>

                    <p><strong>Document Number</strong><br>"
                        . ($this->contract->contract_number ?? 'Not yet assigned') . "</p>

                    <p><strong>Requested By</strong><br>"
                        . ($this->requestedBy->nama_user ?? 'System') . "</p>

                    <p><strong>Revision Notes</strong><br>"
                        . nl2br(e($this->task->revision_notes ?? '-')) . "</p>

                    <p><strong>Date</strong><br>" . now()->format('d M Y H:i') . "</p>

                    <div style='margin-top:28px;'>
                        <a href='{$url}'
                           style='
                                display:inline-block;
                                padding:10px 18px;
                                background:#1d4ed8;
                                color:#ffffff;
                                text-decoration:none;
                                border-radius:6px;
                                font-size:14px;
                           '>
                            View & Work on Revision
                        </a>
                    </div>

                </div>

                <div style='margin-top:20px;font-size:12px;color:#94a3b8;'>
                    Please complete your revision and submit it as soon as possible.<br>
                    Legal Management System
                </div>
            </div>
        </div>
        ";

        $subject = $isReRequest
            ? "[{$badgeLabel}] Re-Request Revision (Loop #{$loopCount}) — {$this->contract->title}"
            : "[{$badgeLabel}] Revision Task Assigned — {$this->contract->title}";

        return (new MailMessage)
            ->subject($subject)
            ->view('emails.review-assignment', ['content' => $content]);
    }

    public function toArray($notifiable)
    {
        $loopCount   = $this->task->loop_count ?? 0;
        $isReRequest = $loopCount > 0;

        return [
            'type'            => 'revision_task_assigned',
            'title'           => $isReRequest
                ? 'Revision Re-Requested (Loop #' . $loopCount . ')'
                : 'Revision Task Assigned',
            'message'         => 'You have a revision task for: ' . $this->contract->title
                . ($isReRequest ? ' (Loop #' . $loopCount . ')' : ''),
            'contract_id'     => $this->contract->id,
            'contract_title'  => $this->contract->title,
            'contract_number' => $this->contract->contract_number ?? 'N/A',
            'contract_type'   => $this->contract->contract_type,
            'task_id'         => $this->task->id,
            'loop_count'      => $loopCount,
            'revision_notes'  => $this->task->revision_notes,
            'requested_by'    => $this->requestedBy->nama_user ?? 'System',
            'sent_at'         => now()->toDateTimeString(),
            'action_url'      => $this->resolveUrl(),
            'icon'            => 'fa-edit',
            'color'           => $isReRequest ? 'yellow' : 'blue',
        ];
    }

    public function toBroadcast($notifiable)
    {
        $loopCount = $this->task->loop_count ?? 0;
        $isReRequest = $loopCount > 0;

        return new BroadcastMessage([
            'id' => $this->id,
            'type' => 'revision_task_assigned',
            'title' => $isReRequest
                ? 'Revision Re-Requested (Loop #' . $loopCount . ')'
                : 'Revision Task Assigned',
            'message' => 'You have a revision task for: ' . $this->contract->title
                . ($isReRequest ? ' (Loop #' . $loopCount . ')' : ''),
            'contract_id' => $this->contract->id,
            'contract_title' => $this->contract->title,
            'contract_number' => $this->contract->contract_number ?? 'N/A',
            'contract_type' => $this->contract->contract_type,
            'task_id' => $this->task->id,
            'loop_count' => $loopCount,
            'revision_notes' => $this->task->revision_notes,
            'requested_by' => $this->requestedBy->nama_user ?? 'System',
            'sent_at' => now()->toDateTimeString(),
            'action_url' => $this->resolveUrl(),
            'icon' => 'fa-edit',
            'color' => $isReRequest ? 'yellow' : 'blue',
            'timestamp' => now()->toDateTimeString(),
        ]);
    }
}