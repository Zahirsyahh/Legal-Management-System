<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

class ContractReviewStartedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $contract;
    protected $startedBy;


    public function __construct($contract, $startedBy)
    {
        $this->contract = $contract;
        $this->startedBy = $startedBy;
    }

    public function via(object $notifiable): array
    {
        return ['mail', 'database', 'broadcast'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $type = $this->contract->contract_type ?? 'contract';
        
        // Badge styling
        $badgeBg = $type === 'surat' ? '#e6f9f0' : '#e8f1ff';
        $badgeText = $type === 'surat' ? '#0f9d58' : '#2563eb';
        $badgeLabel = strtoupper($type);

        $estimatedDate = now()->addDays(10)->format('d M Y');

        $content = "
        <div style='font-family:Arial,Helvetica,sans-serif;background:#f4f6f9;padding:40px 20px;'>
            <div style='max-width:640px;'>

                <div style='background:#ffffff;padding:32px;border-radius:8px;'>

                    <h2 style='margin:0 0 20px 0;font-size:20px;'>
                        Review Process Started
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

                    <p>The review process for the following document has been initiated.</p>

                    <hr style='border:none;border-top:1px solid #e5e7eb;margin:20px 0;'>

                    <p><strong>Title</strong><br>{$this->contract->title}</p>

                    <p><strong>Document Number</strong><br>" . ($this->contract->contract_number ?? 'Not assigned yet') . "</p>

                    <p><strong>Started By</strong><br>{$this->startedBy->nama_user}</p>

                    <p><strong>Estimated Completion</strong><br>{$estimatedDate}</p>

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
                    You will receive updates as the review progresses.<br>
                    Legal Management System
                </div>

            </div>
        </div>
        ";

        return (new MailMessage)
            ->subject("[" . strtoupper($type) . "] Review Started - " . $this->contract->title)
            ->view('emails.review-assignment', [
                'content' => $content
            ]);
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'review_started',
            'title' => 'Document Review Started',
            'message' => 'Review process started for document: ' . $this->contract->title,
            'contract_id' => $this->contract->id,
            'contract_title' => $this->contract->title,
            'contract_number' => $this->contract->contract_number,
            'started_by' => $this->startedBy->nama_user,
            'started_at' => now()->toDateTimeString(),
            'action_url' => url('/contracts/' . $this->contract->id),
            'icon' => 'fa-play-circle',
            'color' => 'blue'
        ];
    }

    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'id' => $this->id,
            'type' => 'review_started',
            'title' => 'Document Review Started',
            'message' => 'Review started for: ' . $this->contract->title,
            'contract_id' => $this->contract->id,
            'contract_title' => $this->contract->title,
            'started_by' => $this->startedBy->nama_user,
            'timestamp' => now()->toDateTimeString(),
            'icon' => 'fa-play-circle',
            'color' => 'blue'
        ]);
    }
}