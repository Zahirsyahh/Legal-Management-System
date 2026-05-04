<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * DeadlineReminderNotification
 *
 * Dikirim ke semua reviewer yang terlibat di contract ketika:
 *  - 3 hari sebelum drafting_deadline  → $daysLeft = 3
 *  - 1 hari sebelum drafting_deadline  → $daysLeft = 1
 *
 * Constructor:
 *   $contract   — model Contract
 *   $daysLeft   — int (3 atau 1)
 */
class DeadlineReminderNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $contract;
    protected int $daysLeft;

    public function __construct($contract, int $daysLeft)
    {
        $this->contract = $contract;
        $this->daysLeft = $daysLeft;
    }

    public function via($notifiable): array
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

    /**
     * Tentukan warna & label urgensi berdasarkan sisa hari
     */
    protected function urgencyConfig(): array
    {
        if ($this->daysLeft <= 1) {
            return [
                'color'        => '#dc2626',   // merah
                'badge_bg'     => '#fef2f2',
                'badge_text'   => '#dc2626',
                'badge_label'  => '⚠️ URGENT – 1 DAY LEFT',
                'subject_pfx'  => '🚨 URGENT',
                'icon'         => 'fa-exclamation-circle',
                'db_color'     => 'red',
                'headline'     => '⚠️ Deadline Tomorrow!',
                'body_note'    => 'This document expires <strong>tomorrow</strong>. Please complete your review immediately.',
            ];
        }

        return [
            'color'        => '#d97706',   // amber
            'badge_bg'     => '#fffbeb',
            'badge_text'   => '#d97706',
            'badge_label'  => '⏰ 3 DAYS LEFT',
            'subject_pfx'  => '⏰ Reminder',
            'icon'         => 'fa-clock',
            'db_color'     => 'yellow',
            'headline'     => '⏰ Deadline in 3 Days',
            'body_note'    => 'This document\'s expected deadline is <strong>3 days away</strong>. Please ensure your review is completed on time.',
        ];
    }

    public function toMail($notifiable): MailMessage
    {
        $url    = $this->resolveUrl();
        $cfg    = $this->urgencyConfig();
        $type   = strtoupper($this->contract->contract_type ?? 'DOC');

        $deadline = $this->contract->drafting_deadline
            ? \Carbon\Carbon::parse($this->contract->drafting_deadline)->format('d M Y')
            : 'N/A';

        // Daftar reviewer stages yang aktif
        $stageRows = '';
        $stages = $this->contract->reviewStages()
            ->with('assignedUser:id_user,nama_user')
            ->whereIn('status', ['in_progress', 'assigned', 'pending'])
            ->orderBy('sequence')
            ->get();

        foreach ($stages as $stage) {
            $assignee   = $stage->assignedUser?->nama_user ?? 'Unassigned';
            $statusBadge = match ($stage->status) {
                'in_progress' => "<span style='color:#2563eb;font-weight:600;'>In Progress</span>",
                'assigned'    => "<span style='color:#d97706;font-weight:600;'>Assigned</span>",
                default       => "<span style='color:#6b7280;'>Pending</span>",
            };

            $stageRows .= "
                <tr>
                    <td style='padding:7px 10px;font-size:13px;color:#374151;
                               border-bottom:1px solid #f3f4f6;'>
                        {$stage->stage_name}
                    </td>
                    <td style='padding:7px 10px;font-size:13px;color:#6b7280;
                               border-bottom:1px solid #f3f4f6;'>
                        {$assignee}
                    </td>
                    <td style='padding:7px 10px;font-size:13px;
                               border-bottom:1px solid #f3f4f6;'>
                        {$statusBadge}
                    </td>
                </tr>";
        }

        $stagesSection = $stageRows ? "
            <div style='margin-top:20px;'>
                <p style='margin:0 0 8px;font-size:12px;font-weight:700;
                          text-transform:uppercase;letter-spacing:.05em;color:#374151;'>
                    Active Review Stages
                </p>
                <table style='width:100%;border-collapse:collapse;
                              border:1px solid #e5e7eb;border-radius:6px;overflow:hidden;'>
                    <thead>
                        <tr style='background:#f9fafb;'>
                            <th style='padding:7px 10px;font-size:12px;color:#6b7280;
                                       text-align:left;font-weight:600;'>Stage</th>
                            <th style='padding:7px 10px;font-size:12px;color:#6b7280;
                                       text-align:left;font-weight:600;'>Reviewer</th>
                            <th style='padding:7px 10px;font-size:12px;color:#6b7280;
                                       text-align:left;font-weight:600;'>Status</th>
                        </tr>
                    </thead>
                    <tbody>{$stageRows}</tbody>
                </table>
            </div>" : '';

        $content = "
        <div style='font-family:Arial,Helvetica,sans-serif;background:#f4f6f9;padding:40px 20px;'>
            <div style='max-width:640px;'>
                <div style='background:#ffffff;padding:32px;border-radius:8px;
                            border-top:4px solid {$cfg['color']};'>

                    <h2 style='margin:0 0 20px 0;font-size:20px;color:{$cfg['color']};'>
                        {$cfg['headline']}
                    </h2>

                    <span style='display:inline-block;padding:6px 14px;font-size:12px;
                                 font-weight:700;border-radius:20px;letter-spacing:.04em;
                                 background:{$cfg['badge_bg']};color:{$cfg['badge_text']};
                                 margin-bottom:20px;'>
                        {$cfg['badge_label']}
                    </span>

                    <p>Hello <strong>{$notifiable->nama_user}</strong>,</p>
                    <p>{$cfg['body_note']}</p>

                    <hr style='border:none;border-top:1px solid #e5e7eb;margin:20px 0;'>

                    <!-- Document Details -->
                    <table style='width:100%;border-collapse:collapse;'>
                        <tr>
                            <td style='padding:6px 0;font-size:13px;color:#6b7280;width:40%;'>
                                Document Title
                            </td>
                            <td style='padding:6px 0;font-size:13px;color:#111827;font-weight:600;'>
                                {$this->contract->title}
                            </td>
                        </tr>
                        <tr>
                            <td style='padding:6px 0;font-size:13px;color:#6b7280;'>
                                Contract Number
                            </td>
                            <td style='padding:6px 0;font-size:13px;color:#111827;font-family:monospace;'>
                                " . ($this->contract->contract_number ?? '<em style=\"color:#9ca3af;\">Not yet issued</em>') . "
                            </td>
                        </tr>
                        <tr>
                            <td style='padding:6px 0;font-size:13px;color:#6b7280;'>
                                Type
                            </td>
                            <td style='padding:6px 0;font-size:13px;color:#111827;'>
                                {$type}
                            </td>
                        </tr>
                        <tr>
                            <td style='padding:6px 0;font-size:13px;color:#6b7280;'>
                                Counterparty
                            </td>
                            <td style='padding:6px 0;font-size:13px;color:#111827;'>
                                " . ($this->contract->counterparty_name ?? '-') . "
                            </td>
                        </tr>
                        <tr>
                            <td style='padding:6px 0;font-size:13px;color:#6b7280;'>
                                Requested By
                            </td>
                            <td style='padding:6px 0;font-size:13px;color:#111827;'>
                                " . ($this->contract->user?->nama_user ?? '-') . "
                            </td>
                        </tr>
                        <tr>
                            <td style='padding:6px 0;font-size:13px;color:#6b7280;'>
                                Current Status
                            </td>
                            <td style='padding:6px 0;font-size:13px;color:#111827;'>
                                " . ucwords(str_replace('_', ' ', $this->contract->status)) . "
                            </td>
                        </tr>
                        <tr>
                            <td style='padding:6px 0;font-size:13px;color:#6b7280;'>Expected Deadline</td>
                            <td style='padding:6px 0;font-size:16px;font-weight:700;color:{$cfg['color']};'>
                                📅 {$deadline}
                            </td>
                        </tr>
                    </table>

                    {$stagesSection}

                    <div style='margin-top:24px;padding:14px 16px;border-radius:6px;
                                background:{$cfg['badge_bg']};border:1px solid;
                                border-color:{$cfg['color']}40;'>
                        <p style='margin:0;font-size:13px;color:{$cfg['color']};'>
                            <strong>Action Required:</strong>
                            Please log in and complete your pending review tasks before the deadline.
                        </p>
                    </div>

                    <div style='margin-top:24px;'>
                        <a href='{$url}'
                           style='display:inline-block;padding:10px 20px;font-size:14px;
                                  font-weight:600;color:#ffffff;text-decoration:none;
                                  background:{$cfg['color']};border-radius:6px;'>
                            View Document →
                        </a>
                    </div>

                </div>
                <div style='margin-top:16px;font-size:11px;color:#94a3b8;text-align:center;'>
                    This is an automated deadline reminder from the Legal Management System.
                </div>
            </div>
        </div>";

        return (new MailMessage)
            ->subject("{$cfg['subject_pfx']}: Deadline in {$this->daysLeft} day(s) — {$this->contract->title}")
            ->view('emails.review-assignment', ['content' => $content]);
    }

    public function toArray($notifiable): array
    {
        $deadline = $this->contract->drafting_deadline
            ? \Carbon\Carbon::parse($this->contract->drafting_deadline)->format('d M Y')
            : 'N/A';

        $cfg = $this->urgencyConfig();

        return [
            'type'            => 'deadline_reminder',
            'title'           => $this->daysLeft <= 1
                ? '⚠️ Deadline Tomorrow!'
                : '⏰ Deadline in 3 Days',
            'message'         => "Document \"{$this->contract->title}\" has a deadline on {$deadline} ({$this->daysLeft} day(s) remaining).",
            'contract_id'     => $this->contract->id,
            'contract_title'  => $this->contract->title,
            'contract_number' => $this->contract->contract_number,
            'contract_type'   => $this->contract->contract_type,
            'days_left'       => $this->daysLeft,
            'deadline_date'   => $deadline,
            'requested_by'    => $this->contract->user?->nama_user ?? null,
            'action_url'      => $this->resolveUrl(),
            'icon'            => $cfg['icon'],
            'color'           => $cfg['db_color'],
        ];
    }

    public function toBroadcast($notifiable): BroadcastMessage
    {
        $cfg = $this->urgencyConfig();

        return new BroadcastMessage([
            'id'             => $this->id,
            'type'           => 'deadline_reminder',
            'title'          => $this->daysLeft <= 1
                ? '⚠️ Deadline Tomorrow!'
                : '⏰ Deadline in 3 Days',
            'message'        => "\"{$this->contract->title}\" — {$this->daysLeft} day(s) left.",
            'contract_id'    => $this->contract->id,
            'contract_title' => $this->contract->title,
            'days_left'      => $this->daysLeft,
            'icon'           => $cfg['icon'],
            'color'          => $cfg['db_color'],
            'action_url'     => $this->resolveUrl(),
            'timestamp'      => now()->toDateTimeString(),
        ]);
    }
}