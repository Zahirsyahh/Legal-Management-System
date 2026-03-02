<x-app-layout-dark title="Reports">
    <div class="pb-8 px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto">
        <h1 class="text-3xl font-bold mb-6">Reports Dashboard</h1>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <a href="{{ route('admin.reports.contracts') }}" 
               class="glass-card rounded-xl p-6 hover:bg-white/5 transition-colors">
                <div class="flex items-center">
                    <div class="p-3 bg-blue-500/20 rounded-lg mr-4">
                        <svg class="w-8 h-8 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-xl font-semibold">Contracts Report</h3>
                        <p class="text-gray-400 mt-1">View and export contract data</p>
                    </div>
                </div>
            </a>
            
            <a href="{{ route('admin.reports.users') }}" 
               class="glass-card rounded-xl p-6 hover:bg-white/5 transition-colors">
                <div class="flex items-center">
                    <div class="p-3 bg-green-500/20 rounded-lg mr-4">
                        <svg class="w-8 h-8 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5 1.5l-5.5-5.5" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-xl font-semibold">Users Report</h3>
                        <p class="text-gray-400 mt-1">View user activity and stats</p>
                    </div>
                </div>
            </a>
            
            <a href="{{ route('admin.reports.export', 'contracts') }}" 
               class="glass-card rounded-xl p-6 hover:bg-white/5 transition-colors">
                <div class="flex items-center">
                    <div class="p-3 bg-purple-500/20 rounded-lg mr-4">
                        <svg class="w-8 h-8 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-xl font-semibold">Export Data</h3>
                        <p class="text-gray-400 mt-1">Export reports to CSV/Excel</p>
                    </div>
                </div>
            </a>
        </div>
        
        <!-- Quick Stats -->
        <div class="glass-card rounded-xl p-6">
            <h2 class="text-xl font-semibold mb-4">Quick Statistics</h2>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div>
                    <p class="text-sm text-gray-400">Total Contracts</p>
                    <p class="text-2xl font-bold">{{ \App\Models\Contract::count() }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-400">Active Users</p>
                    <p class="text-2xl font-bold">{{ \App\Models\User::count() }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-400">This Month</p>
                    <p class="text-2xl font-bold">{{ \App\Models\Contract::whereMonth('created_at', now()->month)->count() }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-400">Pending Review</p>
                    <p class="text-2xl font-bold">{{ \App\Models\Contract::whereIn('status', ['submitted', 'legal_reviewing', 'fat_reviewing'])->count() }}</p>
                </div>
            </div>
        </div>
    </div>
</x-app-layout-dark>