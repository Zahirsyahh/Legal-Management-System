{{-- resources/views/surat/create.blade.php --}}
<x-app-layout-dark title="Create Surat Keluar">

    <style>
        /* ── Animations ── */
        @keyframes fadeInDown {
            from { opacity: 0; transform: translateY(-12px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(16px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        @keyframes scaleIn {
            from { opacity: 0; transform: scale(0.92); }
            to   { opacity: 1; transform: scale(1); }
        }
        @keyframes pulse-ring {
            0%   { box-shadow: 0 0 0 0 rgba(34,197,94,.45); }
            70%  { box-shadow: 0 0 0 10px rgba(34,197,94,0); }
            100% { box-shadow: 0 0 0 0 rgba(34,197,94,0); }
        }
        @keyframes checkPop {
            0%   { transform: scale(0) rotate(-15deg); opacity: 0; }
            60%  { transform: scale(1.2) rotate(5deg); opacity: 1; }
            100% { transform: scale(1) rotate(0deg); opacity: 1; }
        }
        @keyframes shimmer {
            0%   { background-position: -200% center; }
            100% { background-position: 200% center; }
        }
        @keyframes borderGlow {
            0%, 100% { border-color: rgba(34,197,94,.4); }
            50%       { border-color: rgba(34,197,94,.9); }
        }

        .animate-fade-in-down { animation: fadeInDown .5s ease both; }
        .animate-fade-in-up   { animation: fadeInUp  .55s ease both; }
        .animate-scale-in     { animation: scaleIn   .45s ease both; }

        /* ── Step dots in workflow ── */
        .step-dot {
            width: 8px; height: 8px;
            border-radius: 50%;
            flex-shrink: 0;
        }

        /* ── Upload zone ── */
        .upload-zone {
            border: 2px dashed rgba(107,114,128,.45);
            border-radius: 12px;
            padding: 24px 20px;
            text-align: center;
            cursor: pointer;
            display: block;
            width: 100%;
            transition: border-color .2s, background .2s;
        }
        .upload-zone:hover {
            border-color: rgba(96,165,250,.65);
            background: rgba(96,165,250,.04);
        }
        .upload-zone.has-file {
            border-color: rgba(34,197,94,.45);
            background: rgba(34,197,94,.03);
            cursor: pointer;
        }

        /* ── File result card ── */
        @keyframes slideDown {
            from { opacity: 0; transform: translateY(-8px); max-height: 0; }
            to   { opacity: 1; transform: translateY(0);   max-height: 80px; }
        }
        #file-result-card {
            display: none;
            overflow: hidden;
        }
        #file-result-card.show {
            display: flex;
            animation: slideDown .3s ease both;
        }

        /* ── Pulse ring on success icon ── */
        .pulse-green { animation: pulse-ring 1.5s ease-out 1; }

        /* ── Shimmer submit button ── */
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

        /* ── Glass card ── */
        .form-glass {
            background: rgba(17,24,39,.6);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255,255,255,.07);
            border-radius: 20px;
        }

        /* ── Input focus glow ── */
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

        /* ── Section headers inside card ── */
        .section-label {
            font-size: .7rem;
            font-weight: 700;
            letter-spacing: .1em;
            text-transform: uppercase;
            color: #6b7280;
            margin-bottom: 12px;
        }

        /* ── Tag badge ── */
        .tag-badge {
            display: inline-flex; align-items: center;
            padding: 3px 10px;
            border-radius: 20px;
            font-size: .72rem;
            font-weight: 600;
            letter-spacing: .03em;
        }
    </style>

    <div class="pb-12 px-4 sm:px-6 lg:px-8 max-w-6xl mx-auto">

        {{-- ──────────────────────────────────────────
             PHP: resolve department from HRMS
        ────────────────────────────────────────── --}}
        @php
            $user           = Auth::user();
            $hrmsUser       = \Illuminate\Support\Facades\DB::table('tbl_user')
                                ->where('email', $user->email)
                                ->first(['kode_department','nama_user']);
            $departmentCode = $hrmsUser ? strtoupper($hrmsUser->kode_department ?? '') : '';
            $departmentName = '';
            if ($departmentCode) {
                $dept = \Illuminate\Support\Facades\DB::table('tbl_department')
                            ->where('kode_pendek', $departmentCode)
                            ->first(['nama_departemen']);
                $departmentName = $dept->nama_departemen ?? $departmentCode;
            }
            $hasError     = empty($departmentCode);
            $errorMessage = $hasError ? 'Akun Anda tidak terhubung ke departemen manapun di HRMS. Hubungi administrator.' : '';
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
                        <h1 class="text-2xl md:text-3xl font-bold text-white tracking-tight">Create Surat Keluar</h1>
                    </div>
                    <p class="text-gray-500 text-sm ml-13 pl-0.5">Form pengajuan penomoran surat keluar · File PDF wajib diupload</p>
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

        {{-- ── GLOBAL ALERTS ── --}}
        @if($hasError)
            <div class="mb-5 p-4 bg-yellow-500/10 border border-yellow-500/25 rounded-2xl animate-fade-in-down flex gap-3">
                <svg class="w-5 h-5 text-yellow-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.998-.833-2.732 0L4.732 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                </svg>
                <div>
                    <p class="text-yellow-400 font-semibold text-sm mb-0.5">Department tidak terdeteksi</p>
                    <p class="text-yellow-300/80 text-xs">{{ $errorMessage }}</p>
                    <p class="text-yellow-500/60 text-xs mt-1">Form tetap bisa diisi, namun tidak bisa di-submit hingga masalah ini diselesaikan.</p>
                </div>
            </div>
        @endif

        @if($errors->any())
            <div class="mb-5 p-4 bg-red-500/10 border border-red-500/25 rounded-2xl animate-fade-in-down">
                <div class="flex items-center gap-2 text-red-400 mb-2 text-sm font-semibold">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Terdapat kesalahan:
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

        {{-- ── MAIN FORM CARD ── --}}
        <div class="form-glass p-6 md:p-8 animate-scale-in">

            <form action="{{ route('surat.store') }}" method="POST" enctype="multipart/form-data"
                  class="space-y-8" id="surat-form">
                @csrf
                <input type="hidden" name="contract_type" value="surat">
                <input type="hidden" name="status" value="draft">
                <input type="hidden" name="department_code" value="{{ $departmentCode }}">

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 lg:gap-10">

                    {{-- ════════════════════════
                         LEFT COLUMN
                    ════════════════════════ --}}
                    <div class="space-y-6">

                        {{-- Letter number (auto) --}}
                        <div>
                            <p class="section-label">Nomor Surat</p>
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
                                        <span class="text-yellow-400 text-xs">Menunggu persetujuan Legal</span>
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
                                       placeholder="Surat Tugas, Surat Pernyataan, dll."
                                       class="input-field @error('title') error @enderror">
                            </div>
                            @error('title')
                                <p class="mt-1.5 text-xs text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Date --}}
                        <div>
                            <label for="effective_date" class="block text-sm font-medium text-gray-300 mb-2">
                                Tanggal Surat <span class="text-red-400 ml-0.5">*</span>
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
                        </div>

                        {{-- Description --}}
                        <div>
                            <label for="description" class="block text-sm font-medium text-gray-300 mb-2">
                                Deskripsi
                                <span class="text-gray-600 font-normal text-xs ml-1">(opsional)</span>
                            </label>
                            <textarea name="description"
                                      id="description"
                                      rows="4"
                                      placeholder="Jelaskan secara singkat tujuan dan isi surat..."
                                      class="input-field no-icon resize-none" style="padding-left:14px">{{ old('description') }}</textarea>
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
                                            <p class="text-gray-600 text-xs mt-1">Diambil otomatis dari HRMS · tidak dapat diubah</p>
                                        @else
                                            <span class="text-red-400 text-sm font-medium">Tidak terdeteksi</span>
                                            <p class="text-red-400/60 text-xs mt-0.5">Hubungi administrator</p>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- ════════════════════════
                         RIGHT COLUMN
                    ════════════════════════ --}}
                    <div class="space-y-6">

                        {{-- ── FILE UPLOAD SECTION ── --}}
                        <div>
                            <div class="flex items-center justify-between mb-3">
                                <p class="section-label mb-0">File Draft Surat (PDF)</p>
                                <span class="text-red-400 text-xs font-semibold">Wajib *</span>
                            </div>

                            {{-- ── DROPZONE ── --}}
                            <label for="surat_file_input" id="upload-zone" class="upload-zone">
                                <input type="file"
                                       name="surat_file"
                                       id="surat_file_input"
                                       accept=".pdf,application/pdf"
                                       required
                                       class="hidden">

                                <div id="upload_placeholder" class="space-y-2">
                                    <div class="w-12 h-12 mx-auto rounded-xl bg-gray-800 border border-gray-700 flex items-center justify-center">
                                        <svg class="w-6 h-6 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                                        </svg>
                                    </div>
                                    <p class="text-sm text-gray-400">
                                        <span class="text-blue-400 font-semibold">Klik untuk upload</span>
                                        <span class="text-gray-600"> atau drag & drop</span>
                                    </p>
                                    <p class="text-xs text-gray-600">Format PDF · Maks. 10 MB</p>
                                </div>

                                {{-- icon ganti ke checkmark saat file dipilih --}}
                                <div id="upload_selected" class="hidden space-y-2">
                                    <div class="w-12 h-12 mx-auto rounded-xl bg-green-500/15 border border-green-500/30 flex items-center justify-center">
                                        <svg class="w-6 h-6 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                        </svg>
                                    </div>
                                    <p class="text-xs text-green-400 font-semibold">File terpilih · klik untuk ganti</p>
                                </div>
                            </label>

                            {{-- ── FILE RESULT CARD (muncul setelah file dipilih) ── --}}
                            <div id="file-result-card"
                                 class="mt-3 items-center gap-3 px-4 py-3 bg-gray-800/80 border border-gray-700 rounded-xl">

                                {{-- PDF icon --}}
                                <div class="w-9 h-9 rounded-lg bg-red-500/15 border border-red-500/25 flex items-center justify-center flex-shrink-0">
                                    <svg class="w-5 h-5 text-red-400" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8l-6-6zm-1 1.5L18.5 9H13V3.5zM8.5 17.5c-.28 0-.5-.22-.5-.5s.22-.5.5-.5.5.22.5.5-.22.5-.5.5zm0-3c-.83 0-1.5.67-1.5 1.5S7.67 17.5 8.5 17.5 10 16.83 10 16s-.67-1.5-1.5-1.5zm4.5 2.5h-1.5V14H11v3h1.5v1H11v.5h3V17zm3-2.5h-1v4h1v-1.5h.5c.83 0 1.5-.67 1.5-1.5S16.83 14 16 14zm0 2h-.5v-1H16c.28 0 .5.22.5.5s-.22.5-.5.5z"/>
                                    </svg>
                                </div>

                                {{-- Filename & size --}}
                                <div class="flex-1 min-w-0">
                                    <p id="file_name_display"
                                       class="text-sm font-semibold text-gray-200 truncate"></p>
                                    <p class="text-xs text-gray-500 mt-0.5">
                                        <span id="file_size_display"></span>
                                        <span class="mx-1.5 text-gray-700">·</span>
                                        <span class="text-green-400 font-medium">✓ Siap diupload</span>
                                    </p>
                                </div>

                                {{-- Remove button --}}
                                <button type="button"
                                        onclick="removeFile(event)"
                                        title="Hapus file"
                                        class="flex-shrink-0 w-7 h-7 flex items-center justify-center rounded-lg text-gray-600 hover:text-red-400 hover:bg-red-500/10 transition-all duration-150">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                </button>
                            </div>

                            @error('surat_file')
                                <p class="mt-1.5 text-xs text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- ── WORKFLOW STEPS ── --}}
                        <div>
                            <p class="section-label">Alur Pengajuan</p>
                            <div class="bg-gray-900/50 rounded-xl border border-gray-700/40 p-4 space-y-3">
                                @php
                                    $steps = [
                                        ['color'=>'bg-blue-400',   'label'=>'User isi form & upload draft surat'],
                                        ['color'=>'bg-yellow-400', 'label'=>'Legal menerima & melakukan review'],
                                        ['color'=>'bg-orange-400', 'label'=>'Legal generate nomor surat resmi'],
                                        ['color'=>'bg-purple-400', 'label'=>'User upload surat final (nomor + ttd)'],
                                        ['color'=>'bg-green-400',  'label'=>'Status berubah menjadi EXECUTED'],
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
                                        <p class="text-xs text-gray-{{ $i === 0 ? '300 font-medium' : '500' }} leading-none pt-0.5">
                                            {{ $step['label'] }}
                                        </p>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        {{-- ── FORMAT INFO ── --}}
                        <div class="bg-blue-500/5 rounded-xl border border-blue-500/15 p-4">
                            <div class="flex gap-3">
                                <svg class="w-4 h-4 text-blue-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <div>
                                    <p class="text-blue-300 text-xs font-semibold mb-1">Format Nomor Surat</p>
                                    <p class="text-gray-500 text-xs font-mono leading-relaxed">
                                        [Seq] / [Kode Dept] / GNI / S / [Bulan Romawi] / [Tahun]
                                    </p>
                                    <p class="mt-2 text-gray-600 text-xs">Contoh:</p>
                                    <code class="inline-block mt-0.5 text-xs font-mono bg-gray-900/80 text-cyan-400 px-2.5 py-1 rounded-lg border border-gray-700/60">
                                        001/ITS/GNI/S/II/2026
                                    </code>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ── FORM ACTIONS ── --}}
                <div class="flex items-center justify-end gap-3 pt-6 border-t border-gray-800/80">
                    <a href="{{ route('contracts.index') }}"
                       class="px-5 py-2.5 text-sm font-medium text-gray-400 hover:text-white border border-gray-700 hover:border-gray-500 rounded-xl bg-gray-800/40 hover:bg-gray-800 transition-all duration-200">
                        Batal
                    </a>
                    <button type="submit"
                            id="submit-btn"
                            {{ $hasError ? 'disabled' : '' }}
                            class="btn-submit px-7 py-2.5 text-sm font-semibold text-white rounded-xl flex items-center gap-2 shadow-lg">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                        </svg>
                        Submit Surat Keluar
                    </button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
    <script>
    document.addEventListener('DOMContentLoaded', function () {

        // ── Date defaults ──────────────────────────────
        const today = new Date().toISOString().split('T')[0];
        const effDate = document.getElementById('effective_date');
        if (effDate) { effDate.value = effDate.value || today; effDate.min = today; }

        // ── Elements ───────────────────────────────────
        const fileInput         = document.getElementById('surat_file_input');
        const uploadZone        = document.getElementById('upload-zone');
        const uploadPlaceholder = document.getElementById('upload_placeholder');
        const uploadSelected    = document.getElementById('upload_selected');
        const fileResultCard    = document.getElementById('file-result-card');
        const fileNameDisplay   = document.getElementById('file_name_display');
        const fileSizeDisplay   = document.getElementById('file_size_display');

        // ── Helper ─────────────────────────────────────
        function formatBytes(bytes) {
            if (bytes < 1024)        return bytes + ' B';
            if (bytes < 1048576)     return (bytes / 1024).toFixed(1) + ' KB';
            return (bytes / 1048576).toFixed(2) + ' MB';
        }

        function showFile(file) {
            if (file.type !== 'application/pdf') {
                alert('Hanya file PDF yang diperbolehkan!');
                fileInput.value = '';
                return;
            }
            if (file.size > 10 * 1024 * 1024) {
                alert('Ukuran file maksimal 10 MB!');
                fileInput.value = '';
                return;
            }

            // Dropzone: ganti ke state "selected"
            uploadZone.classList.add('has-file');
            uploadPlaceholder.classList.add('hidden');
            uploadSelected.classList.remove('hidden');

            // Tampilkan file result card
            fileNameDisplay.textContent = file.name;
            fileSizeDisplay.textContent = formatBytes(file.size);
            fileResultCard.classList.add('show');
        }

        // ── File input change ──────────────────────────
        if (fileInput) {
            fileInput.addEventListener('change', function (e) {
                const file = e.target.files[0];
                if (file) showFile(file);
            });
        }

        // ── Drag & drop ────────────────────────────────
        if (uploadZone) {
            uploadZone.addEventListener('dragover', function (e) {
                e.preventDefault();
                this.style.borderColor = 'rgba(96,165,250,.7)';
            });
            uploadZone.addEventListener('dragleave', function () {
                this.style.borderColor = '';
            });
            uploadZone.addEventListener('drop', function (e) {
                e.preventDefault();
                this.style.borderColor = '';
                const file = e.dataTransfer.files[0];
                if (file) {
                    const dt = new DataTransfer();
                    dt.items.add(file);
                    fileInput.files = dt.files;
                    showFile(file);
                }
            });
        }

        // ── Auto-uppercase title ───────────────────────
        const titleInput = document.getElementById('title');
        if (titleInput) {
            titleInput.addEventListener('blur', function () {
                this.value = this.value.toUpperCase();
            });
        }

        // ── Form submit validation ─────────────────────
        const form = document.getElementById('surat-form');
        if (form) {
            form.addEventListener('submit', function (e) {
                @if($hasError)
                    e.preventDefault();
                    alert('Anda belum terdaftar pada departemen di HRMS. Hubungi administrator.');
                    return;
                @endif
                if (!fileInput || fileInput.files.length === 0) {
                    e.preventDefault();
                    alert('Silakan upload file PDF terlebih dahulu!');
                    return;
                }
                if (!confirm('Submit Surat Keluar untuk persetujuan Legal?')) {
                    e.preventDefault();
                }
            });
        }
    });

    // ── removeFile (global) ────────────────────────────
    window.removeFile = function (e) {
        if (e) e.preventDefault();

        const fileInput         = document.getElementById('surat_file_input');
        const uploadZone        = document.getElementById('upload-zone');
        const uploadPlaceholder = document.getElementById('upload_placeholder');
        const uploadSelected    = document.getElementById('upload_selected');
        const fileResultCard    = document.getElementById('file-result-card');

        if (fileInput)         fileInput.value = '';
        if (uploadZone)        { uploadZone.classList.remove('has-file'); uploadZone.style.borderColor = ''; }
        if (uploadPlaceholder) uploadPlaceholder.classList.remove('hidden');
        if (uploadSelected)    uploadSelected.classList.add('hidden');
        if (fileResultCard)    fileResultCard.classList.remove('show');
    };
    </script>
    @endpush

</x-app-layout-dark>