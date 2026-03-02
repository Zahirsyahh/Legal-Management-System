<x-app-layout-dark title="Edit Profile">
    <div class="pb-8 px-4 sm:px-6 lg:px-8 max-w-4xl mx-auto">
        <!-- Header Section -->
        <div class="mb-8 animate-fade-in">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl md:text-4xl font-bold mb-2">Profile Settings</h1>
                    <p class="text-gray-400">
                        Manage your account settings and preferences
                    </p>
                </div>
                <div class="flex items-center space-x-2">
                    <a href="{{ route('dashboard') }}" 
                       class="flex items-center text-gray-400 hover:text-gray-300">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                        Back to Dashboard
                    </a>
                </div>
            </div>
        </div>

        <!-- User Profile Card -->
        <div class="glass-card rounded-2xl p-6 mb-6">
            <div class="flex items-center space-x-4 mb-6">
                <div class="w-16 h-16 rounded-full bg-gradient-to-br from-blue-500/30 to-cyan-500/30 flex items-center justify-center">
                    <svg class="w-8 h-8 text-blue-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                </div>
                <div>
                    <h3 class="text-xl font-semibold text-gray-300">{{ Auth::user()->name }}</h3>
                    <p class="text-gray-400">{{ Auth::user()->email }}</p>
                    <span class="inline-block mt-1 px-3 py-1 text-xs rounded-full bg-gradient-to-r from-blue-500/20 to-cyan-500/20 text-blue-300">
                        {{ Auth::user()->getRoleNames()->first() ?? 'User' }}
                    </span>
                </div>
            </div>
            
            <div class="grid grid-cols-2 gap-4 text-sm">
                <div class="flex justify-between p-3 bg-white/5 rounded-lg">
                    <span class="text-gray-400">Member Since</span>
                    <span class="text-gray-300">{{ optional(Auth::user()->created_at)->format('M d, Y') ?? '-' }}</span>
                </div>
                <div class="flex justify-between p-3 bg-white/5 rounded-lg">
                    <span class="text-gray-400">Last Updated</span>
                    <span class="text-gray-300">{{ optional(Auth::user()->updated_at)->diffForHumans() ?? '-' }}</span>
                </div>
            </div>
        </div>

        <!-- Profile Update Forms -->
        <div class="space-y-6 animate-slide-up">
            
            <!-- Profile Information Form -->
            <div class="glass-card rounded-2xl p-6 border border-blue-500/20">
                <div class="flex items-center justify-between mb-6">
                    <div>
                        <h2 class="text-xl font-semibold text-blue-300 flex items-center">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                            Profile Information
                        </h2>
                        <p class="text-sm text-gray-400 mt-1">Update your account's profile information</p>
                    </div>
                    <div class="w-2 h-2 rounded-full bg-blue-500 animate-pulse"></div>
                </div>

                <form method="post" action="{{ route('profile.update') }}" class="space-y-6">
                    @csrf
                    @method('patch')

                    <!-- Name Field -->
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-300 mb-2">
                            Full Name <span class="text-red-400">*</span>
                        </label>
                        <div class="relative">
                            <input type="text" 
                                   id="name" 
                                   name="name" 
                                   value="{{ old('name', $user->name) }}"
                                   required
                                   autofocus
                                   autocomplete="name"
                                   class="w-full px-4 py-3 pl-10 bg-dark-800 border border-gray-700 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors"
                                   placeholder="Enter your full name">
                            <div class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                            </div>
                        </div>
                        @error('name')
                            <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Email Field -->
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-300 mb-2">
                            Email Address <span class="text-red-400">*</span>
                        </label>
                        <div class="relative">
                            <input type="email" 
                                   id="email" 
                                   name="email" 
                                   value="{{ old('email', $user->email) }}"
                                   required
                                   autocomplete="username"
                                   class="w-full px-4 py-3 pl-10 bg-dark-800 border border-gray-700 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors"
                                   placeholder="you@example.com">
                            <div class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                </svg>
                            </div>
                        </div>
                        @error('email')
                            <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                        @enderror

                        <!-- Email Verification Status -->
                        @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                            <div class="mt-3 p-3 bg-yellow-500/10 border border-yellow-500/20 rounded-lg">
                                <p class="text-sm text-yellow-300">
                                    Your email address is unverified.
                                    <button form="send-verification" 
                                            class="underline text-yellow-200 hover:text-yellow-100 ml-1">
                                        Click here to re-send the verification email.
                                    </button>
                                </p>
                                @if (session('status') === 'verification-link-sent')
                                    <p class="mt-2 text-sm text-green-300">
                                        A new verification link has been sent to your email address.
                                    </p>
                                @endif
                            </div>
                        @endif
                    </div>

                    <!-- Save Button -->
                    <div class="flex items-center gap-4 pt-4">
                        <button type="submit" 
                                class="px-6 py-3 bg-gradient-to-r from-blue-600 to-cyan-600 hover:from-blue-700 hover:to-cyan-700 rounded-lg font-medium transition-colors shadow-lg shadow-blue-500/20">
                            Save Changes
                        </button>

                        @if (session('status') === 'profile-updated')
                            <div x-data="{ show: true }" 
                                 x-show="show" 
                                 x-transition 
                                 x-init="setTimeout(() => show = false, 3000)"
                                 class="flex items-center text-green-400">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                                <span class="text-sm">Profile updated successfully!</span>
                            </div>
                        @endif
                    </div>
                </form>
                
                <!-- Verification Form (hidden) -->
                <form id="send-verification" method="post" action="{{ route('verification.send') }}" class="hidden">
                    @csrf
                </form>
            </div>

            <!-- Update Password Form -->
            <div class="glass-card rounded-2xl p-6 border border-emerald-500/20">
                <div class="flex items-center justify-between mb-6">
                    <div>
                        <h2 class="text-xl font-semibold text-emerald-300 flex items-center">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                            </svg>
                            Update Password
                        </h2>
                        <p class="text-sm text-gray-400 mt-1">Ensure your account is using a secure password</p>
                    </div>
                    <div class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></div>
                </div>

                <form method="post" action="{{ route('password.update') }}" class="space-y-6">
                    @csrf
                    @method('put')

                    <!-- Current Password -->
                    <div>
                        <label for="update_password_current_password" class="block text-sm font-medium text-gray-300 mb-2">
                            Current Password <span class="text-red-400">*</span>
                        </label>
                        <div class="relative">
                            <input type="password" 
                                   id="update_password_current_password" 
                                   name="current_password" 
                                   autocomplete="current-password"
                                   class="w-full px-4 py-3 pl-10 bg-dark-800 border border-gray-700 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition-colors"
                                   placeholder="Enter current password">
                            <div class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                </svg>
                            </div>
                        </div>
                        @error('current_password', 'updatePassword')
                            <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- New Password -->
                    <div>
                        <label for="update_password_password" class="block text-sm font-medium text-gray-300 mb-2">
                            New Password <span class="text-red-400">*</span>
                        </label>
                        <div class="relative">
                            <input type="password" 
                                   id="update_password_password" 
                                   name="password" 
                                   autocomplete="new-password"
                                   class="w-full px-4 py-3 pl-10 bg-dark-800 border border-gray-700 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition-colors"
                                   placeholder="Enter new password">
                            <div class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                                </svg>
                            </div>
                        </div>
                        @error('password', 'updatePassword')
                            <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Confirm Password -->
                    <div>
                        <label for="update_password_password_confirmation" class="block text-sm font-medium text-gray-300 mb-2">
                            Confirm New Password <span class="text-red-400">*</span>
                        </label>
                        <div class="relative">
                            <input type="password" 
                                   id="update_password_password_confirmation" 
                                   name="password_confirmation" 
                                   autocomplete="new-password"
                                   class="w-full px-4 py-3 pl-10 bg-dark-800 border border-gray-700 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition-colors"
                                   placeholder="Confirm new password">
                            <div class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                        </div>
                        @error('password_confirmation', 'updatePassword')
                            <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Password Strength Indicator -->
                    <div class="bg-white/5 rounded-lg p-4">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-sm text-gray-400">Password strength</span>
                            <span class="text-xs text-emerald-400">Tips:</span>
                        </div>
                        <div class="space-y-2 text-xs text-gray-500">
                            <div class="flex items-center">
                                <div class="w-1 h-1 rounded-full bg-gray-600 mr-2"></div>
                                Use at least 8 characters
                            </div>
                            <div class="flex items-center">
                                <div class="w-1 h-1 rounded-full bg-gray-600 mr-2"></div>
                                Mix letters, numbers & symbols
                            </div>
                            <div class="flex items-center">
                                <div class="w-1 h-1 rounded-full bg-gray-600 mr-2"></div>
                                Avoid common words or phrases
                            </div>
                        </div>
                    </div>

                    <!-- Save Button -->
                    <div class="flex items-center gap-4 pt-4">
                        <button type="submit" 
                                class="px-6 py-3 bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 rounded-lg font-medium transition-colors shadow-lg shadow-emerald-500/20">
                            Update Password
                        </button>

                        @if (session('status') === 'password-updated')
                            <div x-data="{ show: true }" 
                                 x-show="show" 
                                 x-transition 
                                 x-init="setTimeout(() => show = false, 3000)"
                                 class="flex items-center text-green-400">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                                <span class="text-sm">Password updated successfully!</span>
                            </div>
                        @endif
                    </div>
                </form>
            </div>

            <!-- Delete Account Section -->
            <div class="glass-card rounded-2xl p-6 border border-red-500/20 bg-gradient-to-br from-red-900/10 to-transparent">
                <div class="flex items-center justify-between mb-6">
                    <div>
                        <h2 class="text-xl font-semibold text-red-300 flex items-center">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                            </svg>
                            Delete Account
                        </h2>
                        <p class="text-sm text-red-300/70 mt-1">Permanently delete your account and all associated data</p>
                    </div>
                    <div class="w-2 h-2 rounded-full bg-red-500 animate-pulse"></div>
                </div>

                <div class="space-y-4">
                    <div class="p-4 bg-red-500/10 border border-red-500/20 rounded-lg">
                        <p class="text-sm text-red-300">
                            Once your account is deleted, all of its resources and data will be permanently deleted. 
                            Before deleting your account, please download any data or information that you wish to retain.
                        </p>
                    </div>

                    <button x-data="" 
                            x-on:click.prevent="$dispatch('open-modal', 'confirm-user-deletion')"
                            class="w-full px-4 py-3 bg-gradient-to-r from-red-600 to-pink-600 hover:from-red-700 hover:to-pink-700 rounded-lg font-medium transition-colors flex items-center justify-center group">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                        Delete Account
                        <svg class="w-4 h-4 ml-2 transform group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                        </svg>
                    </button>
                </div>

                <!-- Delete Account Modal -->
                <x-modal name="confirm-user-deletion" :show="$errors->userDeletion->isNotEmpty()" focusable>
                    <div class="p-6">
                        <form method="post" action="{{ route('profile.destroy') }}">
                            @csrf
                            @method('delete')

                            <div class="flex items-center mb-4">
                                <div class="w-12 h-12 rounded-full bg-red-500/20 flex items-center justify-center mr-3">
                                    <svg class="w-6 h-6 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.346 16.5c-.77.833.192 2.5 1.732 2.5z" />
                                    </svg>
                                </div>
                                <div>
                                    <h2 class="text-xl font-semibold text-red-300">Confirm Account Deletion</h2>
                                    <p class="text-sm text-gray-400 mt-1">This action cannot be undone</p>
                                </div>
                            </div>

                            <p class="mt-4 text-sm text-gray-300">
                                Once your account is deleted, all of its resources and data will be permanently deleted. 
                                Please enter your password to confirm you would like to permanently delete your account.
                            </p>

                            <div class="mt-6">
                                <label for="password" class="block text-sm font-medium text-gray-300 mb-2">
                                    Password <span class="text-red-400">*</span>
                                </label>
                                <div class="relative">
                                    <input type="password" 
                                           id="password" 
                                           name="password" 
                                           class="w-full px-4 py-3 pl-10 bg-dark-800 border border-gray-700 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent transition-colors"
                                           placeholder="Enter your password to confirm">
                                    <div class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                        </svg>
                                    </div>
                                </div>
                                @error('password', 'userDeletion')
                                    <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="mt-8 flex justify-end space-x-3">
                                <button type="button" 
                                        x-on:click="$dispatch('close')"
                                        class="px-4 py-2 border border-gray-700 rounded-lg hover:bg-gray-800 transition-colors">
                                    Cancel
                                </button>

                                <button type="submit" 
                                        class="px-4 py-2 bg-gradient-to-r from-red-600 to-pink-600 hover:from-red-700 hover:to-pink-700 rounded-lg font-medium transition-colors">
                                    Delete Account
                                </button>
                            </div>
                        </form>
                    </div>
                </x-modal>
            </div>
        </div>

        <!-- Footer -->
        <div class="mt-8 pt-6 border-t border-gray-800 text-center text-gray-500 text-sm">
            <p>Legal Management System v1.0 • {{ now()->format('F j, Y') }} • Profile last updated {{ Auth::user()->updated_at?->diffForHumans() ?? '—' }}</p>
        </div>
    </div>

    <!-- Add custom styles -->
    <style>
        .glass-card {
            backdrop-filter: blur(10px);
            background: rgba(30, 41, 59, 0.7);
            border: 1px solid rgba(255, 255, 255, 0.1);
            transition: all 0.3s ease;
        }
        
        .glass-card:hover {
            border-color: rgba(255, 255, 255, 0.2);
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
        }
        
        .gradient-text {
            background: linear-gradient(90deg, #3b82f6, #06b6d4);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        input:focus {
            outline: none;
            ring: 2px;
            ring-color: rgba(59, 130, 246, 0.5);
        }
        
        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
        }
        
        .animate-pulse {
            animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
        }
    </style>
</x-app-layout-dark>