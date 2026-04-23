<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\BroadcastMessage;

class StageRemovedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $contract;
    protected string $stageName;
    protected $removedBy;

    public function __construct($contract, string $stageName, $removedBy = null)
    {
        $this->contract  = $contract;
        $this->stageName = $stageName;
        $this->removedBy = $removedBy;
    }

    public function via($notifiable)
    {
        return ['mail', 'database', 'broadcast'];
    }

    protected function resolveUrl(): string
    {
        return route(
            $this->contract->contract_type === 'surat'
                ? 'surat.show'
                : 'contracts.show',
            $this->contract
        );
    }

    public function toMail($notifiable)
    {
        $type       = $this->contract->contract_type ?? 'contract';
        $url        = $this->resolveUrl();

        $badgeBg    = $type === 'surat' ? '#e6f9f0' : '#e8f1ff';
        $badgeText  = $type === 'surat' ? '#0f9d58' : '#2563eb';
        $badgeLabel = strtoupper($type);

        $content = "
        <div style='font-family:Arial,Helvetica,sans-serif;background:#f4f6f9;padding:40px 20px;'>
            <div style='max-width:640px;'>
                <div style='background:#ffffff;padding:32px;border-radius:8px;'>

                    <h2 style='margin:0 0 20px 0;font-size:20px;color:#6b7280;'>
                        Your Review Stage Has Been Removed
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

                    <p>
                        Your review stage for the document below has been removed from the workflow.
                        No further action is required from you.
                    </p>

                    <hr style='border:none;border-top:1px solid #e5e7eb;margin:20px 0;'>

                    <p><strong>Document Title</strong><br>{$this->contract->title}</p>

                    <p><strong>Document Number</strong><br>"
                        . ($this->contract->contract_number ?? 'Not yet assigned') . "</p>

                    <p><strong>Stage Removed</strong><br>" . e($this->stageName) . "</p>

                    <p><strong>Removed By</strong><br>"
                        . ($this->removedBy->nama_user ?? 'System') . "</p>

                    <p><strong>Date</strong><br>" . now()->format('d M Y H:i') . "</p>

                    <div style='
                        background:#f9fafb;
                        border-left:4px solid #9ca3af;
                        padding:12px 16px;
                        border-radius:4px;
                        margin:20px 0;
                        font-size:14px;
                        color:#6b7280;
                    '>
                        If you believe this is a mistake, please contact the Legal team.
                    </div>

                    <div style='margin-top:28px;'>
                        <a href='{$url}'
                           style='
                                display:inline-block;
                                padding:10px 18px;
                                background:#6b7280;
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
            ->subject("[{$badgeLabel}] Review Stage Removed — {$this->contract->title}")
            ->view('emails.review-assignment', ['content' => $content]);
    }

    public function toArray($notifiable)
    {
        return [
            'type'            => 'stage_removed',
            'title'           => 'Your Review Stage Has Been Removed',
            'message'         => 'Your stage "' . $this->stageName . '" has been removed from the review workflow for "'
                . $this->contract->title . '".',
            'contract_id'     => $this->contract->id,
            'contract_title'  => $this->contract->title,
            'contract_number' => $this->contract->contract_number ?? 'N/A',
            'contract_type'   => $this->contract->contract_type,
            'stage_name'      => $this->stageName,
            'removed_by'      => $this->removedBy->nama_user ?? 'System',
            'removed_at'      => now()->toDateTimeString(),
            'action_url'      => $this->resolveUrl(),
            'icon'            => 'fa-user-minus',
            'color'           => 'gray',
        ];
    }

    public function toBroadcast($notifiable)
    {
        return new BroadcastMessage([
            'id' => $this->id,
            'type' => 'stage_removed',
            'title' => 'Your Review Stage Has Been Removed',
            'message' => 'Your stage "' . $this->stageName . '" has been removed from the review workflow for "'
                . $this->contract->title . '".',
            'contract_id' => $this->contract->id,
            'contract_title' => $this->contract->title,
            'contract_number' => $this->contract->contract_number ?? 'N/A',
            'contract_type' => $this->contract->contract_type,
            'stage_name' => $this->stageName,
            'removed_by' => $this->removedBy->nama_user ?? 'System',
            'removed_at' => now()->toDateTimeString(),
            'action_url' => $this->resolveUrl(),
            'icon' => 'fa-user-minus',
            'color' => 'gray',
            'timestamp' => now()->toDateTimeString(),
        ]);
    }
}