<?php

namespace App\Notifications;

use App\Models\Contract;
use App\Models\TblUser;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\BroadcastMessage;

class SuratFileUploadedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Contract $contract,
        public TblUser  $uploader
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database', 'broadcast'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $badgeBg   = '#e0f2fe';
        $badgeText = '#0369a1';
        $badgeLabel = 'FILE UPLOADED';

        $url = route('surat.show', $this->contract);

        $content = "
        <div style='font-family:Arial,Helvetica,sans-serif;background:#f4f6f9;padding:40px 20px;'>
            <div style='max-width:640px;'>

                <div style='background:#ffffff;padding:32px;border-radius:8px;'>

                    <h2 style='margin:0 0 20px 0;font-size:20px;color:#111827;'>
                        📎 Document File Uploaded
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

                    <p>A file has been uploaded for the following document:</p>

                    <hr style='border:none;border-top:1px solid #e5e7eb;margin:20px 0;'>

                    <p><strong>Document Title</strong><br>{$this->contract->title}</p>

                    <p><strong>Current Status</strong><br>" . strtoupper($this->contract->status) . "</p>

                    <p><strong>Uploaded By</strong><br>{$this->uploader->nama_user}</p>

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
            ->subject('[FILE UPLOADED] ' . $this->contract->title)
            ->view('emails.review-assignment', [
                'content' => $content
            ]);
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'type'            => 'file_uploaded',
            'title'           => 'Document File Uploaded',
            'message'         => 'A file has been uploaded to the document.',
            'contract_id'     => $this->contract->id,
            'contract_title'  => $this->contract->title,
            'contract_status' => $this->contract->status,
            'sender_name'     => $this->uploader->nama_user,
            'sender_id'       => $this->uploader->id_user ?? $this->uploader->id,
            'action_url'      => route('surat.show', $this->contract),
            'icon'            => 'fa-file-arrow-up',
            'color'           => 'primary'
        ];
    }

    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage($this->toDatabase($notifiable));
    }
}