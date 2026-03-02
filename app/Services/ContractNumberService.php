<?php

namespace App\Services;

use App\Models\Contract;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class ContractNumberService
{
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
     * ✅ Convert contract_type ("surat" atau "kontrak") ke kode S/K
     */
    private function getDocumentTypeCode(string $contractType): string
    {
        $type = strtolower($contractType);
        
        if ($type === 'surat') {
            return 'S';
        } elseif ($type === 'kontrak') {
            return 'K';
        }
        
        // Fallback untuk backward compatibility
        if (str_contains($type, 'surat') || str_contains($type, 'letter')) {
            return 'S';
        }
        
        return 'K'; // Default ke Kontrak
    }

    /**
     * 🔥 NEW: Resolve department code dari user profile
     * Priority: 
     * 1. Contract department_code (jika sudah diset)
     * 2. User kode_department dari tbl_user
     * 3. Fallback ke 'GEN' (General)
     */
    public function resolveDepartmentCode(Contract $contract): string
    {
        // 1. Jika contract sudah punya department_code, pakai itu
        if (!empty($contract->department_code)) {
            return strtoupper($contract->department_code);
        }
        
        // 2. Coba ambil dari user profile (tbl_user.kode_department via email match)
        try {
            $user = $contract->user; // Eloquent relationship
            
            if ($user && $user->email) {
                // Query ke tbl_user HRMS untuk ambil kode_department
                $hrmsUser = DB::table('tbl_user')
                    ->where('email', $user->email)
                    ->first(['kode_department']);
                
                if ($hrmsUser && !empty($hrmsUser->kode_department)) {
                    Log::info('Department code resolved from HRMS', [
                        'user_email' => $user->email,
                        'kode_department' => $hrmsUser->kode_department,
                        'contract_id' => $contract->id,
                    ]);
                    
                    return strtoupper($hrmsUser->kode_department);
                }
            }
        } catch (\Exception $e) {
            Log::warning('Failed to resolve department from HRMS', [
                'error' => $e->getMessage(),
                'contract_id' => $contract->id,
            ]);
        }
        
        // 3. Fallback ke GEN jika tidak ada data
        Log::warning('Department code fallback to GEN', [
            'contract_id' => $contract->id,
            'user_id' => $contract->user_id,
            'reason' => 'No department code found in contract or HRMS',
        ]);
        
        return 'GEN';
    }

    /**
     * 🔥 MAIN METHOD: Generate nomor kontrak resmi
     * Format: {sequence}/{department}-GNI/{type}/{romanMonth}/{year}
     * Contoh: 001/ITE-GNI/K/I/2024
     */
    public function generateForContract(Contract $contract): string
    {
        // ===============================
        // 1. VALIDASI STRICT
        // ===============================
        if ($contract->status !== Contract::STATUS_FINAL_APPROVED) {
            throw new \Exception(
                'Contract must be FINAL APPROVED. Current status: ' . $contract->status
            );
        }
        
        if (!empty($contract->contract_number)) {
            throw new \Exception(
                'Contract already has number: ' . $contract->contract_number
            );
        }
        
        // ✅ VALIDASI: contract_type WAJIB ADA
        if (empty($contract->contract_type)) {
            throw new \Exception('Contract missing contract_type. Please ensure document type was selected during creation.');
        }

        // ===============================
        // 2. GET ALL COMPONENTS
        // ===============================
        
        // 🔥 RESOLVE DEPARTMENT CODE (otomatis dari user atau fallback)
        $departmentCode = $this->resolveDepartmentCode($contract);
        
        $documentType = $this->getDocumentTypeCode($contract->contract_type);
        
        // Gunakan final_approved_at jika ada, atau sekarang
        $date = $contract->final_approved_at ?? now();
        if (is_string($date)) {
            $date = Carbon::parse($date);
        }
        
        $year = $date->year;
        $month = $date->month;
        $romanMonth = $this->toRomanMonth($month);
        
        // ===============================
        // 3. GET NEXT SEQUENCE NUMBER
        // ===============================
        $sequence = $this->getNextSequence($departmentCode, $year);
        
        // ===============================
        // 4. BUILD CONTRACT NUMBER
        // ===============================
        $contractNumber = sprintf(
            '%03d/%s-%s/%s/%s/%d',
            $sequence,
            $departmentCode,
            $this->companyCode,
            $documentType,
            $romanMonth,
            $year
        );

        Log::info('Contract number generated', [
            'contract_id' => $contract->id,
            'department_code' => $departmentCode,
            'department_resolved_from' => empty($contract->department_code) ? 'HRMS' : 'contract',
            'contract_type_raw' => $contract->contract_type,
            'document_type_code' => $documentType,
            'year' => $year,
            'month' => $month,
            'roman_month' => $romanMonth,
            'sequence' => $sequence,
            'final_number' => $contractNumber,
        ]);

        return $contractNumber;
    }

    /**
     * Get next sequence number per department per year
     */
    public function getNextSequence(string $departmentCode, int $year): int{
        // 🔥 documentType DIHAPUS dari pattern
        $pattern = "%/{$departmentCode}-{$this->companyCode}/%/{$year}";

        $lastContract = Contract::whereNotNull('contract_number')
            ->where('contract_number', 'LIKE', $pattern)
            ->orderByRaw(
                "CAST(SUBSTRING_INDEX(contract_number, '/', 1) AS UNSIGNED) DESC"
            )
            ->first();

        if ($lastContract && $lastContract->contract_number) {
            $parts = explode('/', $lastContract->contract_number);
            return ((int) $parts[0]) + 1;
        }

        return 1;
    }


    /**
     * Convert bulan angka ke romawi (I-XII)
     */
    public function toRomanMonth(int $month): string
    {
        $map = [
            1 => 'I', 2 => 'II', 3 => 'III', 4 => 'IV',
            5 => 'V', 6 => 'VI', 7 => 'VII', 8 => 'VIII',
            9 => 'IX', 10 => 'X', 11 => 'XI', 12 => 'XII',
        ];

        return $map[$month] ?? '-';
    }

    /**
     * Check if contract can have number generated
     */
    public function canGenerate(Contract $contract): bool
    {
        return $contract->status === Contract::STATUS_FINAL_APPROVED 
            && empty($contract->contract_number)
            && !empty($contract->contract_type);
    }

    /**
     * Preview nomor kontrak tanpa save (untuk display info)
     */
    public function previewNumber(Contract $contract): string
    {
        // 🔥 RESOLVE DEPARTMENT CODE
        $departmentCode = $this->resolveDepartmentCode($contract);
        $documentType = $this->getDocumentTypeCode($contract->contract_type ?? 'kontrak');
        
        $date = $contract->final_approved_at ?? $contract->submitted_at ?? now();
        if (is_string($date)) {
            $date = Carbon::parse($date);
        }
        
        $year = $date->year;
        $month = $date->month;
        $romanMonth = $this->toRomanMonth($month);
        
        $sequence = $this->getNextSequence($departmentCode, $year);
        $paddedSequence = str_pad($sequence, 3, '0', STR_PAD_LEFT);
        
        return sprintf(
            '%s/%s-%s/%s/%s/%d',
            $paddedSequence,
            $departmentCode,
            $this->companyCode,
            $documentType,
            $romanMonth,
            $year
        );
    }

    /**
     * Debug: Show all components for a contract
     */
    public function debugNumberComponents(Contract $contract): array
    {
        return [
            'department_code_stored' => $contract->department_code,
            'department_code_resolved' => $this->resolveDepartmentCode($contract),
            'contract_type_raw' => $contract->contract_type,
            'document_type_code' => $this->getDocumentTypeCode($contract->contract_type ?? 'kontrak'),
            'current_year' => now()->year,
            'current_month' => now()->month,
            'roman_month' => $this->toRomanMonth(now()->month),
            'company_code' => $this->companyCode,
            'can_generate' => $this->canGenerate($contract),
            'preview' => $this->previewNumber($contract),
        ];
    }
}