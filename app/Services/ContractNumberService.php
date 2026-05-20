<?php

namespace App\Services;

use App\Models\Contract;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class ContractNumberService
{
    // Tetap ada sebagai fallback
    private string $companyCode = 'GNI';

    public function setCompanyCode(string $code): self
    {
        $this->companyCode = strtoupper($code);
        return $this;
    }

    public function getCompanyCode(): string
    {
        return $this->companyCode;
    }

    /**
     * Resolve company code dari contract
     * Priority: contract.company_code → fallback 'GNI'
     */
    public function resolveCompanyCode(Contract $contract): string
    {
        if (!empty($contract->company_code)) {
            return strtoupper($contract->company_code);
        }

        return $this->companyCode; // fallback GNI
    }

    /**
     * Convert contract_type ke kode S/K
     */
    private function getDocumentTypeCode(string $contractType): string
    {
        $type = strtolower($contractType);

        if ($type === 'surat') return 'S';
        if ($type === 'kontrak') return 'K';
        if (str_contains($type, 'surat') || str_contains($type, 'letter')) return 'S';

        return 'K';
    }

    /**
     * Resolve department code dari contract atau tbl_user
     */
    public function resolveDepartmentCode(Contract $contract): string
    {
        if (!empty($contract->department_code)) {
            return strtoupper($contract->department_code);
        }

        try {
            $user = $contract->user;

            if ($user && $user->email) {
                $hrmsUser = DB::table('tbl_user')
                    ->where('email', $user->email)
                    ->first(['kode_department']);

                if ($hrmsUser && !empty($hrmsUser->kode_department)) {
                    return strtoupper($hrmsUser->kode_department);
                }
            }
        } catch (\Exception $e) {
            Log::warning('Failed to resolve department from HRMS', [
                'error'       => $e->getMessage(),
                'contract_id' => $contract->id,
            ]);
        }

        Log::warning('Department code fallback to GEN', [
            'contract_id' => $contract->id,
            'user_id'     => $contract->user_id,
        ]);

        return 'GEN';
    }

    /**
     * Generate nomor resmi
     * Format: 001/{dept}-{company}/{type}/{bulanRomawi}/{tahun}
     * Contoh GNI: 001/EXIM-GNI/S/V/2026
     * Contoh AMI: 001/EXIM-AMI/S/V/2026
     */
    public function generateForContract(Contract $contract): string
    {
        $allowedStatuses = [
            Contract::STATUS_UNDER_REVIEW,
            Contract::STATUS_FINAL_APPROVED,
        ];

        if (!in_array($contract->status, $allowedStatuses)) {
            throw new \Exception(
                'The document status must be UNDER REVIEW or FINAL APPROVED. Current status: ' . $contract->status
            );
        }

        if (!empty($contract->contract_number)) {
            throw new \Exception('The document already has a number: ' . $contract->contract_number);
        }

        if (empty($contract->contract_type)) {
            throw new \Exception('contract_type cannot be empty.');
        }

        // Resolve semua komponen
        $departmentCode = $this->resolveDepartmentCode($contract);
        $companyCode    = $this->resolveCompanyCode($contract);  // ← dari contract
        $documentType   = $this->getDocumentTypeCode($contract->contract_type);

        $date = $contract->number_generated_at ?? now();
        if (is_string($date)) $date = Carbon::parse($date);

        $year       = $date->year;
        $romanMonth = $this->toRomanMonth($date->month);
        $sequence   = $this->getNextSequence($departmentCode, $companyCode, $year);

        $contractNumber = sprintf(
            '%03d/%s-%s/%s/%s/%d',
            $sequence,
            $departmentCode,
            $companyCode,   // ← dinamis, bukan hardcoded
            $documentType,
            $romanMonth,
            $year
        );

        Log::info('Contract number generated', [
            'contract_id'     => $contract->id,
            'department_code' => $departmentCode,
            'company_code'    => $companyCode,
            'document_type'   => $documentType,
            'sequence'        => $sequence,
            'final_number'    => $contractNumber,
        ]);

        return $contractNumber;
    }

    /**
     * Get next sequence per department, company, dan tahun
     * Cek dari dua sumber: contracts dan tbl_surat_keluar_legal (HRMS)
     */
    public function getNextSequence(string $departmentCode, string $companyCode, int $year): int
    {
        $pattern = "%/{$departmentCode}-{$companyCode}/%/{$year}";

        // Cek nomor tertinggi di Legal System
        $lastLegal = Contract::whereNotNull('contract_number')
            ->where('contract_number', 'LIKE', $pattern)
            ->orderByRaw("CAST(SUBSTRING_INDEX(contract_number, '/', 1) AS UNSIGNED) DESC")
            ->value('contract_number');

        // Cek nomor tertinggi di HRMS
        $lastHrms = DB::table('tbl_surat_keluar_legal')
            ->whereNotNull('nomor_surat_keluar')
            ->where('nomor_surat_keluar', '!=', '')
            ->where('nomor_surat_keluar', 'LIKE', $pattern)
            ->orderByRaw("CAST(SUBSTRING_INDEX(nomor_surat_keluar, '/', 1) AS UNSIGNED) DESC")
            ->value('nomor_surat_keluar');

        $seqLegal = $lastLegal ? (int) explode('/', $lastLegal)[0] : 0;
        $seqHrms  = $lastHrms  ? (int) explode('/', $lastHrms)[0]  : 0;

        return max($seqLegal, $seqHrms) + 1;
    }

    /**
     * Preview nomor tanpa save
     */
    public function previewNumber(Contract $contract): string
    {
        $departmentCode = $this->resolveDepartmentCode($contract);
        $companyCode    = $this->resolveCompanyCode($contract);  // ← dinamis
        $documentType   = $this->getDocumentTypeCode($contract->contract_type ?? 'kontrak');

        $date = $contract->final_approved_at ?? $contract->submitted_at ?? now();
        if (is_string($date)) $date = Carbon::parse($date);

        $year       = $date->year;
        $romanMonth = $this->toRomanMonth($date->month);
        $sequence   = $this->getNextSequence($departmentCode, $companyCode, $year);

        return sprintf(
            '%s/%s-%s/%s/%s/%d',
            str_pad($sequence, 3, '0', STR_PAD_LEFT),
            $departmentCode,
            $companyCode,
            $documentType,
            $romanMonth,
            $year
        );
    }

    public function toRomanMonth(int $month): string
    {
        return [
            1 => 'I', 2 => 'II', 3 => 'III', 4 => 'IV',
            5 => 'V', 6 => 'VI', 7 => 'VII', 8 => 'VIII',
            9 => 'IX', 10 => 'X', 11 => 'XI', 12 => 'XII',
        ][$month] ?? '-';
    }

    public function canGenerate(Contract $contract): bool
    {
        return $contract->status === Contract::STATUS_UNDER_REVIEW
            && empty($contract->contract_number)
            && !empty($contract->contract_type);
    }

    public function debugNumberComponents(Contract $contract): array
    {
        return [
            'department_code_stored'   => $contract->department_code,
            'department_code_resolved' => $this->resolveDepartmentCode($contract),
            'company_code_stored'      => $contract->company_code,
            'company_code_resolved'    => $this->resolveCompanyCode($contract),
            'contract_type_raw'        => $contract->contract_type,
            'document_type_code'       => $this->getDocumentTypeCode($contract->contract_type ?? 'kontrak'),
            'current_year'             => now()->year,
            'roman_month'              => $this->toRomanMonth(now()->month),
            'preview'                  => $this->previewNumber($contract),
        ];
    }
}