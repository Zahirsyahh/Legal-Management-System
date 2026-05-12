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
        $yearShort = strlen($year) === 4 
            ? substr($year, 2, 2) 
            : str_pad($year, 2, '0', STR_PAD_LEFT);

        $prefix = $company . $yearShort . $departmentCode . $docType;

        // Ambil semua record_id yang cocok dengan prefix ini
        $existingNumbers = Archive::where('record_id', 'like', $prefix . '%')
            ->pluck('record_id')
            ->map(fn($id) => (int) substr($id, strlen($prefix))) // ekstrak angka: "002" → 2
            ->filter(fn($n) => $n > 0)                          // buang yang tidak valid
            ->sort()
            ->values();

        // Cari slot kosong pertama mulai dari 1
        $nextNumber = 1;
        foreach ($existingNumbers as $used) {
            if ($used === $nextNumber) {
                $nextNumber++; // slot ini terpakai, coba berikutnya
            } else {
                break; // ketemu gap!
            }
        }

        return $prefix . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);
    }
}