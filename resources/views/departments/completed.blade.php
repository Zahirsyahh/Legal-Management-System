@php
    // Determine department based on route prefix
    $routePrefix = request()->route()->getPrefix();
    $department = match(true) {
        str_contains($routePrefix, 'finance') => 'FIN',
        str_contains($routePrefix, 'accounting') => 'ACC',
        str_contains($routePrefix, 'tax') => 'TAX',
        default => 'FIN'
    };
    
    // Department colors
    $deptColors = [
        'FIN' => [
            'bg' => 'from-green-500/10 to-emerald-600/10',
            'text' => 'text-green-400',
            'text-gradient' => 'from-green-400 to-emerald-300',
            'border' => 'border-green-500/20',
            'gradient' => 'from-green-400 to-emerald-400',
        ],
        'ACC' => [
            'bg' => 'from-blue-500/10 to-cyan-600/10',
            'text' => 'text-blue-400',
            'text-gradient' => 'from-blue-400 to-cyan-300',
            'border' => 'border-blue-500/20',
            'gradient' => 'from-blue-400 to-cyan-400',
        ],
        'TAX' => [
            'bg' => 'from-purple-500/10 to-pink-600/10',
            'text' => 'text-purple-400',
            'text-gradient' => 'from-purple-400 to-pink-300',
            'border' => 'border-purple-500/20',
            'gradient' => 'from-purple-400 to-pink-400',
        ],
    ];
    
    $currentColor = $deptColors[$department] ?? $deptColors['FIN'];
@endphp

