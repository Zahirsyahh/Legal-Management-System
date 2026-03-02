<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\BroadcastMessage;

class ContractRejectedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $contract;
    protected $stage;
    protected $reason;
    protected $rejectedBy;

    public function __construct($contract, $stage, $reason, $rejectedBy = null)
    {
        $this->contract = $contract;
        $this->stage = $stage;
        $this->reason = $reason;
        $this->rejectedBy = $rejectedBy;
    }

    public function via($notifiable)
    {
        return ['mail', 'database', 'broadcast'];
    }

    public function toMail($notifiable)
    {
        $type = $this->contract->contract_type ?? 'contract';
        $stageName = $this->stage ? str_replace('_', ' ', $this->stage->stage_name) : 'review';
        
        // Badge styling
        $badgeBg = $type === 'surat' ? '#e6f9f0' : '#e8f1ff';
        $badgeText = $type === 'surat' ? '#0f9d58' : '#2563eb';
        $badgeLabel = strtoupper($type);

        $content = "
        <div style='font-family:Arial,Helvetica,sans-serif;background:#f4f6f9;padding:40px 20px;'>
            <div style='max-width:640px;'>

                <div style='background:#ffffff;padding:32px;border-radius:8px;'>

                    <h2 style='margin:0 0 20px 0;font-size:20px;color:#b91c1c;'>
                        Document Rejected
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

                    <p>A document has been rejected during the review process.</p>

                    <hr style='border:none;border-top:1px solid #e5e7eb;margin:20px 0;'>

                    <p><strong>Title</strong><br>{$this->contract->title}</p>

                    <p><strong>Document Number</strong><br>" . ($this->contract->contract_number ?? 'Not assigned yet') . "</p>

                    <p><strong>Stage</strong><br>" . ucfirst($stageName) . "</p>

                    <p><strong>Rejected By</strong><br>" . ($this->rejectedBy?->nama_user ?? 'System') . "</p>

                    <p><strong>Date</strong><br>" . now()->format('d M Y H:i') . "</p>

                    <p><strong>Reason for Rejection</strong><br>" . nl2br(e($this->reason)) . "</p>

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
                    Please review the rejection reason and take necessary action.<br>
                    Legal Management System
                </div>

            </div>
        </div>
        ";

        return (new MailMessage)
            ->subject("[" . strtoupper($type) . "] Document Rejected - " . $this->contract->title)
            ->view('emails.review-assignment', [
                'content' => $content
            ]);
    }

    public function toArray($notifiable)
    {
        $stageName = $this->stage ? str_replace('_', ' ', $this->stage->stage_name) : 'review';
        
        return [
            'type' => 'document_rejected',
            'title' => 'Document Rejected',
            'message' => 'Document "' . $this->contract->title . '" was rejected at ' . ucfirst($stageName) . ' stage',
            'contract_id' => $this->contract->id,
            'contract_title' => $this->contract->title,
            'contract_number' => $this->contract->contract_number ?? 'N/A',
            'stage' => ucfirst($stageName),
            'reason' => $this->reason,
            'rejected_by' => $this->rejectedBy?->nama_user ?? 'System',
            'rejected_at' => now()->toDateTimeString(),
            'action_url' => url('/contracts/' . $this->contract->id),
            'icon' => 'fa-times-circle',
            'color' => 'red'
        ];
    }

    public function toBroadcast($notifiable)
    {
        return new BroadcastMessage([
            'id' => $this->id,
            'type' => 'document_rejected',
            'title' => 'Document Rejected',
            'message' => 'Document "' . $this->contract->title . '" was rejected',
            'contract_id' => $this->contract->id,
            'contract_title' => $this->contract->title,
            'stage' => $this->stage ? str_replace('_', ' ', $this->stage->stage_name) : 'review',
            'rejected_by' => $this->rejectedBy?->nama_user ?? 'System',
            'timestamp' => now()->toDateTimeString(),
            'icon' => 'fa-times-circle',
            'color' => 'red'
        ]);
    }
}