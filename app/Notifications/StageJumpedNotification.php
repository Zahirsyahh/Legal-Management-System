<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\BroadcastMessage;

class StageJumpedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $contract;
    protected $fromStage;
    protected $toStage;
    protected $jumpedBy;

    public function __construct($contract, $fromStage, $toStage, $jumpedBy)
    {
        $this->contract = $contract;
        $this->fromStage = $fromStage;
        $this->toStage = $toStage;
        $this->jumpedBy = $jumpedBy;
    }

    public function via(object $notifiable): array
    {
        return ['database', 'broadcast', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $type = $this->contract->contract_type ?? 'contract';

        $fromStageName = $this->fromStage
            ? str_replace('_', ' ', $this->fromStage->stage_name)
            : 'previous';

        $toStageName = $this->toStage
            ? str_replace('_', ' ', $this->toStage->stage_name)
            : 'next';

        // ==============================
        // PREVIOUS REVIEWER NOTES ONLY
        // ==============================
        $previousNotes = null;

        if ($this->fromStage) {
            $previousNotes = $this->fromStage->notes;
        }

        $previousNotesSection = '';

        if (!empty($previousNotes)) {
            $previousNotesSection = "
                <div style='
                    margin-top:24px;
                    padding:16px;
                    background:#f8fafc;
                    border-left:4px solid #0891b2;
                    border-radius:6px;
                    font-size:14px;
                    line-height:1.6;
                '>
                    <strong>Previous Reviewer Notes:</strong><br><br>
                    " . nl2br(e($previousNotes)) . "
                </div>
            ";
        }

        // Badge styling
        $badgeBg = $type === 'surat' ? '#e6f9f0' : '#e8f1ff';
        $badgeText = $type === 'surat' ? '#0f9d58' : '#2563eb';
        $badgeLabel = strtoupper($type);

        $content = "
        <div style='font-family:Arial,Helvetica,sans-serif;background:#f4f6f9;padding:40px 20px;'>
            <div style='max-width:640px;'>

                <div style='background:#ffffff;padding:32px;border-radius:8px;'>

                    <h2 style='margin:0 0 20px 0;font-size:20px;color:#0891b2;'>
                        Stage Updated
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

                    <p>Your document has been reviewed.</p>

                    <hr style='border:none;border-top:1px solid #e5e7eb;margin:20px 0;'>

                    <p><strong>Title</strong><br>{$this->contract->title}</p>

                    <p><strong>Document Number</strong><br>" . 
                        ($this->contract->contract_number ?? 'Not assigned yet') . 
                    "</p>

                    <p><strong>Previous Stage</strong><br>" . ucfirst($fromStageName) . "</p>

                    <p><strong>Current Stage</strong><br>" . ucfirst($toStageName) . "</p>

                    <p><strong>Moved By</strong><br>{$this->jumpedBy->nama_user}</p>

                    <p><strong>Moved At</strong><br>" . now()->format('d M Y H:i') . "</p>

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
                            Review Document
                        </a>
                    </div>

                </div>

                <div style='margin-top:20px;font-size:12px;color:#94a3b8;'>
                    The document is now ready for your review.<br>
                    Legal Management System
                </div>

            </div>
        </div>
        ";

        return (new MailMessage)
            ->subject("[" . strtoupper($type) . "] Stage Updated - " . $this->contract->title)
            ->view('emails.review-assignment', [
                'content' => $content
            ]);
    }

    public function toArray(object $notifiable): array
    {
        $fromStageName = $this->fromStage
            ? str_replace('_', ' ', $this->fromStage->stage_name)
            : 'previous';

        $toStageName = $this->toStage
            ? str_replace('_', ' ', $this->toStage->stage_name)
            : 'next';

        return [
            'type' => 'stage_jumped',
            'title' => 'Stage Updated',
            'message' => 'Document moved from ' . ucfirst($fromStageName) . ' to ' . ucfirst($toStageName),
            'contract_id' => $this->contract->id,
            'contract_title' => $this->contract->title,
            'contract_number' => $this->contract->contract_number,
            'from_stage' => ucfirst($fromStageName),
            'to_stage' => ucfirst($toStageName),
            'jumped_by' => $this->jumpedBy->nama_user,
            'jumped_at' => now()->toDateTimeString(),
            'action_url' => url('/contracts/' . $this->contract->id),
            'icon' => 'fa-arrow-right',
            'color' => 'cyan'
        ];
    }

    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        $toStageName = $this->toStage
            ? str_replace('_', ' ', $this->toStage->stage_name)
            : 'next';

        return new BroadcastMessage([
            'id' => $this->id,
            'type' => 'stage_jumped',
            'title' => 'Stage Updated',
            'message' => 'Document moved to your review stage',
            'contract_id' => $this->contract->id,
            'contract_title' => $this->contract->title,
            'to_stage' => ucfirst($toStageName),
            'jumped_by' => $this->jumpedBy->nama_user,
            'timestamp' => now()->toDateTimeString(),
            'icon' => 'fa-arrow-right',
            'color' => 'cyan'
        ]);
    }
}
