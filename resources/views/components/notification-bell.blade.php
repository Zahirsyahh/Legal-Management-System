@php
    try {
        $unreadCount = auth()->user()->unreadNotifications()->count();
    } catch (\Exception $e) {
        $unreadCount = 0;
    }
@endphp

<div class="relative" id="notificationDropdownContainer" x-data="{ isOpen: false }" @click.outside="isOpen = false">
    <!-- Notification Bell Button -->
    <button 
        class="relative p-2 text-gray-300 hover:text-white focus:outline-none transition-colors"
        @click="isOpen = !isOpen; if(isOpen) loadNotifications()"
        aria-label="Notifications"
    >
        <i class="fas fa-bell text-xl"></i>
        
        @if($unreadCount > 0)
            <span class="absolute -top-1 -right-1 flex items-center justify-center h-5 w-5 text-xs bg-red-500 text-white rounded-full animate-pulse">
                {{ $unreadCount > 9 ? '9+' : $unreadCount }}
            </span>
        @endif
    </button>
    
    <!-- Notification Dropdown -->
    <div 
        x-show="isOpen"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
        class="absolute right-0 mt-2 w-80 bg-gray-800 border border-gray-700 rounded-xl shadow-xl z-50"
        style="display: none;"
    >
        <!-- Header -->
        <div class="p-4 border-b border-gray-700">
            <div class="flex justify-between items-center">
                <h3 class="font-semibold text-white">Notifications</h3>
                @if($unreadCount > 0)
                    <button 
                        class="text-sm text-blue-400 hover:text-blue-300"
                        @click="markAllAsRead()"
                    >
                        Mark all as read
                    </button>
                @endif
            </div>
        </div>
        
        <!-- Notification List -->
        <div class="max-h-80 overflow-y-auto" id="notificationList">
            <!-- Loading state -->
            <div class="text-center py-6" id="notificationLoading">
                <div class="inline-flex items-center justify-center">
                    <svg class="animate-spin h-6 w-6 text-blue-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                </div>
                <p class="text-gray-400 text-sm mt-2">Loading notifications...</p>
            </div>
            
            <!-- Will be populated by JavaScript -->
        </div>
        
        <!-- Footer -->
        <div class="p-3 border-t border-gray-700 text-center">
            <a 
                href="{{ route('notifications.index') }}"
                class="text-sm text-blue-400 hover:text-blue-300 inline-flex items-center"
            >
                View all notifications
                <i class="fas fa-arrow-right ml-2 text-xs"></i>
            </a>
        </div>
    </div>
</div>

<script>
function loadNotifications() {
    const notificationList = document.getElementById('notificationList');
    const loadingElement = document.getElementById('notificationLoading');
    
    fetch('/api/notifications')
        .then(response => response.json())
        .then(data => {
            renderNotifications(data.notifications || []);
            updateNotificationBadge(data.unread_count || 0);
            if (loadingElement) {
                loadingElement.style.display = 'none';
            }
        })
        .catch(error => {
            console.error('Error loading notifications:', error);
            notificationList.innerHTML = `
                <div class="text-center py-6">
                    <i class="fas fa-exclamation-triangle text-red-400 text-xl mb-3"></i>
                    <p class="text-red-400 text-sm">Failed to load notifications</p>
                </div>
            `;
        });
}

function renderNotifications(notifications) {
    const notificationList = document.getElementById('notificationList');
    
    if (!notifications || notifications.length === 0) {
        notificationList.innerHTML = `
            <div class="text-center py-6">
                <i class="fas fa-bell-slash text-gray-500 text-xl mb-3"></i>
                <p class="text-gray-400 text-sm">No notifications</p>
            </div>
        `;
        return;
    }
    
    let html = '';
    
    notifications.forEach(notification => {
        const isUnread = !notification.read_at || notification.is_unread;
        
        html += `
            <div class="p-3 border-b border-gray-700 hover:bg-gray-700/30 cursor-pointer transition-colors ${isUnread ? 'bg-blue-900/10 border-l-2 border-blue-500' : ''}"
                 onclick="handleNotificationClick('${notification.id}', '${notification.action_url}')">
                <div class="flex items-start space-x-3">
                    <!-- Icon -->
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 rounded-lg ${getNotificationColor(notification.icon)} flex items-center justify-center">
                            <i class="fas ${notification.icon || 'fa-bell'} text-white text-sm"></i>
                        </div>
                    </div>
                    
                    <!-- Content -->
                    <div class="flex-1 min-w-0">
                        <p class="text-sm text-gray-200 font-medium mb-1">${notification.title || 'Notification'}</p>
                        <p class="text-xs text-gray-400 mb-2">${notification.message}</p>
                        <div class="flex items-center justify-between">
                            <span class="text-xs text-gray-500">${notification.created_at}</span>
                            ${isUnread ? '<span class="w-2 h-2 bg-blue-500 rounded-full"></span>' : ''}
                        </div>
                    </div>
                </div>
            </div>
        `;
    });
    
    notificationList.innerHTML = html;
}

