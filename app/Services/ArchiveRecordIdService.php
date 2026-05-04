<?php

namespace App\Services;

use App\Models\Archive;

class ArchiveRecordIdService
{
    /**
     * Generate Record ID
     * Format: CC YY DD TT NNN
     *   CC  = Company (GNI / AMI)
     *   YY  = 2-digit year (e.g. 26 for 2026)
     *   DD  = Department code
     *   TT  = Doc type
     *   NNN = 3-digit sequential number
     */
    public function generate(string $company, string $year, string $departmentCode, string $docType): string
    {
        // Normalise year → always 2 digits
        // Accepts "2026", "26", etc.
        $yearShort = strlen($year) === 4 ? substr($year, 2, 2) : str_pad($year, 2, '0', STR_PAD_LEFT);

        $prefix = $company . $yearShort . $departmentCode . $docType;

        $lastRecord = Archive::where('record_id', 'like', $prefix . '%')
            ->orderBy('record_id', 'desc')
            ->first();

        if ($lastRecord) {
            $lastNumber = (int) substr($lastRecord->record_id, strlen($prefix));
            $nextNumber = str_pad($lastNumber + 1, 3, '0', STR_PAD_LEFT);
        } else {
            $nextNumber = '001';
        }

        return $prefix . $nextNumber;
    }
}