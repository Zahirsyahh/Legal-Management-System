@php
    // Get all available roles
    $allRoles = \Spatie\Permission\Models\Role::all();
    
    // Get user's current roles
    $userRoles = $user->roles->pluck('name')->toArray();
@endphp

<x-app-layout-dark title="Edit User">
    
    <style>
        /* Custom styles for form */
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
        
        .glass-card {
            background: rgba(255, 255, 255, 0.03);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
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
        
        .btn-secondary {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: white;
        }
        
        .btn-secondary:hover {
            background: rgba(255, 255, 255, 0.1);
            border-color: rgba(255, 255, 255, 0.2);
        }
        
        .form-input {
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: white;
            transition: all 0.3s ease;
        }
        
        .form-input:focus {
            background: rgba(255, 255, 255, 0.05);
            border-color: rgba(14, 165, 233, 0.5);
            box-shadow: 0 0 0 3px rgba(14, 165, 233, 0.1);
            outline: none;
        }
        
        /* Role Checkbox Grid */
        .roles-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
            gap: 12px;
            margin-top: 16px;
        }
        
        .role-checkbox-card {
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 0.5rem;
            padding: 16px;
            cursor: pointer;
            transition: all 0.2s ease;
            position: relative;
        }
        
        .role-checkbox-card:hover {
            background: rgba(59, 130, 246, 0.05);
            border-color: rgba(59, 130, 246, 0.2);
        }
        
        .role-checkbox-card.checked {
            background: rgba(34, 197, 94, 0.1);
            border-color: rgba(34, 197, 94, 0.3);
        }
        
        .role-checkbox-input {
            position: absolute;
            opacity: 0;
            width: 0;
            height: 0;
        }
        
        .role-checkbox-label {
            display: flex;
            align-items: center;
            cursor: pointer;
            font-size: 0.875rem;
            color: #e2e8f0;
        }
        
        .custom-checkbox {
            width: 18px;
            height: 18px;
            border: 2px solid rgba(255, 255, 255, 0.3);
            border-radius: 4px;
            margin-right: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s ease;
        }
        
        .role-checkbox-card.checked .custom-checkbox {
            background: #10b981;
            border-color: #10b981;
        }
        
        .custom-checkbox svg {
            width: 12px;
            height: 12px;
            opacity: 0;
            transition: opacity 0.2s ease;
        }
        
        .role-checkbox-card.checked .custom-checkbox svg {
            opacity: 1;
        }
        
        .role-name {
            font-weight: 500;
        }
    </style>
    
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        
        <!-- Header Section -->
        <div class="flex flex-col lg:flex-row lg:items-center justify-between mb-8 gap-4">
            <div>
                <div class="flex items-center gap-3 mb-2">
                    <div class="p-3 rounded-xl bg-gradient-to-br from-green-500/10 to-emerald-600/10 border border-green-500/20">
                        <svg class="w-6 h-6 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5 1.5l-5.5-5.5"/>
                        </svg>
                    </div>
                    <div>
                        <h1 class="text-3xl font-bold bg-gradient-to-r from-green-400 via-emerald-400 to-green-400 bg-clip-text text-transparent">
                            Edit User
                        </h1>
                        <p class="text-gray-400 mt-1">Update user account information</p>
                    </div>
                </div>
                
                <!-- User Info Card -->
                <div class="mt-4 glass-card rounded-xl p-4 max-w-md">
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 rounded-full bg-gradient-to-br from-green-500/20 to-emerald-600/20 flex items-center justify-center">
                            <svg class="w-6 h-6 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                        </div>
                        <div>
                            <p class="font-medium text-white">{{ $user->display_name }}</p>
                            <p class="text-sm text-gray-400">ID: {{ $user->id_user }}</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Action Buttons -->
            <div class="flex gap-3">
                <a href="{{ route('admin.master-departments.index', ['type' => 'user']) }}" 
                   class="px-4 py-3 glass-card rounded-xl flex items-center gap-2 hover:bg-white/5 transition-all duration-300">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    Back to Users
                </a>
            </div>
        </div>

        <!-- Flash Messages -->
        @if(session('success'))
            <div class="mb-6 animate-fade-in">
                <div class="glass-card border border-green-500/30 bg-green-900/10 rounded-xl p-4">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-3">
                            <div class="flex-shrink-0">
                                <svg class="h-6 w-6 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-green-300">{{ session('success') }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        @if($errors->any())
            <div class="mb-6 animate-fade-in">
                <div class="glass-card border border-red-500/30 bg-red-900/10 rounded-xl p-4">
                    <div class="flex items-center space-x-3">
                        <div class="flex-shrink-0">
                            <svg class="h-6 w-6 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-sm font-medium text-red-300">Please fix the following errors:</h3>
                            <ul class="mt-2 text-sm text-red-400 list-disc list-inside">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <!-- Form Section -->
        <div class="gradient-border rounded-xl overflow-hidden">
            <form action="{{ route('admin.master-departments.update', $user->id_user) }}" method="POST">
                @csrf
                @method('PUT')
                <input type="hidden" name="type" value="user">
                
                <div class="p-6 md:p-8">
                    <!-- Basic Information -->
                    <div class="mb-8">
                        <h2 class="text-xl font-bold text-white mb-4 flex items-center gap-2">
                            <svg class="w-5 h-5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                            Basic Information
                        </h2>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- User ID (Read-only) -->
                            <div>
                                <label class="block text-sm font-medium text-gray-300 mb-2">
                                    User ID
                                </label>
                                <div class="w-full px-4 py-3 bg-dark-800/50 border border-gray-700 rounded-lg text-white">
                                    {{ $user->id_user }}
                                </div>
                                <p class="mt-1 text-xs text-gray-500">User ID cannot be changed</p>
                            </div>
                            
                            <!-- Full Name -->
                            <div>
                                <label for="nama_user" class="block text-sm font-medium text-gray-300 mb-2">
                                    Full Name <span class="text-red-400">*</span>
                                </label>
                                <input type="text" 
                                       name="nama_user" 
                                       id="nama_user"
                                       required
                                       class="w-full px-4 py-3 form-input rounded-lg"
                                       placeholder="Enter full name"
                                       value="{{ old('nama_user', $user->nama_user) }}">
                            </div>
                        </div>
                    </div>

                    <!-- Account Information -->
                    <div class="mb-8">
                        <h2 class="text-xl font-bold text-white mb-4 flex items-center gap-2">
                            <svg class="w-5 h-5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                            Account Information
                        </h2>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Username -->
                            <div>
                                <label for="username" class="block text-sm font-medium text-gray-300 mb-2">
                                    Username <span class="text-red-400">*</span>
                                </label>
                                <input type="text" 
                                       name="username" 
                                       id="username"
                                       required
                                       class="w-full px-4 py-3 form-input rounded-lg"
                                       placeholder="Enter username"
                                       value="{{ old('username', $user->username) }}">
                            </div>
                            
                            <!-- Email -->
                            <div>
                                <label for="email" class="block text-sm font-medium text-gray-300 mb-2">
                                    Email Address
                                </label>
                                <input type="email" 
                                       name="email" 
                                       id="email"
                                       class="w-full px-4 py-3 form-input rounded-lg"
                                       placeholder="Enter email address"
                                       value="{{ old('email', $user->email) }}">
                            </div>

                            <!-- Password -->
                            <div>
                                <label for="password" class="block text-sm font-medium text-gray-300 mb-2">
                                    Password
                                    <span class="text-gray-500 text-xs ml-2">(Leave empty to keep current password)</span>
                                </label>
                                <input type="password" 
                                       name="password" 
                                       id="password"
                                       class="w-full px-4 py-3 form-input rounded-lg"
                                       placeholder="Enter new password"
                                       minlength="6">
                                <p class="mt-1 text-xs text-gray-500">Minimum 6 characters</p>
                            </div>

                            <!-- NIP -->
                            <div>
                                <label for="nip" class="block text-sm font-medium text-gray-300 mb-2">
                                    NIP (Employee ID)
                                </label>
                                <input type="text" 
                                       name="nip" 
                                       id="nip"
                                       maxlength="11"
                                       class="w-full px-4 py-3 form-input rounded-lg"
                                       placeholder="Enter NIP"
                                       value="{{ old('nip', $user->nip) }}">
                            </div>
                        </div>
                    </div>

                    <!-- Job Information -->
                    <div class="mb-8">
                        <h2 class="text-xl font-bold text-white mb-4 flex items-center gap-2">
                            <svg class="w-5 h-5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                            </svg>
                            Job Information
                        </h2>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Position -->
                            <div>
                                <label for="jabatan" class="block text-sm font-medium text-gray-300 mb-2">
                                    Position <span class="text-red-400">*</span>
                                </label>
                                <input type="text" 
                                       name="jabatan" 
                                       id="jabatan"
                                       required
                                       class="w-full px-4 py-3 form-input rounded-lg"
                                       placeholder="Enter job position"
                                       value="{{ old('jabatan', $user->jabatan) }}">
                            </div>
                            
                            <!-- Department -->
                            <div>
                                <label for="kode_department" class="block text-sm font-medium text-gray-300 mb-2">
                                    Department
                                </label>
                                <select name="kode_department" 
                                        id="kode_department"
                                        class="w-full px-4 py-3 form-input rounded-lg">
                                    <option value="">Select Department</option>
                                    @foreach($departments as $department)
                                        <option value="{{ $department->kode_pendek }}" 
                                                {{ (old('kode_department', $user->kode_department) == $department->kode_pendek) ? 'selected' : '' }}>
                                            {{ $department->kode_pendek }} - {{ $department->nama_departemen }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Employee Status -->
                            <div>
                                <label for="status_karyawan" class="block text-sm font-medium text-gray-300 mb-2">
                                    Employee Status <span class="text-red-400">*</span>
                                </label>
                                <select name="status_karyawan" 
                                        id="status_karyawan"
                                        required
                                        class="w-full px-4 py-3 form-input rounded-lg">
                                    <option value="AKTIF" {{ old('status_karyawan', $user->status_karyawan) == 'AKTIF' ? 'selected' : '' }}>Active</option>
                                    <option value="TIDAK AKTIF" {{ old('status_karyawan', $user->status_karyawan) == 'TIDAK AKTIF' ? 'selected' : '' }}>Inactive</option>
                                </select>
                            </div>

                            <!-- Employment Status Code -->
                            <div>
                                <label for="kode_status_kepegawaian" class="block text-sm font-medium text-gray-300 mb-2">
                                    Employment Status Code <span class="text-red-400">*</span>
                                </label>
                                <select name="kode_status_kepegawaian" 
                                        id="kode_status_kepegawaian"
                                        required
                                        class="w-full px-4 py-3 form-input rounded-lg">
                                    <option value="1" {{ old('kode_status_kepegawaian', $user->kode_status_kepegawaian) == '1' ? 'selected' : '' }}>1 - Permanent</option>
                                    <option value="2" {{ old('kode_status_kepegawaian', $user->kode_status_kepegawaian) == '2' ? 'selected' : '' }}>2 - Contract</option>
                                    <option value="3" {{ old('kode_status_kepegawaian', $user->kode_status_kepegawaian) == '3' ? 'selected' : '' }}>3 - Intern</option>
                                    <option value="4" {{ old('kode_status_kepegawaian', $user->kode_status_kepegawaian) == '4' ? 'selected' : '' }}>4 - Freelance</option>
                                </select>
                            </div>

                            <!-- Access Level -->
                            <div>
                                <label for="hak_akses" class="block text-sm font-medium text-gray-300 mb-2">
                                    Access Level
                                </label>
                                <select name="hak_akses" 
                                        id="hak_akses"
                                        class="w-full px-4 py-3 form-input rounded-lg">
                                    <option value="2" {{ old('hak_akses', $user->hak_akses) == '2' ? 'selected' : '' }}>2 - General User</option>
                                    <option value="1" {{ old('hak_akses', $user->hak_akses) == '1' ? 'selected' : '' }}>1 - Super Admin</option>
                                    <option value="3" {{ old('hak_akses', $user->hak_akses) == '3' ? 'selected' : '' }}>3 - Basic User</option>
                                    <option value="4" {{ old('hak_akses', $user->hak_akses) == '4' ? 'selected' : '' }}>4 - HRD</option>
                                    <option value="5" {{ old('hak_akses', $user->hak_akses) == '5' ? 'selected' : '' }}>5 - Manager</option>
                                    <option value="6" {{ old('hak_akses', $user->hak_akses) == '6' ? 'selected' : '' }}>6 - HRD Site</option>
                                </select>
                            </div>

                            <!-- Join Date -->
                            <div>
                                <label for="tgl_masuk_karyawan" class="block text-sm font-medium text-gray-300 mb-2">
                                    Join Date
                                </label>
                                <input type="date" 
                                       name="tgl_masuk_karyawan" 
                                       id="tgl_masuk_karyawan"
                                       class="w-full px-4 py-3 form-input rounded-lg"
                                       value="{{ old('tgl_masuk_karyawan', $user->tgl_masuk_karyawan) }}">
                            </div>
                        </div>
                    </div>

                    <!-- Role Information - CHECKBOX GRID -->
                    <div class="mb-8">
                        <h2 class="text-xl font-bold text-white mb-4 flex items-center gap-2">
                            <svg class="w-5 h-5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                            </svg>
                            User Roles
                        </h2>
                        
                        <p class="text-gray-400 mb-4">Select roles for this user by checking the boxes below.</p>
                        
                        <div class="roles-grid">
                            @foreach($allRoles as $role)
                                @php
                                    $isChecked = in_array($role->name, $userRoles);
                                @endphp
                                <div class="role-checkbox-card {{ $isChecked ? 'checked' : '' }}"
                                     onclick="toggleRoleCheckbox(this, '{{ $role->name }}')">
                                    <input type="checkbox" 
                                           name="roles[]" 
                                           value="{{ $role->name }}"
                                           id="role_{{ $role->name }}"
                                           class="role-checkbox-input"
                                           {{ $isChecked ? 'checked' : '' }}>
                                    <label for="role_{{ $role->name }}" class="role-checkbox-label">
                                        <span class="custom-checkbox">
                                            <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                            </svg>
                                        </span>
                                        <span class="role-name">{{ ucfirst(str_replace('-', ' ', $role->name)) }}</span>
                                    </label>
                                </div>
                            @endforeach
                        </div>
                        
                        <p class="mt-4 text-sm text-gray-500">
                            You can select multiple roles. Each role grants specific permissions to the user.
                        </p>
                    </div>

                    <!-- Contact Information -->
                    <div class="mb-8">
                        <h2 class="text-xl font-bold text-white mb-4 flex items-center gap-2">
                            <svg class="w-5 h-5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                            </svg>
                            Contact Information
                        </h2>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Phone Number -->
                            <div>
                                <label for="no_hp" class="block text-sm font-medium text-gray-300 mb-2">
                                    Phone Number
                                </label>
                                <input type="text" 
                                       name="no_hp" 
                                       id="no_hp"
                                       maxlength="16"
                                       class="w-full px-4 py-3 form-input rounded-lg"
                                       placeholder="Enter phone number"
                                       value="{{ old('no_hp', $user->no_hp) }}">
                            </div>
                            
                            <!-- ID Card Number (KTP) -->
                            <div>
                                <label for="no_ktp" class="block text-sm font-medium text-gray-300 mb-2">
                                    ID Card Number (KTP)
                                </label>
                                <input type="text" 
                                       name="no_ktp" 
                                       id="no_ktp"
                                       maxlength="18"
                                       class="w-full px-4 py-3 form-input rounded-lg"
                                       placeholder="Enter ID card number"
                                       value="{{ old('no_ktp', $user->no_ktp) }}">
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Form Actions -->
                <div class="px-6 py-4 border-t border-gray-800 bg-gradient-to-r from-dark-800/50 to-dark-900/50 flex flex-col sm:flex-row items-center justify-between gap-4">
                    <div class="text-sm text-gray-400">
                        <span class="text-red-400">*</span> Required fields
                    </div>
                    
                    <div class="flex gap-3">
                        <a href="{{ route('admin.master-departments.index', ['type' => 'user']) }}" 
                           class="px-6 py-3 btn-secondary rounded-lg hover:bg-white/10 transition-all duration-300">
                            Cancel
                        </a>
                        <button type="submit" 
                                class="px-6 py-3 btn-primary rounded-lg flex items-center gap-2 transition-all duration-300">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            Update User
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script>
        function toggleRoleCheckbox(card, roleName) {
            const checkbox = card.querySelector('input[type="checkbox"]');
            checkbox.checked = !checkbox.checked;
            
            if (checkbox.checked) {
                card.classList.add('checked');
            } else {
                card.classList.remove('checked');
            }
        }
    </script>

</x-app-layout-dark>