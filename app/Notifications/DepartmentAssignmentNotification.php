<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * DepartmentAssignmentNotification
 *
 * Dikirim ke DEPARTMENT ADMIN (admin_fin, admin_acc, admin_tax)
 * ketika departemen mereka dimasukkan ke dalam substantial review workflow.
 *
 * Admin perlu segera menugaskan staff reviewer dari departemennya.
 */
class DepartmentAssignmentNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $contract;
    protected $department;
    protected $assignedBy;

    public function __construct($contract, $department, $assignedBy = null)
    {
        $this->contract   = $contract;
        $this->department = $department;
        $this->assignedBy = $assignedBy;
    }

    public function via(object $notifiable): array
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

    public function toMail(object $notifiable): MailMessage
    {
        $type       = $this->contract->contract_type ?? 'contract';
        $url        = $this->resolveUrl();

        $badgeBg    = $type === 'surat' ? '#e6f9f0' : '#e8f1ff';
        $badgeText  = $type === 'surat' ? '#0f9d58' : '#2563eb';
        $badgeLabel = strtoupper($type);

        $dueDate = now()->addDays(2)->format('d M Y');

        $content = "
        <div style='font-family:Arial,Helvetica,sans-serif;background:#f4f6f9;padding:40px 20px;'>
            <div style='max-width:640px;'>
                <div style='background:#ffffff;padding:32px;border-radius:8px;'>

                    <h2 style='margin:0 0 20px 0;font-size:20px;color:#7c3aed;'>
                        🏢 Department Review Assignment
                    </h2>

                    <span style='display:inline-block;padding:6px 12px;font-size:12px;
                                 font-weight:600;border-radius:20px;
                                 background:{$badgeBg};color:{$badgeText};margin-bottom:20px;'>
                        {$badgeLabel}
                    </span>

                    <p>Hello <strong>{$notifiable->nama_user}</strong>,</p>

                    <p>
                        Your department (<strong>{$this->department->name}</strong>) has been
                        included in a substantial review workflow. As department admin,
                        please assign a reviewer to review this document.
                    </p>

                    <hr style='border:none;border-top:1px solid #e5e7eb;margin:20px 0;'>

                    <p><strong>Document Title</strong><br>{$this->contract->title}</p>

                    <p><strong>Document Number</strong><br>"
                        . ($this->contract->contract_number ?? 'Not yet assigned') . "</p>

                    <p><strong>Department</strong><br>{$this->department->name}</p>

                    <p><strong>Assigned By (Legal)</strong><br>"
                        . ($this->assignedBy?->nama_user ?? 'System') . "</p>

                    <p><strong>Assignment Deadline</strong><br>{$dueDate}</p>

                    <div style='margin-top:16px;padding:12px 16px;background:#fef3c7;
                                border-left:4px solid #f59e0b;border-radius:4px;
                                font-size:13px;color:#92400e;'>
                        <strong>Action Required:</strong> Please assign a staff reviewer
                        from your department within 2 business days.
                    </div>

                    <div style='margin-top:28px;'>
                        <a href='{$url}'
                           style='display:inline-block;padding:10px 18px;
                                  background:#7c3aed;color:#ffffff;
                                  text-decoration:none;border-radius:6px;font-size:14px;'>
                            Assign Reviewer
                        </a>
                    </div>

                </div>
                <div style='margin-top:20px;font-size:12px;color:#94a3b8;'>
                    This notification is intended for department admins only.<br>
                    Legal Management System
                </div>
            </div>
        </div>";

        return (new MailMessage)
            ->subject("[{$badgeLabel}] Action Required: Assign Reviewer — {$this->contract->title}")
            ->view('emails.review-assignment', ['content' => $content]);
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type'            => 'department_assignment',
            'title'           => 'Department Review Assignment',
            'message'         => 'Your department (' . $this->department->name
                . ') needs a reviewer for: ' . $this->contract->title,
            'contract_id'     => $this->contract->id,
            'contract_title'  => $this->contract->title,
            'contract_number' => $this->contract->contract_number ?? 'N/A',
            'department_id'   => $this->department->id,
            'department_name' => $this->department->name,
            'assigned_by'     => $this->assignedBy?->nama_user ?? 'System',
            'assigned_at'     => now()->toDateTimeString(),
            'action_url'      => $this->resolveUrl(),
            'icon'            => 'fa-building',
            'color'           => 'purple',
        ];
    }

    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'id'              => $this->id,
            'type'            => 'department_assignment',
            'title'           => 'Department Review Assignment',
            'message'         => 'Assign a reviewer for: ' . $this->contract->title,
            'contract_id'     => $this->contract->id,
            'contract_title'  => $this->contract->title,
            'department'      => $this->department->name,
            'assigned_by'     => $this->assignedBy?->nama_user ?? 'System',
            'timestamp'       => now()->toDateTimeString(),
            'icon'            => 'fa-building',
            'color'           => 'purple',
        ]);
    }
}