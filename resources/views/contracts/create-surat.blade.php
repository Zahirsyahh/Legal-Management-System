{{-- resources/views/surat/create.blade.php --}}
<x-app-layout-dark title="Create Outgoing Letter">

    <style>
        @keyframes fadeInDown {
            from { opacity: 0; transform: translateY(-12px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        @keyframes scaleIn {
            from { opacity: 0; transform: scale(0.92); }
            to   { opacity: 1; transform: scale(1); }
        }
        @keyframes fileReveal {
            from { opacity: 0; transform: translateY(10px) scale(0.97); }
            to   { opacity: 1; transform: translateY(0) scale(1); }
        }
        @keyframes checkBounce {
            0%   { transform: scale(0) rotate(-20deg); opacity: 0; }
            60%  { transform: scale(1.25) rotate(6deg); opacity: 1; }
            100% { transform: scale(1) rotate(0deg); opacity: 1; }
        }
        @keyframes progressBar {
            from { width: 0%; }
            to   { width: 100%; }
        }

        .animate-fade-in-down { animation: fadeInDown .5s ease both; }
        .animate-scale-in     { animation: scaleIn   .45s ease both; }

        .step-dot {
            width: 8px; height: 8px;
            border-radius: 50%;
            flex-shrink: 0;
        }

        .upload-zone {
            border: 2px dashed rgba(107,114,128,.45);
            border-radius: 12px;
            padding: 28px 20px;
            text-align: center;
            cursor: pointer;
            display: block;
            width: 100%;
            transition: border-color .25s, background .25s, transform .2s;
        }
        .upload-zone:hover {
            border-color: rgba(96,165,250,.7);
            background: rgba(96,165,250,.05);
            transform: translateY(-1px);
        }

        #file-preview-card {
            display: none;
            opacity: 0;
        }
        #file-preview-card.visible {
            display: block;
            animation: fileReveal .35s cubic-bezier(.22,.68,0,1.2) both;
            opacity: 1;
        }
        /* Animasi hanya jalan sekali saat .animate class ditambah, bukan setiap render */
        #file-preview-card.visible .file-check-icon {
            animation: checkBounce .5s cubic-bezier(.22,.68,0,1.2) .1s both;
        }
        #file-preview-card.visible .file-progress-bar {
            animation: progressBar .6s ease .2s both;
        }

        .btn-submit {
            background: linear-gradient(90deg, #2563eb, #06b6d4, #2563eb);
            background-size: 200% auto;
            transition: background-position .5s ease, transform .2s, box-shadow .2s;
        }
        .btn-submit:hover:not(:disabled) {
            background-position: right center;
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(37,99,235,.4);
        }
        .btn-submit:disabled { opacity: .45; cursor: not-allowed; }

        .form-glass {
            background: rgba(17,24,39,.6);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255,255,255,.07);
            border-radius: 20px;
        }

        .input-field {
            background: rgba(17,24,39,.7);
            border: 1px solid rgba(75,85,99,.6);
            border-radius: 10px;
            padding: 11px 14px 11px 42px;
            color: #e5e7eb;
            width: 100%;
            transition: border-color .2s, box-shadow .2s;
        }
        .input-field:focus {
            outline: none;
            border-color: rgba(96,165,250,.7);
            box-shadow: 0 0 0 3px rgba(96,165,250,.15);
        }
        .input-field.no-icon { padding-left: 14px; }
        .input-field.error   { border-color: rgba(239,68,68,.6); }

        .section-label {
            font-size: .7rem;
            font-weight: 700;
            letter-spacing: .1em;
            text-transform: uppercase;
            color: #6b7280;
            margin-bottom: 12px;
        }

        .tag-badge {
            display: inline-flex; align-items: center;
            padding: 3px 10px;
            border-radius: 20px;
            font-size: .72rem;
            font-weight: 600;
            letter-spacing: .03em;
        }

        #upload-zone-wrapper { transition: all .3s ease; }
        #upload-zone-wrapper.hidden-soft { display: none; }

        /* Format info box highlight saat company dipilih */
        #fmt-example { transition: color .2s, background .2s; }
        #fmt-example.has-value { color: #22d3ee; }
    </style>

    <div class="pb-12 px-4 sm:px-6 lg:px-8 max-w-6xl mx-auto">

        {{-- ── Resolve department dari HRMS ── --}}
        @php
            $user           = Auth::user();
            $hrmsUser       = \Illuminate\Support\Facades\DB::table('tbl_user')
                                ->where('email', $user->email)
                                ->first(['kode_department', 'nama_user']);
            $departmentCode = $hrmsUser ? strtoupper($hrmsUser->kode_department ?? '') : '';
            $departmentName = '';
            if ($departmentCode) {
                $dept           = \Illuminate\Support\Facades\DB::table('tbl_department')
                                    ->where('kode_pendek', $departmentCode)
                                    ->first(['nama_departemen']);
                $departmentName = $dept->nama_departemen ?? $departmentCode;
            }
            $hasError     = empty($departmentCode);
            $errorMessage = $hasError
                ? 'Your account is not linked to any department in HRMS. Please contact the administrator.'
                : '';
        @endphp

        {{-- ── HEADER ── --}}
        <div class="mb-8 animate-fade-in-down">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <div class="flex items-center gap-3 mb-1">
                        <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-blue-600 to-cyan-500 flex items-center justify-center shadow-lg shadow-blue-500/30">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                        </div>
                        <h1 class="text-2xl md:text-3xl font-bold text-white tracking-tight">Create Outgoing Letter</h1>
                    </div>
                    <p class="text-gray-500 text-sm pl-0.5">Outgoing letter numbering request · PDF file upload is required</p>
                </div>
                <a href="{{ route('contracts.index') }}"
                   class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-medium text-gray-400 hover:text-white border border-gray-700 hover:border-gray-500 bg-gray-800/50 hover:bg-gray-800 transition-all duration-200">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    Back to List
                </a>
            </div>
        </div>

        {{-- ── ALERTS ── --}}
        @if($hasError)
            <div class="mb-5 p-4 bg-yellow-500/10 border border-yellow-500/25 rounded-2xl animate-fade-in-down flex gap-3">
                <svg class="w-5 h-5 text-yellow-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.998-.833-2.732 0L4.732 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                </svg>
                <div>
                    <p class="text-yellow-400 font-semibold text-sm mb-0.5">Department not detected</p>
                    <p class="text-yellow-300/80 text-xs">{{ $errorMessage }}</p>
                    <p class="text-yellow-500/60 text-xs mt-1">The form can still be filled in, but cannot be submitted until this issue is resolved.</p>
                </div>
            </div>
        @endif

        @if($errors->any())
            <div class="mb-5 p-4 bg-red-500/10 border border-red-500/25 rounded-2xl animate-fade-in-down">
                <div class="flex items-center gap-2 text-red-400 mb-2 text-sm font-semibold">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    The following errors occurred:
                </div>
                <ul class="list-disc list-inside text-xs text-red-400/80 space-y-1">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if(session('success'))
            <div class="mb-5 p-4 bg-green-500/10 border border-green-500/25 rounded-2xl animate-fade-in-down flex items-center gap-2 text-green-400 text-sm font-medium">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="mb-5 p-4 bg-red-500/10 border border-red-500/25 rounded-2xl animate-fade-in-down flex items-center gap-2 text-red-400 text-sm font-medium">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
                {{ session('error') }}
            </div>
        @endif

        {{-- ── MAIN FORM ── --}}
        <div class="form-glass p-6 md:p-8 animate-scale-in">

            <form action="{{ route('surat.store') }}" method="POST" enctype="multipart/form-data"
                  class="space-y-8" id="surat-form">
                @csrf
                <input type="hidden" name="contract_type" value="surat">
                <input type="hidden" name="status"        value="draft">
                <input type="hidden" name="department_code" value="{{ $departmentCode }}">

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 lg:gap-10">

                    {{-- ════ LEFT COLUMN ════ --}}
                    <div class="space-y-6">

                        {{-- Letter number status --}}
                        <div>
                            <p class="section-label">Letter Number</p>
                            <div class="bg-gray-900/60 rounded-xl border border-gray-700/50 p-4 flex items-center gap-3">
                                <div class="w-9 h-9 rounded-lg bg-purple-500/15 flex items-center justify-center flex-shrink-0">
                                    <svg class="w-4 h-4 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l5 5a2 2 0 01.586 1.414V19a2 2 0 01-2 2H7a2 2 0 01-2-2V5a2 2 0 012-2z"/>
                                    </svg>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-gray-500 text-xs mb-1">Generated by Legal Department</p>
                                    <div class="flex items-center gap-2">
                                        <span class="w-1.5 h-1.5 bg-yellow-400 rounded-full flex-shrink-0"></span>
                                        <span class="text-yellow-400 text-xs">Awaiting Legal approval</span>
                                    </div>
                                </div>
                                <span class="tag-badge bg-yellow-500/10 text-yellow-400 border border-yellow-500/20">PENDING</span>
                            </div>
                        </div>

                        {{-- Title --}}
                        <div>
                            <label for="title" class="block text-sm font-medium text-gray-300 mb-2">
                                Title <span class="text-red-400 ml-0.5">*</span>
                            </label>
                            <div class="relative">
                                <div class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-600 pointer-events-none">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/>
                                    </svg>
                                </div>
                                <input type="text"
                                       name="title"
                                       id="title"
                                       value="{{ old('title') }}"
                                       required
                                       placeholder="Assignment Letter, Statement Letter, etc."
                                       class="input-field @error('title') error @enderror">
                            </div>
                            @error('title')
                                <p class="mt-1.5 text-xs text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Company --}}
                        <div>
                            <label for="company" class="block text-sm font-medium text-gray-300 mb-2">
                                Company <span class="text-red-400 ml-0.5">*</span>
                            </label>
                            <div class="relative">
                                <div class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-600 pointer-events-none">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                    </svg>
                                </div>
                                <select name="company"
                                        id="company"
                                        required
                                        class="input-field @error('company') error @enderror"
                                        style="padding-left: 42px;">
                                    <option value="">Select Company</option>
                                    <option value="GNI" {{ old('company') == 'GNI' ? 'selected' : '' }}>GNI — Gunbuster Nickel Industry</option>
                                    <option value="AMI" {{ old('company') == 'AMI' ? 'selected' : '' }}>AMI — Alchemist Metal Industry</option>
                                </select>
                            </div>
                            @error('company')
                                <p class="mt-1.5 text-xs text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Letter Date --}}
                        <div>
                            <label for="effective_date" class="block text-sm font-medium text-gray-300 mb-2">
                                Letter Date <span class="text-red-400 ml-0.5">*</span>
                            </label>
                            <div class="relative">
                                <div class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-600 pointer-events-none">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                </div>
                                <input type="date"
                                       name="effective_date"
                                       id="effective_date"
                                       value="{{ old('effective_date', date('Y-m-d')) }}"
                                       required
                                       class="input-field">
                            </div>
                            @error('effective_date')
                                <p class="mt-1.5 text-xs text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Description --}}
                        <div>
                            <label for="description" class="block text-sm font-medium text-gray-300 mb-2">
                                Description
                                <span class="text-gray-600 font-normal text-xs ml-1">(optional)</span>
                            </label>
                            <textarea name="description"
                                      id="description"
                                      rows="4"
                                      placeholder="Briefly describe the purpose and content of the letter..."
                                      class="input-field no-icon resize-none"
                                      style="padding-left:14px">{{ old('description') }}</textarea>
                        </div>

                        {{-- Department (readonly) --}}
                        <div>
                            <p class="section-label">Department</p>
                            <div class="bg-gray-900/60 rounded-xl border border-gray-700/50 p-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-lg bg-gradient-to-br from-emerald-600 to-green-500 flex items-center justify-center flex-shrink-0">
                                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                        </svg>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        @if($departmentCode)
                                            <div class="flex items-center gap-2 flex-wrap">
                                                <span class="font-mono text-sm font-bold text-emerald-400 bg-emerald-500/10 px-2.5 py-1 rounded-lg border border-emerald-500/20">
                                                    {{ $departmentCode }}
                                                </span>
                                                @if($departmentName)
                                                    <span class="text-gray-400 text-xs">{{ $departmentName }}</span>
                                                @endif
                                            </div>
                                            <p class="text-gray-600 text-xs mt-1">Auto-fetched from HRMS · cannot be changed</p>
                                        @else
                                            <span class="text-red-400 text-sm font-medium">Not detected</span>
                                            <p class="text-red-400/60 text-xs mt-0.5">Please contact the administrator</p>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- ════ RIGHT COLUMN ════ --}}
                    <div class="space-y-6">

                        {{-- File Upload --}}
                        <div>
                            <div class="flex items-center justify-between mb-3">
                                <p class="section-label mb-0">Draft Letter File (PDF)</p>
                                <span class="text-red-400 text-xs font-semibold">Required *</span>
                            </div>

                            <input type="file"
                                   name="surat_file"
                                   id="surat_file_input"
                                   accept=".pdf,application/pdf"
                                   required
                                   class="hidden">

                            {{-- Dropzone --}}
                            <div id="upload-zone-wrapper">
                                <label for="surat_file_input" id="upload-zone" class="upload-zone">
                                    <div class="space-y-3">
                                        <div class="w-14 h-14 mx-auto rounded-2xl bg-gray-800 border border-gray-700/80 flex items-center justify-center">
                                            <svg class="w-7 h-7 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                                            </svg>
                                        </div>
                                        <div>
                                            <p class="text-sm text-gray-400">
                                                <span class="text-blue-400 font-semibold">Click to upload</span>
                                                <span class="text-gray-600"> or drag & drop</span>
                                            </p>
                                            <p class="text-xs text-gray-600 mt-1">PDF format only · Max. 10 MB</p>
                                        </div>
                                    </div>
                                </label>
                            </div>

                            {{-- File Preview Card --}}
                            <div id="file-preview-card"
                                 class="rounded-2xl border border-green-500/30 bg-gradient-to-br from-green-500/8 to-emerald-500/5 overflow-hidden">

                                <div class="flex items-center gap-2 px-4 pt-4 pb-3 border-b border-green-500/15">
                                    <div class="file-check-icon w-5 h-5 rounded-full bg-green-500 flex items-center justify-center flex-shrink-0 shadow-lg shadow-green-500/40">
                                        <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                                        </svg>
                                    </div>
                                    <span class="text-green-400 text-xs font-bold tracking-wide uppercase">File Ready to Submit</span>
                                    <div class="ml-auto">
                                        <span class="tag-badge bg-green-500/12 text-green-400 border border-green-500/25">✓ PDF</span>
                                    </div>
                                </div>

                                <div class="px-4 py-3 flex items-center gap-4">
                                    <div class="w-12 h-14 rounded-xl bg-red-500/12 border border-red-500/25 flex flex-col items-center justify-center flex-shrink-0 gap-0.5">
                                        <svg class="w-6 h-6 text-red-400" fill="currentColor" viewBox="0 0 24 24">
                                            <path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8l-6-6zm-1 1.5L18.5 9H13V3.5z"/>
                                        </svg>
                                        <span class="text-red-400 text-[9px] font-bold tracking-widest">PDF</span>
                                    </div>

                                    <div class="flex-1 min-w-0">
                                        <p id="file_name_display" class="text-sm font-semibold text-gray-100 truncate mb-1"></p>
                                        <div class="flex items-center gap-3 flex-wrap">
                                            <span class="inline-flex items-center gap-1 text-xs text-gray-400">
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                                                </svg>
                                                <span id="file_size_display" class="font-mono"></span>
                                            </span>
                                            <span class="inline-flex items-center gap-1 text-xs text-gray-500">
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                </svg>
                                                <span id="file_time_display"></span>
                                            </span>
                                        </div>
                                        <div class="mt-2.5 h-1 w-full bg-gray-700/60 rounded-full overflow-hidden">
                                            <div class="file-progress-bar h-full bg-gradient-to-r from-green-500 to-emerald-400 rounded-full"></div>
                                        </div>
                                    </div>

                                    <div class="flex flex-col gap-1.5 flex-shrink-0">
                                        <label for="surat_file_input"
                                               class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-medium text-blue-400 hover:text-white bg-blue-500/10 hover:bg-blue-500/20 border border-blue-500/20 hover:border-blue-400/40 cursor-pointer transition-all duration-150">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                                            </svg>
                                            Change
                                        </label>
                                        <button type="button"
                                                onclick="removeFile(event)"
                                                class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-medium text-gray-500 hover:text-red-400 bg-gray-700/30 hover:bg-red-500/10 border border-gray-700/40 hover:border-red-500/25 transition-all duration-150">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                            </svg>
                                            Remove
                                        </button>
                                    </div>
                                </div>
                            </div>

                            @error('surat_file')
                                <p class="mt-1.5 text-xs text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Workflow Steps --}}
                        <div>
                            <p class="section-label">Submission Workflow</p>
                            <div class="bg-gray-900/50 rounded-xl border border-gray-700/40 p-4 space-y-3">
                                @php
                                    $steps = [
                                        ['color' => 'bg-blue-400',   'label' => 'User fills out the form & uploads draft letter'],
                                        ['color' => 'bg-yellow-400', 'label' => 'Legal receives & reviews the submission'],
                                        ['color' => 'bg-orange-400', 'label' => 'Legal generates the official letter number'],
                                        ['color' => 'bg-purple-400', 'label' => 'User uploads the final letter (with number & signature)'],
                                        ['color' => 'bg-green-400',  'label' => 'Status changes to RELEASED'],
                                    ];
                                @endphp
                                @foreach($steps as $i => $step)
                                    <div class="flex items-start gap-3">
                                        <div class="flex flex-col items-center">
                                            <div class="step-dot {{ $step['color'] }} {{ $i === 0 ? 'ring-2 ring-offset-2 ring-offset-gray-900 ring-blue-400/50' : '' }}"></div>
                                            @if(!$loop->last)
                                                <div class="w-px h-4 bg-gray-700/60 mt-1"></div>
                                            @endif
                                        </div>
                                        <p class="text-xs {{ $i === 0 ? 'text-gray-300 font-medium' : 'text-gray-500' }} leading-none pt-0.5">
                                            {{ $step['label'] }}
                                        </p>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        {{-- Format Info (dinamis) --}}
                        <div class="bg-blue-500/5 rounded-xl border border-blue-500/15 p-4">
                            <div class="flex gap-3">
                                <svg class="w-4 h-4 text-blue-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <div class="w-full">
                                    <p class="text-blue-300 text-xs font-semibold mb-1">Letter Number Format</p>
                                    <p class="text-gray-500 text-xs font-mono leading-relaxed">
                                        [Seq] / [Dept] - <span id="fmt-company" class="text-gray-400 font-bold">???</span> / S / [Month] / [Year]
                                    </p>
                                    <p class="mt-2 text-gray-600 text-xs">Preview:</p>
                                    <code id="fmt-example"
                                          class="inline-block mt-0.5 text-xs font-mono bg-gray-900/80 text-gray-500 px-2.5 py-1 rounded-lg border border-gray-700/60 w-full">
                                        Select company to see preview
                                    </code>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Form Actions --}}
                <div class="flex items-center justify-end gap-3 pt-6 border-t border-gray-800/80">
                    <a href="{{ route('contracts.index') }}"
                       class="px-5 py-2.5 text-sm font-medium text-gray-400 hover:text-white border border-gray-700 hover:border-gray-500 rounded-xl bg-gray-800/40 hover:bg-gray-800 transition-all duration-200">
                        Cancel
                    </a>
                    <button type="submit"
                            id="submit-btn"
                            {{ $hasError ? 'disabled' : '' }}
                            class="btn-submit px-7 py-2.5 text-sm font-semibold text-white rounded-xl flex items-center gap-2 shadow-lg">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                        </svg>
                        Submit Outgoing Letter
                    </button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
    <script>
    document.addEventListener('DOMContentLoaded', function () {

        // ── Date defaults ──────────────────────────────────────
        const today   = new Date().toISOString().split('T')[0];
        const effDate = document.getElementById('effective_date');
        if (effDate) {
            if (!effDate.value) effDate.value = today;
            effDate.min = today;
        }

        // ── File upload elements ────────────────────────────────
        const fileInput         = document.getElementById('surat_file_input');
        const uploadZoneWrapper = document.getElementById('upload-zone-wrapper');
        const filePreviewCard   = document.getElementById('file-preview-card');
        const fileNameDisplay   = document.getElementById('file_name_display');
        const fileSizeDisplay   = document.getElementById('file_size_display');
        const fileTimeDisplay   = document.getElementById('file_time_display');

        // ── Format preview elements ────────────────────────────
        const companySelect = document.getElementById('company');
        const fmtCompany    = document.getElementById('fmt-company');
        const fmtExample    = document.getElementById('fmt-example');
        const deptCode      = '{{ $departmentCode ?: "DEPT" }}';
        const romanMonths   = ['I','II','III','IV','V','VI','VII','VIII','IX','X','XI','XII'];

        // ── Update format preview ──────────────────────────────
        function updateFormatPreview() {
            const company = companySelect?.value || '';
            if (!company) {
                if (fmtCompany) fmtCompany.textContent = '???';
                if (fmtExample) {
                    fmtExample.textContent = 'Select company to see preview';
                    fmtExample.classList.remove('has-value', 'text-cyan-400');
                    fmtExample.classList.add('text-gray-500');
                }
                return;
            }

            const now    = new Date();
            const roman  = romanMonths[now.getMonth()];
            const year   = now.getFullYear();
            const preview = `001/${deptCode}-${company}/S/${roman}/${year}`;

            if (fmtCompany) fmtCompany.textContent = company;
            if (fmtExample) {
                fmtExample.textContent = preview;
                fmtExample.classList.add('has-value', 'text-cyan-400');
                fmtExample.classList.remove('text-gray-500');
            }
        }

        if (companySelect) {
            companySelect.addEventListener('change', updateFormatPreview);
            // Jalankan saat load jika ada old() value
            updateFormatPreview();
        }

        // ── Format bytes ───────────────────────────────────────
        function formatBytes(bytes) {
            if (bytes < 1024)        return bytes + ' B';
            if (bytes < 1_048_576)   return (bytes / 1024).toFixed(1) + ' KB';
            return (bytes / 1_048_576).toFixed(2) + ' MB';
        }

        // ── Show file preview ──────────────────────────────────
        function showFile(file) {
            if (file.type !== 'application/pdf') {
                alert('Only PDF files are allowed!');
                fileInput.value = '';
                return;
            }
            if (file.size > 10 * 1024 * 1024) {
                alert('Maximum file size is 10 MB!');
                fileInput.value = '';
                return;
            }

            // Reset animasi agar re-trigger saat ganti file
            filePreviewCard.classList.remove('visible');
            void filePreviewCard.offsetWidth; // force reflow

            uploadZoneWrapper.classList.add('hidden-soft');

            if (fileNameDisplay) fileNameDisplay.textContent = file.name;
            if (fileSizeDisplay) fileSizeDisplay.textContent = formatBytes(file.size);
            if (fileTimeDisplay) {
                const d = new Date();
                fileTimeDisplay.textContent = 'Selected at ' + d.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
            }

            filePreviewCard.classList.add('visible');
        }

        // ── File input change ──────────────────────────────────
        if (fileInput) {
            fileInput.addEventListener('change', function (e) {
                const file = e.target.files[0];
                if (file) showFile(file);
            });
        }

        // ── Drag & drop ────────────────────────────────────────
        const uploadZone = document.getElementById('upload-zone');
        if (uploadZone) {
            uploadZone.addEventListener('dragover', function (e) {
                e.preventDefault();
                this.style.borderColor = 'rgba(96,165,250,.8)';
                this.style.background  = 'rgba(96,165,250,.07)';
            });
            uploadZone.addEventListener('dragleave', function () {
                this.style.borderColor = '';
                this.style.background  = '';
            });
            uploadZone.addEventListener('drop', function (e) {
                e.preventDefault();
                this.style.borderColor = '';
                this.style.background  = '';
                const file = e.dataTransfer.files[0];
                if (file) {
                    const dt = new DataTransfer();
                    dt.items.add(file);
                    fileInput.files = dt.files;
                    showFile(file);
                }
            });
        }

        // ── Auto-uppercase title ───────────────────────────────
        const titleInput = document.getElementById('title');
        if (titleInput) {
            titleInput.addEventListener('blur', function () {
                this.value = this.value.toUpperCase();
            });
        }

        // ── Form submit validation ─────────────────────────────
        const form = document.getElementById('surat-form');
        if (form) {
            form.addEventListener('submit', function (e) {

                @if($hasError)
                    e.preventDefault();
                    alert('Your account is not registered to any department in HRMS. Please contact the administrator.');
                    return;
                @endif

                // Validasi company wajib dipilih
                if (!companySelect || !companySelect.value) {
                    e.preventDefault();
                    alert('Please select a company (GNI or AMI) before submitting.');
                    companySelect?.focus();
                    return;
                }

                // Validasi file wajib ada
                if (!fileInput || fileInput.files.length === 0) {
                    e.preventDefault();
                    alert('Please upload a PDF file first!');
                    return;
                }

                // Konfirmasi submit
                if (!confirm('Submit this Outgoing Letter for Legal approval?')) {
                    e.preventDefault();
                }
            });
        }
    });

    // ── removeFile (global scope, dipanggil dari onclick) ──────
    window.removeFile = function (e) {
        if (e) e.preventDefault();

        const fileInput         = document.getElementById('surat_file_input');
        const uploadZoneWrapper = document.getElementById('upload-zone-wrapper');
        const filePreviewCard   = document.getElementById('file-preview-card');

        if (fileInput)         fileInput.value = '';
        if (filePreviewCard)   filePreviewCard.classList.remove('visible');
        if (uploadZoneWrapper) uploadZoneWrapper.classList.remove('hidden-soft');
    };
    </script>
    @endpush

</x-app-layout-dark>