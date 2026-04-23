<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\BroadcastMessage;

class RevisionTaskSubmittedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $contract;
    protected $task;
    protected $submittedBy;

    public function __construct($contract, $task, $submittedBy = null)
    {
        $this->contract    = $contract;
        $this->task        = $task;
        $this->submittedBy = $submittedBy;
    }

    public function via($notifiable)
    {
        return ['mail', 'database', 'broadcast'];
    }

    protected function resolveUrl(): string
    {
        // Arahkan ke stage Bimo (from_stage) supaya langsung bisa approve/re-request
        if ($this->task->fromStage && $this->task->fromStage->contract_id) {
            return route('review-stages.show', [
                $this->contract,
                $this->task->from_stage_id,
            ]);
        }

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
            ? "<p style='color:#6b7280;font-size:13px;'>This is revision loop #{$loopCount}.</p>"
            : '';

        $content = "
        <div style='font-family:Arial,Helvetica,sans-serif;background:#f4f6f9;padding:40px 20px;'>
            <div style='max-width:640px;'>
                <div style='background:#ffffff;padding:32px;border-radius:8px;'>

                    <h2 style='margin:0 0 20px 0;font-size:20px;color:#7c3aed;'>
                        📬 Revision Submitted — Action Required
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
                        <strong>" . ($this->submittedBy->nama_user ?? 'A reviewer') . "</strong>
                        has submitted their revision result and is waiting for your decision.
                    </p>

                    {$loopNote}

                    <hr style='border:none;border-top:1px solid #e5e7eb;margin:20px 0;'>

                    <p><strong>Document Title</strong><br>{$this->contract->title}</p>

                    <p><strong>Document Number</strong><br>"
                        . ($this->contract->contract_number ?? 'Not yet assigned') . "</p>

                    <p><strong>Submitted By</strong><br>"
                        . ($this->submittedBy->nama_user ?? 'System') . "</p>

                    <p><strong>Response Notes</strong><br>"
                        . nl2br(e($this->task->response_notes ?? '-')) . "</p>

                    <p><strong>Submitted At</strong><br>" . now()->format('d M Y H:i') . "</p>

                    <div style='
                        background:#f5f3ff;
                        border-left:4px solid #7c3aed;
                        padding:12px 16px;
                        border-radius:4px;
                        margin:20px 0;
                        font-size:14px;
                        color:#4c1d95;
                    '>
                        <strong>Next Step:</strong> Please review the submission and either
                        <strong>Approve</strong> or <strong>Re-Request</strong> the revision.
                    </div>

                    <div style='margin-top:28px;'>
                        <a href='{$url}'
                           style='
                                display:inline-block;
                                padding:10px 18px;
                                background:#7c3aed;
                                color:#ffffff;
                                text-decoration:none;
                                border-radius:6px;
                                font-size:14px;
                           '>
                            Review Submission
                        </a>
                    </div>

                </div>

                <div style='margin-top:20px;font-size:12px;color:#94a3b8;'>
                    You can approve or re-request another revision from your stage page.<br>
                    Legal Management System
                </div>
            </div>
        </div>
        ";

        return (new MailMessage)
            ->subject("[{$badgeLabel}] Revision Submitted by " . ($this->submittedBy->nama_user ?? 'Reviewer') . " — {$this->contract->title}")
            ->view('emails.review-assignment', ['content' => $content]);
    }

    public function toArray($notifiable)
    {
        return [
            'type'            => 'revision_task_submitted',
            'title'           => 'Revision Submitted — Action Required',
            'message'         => ($this->submittedBy->nama_user ?? 'A reviewer')
                . ' has submitted their revision for "' . $this->contract->title . '". Please review and decide.',
            'contract_id'     => $this->contract->id,
            'contract_title'  => $this->contract->title,
            'contract_number' => $this->contract->contract_number ?? 'N/A',
            'contract_type'   => $this->contract->contract_type,
            'task_id'         => $this->task->id,
            'loop_count'      => $this->task->loop_count ?? 0,
            'submitted_by'    => $this->submittedBy->nama_user ?? 'System',
            'response_notes'  => $this->task->response_notes,
            'submitted_at'    => now()->toDateTimeString(),
            'action_url'      => $this->resolveUrl(),
            'icon'            => 'fa-inbox',
            'color'           => 'purple',
        ];
    }

    public function toBroadcast($notifiable)
    {
        return new BroadcastMessage([
            'id' => $this->id,
            'type' => 'revision_task_submitted',
            'title' => 'Revision Submitted — Action Required',
            'message' => ($this->submittedBy->nama_user ?? 'A reviewer')
                . ' has submitted their revision for "' . $this->contract->title . '". Please review and decide.',
            'contract_id' => $this->contract->id,
            'contract_title' => $this->contract->title,
            'contract_number' => $this->contract->contract_number ?? 'N/A',
            'contract_type' => $this->contract->contract_type,
            'task_id' => $this->task->id,
            'loop_count' => $this->task->loop_count ?? 0,
            'submitted_by' => $this->submittedBy->nama_user ?? 'System',
            'response_notes' => $this->task->response_notes,
            'submitted_at' => now()->toDateTimeString(),
            'action_url' => $this->resolveUrl(),
            'icon' => 'fa-inbox',
            'color' => 'purple',
            'timestamp' => now()->toDateTimeString(),
        ]);
    }
}