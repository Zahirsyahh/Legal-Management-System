<?php

namespace App\Services;

use App\Models\Archive;

class ArchiveRecordIdService
{
    /**
     * Generate Record ID
     * Format: YYDDTTNNN
     */
    public function generate(string $year, string $departmentCode, string $docType): string
    {
        $prefix = $year . $departmentCode . $docType;

        // cari record terakhir berdasarkan prefix
        $lastRecord = Archive::where('record_id', 'like', $year.$departmentCode.'%')
            ->orderBy('record_id', 'desc')
            ->first();

        if ($lastRecord) {

            $lastNumber = substr($lastRecord->record_id, -3);
            $nextNumber = str_pad(((int) $lastNumber) + 1, 3, '0', STR_PAD_LEFT);

        } else {

            $nextNumber = '001';

        }

        return $prefix . $nextNumber;
    }
}
