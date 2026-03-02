<x-app-layout-dark title="Notifications">
    <div class="pb-8 px-4 sm:px-6 lg:px-8 max-w-4xl mx-auto">
        <!-- Success Message -->
        @if(session('success'))
            <div class="mb-6 p-4 bg-green-500/10 border border-green-500/20 rounded-xl">
                <div class="flex items-center">
                    <i class="fas fa-check-circle text-green-400 mr-3"></i>
                    <p class="text-green-300">{{ session('success') }}</p>
                </div>
            </div>
        @endif
        
        <!-- Header dengan gradient -->
        <div class="mb-8">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
                <div>
                    <div class="flex items-center gap-3 mb-2">
                        <div class="p-3 rounded-xl bg-gradient-to-r from-blue-500/20 to-purple-500/20">
                            <i class="fas fa-bell text-2xl text-blue-400"></i>
                        </div>
                        <div>
                            <h1 class="text-3xl font-bold bg-gradient-to-r from-blue-400 to-purple-400 bg-clip-text text-transparent">
                                Notifications
                            </h1>
                            <p class="text-gray-400 mt-1">Stay updated with your activities</p>
                        </div>
                    </div>
                </div>
                
                @if(auth()->user()->unreadNotifications()->count() > 0)
                    <div class="flex items-center gap-3">
                        <div class="relative">
                            <span class="animate-ping absolute -top-1 -right-1 h-3 w-3 rounded-full bg-red-500 opacity-75"></span>
                            <span class="relative flex h-5 w-5">
                                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-400 opacity-75"></span>
                                <span class="relative inline-flex rounded-full h-5 w-5 bg-red-500 items-center justify-center text-xs font-bold">
                                    {{ auth()->user()->unreadNotifications()->count() }}
                                </span>
                            </span>
                        </div>
                        <form action="{{ route('notifications.read-all') }}" method="POST">
                            @csrf
                            <button type="submit" 
                                    class="px-4 py-2 bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 rounded-lg text-sm font-medium shadow-lg hover:shadow-xl transition-all duration-200">
                                <i class="fas fa-check-double mr-2"></i>
                                Mark all as read
                            </button>
                        </form>
                    </div>
                @endif
            </div>
            
            <!-- Stats Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-8">
                <div class="bg-gradient-to-br from-gray-800 to-gray-900 rounded-xl p-4 border border-gray-700 shadow-lg">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-gray-400 text-sm">Total</p>
                            <p class="text-2xl font-bold text-white">{{ auth()->user()->notifications()->count() }}</p>
                        </div>
                        <div class="p-3 rounded-lg bg-gray-700/50">
                            <i class="fas fa-bell text-blue-400"></i>
                        </div>
                    </div>
                </div>
                
                <div class="bg-gradient-to-br from-gray-800 to-gray-900 rounded-xl p-4 border border-gray-700 shadow-lg">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-gray-400 text-sm">Unread</p>
                            <p class="text-2xl font-bold text-yellow-400">{{ auth()->user()->unreadNotifications()->count() }}</p>
                        </div>
                        <div class="p-3 rounded-lg bg-gray-700/50">
                            <i class="fas fa-envelope text-yellow-400"></i>
                        </div>
                    </div>
                </div>
                
                <div class="bg-gradient-to-br from-gray-800 to-gray-900 rounded-xl p-4 border border-gray-700 shadow-lg">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-gray-400 text-sm">Today</p>
                            <p class="text-2xl font-bold text-green-400">{{ auth()->user()->notifications()->whereDate('created_at', today())->count() }}</p>
                        </div>
                        <div class="p-3 rounded-lg bg-gray-700/50">
                            <i class="fas fa-calendar-day text-green-400"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Filter Tabs -->
        <div class="flex space-x-2 mb-6 overflow-x-auto pb-2">
            <a href="{{ route('notifications.index', ['filter' => 'all']) }}" 
               class="px-4 py-2 rounded-lg text-sm font-medium transition-all duration-200 
                      {{ request('filter', 'all') === 'all' ? 'bg-blue-600 text-white' : 'bg-gray-800 text-gray-400 hover:bg-gray-700' }}">
                All Notifications
            </a>
            <a href="{{ route('notifications.index', ['filter' => 'unread']) }}" 
               class="px-4 py-2 rounded-lg text-sm font-medium transition-all duration-200 
                      {{ request('filter') === 'unread' ? 'bg-yellow-600 text-white' : 'bg-gray-800 text-gray-400 hover:bg-gray-700' }}">
                Unread Only
            </a>
            <a href="{{ route('notifications.index', ['filter' => 'today']) }}" 
               class="px-4 py-2 rounded-lg text-sm font-medium transition-all duration-200 
                      {{ request('filter') === 'today' ? 'bg-green-600 text-white' : 'bg-gray-800 text-gray-400 hover:bg-gray-700' }}">
                Today
            </a>
        </div>
        
        <!-- Notifications List -->
        <div class="space-y-4">
            @forelse($notifications as $notification)
                @php
                    $data = $notification->data;
                    $isUnread = is_null($notification->read_at);
                    
                    // Determine notification type color
                    $typeColors = [
                        'stage_assigned' => ['bg' => 'from-blue-500/20 to-blue-600/20', 'icon' => 'fa-user-check', 'text' => 'text-blue-400'],
                        'department_assignment' => ['bg' => 'from-purple-500/20 to-purple-600/20', 'icon' => 'fa-building', 'text' => 'text-purple-400'],
                        'staff_assigned' => ['bg' => 'from-green-500/20 to-green-600/20', 'icon' => 'fa-users', 'text' => 'text-green-400'],
                        'stage_jumped' => ['bg' => 'from-yellow-500/20 to-yellow-600/20', 'icon' => 'fa-exchange-alt', 'text' => 'text-yellow-400'],
                        'revision_requested' => ['bg' => 'from-orange-500/20 to-orange-600/20', 'icon' => 'fa-edit', 'text' => 'text-orange-400'],
                        'contract_rejected' => ['bg' => 'from-red-500/20 to-red-600/20', 'icon' => 'fa-times-circle', 'text' => 'text-red-400'],
                        'contract_review_started' => ['bg' => 'from-indigo-500/20 to-indigo-600/20', 'icon' => 'fa-play-circle', 'text' => 'text-indigo-400'],
                    ];
                    
                    $typeConfig = $typeColors[$data['type'] ?? ''] ?? ['bg' => 'from-gray-500/20 to-gray-600/20', 'icon' => 'fa-bell', 'text' => 'text-gray-400'];
                @endphp
                
                <div class="group relative">
                    <!-- Unread indicator -->
                    @if($isUnread)
                        <div class="absolute -left-4 top-1/2 transform -translate-y-1/2">
                            <div class="w-2 h-2 rounded-full bg-blue-500 animate-pulse"></div>
                        </div>
                    @endif
                    
                    <div class="bg-gradient-to-br from-gray-800 to-gray-900 rounded-xl p-4 border {{ $isUnread ? 'border-blue-500/30 shadow-lg shadow-blue-500/10' : 'border-gray-700' }} 
                                hover:border-gray-600 hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1">
                        
                        <!-- Notification Header -->
                        <div class="flex items-start justify-between mb-3">
                            <div class="flex items-center space-x-3">
                                <div class="p-3 rounded-xl bg-gradient-to-r {{ $typeConfig['bg'] }}">
                                    <i class="fas {{ $typeConfig['icon'] }} {{ $typeConfig['text'] }} text-lg"></i>
                                </div>
                                <div>
                                    <div class="flex items-center gap-2">
                                        <h3 class="font-bold text-white">
                                            {{ ucwords(str_replace('_', ' ', $data['type'] ?? 'notification')) }}
                                        </h3>
                                        @if($isUnread)
                                            <span class="px-2 py-0.5 bg-blue-500/20 text-blue-400 text-xs rounded-full">
                                                New
                                            </span>
                                        @endif
                                    </div>
                                    <p class="text-sm text-gray-400">
                                        <i class="far fa-clock mr-1"></i>
                                        {{ $notification->created_at->diffForHumans() }}
                                    </p>
                                </div>
                            </div>
                            
                            <!-- Actions Dropdown -->
                            <div class="relative group">
                                <button type="button" class="p-2 rounded-lg hover:bg-gray-700 transition-colors" id="notification-menu-{{ $notification->id }}">
                                    <i class="fas fa-ellipsis-v text-gray-400"></i>
                                </button>
                                <div class="absolute right-0 top-full mt-2 w-48 bg-gray-800 rounded-xl shadow-2xl border border-gray-700 
                                            opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 z-10">
                                    <div class="py-2">
                                        @if($isUnread)
                                            <form action="{{ route('notifications.read', $notification) }}" method="POST">
                                                @csrf
                                                <button type="submit" 
                                                        class="w-full text-left px-4 py-2 text-sm hover:bg-gray-700 transition-colors">
                                                    <i class="fas fa-check mr-2 text-green-400"></i>
                                                    Mark as read
                                                </button>
                                            </form>
                                        @endif
                                        <form action="{{ route('notifications.destroy', $notification) }}" method="POST">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" 
                                                    onclick="return confirm('Are you sure you want to delete this notification?')"
                                                    class="w-full text-left px-4 py-2 text-sm hover:bg-gray-700 transition-colors text-red-400">
                                                <i class="fas fa-trash mr-2"></i>
                                                Delete notification
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Notification Body -->
                        <div class="mb-4">
                            <p class="text-gray-300 mb-3">
                                {{ $data['message'] ?? 'You have a new notification' }}
                            </p>
                            
                            @if(isset($data['contract_title']))
                                <div class="flex items-center gap-2 p-3 bg-gray-800/50 rounded-lg mb-3">
                                    <i class="fas fa-file-contract text-blue-400"></i>
                                    <div>
                                        <p class="text-sm font-medium text-white">{{ $data['contract_title'] }}</p>
                                        @if(isset($data['contract_number']))
                                            <p class="text-xs text-gray-400">#{{ $data['contract_number'] }}</p>
                                        @endif
                                    </div>
                                </div>
                            @endif
                            
                            <!-- Metadata -->
                            <div class="flex flex-wrap gap-2">
                                @if(isset($data['stage_name']))
                                    <span class="px-3 py-1 bg-gray-800 rounded-full text-xs text-gray-300">
                                        <i class="fas fa-layer-group mr-1"></i>
                                        {{ str_replace('_', ' ', $data['stage_name']) }}
                                    </span>
                                @endif
                                
                                @if(isset($data['department_name']))
                                    <span class="px-3 py-1 bg-gray-800 rounded-full text-xs text-gray-300">
                                        <i class="fas fa-building mr-1"></i>
                                        {{ $data['department_name'] }}
                                    </span>
                                @endif
                                
                                @if(isset($data['assigned_by']))
                                    <span class="px-3 py-1 bg-gray-800 rounded-full text-xs text-gray-300">
                                        <i class="fas fa-user-tie mr-1"></i>
                                        By {{ $data['assigned_by'] }}
                                    </span>
                                @endif
                            </div>
                        </div>
                        
                        <!-- Action Buttons -->
                        <div class="flex items-center justify-between pt-3 border-t border-gray-700">
                            <div class="flex items-center space-x-3">
                                @if(isset($data['action_url']) && $data['action_url'] !== '#')
                                    <a href="{{ $data['action_url'] }}" 
                                       class="px-4 py-2 bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 
                                              rounded-lg text-sm font-medium transition-all duration-200">
                                        <i class="fas fa-external-link-alt mr-2"></i>
                                        View Details
                                    </a>
                                @endif
                            </div>
                            
                            @if($isUnread)
                                <form action="{{ route('notifications.read', $notification) }}" method="POST">
                                    @csrf
                                    <button type="submit" 
                                            class="text-sm text-gray-400 hover:text-white transition-colors">
                                        <i class="fas fa-check-circle mr-1"></i>
                                        Mark as read
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <!-- Empty State -->
                <div class="text-center py-16">
                    <div class="inline-flex items-center justify-center w-24 h-24 rounded-full bg-gradient-to-br from-gray-800 to-gray-900 mb-6">
                        <i class="fas fa-bell-slash text-4xl text-gray-500"></i>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-300 mb-2">No notifications yet</h3>
                    <p class="text-gray-500 max-w-md mx-auto mb-6">
                        You're all caught up! Check back later for updates on your contracts and reviews.
                    </p>
                    <a href="{{ route('reviews.my-reviews') }}" 
                       class="inline-flex items-center gap-2 px-6 py-3 bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 
                              rounded-xl text-white font-medium transition-all duration-200">
                        <i class="fas fa-tasks"></i>
                        Go to My Reviews
                    </a>
                </div>
            @endforelse
        </div>
        
        <!-- Pagination dengan design yang lebih baik -->
        @if($notifications->hasPages())
            <div class="mt-12">
                <div class="flex items-center justify-between bg-gray-800 rounded-xl p-4">
                    <div class="text-sm text-gray-400">
                        Showing {{ $notifications->firstItem() }} to {{ $notifications->lastItem() }} of {{ $notifications->total() }} notifications
                    </div>
                    <div class="flex items-center space-x-2">
                        @if($notifications->onFirstPage())
                            <span class="px-4 py-2 bg-gray-800 rounded-lg text-gray-600 cursor-not-allowed">
                                <i class="fas fa-chevron-left mr-2"></i> Previous
                            </span>
                        @else
                            <a href="{{ $notifications->previousPageUrl() }}" 
                               class="px-4 py-2 bg-gray-700 hover:bg-gray-600 rounded-lg text-white transition-colors">
                                <i class="fas fa-chevron-left mr-2"></i> Previous
                            </a>
                        @endif
                        
                        <div class="flex items-center space-x-1">
                            @foreach(range(1, min(5, $notifications->lastPage())) as $page)
                                @if($page == $notifications->currentPage())
                                    <span class="w-10 h-10 flex items-center justify-center bg-blue-600 text-white rounded-lg">
                                        {{ $page }}
                                    </span>
                                @else
                                    <a href="{{ $notifications->url($page) }}" 
                                       class="w-10 h-10 flex items-center justify-center bg-gray-700 hover:bg-gray-600 text-white rounded-lg transition-colors">
                                        {{ $page }}
                                    </a>
                                @endif
                            @endforeach
                        </div>
                        
                        @if($notifications->hasMorePages())
                            <a href="{{ $notifications->nextPageUrl() }}" 
                               class="px-4 py-2 bg-gray-700 hover:bg-gray-600 rounded-lg text-white transition-colors">
                                Next <i class="fas fa-chevron-right ml-2"></i>
                            </a>
                        @else
                            <span class="px-4 py-2 bg-gray-800 rounded-lg text-gray-600 cursor-not-allowed">
                                Next <i class="fas fa-chevron-right ml-2"></i>
                            </span>
                        @endif
                    </div>
                </div>
            </div>
        @endif
        
        <!-- Clear All Button (Bottom) -->
        @if(auth()->user()->notifications()->count() > 0)
            <div class="mt-8 pt-6 border-t border-gray-700">
                <form action="{{ route('notifications.clear-all') }}" method="POST" 
                      onsubmit="return confirm('Are you sure you want to clear ALL notifications? This action cannot be undone.')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" 
                            class="px-5 py-2.5 bg-gradient-to-r from-red-600/20 to-red-700/20 hover:from-red-700/30 hover:to-red-800/30 
                                   border border-red-700/50 text-red-400 hover:text-red-300 rounded-xl text-sm font-medium 
                                   transition-all duration-200">
                        <i class="fas fa-trash-alt mr-2"></i>
                        Clear All Notifications
                    </button>
                </form>
            </div>
        @endif
    </div>
    
    <!-- Tambahkan animasi CSS -->
    <style>
        .animate-pulse {
            animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
        }
        
        @keyframes pulse {
            0%, 100% {
                opacity: 1;
            }
            50% {
                opacity: .5;
            }
        }
        
        .hover\:-translate-y-1:hover {
            transform: translateY(-4px);
        }
        
        .group:hover .group-hover\:visible {
            visibility: visible;
            opacity: 1;
        }
        
        .bg-gradient-to-r {
            background-size: 200% 200%;
        }
        
        .hover\:shadow-xl:hover {
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        }
    </style>
    
    <!-- JavaScript untuk dropdown menu -->
    <script>
        // Close dropdown when clicking outside
        document.addEventListener('click', function(event) {
            if (!event.target.closest('.relative.group')) {
                document.querySelectorAll('.absolute.right-0').forEach(dropdown => {
                    dropdown.classList.add('invisible', 'opacity-0');
                });
            }
        });
        
        // Toggle dropdown
        document.querySelectorAll('[id^="notification-menu-"]').forEach(button => {
            button.addEventListener('click', function(e) {
                e.stopPropagation();
                const dropdown = this.nextElementSibling;
                const isVisible = !dropdown.classList.contains('invisible');
                
                // Close all other dropdowns
                document.querySelectorAll('.absolute.right-0').forEach(d => {
                    d.classList.add('invisible', 'opacity-0');
                });
                
                // Toggle current dropdown
                if (!isVisible) {
                    dropdown.classList.remove('invisible', 'opacity-0');
                }
            });
        });
    </script>
</x-app-layout-dark>