<?php

namespace App\Services;

use App\Models\Archive;

class ArchiveRecordIdService
{
    /**
     * Generate Record ID
     * Format: YYDDTTNNN
     */
    public function generate(string $company, string $year, string $departmentCode, string $docType): string
    {
        $prefix = $company . $year . $departmentCode . $docType;

        $lastRecord = Archive::where('record_id', 'like', $prefix . '%')
            ->orderBy('record_id', 'desc')
            ->first();

        if ($lastRecord) {
            $lastNumber = (int) substr($lastRecord->record_id, strlen($prefix));
            $nextNumber = str_pad(((int) $lastNumber) + 1, 3, '0', STR_PAD_LEFT);
        } else {
            $nextNumber = '001';
        }

        return $prefix . $nextNumber;
    }
}
