<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

class StaffAssignedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $contract;
    protected $department;
    protected $assignedBy;
    protected $notes;

    public function __construct($contract, $department, $assignedBy = null, $notes = null)
    {
        $this->contract = $contract;
        $this->department = $department;
        $this->assignedBy = $assignedBy;
        $this->notes = $notes;
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

        $dueDate = now()->addDays(3)->format('d M Y');

        $notesHtml = $this->notes ? "<p><strong>Notes</strong><br>" . nl2br(e($this->notes)) . "</p>" : '';

        $content = "
        <div style='font-family:Arial,Helvetica,sans-serif;background:#f4f6f9;padding:40px 20px;'>
            <div style='max-width:640px;'>

                <div style='background:#ffffff;padding:32px;border-radius:8px;'>

                    <h2 style='margin:0 0 20px 0;font-size:20px;'>
                        Staff Assignment
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

                    <p>You have been assigned to review a document.</p>

                    <hr style='border:none;border-top:1px solid #e5e7eb;margin:20px 0;'>

                    <p><strong>Title</strong><br>{$this->contract->title}</p>

                    <p><strong>Document Number</strong><br>" . ($this->contract->contract_number ?? 'Not assigned yet') . "</p>

                    <p><strong>Department</strong><br>{$this->department->name}</p>

                    <p><strong>Assigned By</strong><br>" . ($this->assignedBy->nama_user ?? 'System') . "</p>

                    <p><strong>Due Date</strong><br>{$dueDate}</p>

                    {$notesHtml}

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
                            Start Review
                        </a>
                    </div>

                </div>

                <div style='margin-top:20px;font-size:12px;color:#94a3b8;'>
                    Please complete your review within 3 business days.<br>
                    Legal Management System
                </div>

            </div>
        </div>
        ";

        return (new MailMessage)
            ->subject("[" . strtoupper($type) . "] Staff Assignment - " . $this->contract->title)
            ->view('emails.review-assignment', [
                'content' => $content
            ]);
    }

    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'id' => $this->id,
            'type' => 'staff_assigned',
            'title' => 'Staff Assignment',
            'message' => 'You have been assigned to review: ' . $this->contract->title,
            'contract_id' => $this->contract->id,
            'contract_title' => $this->contract->title,
            'department' => $this->department->name,
            'assigned_by' => $this->assignedBy->nama_user ?? 'System',
            'timestamp' => now()->toDateTimeString(),
            'icon' => 'fa-user-check',
            'color' => 'teal'
        ]);
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'staff_assigned',
            'title' => 'Staff Assignment',
            'message' => 'You have been assigned to review document: ' . $this->contract->title,
            'contract_id' => $this->contract->id,
            'contract_title' => $this->contract->title,
            'contract_number' => $this->contract->contract_number,
            'department_id' => $this->department->id,
            'department_name' => $this->department->name,
            'assigned_by' => $this->assignedBy->nama_user ?? 'System',
            'notes' => $this->notes,
            'assigned_at' => now()->toDateTimeString(),
            'action_url' => url('/contracts/' . $this->contract->id),
            'icon' => 'fa-user-check',
            'color' => 'teal'
        ];
    }
}