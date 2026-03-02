<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SuratApprovedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $contract;
    protected $approvedBy;

    public function __construct($contract, $approvedBy)
    {
        $this->contract   = $contract;
        $this->approvedBy = $approvedBy;
    }

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    protected function resolveUrl(): string
    {
        return route('surat.show', $this->contract);
    }

    public function toMail(object $notifiable): MailMessage
    {
        $url = $this->resolveUrl();

        $badgeBg   = '#e6f9f0';
        $badgeText = '#0f9d58';
        $badgeLabel = 'APPROVED';

        $description = $this->contract->description
            ? nl2br(e($this->contract->description))
            : '<em>No description provided</em>';

        $content = "
        <div style='font-family:Arial,Helvetica,sans-serif;background:#f4f6f9;padding:40px 20px;'>
            <div style='max-width:640px;margin:auto;'>

                <div style='background:#ffffff;padding:32px;border-radius:8px;'>

                    <h2 style='margin:0 0 20px 0;font-size:20px;color:#111827;'>
                        ✅ Letter Approved
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

                    <p>Your letter has been successfully approved by Legal.</p>

                    <hr style='border:none;border-top:1px solid #e5e7eb;margin:20px 0;'>

                    <p><strong>Title</strong><br>{$this->contract->title}</p>

                    <p><strong>Description</strong><br>{$description}</p>

                    <p><strong>Approved By</strong><br>{$this->approvedBy->nama_user}</p>

                    <p><strong>Approved At</strong><br>" . now()->format('d M Y H:i') . "</p>

                    <div style='margin-top:28px;'>
                        <a href='{$url}'
                           style='
                                display:inline-block;
                                padding:10px 18px;
                                background:#111827;
                                color:#ffffff;
                                text-decoration:none;
                                border-radius:6px;
                                font-size:14px;
                           '>
                            View Letter
                        </a>
                    </div>

                </div>

                <div style='margin-top:20px;font-size:12px;color:#94a3b8;text-align:center;'>
                    Legal Management System
                </div>

            </div>
        </div>
        ";

        return (new MailMessage)
            ->subject('[SURAT] Letter Approved - ' . $this->contract->title)
            ->view('emails.review-assignment', [
                'content' => $content
            ]);
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type'           => 'surat_approved',
            'title'          => 'Letter Approved',
            'message'        => 'Your letter has been approved by ' . $this->approvedBy->nama_user,
            'contract_id'    => $this->contract->id,
            'contract_title' => $this->contract->title,
            'contract_type'  => $this->contract->contract_type,
            'approved_by'    => $this->approvedBy->nama_user,
            'approved_at'    => now()->toDateTimeString(),
            'action_url'     => $this->resolveUrl(),
            'icon'           => 'fa-check-circle',
            'color'          => 'green'
        ];
    }
}