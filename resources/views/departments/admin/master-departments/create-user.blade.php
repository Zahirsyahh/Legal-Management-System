<x-app-layout-dark title="Add New User">
    
    <style>
        /* Style tetap sama seperti sebelumnya */
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
        
        /* Improved Select Styling */
        select.form-input {
            appearance: none;
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%239ca3af' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e");
            background-position: right 0.75rem center;
            background-repeat: no-repeat;
            background-size: 1.5em 1.5em;
            padding-right: 2.5rem;
        }
        
        select.form-input option {
            background-color: #1e293b;
            color: white;
        }
        
        select.form-input:focus option {
            background-color: #0f172a;
        }
        
        /* Role Options Styling - SAMA DENGAN EDIT */
        .role-options-container {
            max-height: 200px;
            overflow-y: auto;
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 0.5rem;
            background: rgba(30, 41, 59, 0.5);
            padding: 0.5rem;
        }
        
        .role-option {
            display: flex;
            align-items: center;
            padding: 0.75rem;
            margin-bottom: 0.25rem;
            border-radius: 0.375rem;
            cursor: pointer;
            transition: all 0.2s ease;
            border: 1px solid transparent;
            width: 100%;
            text-align: left;
            background: transparent;
        }
        
        .role-option:hover {
            background: rgba(59, 130, 246, 0.1);
            border-color: rgba(59, 130, 246, 0.2);
        }
        
        .role-option.selected {
            background: rgba(34, 197, 94, 0.1);
            border-color: rgba(34, 197, 94, 0.3);
        }
        
        .role-option:disabled,
        .role-option.disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }
        
        .role-checkbox {
            width: 20px;
            height: 20px;
            border: 2px solid rgba(255, 255, 255, 0.2);
            border-radius: 4px;
            margin-right: 0.75rem;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s ease;
        }
        
        .role-option.selected .role-checkbox {
            background: linear-gradient(135deg, #10b981, #34d399);
            border-color: #10b981;
        }
        
        .role-checkbox svg {
            opacity: 0;
            transition: opacity 0.2s ease;
        }
        
        .role-option.selected .role-checkbox svg {
            opacity: 1;
        }
        
        .role-label {
            flex: 1;
            font-size: 0.875rem;
            color: #e2e8f0;
        }
        
        .role-badge {
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 500;
            background: rgba(139, 92, 246, 0.1);
            color: #a855f7;
            border: 1px solid rgba(139, 92, 246, 0.3);
        }
        
        .role-badge.primary {
            background: rgba(34, 197, 94, 0.1);
            color: #10b981;
            border-color: rgba(34, 197, 94, 0.3);
        }
        
        .selected-roles-container {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
            margin-top: 1rem;
            min-height: 40px;
        }
        
        .empty-state {
            color: #94a3b8;
            font-style: italic;
            font-size: 0.875rem;
        }
        
        .animate-fade-in {
            animation: fade-in 0.2s ease-out;
        }
        
        @keyframes fade-in {
            from {
                opacity: 0;
            }
            to {
                opacity: 1;
            }
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
                            Add New User
                        </h1>
                        <p class="text-gray-400 mt-1">Create a new user account</p>
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
            <form action="{{ route('admin.master-departments.store') }}" method="POST">
                @csrf
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
                            <!-- User ID Optional dengan auto-generate -->
                            <div>
                                <label for="id_user" class="block text-sm font-medium text-gray-300 mb-2">
                                    User ID
                                    <span class="text-gray-500 text-xs ml-2">(Auto-generated if empty)</span>
                                </label>
                                <input type="number" 
                                       name="id_user" 
                                       id="id_user"
                                       class="w-full px-4 py-3 form-input rounded-lg"
                                       placeholder="Leave empty for auto-generate"
                                       value="{{ old('id_user') }}">
                                <p class="mt-1 text-xs text-gray-500">Leave empty to auto-generate next available ID</p>
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
                                       value="{{ old('nama_user') }}">
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
                                       value="{{ old('username') }}">
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
                                       value="{{ old('email') }}">
                            </div>

                            <!-- Password Field -->
                            <div>
                                <label for="password" class="block text-sm font-medium text-gray-300 mb-2">
                                    Password
                                    <span class="text-gray-500 text-xs ml-2">(Default: NIP or username)</span>
                                </label>
                                <div class="relative">
                                    <input type="password" 
                                           name="password" 
                                           id="password"
                                           class="w-full px-4 py-3 form-input rounded-lg pr-12"
                                           placeholder="Leave empty for default password"
                                           minlength="6">
                                    <button type="button" 
                                            id="passwordToggle"
                                            class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-white">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        </svg>
                                    </button>
                                </div>
                                <p class="mt-1 text-xs text-gray-500">Leave empty to use NIP or username as default password (min. 6 chars)</p>
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
                                       value="{{ old('nip') }}">
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
                                       value="{{ old('jabatan') }}">
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
                                        <option value="{{ $department->kode_pendek }}" {{ old('kode_department') == $department->kode_pendek ? 'selected' : '' }}>
                                            {{ $department->kode_pendek }} - {{ $department->nama_departemen }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Status Karyawan -->
                            <div>
                                <label for="status_karyawan" class="block text-sm font-medium text-gray-300 mb-2">
                                    Employee Status <span class="text-red-400">*</span>
                                </label>
                                <select name="status_karyawan" 
                                        id="status_karyawan"
                                        required
                                        class="w-full px-4 py-3 form-input rounded-lg">
                                    <option value="AKTIF" {{ old('status_karyawan', 'AKTIF') == 'AKTIF' ? 'selected' : '' }}>Active</option>
                                    <option value="TIDAK AKTIF" {{ old('status_karyawan') == 'TIDAK AKTIF' ? 'selected' : '' }}>Inactive</option>
                                </select>
                            </div>

                            <!-- Kode Status Kepegawaian -->
                            <div>
                                <label for="kode_status_kepegawaian" class="block text-sm font-medium text-gray-300 mb-2">
                                    Employment Status Code <span class="text-red-400">*</span>
                                </label>
                                <select name="kode_status_kepegawaian" 
                                        id="kode_status_kepegawaian"
                                        required
                                        class="w-full px-4 py-3 form-input rounded-lg">
                                    <option value="1" {{ old('kode_status_kepegawaian', '1') == '1' ? 'selected' : '' }}>1 - Permanent</option>
                                    <option value="2" {{ old('kode_status_kepegawaian') == '2' ? 'selected' : '' }}>2 - Contract</option>
                                    <option value="3" {{ old('kode_status_kepegawaian') == '3' ? 'selected' : '' }}>3 - Intern</option>
                                    <option value="4" {{ old('kode_status_kepegawaian') == '4' ? 'selected' : '' }}>4 - Freelance</option>
                                </select>
                            </div>

                            <!-- Hak Akses -->
                            <div>
                                <label for="hak_akses" class="block text-sm font-medium text-gray-300 mb-2">
                                    Access Level
                                </label>
                                <select name="hak_akses" 
                                        id="hak_akses"
                                        class="w-full px-4 py-3 form-input rounded-lg">
                                    <option value="2" {{ old('hak_akses', '2') == '2' ? 'selected' : '' }}>2 - General User</option>
                                    <option value="1" {{ old('hak_akses') == '1' ? 'selected' : '' }}>1 - Super Admin</option>
                                    <option value="3" {{ old('hak_akses') == '3' ? 'selected' : '' }}>3 - Basic User</option>
                                    <option value="4" {{ old('hak_akses') == '4' ? 'selected' : '' }}>4 - HRD</option>
                                    <option value="5" {{ old('hak_akses') == '5' ? 'selected' : '' }}>5 - Manager</option>
                                    <option value="6" {{ old('hak_akses') == '6' ? 'selected' : '' }}>6 - HRD Site</option>
                                </select>
                            </div>

                            <!-- Tanggal Masuk -->
                            <div>
                                <label for="tgl_masuk_karyawan" class="block text-sm font-medium text-gray-300 mb-2">
                                    Join Date
                                </label>
                                <input type="date" 
                                       name="tgl_masuk_karyawan" 
                                       id="tgl_masuk_karyawan"
                                       class="w-full px-4 py-3 form-input rounded-lg"
                                       value="{{ old('tgl_masuk_karyawan') }}">
                            </div>
                        </div>
                    </div>

                    <!-- Role Information - VERSI BUTTON SAMA DENGAN EDIT -->
                    <div class="mb-8">
                        <h2 class="text-xl font-bold text-white mb-4 flex items-center gap-2">
                            <svg class="w-5 h-5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                            </svg>
                            Role Information
                        </h2>
                        
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                            <!-- Primary Role -->
                            <div>
                                <label for="role" class="block text-sm font-medium text-gray-300 mb-2">
                                    Primary Role <span class="text-red-400">*</span>
                                </label>
                                <select name="role" 
                                        id="role" 
                                        required
                                        class="w-full px-4 py-3 form-input rounded-lg mb-3">
                                    <option value="">Select Primary Role</option>
                                    @foreach($roles as $role)
                                        <option value="{{ $role->name }}" {{ old('role') == $role->name ? 'selected' : '' }}>
                                            {{ ucfirst($role->name) }}
                                        </option>
                                    @endforeach
                                </select>
                                <p class="text-xs text-gray-500">Main role that determines user's primary permissions</p>
                            </div>

                            <!-- Additional Roles -->
                            <div>
                                <label class="block text-sm font-medium text-gray-300 mb-2">
                                    Additional Roles <span class="text-gray-500 text-xs">(Optional)</span>
                                </label>
                                <p class="text-sm text-gray-400 mb-3">Select additional roles for extended permissions</p>
                                
                                <!-- Hidden input untuk menyimpan selected roles -->
                                <input type="hidden" name="roles" id="selectedRolesInput" value="{{ old('roles', '') }}">
                                
                                <!-- Role Options Container -->
                                <div class="role-options-container" id="roleOptionsContainer">
                                    @foreach($allRoles as $role)
                                        @php
                                            $oldRoles = is_array(old('roles')) ? old('roles') : [];
                                            $isSelected = in_array($role->name, $oldRoles);
                                            $currentPrimary = old('role');
                                            $isCurrentlyPrimary = ($currentPrimary === $role->name);
                                        @endphp
                                        
                                        <button type="button"
                                                class="role-option w-full text-left {{ $isSelected ? 'selected' : '' }} {{ $isCurrentlyPrimary ? 'opacity-50 cursor-not-allowed' : '' }}"
                                                data-role="{{ $role->name }}"
                                                onclick="{{ !$isCurrentlyPrimary ? "toggleRole('{$role->name}')" : '' }}">
                                            <div class="role-checkbox">
                                                @if($isSelected)
                                                <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                                </svg>
                                                @endif
                                            </div>
                                            <span class="role-label">{{ ucfirst($role->name) }}</span>
                                            @if($isCurrentlyPrimary)
                                            <span class="role-badge primary">Primary</span>
                                            @endif
                                        </button>
                                    @endforeach
                                </div>
                                
                                <!-- Selected Roles Display -->
                                <div class="selected-roles-container mt-3" id="selectedRolesContainer">
                                    @php
                                        $oldRoles = is_array(old('roles')) ? old('roles') : [];
                                        $hasAdditionalRoles = false;
                                    @endphp
                                    
                                    @foreach($oldRoles as $roleName)
                                        @php
                                            $currentPrimary = old('role');
                                            $isCurrentlyPrimary = ($currentPrimary === $roleName);
                                        @endphp
                                        
                                        @if(!$isCurrentlyPrimary)
                                            <div class="role-badge flex items-center gap-1" id="roleBadge-{{ $roleName }}">
                                                {{ ucfirst($roleName) }}
                                                <button type="button" onclick="removeRole('{{ $roleName }}')" class="ml-1 text-gray-400 hover:text-white">
                                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                                    </svg>
                                                </button>
                                            </div>
                                            @php $hasAdditionalRoles = true; @endphp
                                        @endif
                                    @endforeach
                                    
                                    @if(!$hasAdditionalRoles)
                                        <div class="empty-state">No additional roles selected</div>
                                    @endif
                                </div>
                                
                                <p class="mt-2 text-xs text-gray-500">
                                    • Click on roles to select/deselect them
                                    <br>• Primary role cannot be selected as additional role
                                </p>
                            </div>
                        </div>
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
                            <!-- No HP -->
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
                                       value="{{ old('no_hp') }}">
                            </div>
                            
                            <!-- No KTP -->
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
                                       value="{{ old('no_ktp') }}">
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
                            Create User
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            let selectedRoles = new Set();
            
            // Initialize selected roles from old input
            @if(is_array(old('roles')))
                @foreach(old('roles') as $roleName)
                    selectedRoles.add('{{ $roleName }}');
                @endforeach
            @endif
            
            // Update hidden input
            updateSelectedRolesInput();
            
            // Primary role select change handler
            const primaryRoleSelect = document.getElementById('role');
            if (primaryRoleSelect) {
                primaryRoleSelect.addEventListener('change', function() {
                    const selectedPrimary = this.value;
                    updateRoleOptions();
                });
            }
            
            // Initialize role options
            updateRoleOptions();
            
            // Form validation
            const form = document.querySelector('form');
            form.addEventListener('submit', function(e) {
                let isValid = true;
                const requiredFields = form.querySelectorAll('[required]');
                
                requiredFields.forEach(field => {
                    if (!field.value.trim()) {
                        isValid = false;
                        field.classList.add('border-red-500');
                        
                        let errorMsg = field.parentElement.querySelector('.error-message');
                        if (!errorMsg) {
                            errorMsg = document.createElement('p');
                            errorMsg.className = 'error-message text-red-400 text-xs mt-1';
                            errorMsg.textContent = 'This field is required';
                            field.parentElement.appendChild(errorMsg);
                        }
                    } else {
                        field.classList.remove('border-red-500');
                        const errorMsg = field.parentElement.querySelector('.error-message');
                        if (errorMsg) {
                            errorMsg.remove();
                        }
                    }
                });
                
                if (!isValid) {
                    e.preventDefault();
                    const firstError = form.querySelector('.border-red-500');
                    if (firstError) {
                        firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
                        firstError.focus();
                    }
                }
            });

            // Remove error styling on input
            const inputs = form.querySelectorAll('input, select');
            inputs.forEach(input => {
                input.addEventListener('input', function() {
                    this.classList.remove('border-red-500');
                    const errorMsg = this.parentElement.querySelector('.error-message');
                    if (errorMsg) {
                        errorMsg.remove();
                    }
                });
            });

            // Password toggle functionality
            const passwordToggle = document.getElementById('passwordToggle');
            if (passwordToggle) {
                passwordToggle.addEventListener('click', function() {
                    const passwordField = document.getElementById('password');
                    const type = passwordField.getAttribute('type') === 'password' ? 'text' : 'password';
                    passwordField.setAttribute('type', type);
                    
                    // Toggle icon
                    const icon = this.querySelector('svg');
                    if (type === 'text') {
                        icon.innerHTML = `<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L6.59 6.59m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />`;
                    } else {
                        icon.innerHTML = `<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />`;
                    }
                });
            }

            // Auto-generate password from NIP or username
            const nipField = document.getElementById('nip');
            const usernameField = document.getElementById('username');
            const passwordField = document.getElementById('password');
            
            function autoGeneratePassword() {
                if (!passwordField.value) {
                    const value = nipField.value || usernameField.value;
                    if (value) {
                        passwordField.value = value;
                    }
                }
            }
            
            nipField.addEventListener('blur', autoGeneratePassword);
            usernameField.addEventListener('blur', autoGeneratePassword);
        });
        
        // Toggle role selection
        function toggleRole(roleName) {
            const primaryRole = document.getElementById('role').value;
            
            // Don't allow selecting primary role as additional
            if (roleName === primaryRole) {
                return;
            }
            
            const roleElement = document.querySelector(`.role-option[data-role="${roleName}"]`);
            
            if (selectedRoles.has(roleName)) {
                // Remove role
                selectedRoles.delete(roleName);
                roleElement.classList.remove('selected');
            } else {
                // Add role
                selectedRoles.add(roleName);
                roleElement.classList.add('selected');
            }
            
            updateSelectedRolesDisplay();
            updateSelectedRolesInput();
        }
        
        // Remove role from selection
        function removeRole(roleName) {
            selectedRoles.delete(roleName);
            
            const roleElement = document.querySelector(`.role-option[data-role="${roleName}"]`);
            if (roleElement) {
                roleElement.classList.remove('selected');
            }
            
            updateSelectedRolesDisplay();
            updateSelectedRolesInput();
        }
        
        // Update selected roles display
        function updateSelectedRolesDisplay() {
            const container = document.getElementById('selectedRolesContainer');
            if (!container) return;
            
            container.innerHTML = '';
            
            if (selectedRoles.size === 0) {
                container.innerHTML = '<div class="empty-state">No additional roles selected</div>';
                return;
            }
            
            selectedRoles.forEach(roleName => {
                const badge = document.createElement('div');
                badge.className = 'role-badge flex items-center gap-1';
                badge.id = `roleBadge-${roleName}`;
                badge.innerHTML = `
                    ${roleName.charAt(0).toUpperCase() + roleName.slice(1)}
                    <button type="button" onclick="removeRole('${roleName}')" class="ml-1 text-gray-400 hover:text-white">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                `;
                container.appendChild(badge);
            });
        }
        
        // Update hidden input with selected roles
        function updateSelectedRolesInput() {
            const input = document.getElementById('selectedRolesInput');
            if (input) {
                input.value = Array.from(selectedRoles).join(',');
            }
        }
        
        // Update role options based on primary role selection
        function updateRoleOptions() {
            const primaryRole = document.getElementById('role').value;
            const roleOptions = document.querySelectorAll('.role-option');
            
            roleOptions.forEach(option => {
                const roleName = option.dataset.role;
                
                if (roleName === primaryRole) {
                    // Disable primary role option
                    option.classList.add('opacity-50', 'cursor-not-allowed');
                    option.onclick = null;
                    
                    // Remove from selected roles if it was selected
                    if (selectedRoles.has(roleName)) {
                        selectedRoles.delete(roleName);
                        option.classList.remove('selected');
                    }
                } else {
                    // Enable non-primary role options
                    option.classList.remove('opacity-50', 'cursor-not-allowed');
                    option.onclick = () => toggleRole(roleName);
                }
            });
            
            updateSelectedRolesDisplay();
            updateSelectedRolesInput();
        }
    </script>

</x-app-layout-dark>