<x-app-layout-dark title="User Management">
    <div class="pb-8 px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto">
        <!-- Header -->
        <div class="mb-8">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div>
                    <h1 class="text-3xl md:text-4xl font-bold mb-2">Role Management</h1>
                    <p class="text-gray-400">Manage user's roles</p>
                </div>
                <a href="{{ route('admin.users.create') }}" 
                   class="btn-primary flex items-center px-5 py-3 rounded-lg">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Add New User
                </a>
            </div>
        </div>

        <!-- Search and Filters -->
        <div class="glass-card rounded-xl p-6 mb-6">
            <div class="flex flex-col md:flex-row gap-4 justify-between items-start md:items-center">
                <div class="relative w-full md:w-auto">
                    <div class="absolute left-3 top-1/2 transform -translate-y-1/2">
                        <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </div>
                    <input type="text" 
                           id="searchInput"
                           placeholder="Search by name, email, or role..." 
                           class="pl-10 pr-4 py-3 w-full md:w-96 bg-dark-800/50 border border-gray-700 rounded-xl focus:border-cyan-500 focus:ring-1 focus:ring-cyan-500 transition-all duration-300 outline-none"
                           value="{{ request('search', '') }}">
                    @if(request('search'))
                        <button id="clearSearch" 
                                class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-500 hover:text-white">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    @endif
                </div>
                <div class="flex gap-2">
                    <form method="GET" action="{{ route('admin.users.index') }}" id="searchForm">
                        <input type="hidden" name="search" id="searchValue" value="{{ request('search', '') }}">
                        <button type="submit" 
                                class="px-5 py-3 bg-cyan-600 hover:bg-cyan-700 text-white rounded-xl font-medium transition-colors">
                            Search
                        </button>
                    </form>
                    @if(request('search') || request('role_filter'))
                    <a href="{{ route('admin.users.index') }}" 
                       class="px-5 py-3 bg-gray-700 hover:bg-gray-600 text-white rounded-xl font-medium transition-colors">
                        Clear
                    </a>
                    @endif
                </div>
            </div>
            @if(request('search'))
                <p class="mt-3 text-sm text-gray-400">
                    Showing results for: <span class="text-cyan-300 font-medium">"{{ request('search') }}"</span>
                    @if($users->total() > 0)
                        <span class="text-gray-500">({{ $users->total() }} found)</span>
                    @endif
                </p>
            @endif
        </div>

        <!-- Flash Messages -->
        @if(session('success'))
            <div class="mb-6 p-4 bg-green-500/10 border border-green-500/30 rounded-xl">
                <p class="text-green-400">{{ session('success') }}</p>
            </div>
        @endif
        
        @if(session('error'))
            <div class="mb-6 p-4 bg-red-500/10 border border-red-500/30 rounded-xl">
                <p class="text-red-400">{{ session('error') }}</p>
            </div>
        @endif

        <!-- User Stats -->
        <div class="grid grid-cols-1 md:grid-cols-6 gap-4 mb-6">
            <div class="glass-card rounded-xl p-4">
                <p class="text-sm text-gray-400">Total Users</p>
                <p class="text-2xl font-bold mt-1">{{ $stats['total'] }}</p>
            </div>
            <div class="glass-card rounded-xl p-4">
                <p class="text-sm text-gray-400">Admins</p>
                <p class="text-2xl font-bold mt-1">{{ $stats['admins'] }}</p>
            </div>
            <div class="glass-card rounded-xl p-4">
                <p class="text-sm text-gray-400">Legal Team</p>
                <p class="text-2xl font-bold mt-1">{{ $stats['legal'] }}</p>
            </div>
            <!-- Finance Team -->
            <div class="glass-card rounded-xl p-4">
                <p class="text-sm text-gray-400">Finance Team</p>
                <p class="text-2xl font-bold mt-1">{{ $stats['staff_fin'] }}</p>
            </div>
            <!-- Accounting Team -->
            <div class="glass-card rounded-xl p-4">
                <p class="text-sm text-gray-400">Accounting Team</p>
                <p class="text-2xl font-bold mt-1">{{ $stats['staff_acc'] }}</p>
            </div>
            <!-- Tax Team -->
            <div class="glass-card rounded-xl p-4">
                <p class="text-sm text-gray-400">Tax Team</p>
                <p class="text-2xl font-bold mt-1">{{ $stats['staff_tax'] }}</p>
            </div>
        </div>

        <!-- Users Table -->
        <div class="glass-card rounded-xl overflow-hidden">
            @if($users->count() > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-800">
                        <thead>
                            <tr class="bg-dark-800/50">
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase">Name</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase">Email</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase">Role</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase">Joined</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-800">
                            @foreach($users as $user)
                            <tr class="hover:bg-dark-800/30 transition-colors">
                                <td class="px-6 py-4">
                                    <div class="flex items-center">
                                        <div class="w-8 h-8 rounded-full bg-gradient-to-r from-blue-500 to-cyan-500 flex items-center justify-center mr-3">
                                            <span class="text-xs font-medium">{{ strtoupper(substr($user->name, 0, 1)) }}</span>
                                        </div>
                                        <div>
                                            <p class="text-sm font-medium text-gray-300">{{ $user->name }}</p>
                                            <p class="text-xs text-gray-500">ID: {{ $user->id_user }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-300">{{ $user->email }}</td>
                                <td class="px-6 py-4">
                                    @foreach($user->roles as $role)
                                    <span class="px-2 py-1 text-xs rounded-full 
                                        {{ $role->name == 'admin' ? 'bg-red-500/20 text-red-300' : 
                                           ($role->name == 'legal' ? 'bg-blue-500/20 text-blue-300' : 
                                           ($role->name == 'staff_fin' ? 'bg-purple-500/20 text-purple-300' : 
                                           ($role->name == 'staff_acc' ? 'bg-indigo-500/20 text-indigo-300' : 
                                           ($role->name == 'staff_tax' ? 'bg-emerald-500/20 text-emerald-300' : 
                                           'bg-gray-700 text-gray-300')))) }}">
                                        {{ ucfirst(str_replace('staff_', '', $role->name)) }}
                                    </span>
                                    @endforeach
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-400">
                                    @php
                                        $date = $user->created_at ?? $user->tgl_masuk_karyawan ?? now();
                                    @endphp
                                    {{ \Carbon\Carbon::parse($date)->format('M d, Y') }}
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex space-x-2">
                                        <a href="{{ route('admin.users.edit', $user) }}" 
                                           class="text-green-400 hover:text-green-300" title="Edit">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                            </svg>
                                        </a>
                                        <form action="{{ route('admin.users.destroy', $user) }}" 
                                              method="POST" 
                                              class="inline"
                                              onsubmit="return confirm('Delete this user? This action cannot be undone.')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" 
                                                    class="text-red-400 hover:text-red-300 {{ $user->id === auth()->id() ? 'opacity-50 cursor-not-allowed' : '' }}"
                                                    {{ $user->id_user === auth()->id() ? 'disabled' : '' }}
                                                    title="Delete">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                
                <!-- Pagination -->
                <div class="px-6 py-4 border-t border-gray-800">
                    {{ $users->appends(['search' => request('search')])->links() }}
                </div>
            @else
                <div class="text-center py-12">
                    <svg class="mx-auto h-16 w-16 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5 1.5l-5.5-5.5" />
                    </svg>
                    <h3 class="mt-6 text-lg font-medium text-gray-300">No users found</h3>
                    <p class="mt-2 text-gray-500">
                        @if(request('search'))
                            No users found for "{{ request('search') }}"
                        @else
                            Get started by creating the first user account
                        @endif
                    </p>
                    <div class="mt-6">
                        @if(request('search'))
                            <a href="{{ route('admin.users.index') }}" 
                               class="inline-flex items-center px-5 py-3 btn-secondary rounded-lg mr-3">
                                Clear Search
                            </a>
                        @endif
                        <a href="{{ route('admin.users.create') }}" 
                           class="inline-flex items-center px-5 py-3 btn-primary rounded-lg">
                            Create First User
                        </a>
                    </div>
                </div>
            @endif
        </div>
    </div>

    @push('scripts')
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const searchInput = document.getElementById('searchInput');
        const searchValue = document.getElementById('searchValue');
        const searchForm = document.getElementById('searchForm');
        const clearSearch = document.getElementById('clearSearch');
        
        // Auto submit after 800ms of typing
        let searchTimeout;
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(function() {
                searchValue.value = searchInput.value;
                searchForm.submit();
            }, 800);
        });
        
        // Enter key to submit immediately
        searchInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                searchValue.value = searchInput.value;
                searchForm.submit();
            }
        });
        
        // Clear search button
        if (clearSearch) {
            clearSearch.addEventListener('click', function() {
                searchInput.value = '';
                searchValue.value = '';
                searchForm.submit();
            });
        }
        
        // Auto-focus search input
        @if(request('search'))
            searchInput.focus();
            searchInput.setSelectionRange(searchInput.value.length, searchInput.value.length);
        @endif
    });
    </script>
    @endpush
</x-app-layout-dark>