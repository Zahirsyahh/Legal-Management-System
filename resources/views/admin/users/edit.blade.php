<x-app-layout-dark title="Edit User">
    <div class="pb-8 px-4 sm:px-6 lg:px-8 max-w-2xl mx-auto">
        <!-- Header -->
        <div class="mb-8">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl md:text-4xl font-bold mb-2">Edit User</h1>
                    <p class="text-gray-400">
                        Editing: <span class="font-medium text-gray-300">{{ $user->name }}</span>
                    </p>
                </div>
                <a href="{{ route('admin.users.index') }}" 
                   class="flex items-center text-blue-400 hover:text-blue-300">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Back to Users
                </a>
            </div>
        </div>

        <!-- Form Card -->
        <div class="glass-card rounded-2xl p-6">
            <form action="{{ route('admin.users.update', $user) }}" method="POST">
                @csrf
                @method('PUT')
                
                <!-- Flash Messages -->
                @if(session('success'))
                    <div class="mb-6 p-4 bg-green-500/10 border border-green-500/30 rounded-xl">
                        <p class="text-green-400">{{ session('success') }}</p>
                    </div>
                @endif
                
                @if($errors->any())
                    <div class="mb-6 p-4 bg-red-500/10 border border-red-500/30 rounded-xl">
                        <ul class="text-red-400">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <!-- Form Fields -->
                <div class="space-y-6">
                    <!-- Name -->
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-300 mb-2">
                            Full Name <span class="text-red-400">*</span>
                        </label>
                        <input type="text" 
                               name="name" 
                               id="name" 
                               value="{{ old('name', $user->name) }}"
                               required
                               class="w-full px-4 py-3 bg-dark-800 border border-gray-700 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors">
                    </div>

                    <!-- Email -->
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-300 mb-2">
                            Email Address <span class="text-red-400">*</span>
                        </label>
                        <input type="email" 
                               name="email" 
                               id="email" 
                               value="{{ old('email', $user->email) }}"
                               required
                               class="w-full px-4 py-3 bg-dark-800 border border-gray-700 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors">
                    </div>

                    <!-- Password (Optional) -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="password" class="block text-sm font-medium text-gray-300 mb-2">
                                New Password (leave blank to keep current)
                            </label>
                            <input type="password" 
                                   name="password" 
                                   id="password" 
                                   class="w-full px-4 py-3 bg-dark-800 border border-gray-700 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors"
                                   placeholder="••••••••">
                        </div>
                        
                        <div>
                            <label for="password_confirmation" class="block text-sm font-medium text-gray-300 mb-2">
                                Confirm New Password
                            </label>
                            <input type="password" 
                                   name="password_confirmation" 
                                   id="password_confirmation" 
                                   class="w-full px-4 py-3 bg-dark-800 border border-gray-700 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors"
                                   placeholder="••••••••">
                        </div>
                    </div>

                    <!-- Role -->
                    <div>
                        <label for="role" class="block text-sm font-medium text-gray-300 mb-2">
                            Role <span class="text-red-400">*</span>
                        </label>
                        <select name="role" 
                                id="role" 
                                required
                                class="w-full px-4 py-3 bg-dark-800 border border-gray-700 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors">
                            @foreach($roles as $role)
                                <option value="{{ $role->name }}" 
                                    {{ $user->hasRole($role->name) ? 'selected' : '' }}>
                                    {{ ucfirst($role->name) }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- User Info -->
                    <div class="p-4 bg-dark-800/50 rounded-lg">
                        <p class="text-sm text-gray-400 mb-2">User Information</p>
                        <div class="grid grid-cols-2 gap-4 text-sm">
                            <div>
                                <span class="text-gray-500">User ID:</span>
                                <span class="text-gray-300 ml-2">{{ $user->id }}</span>
                            </div>
                            <div>
                                <span class="text-gray-500">Created:</span>
                                <span class="text-gray-300 ml-2">
                                    @if($user->tgl_masuk_karyawan)
                                        {{ \Carbon\Carbon::parse($user->tgl_masuk_karyawan)->format('M d, Y') }}
                                    @else
                                        -
                                    @endif
                                </span>
                            </div>
                            <div>
                                <span class="text-gray-500">Contracts:</span>
                                <span class="text-gray-300 ml-2">{{ $user->contracts()->count() }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="flex justify-between items-center pt-6 border-t border-gray-800">
                    <a href="{{ route('admin.users.index') }}" 
                       class="px-6 py-3 border border-gray-700 rounded-lg hover:bg-gray-800 transition-colors">
                        Cancel
                    </a>
                    
                    <div class="flex space-x-3">
                        <a href="{{ route('admin.users.show', $user) }}" 
                           class="px-6 py-3 border border-gray-700 rounded-lg hover:bg-gray-800 transition-colors">
                            View Details
                        </a>
                        
                        <button type="submit"
                                class="px-6 py-3 btn-primary rounded-lg font-medium">
                            Update User
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</x-app-layout-dark>