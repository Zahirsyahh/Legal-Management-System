{{-- resources/views/surat/create.blade.php --}}
<x-app-layout-dark title="Create Surat Keluar">
    <div class="pb-8 px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto">
        <!-- Header -->
        <div class="mb-8 animate-fade-in">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl md:text-4xl font-bold mb-2">📄 Create Surat Keluar</h1>
                    <p class="text-gray-400">Form pengajuan penomoran surat keluar. File PDF wajib diupload.</p>
                </div>
                <a href="{{ route('contracts.index') }}" class="flex items-center text-blue-400 hover:text-blue-300">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Back to List
                </a>
            </div>
        </div>

        {{-- Error/Warning Messages --}}
        @php
            $user = Auth::user();
            $hrmsUser = \Illuminate\Support\Facades\DB::table('tbl_user')
                ->where('email', $user->email)
                ->first(['kode_department', 'nama_user']);
            $departmentCode = $hrmsUser ? strtoupper($hrmsUser->kode_department ?? '') : '';
            
            $departmentName = '';
            if ($departmentCode) {
                $department = \Illuminate\Support\Facades\DB::table('tbl_department')
                    ->where('kode_pendek', $departmentCode)
                    ->first(['nama_departemen']);
                $departmentName = $department->nama_departemen ?? $departmentCode;
            }
            
            $hasError = empty($departmentCode);
            $errorMessage = $hasError ? 'Your account is not linked to any department in HRMS. Please contact administrator.' : '';
        @endphp

        @if($hasError)
            <div class="mb-6 p-4 bg-yellow-500/10 border border-yellow-500/30 rounded-xl">
                <div class="flex items-start gap-3">
                    <svg class="w-6 h-6 text-yellow-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.998-.833-2.732 0L4.732 16.5c-.77.833.192 2.5 1.732 2.5z" />
                    </svg>
                    <div class="flex-1">
                        <h3 class="text-yellow-400 font-semibold mb-1">⚠️ Warning</h3>
                        <p class="text-yellow-300 text-sm">{{ $errorMessage }}</p>
                        <p class="text-yellow-400/70 text-xs mt-2">
                            You can still fill the form, but you won't be able to submit until this is resolved.
                        </p>
                    </div>
                </div>
            </div>
        @endif

        @if($errors->any())
            <div class="mb-6 p-4 bg-red-500/10 border border-red-500/30 rounded-xl">
                <div class="flex items-center gap-2 text-red-400 mb-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span class="font-medium">Terdapat kesalahan:</span>
                </div>
                <ul class="list-disc list-inside text-sm text-red-400 space-y-1">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if(session('error'))
            <div class="mb-6 p-4 bg-red-500/10 border border-red-500/30 rounded-xl">
                <div class="flex items-center gap-2 text-red-400">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                    <span class="font-medium">{{ session('error') }}</span>
                </div>
            </div>
        @endif

        @if(session('success'))
            <div class="mb-6 p-4 bg-green-500/10 border border-green-500/30 rounded-xl">
                <div class="flex items-center gap-2 text-green-400">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    <span class="font-medium">{{ session('success') }}</span>
                </div>
            </div>
        @endif

        <!-- Form Card -->
        <div class="glass-card rounded-2xl p-6 md:p-8 animate-slide-up">
            <form action="{{ route('surat.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6" id="surat-form">
                @csrf
                
                <!-- Error Messages -->
                @if($errors->any())
                    <div class="p-4 bg-red-500/10 border border-red-500/30 rounded-xl">
                        <div class="flex items-center gap-2 text-red-400 mb-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span class="font-medium">Terdapat kesalahan:</span>
                        </div>
                        <ul class="list-disc list-inside text-sm text-red-400 space-y-1">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <!-- Grid 2 Kolom -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 lg:gap-8">
                    <!-- LEFT COLUMN - INFORMASI SURAT -->
                    <div class="space-y-6">
                        <!-- Nomor Surat - OTOMATIS -->
                        <div class="bg-dark-800/40 p-5 rounded-xl border border-gray-700/50">
                            <div class="flex items-center gap-3 mb-3">
                                <div class="w-10 h-10 rounded-full bg-purple-500/20 flex items-center justify-center">
                                    <svg class="w-5 h-5 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l5 5a2 2 0 01.586 1.414V19a2 2 0 01-2 2H7a2 2 0 01-2-2V5a2 2 0 012-2z" />
                                    </svg>
                                </div>
                                <div>
                                    <span class="text-sm font-medium text-gray-400">Letter Number</span>
                                    <p class="text-xs text-gray-500">Generated by Legal</p>
                                </div>
                            </div>
                            <div class="bg-dark-900/60 p-3 rounded-lg border border-gray-700/50">
                                <div class="mt-2 flex items-center gap-2 text-xs">
                                    <span class="w-2 h-2 bg-yellow-500 rounded-full"></span>
                                    <span class="text-yellow-400">Waiting for Legal approval to generate letter number</span>
                                </div>
                            </div>
                        </div>

                        <!-- Kualifikasi Surat -->
                        <div>
                            <label for="title" class="block text-sm font-medium text-gray-300 mb-2">
                                Title <span class="text-red-400">*</span>
                            </label>
                            <div class="relative">
                                <div class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-500">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z" />
                                    </svg>
                                </div>
                                <input type="text" 
                                    name="title" 
                                    id="title" 
                                    value="{{ old('title') }}"
                                    required
                                    class="w-full px-4 py-3 pl-10 bg-dark-800 border border-gray-700 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors @error('title') border-red-500 @enderror"
                                    placeholder="outgoing letters, assignment letters, statement letters, etc.">
                            </div>
                        </div>

                        <!-- Tanggal Surat -->
                        <div>
                            <label for="effective_date" class="block text-sm font-medium text-gray-300 mb-2">
                                Date <span class="text-red-400">*</span>
                            </label>
                            <div class="relative">
                                <div class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-500">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                </div>
                                <input type="date" 
                                    name="effective_date" 
                                    id="effective_date"
                                    value="{{ old('effective_date', date('Y-m-d')) }}"
                                    required
                                    class="w-full px-4 py-3 pl-10 bg-dark-800 border border-gray-700 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            </div>
                        </div>

                        <!-- Deskripsi -->
                        <div>
                            <label for="description" class="block text-sm font-medium text-gray-300 mb-2">
                                Description
                            </label>
                            <textarea name="description" 
                                    id="description" 
                                    rows="4"
                                    class="w-full px-4 py-3 bg-dark-800 border border-gray-700 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                    placeholder="explain the purpose and contents of the letter briefly..">{{ old('description') }}</textarea>
                        </div>
                    </div>

                    <!-- RIGHT COLUMN - FILE & DEPARTMENT -->
                    <div class="space-y-6">
                        <!-- Upload File PDF - FIXED VERSION -->
                        <div class="bg-dark-800/40 p-5 rounded-xl border border-gray-700/50">
                            <div class="flex items-center gap-3 mb-4">
                                <div class="w-10 h-10 rounded-full bg-red-500/20 flex items-center justify-center">
                                    <svg class="w-5 h-5 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                    </svg>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-300">
                                        File Draft Surat (PDF) <span class="text-red-400">*</span>
                                    </label>
                                    <p class="text-xs text-gray-500">Max 10 MB</p>
                                </div>
                            </div>
                            
                            <label for="surat_file_input" class="border-2 border-dashed border-gray-700 rounded-lg p-6 text-center hover:border-blue-500 transition-colors cursor-pointer block w-full">
                                <input type="file" 
                                    name="surat_file" 
                                    id="surat_file_input" 
                                    accept=".pdf,application/pdf"
                                    required
                                    class="hidden">
                                
                                <div id="upload_placeholder" class="space-y-2">
                                    <svg class="w-12 h-12 mx-auto text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                                    </svg>
                                    <p class="text-sm text-gray-400">
                                        <span class="text-blue-400 font-medium">Click to Upload</span>
                                    </p>
                                    <p class="text-xs text-gray-500">File draft surat dalam format PDF</p>
                                </div>
                                
                                <div id="file_info" class="hidden mt-2 p-3 bg-blue-500/10 rounded-lg">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center gap-2">
                                            <svg class="w-5 h-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                            </svg>
                                            <span id="file_name_display" class="text-sm font-medium text-blue-300"></span>
                                        </div>
                                        <button type="button" 
                                                onclick="removeFile()"
                                                class="text-gray-500 hover:text-red-400">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                            </svg>
                                        </button>
                                    </div>
                                    <p class="text-xs text-gray-500 mt-1">
                                        Size: <span id="file_size_display"></span>
                                    </p>
                                </div>
                            </label>
                            @error('surat_file')
                                <p class="mt-2 text-sm text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Department (READONLY dari HRMS) - UPDATED VERSION -->
                        <div class="bg-dark-800/50 p-5 rounded-xl border border-gray-700">
                            <div class="flex items-center gap-3 mb-4">
                                <div class="w-10 h-10 rounded-full bg-gradient-to-br from-green-500 to-emerald-500 flex items-center justify-center">
                                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                    </svg>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-300">Department</label>
                                    <p class="text-xs text-gray-500">Department anda dari HRMS</p>
                                </div>
                            </div>
                            
                            <div class="bg-dark-900/80 p-4 rounded-lg border border-gray-700/50">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <span class="text-sm text-gray-400">Kode Department:</span>
                                        <span class="ml-2 px-3 py-1.5 bg-blue-500/20 text-blue-400 font-mono rounded-lg text-sm">
                                            {{ $departmentCode ?: 'Tidak terdeteksi' }}
                                        </span>
                                    </div>
                                    @if($departmentName)
                                        <span class="text-xs text-gray-500">{{ $departmentName }}</span>
                                    @endif
                                </div>
                                <div class="mt-2 text-xs text-gray-500">
                                    <span class="text-yellow-400">ⓘ</span> Department diambil otomatis dari HRMS, tidak dapat diubah
                                </div>
                                <input type="hidden" name="department_code" value="{{ $departmentCode }}">
                            </div>
                        </div>

                        <!-- Workflow Info -->
                        <div class="bg-blue-500/5 p-5 rounded-xl border border-blue-500/20">
                            <div class="flex items-start gap-3">
                                <div class="w-8 h-8 rounded-full bg-blue-500/10 flex items-center justify-center flex-shrink-0">
                                    <svg class="w-4 h-4 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                                <div>
                                    <h4 class="text-sm font-medium text-blue-300 mb-1">Alur Pengajuan Surat</h4>
                                    <ul class="text-xs text-gray-400 space-y-1.5">
                                        <li class="flex items-center gap-2">
                                            <span class="w-1.5 h-1.5 bg-gray-500 rounded-full"></span>
                                            <span>User mengisi form dan upload draft surat</span>
                                        </li>
                                        <li class="flex items-center gap-2">
                                            <span class="w-1.5 h-1.5 bg-yellow-500 rounded-full"></span>
                                            <span>Legal menerima dan melakukan approval</span>
                                        </li>
                                        <li class="flex items-center gap-2">
                                            <span class="w-1.5 h-1.5 bg-green-500 rounded-full"></span>
                                            <span>Legal generate nomor surat resmi</span>
                                        </li>
                                        <li class="flex items-center gap-2">
                                            <span class="w-1.5 h-1.5 bg-purple-500 rounded-full"></span>
                                            <span>User upload surat final dengan nomor & ttd</span>
                                        </li>
                                        <li class="flex items-center gap-2">
                                            <span class="w-1.5 h-1.5 bg-blue-500 rounded-full"></span>
                                            <span>Status berubah menjadi EXECUTED</span>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Hidden Fields -->
                <input type="hidden" name="contract_type" value="surat">
                <input type="hidden" name="status" value="draft">

                <!-- Form Actions -->
                <div class="flex justify-end items-center gap-4 pt-6 border-t border-gray-800">
                    <a href="{{ route('contracts.index') }}" 
                       class="px-6 py-3 border border-gray-700 rounded-lg hover:bg-gray-800 transition-colors text-gray-300">
                        Cancel
                    </a>
                    
                    <button type="submit"
                            {{ $hasError ? 'disabled' : '' }}
                            class="px-8 py-3 bg-gradient-to-r from-blue-600 to-cyan-600 hover:from-blue-700 hover:to-cyan-700 rounded-lg font-medium transition-all duration-300 transform hover:-translate-y-0.5 shadow-lg shadow-blue-500/30 flex items-center gap-2 {{ $hasError ? 'opacity-50 cursor-not-allowed' : '' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                        </svg>
                        Submit an ongoing letter!
                    </button>
                </div>
            </form>
        </div>
        
        <!-- Help Card -->
        <div class="mt-6 p-4 bg-gray-800/30 border border-gray-700/50 rounded-xl">
            <div class="flex items-start">
                <svg class="w-5 h-5 text-gray-500 mt-0.5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <div class="text-xs text-gray-500">
                    <span class="font-medium text-gray-400">Format Nomor Surat:</span> 
                    [Sequence]/[Kode Dept]/GNI/S/[Romawi Bulan]/[Tahun] <br>
                    <span class="font-medium text-gray-400">Contoh:</span> 
                    <span class="font-mono bg-gray-800 px-2 py-1 rounded">001/ITS/GNI/S/II/2026</span>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            console.log('DOM loaded - initializing surat form');
            
            // Set default date to today
            const today = new Date().toISOString().split('T')[0];
            const effectiveDateInput = document.getElementById('effective_date');
            if (effectiveDateInput) {
                effectiveDateInput.value = today;
                effectiveDateInput.min = today;
            }

            // ========================================
            // FILE UPLOAD HANDLING
            // ========================================
            
            // Get elements
            const fileInput = document.getElementById('surat_file_input');
            const uploadPlaceholder = document.getElementById('upload_placeholder');
            const fileInfo = document.getElementById('file_info');
            const fileNameDisplay = document.getElementById('file_name_display');
            const fileSizeDisplay = document.getElementById('file_size_display');

            // File change event
            if (fileInput) {
                fileInput.addEventListener('change', function(e) {
                    const file = e.target.files[0];
                    console.log('File selected:', file ? file.name : 'none');
                    
                    if (file) {
                        // Validate file type
                        if (file.type !== 'application/pdf') {
                            alert('Only PDF files are allowed!');
                            this.value = '';
                            return;
                        }
                        
                        // Validate file size (10MB)
                        if (file.size > 10 * 1024 * 1024) {
                            alert('Maximum file size 10MB!');
                            this.value = '';
                            return;
                        }

                        // Show file info
                        if (uploadPlaceholder) uploadPlaceholder.classList.add('hidden');
                        if (fileInfo) fileInfo.classList.remove('hidden');
                        
                        // Display file name
                        if (fileNameDisplay) fileNameDisplay.textContent = file.name;
                        
                        // Format and display file size
                        if (fileSizeDisplay) {
                            let fileSize = file.size;
                            if (fileSize < 1024) {
                                fileSizeDisplay.textContent = fileSize + ' bytes';
                            } else if (fileSize < 1024 * 1024) {
                                fileSizeDisplay.textContent = (fileSize / 1024).toFixed(2) + ' KB';
                            } else {
                                fileSizeDisplay.textContent = (fileSize / (1024 * 1024)).toFixed(2) + ' MB';
                            }
                        }
                    }
                });
            }

            // Auto-capitalize title
            const titleInput = document.getElementById('title');
            if (titleInput) {
                titleInput.addEventListener('blur', function() {
                    this.value = this.value.toUpperCase();
                });
            }
        });

        // Remove file function (global)
        window.removeFile = function() {
            const fileInput = document.getElementById('surat_file_input');
            const uploadPlaceholder = document.getElementById('upload_placeholder');
            const fileInfo = document.getElementById('file_info');
            
            if (fileInput) {
                fileInput.value = '';
            }
            if (uploadPlaceholder) {
                uploadPlaceholder.classList.remove('hidden');
            }
            if (fileInfo) {
                fileInfo.classList.add('hidden');
            }
        };
    </script>
    @endpush

    {{-- FALLBACK: inline script untuk jaga-jaga kalau @push bermasalah --}}
    <script>
        (function() {
            console.log('Backup initialization running');
            
            // Set date
            const today = new Date().toISOString().split('T')[0];
            const effDate = document.getElementById('effective_date');
            if (effDate && !effDate.value) {
                effDate.value = today;
                effDate.min = today;
            }
            
            // Form submit handler
            const form = document.getElementById('surat-form');
            if (form && !form.hasAttribute('data-handler-attached')) {
                form.setAttribute('data-handler-attached', 'true');
                form.addEventListener('submit', function(e) {
                    const fileInput = document.getElementById('surat_file_input');
                    @if($hasError)
                        e.preventDefault();
                        alert('You do not have a department registered in HRMS. Please contact your administrator.');
                        return false;
                    @endif
                    
                    if (!fileInput || fileInput.files.length === 0) {
                        e.preventDefault();
                        alert('Please upload the PDF file first!');
                        return false;
                    }
                    if (!confirm('Submit an Outgoing Letter for Legal approval?')) {
                        e.preventDefault();
                        return false;
                    }
                });
            }
        })();
    </script>
</x-app-layout-dark>