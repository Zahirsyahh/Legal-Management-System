{{-- resources/views/departments/admin/master-departments/_table_partial.blade.php --}}

<div id="tableContainer">
    @if($type === 'department')
        {{-- Departments Table - UI Original --}}
        <div id="departmentsTable" class="table-container">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="bg-gradient-to-r from-dark-800 to-dark-900 border-b border-gray-800">
                            <th class="py-4 px-6 text-left">
                                <div class="flex items-center gap-2">
                                    <span class="font-semibold text-gray-300">Code</span>
                                    <button class="text-gray-500 hover:text-cyan-400 transition-colors sort-btn" data-sort="kode_pendek">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"/>
                                        </svg>
                                    </button>
                                </div>
                            </th>
                            <th class="py-4 px-6 text-left">
                                <div class="flex items-center gap-2">
                                    <span class="font-semibold text-gray-300">Department Name</span>
                                    <button class="text-gray-500 hover:text-cyan-400 transition-colors sort-btn" data-sort="nama_departemen">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"/>
                                        </svg>
                                    </button>
                                </div>
                            </th>
                            <th class="py-4 px-6 text-left">
                                <span class="font-semibold text-gray-300">Status</span>
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-800/50" id="departmentsTableBody">
                        @forelse ($departments as $dept)
                        <tr class="table-row-hover group" data-id="{{ $dept->kode_departemen }}">
                            <td class="py-4 px-6">
                                <div class="flex items-center gap-3">
                                    <div class="p-2 rounded-lg bg-gradient-to-br from-cyan-500/10 to-blue-600/10">
                                        <svg class="w-5 h-5 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/>
                                        </svg>
                                    </div>
                                    <div>
                                        <span class="font-mono font-bold text-lg">{{ $dept->kode_pendek ?? '-' }}</span>
                                        <p class="text-xs text-gray-500 mt-1">{{ $dept->kode_departemen ?? '-' }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="py-4 px-6">
                                <div class="flex flex-col">
                                    <span class="font-medium text-white group-hover:text-cyan-300 transition-colors">
                                        {{ $dept->nama_departemen ?? '-' }}
                                    </span>
                                </div>
                            </td>
                            <td class="py-4 px-6">
                                <div class="inline-flex items-center gap-2">
                                    <span class="relative flex h-3 w-3">
                                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
                                        <span class="relative inline-flex rounded-full h-3 w-3 bg-green-500"></span>
                                    </span>
                                    <span class="status-active px-3 py-1.5 rounded-full text-sm font-medium">
                                        Active
                                    </span>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="3" class="py-12 px-6 text-center">
                                <div class="flex flex-col items-center justify-center">
                                    <div class="p-4 rounded-full bg-gradient-to-br from-gray-800/50 to-gray-900/50 mb-4">
                                        <svg class="w-12 h-12 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                                        </svg>
                                    </div>
                                    <h3 class="text-xl font-semibold text-gray-300 mb-2">No Departments Found</h3>
                                    <p class="text-gray-500 max-w-md">No departments match your search criteria</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            {{-- Pagination Departments --}}
            @if($departments->hasPages())
            <div class="px-6 py-4 border-t border-gray-800 bg-gradient-to-r from-dark-800/50 to-dark-900/50 flex flex-col sm:flex-row items-center justify-between gap-4">
                <div class="text-sm text-gray-400">
                    Showing <span class="font-medium text-white">{{ $departments->firstItem() ?? 0 }}</span> 
                    to <span class="font-medium text-white">{{ $departments->lastItem() ?? 0 }}</span> 
                    of <span class="font-medium text-white">{{ $departments->total() }}</span> records
                </div>
                
                <div class="flex items-center gap-2">
                    @if($departments->onFirstPage())
                    <button class="pagination-btn px-4 py-2 rounded-lg disabled opacity-50 cursor-not-allowed">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                        </svg>
                    </button>
                    @else
                    <button data-page="{{ $departments->currentPage() - 1 }}" 
                            class="pagination-btn px-4 py-2 rounded-lg hover:bg-white/10">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                        </svg>
                    </button>
                    @endif
                    
                    @php
                        $current = $departments->currentPage();
                        $last = $departments->lastPage();
                        $start = max(1, $current - 2);
                        $end = min($last, $current + 2);
                    @endphp
                    
                    @if($start > 1)
                        <button data-page="1" class="pagination-btn px-4 py-2 rounded-lg">1</button>
                        @if($start > 2)
                            <span class="px-2 text-gray-500">...</span>
                        @endif
                    @endif
                    
                    @for($i = $start; $i <= $end; $i++)
                        @if($i == $current)
                        <button class="pagination-btn active px-4 py-2 rounded-lg">{{ $i }}</button>
                        @else
                        <button data-page="{{ $i }}" class="pagination-btn px-4 py-2 rounded-lg">{{ $i }}</button>
                        @endif
                    @endfor
                    
                    @if($end < $last)
                        @if($end < $last - 1)
                            <span class="px-2 text-gray-500">...</span>
                        @endif
                        <button data-page="{{ $last }}" class="pagination-btn px-4 py-2 rounded-lg">{{ $last }}</button>
                    @endif
                    
                    @if($departments->hasMorePages())
                    <button data-page="{{ $departments->currentPage() + 1 }}" 
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
        
    @elseif($type === 'jabatan')
        {{-- Jabatan Table - UI Original --}}
        <div id="jabatanTable" class="table-container">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="bg-gradient-to-r from-dark-800 to-dark-900 border-b border-gray-800">
                            <th class="py-4 px-6 text-left">
                                <div class="flex items-center gap-2">
                                    <span class="font-semibold text-gray-300">No</span>
                                </div>
                            </th>
                            <th class="py-4 px-6 text-left">
                                <div class="flex items-center gap-2">
                                    <span class="font-semibold text-gray-300">Jabatan Name</span>
                                    <button class="text-gray-500 hover:text-amber-400 transition-colors sort-btn" data-sort="nama_jabatan">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"/>
                                        </svg>
                                    </button>
                                </div>
                            </th>
                            <th class="py-4 px-6 text-center">
                                <span class="font-semibold text-gray-300">Status</span>
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-800/50" id="jabatanTableBody">
                        @forelse ($jabatans as $index => $jabatan)
                        <tr class="table-row-hover group" data-id="{{ $jabatan->no_jabatan }}">
                            <td class="py-4 px-6">
                                <div class="flex items-center gap-3">
                                    <div class="p-2 rounded-lg bg-gradient-to-br from-amber-500/10 to-orange-600/10">
                                        <svg class="w-5 h-5 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                        </svg>
                                    </div>
                                    <div>
                                        <span class="font-mono font-bold text-lg">{{ $jabatans->firstItem() + $index }}</span>
                                    </div>
                                </div>
                            </td>
                            <td class="py-4 px-6">
                                <div class="flex flex-col">
                                    <span class="font-medium text-white group-hover:text-amber-300 transition-colors">
                                        {{ $jabatan->nama_jabatan ?? '-' }}
                                    </span>
                                </div>
                            </td>
                            <td class="py-4 px-6 text-center">
                                <div class="inline-flex items-center justify-center gap-2">
                                    @if(($jabatan->status_jabatan ?? 'Aktif') === 'Aktif')
                                    <span class="status-dot-active"></span>
                                    <span class="status-active px-3 py-1.5 rounded-full text-sm font-medium">
                                        Active
                                    </span>
                                    @else
                                    <span class="status-dot-inactive"></span>
                                    <span class="status-inactive px-3 py-1.5 rounded-full text-sm font-medium">
                                        Inactive
                                    </span>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="3" class="py-12 px-6 text-center">
                                <div class="flex flex-col items-center justify-center">
                                    <div class="p-4 rounded-full bg-gradient-to-br from-gray-800/50 to-gray-900/50 mb-4">
                                        <svg class="w-12 h-12 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                        </svg>
                                    </div>
                                    <h3 class="text-xl font-semibold text-gray-300 mb-2">No Positions Found</h3>
                                    <p class="text-gray-500 max-w-md">No positions match your search criteria</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            {{-- Pagination Jabatan --}}
            @if($jabatans->hasPages())
            <div class="px-6 py-4 border-t border-gray-800 bg-gradient-to-r from-dark-800/50 to-dark-900/50 flex flex-col sm:flex-row items-center justify-between gap-4">
                <div class="text-sm text-gray-400">
                    Showing <span class="font-medium text-white">{{ $jabatans->firstItem() ?? 0 }}</span> 
                    to <span class="font-medium text-white">{{ $jabatans->lastItem() ?? 0 }}</span> 
                    of <span class="font-medium text-white">{{ $jabatans->total() }}</span> records
                </div>
                
                <div class="flex items-center gap-2">
                    @if($jabatans->onFirstPage())
                    <button class="pagination-btn px-4 py-2 rounded-lg disabled opacity-50 cursor-not-allowed">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                        </svg>
                    </button>
                    @else
                    <button data-page="{{ $jabatans->currentPage() - 1 }}" 
                            class="pagination-btn px-4 py-2 rounded-lg hover:bg-white/10">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                        </svg>
                    </button>
                    @endif
                    
                    @php
                        $current = $jabatans->currentPage();
                        $last = $jabatans->lastPage();
                        $start = max(1, $current - 2);
                        $end = min($last, $current + 2);
                    @endphp
                    
                    @if($start > 1)
                        <button data-page="1" class="pagination-btn px-4 py-2 rounded-lg">1</button>
                        @if($start > 2)
                            <span class="px-2 text-gray-500">...</span>
                        @endif
                    @endif
                    
                    @for($i = $start; $i <= $end; $i++)
                        @if($i == $current)
                        <button class="pagination-btn active px-4 py-2 rounded-lg">{{ $i }}</button>
                        @else
                        <button data-page="{{ $i }}" class="pagination-btn px-4 py-2 rounded-lg">{{ $i }}</button>
                        @endif
                    @endfor
                    
                    @if($end < $last)
                        @if($end < $last - 1)
                            <span class="px-2 text-gray-500">...</span>
                        @endif
                        <button data-page="{{ $last }}" class="pagination-btn px-4 py-2 rounded-lg">{{ $last }}</button>
                    @endif
                    
                    @if($jabatans->hasMorePages())
                    <button data-page="{{ $jabatans->currentPage() + 1 }}" 
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
        
    @elseif($type === 'user')
        {{-- Users Table - UI Original --}}
        <div id="usersTable" class="table-container">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="bg-gradient-to-r from-dark-800 to-dark-900 border-b border-gray-800">
                            <th class="py-4 px-6 text-left">
                                <div class="flex items-center gap-2">
                                    <span class="font-semibold text-gray-300">User ID</span>
                                </div>
                            </th>
                            <th class="py-4 px-6 text-left">
                                <div class="flex items-center gap-2">
                                    <span class="font-semibold text-gray-300">Name</span>
                                    <button class="text-gray-500 hover:text-green-400 transition-colors sort-btn" data-sort="nama_user">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"/>
                                        </svg>
                                    </button>
                                </div>
                            </th>
                            <th class="py-4 px-6 text-left">
                                <span class="font-semibold text-gray-300">Username / Email</span>
                            </th>
                            <th class="py-4 px-6 text-left">
                                <span class="font-semibold text-gray-300">Position</span>
                            </th>
                            <th class="py-4 px-6 text-left">
                                <span class="font-semibold text-gray-300">Department</span>
                            </th>
                            <th class="py-4 px-6 text-left">
                                <span class="font-semibold text-gray-300">Status</span>
                            </th>
                            <th class="py-4 px-6 text-center">
                                <span class="font-semibold text-gray-300">Actions</span>
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-800/50" id="usersTableBody">
                        @forelse ($users as $user)
                        <tr class="table-row-hover group" data-id="{{ $user->id_user }}">
                            <td class="py-4 px-6">
                                <div class="flex items-center gap-3">
                                    <div class="p-2 rounded-lg bg-gradient-to-br from-green-500/10 to-emerald-600/10">
                                        <svg class="w-5 h-5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                        </svg>
                                    </div>
                                    <div>
                                        <span class="font-mono font-bold text-lg">{{ $user->id_user }}</span>
                                    </div>
                                </div>
                            </td>
                            <td class="py-4 px-6">
                                <div class="flex flex-col">
                                    <span class="font-medium text-white group-hover:text-green-300 transition-colors">
                                        {{ $user->nama_user ?? $user->username ?? '-' }}
                                    </span>
                                    <span class="text-xs text-gray-500 mt-1">
                                        NIK: {{ $user->nik ?? 'N/A' }}
                                    </span>
                                </div>
                            </td>
                            <td class="py-4 px-6">
                                <div class="flex flex-col">
                                    <span class="text-sm text-gray-300">
                                        {{ $user->username ?? '-' }}
                                    </span>
                                    <span class="text-xs text-gray-500">
                                        {{ $user->email ?? 'No email' }}
                                    </span>
                                </div>
                            </td>
                            <td class="py-4 px-6">
                                <div class="inline-flex">
                                    <span class="px-3 py-1.5 text-sm font-medium rounded-full bg-gradient-to-br from-amber-500/10 to-orange-600/10 text-amber-300 border border-amber-500/20">
                                        {{ $user->jabatan ?? 'N/A' }}
                                    </span>
                                </div>
                            </td>
                            <td class="py-4 px-6">
                                <div class="flex flex-col">
                                    <span class="text-sm text-gray-300">
                                        {{ $user->department->nama_departemen ?? 'N/A' }}
                                    </span>
                                    <span class="text-xs text-gray-500">
                                        {{ $user->kode_department ?? 'No code' }}
                                    </span>
                                </div>
                            </td>
                            <td class="py-4 px-6">
                                <div class="inline-flex items-center gap-2">
                                    @if(($user->status_karyawan ?? 'AKTIF') === 'AKTIF')
                                    <span class="status-dot-active"></span>
                                    <span class="status-active px-3 py-1.5 rounded-full text-sm font-medium">
                                        Active
                                    </span>
                                    @else
                                    <span class="status-dot-inactive"></span>
                                    <span class="status-inactive px-3 py-1.5 rounded-full text-sm font-medium">
                                        Inactive
                                    </span>
                                    @endif
                                </div>
                            </td>
                            <td class="py-4 px-6">
                                <div class="flex items-center justify-center gap-2">
                                    <a href="{{ route('admin.master-departments.edit', ['master_department' => $user->id_user, 'type' => 'user']) }}" 
                                       class="p-2 rounded-lg bg-gradient-to-br from-blue-500/10 to-cyan-600/10 hover:from-blue-500/20 hover:to-cyan-600/20 transition-all duration-300"
                                       title="Edit User">
                                        <svg class="w-4 h-4 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                    </a>
                                    
                                    <form action="{{ route('admin.master-departments.destroy', ['master_department' => $user->id_user, 'type' => 'user']) }}" 
                                          method="POST" 
                                          class="inline"
                                          onsubmit="return confirmDelete('{{ $user->nama_user ?? $user->username }}')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" 
                                                class="p-2 rounded-lg bg-gradient-to-br from-red-500/10 to-pink-600/10 hover:from-red-500/20 hover:to-pink-600/20 transition-all duration-300"
                                                title="Delete User">
                                            <svg class="w-4 h-4 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                            </svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="py-12 px-6 text-center">
                                <div class="flex flex-col items-center justify-center">
                                    <div class="p-4 rounded-full bg-gradient-to-br from-gray-800/50 to-gray-900/50 mb-4">
                                        <svg class="w-12 h-12 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5 1.5l-5.5-5.5"/>
                                        </svg>
                                    </div>
                                    <h3 class="text-xl font-semibold text-gray-300 mb-2">No Users Found</h3>
                                    <p class="text-gray-500 max-w-md">No users match your search criteria</p>
                                    <div class="mt-6">
                                        <a href="{{ route('admin.master-departments.create', ['type' => 'user']) }}" 
                                           class="px-5 py-3 bg-gradient-to-r from-green-600 to-emerald-600 rounded-lg hover:from-green-500 hover:to-emerald-500 transition-all duration-300">
                                            Add Your First User
                                        </a>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            {{-- Pagination Users --}}
            @if($users->hasPages())
            <div class="px-6 py-4 border-t border-gray-800 bg-gradient-to-r from-dark-800/50 to-dark-900/50 flex flex-col sm:flex-row items-center justify-between gap-4">
                <div class="text-sm text-gray-400">
                    Showing <span class="font-medium text-white">{{ $users->firstItem() ?? 0 }}</span> 
                    to <span class="font-medium text-white">{{ $users->lastItem() ?? 0 }}</span> 
                    of <span class="font-medium text-white">{{ $users->total() }}</span> records
                </div>
                
                <div class="flex items-center gap-2">
                    @if($users->onFirstPage())
                    <button class="pagination-btn px-4 py-2 rounded-lg disabled opacity-50 cursor-not-allowed">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                        </svg>
                    </button>
                    @else
                    <button data-page="{{ $users->currentPage() - 1 }}" 
                            class="pagination-btn px-4 py-2 rounded-lg hover:bg-white/10">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                        </svg>
                    </button>
                    @endif
                    
                    @php
                        $current = $users->currentPage();
                        $last = $users->lastPage();
                        $start = max(1, $current - 2);
                        $end = min($last, $current + 2);
                    @endphp
                    
                    @if($start > 1)
                        <button data-page="1" class="pagination-btn px-4 py-2 rounded-lg">1</button>
                        @if($start > 2)
                            <span class="px-2 text-gray-500">...</span>
                        @endif
                    @endif
                    
                    @for($i = $start; $i <= $end; $i++)
                        @if($i == $current)
                        <button class="pagination-btn active px-4 py-2 rounded-lg">{{ $i }}</button>
                        @else
                        <button data-page="{{ $i }}" class="pagination-btn px-4 py-2 rounded-lg">{{ $i }}</button>
                        @endif
                    @endfor
                    
                    @if($end < $last)
                        @if($end < $last - 1)
                            <span class="px-2 text-gray-500">...</span>
                        @endif
                        <button data-page="{{ $last }}" class="pagination-btn px-4 py-2 rounded-lg">{{ $last }}</button>
                    @endif
                    
                    @if($users->hasMorePages())
                    <button data-page="{{ $users->currentPage() + 1 }}" 
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
    @endif
</div>