<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

class DocumentReturnedToDraftNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $contract;
    protected $returnedBy;

    public function __construct($contract, $returnedBy)
    {
        $this->contract = $contract;
        $this->returnedBy = $returnedBy;
    }

    public function via(object $notifiable): array
    {
        return ['mail', 'database', 'broadcast'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $type = $this->contract->contract_type ?? 'contract';

        // warna berbeda dari submitted (biar jelas ini returned)
        $badgeBg   = '#fff4e5';
        $badgeText = '#d97706';
        $badgeLabel = 'RETURNED TO DRAFT';

        $content = "
        <div style='font-family:Arial,Helvetica,sans-serif;background:#f4f6f9;padding:40px 20px;'>
            <div style='max-width:640px;'>

                <div style='background:#ffffff;padding:32px;border-radius:8px;'>

                    <h2 style='margin:0 0 20px 0;font-size:20px;color:#111827;'>
                        🔁 Document Returned to Draft
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

                    <p>Your document has been returned to draft by Admin. Please review and update it before submitting again.</p>

                    <hr style='border:none;border-top:1px solid #e5e7eb;margin:20px 0;'>

                    <p><strong>Title</strong><br>{$this->contract->title}</p>

                    <p><strong>Document Number</strong><br>" . ($this->contract->contract_number ?? 'Not assigned yet') . "</p>

                    <p><strong>Department</strong><br>{$this->contract->department_code}</p>

                    <p><strong>Returned By</strong><br>{$this->returnedBy->nama_user}</p>

                    <p><strong>Returned At</strong><br>" . now()->format('d M Y H:i') . "</p>

                    <div style='margin-top:28px;'>
                        <a href='" . url('/contracts/' . $this->contract->id) . "'
                           style='
                                display:inline-block;
                                padding:10px 18px;
                                background:#b45309;
                                color:#ffffff;
                                text-decoration:none;
                                border-radius:6px;
                                font-size:14px;
                           '>
                            Revise Document
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
            ->subject("[" . strtoupper($type) . "] Returned to Draft - " . $this->contract->title)
            ->view('emails.review-assignment', [
                'content' => $content
            ]);
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'document_returned_to_draft',
            'title' => 'Document Returned to Draft',
            'message' => 'Your document "' . $this->contract->title . '" was returned to draft.',
            'contract_id' => $this->contract->id,
            'contract_title' => $this->contract->title,
            'contract_number' => $this->contract->contract_number,
            'department_code' => $this->contract->department_code,
            'returned_by' => $this->returnedBy->nama_user,
            'returned_at' => now()->toDateTimeString(),
            'action_url' => url('/contracts/' . $this->contract->id),
            'icon' => 'fa-undo',
            'color' => 'orange'
        ];
    }

    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'id' => $this->id,
            'type' => 'document_returned_to_draft',
            'title' => 'Document Returned to Draft',
            'message' => 'Your document was returned to draft.',
            'contract_id' => $this->contract->id,
            'contract_title' => $this->contract->title,
            'returned_by' => $this->returnedBy->nama_user,
            'timestamp' => now()->toDateTimeString(),
            'icon' => 'fa-undo',
            'color' => 'orange'
        ]);
    }
}