function getNotificationColor(icon) {
    const colors = {
        'fa-file-contract': 'bg-blue-500',
        'fa-check-circle': 'bg-green-500',
        'fa-times-circle': 'bg-red-500',
        'fa-comment': 'bg-yellow-500',
        'fa-user-tag': 'bg-purple-500',
        'fa-clock': 'bg-orange-500',
        'fa-exclamation-triangle': 'bg-red-500',
        'fa-info-circle': 'bg-blue-500'
    };
    
    return colors[icon] || 'bg-gray-600';
}

function handleNotificationClick(notificationId, actionUrl) {
    // Mark as read
    fetch(`/api/notifications/${notificationId}/read`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}'
        }
    }).then(() => {
        // Remove unread style
        const notificationElement = document.querySelector(`[onclick*="${notificationId}"]`);
        if (notificationElement) {
            notificationElement.classList.remove('bg-blue-900/10', 'border-l-2', 'border-blue-500');
            const dot = notificationElement.querySelector('.bg-blue-500');
            if (dot) dot.remove();
            
            // Update badge count
            updateBadgeCount(-1);
        }
        
        // Navigate if actionUrl exists
        if (actionUrl && actionUrl !== '#') {
            window.location.href = actionUrl;
        }
    });
}

async function markAllAsRead() {
    try {
        const response = await fetch('{{ route("notifications.read-all") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}'
            }
        });
        
        if (response.ok) {
            // Remove all unread styles
            document.querySelectorAll('.bg-blue-900/10').forEach(item => {
                item.classList.remove('bg-blue-900/10', 'border-l-2', 'border-blue-500');
                const dot = item.querySelector('.bg-blue-500');
                if (dot) dot.remove();
            });
            
            // Hide badge
            updateBadgeCount('clear');
        }
    } catch (error) {
        console.error('Error marking all as read:', error);
    }
}

function updateNotificationBadge(count) {
    let badge = document.querySelector('#notificationDropdownContainer .absolute');
    
    if (!badge && count > 0) {
        const bell = document.querySelector('#notificationDropdownContainer i.fa-bell');
        if (bell) {
            const parent = bell.parentElement;
            badge = document.createElement('span');
            badge.className = 'absolute -top-1 -right-1 flex items-center justify-center h-5 w-5 text-xs bg-red-500 text-white rounded-full animate-pulse';
            badge.textContent = count > 9 ? '9+' : count;
            parent.appendChild(badge);
        }
    } else if (badge) {
        if (count > 0) {
            badge.textContent = count > 9 ? '9+' : count;
            badge.style.display = 'flex';
        } else {
            badge.style.display = 'none';
        }
    }
}

function updateBadgeCount(change) {
    const badge = document.querySelector('#notificationDropdownContainer .absolute');
    if (!badge) return;
    
    if (change === 'clear') {
        badge.style.display = 'none';
        return;
    }
    
    const currentCount = parseInt(badge.textContent) || 0;
    const newCount = Math.max(0, currentCount + change);
    
    if (newCount > 0) {
        badge.textContent = newCount > 9 ? '9+' : newCount;
        badge.style.display = 'flex';
    } else {
        badge.style.display = 'none';
    }
}

// Auto refresh notifications every 30 seconds if dropdown is open
setInterval(() => {
    const container = document.getElementById('notificationDropdownContainer');
    if (container && container.hasAttribute('x-data')) {
        const isOpen = eval(container.getAttribute('x-data')).isOpen;
        if (isOpen) {
            loadNotifications();
        }
    }
}, 30000);
</script>