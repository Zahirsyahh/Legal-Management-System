<x-app-layout-dark title="Document Report">
    
    <style>
        /* Custom styles for Reports */
        .gradient-border {
            position: relative;
            background: linear-gradient(145deg, rgba(30, 41, 59, 0.8), rgba(15, 23, 42, 0.9));
            border: 1px solid transparent;
            border-radius: 0.75rem;
        }
        
        .gradient-border::before {
            content: '';
            position: absolute;
            top: -1px;
            left: -1px;
            right: -1px;
            bottom: -1px;
            background: linear-gradient(135deg, rgba(14, 165, 233, 0.3), rgba(59, 130, 246, 0.1), rgba(14, 165, 233, 0.3));
            border-radius: 0.75rem;
            z-index: -1;
            opacity: 0.5;
        }
        
        .search-highlight {
            background: linear-gradient(120deg, rgba(245, 158, 11, 0.2), rgba(245, 158, 11, 0.1));
            border-radius: 0.25rem;
            padding: 0.1rem 0.25rem;
        }
        
        /* Status Badges */
        .status-draft {
            background: linear-gradient(135deg, rgba(156, 163, 175, 0.15), rgba(75, 85, 99, 0.05));
            color: #9ca3af;
            border: 1px solid rgba(156, 163, 175, 0.3);
        }
        
        .status-submitted {
            background: linear-gradient(135deg, rgba(59, 130, 246, 0.15), rgba(37, 99, 235, 0.05));
            color: #60a5fa;
            border: 1px solid rgba(59, 130, 246, 0.3);
        }
        
        .status-under_review {
            background: linear-gradient(135deg, rgba(245, 158, 11, 0.15), rgba(217, 119, 6, 0.05));
            color: #fbbf24;
            border: 1px solid rgba(245, 158, 11, 0.3);
        }
        
        .status-final_approved {
            background: linear-gradient(135deg, rgba(34, 197, 94, 0.15), rgba(21, 128, 61, 0.05));
            color: #4ade80;
            border: 1px solid rgba(34, 197, 94, 0.3);
        }
        
        .status-number_issued {
            background: linear-gradient(135deg, rgba(139, 92, 246, 0.15), rgba(126, 34, 206, 0.05));
            color: #a78bfa;
            border: 1px solid rgba(139, 92, 246, 0.3);
        }
        
        .status-released {
            background: linear-gradient(135deg, rgba(6, 182, 212, 0.15), rgba(14, 116, 144, 0.05));
            color: #22d3ee;
            border: 1px solid rgba(6, 182, 212, 0.3);
        }
        
        .status-revision_needed {
            background: linear-gradient(135deg, rgba(249, 115, 22, 0.15), rgba(194, 65, 12, 0.05));
            color: #fb923c;
            border: 1px solid rgba(249, 115, 22, 0.3);
        }
        
        .status-declined {
            background: linear-gradient(135deg, rgba(239, 68, 68, 0.15), rgba(185, 28, 28, 0.05));
            color: #f87171;
            border: 1px solid rgba(239, 68, 68, 0.3);
        }
        
        .action-btn {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
        }
        
        .action-btn::after {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 5px;
            height: 5px;
            background: rgba(255, 255, 255, 0.3);
            opacity: 0;
            border-radius: 100%;
            transform: scale(1, 1) translate(-50%);
            transform-origin: 50% 50%;
        }
        
        .action-btn:focus:not(:active)::after {
            animation: ripple 1s ease-out;
        }
        
        .table-row-hover {
            transition: all 0.2s ease;
        }
        
        .table-row-hover:hover {
            background: linear-gradient(90deg, rgba(14, 165, 233, 0.05), rgba(59, 130, 246, 0.02));
            transform: translateY(-1px);
        }
        
        .pagination-btn {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            transition: all 0.3s ease;
        }
        
        .pagination-btn:hover:not(.disabled) {
            background: rgba(255, 255, 255, 0.1);
            border-color: rgba(14, 165, 233, 0.3);
            transform: translateY(-1px);
        }
        
        .pagination-btn.active {
            background: linear-gradient(135deg, rgba(14, 165, 233, 0.2), rgba(2, 132, 199, 0.2));
            border-color: rgba(14, 165, 233, 0.4);
            color: white;
        }
        
        .badge-count {
            background: linear-gradient(135deg, rgba(139, 92, 246, 0.2), rgba(168, 85, 247, 0.1));
            border: 1px solid rgba(139, 92, 246, 0.3);
            color: #a855f7;
        }
        
        .filter-input {
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid rgba(255, 255, 255, 0.1);
            transition: all 0.3s ease;
            color: white;
            font-size: 0.95rem;
        }
        
        .filter-input:focus {
            border-color: rgba(14, 165, 233, 0.5);
            box-shadow: 0 0 0 3px rgba(14, 165, 233, 0.1);
            outline: none;
            background: rgba(255, 255, 255, 0.05);
        }
        
        .filter-input::placeholder {
            color: rgba(156, 163, 175, 0.5);
        }
        
        .filter-select {
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: white;
            transition: all 0.3s ease;
            font-size: 0.95rem;
            cursor: pointer;
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%239ca3af'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M19 9l-7 7-7-7'%3E%3C/path%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 1rem center;
            background-size: 1.2rem;
            padding-right: 2.5rem;
        }
        
        .filter-select:focus {
            border-color: rgba(14, 165, 233, 0.5);
            box-shadow: 0 0 0 3px rgba(14, 165, 233, 0.1);
            outline: none;
            background-color: rgba(255, 255, 255, 0.05);
        }
        
        .filter-select option {
            background: #1e293b;
            color: white;
            padding: 0.5rem;
        }
        
        /* New Filter Section Styles */
        .filter-section {
            background: linear-gradient(145deg, rgba(30, 41, 59, 0.6), rgba(15, 23, 42, 0.7));
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 1rem;
        }
        
        .filter-header {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 1rem 1.5rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
        }
        
        .filter-header h3 {
            font-size: 1.1rem;
            font-weight: 600;
            color: white;
        }
        
        .filter-header svg {
            width: 1.25rem;
            height: 1.25rem;
            color: #60a5fa;
        }
        
        .filter-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            padding: 1.5rem;
        }
        
        .filter-item {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }
        
        .filter-item label {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.85rem;
            font-weight: 500;
            color: #9ca3af;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        
        .filter-item label svg {
            width: 1rem;
            height: 1rem;
            color: #60a5fa;
        }
        
        .filter-actions {
            display: flex;
            align-items: center;
            justify-content: flex-end;
            gap: 1rem;
            padding: 1rem 1.5rem;
            border-top: 1px solid rgba(255, 255, 255, 0.08);
            background: rgba(0, 0, 0, 0.2);
        }
        
        .btn-filter {
            background: linear-gradient(135deg, #3b82f6, #2563eb);
            color: white;
            border: none;
            padding: 0.75rem 1.5rem;
            border-radius: 0.75rem;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(37, 99, 235, 0.3);
        }
        
        .btn-filter:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(37, 99, 235, 0.4);
            background: linear-gradient(135deg, #2563eb, #1d4ed8);
        }
        
        .btn-reset {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: #e5e7eb;
            padding: 0.75rem 1.5rem;
            border-radius: 0.75rem;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.3s ease;
        }
        
        .btn-reset:hover {
            background: rgba(255, 255, 255, 0.1);
            border-color: rgba(239, 68, 68, 0.3);
            color: #f87171;
        }
        
        .btn-back {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: #e5e7eb;
            padding: 0.75rem 1.5rem;
            border-radius: 0.75rem;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.3s ease;
        }
        
        .btn-back:hover {
            background: rgba(255, 255, 255, 0.1);
            border-color: rgba(255, 255, 255, 0.2);
            transform: translateX(-3px);
        }
        
        .btn-print {
            background: linear-gradient(135deg, #f59e0b, #d97706);
            color: white;
            border: none;
            padding: 0.75rem 1.5rem;
            border-radius: 0.75rem;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(245, 158, 11, 0.3);
        }
        
        .btn-print:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(245, 158, 11, 0.4);
            background: linear-gradient(135deg, #d97706, #b45309);
        }
        
        .btn-excel {
            background: linear-gradient(135deg, #10b981, #059669);
            color: white;
            border: none;
            padding: 0.75rem 1.5rem;
            border-radius: 0.75rem;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(16, 185, 129, 0.3);
        }
        
        .btn-excel:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(16, 185, 129, 0.4);
            background: linear-gradient(135deg, #059669, #047857);
        }
        
        .active-filter-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
            background: rgba(59, 130, 246, 0.1);
            border: 1px solid rgba(59, 130, 246, 0.3);
            border-radius: 2rem;
            color: #60a5fa;
            font-size: 0.85rem;
        }
        
        .active-filter-badge button {
            background: none;
            border: none;
            color: #9ca3af;
            cursor: pointer;
            padding: 0.2rem;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s ease;
        }
        
        .active-filter-badge button:hover {
            background: rgba(255, 255, 255, 0.1);
            color: #f87171;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #0ea5e9, #3b82f6);
            color: white;
            border: none;
            box-shadow: 0 4px 15px rgba(14, 165, 233, 0.2);
        }
        
        .btn-primary:hover {
            background: linear-gradient(135deg, #0284c7, #2563eb);
            box-shadow: 0 6px 20px rgba(14, 165, 233, 0.3);
            transform: translateY(-2px);
        }
        
        .btn-success {
            background: linear-gradient(135deg, #10b981, #059669);
            color: white;
            border: none;
            box-shadow: 0 4px 15px rgba(16, 185, 129, 0.2);
        }
        
        .btn-success:hover {
            background: linear-gradient(135deg, #059669, #047857);
            box-shadow: 0 6px 20px rgba(16, 185, 129, 0.3);
            transform: translateY(-2px);
        }
        
        .btn-warning {
            background: linear-gradient(135deg, #f59e0b, #d97706);
            color: white;
            border: none;
            box-shadow: 0 4px 15px rgba(245, 158, 11, 0.2);
        }
        
        .btn-warning:hover {
            background: linear-gradient(135deg, #d97706, #b45309);
            box-shadow: 0 6px 20px rgba(245, 158, 11, 0.3);
            transform: translateY(-2px);
        }
        
        .btn-back {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: white;
        }
        
        .btn-back:hover {
            background: rgba(255, 255, 255, 0.1);
        }
        
        /* Glass effect */
        .glass-card {
            background: rgba(255, 255, 255, 0.03);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        /* Animations */
        @keyframes ripple {
            0% {
                transform: scale(0, 0);
                opacity: 0.5;
            }
            20% {
                transform: scale(25, 25);
                opacity: 0.3;
            }
            100% {
                opacity: 0;
                transform: scale(40, 40);
            }
        }
        
        @keyframes fade-in {
            from {
                opacity: 0;
            }
            to {
                opacity: 1;
            }
        }
        
        @keyframes slide-up {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .animate-fade-in {
            animation: fade-in 0.2s ease-out;
        }
        
        .animate-slide-up {
            animation: slide-up 0.3s ease-out;
        }
    </style>
    
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        
        <!-- Header Section -->
        <div class="flex flex-col lg:flex-row lg:items-center justify-between mb-8 gap-4">
            <div>
                <div class="flex items-center gap-3 mb-2">
                    <div class="p-3 rounded-xl bg-gradient-to-br from-cyan-500/10 to-blue-600/10 border border-cyan-500/20">
                        <svg class="w-6 h-6 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    </div>
                    <div>
                        <h1 class="text-3xl font-bold bg-gradient-to-r from-cyan-400 via-blue-400 to-cyan-400 bg-clip-text text-transparent">
                            @php
                                $title = 'Document Report';
                                if ($userRole === 'user') $title = 'Document Reports';
                                elseif (in_array($userRole, ['admin_fin', 'staff_fin'])) $title = 'Finance Document Report';
                                elseif (in_array($userRole, ['admin_acc', 'staff_acc'])) $title = 'Accounting Document Report';
                                elseif (in_array($userRole, ['admin_tax', 'staff_tax'])) $title = 'Tax Document Report';
                                elseif (in_array($userRole, ['admin', 'legal'])) $title = 'All Document Reports';
                            @endphp
                            {{ $title }}
                        </h1>
                        <p class="text-gray-400 mt-1">
                            @if(in_array($userRole, [
                                    'admin','legal',
                                    'admin_fin','staff_fin',
                                    'admin_acc','staff_acc',
                                    'admin_tax','staff_tax'
                                ]))
                                Generate and export your reports! (Akses Admin & Legal)
                            @elseif(in_array($userRole, ['admin_fin', 'staff_fin', 'admin_acc', 'staff_acc', 'admin_tax', 'staff_tax']))
                                Menampilkan semua kontrak dengan department {{ $userDept }}
                            @else
                                Menampilkan kontrak yang Anda buat
                            @endif
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filter Section - REDESIGNED -->
        <div class="filter-section mb-6">
            <div class="filter-header">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                </svg>
                <h3>Filter Documents</h3>
            </div>
            
            <form id="filterForm" action="{{ route('reports.contracts') }}" method="GET">
                <div class="filter-grid">
                    <!-- Date Range -->
                    <div class="filter-item">
                        <label>
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                            Dari Tanggal
                        </label>
                        <input type="date" 
                               name="start_date" 
                               value="{{ $activeFilters['start_date'] ?? '' }}"
                               class="filter-input w-full px-4 py-3 rounded-lg">
                    </div>
                    
                    <div class="filter-item">
                        <label>
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                            Sampai Tanggal
                        </label>
                        <input type="date" 
                               name="end_date" 
                               value="{{ $activeFilters['end_date'] ?? '' }}"
                               class="filter-input w-full px-4 py-3 rounded-lg">
                    </div>
                    
                    <!-- Status Filter -->
                    <div class="filter-item">
                        <label>
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                            </svg>
                            Status
                        </label>
                        <select name="status" class="filter-select w-full px-4 py-3 rounded-lg">
                            <option value="">Semua Status</option>
                            @foreach($statuses as $value => $label)
                                <option value="{{ $value }}" {{ ($activeFilters['status'] ?? '') == $value ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    
                    <!-- Document Type Filter -->
                    <div class="filter-item">
                        <label>
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7v8a2 2 0 002 2h6M8 7V5a2 2 0 012-2h4.586a1 1 0 01.707.293l4.414 4.414a1 1 0 01.293.707V15a2 2 0 01-2 2h-2M8 7H6a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2v-2"/>
                            </svg>
                            Document Type
                        </label>
                        <select name="contract_type" class="filter-select w-full px-4 py-3 rounded-lg">
                            <option value="">All Type</option>
                            @foreach($documentTypes as $value => $label)
                                <option value="{{ $value }}" {{ ($activeFilters['contract_type'] ?? '') == $value ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    
                    <!-- Department Filter (khusus Admin/Legal) -->
                    @if(in_array($userRole, [
                        'admin','legal',
                        'admin_fin','staff_fin',
                        'admin_acc','staff_acc',
                        'admin_tax','staff_tax'
                    ]))
                    <div class="filter-item">
                        <label>
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                            </svg>
                            Department
                        </label>
                        <select name="department" class="filter-select w-full px-4 py-3 rounded-lg">
                            <option value="">All Department</option>
                            @foreach($departments as $dept)
                                <option value="{{ $dept->kode_pendek }}" 
                                    {{ ($activeFilters['department'] ?? '') == $dept->kode_pendek ? 'selected' : '' }}>
                                    {{ $dept->nama_departemen }} ({{ $dept->kode_pendek }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    @endif
                </div>
                
                <!-- Active Filters Display -->
                @php
                    $activeFilterCount = 0;
                    foreach(['start_date', 'end_date', 'status', 'contract_type', 'department'] as $filter) {
                        if(!empty($activeFilters[$filter])) $activeFilterCount++;
                    }
                @endphp
                
                @if($activeFilterCount > 0)
                <div class="px-6 pb-2 flex flex-wrap items-center gap-2">
                    <span class="text-sm text-gray-400">Active filters:</span>
                    
                    @if(!empty($activeFilters['start_date']))
                    <div class="active-filter-badge">
                        <span>From: {{ \Carbon\Carbon::parse($activeFilters['start_date'])->format('d/m/Y') }}</span>
                        <button type="button" onclick="removeFilter('start_date')">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                    @endif
                    
                    @if(!empty($activeFilters['end_date']))
                    <div class="active-filter-badge">
                        <span>To: {{ \Carbon\Carbon::parse($activeFilters['end_date'])->format('d/m/Y') }}</span>
                        <button type="button" onclick="removeFilter('end_date')">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                    @endif
                    
                    @if(!empty($activeFilters['status']))
                    <div class="active-filter-badge">
                        <span>Status: {{ $statuses[$activeFilters['status']] ?? $activeFilters['status'] }}</span>
                        <button type="button" onclick="removeFilter('status')">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                    @endif
                    
                    @if(!empty($activeFilters['contract_type']))
                    <div class="active-filter-badge">
                        <span>Type: {{ $documentTypes[$activeFilters['contract_type']] ?? $activeFilters['contract_type'] }}</span>
                        <button type="button" onclick="removeFilter('contract_type')">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                    @endif
                    
                    @if(!empty($activeFilters['department']))
                    <div class="active-filter-badge">
                        <span>Dept: {{ $activeFilters['department'] }}</span>
                        <button type="button" onclick="removeFilter('department')">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                    @endif
                </div>
                @endif
                
                <!-- Action Buttons -->
                <div class="filter-actions">
                    <button type="button" 
                            onclick="window.location.href='{{ route('reports.index') }}'" 
                            class="btn-back flex items-center gap-2 transition-all duration-300">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                        </svg>
                        BACK
                    </button>
                    
                    <button type="button" 
                            onclick="resetFilters()"
                            class="btn-reset flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                        </svg>
                        RESET
                    </button>
                    
                    <button type="submit" 
                            class="btn-filter flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                        </svg>
                        APPLY FILTERS
                    </button>
                    
                    <button type="button" 
                            onclick="printReport()"
                            class="btn-print flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                        </svg>
                        PRINT
                    </button>
                    
                   <button type="button" 
                        onclick="exportExcel()"
                        class="btn-excel flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                        </svg>
                        DOWNLOAD EXCEL
                    </button>
                </div>
            </form>
        </div>

        <!-- Summary Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
            <div class="glass-card rounded-xl px-4 py-3 flex items-center gap-3">
                <div class="p-2 rounded-lg bg-gradient-to-br from-blue-500/10 to-cyan-600/10">
                    <svg class="w-5 h-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-sm text-gray-400">Total</p>
                    <p class="text-2xl font-bold">{{ $contracts->total() }}</p>
                </div>
            </div>
            
            <div class="glass-card rounded-xl px-4 py-3 flex items-center gap-3">
                <div class="p-2 rounded-lg bg-gradient-to-br from-green-500/10 to-emerald-600/10">
                    <svg class="w-5 h-5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-sm text-gray-400">Released</p>
                    <p class="text-2xl font-bold">{{ $contracts->where('status', 'released')->count() }}</p>
                </div>
            </div>
            
            <div class="glass-card rounded-xl px-4 py-3 flex items-center gap-3">
                <div class="p-2 rounded-lg bg-gradient-to-br from-amber-500/10 to-orange-600/10">
                    <svg class="w-5 h-5 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-sm text-gray-400">In Review</p>
                    <p class="text-2xl font-bold">{{ $contracts->where('status', 'under_review')->count() }}</p>
                </div>
            </div>
            
            <div class="glass-card rounded-xl px-4 py-3 flex items-center gap-3">
                <div class="p-2 rounded-lg bg-gradient-to-br from-purple-500/10 to-pink-600/10">
                    <svg class="w-5 h-5 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-sm text-gray-400">This Month</p>
                    <p class="text-2xl font-bold">{{ $contracts->where('created_at', '>=', now()->startOfMonth())->count() }}</p>
                </div>
            </div>
        </div>

        <!-- Main Table -->
        <div class="gradient-border rounded-xl overflow-hidden mb-8">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="bg-gradient-to-r from-dark-800 to-dark-900 border-b border-gray-800">
                            <th class="py-4 px-4 text-left">
                                <span class="font-semibold text-gray-300">NO</span>
                            </th>
                            <th class="py-4 px-4 text-left">
                                <span class="font-semibold text-gray-300">NUMBERING</span>
                            </th>
                            <th class="py-4 px-4 text-left">
                                <span class="font-semibold text-gray-300">DEPT</span>
                            </th>
                            <th class="py-4 px-4 text-left">
                                <span class="font-semibold text-gray-300">COUNTERPARTY</span>
                            </th>
                            <th class="py-4 px-4 text-left">
                                <span class="font-semibold text-gray-300">DESCRIPTION</span>
                            </th>
                            <th class="py-4 px-4 text-left">
                                <span class="font-semibold text-gray-300">EFFECTIVE DATE</span>
                            </th>
                            <th class="py-4 px-4 text-left">
                                <span class="font-semibold text-gray-300">EXPIRY DATE</span>
                            </th>
                            <th class="py-4 px-4 text-left">
                                <span class="font-semibold text-gray-300">SUBMITTED AT</span>
                            </th>
                            <th class="py-4 px-4 text-left">
                                <span class="font-semibold text-gray-300">REQUESTED BY</span>
                            </th>
                            <th class="py-4 px-4 text-left">
                                <span class="font-semibold text-gray-300">STATUS</span>
                            </th>
                            <th class="py-4 px-4 text-left">
                                <span class="font-semibold text-gray-300">TIPE</span>
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-800/50" id="tableBody">
                        @forelse ($contracts as $index => $contract)
                        <tr class="table-row-hover group">
                            <td class="py-4 px-4">
                                <span class="font-medium text-gray-300">{{ $contracts->firstItem() + $index }}</span>
                            </td>
                            <td class="py-4 px-4">
                                <span class="font-mono text-sm text-white">{{ $contract->contract_number ?? '-' }}</span>
                            </td>
                            <td class="py-4 px-4">
                                <span class="px-3 py-1.5 rounded-full text-sm font-medium bg-gradient-to-br from-cyan-500/10 to-blue-600/10 text-cyan-300 border border-cyan-500/20">
                                    {{ $contract->department_code ?? '-' }}
                                </span>
                            </td>
                            <td class="py-4 px-4">
                                <span class="text-gray-300">{{ $contract->counterparty_name ?? '-' }}</span>
                            </td>
                            <td class="py-4 px-4">
                                <span class="text-gray-300 line-clamp-2">{{ $contract->description ?? '-' }}</span>
                            </td>
                            <td class="py-4 px-4">
                                <span class="text-gray-400 text-sm">{{ $contract->effective_date ? \Carbon\Carbon::parse($contract->effective_date)->format('d/m/Y') : '-' }}</span>
                            </td>
                            <td class="py-4 px-4">
                                <span class="text-gray-400 text-sm">{{ $contract->expiry_date ? \Carbon\Carbon::parse($contract->expiry_date)->format('d/m/Y') : '-' }}</span>
                            </td>
                            <td class="py-4 px-4">
                                <span class="text-gray-400 text-sm">{{ $contract->created_at ? \Carbon\Carbon::parse($contract->created_at)->format('d/m/Y H:i') : '-' }}</span>
                            </td>
                            <td class="py-4 px-4">
                                <span class="text-gray-300">{{ $contract->user->nama_user ?? $contract->user_id ?? '-' }}</span>
                            </td>
                            <td class="py-4 px-4">
                                @php
                                    $statusClass = 'status-' . str_replace('_', '-', $contract->status);
                                    $statusLabel = $statuses[$contract->status] ?? $contract->status;
                                @endphp
                                <span class="{{ $statusClass }} px-3 py-1.5 rounded-full text-sm font-medium">
                                    {{ $statusLabel }}
                                </span>
                            </td>
                            <td class="py-4 px-4">
                                @if($contract->contract_type === 'surat')
                                <span class="px-3 py-1.5 rounded-full text-sm font-medium bg-blue-500/10 text-blue-400 border border-blue-500/20">
                                    Surat
                                </span>
                                @elseif($contract->contract_type === 'kontrak')
                                <span class="px-3 py-1.5 rounded-full text-sm font-medium bg-purple-500/10 text-purple-400 border border-purple-500/20">
                                    Kontrak
                                </span>
                                @else
                                <span class="text-gray-500">-</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="11" class="py-12 px-4 text-center">
                                <div class="flex flex-col items-center justify-center">
                                    <div class="p-4 rounded-full bg-gradient-to-br from-gray-800/50 to-gray-900/50 mb-4">
                                        <svg class="w-12 h-12 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                        </svg>
                                    </div>
                                    <h3 class="text-xl font-semibold text-gray-300 mb-2">Tidak Ada Data</h3>
                                    <p class="text-gray-500 max-w-md">Tidak ada kontrak atau surat yang ditemukan dengan filter yang dipilih</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            @if($contracts->hasPages())
            <div class="px-6 py-4 border-t border-gray-800 bg-gradient-to-r from-dark-800/50 to-dark-900/50 flex flex-col sm:flex-row items-center justify-between gap-4">
                <div class="text-sm text-gray-400">
                    Menampilkan <span class="font-medium text-white">{{ $contracts->firstItem() ?? 0 }}</span> 
                    sampai <span class="font-medium text-white">{{ $contracts->lastItem() ?? 0 }}</span> 
                    dari <span class="font-medium text-white">{{ $contracts->total() }}</span> data
                </div>
                
                <div class="flex items-center gap-2">
                    @if($contracts->onFirstPage())
                    <button class="pagination-btn px-4 py-2 rounded-lg disabled opacity-50 cursor-not-allowed">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                        </svg>
                    </button>
                    @else
                    <a href="{{ $contracts->previousPageUrl() }}" 
                       class="pagination-btn px-4 py-2 rounded-lg hover:bg-white/10">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                        </svg>
                    </a>
                    @endif
                    
                    @php
                        $current = $contracts->currentPage();
                        $last = $contracts->lastPage();
                        $start = max(1, $current - 2);
                        $end = min($last, $current + 2);
                        
                        if($start > 1) {
                            echo '<a href="' . $contracts->url(1) . '" class="pagination-btn px-4 py-2 rounded-lg">1</a>';
                            if($start > 2) {
                                echo '<span class="px-2 text-gray-500">...</span>';
                            }
                        }
                        
                        for($i = $start; $i <= $end; $i++) {
                            if($i == $current) {
                                echo '<button class="pagination-btn active px-4 py-2 rounded-lg">' . $i . '</button>';
                            } else {
                                echo '<a href="' . $contracts->url($i) . '" class="pagination-btn px-4 py-2 rounded-lg">' . $i . '</a>';
                            }
                        }
                        
                        if($end < $last) {
                            if($end < $last - 1) {
                                echo '<span class="px-2 text-gray-500">...</span>';
                            }
                            echo '<a href="' . $contracts->url($last) . '" class="pagination-btn px-4 py-2 rounded-lg">' . $last . '</a>';
                        }
                    @endphp
                    
                    @if($contracts->hasMorePages())
                    <a href="{{ $contracts->nextPageUrl() }}" 
                       class="pagination-btn px-4 py-2 rounded-lg hover:bg-white/10">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </a>
                    @else
                    <button class="pagination-btn px-4 py-2 rounded-lg disabled opacity-50 cursor-not-allowed">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </button>
                    @endif
                </div>
            </div>
            @endif
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Auto submit form saat filter berubah? (Opsional)
            // const filterSelects = document.querySelectorAll('.filter-select');
            // filterSelects.forEach(select => {
            //     select.addEventListener('change', function() {
            //         document.getElementById('filterForm').submit();
            //     });
            // });
        });
        
        function printReport() {
            // Build URL dengan filter yang sama
            const form = document.getElementById('filterForm');
            const formData = new FormData(form);
            const params = new URLSearchParams(formData).toString();
            
            window.open('{{ route("reports.print") }}?' + params, '_blank');
        }
        
        function exportExcel() {
            const form = document.getElementById('filterForm');
            const formData = new FormData(form);
            const params = new URLSearchParams(formData).toString();
            
            window.location.href = '{{ route("reports.export-excel") }}?' + params;
        }
        
        function goBack() {
            window.location.href = '{{ route("reports.index") }}';
        }
        
        function resetFilters() {
            window.location.href = '{{ route("reports.contracts") }}';
        }
        
        function removeFilter(filterName) {
            const url = new URL(window.location.href);
            url.searchParams.delete(filterName);
            window.location.href = url.toString();
        }
    </script>

</x-app-layout-dark>