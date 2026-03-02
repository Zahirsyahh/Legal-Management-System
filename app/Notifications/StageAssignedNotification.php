<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

class StageAssignedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $contract;
    protected $fromStage; // ⬅ tambahin ini
    protected $stage;
    protected $assignedBy;

    public function __construct($contract, $fromStage, $stage, $assignedBy = null)
    {
        $this->contract = $contract;
        $this->fromStage = $fromStage;
        $this->stage = $stage;
        $this->assignedBy = $assignedBy;
    }

    public function via(object $notifiable): array
    {
        return ['mail', 'database', 'broadcast'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $type = $this->contract->contract_type ?? 'contract';

        $stageName = $this->stage
            ? str_replace('_', ' ', $this->stage->stage_name)
            : 'review';

        // ==========================
        // SAME STYLE AS STAGE JUMPED
        // ==========================
        $previousNotes = $this->fromStage->notes ?? null;

        $previousNotesSection = '';

        if (!empty($previousNotes)) {
            $previousNotesSection = "
                <div style='
                    margin-top:24px;
                    padding:16px;
                    background:#f8fafc;
                    border-left:4px solid #6366f1;
                    border-radius:6px;
                    font-size:14px;
                    line-height:1.6;
                '>
                    <strong>Previous Reviewer Notes:</strong><br><br>
                    " . nl2br(e($previousNotes)) . "
                </div>
            ";
        }

        $badgeBg   = $type === 'surat' ? '#e6f9f0' : '#e8f1ff';
        $badgeText = $type === 'surat' ? '#0f9d58' : '#2563eb';
        $badgeLabel = strtoupper($type);

        $content = "
        <div style='font-family:Arial,Helvetica,sans-serif;background:#f4f6f9;padding:40px 20px;'>
            <div style='max-width:640px;'>

                <div style='background:#ffffff;padding:32px;border-radius:8px;'>

                    <h2 style='margin:0 0 20px 0;font-size:20px;color:#111827;'>
                        📋 New Review Assignment
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

                    <p>You have been assigned to review the following document:</p>

                    <hr style='border:none;border-top:1px solid #e5e7eb;margin:20px 0;'>

                    <p><strong>Title</strong><br>{$this->contract->title}</p>

                    <p><strong>Contract Number</strong><br>" . 
                        ($this->contract->contract_number ?? 'Not assigned yet') . "
                    </p>

                    <p><strong>Stage</strong><br>" . ucfirst($stageName) . "</p>

                    <p><strong>Assigned By</strong><br>" . 
                        ($this->assignedBy?->nama_user ?? 'System') . "
                    </p>

                    <p><strong>Assigned At</strong><br>" . 
                        now()->format('d M Y H:i') . "
                    </p>

                    {$previousNotesSection}

                    <div style='margin-top:28px;'>
                        <a href='" . url('/contracts/' . $this->contract->id) . "'
                           style='
                                display:inline-block;
                                padding:10px 18px;
                                background:#111827;
                                color:#ffffff;
                                text-decoration:none;
                                border-radius:6px;
                                font-size:14px;
                           '>
                            Start Review
                        </a>
                    </div>

                </div>

                <div style='margin-top:20px;font-size:12px;color:#94a3b8;'>
                    Legal Management System
                </div>

            </div>
        </div>
        ";

        return (new MailMessage)
            ->subject("[" . strtoupper($type) . "] Review Assignment - " . $this->contract->title)
            ->view('emails.review-assignment', [
                'content' => $content
            ]);
    }

    public function toArray(object $notifiable): array
    {
        $stageName = $this->stage
            ? str_replace('_', ' ', $this->stage->stage_name)
            : 'review';

        return [
            'type' => 'stage_assigned',
            'title' => 'Stage Assignment',
            'message' => 'You have been assigned to stage: ' . ucfirst($stageName),
            'contract_id' => $this->contract->id,
            'contract_title' => $this->contract->title,
            'stage_id' => $this->stage->id ?? null,
            'stage_name' => ucfirst($stageName),
            'assigned_by' => $this->assignedBy?->nama_user ?? 'System',
            'assigned_at' => now()->toDateTimeString(),
            'action_url' => url('/contracts/' . $this->contract->id),
            'icon' => 'fa-tasks',
            'color' => 'indigo'
        ];
    }

    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage($this->toArray($notifiable));
    }
}