<x-app-layout-dark title="Completed Contracts - {{ $department }}">
    
    <style>
        /* Custom styles for Completed Contracts */
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
        
        .status-completed {
            background: linear-gradient(135deg, rgba(34, 197, 94, 0.15), rgba(34, 197, 94, 0.05));
            color: #4ade80;
            border: 1px solid rgba(34, 197, 94, 0.3);
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
        
        .loading-shimmer {
            background: linear-gradient(
                90deg,
                rgba(255, 255, 255, 0) 0%,
                rgba(255, 255, 255, 0.03) 50%,
                rgba(255, 255, 255, 0) 100%
            );
            background-size: 1000px 100%;
            animation: shimmer 2s infinite;
        }
        
        /* Animations */
        @keyframes shimmer {
            0% {
                background-position: -1000px 0;
            }
            100% {
                background-position: 1000px 0;
            }
        }
        
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
        
        @keyframes scale-in {
            from {
                opacity: 0;
                transform: scale(0.95);
            }
            to {
                opacity: 1;
                transform: scale(1);
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
        
        .animate-scale-in {
            animation: scale-in 0.2s ease-out;
        }
        
        .animate-slide-up {
            animation: slide-up 0.3s ease-out;
        }
        
        /* Glass effect */
        .glass-card {
            background: rgba(255, 255, 255, 0.03);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        /* Button styles */
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
        
        /* Category badge */
        .category-badge {
            padding: 0.5rem 1rem;
            border-radius: 0.5rem;
            font-weight: 600;
            font-size: 0.875rem;
        }
        
        .category-badge.finance {
            background: linear-gradient(135deg, rgba(34, 197, 94, 0.2), rgba(21, 128, 61, 0.2));
            color: #4ade80;
            border: 1px solid rgba(74, 222, 128, 0.3);
        }
        
        .category-badge.accounting {
            background: linear-gradient(135deg, rgba(14, 165, 233, 0.2), rgba(2, 132, 199, 0.2));
            color: #38bdf8;
            border: 1px solid rgba(56, 189, 248, 0.3);
        }
        
        .category-badge.tax {
            background: linear-gradient(135deg, rgba(168, 85, 247, 0.2), rgba(139, 92, 246, 0.2));
            color: #a855f7;
            border: 1px solid rgba(168, 85, 247, 0.3);
        }
        
        /* Status indicator */
        .status-dot-completed {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: linear-gradient(135deg, #4ade80, #22c55e);
            box-shadow: 0 0 8px rgba(74, 222, 128, 0.5);
        }
    </style>
    
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        
        <!-- Header Section -->
        <div class="flex flex-col lg:flex-row lg:items-center justify-between mb-8 gap-4">
            <div>
                <!-- Back Button -->
                <div class="flex items-center gap-3 mb-6">
                    @php
                        $dashboardRoute = match($department) {
                            'FIN' => 'finance-admin.dashboard',
                            'ACC' => 'accounting-admin.dashboard',
                            'TAX' => 'tax-admin.dashboard',
                            default => 'finance-admin.dashboard'
                        };
                    @endphp
                    <a href="{{ route($dashboardRoute) }}" 
                       class="group action-btn p-2.5 rounded-xl border border-gray-700/50 bg-gray-800/30 hover:bg-gray-800/50 hover:border-gray-600 transition-all duration-300 flex items-center gap-2">
                        <svg class="w-5 h-5 text-gray-400 group-hover:text-gray-300 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                        <span class="text-sm font-medium text-gray-300">Back to Dashboard</span>
                    </a>
                </div>
                
                <div class="flex items-center gap-3 mb-2">
                    <div class="p-3 rounded-xl bg-gradient-to-br {{ $currentColor['bg'] }} border {{ $currentColor['border'] }}">
                        <svg class="w-6 h-6 {{ $currentColor['text'] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                    </div>
                    <div>
                        <h1 class="text-3xl font-bold bg-gradient-to-r {{ $currentColor['text-gradient'] }} bg-clip-text text-transparent">
                            Completed Contracts
                        </h1>
                        <p class="text-gray-400 mt-1">Final approved contracts with {{ $department }} department contribution</p>
                    </div>
                </div>
                
                <!-- Stats Cards -->
                <div class="flex flex-wrap gap-4 mt-6">
                    <div class="glass-card rounded-xl px-4 py-3 flex items-center gap-3 min-w-[200px]">
                        <div class="p-2 rounded-lg {{ $currentColor['bg'] }}">
                            <svg class="w-5 h-5 {{ $currentColor['text'] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm text-gray-400">Total Completed</p>
                            <p class="text-2xl font-bold text-white">
                                {{ $contracts->total() }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Action Buttons -->
            <div class="flex gap-3 mt-6 lg:mt-0">
                <!-- Export Button -->
                <button onclick="exportCompleted()"
                        class="glass-card px-6 py-3 rounded-xl flex items-center gap-2 hover:bg-white/5 transition-all duration-300 action-btn">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    Export
                </button>
                
                <!-- Filter Button -->
                <div class="relative">
                    <button id="filterBtn" class="glass-card px-4 py-3 rounded-xl flex items-center gap-2 hover:bg-white/5 transition-all duration-300 action-btn">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                        </svg>
                        Filter
                        <svg class="w-4 h-4 transition-transform duration-200" id="filterArrow" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                    
                    <!-- Filter Dropdown Menu -->
                    <div id="filterDropdown" class="absolute right-0 mt-2 w-64 glass-card rounded-xl shadow-2xl border border-gray-800/50 opacity-0 invisible transition-all duration-200 transform origin-top-right scale-95 z-50">
                        <div class="p-4">
                            <div class="px-3 py-2 text-xs font-semibold text-gray-400 uppercase tracking-wider mb-3 border-b border-gray-800/50">
                                Filter Options
                            </div>
                            
                            <div class="space-y-3">
                                <!-- Date Range -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-300 mb-1">Date Range</label>
                                    <select id="dateFilter" class="w-full bg-dark-800/50 border border-gray-700 rounded-lg px-3 py-2 text-sm focus:border-cyan-500 focus:ring-1 focus:ring-cyan-500 transition-all duration-300 outline-none">
                                        <option value="all">All Time</option>
                                        <option value="today">Today</option>
                                        <option value="week">This Week</option>
                                        <option value="month">This Month</option>
                                        <option value="quarter">This Quarter</option>
                                        <option value="year">This Year</option>
                                    </select>
                                </div>
                                
                                <!-- Sort Order -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-300 mb-1">Sort By</label>
                                    <select id="sortFilter" class="w-full bg-dark-800/50 border border-gray-700 rounded-lg px-3 py-2 text-sm focus:border-cyan-500 focus:ring-1 focus:ring-cyan-500 transition-all duration-300 outline-none">
                                        <option value="newest">Newest First</option>
                                        <option value="oldest">Oldest First</option>
                                        <option value="value_high">Highest Value</option>
                                        <option value="value_low">Lowest Value</option>
                                    </select>
                                </div>
                                
                                <!-- Apply Button -->
                                <div class="pt-2">
                                    <button onclick="applyFilters()" 
                                            class="w-full px-4 py-2 bg-gradient-to-r from-cyan-500 to-blue-500 hover:from-cyan-600 hover:to-blue-600 rounded-lg font-medium text-white transition-all duration-300">
                                        Apply Filters
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Current Category Display -->
        <div class="glass-card rounded-xl p-6 mb-6 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div id="categoryIcon" class="p-2 rounded-lg {{ $currentColor['bg'] }}">
                    <svg class="w-6 h-6 {{ $currentColor['text'] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                </div>
                <div>
                    <h2 class="text-xl font-bold text-white">
                        {{ $department }} Completed Contracts
                    </h2>
                    <p class="text-gray-400 text-sm">
                        Showing contracts where {{ $department }} department contributed to the review process
                    </p>
                </div>
            </div>
            <div class="category-badge {{ strtolower($department) }}">
                {{ $department }}
            </div>
        </div>

        <!-- Search and Filters -->
        <div class="glass-card rounded-xl p-6 mb-6">
            <div class="flex flex-col md:flex-row gap-4 justify-between items-start md:items-center">
                <div class="relative w-full md:w-auto">
                    <div class="absolute left-3 top-1/2 transform -translate-y-1/2">
                        <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                    <input type="text" 
                           id="searchInput"
                           placeholder="Search by contract title, counterparty, or number..." 
                           class="pl-10 pr-4 py-3 w-full md:w-96 bg-dark-800/50 border border-gray-700 rounded-xl focus:border-cyan-500 focus:ring-1 focus:ring-cyan-500 transition-all duration-300 outline-none"
                           value="{{ request('search', '') }}">
                </div>
                
                <div class="flex items-center gap-3">
                    <!-- Contract Type Filter -->
                    <div class="flex items-center gap-2">
                        <span class="text-gray-400 text-sm">Type:</span>
                        <select id="typeFilter" class="bg-dark-800/50 border border-gray-700 rounded-lg px-3 py-2 text-sm focus:border-cyan-500 focus:ring-1 focus:ring-cyan-500 transition-all duration-300 outline-none">
                            <option value="">All Types</option>
                            <option value="sales">Sales</option>
                            <option value="procurement">Procurement</option>
                            <option value="service">Service</option>
                            <option value="partnership">Partnership</option>
                            <option value="nda">NDA</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                    
                    <!-- Counterparty Filter -->
                    <div class="flex items-center gap-2">
                        <span class="text-gray-400 text-sm">Counterparty:</span>
                        <select id="counterpartyFilter" class="bg-dark-800/50 border border-gray-700 rounded-lg px-3 py-2 text-sm focus:border-cyan-500 focus:ring-1 focus:ring-cyan-500 transition-all duration-300 outline-none">
                            <option value="">All Counterparties</option>
                            @foreach($counterparties as $counterparty)
                                <option value="{{ $counterparty }}">{{ $counterparty }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Table - Completed Contracts -->
        <div id="dataTable" class="gradient-border rounded-xl overflow-hidden mb-8">
            <div class="table-container">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="bg-gradient-to-r from-dark-800 to-dark-900 border-b border-gray-800">
                                <th class="py-4 px-6 text-left">
                                    <div class="flex items-center gap-2">
                                        <span class="font-semibold text-gray-300">Contract No.</span>
                                        <button class="text-gray-500 hover:text-cyan-400 transition-colors sort-btn" data-sort="contract_number">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"/>
                                            </svg>
                                        </button>
                                    </div>
                                </th>
                                <th class="py-4 px-6 text-left">
                                    <div class="flex items-center gap-2">
                                        <span class="font-semibold text-gray-300">Contract Title</span>
                                        <button class="text-gray-500 hover:text-cyan-400 transition-colors sort-btn" data-sort="title">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"/>
                                            </svg>
                                        </button>
                                    </div>
                                </th>
                                <th class="py-4 px-6 text-left">
                                    <span class="font-semibold text-gray-300">Counterparty</span>
                                </th>
                                <th class="py-4 px-6 text-left">
                                    <span class="font-semibold text-gray-300">Contract Value</span>
                                </th>
                                <th class="py-4 px-6 text-left">
                                    <span class="font-semibold text-gray-300">Completed Date</span>
                                </th>
                                <th class="py-4 px-6 text-left">
                                    <span class="font-semibold text-gray-300">Department Review</span>
                                </th>
                                <th class="py-4 px-6 text-center">
                                    <span class="font-semibold text-gray-300">Actions</span>
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-800/50" id="contractsTableBody">
                            @forelse ($contracts as $contract)
                            <tr class="table-row-hover group" data-id="{{ $contract->id }}">
                                <!-- Contract Number -->
                                <td class="py-4 px-6">
                                    <div class="flex items-center gap-3">
                                        <div class="p-2 rounded-lg bg-gradient-to-br {{ $currentColor['bg'] }}">
                                            <svg class="w-5 h-5 {{ $currentColor['text'] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                            </svg>
                                        </div>
                                        <div>
                                            <span class="font-mono font-bold text-lg">{{ $contract->contract_number ?? 'N/A' }}</span>
                                            <p class="text-xs text-gray-500 mt-1">
                                                {{ $contract->contract_type ?? 'No type' }}
                                            </p>
                                        </div>
                                    </div>
                                </td>
                                
                                <!-- Contract Title -->
                                <td class="py-4 px-6">
                                    <div class="flex flex-col">
                                        <span class="font-medium text-white group-hover:text-cyan-300 transition-colors">
                                            {{ $contract->title }}
                                        </span>
                                        <span class="text-xs text-gray-400 mt-1 truncate">
                                            {{ Str::limit($contract->description, 60) }}
                                        </span>
                                    </div>
                                </td>
                                
                                <!-- Counterparty -->
                                <td class="py-4 px-6">
                                    <div class="flex flex-col">
                                        <span class="text-sm text-gray-300">
                                            {{ $contract->counterparty_name }}
                                        </span>
                                        <span class="text-xs text-gray-500">
                                            {{ $contract->counterparty_email ?? 'No email' }}
                                        </span>
                                    </div>
                                </td>
                                
                                <!-- Contract Value -->
                                <td class="py-4 px-6">
                                    <div class="flex flex-col">
                                        <span class="text-lg font-bold text-gray-300">
                                            @if($contract->contract_value)
                                                {{ number_format($contract->contract_value, 2) }}
                                            @else
                                                <span class="text-gray-500">N/A</span>
                                            @endif
                                        </span>
                                        <span class="text-xs text-gray-400">
                                            {{ $contract->currency ?? 'IDR' }}
                                        </span>
                                    </div>
                                </td>
                                
                                <!-- Completed Date -->
                                <td class="py-4 px-6">
                                    <div class="flex flex-col">
                                        <span class="text-sm text-gray-300">
                                            {{ $contract->final_approved_at ? $contract->final_approved_at->format('M d, Y') : 'N/A' }}
                                        </span>
                                        <span class="text-xs text-gray-400">
                                            {{ $contract->final_approved_at ? $contract->final_approved_at->format('H:i') : '' }}
                                        </span>
                                    </div>
                                </td>
                                
                                <!-- Department Review -->
                                <td class="py-4 px-6">
                                    <div class="space-y-1">
                                        @foreach($contract->reviewStages->where('stage_type', strtolower($department)) as $stage)
                                            <div class="flex items-center gap-2">
                                                <span class="status-dot-completed"></span>
                                                <span class="text-xs text-gray-300">
                                                    {{ $stage->assignedUser->name ?? 'Unassigned' }}
                                                </span>
                                            </div>
                                        @endforeach
                                        @if($contract->reviewStages->where('stage_type', strtolower($department))->isEmpty())
                                            <span class="text-xs text-gray-500">No {{ $department }} review</span>
                                        @endif
                                    </div>
                                </td>
                                
                                <!-- Actions -->
                                <td class="py-4 px-6">
                                    <div class="flex items-center justify-center gap-2">
                                        <!-- View Button -->
                                        <a href="{{ route('contracts.show', $contract) }}" 
                                           class="p-2 rounded-lg bg-gradient-to-br from-blue-500/10 to-cyan-600/10 hover:from-blue-500/20 hover:to-cyan-600/20 transition-all duration-300"
                                           title="View Contract">
                                            <svg class="w-4 h-4 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                            </svg>
                                        </a>
                                        
                                        <!-- Download Button -->
                                        @if($contract->synology_folder_path)
                                        <a href="{{ $contract->synology_folder_path }}" 
                                           target="_blank"
                                           class="p-2 rounded-lg bg-gradient-to-br from-green-500/10 to-emerald-600/10 hover:from-green-500/20 hover:to-emerald-600/20 transition-all duration-300"
                                           title="Download Document">
                                            <svg class="w-4 h-4 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                            </svg>
                                        </a>
                                        @endif
                                        
                                        <!-- History Button -->
                                        <button onclick="showContractHistory({{ $contract->id }})"
                                                class="p-2 rounded-lg bg-gradient-to-br from-amber-500/10 to-orange-600/10 hover:from-amber-500/20 hover:to-orange-600/20 transition-all duration-300"
                                                title="View Review History">
                                            <svg class="w-4 h-4 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="py-12 px-6 text-center">
                                    <div class="flex flex-col items-center justify-center">
                                        <div class="p-4 rounded-full bg-gradient-to-br from-gray-800/50 to-gray-900/50 mb-4">
                                            <svg class="w-12 h-12 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                            </svg>
                                        </div>
                                        <h3 class="text-xl font-semibold text-gray-300 mb-2">No Completed Contracts Found</h3>
                                        <p class="text-gray-500 max-w-md">
                                            No finalized contracts found where {{ $department }} department contributed to the review process.
                                        </p>
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                
                <!-- Table Footer with Pagination -->
                @if($contracts->hasPages())
                <div class="px-6 py-4 border-t border-gray-800 bg-gradient-to-r from-dark-800/50 to-dark-900/50 flex flex-col sm:flex-row items-center justify-between gap-4">
                    <div class="text-sm text-gray-400">
                        Showing <span class="font-medium text-white">{{ $contracts->firstItem() ?? 0 }}</span> 
                        to <span class="font-medium text-white">{{ $contracts->lastItem() ?? 0 }}</span> 
                        of <span class="font-medium text-white">{{ $contracts->total() }}</span> records
                    </div>
                    
                    <div class="flex items-center gap-2">
                        @if($contracts->onFirstPage())
                        <button class="pagination-btn px-4 py-2 rounded-lg disabled opacity-50 cursor-not-allowed">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                            </svg>
                        </button>
                        @else
                        <button onclick="changePage({{ $contracts->currentPage() - 1 }})" 
                                class="pagination-btn px-4 py-2 rounded-lg hover:bg-white/10">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                            </svg>
                        </button>
                        @endif
                        
                        @php
                            $current = $contracts->currentPage();
                            $last = $contracts->lastPage();
                            $start = max(1, $current - 2);
                            $end = min($last, $current + 2);
                            
                            if($start > 1) {
                                echo '<button onclick="changePage(1)" class="pagination-btn px-4 py-2 rounded-lg">1</button>';
                                if($start > 2) {
                                    echo '<span class="px-2 text-gray-500">...</span>';
                                }
                            }
                            
                            for($i = $start; $i <= $end; $i++) {
                                if($i == $current) {
                                    echo '<button class="pagination-btn active px-4 py-2 rounded-lg">' . $i . '</button>';
                                } else {
                                    echo '<button onclick="changePage(' . $i . ')" class="pagination-btn px-4 py-2 rounded-lg">' . $i . '</button>';
                                }
                            }
                            
                            if($end < $last) {
                                if($end < $last - 1) {
                                    echo '<span class="px-2 text-gray-500">...</span>';
                                }
                                echo '<button onclick="changePage(' . $last . ')" class="pagination-btn px-4 py-2 rounded-lg">' . $last . '</button>';
                            }
                        @endphp
                        
                        @if($contracts->hasMorePages())
                        <button onclick="changePage({{ $contracts->currentPage() + 1 }})" 
                                class="pagination-btn px-4 py-2 rounded-lg hover:bg-white/10">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </button>
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

    <!-- Contract History Modal -->
    <div id="historyModal" class="fixed inset-0 bg-black/70 flex items-center justify-center p-4 z-50 opacity-0 invisible transition-opacity duration-300">
        <div class="glass-card rounded-xl w-full max-w-2xl max-h-[80vh] overflow-hidden transform scale-95 transition-transform duration-300">
            <div class="p-6 border-b border-gray-800 flex items-center justify-between">
                <h3 class="text-xl font-bold text-gray-300">Contract Review History</h3>
                <button onclick="closeHistoryModal()" class="p-2 rounded-lg hover:bg-gray-800/50 transition-colors">
                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <div class="p-6 overflow-y-auto max-h-[60vh]" id="historyContent">
                <!-- History content will be loaded here -->
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Filter Dropdown
            const filterBtn = document.getElementById('filterBtn');
            const filterDropdown = document.getElementById('filterDropdown');
            const filterArrow = document.getElementById('filterArrow');
            let isFilterOpen = false;
            
            if (filterBtn) {
                filterBtn.addEventListener('click', function(e) {
                    e.stopPropagation();
                    toggleFilterDropdown();
                });
                
                document.addEventListener('click', function(e) {
                    if (!filterBtn.contains(e.target) && !filterDropdown.contains(e.target)) {
                        toggleFilterDropdown(false);
                    }
                });
                
                document.addEventListener('keydown', function(e) {
                    if (e.key === 'Escape' && isFilterOpen) {
                        toggleFilterDropdown(false);
                    }
                });
            }
            
            function toggleFilterDropdown(show = null) {
                if (show === null) {
                    isFilterOpen = !isFilterOpen;
                } else {
                    isFilterOpen = show;
                }
                
                if (isFilterOpen) {
                    filterDropdown.classList.remove('opacity-0', 'invisible', 'scale-95');
                    filterDropdown.classList.add('opacity-100', 'visible', 'scale-100');
                    filterArrow.style.transform = 'rotate(180deg)';
                } else {
                    filterDropdown.classList.remove('opacity-100', 'visible', 'scale-100');
                    filterDropdown.classList.add('opacity-0', 'invisible', 'scale-95');
                    filterArrow.style.transform = 'rotate(0deg)';
                }
            }
            
            // Search functionality
            const searchInput = document.getElementById('searchInput');
            if (searchInput) {
                let searchTimeout;
                searchInput.addEventListener('input', function() {
                    clearTimeout(searchTimeout);
                    searchTimeout = setTimeout(() => {
                        performSearch(this.value);
                    }, 500);
                });
                
                searchInput.addEventListener('keypress', function(e) {
                    if (e.key === 'Enter') {
                        performSearch(this.value);
                    }
                });
            }
            
            function performSearch(searchTerm) {
                let url = new URL(window.location.href);
                if (searchTerm.trim()) {
                    url.searchParams.set('search', searchTerm);
                } else {
                    url.searchParams.delete('search');
                }
                url.searchParams.set('page', '1');
                window.location.href = url.toString();
            }
            
            // Filter functionality
            window.applyFilters = function() {
                const dateFilter = document.getElementById('dateFilter').value;
                const sortFilter = document.getElementById('sortFilter').value;
                const typeFilter = document.getElementById('typeFilter').value;
                const counterpartyFilter = document.getElementById('counterpartyFilter').value;
                
                let url = new URL(window.location.href);
                
                if (dateFilter && dateFilter !== 'all') {
                    url.searchParams.set('date_filter', dateFilter);
                } else {
                    url.searchParams.delete('date_filter');
                }
                
                if (sortFilter && sortFilter !== 'newest') {
                    url.searchParams.set('sort', sortFilter);
                } else {
                    url.searchParams.delete('sort');
                }
                
                if (typeFilter) {
                    url.searchParams.set('type', typeFilter);
                } else {
                    url.searchParams.delete('type');
                }
                
                if (counterpartyFilter) {
                    url.searchParams.set('counterparty', counterpartyFilter);
                } else {
                    url.searchParams.delete('counterparty');
                }
                
                url.searchParams.set('page', '1');
                window.location.href = url.toString();
                toggleFilterDropdown(false);
            };
            
            // Populate filters from URL
            const urlParams = new URLSearchParams(window.location.search);
            document.getElementById('dateFilter').value = urlParams.get('date_filter') || 'all';
            document.getElementById('sortFilter').value = urlParams.get('sort') || 'newest';
            document.getElementById('typeFilter').value = urlParams.get('type') || '';
            document.getElementById('counterpartyFilter').value = urlParams.get('counterparty') || '';
            
            // Sort functionality
            document.querySelectorAll('.sort-btn').forEach(button => {
                button.addEventListener('click', function() {
                    const sortField = this.dataset.sort;
                    let sortOrder = 'asc';
                    
                    let url = new URL(window.location.href);
                    const currentSort = url.searchParams.get('sort');
                    const currentOrder = url.searchParams.get('order');
                    
                    if (currentSort === sortField && currentOrder === 'asc') {
                        sortOrder = 'desc';
                    }
                    
                    url.searchParams.set('sort', sortField);
                    url.searchParams.set('order', sortOrder);
                    window.location.href = url.toString();
                });
            });
            
            // Pagination
            window.changePage = function(page) {
                let url = new URL(window.location.href);
                url.searchParams.set('page', page);
                window.location.href = url.toString();
            };
            
            // Export function
            window.exportCompleted = function() {
                // In a real application, this would make an API call to export data
                alert('Export feature would be implemented here. Data would be exported as Excel/CSV.');
            };
            
            // Contract History Modal
            window.showContractHistory = async function(contractId) {
                try {
                    const response = await fetch(`/api/contracts/${contractId}/review-history`);
                    const data = await response.json();
                    
                    let html = `
                        <div class="space-y-4">
                            <div class="p-4 bg-gray-800/50 rounded-lg">
                                <h4 class="font-semibold text-gray-300 mb-2">Contract Information</h4>
                                <p class="text-sm text-gray-400">ID: ${data.contract_number || 'N/A'}</p>
                                <p class="text-sm text-gray-400">Title: ${data.title || 'N/A'}</p>
                            </div>
                            
                            <div class="space-y-3">
                                <h4 class="font-semibold text-gray-300">Review Timeline</h4>
                    `;
                    
                    if (data.review_stages && data.review_stages.length > 0) {
                        data.review_stages.forEach(stage => {
                            html += `
                                <div class="border-l-2 border-blue-500 pl-4 py-2">
                                    <div class="flex items-center justify-between">
                                        <span class="font-medium text-gray-300">${stage.stage_name}</span>
                                        <span class="text-xs px-2 py-1 rounded-full ${stage.status === 'completed' ? 'bg-green-500/10 text-green-400' : 'bg-gray-700 text-gray-400'}">
                                            ${stage.status}
                                        </span>
                                    </div>
                                    <p class="text-sm text-gray-400">${stage.assigned_user_name || 'Unassigned'}</p>
                                    <p class="text-xs text-gray-500">${stage.completed_at || 'Not completed'}</p>
                                </div>
                            `;
                        });
                    } else {
                        html += `<p class="text-gray-400 text-sm">No review history available.</p>`;
                    }
                    
                    html += `</div></div>`;
                    
                    document.getElementById('historyContent').innerHTML = html;
                    
                    // Show modal
                    const modal = document.getElementById('historyModal');
                    modal.classList.remove('opacity-0', 'invisible');
                    modal.classList.add('opacity-100', 'visible');
                    setTimeout(() => {
                        modal.querySelector('.glass-card').classList.remove('scale-95');
                        modal.querySelector('.glass-card').classList.add('scale-100');
                    }, 10);
                } catch (error) {
                    console.error('Error loading contract history:', error);
                    document.getElementById('historyContent').innerHTML = `
                        <div class="text-center py-8">
                            <p class="text-red-400">Error loading contract history</p>
                        </div>
                    `;
                }
            };
            
            window.closeHistoryModal = function() {
                const modal = document.getElementById('historyModal');
                modal.querySelector('.glass-card').classList.remove('scale-100');
                modal.querySelector('.glass-card').classList.add('scale-95');
                setTimeout(() => {
                    modal.classList.remove('opacity-100', 'visible');
                    modal.classList.add('opacity-0', 'invisible');
                }, 200);
            };
            
            // Highlight search
            const searchTerm = "{{ request('search', '') }}";
            if (searchTerm) {
                highlightSearch(searchTerm);
            }
            
            function highlightSearch(term) {
                const rows = document.querySelectorAll('tbody tr');
                const searchTermLower = term.toLowerCase();
                
                rows.forEach(row => {
                    const text = row.textContent.toLowerCase();
                    if (text.includes(searchTermLower)) {
                        walkTextNodes(row, searchTermLower);
                    }
                });
            }
            
            function walkTextNodes(element, searchTerm) {
                const walker = document.createTreeWalker(
                    element,
                    NodeFilter.SHOW_TEXT,
                    null,
                    false
                );
                
                let node;
                const nodes = [];
                while (node = walker.nextNode()) {
                    nodes.push(node);
                }
                
                nodes.forEach(node => {
                    if (node.textContent.toLowerCase().includes(searchTerm)) {
                        const span = document.createElement('span');
                        span.className = 'search-highlight';
                        span.textContent = node.textContent;
                        node.parentNode.replaceChild(span, node);
                    }
                });
            }
            
            // Animate counters
            animateCounters();
            
            function animateCounters() {
                const counters = document.querySelectorAll('.text-2xl.font-bold');
                counters.forEach(counter => {
                    const target = parseInt(counter.textContent.replace(/,/g, ''));
                    if (isNaN(target)) return;
                    
                    const duration = 1000;
                    const step = target / (duration / 16);
                    let current = 0;
                    
                    const timer = setInterval(() => {
                        current += step;
                        if (current >= target) {
                            current = target;
                            clearInterval(timer);
                        }
                        counter.textContent = Math.floor(current).toLocaleString();
                    }, 16);
                });
            }
        });
    </script>

</x-app-layout-dark>