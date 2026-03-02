<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\BroadcastMessage;

class DepartmentAssignmentNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $contract;
    protected $department;
    protected $assignedBy;

    public function __construct($contract, $department, $assignedBy = null)
    {
        $this->contract = $contract;
        $this->department = $department;
        $this->assignedBy = $assignedBy;
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

        $dueDate = now()->addDays(2)->format('d M Y');

        $content = "
        <div style='font-family:Arial,Helvetica,sans-serif;background:#f4f6f9;padding:40px 20px;'>
            <div style='max-width:640px;'>

                <div style='background:#ffffff;padding:32px;border-radius:8px;'>

                    <h2 style='margin:0 0 20px 0;font-size:20px;'>
                        Department Assignment
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

                    <p>A document has been assigned to your department for review.</p>

                    <hr style='border:none;border-top:1px solid #e5e7eb;margin:20px 0;'>

                    <p><strong>Title</strong><br>{$this->contract->title}</p>

                    <p><strong>Document Number</strong><br>" . ($this->contract->contract_number ?? 'Not assigned yet') . "</p>

                    <p><strong>Department</strong><br>{$this->department->name}</p>

                    <p><strong>Assigned By</strong><br>" . ($this->assignedBy?->nama_user ?? 'System') . "</p>

                    <p><strong>Due Date</strong><br>{$dueDate}</p>

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
                    Please assign a staff member to review this document within 2 business days.<br>
                    Legal Management System
                </div>

            </div>
        </div>
        ";

        return (new MailMessage)
            ->subject("[" . strtoupper($type) . "] Department Assignment - " . $this->contract->title)
            ->view('emails.review-assignment', [
                'content' => $content
            ]);
    }
    
    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'id' => $this->id,
            'type' => 'department_assignment',
            'title' => 'Department Assignment',
            'message' => 'Document assigned to ' . $this->department->name . ' department',
            'contract_id' => $this->contract->id,
            'contract_title' => $this->contract->title,
            'department' => $this->department->name,
            'assigned_by' => $this->assignedBy?->nama_user ?? 'System',
            'timestamp' => now()->toDateTimeString(),
            'icon' => 'fa-building',
            'color' => 'purple'
        ]);
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'department_assignment',
            'title' => 'Department Assignment',
            'message' => 'Document assigned to ' . $this->department->name . ' department',
            'contract_id' => $this->contract->id,
            'contract_title' => $this->contract->title,
            'contract_number' => $this->contract->contract_number,
            'department_id' => $this->department->id,
            'department_name' => $this->department->name,
            'assigned_by' => $this->assignedBy?->nama_user ?? 'System',
            'assigned_at' => now()->toDateTimeString(),
            'action_url' => url('/contracts/' . $this->contract->id),
            'icon' => 'fa-building',
            'color' => 'purple'
        ];
    }
}