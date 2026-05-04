<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * InvitationDeclinedNotification
 *
 * Dikirim ke Legal users ketika sebuah department menolak (decline)
 * review invitation. Menyertakan alasan penolakan dan link untuk re-invite.
 */
class InvitationDeclinedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        protected $contract,
        protected $department,
        protected $declinedBy,
        protected ?string $declineReason = null
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database', 'broadcast'];
    }

    protected function resolveContractUrl(): string
    {
        return route('contracts.show', $this->contract);
    }

    protected function resolveWorkflowEditUrl(): string
    {
        return route('legal.workflow.edit', $this->contract);
    }

    public function toMail(object $notifiable): MailMessage
    {
        $url        = $this->resolveContractUrl();
        $editUrl    = $this->resolveWorkflowEditUrl();
        $reasonHtml = $this->declineReason
            ? "<p><strong>Reason:</strong><br><em>" . e($this->declineReason) . "</em></p>"
            : "<p><em>No reason provided.</em></p>";

        $content = "
        <div style='font-family:Arial,Helvetica,sans-serif;background:#f4f6f9;padding:40px 20px;'>
            <div style='max-width:640px;'>
                <div style='background:#ffffff;padding:32px;border-radius:8px;'>

                    <h2 style='margin:0 0 20px 0;font-size:20px;color:#dc2626;'>
                        ⚠️ Review Invitation Declined
                    </h2>

                    <div style='padding:12px 16px;background:#fef2f2;border-left:4px solid #dc2626;
                                border-radius:4px;margin-bottom:20px;font-size:13px;color:#991b1b;'>
                        <strong>{$this->department->name}</strong> has declined the review invitation.
                    </div>

                    <p>Hello <strong>{$notifiable->nama_user}</strong>,</p>

                    <p>
                        The department admin of <strong>{$this->department->name}</strong>
                        (<strong>{$this->declinedBy->nama_user}</strong>) has declined the
                        review invitation for the following contract:
                    </p>

                    <hr style='border:none;border-top:1px solid #e5e7eb;margin:20px 0;'>

                    <p><strong>Contract Title</strong><br>{$this->contract->title}</p>
                    <p><strong>Contract Number</strong><br>"
                        . ($this->contract->contract_number ?? 'Not yet assigned') . "</p>
                    <p><strong>Department</strong><br>{$this->department->name}</p>
                    <p><strong>Declined By</strong><br>{$this->declinedBy->nama_user}</p>
                    <p><strong>Declined At</strong><br>" . now()->format('d M Y, H:i') . "</p>

                    <p><strong>Decline Reason</strong></p>
                    {$reasonHtml}

                    <div style='margin-top:16px;padding:12px 16px;background:#fff7ed;
                                border-left:4px solid #f59e0b;border-radius:4px;
                                font-size:13px;color:#92400e;'>
                        <strong>Action Required:</strong> You may re-invite this department
                        or adjust the workflow as needed.
                    </div>

                    <div style='margin-top:28px;display:flex;gap:12px;'>
                        <a href='{$editUrl}'
                           style='display:inline-block;padding:10px 18px;
                                  background:#7c3aed;color:#ffffff;
                                  text-decoration:none;border-radius:6px;font-size:14px;'>
                            Edit Workflow / Re-Invite
                        </a>
                        <a href='{$url}'
                           style='display:inline-block;padding:10px 18px;
                                  background:#e5e7eb;color:#374151;
                                  text-decoration:none;border-radius:6px;font-size:14px;'>
                            View Contract
                        </a>
                    </div>

                </div>
                <div style='margin-top:20px;font-size:12px;color:#94a3b8;'>
                    Legal Management System
                </div>
            </div>
        </div>";

        return (new MailMessage)
            ->subject("[ACTION REQUIRED] {$this->department->name} Declined Invitation — {$this->contract->title}")
            ->view('emails.review-assignment', ['content' => $content]);
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type'            => 'invitation_declined',
            'title'           => '⚠️ Invitation Declined — ' . $this->department->name,
            'message'         => $this->declinedBy->nama_user
                . ' from ' . $this->department->name
                . ' declined the review invitation for: ' . $this->contract->title,
            'contract_id'     => $this->contract->id,
            'contract_title'  => $this->contract->title,
            'contract_number' => $this->contract->contract_number ?? 'N/A',
            'department_id'   => $this->department->id,
            'department_name' => $this->department->name,
            'declined_by'     => $this->declinedBy->nama_user,
            'decline_reason'  => $this->declineReason,
            'declined_at'     => now()->toDateTimeString(),
            'action_url'      => $this->resolveWorkflowEditUrl(),
            'icon'            => 'fa-times-circle',
            'color'           => 'red',
        ];
    }

    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'id'              => $this->id,
            'type'            => 'invitation_declined',
            'title'           => '⚠️ Invitation Declined',
            'message'         => $this->department->name . ' declined the review invitation for: '
                . $this->contract->title,
            'contract_id'     => $this->contract->id,
            'contract_title'  => $this->contract->title,
            'department'      => $this->department->name,
            'declined_by'     => $this->declinedBy->nama_user,
            'decline_reason'  => $this->declineReason,
            'timestamp'       => now()->toDateTimeString(),
            'icon'            => 'fa-times-circle',
            'color'           => 'red',
        ]);
    }
}