<?php

namespace App\Helpers;

class SynologyMapper
{
    /**
     * Mapping kode_department (tbl_user) → Synology folder info
     * Format DB : /SHARE DATA LEGAL/LEGAL-XXX||https://gofile.me/...
     */
    const MAP = [
        'ACC'  => [
            'folder' => 'LEGAL-ACCOUNTING',
            'path'   => '/SHARE DATA LEGAL/LEGAL-ACCOUNTING',
            'link'   => 'https://gofile.me/7xZM6/yWuH7sTsU',
            'label'  => 'Accounting',
        ],
        'COP'  => [
            'folder' => 'LEGAL-COAL',
            'path'   => '/SHARE DATA LEGAL/LEGAL-COAL',
            'link'   => 'https://gofile.me/7xZM6/cdppoRHoF',
            'label'  => 'Coal Operations',
        ],
        'EXIM' => [
            'folder' => 'LEGAL-EXIM',
            'path'   => '/SHARE DATA LEGAL/LEGAL-EXIM',
            'link'   => 'https://gofile.me/7xZM6/wCYr86m2Q',
            'label'  => 'Export-Import',
        ],
        'FIN'  => [
            'folder' => 'LEGAL-FINANCE',
            'path'   => '/SHARE DATA LEGAL/LEGAL-FINANCE',
            'link'   => 'https://gofile.me/7xZM6/OS96zNc1C',
            'label'  => 'Finance',
        ],
        // HRGA & IT — 3 divisi, 1 folder
        'HRD'  => [
            'folder' => 'LEGAL-HRGA&IT',
            'path'   => '/SHARE DATA LEGAL/LEGAL-HRGA&IT',
            'link'   => 'https://gofile.me/7xZM6/jBL746KTd',
            'label'  => 'HRGA & IT (HRD)',
        ],
        'GA'   => [
            'folder' => 'LEGAL-HRGA&IT',
            'path'   => '/SHARE DATA LEGAL/LEGAL-HRGA&IT',
            'link'   => 'https://gofile.me/7xZM6/jBL746KTd',
            'label'  => 'HRGA & IT (General Affair)',
        ],
        'ITE'  => [
            'folder' => 'LEGAL-HRGA&IT',
            'path'   => '/SHARE DATA LEGAL/LEGAL-HRGA&IT',
            'link'   => 'https://gofile.me/7xZM6/jBL746KTd',
            'label'  => 'HRGA & IT (Information Technology)',
        ],
        'HSE'  => [
            'folder' => 'LEGAL-HSE',
            'path'   => '/SHARE DATA LEGAL/LEGAL-HSE',
            'link'   => 'https://gofile.me/7xZM6/edzGJENFd',
            'label'  => 'HSE Corporate',
        ],
        'OPE'  => [
            'folder' => 'LEGAL-OPERATION',
            'path'   => '/SHARE DATA LEGAL/LEGAL-OPERATION',
            'link'   => 'https://gofile.me/7xZM6/Xkr1s2qm9',
            'label'  => 'Operation',
        ],
        'NOP'  => [
            'folder' => 'LEGAL-ORE',
            'path'   => '/SHARE DATA LEGAL/LEGAL-ORE',
            'link'   => 'https://gofile.me/7xZM6/OUlXZNi88',
            'label'  => 'Nickel Ore Procurement',
        ],
        'PCH'  => [
            'folder' => 'LEGAL-PURCHASING',
            'path'   => '/SHARE DATA LEGAL/LEGAL-PURCHASING',
            'link'   => 'https://gofile.me/7xZM6/gzHmVJhzA',
            'label'  => 'Purchasing',
        ],
        'SLS'  => [
            'folder' => 'LEGAL-SALES',
            'path'   => '/SHARE DATA LEGAL/LEGAL-SALES',
            'link'   => 'https://gofile.me/7xZM6/4DnMr6DHo',
            'label'  => 'Sales & Marketing',
        ],
        'TAX'  => [
            'folder' => 'LEGAL-TAX',
            'path'   => '/SHARE DATA LEGAL/LEGAL-TAX',
            'link'   => 'https://gofile.me/7xZM6/JWttbmhJz',
            'label'  => 'Tax',
        ],
    ];

    /**
     * Resolve synology info dari kode_department user.
     * Return null jika tidak ditemukan.
     */
    public static function resolve(string $kodeDepartment): ?array
    {
        $kode = strtoupper(trim($kodeDepartment));
        return self::MAP[$kode] ?? null;
    }

    /**
     * Build string yang disimpan ke DB:
     * "/SHARE DATA LEGAL/LEGAL-FINANCE||https://gofile.me/..."
     */
    public static function toDbValue(string $kodeDepartment): ?string
    {
        $info = self::resolve($kodeDepartment);
        if (!$info) return null;
        return $info['path'] . '||' . $info['link'];
    }

    /**
     * Parse DB value kembali ke ['path' => ..., 'link' => ...]
     */
    public static function parse(?string $dbValue): array
    {
        if (!$dbValue) return ['path' => null, 'link' => null];

        $parts = explode('||', $dbValue, 2);
        return [
            'path' => $parts[0] ?? null,
            'link' => $parts[1] ?? null,
        ];
    }

    /**
     * Daftar semua pilihan untuk dropdown di blade
     * Return: [['value' => 'path||link', 'label' => '...', 'path' => '...', 'link' => '...'], ...]
     */
    public static function allOptions(): array
    {
        $seen   = [];
        $result = [];

        foreach (self::MAP as $kode => $info) {
            // Deduplicate berdasarkan folder (HRGA&IT muncul 1x saja)
            if (in_array($info['folder'], $seen)) continue;
            $seen[] = $info['folder'];

            $result[] = [
                'value'  => $info['path'] . '||' . $info['link'],
                'label'  => $info['folder'],
                'path'   => $info['path'],
                'link'   => $info['link'],
                'folder' => $info['folder'],
            ];
        }

        return $result;
    }
}