<?php

namespace App\Notifications;

use App\Models\Contract;
use App\Models\TblUser;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\BroadcastMessage;

class SuratReleasedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Contract $contract,
        public TblUser  $actor
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database', 'broadcast'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $badgeBg   = '#dcfce7';
        $badgeText = '#166534';
        $badgeLabel = 'COMPLETED';

        $url = route('surat.show', $this->contract);

        $content = "
        <div style='font-family:Arial,Helvetica,sans-serif;background:#f4f6f9;padding:40px 20px;'>
            <div style='max-width:640px;'>

                <div style='background:#ffffff;padding:32px;border-radius:8px;'>

                    <h2 style='margin:0 0 20px 0;font-size:20px;color:#111827;'>
                        🚀 Document Completed
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

                    <p>The following document has been officially Completed:</p>

                    <hr style='border:none;border-top:1px solid #e5e7eb;margin:20px 0;'>

                    <p><strong>Document Title</strong><br>{$this->contract->title}</p>

                    <p><strong>Document Number</strong><br>{$this->contract->contract_number}</p>

                    <p><strong>Completed By</strong><br>{$this->actor->nama_user}</p>

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
                            View Document
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
            ->subject('[DOCUMENT COMPLETED] ' . $this->contract->title)
            ->view('emails.review-assignment', [
                'content' => $content
            ]);
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'type'            => 'letter_released',
            'title'           => 'Document Completed',
            'message'         => 'The document has been officially Completed.',
            'contract_id'     => $this->contract->id,
            'contract_title'  => $this->contract->title,
            'contract_status' => $this->contract->status,
            'sender_name'     => $this->actor->nama_user,
            'sender_id'       => $this->actor->id_user ?? $this->actor->id,
            'action_url'      => route('surat.show', $this->contract),
            'icon'            => 'fa-circle-check',
            'color'           => 'success'
        ];
    }

    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage($this->toDatabase($notifiable));
    }
}