<!-- Filter Section Component -->
<div class="glass-card rounded-xl p-6 mb-6">
    <form id="filterForm" action="{{ route('admin.reports.contracts') }}" method="GET">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <!-- Date Range -->
            <div>
                <label class="block text-sm font-medium text-gray-400 mb-2">Dari Tanggal</label>
                <input type="date" 
                       name="start_date" 
                       value="{{ $activeFilters['start_date'] ?? '' }}"
                       class="filter-input w-full px-4 py-3 rounded-lg text-white">
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-400 mb-2">Sampai Tanggal</label>
                <input type="date" 
                       name="end_date" 
                       value="{{ $activeFilters['end_date'] ?? '' }}"
                       class="filter-input w-full px-4 py-3 rounded-lg text-white">
            </div>
            
            <!-- Status Filter -->
            <div>
                <label class="block text-sm font-medium text-gray-400 mb-2">Status</label>
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
            <div>
                <label class="block text-sm font-medium text-gray-400 mb-2">Tipe Dokumen</label>
                <select name="contract_type" class="filter-select w-full px-4 py-3 rounded-lg">
                    <option value="">Semua Tipe</option>
                    @foreach($documentTypes as $value => $label)
                        <option value="{{ $value }}" {{ ($activeFilters['contract_type'] ?? '') == $value ? 'selected' : '' }}>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
            </div>
            
            <!-- Department Filter (khusus Admin/Legal) -->
            @if(in_array($userRole, ['admin', 'legal']))
            <div>
                <label class="block text-sm font-medium text-gray-400 mb-2">Department</label>
                <select name="department" class="filter-select w-full px-4 py-3 rounded-lg">
                    <option value="">Semua Department</option>
                    <option value="FIN" {{ ($activeFilters['department'] ?? '') == 'FIN' ? 'selected' : '' }}>Finance</option>
                    <option value="ACC" {{ ($activeFilters['department'] ?? '') == 'ACC' ? 'selected' : '' }}>Accounting</option>
                    <option value="TAX" {{ ($activeFilters['department'] ?? '') == 'TAX' ? 'selected' : '' }}>Tax</option>
                    <option value="LEGAL" {{ ($activeFilters['department'] ?? '') == 'LEGAL' ? 'selected' : '' }}>Legal</option>
                </select>
            </div>
            @endif
        </div>
        
        <!-- Action Buttons -->
        <div class="flex flex-wrap items-center justify-end gap-3 mt-6">
            <button type="button" 
                    onclick="window.location.href='{{ route('admin.reports.index') }}'" 
                    class="btn-back px-6 py-3 rounded-xl flex items-center gap-2 transition-all duration-300 action-btn">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                KEMBALI
            </button>
            
            <button type="submit" 
                    class="glass-card px-6 py-3 rounded-xl flex items-center gap-2 hover:bg-white/5 transition-all duration-300 action-btn btn-primary">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                </svg>
                FILTER
            </button>
            
            <button type="button" 
                    onclick="printReport()"
                    class="glass-card px-6 py-3 rounded-xl flex items-center gap-2 hover:bg-white/5 transition-all duration-300 action-btn btn-warning">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                </svg>
                PRINT
            </button>
            
            <button type="button" 
                    onclick="exportExcel()"
                    class="glass-card px-6 py-3 rounded-xl flex items-center gap-2 hover:bg-white/5 transition-all duration-300 action-btn btn-success">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                </svg>
                DOWNLOAD EXCEL
            </button>
        </div>
    </form>
</div>

<script>
    function printReport() {
        const form = document.getElementById('filterForm');
        const formData = new FormData(form);
        const params = new URLSearchParams(formData).toString();
        window.open('{{ route("admin.reports.print") }}?' + params, '_blank');
    }
    
    function exportExcel() {
        const form = document.getElementById('filterForm');
        const formData = new FormData(form);
        const params = new URLSearchParams(formData).toString();
        window.location.href = '{{ route("admin.reports.export-excel") }}?' + params;
    }
</script>