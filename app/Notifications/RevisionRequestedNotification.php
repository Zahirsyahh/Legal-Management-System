<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\BroadcastMessage;

class RevisionRequestedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $contract;
    protected $fromStage;
    protected $toStage;
    protected $revisionNotes;
    protected $requestedBy;

    public function __construct($contract, $fromStage, $toStage, $revisionNotes, $requestedBy = null)
    {
        $this->contract = $contract;
        $this->fromStage = $fromStage;
        $this->toStage = $toStage;
        $this->revisionNotes = $revisionNotes;
        $this->requestedBy = $requestedBy;
    }

    public function via(object $notifiable): array
    {
        return ['mail', 'database', 'broadcast'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $type = $this->contract->contract_type ?? 'contract';
        $fromStageName = $this->fromStage ? str_replace('_', ' ', $this->fromStage->stage_name) : 'previous';
        $toStageName = $this->toStage ? str_replace('_', ' ', $this->toStage->stage_name) : 'revision';
        
        // Badge styling
        $badgeBg = $type === 'surat' ? '#e6f9f0' : '#e8f1ff';
        $badgeText = $type === 'surat' ? '#0f9d58' : '#2563eb';
        $badgeLabel = strtoupper($type);

        $content = "
        <div style='font-family:Arial,Helvetica,sans-serif;background:#f4f6f9;padding:40px 20px;'>
            <div style='max-width:640px;'>

                <div style='background:#ffffff;padding:32px;border-radius:8px;'>

                    <h2 style='margin:0 0 20px 0;font-size:20px;color:#b45309;'>
                        Revision Requested
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

                    <p>A revision has been requested for a document.</p>

                    <hr style='border:none;border-top:1px solid #e5e7eb;margin:20px 0;'>

                    <p><strong>Title</strong><br>{$this->contract->title}</p>

                    <p><strong>Document Number</strong><br>" . ($this->contract->contract_number ?? 'Not assigned yet') . "</p>

                    <p><strong>From Stage</strong><br>" . ucfirst($fromStageName) . "</p>

                    <p><strong>To Stage</strong><br>" . ucfirst($toStageName) . "</p>

                    <p><strong>Requested By</strong><br>" . ($this->requestedBy?->nama_user ?? 'System') . "</p>

                    <p><strong>Revision Notes</strong><br>" . nl2br(e($this->revisionNotes)) . "</p>

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
                            View Document
                        </a>
                    </div>

                </div>

                <div style='margin-top:20px;font-size:12px;color:#94a3b8;'>
                    Please review the revision notes and make necessary changes.<br>
                    Legal Management System
                </div>

            </div>
        </div>
        ";

        return (new MailMessage)
            ->subject("[" . strtoupper($type) . "] Revision Requested - " . $this->contract->title)
            ->view('emails.review-assignment', [
                'content' => $content
            ]);
    }

    public function toArray(object $notifiable): array
    {
        $fromStageName = $this->fromStage ? str_replace('_', ' ', $this->fromStage->stage_name) : 'previous';
        $toStageName = $this->toStage ? str_replace('_', ' ', $this->toStage->stage_name) : 'revision';
        
        return [
            'type' => 'revision_requested',
            'title' => 'Revision Requested',
            'message' => 'Revision requested from ' . ucfirst($fromStageName) . ' to ' . ucfirst($toStageName),
            'contract_id' => $this->contract->id,
            'contract_title' => $this->contract->title,
            'contract_number' => $this->contract->contract_number,
            'from_stage' => ucfirst($fromStageName),
            'to_stage' => ucfirst($toStageName),
            'revision_notes' => $this->revisionNotes,
            'requested_by' => $this->requestedBy?->nama_user ?? 'System',
            'requested_at' => now()->toDateTimeString(),
            'action_url' => url('/contracts/' . $this->contract->id),
            'icon' => 'fa-edit',
            'color' => 'orange'
        ];
    }

    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'id' => $this->id,
            'type' => 'revision_requested',
            'title' => 'Revision Requested',
            'message' => 'Revision requested for: ' . $this->contract->title,
            'contract_id' => $this->contract->id,
            'contract_title' => $this->contract->title,
            'requested_by' => $this->requestedBy?->nama_user ?? 'System',
            'timestamp' => now()->toDateTimeString(),
            'icon' => 'fa-edit',
            'color' => 'orange'
        ]);
    }
}