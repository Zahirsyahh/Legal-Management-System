<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Print Laporan Kontrak</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            margin: 0;
            padding: 20px;
            background: white;
        }
        
        .print-header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #333;
        }
        
        .print-header h1 {
            margin: 0;
            font-size: 24px;
            color: #000;
        }
        
        .print-header h2 {
            margin: 10px 0 0;
            font-size: 18px;
            color: #666;
            font-weight: normal;
        }
        
        .filter-info {
            margin-bottom: 20px;
            padding: 15px;
            background: #f5f5f5;
            border-radius: 5px;
            font-size: 14px;
        }
        
        .filter-info p {
            margin: 5px 0;
        }
        
        .filter-info strong {
            color: #000;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            font-size: 12px;
        }
        
        th {
            background: #333;
            color: white;
            padding: 10px;
            text-align: left;
            font-weight: bold;
        }
        
        td {
            padding: 8px 10px;
            border-bottom: 1px solid #ddd;
        }
        
        tr:nth-child(even) {
            background: #f9f9f9;
        }
        
        .print-footer {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
            text-align: right;
            font-size: 12px;
            color: #666;
        }
        
        .status-badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 3px;
            font-size: 11px;
            font-weight: bold;
        }
        
        .status-draft { background: #e0e0e0; color: #333; }
        .status-submitted { background: #cce5ff; color: #004085; }
        .status-under_review { background: #fff3cd; color: #856404; }
        .status-final_approved { background: #d4edda; color: #155724; }
        .status-number_issued { background: #d1c4e9; color: #4a1b6d; }
        .status-released { background: #b2ebf2; color: #006064; }
        .status-revision_needed { background: #ffe5d0; color: #a86400; }
        .status-declined { background: #f8d7da; color: #721c24; }
        
        .dept-badge {
            display: inline-block;
            padding: 3px 8px;
            background: #e9ecef;
            border-radius: 3px;
            font-weight: bold;
            font-size: 11px;
        }
        
        .type-badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 3px;
            font-size: 11px;
        }
        
        .type-surat { background: #cce5ff; color: #004085; }
        .type-kontrak { background: #d1c4e9; color: #4a1b6d; }
        
        @media print {
            body {
                padding: 0;
                background: white;
            }
            
            .no-print {
                display: none;
            }
            
            th {
                background: #333 !important;
                color: white !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
            
            .status-badge, .dept-badge, .type-badge {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
        }
        
        .no-print {
            margin-bottom: 20px;
            text-align: right;
        }
        
        .no-print button {
            padding: 10px 20px;
            background: #333;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
        }
        
        .no-print button:hover {
            background: #555;
        }
    </style>
</head>
<body>
    <div class="no-print">
        <button onclick="window.print()">Print / Simpan PDF</button>
        <button onclick="window.close()">Tutup</button>
    </div>
    
    <div class="print-header">
        <h1>LAPORAN KONTRAK</h1>
        <h2>
            @php
                $title = 'Semua Kontrak';
                if ($userRole === 'user') $title = 'Kontrak Saya';
                elseif (in_array($userRole, ['admin_fin', 'staff_fin'])) $title = 'Kontrak Department Finance';
                elseif (in_array($userRole, ['admin_acc', 'staff_acc'])) $title = 'Kontrak Department Accounting';
                elseif (in_array($userRole, ['admin_tax', 'staff_tax'])) $title = 'Kontrak Department Tax';
                elseif (in_array($userRole, ['admin', 'legal'])) $title = 'Semua Kontrak';
            @endphp
            {{ $title }}
        </h2>
    </div>
    
<div class="filter-info">
    <p><strong>Periode:</strong> 
        @if(request()->filled('start_date') && request()->filled('end_date'))
            {{ \Carbon\Carbon::parse(request('start_date'))->format('d/m/Y') }} 
            - 
            {{ \Carbon\Carbon::parse(request('end_date'))->format('d/m/Y') }}
        @elseif(request()->filled('start_date'))
            Dari {{ \Carbon\Carbon::parse(request('start_date'))->format('d/m/Y') }}
        @elseif(request()->filled('end_date'))
            Sampai {{ \Carbon\Carbon::parse(request('end_date'))->format('d/m/Y') }}
        @else
            Semua Periode
        @endif
    </p>

    @if(request()->filled('status'))
        <p>
            <strong>Status:</strong> 
            {{ $statuses[request('status')] ?? request('status') }}
        </p>
    @endif
</div>
    
    <table>
        <thead>
            <tr>
                <th>NO</th>
                <th>NOMOR SURAT</th>
                <th>DEPT</th>
                <th>COUNTERPARTY</th>
                <th>DESKRIPSI</th>
                <th>EFFECTIVE DATE</th>
                <th>EXPIRY DATE</th>
                <th>SUBMITTED AT</th>
                <th>REQUESTED BY</th>
                <th>STATUS</th>
                <th>TIPE</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($contracts as $index => $contract)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $contract->contract_number ?? '-' }}</td>
                <td>
                    <span class="dept-badge">{{ $contract->department_code ?? '-' }}</span>
                </td>
                <td>{{ $contract->counterparty_name ?? '-' }}</td>
                <td>{{ $contract->description ?? '-' }}</td>
                <td>{{ $contract->effective_date ? \Carbon\Carbon::parse($contract->effective_date)->format('d/m/Y') : '-' }}</td>
                <td>{{ $contract->expiry_date ? \Carbon\Carbon::parse($contract->expiry_date)->format('d/m/Y') : '-' }}</td>
                <td>{{ $contract->created_at ? \Carbon\Carbon::parse($contract->created_at)->format('d/m/Y H:i') : '-' }}</td>
                <td>{{ $contract->user->nama_user ?? $contract->user_id ?? '-' }}</td>
                <td>
                    @php
                        $statusClass = 'status-' . str_replace('_', '-', $contract->status);
                        $statusLabel = $statuses[$contract->status] ?? $contract->status;
                    @endphp
                    <span class="status-badge {{ $statusClass }}">{{ $statusLabel }}</span>
                </td>
                <td>
                    @if($contract->contract_type === 'surat')
                        <span class="type-badge type-surat">Surat</span>
                    @elseif($contract->contract_type === 'kontrak')
                        <span class="type-badge type-kontrak">Kontrak</span>
                    @else
                        -
                    @endif
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="11" style="text-align: center; padding: 30px;">
                    Tidak ada data untuk ditampilkan
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
    
    <div class="print-footer">
        <p>Dokumen ini dicetak dari sistem Legal Contract Management</p>
    </div>
    
    <script>
        // Auto print when page loads (optional)
        // window.onload = function() {
        //     window.print();
        // };
    </script>
</body>
</html>