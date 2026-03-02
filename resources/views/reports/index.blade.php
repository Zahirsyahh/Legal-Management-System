<x-app-layout-dark title="Laporan">
    
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
        }
        
        .filter-input:focus {
            border-color: rgba(14, 165, 233, 0.5);
            box-shadow: 0 0 0 2px rgba(14, 165, 233, 0.1);
            outline: none;
        }
        
        .filter-select {
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: white;
            transition: all 0.3s ease;
        }
        
        .filter-select:focus {
            border-color: rgba(14, 165, 233, 0.5);
            box-shadow: 0 0 0 2px rgba(14, 165, 233, 0.1);
            outline: none;
        }
        
        .filter-select option {
            background: #1e293b;
            color: white;
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
                            Reports
                        </h1>
                        <p class="text-gray-400 mt-1">Generate dan export laporan kontrak dan surat</p>
                    </div>
                </div>
                
                <!-- Stats Cards -->
                <div class="flex flex-wrap gap-4 mt-6">
                    <div class="glass-card rounded-xl px-4 py-3 flex items-center gap-3 min-w-[200px]">
                        <div class="p-2 rounded-lg bg-gradient-to-br from-cyan-500/10 to-blue-600/10">
                            <svg class="w-5 h-5 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm text-gray-400">Total Kontrak</p>
                            <p id="totalContracts" class="text-2xl font-bold">0</p>
                        </div>
                    </div>
                    
                    <div class="glass-card rounded-xl px-4 py-3 flex items-center gap-3 min-w-[200px]">
                        <div class="p-2 rounded-lg bg-gradient-to-br from-amber-500/10 to-orange-600/10">
                            <svg class="w-5 h-5 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7v8a2 2 0 002 2h6M8 7V5a2 2 0 012-2h4.586a1 1 0 01.707.293l4.414 4.414a1 1 0 01.293.707V15a2 2 0 01-2 2h-2M8 7H6a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2v-2"/>
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm text-gray-400">Dalam Review</p>
                            <p id="inReview" class="text-2xl font-bold">0</p>
                        </div>
                    </div>
                    
                    <div class="glass-card rounded-xl px-4 py-3 flex items-center gap-3 min-w-[200px]">
                        <div class="p-2 rounded-lg bg-gradient-to-br from-green-500/10 to-emerald-600/10">
                            <svg class="w-5 h-5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm text-gray-400">Released</p>
                            <p id="released" class="text-2xl font-bold">0</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Action Buttons -->
            <div class="flex gap-3">
                <button onclick="window.location.href='{{ route('reports.contracts') }}'" 
                        class="glass-card px-6 py-3 rounded-xl flex items-center gap-2 hover:bg-white/5 transition-all duration-300 action-btn btn-primary">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                    Lihat Laporan
                </button>
            </div>
        </div>

        <!-- Quick Access Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <!-- Semua Kontrak -->
            <a href="{{ route('reports.contracts') }}" class="glass-card rounded-xl p-6 hover:bg-white/5 transition-all duration-300 group animate-slide-up">
                <div class="flex items-center justify-between mb-4">
                    <div class="p-3 rounded-lg bg-gradient-to-br from-blue-500/10 to-cyan-600/10">
                        <svg class="w-6 h-6 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                        </svg>
                    </div>
                    <span class="badge-count px-3 py-1 rounded-full text-sm">View All</span>
                </div>
                <h3 class="text-xl font-semibold text-white mb-2">Semua Kontrak</h3>
                <p class="text-gray-400 text-sm">Lihat semua kontrak dan surat</p>
            </a>
            
            <!-- Filter by Status -->
            <div class="glass-card rounded-xl p-6 hover:bg-white/5 transition-all duration-300 group animate-slide-up" style="animation-delay: 0.1s">
                <div class="flex items-center justify-between mb-4">
                    <div class="p-3 rounded-lg bg-gradient-to-br from-amber-500/10 to-orange-600/10">
                        <svg class="w-6 h-6 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                        </svg>
                    </div>
                    <span class="badge-count px-3 py-1 rounded-full text-sm">Filter</span>
                </div>
                <h3 class="text-xl font-semibold text-white mb-2">Filter Status</h3>
                <p class="text-gray-400 text-sm">Filter berdasarkan status dokumen</p>
            </div>
            
            <!-- Export Data -->
            <div class="glass-card rounded-xl p-6 hover:bg-white/5 transition-all duration-300 group animate-slide-up" style="animation-delay: 0.2s">
                <div class="flex items-center justify-between mb-4">
                    <div class="p-3 rounded-lg bg-gradient-to-br from-green-500/10 to-emerald-600/10">
                        <svg class="w-6 h-6 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                        </svg>
                    </div>
                    <span class="badge-count px-3 py-1 rounded-full text-sm">CSV/Excel</span>
                </div>
                <h3 class="text-xl font-semibold text-white mb-2">Export Data</h3>
                <p class="text-gray-400 text-sm">Export ke format CSV atau Excel</p>
            </div>
            
            <!-- Print Report -->
            <div class="glass-card rounded-xl p-6 hover:bg-white/5 transition-all duration-300 group animate-slide-up" style="animation-delay: 0.3s">
                <div class="flex items-center justify-between mb-4">
                    <div class="p-3 rounded-lg bg-gradient-to-br from-purple-500/10 to-pink-600/10">
                        <svg class="w-6 h-6 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                        </svg>
                    </div>
                    <span class="badge-count px-3 py-1 rounded-full text-sm">Print</span>
                </div>
                <h3 class="text-xl font-semibold text-white mb-2">Print Report</h3>
                <p class="text-gray-400 text-sm">Cetak laporan untuk arsip</p>
            </div>
        </div>

        <!-- Recent Reports -->
        <div class="glass-card rounded-xl p-6 mb-6">
            <div class="flex items-center justify-between mb-6">
                <div class="flex items-center gap-3">
                    <div class="p-2 rounded-lg bg-gradient-to-br from-cyan-500/10 to-blue-600/10">
                        <svg class="w-5 h-5 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <h2 class="text-xl font-bold text-white">Recent Reports</h2>
                </div>
                <a href="{{ route('reports.contracts') }}" class="text-cyan-400 hover:text-cyan-300 text-sm flex items-center gap-1">
                    View All
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </a>
            </div>
            
            <!-- Report History Table -->
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="border-b border-gray-800">
                            <th class="py-3 px-4 text-left text-gray-400 font-medium">Date</th>
                            <th class="py-3 px-4 text-left text-gray-400 font-medium">Report Name</th>
                            <th class="py-3 px-4 text-left text-gray-400 font-medium">Type</th>
                            <th class="py-3 px-4 text-left text-gray-400 font-medium">Status</th>
                            <th class="py-3 px-4 text-left text-gray-400 font-medium">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-800/50">
                        <tr class="table-row-hover">
                            <td class="py-3 px-4 text-gray-300">{{ now()->format('d/m/Y H:i') }}</td>
                            <td class="py-3 px-4 text-white">Laporan Kontrak - {{ now()->format('M Y') }}</td>
                            <td class="py-3 px-4">
                                <span class="px-3 py-1 rounded-full text-xs font-medium bg-blue-500/10 text-blue-400 border border-blue-500/20">
                                    Kontrak
                                </span>
                            </td>
                            <td class="py-3 px-4">
                                <span class="status-final_approved px-3 py-1 rounded-full text-xs font-medium">
                                    Approved
                                </span>
                            </td>
                            <td class="py-3 px-4">
                                <div class="flex items-center gap-2">
                                    <button class="p-2 rounded-lg bg-blue-500/10 hover:bg-blue-500/20 transition-colors" title="Download">
                                        <svg class="w-4 h-4 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                                        </svg>
                                    </button>
                                    <button class="p-2 rounded-lg bg-green-500/10 hover:bg-green-500/20 transition-colors" title="Print">
                                        <svg class="w-4 h-4 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                                        </svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            
            // Animate counters
            animateCounters();
        });
        
        function animateCounters() {
            const counters = document.querySelectorAll('.text-2xl.font-bold');
            counters.forEach(counter => {
                const target = parseInt(counter.textContent);
                if (isNaN(target) || target === 0) return;
                
                const duration = 1000;
                const step = target / (duration / 16);
                let current = 0;
                
                const timer = setInterval(() => {
                    current += step;
                    if (current >= target) {
                        current = target;
                        clearInterval(timer);
                    }
                    counter.textContent = Math.floor(current);
                }, 16);
            });
        }
    </script>

</x-app-layout-dark>