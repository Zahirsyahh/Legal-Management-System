<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

class DocumentSubmittedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $contract;
    protected $submittedBy;

    public function __construct($contract, $submittedBy)
    {
        $this->contract    = $contract;
        $this->submittedBy = $submittedBy;
    }

    public function via(object $notifiable): array
    {
        return ['mail', 'database', 'broadcast'];
    }

    /**
     * Generate dynamic URL based on document type
     */
    protected function resolveUrl(): string
    {
        return route(
            $this->contract->contract_type === 'surat'
                ? 'surat.show'
                : 'contracts.show',
            $this->contract
        );
    }

    public function toMail(object $notifiable): MailMessage
    {
        $type = $this->contract->contract_type ?? 'contract';
        $url  = $this->resolveUrl();

        $badgeBg   = $type === 'surat' ? '#e6f9f0' : '#e8f1ff';
        $badgeText = $type === 'surat' ? '#0f9d58' : '#2563eb';
        $badgeLabel = strtoupper($type);

        $content = "
        <div style='font-family:Arial,Helvetica,sans-serif;background:#f4f6f9;padding:40px 20px;'>
            <div style='max-width:640px;'>

                <div style='background:#ffffff;padding:32px;border-radius:8px;'>

                    <h2 style='margin:0 0 20px 0;font-size:20px;color:#111827;'>
                        📥 New Document Submitted
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

                    <p>A new document has been submitted to the system and requires legal review.</p>

                    <hr style='border:none;border-top:1px solid #e5e7eb;margin:20px 0;'>

                    <p><strong>Title</strong><br>{$this->contract->title}</p>

                    <p><strong>Description</strong><br>
                        " . (
                            $this->contract->description
                                ? nl2br(e($this->contract->description))
                                : '<em>No description provided</em>'
                        ) . "
                    </p>

                    <p><strong>Document Number</strong><br>" 
                        . ($this->contract->contract_number ?? 'Not generated yet') . "</p>

                    <p><strong>Department</strong><br>{$this->contract->department_code}</p>

                    <p><strong>Submitted By</strong><br>{$this->submittedBy->nama_user}</p>

                    <p><strong>Submitted At</strong><br>" 
                        . now()->format('d M Y H:i') . "</p>

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
                            Review Document
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
            ->subject("[" . strtoupper($type) . "] New Submission - " . $this->contract->title)
            ->view('emails.review-assignment', [
                'content' => $content
            ]);
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type'             => 'document_submitted',
            'title'            => 'New Document Submitted',
            'message'          => 'New document submitted: ' . $this->contract->title,
            'contract_id'      => $this->contract->id,
            'contract_title'   => $this->contract->title,
            'contract_number'  => $this->contract->contract_number,
            'department_code'  => $this->contract->department_code,
            'contract_type'    => $this->contract->contract_type,
            'submitted_by'     => $this->submittedBy->nama_user,
            'submitted_at'     => now()->toDateTimeString(),
            'action_url'       => $this->resolveUrl(),
            'icon'             => 'fa-paper-plane',
            'color'            => 'blue'
        ];
    }

    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'id'              => $this->id,
            'type'            => 'document_submitted',
            'title'           => 'New Document Submitted',
            'message'         => 'New submission: ' . $this->contract->title,
            'contract_id'     => $this->contract->id,
            'contract_title'  => $this->contract->title,
            'contract_type'   => $this->contract->contract_type,
            'submitted_by'    => $this->submittedBy->nama_user,
            'timestamp'       => now()->toDateTimeString(),
            'action_url'      => $this->resolveUrl(),
            'icon'            => 'fa-paper-plane',
            'color'           => 'blue'
        ]);
    }
}