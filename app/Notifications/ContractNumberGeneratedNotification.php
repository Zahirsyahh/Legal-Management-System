<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * ContractNumberGeneratedNotification
 *
 * Dikirim ke:
 *  - Document owner
 *  - Semua legal reviewer yang terlibat di workflow
 *
 * Menginformasikan:
 *  - Nomor dokumen yang sudah digenerate
 *  - Nama user yang akan menangani Executing (document owner)
 *  - Nama legal yang akan menangani Archiving
 *  - Ajakan/himbauan untuk segera memulai proses executing & archiving
 *
 * Constructor:
 *   $contract         — model Contract
 *   $contractNumber   — string nomor dokumen
 *   $actor            — TblUser yang generate nomor
 *   $executingUser    — TblUser assigned ke executing stage (opsional)
 *   $archivingUser    — TblUser assigned ke archiving stage (opsional)
 */
class ContractNumberGeneratedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $contract;
    protected string $contractNumber;
    protected $actor;
    protected $executingUser;
    protected $archivingUser;

    public function __construct(
        $contract,
        string $contractNumber,
        $actor         = null,
        $executingUser = null,
        $archivingUser = null
    ) {
        $this->contract       = $contract;
        $this->contractNumber = $contractNumber;
        $this->actor          = $actor;
        $this->executingUser  = $executingUser;
        $this->archivingUser  = $archivingUser;
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

    public function toMail($notifiable): MailMessage
    {
        $type       = $this->contract->contract_type ?? 'contract';
        $url        = $this->resolveUrl();

        $badgeBg    = $type === 'surat' ? '#e6f9f0' : '#e8f1ff';
        $badgeText  = $type === 'surat' ? '#0f9d58' : '#2563eb';
        $badgeLabel = strtoupper($type);

        // ── Next steps: Executing & Archiving ────────────────────────────
        $executingName = $this->executingUser?->nama_user ?? 'Document Owner';
        $archivingName = $this->archivingUser?->nama_user ?? 'Legal Team';

        $nextStepsSection = "
            <div style='margin-top:20px;padding:16px;background:#f0fdf4;
                        border:1px solid #bbf7d0;border-radius:8px;'>
                <p style='margin:0 0 10px;font-size:12px;font-weight:700;
                          text-transform:uppercase;letter-spacing:.05em;color:#166534;'>
                    Next Steps
                </p>
                <table style='width:100%;border-collapse:collapse;'>
                    <tr>
                        <td style='padding:8px 0;font-size:13px;color:#374151;
                                   border-bottom:1px solid #d1fae5;width:40%;font-weight:600;'>
                            ✍️ Executing — Document Signing
                        </td>
                        <td style='padding:8px 0;font-size:13px;color:#6b7280;
                                   border-bottom:1px solid #d1fae5;'>
                            {$executingName}
                        </td>
                    </tr>
                    <tr>
                        <td style='padding:8px 0;font-size:13px;color:#374151;font-weight:600;'>
                            🗂️ Archiving — Final Document Storage
                        </td>
                        <td style='padding:8px 0;font-size:13px;color:#6b7280;'>
                            {$archivingName}
                        </td>
                    </tr>
                </table>
                <p style='margin:10px 0 0;font-size:12px;color:#166534;'>
                    The executing stage is now active. <strong>{$executingName}</strong>
                    will be notified to proceed with document signing.
                    Once complete, <strong>{$archivingName}</strong> will handle final archiving.
                </p>
            </div>";

        $content = "
        <div style='font-family:Arial,Helvetica,sans-serif;background:#f4f6f9;padding:40px 20px;'>
            <div style='max-width:640px;'>
                <div style='background:#ffffff;padding:32px;border-radius:8px;'>

                    <h2 style='margin:0 0 20px 0;font-size:20px;color:#059669;'>
                        ✅ Document Number Generated
                    </h2>

                    <span style='display:inline-block;padding:6px 12px;font-size:12px;
                                 font-weight:600;border-radius:20px;
                                 background:{$badgeBg};color:{$badgeText};margin-bottom:20px;'>
                        {$badgeLabel}
                    </span>

                    <p>Hello <strong>{$notifiable->nama_user}</strong>,</p>
                    <p>The review process has been completed and an official number
                       has been generated for this document.</p>

                    <hr style='border:none;border-top:1px solid #e5e7eb;margin:20px 0;'>

                    <p><strong>Document Title</strong><br>{$this->contract->title}</p>

                    <p><strong>Official Document Number</strong><br>
                        <span style='font-family:monospace;font-weight:bold;font-size:16px;
                                     color:#059669;letter-spacing:.05em;'>
                            {$this->contractNumber}
                        </span>
                    </p>

                    <p><strong>Generated By</strong><br>"
                        . ($this->actor?->nama_user ?? 'System') . "</p>

                    <p><strong>Generated At</strong><br>"
                        . now()->format('d M Y H:i') . "</p>

                    {$nextStepsSection}

                    <div style='margin-top:28px;'>
                        <a href='{$url}'
                           style='display:inline-block;padding:10px 18px;
                                  background:#059669;color:#ffffff;
                                  text-decoration:none;border-radius:6px;font-size:14px;'>
                            View Document
                        </a>
                    </div>

                </div>
                <div style='margin-top:20px;font-size:12px;color:#94a3b8;'>
                    The document is now finalized and ready for execution.<br>
                    Legal Management System
                </div>
            </div>
        </div>";

        return (new MailMessage)
            ->subject("[{$badgeLabel}] Document Number Generated — {$this->contractNumber}")
            ->view('emails.review-assignment', ['content' => $content]);
    }

    public function toArray($notifiable): array
    {
        return [
            'type'             => 'document_number_generated',
            'title'            => 'Document Number Generated',
            'message'          => 'Number generated: ' . $this->contractNumber
                . '. Executing: ' . ($this->executingUser?->nama_user ?? 'Owner')
                . ', Archiving: ' . ($this->archivingUser?->nama_user ?? 'Legal'),
            'contract_id'      => $this->contract->id,
            'contract_title'   => $this->contract->title,
            'contract_number'  => $this->contractNumber,
            'contract_type'    => $this->contract->contract_type,
            'generated_by'     => $this->actor?->nama_user ?? 'System',
            'executing_user'   => $this->executingUser?->nama_user ?? null,
            'archiving_user'   => $this->archivingUser?->nama_user ?? null,
            'generated_at'     => now()->toDateTimeString(),
            'action_url'       => $this->resolveUrl(),
            'icon'             => 'fa-check-circle',
            'color'            => 'green',
        ];
    }

    public function toBroadcast($notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'id'              => $this->id,
            'type'            => 'document_number_generated',
            'title'           => 'Document Number Generated',
            'message'         => 'Number: ' . $this->contractNumber
                . ' — Executing & Archiving process begins.',
            'contract_id'     => $this->contract->id,
            'contract_title'  => $this->contract->title,
            'contract_number' => $this->contractNumber,
            'executing_user'  => $this->executingUser?->nama_user ?? null,
            'archiving_user'  => $this->archivingUser?->nama_user ?? null,
            'generated_by'    => $this->actor?->nama_user ?? 'System',
            'timestamp'       => now()->toDateTimeString(),
            'icon'            => 'fa-check-circle',
            'color'           => 'green',
            'action_url'      => $this->resolveUrl(),
        ]);
    }
}