<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * ContractReviewStartedNotification
 *
 * Dikirim ke:
 *  - Semua legal reviewer yang terdaftar di workflow
 *  - Contract owner (document owner)
 *
 * Menampilkan seluruh daftar reviewer dalam satu email.
 *
 * Constructor:
 *   $contract   — model Contract
 *   $startedBy  — TblUser yang memulai review
 *   $reviewTeam — array [ ['name'=>'...', 'email'=>'...', 'stage'=>'...'] ]
 *                 (opsional, backward-compatible jika tidak dikirim)
 */
class ContractReviewStartedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $contract;
    protected $startedBy;
    protected array $reviewTeam;

    public function __construct($contract, $startedBy, array $reviewTeam = [])
    {
        $this->contract   = $contract;
        $this->startedBy  = $startedBy;
        $this->reviewTeam = $reviewTeam;
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

        $estimatedDate = now()->addDays(10)->format('d M Y');

        // ── Reviewer team table ───────────────────────────────────────────
        $teamSection = '';
        if (!empty($this->reviewTeam)) {
            $rows = '';
            foreach ($this->reviewTeam as $member) {
                $rows .= "
                    <tr>
                        <td style='padding:8px 12px;font-size:13px;color:#374151;border-bottom:1px solid #f1f5f9;'>
                            " . e($member['name'] ?? '—') . "
                        </td>
                        <td style='padding:8px 12px;font-size:13px;color:#6b7280;border-bottom:1px solid #f1f5f9;'>
                            " . e($member['stage'] ?? '—') . "
                        </td>
                    </tr>";
            }
            $teamSection = "
                <div style='margin-top:20px;'>
                    <p style='margin:0 0 8px;font-size:12px;font-weight:700;
                              text-transform:uppercase;letter-spacing:.05em;color:#64748b;'>
                        Review Team
                    </p>
                    <table style='width:100%;border-collapse:collapse;
                                  border:1px solid #e5e7eb;border-radius:8px;overflow:hidden;'>
                        <thead>
                            <tr style='background:#f8fafc;'>
                                <th style='padding:8px 12px;text-align:left;font-size:11px;
                                           font-weight:700;color:#94a3b8;text-transform:uppercase;'>
                                    Reviewer
                                </th>
                                <th style='padding:8px 12px;text-align:left;font-size:11px;
                                           font-weight:700;color:#94a3b8;text-transform:uppercase;'>
                                    Stage
                                </th>
                            </tr>
                        </thead>
                        <tbody>{$rows}</tbody>
                    </table>
                </div>";
        }

        $content = "
        <div style='font-family:Arial,Helvetica,sans-serif;background:#f4f6f9;padding:40px 20px;'>
            <div style='max-width:640px;'>
                <div style='background:#ffffff;padding:32px;border-radius:8px;'>

                    <h2 style='margin:0 0 20px 0;font-size:20px;'>
                        Review Process Started
                    </h2>

                    <span style='display:inline-block;padding:6px 12px;font-size:12px;
                                 font-weight:600;border-radius:20px;
                                 background:{$badgeBg};color:{$badgeText};margin-bottom:20px;'>
                        {$badgeLabel}
                    </span>

                    <p>Hello <strong>{$notifiable->nama_user}</strong>,</p>
                    <p>The review process for the following document has been initiated.</p>

                    <hr style='border:none;border-top:1px solid #e5e7eb;margin:20px 0;'>

                    <p><strong>Document Title</strong><br>{$this->contract->title}</p>

                    <p><strong>Document Number</strong><br>"
                        . ($this->contract->contract_number ?? 'Not yet assigned') . "</p>

                    <p><strong>Started By</strong><br>{$this->startedBy->nama_user}</p>

                    <p><strong>Estimated Completion</strong><br>{$estimatedDate}</p>

                    {$teamSection}

                    <div style='margin-top:28px;'>
                        <a href='{$url}'
                           style='display:inline-block;padding:10px 18px;
                                  background:#111827;color:#ffffff;
                                  text-decoration:none;border-radius:6px;font-size:14px;'>
                            View Document
                        </a>
                    </div>

                </div>
                <div style='margin-top:20px;font-size:12px;color:#94a3b8;'>
                    You will receive updates as the review progresses.<br>
                    Legal Management System
                </div>
            </div>
        </div>";

        return (new MailMessage)
            ->subject("[{$badgeLabel}] Review Started — {$this->contract->title}")
            ->view('emails.review-assignment', ['content' => $content]);
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type'            => 'review_started',
            'title'           => 'Document Review Started',
            'message'         => 'Review process started for: ' . $this->contract->title,
            'contract_id'     => $this->contract->id,
            'contract_title'  => $this->contract->title,
            'contract_number' => $this->contract->contract_number ?? 'N/A',
            'started_by'      => $this->startedBy->nama_user,
            'reviewer_count'  => count($this->reviewTeam),
            'started_at'      => now()->toDateTimeString(),
            'action_url'      => $this->resolveUrl(),
            'icon'            => 'fa-play-circle',
            'color'           => 'blue',
        ];
    }

    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'id'             => $this->id,
            'type'           => 'review_started',
            'title'          => 'Document Review Started',
            'message'        => 'Review started for: ' . $this->contract->title,
            'contract_id'    => $this->contract->id,
            'contract_title' => $this->contract->title,
            'started_by'     => $this->startedBy->nama_user,
            'timestamp'      => now()->toDateTimeString(),
            'icon'           => 'fa-play-circle',
            'color'          => 'blue',
        ]);
    }
}