{{-- resources/views/contracts/start-review-dynamic.blade.php --}}
<x-app-layout-dark title="Setup Review Workflow — {{ $contract->title }}">

<style>
/* ─── Shared Base ─────────────────────────────────── */
.glass-card { 
    background: rgba(255, 255, 255, .03); 
    backdrop-filter: blur(12px); 
    border: 1px solid rgba(255, 255, 255, .08); 
    box-shadow: 0 4px 24px -1px rgba(0, 0, 0, 0.2);
}
.input-field { 
    background: rgba(15, 23, 42, 0.6); 
    border: 1px solid rgba(255, 255, 255, .1); 
    transition: all .2s cubic-bezier(0.4, 0, 0.2, 1); 
    color: #e2e8f0;
}
.input-field:focus { 
    border-color: #3b82f6; 
    box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.15); 
    outline: none; 
}
.select-field { 
    appearance: none; 
    background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%2394a3b8' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e"); 
    background-position: right .75rem center; 
    background-repeat: no-repeat; 
    background-size: 1.2em; 
}

/* ─── Workflow Builder ────────────────────────────── */
.wb-seq { 
    background: rgba(30, 41, 59, 0.4); 
    border: 1px solid rgba(255, 255, 255, .05); 
    border-radius: 1rem; 
    padding: 1.25rem; 
    transition: border-color 0.2s;
}
.wb-seq:hover { border-color: rgba(59, 130, 246, 0.4); }
.wb-seq.dragging { opacity: .4; transform: scale(0.96); }
.wb-seq.drag-over { border-color: #3b82f6 !important; background: rgba(59, 130, 246, 0.05); }

.wb-par { 
    border: 2px dashed rgba(168, 85, 247, 0.3); 
    border-radius: 1rem; 
    background: rgba(88, 28, 135, 0.04); 
    padding: 1.25rem; 
}
.wb-par.drag-over { border-color: #a855f7 !important; background: rgba(168, 85, 247, 0.08); }

.dept-card { 
    border: 1px solid rgba(255, 255, 255, .08); 
    border-radius: 0.75rem; 
    background: rgba(15, 23, 42, 0.4); 
    padding: 0.75rem; 
    cursor: pointer; 
    transition: all 0.2s; 
}
.dept-card:hover { background: rgba(255, 255, 255, 0.05); border-color: rgba(168, 85, 247, 0.4); }
.dept-card.selected { border-color: #a855f7 !important; background: rgba(168, 85, 247, 0.15) !important; box-shadow: 0 0 15px rgba(168, 85, 247, 0.1); }

.drag-handle { cursor: grab; color: rgba(255, 255, 255, .2); padding: 0.5rem; border-radius: 0.375rem; transition: none; }
.drag-handle:hover { background: rgba(255, 255, 255, 0.05); color: #60a5fa; }

.seq-num { 
    width: 32px; height: 32px; border-radius: 10px; 
    background: linear-gradient(135deg, #3b82f6, #2563eb); 
    color: #fff; font-weight: 700; display: flex; align-items: center; justify-content: center; 
}

/* ─── Buttons ────────────────────────────────────── */
.btn-secondary-action {
    display: flex; align-items: center; gap: 0.5rem; padding: 0.5rem 1rem;
    font-size: 0.813rem; font-weight: 600; border-radius: 0.5rem; transition: all 0.2s;
}
.btn-add-seq { border: 1px solid rgba(59, 130, 246, 0.4); background: rgba(59, 130, 246, 0.05); color: #60a5fa; }
.btn-add-seq:hover { background: rgba(59, 130, 246, 0.15); transform: translateY(-1px); }

.btn-add-par { border: 1px solid rgba(168, 85, 247, 0.4); background: rgba(168, 85, 247, 0.05); color: #c084fc; }
.btn-add-par:hover:not(:disabled) { background: rgba(168, 85, 247, 0.15); transform: translateY(-1px); }

.btn-remove { color: rgba(248, 113, 113, 0.5); transition: color 0.2s; }
.btn-remove:hover { color: #f87171; }

.btn-submit { 
    background: #10b981; color: #fff; border-radius: 0.75rem; 
    padding: 0.75rem 2rem; font-weight: 600; transition: all 0.2s;
    box-shadow: 0 4px 12px rgba(16, 185, 129, 0.2);
}
.btn-submit:hover { background: #059669; transform: translateY(-1px); box-shadow: 0 6px 16px rgba(16, 185, 129, 0.3); }

.checkbox-wrapper {
    display: flex; align-items: center; gap: 0.5rem; cursor: pointer;
    font-size: 0.75rem; font-weight: 500; color: #c084fc;
    padding: 0.25rem 0.5rem; border-radius: 0.5rem;
    background: rgba(168, 85, 247, 0.05); transition: all 0.2s;
}
.checkbox-wrapper:hover { background: rgba(168, 85, 247, 0.12); }
.checkbox-wrapper input[type="checkbox"] {
    width: 1rem; height: 1rem; border-radius: 0.25rem;
    border: 1.5px solid rgba(168, 85, 247, 0.5);
    background: rgba(15, 23, 42, 0.6); cursor: pointer;
    appearance: none; -webkit-appearance: none; position: relative; transition: all 0.2s;
}
.checkbox-wrapper input[type="checkbox"]:checked { background: #a855f7; border-color: #a855f7; }
.checkbox-wrapper input[type="checkbox"]:checked::after {
    content: '✓'; position: absolute; color: white; font-size: 0.7rem;
    left: 50%; top: 50%; transform: translate(-50%, -50%);
}

/* ─── Synology Folder Selector ───────────────────── */
.synology-box {
    background: rgba(15, 23, 42, 0.5);
    border: 1px solid rgba(255, 255, 255, 0.08);
    border-radius: 0.75rem;
    padding: 1rem;
    transition: border-color 0.2s;
}
.synology-box.has-value {
    border-color: rgba(16, 185, 129, 0.35);
    background: rgba(16, 185, 129, 0.04);
}
.synology-box.auto-detected {
    border-color: rgba(59, 130, 246, 0.35);
    background: rgba(59, 130, 246, 0.04);
}

.synology-dropdown {
    display: none;
    position: absolute;
    top: calc(100% + 6px);
    left: 0; right: 0;
    z-index: 50;
    background: rgba(15, 23, 42, 0.98);
    border: 1px solid rgba(255,255,255,0.1);
    border-radius: 0.75rem;
    box-shadow: 0 20px 40px rgba(0,0,0,0.4);
    backdrop-filter: blur(12px);
    overflow: hidden;
}
.synology-dropdown.open { display: block; }

.synology-option {
    display: flex; align-items: center; justify-content: space-between;
    padding: 0.65rem 1rem; cursor: pointer;
    border-bottom: 1px solid rgba(255,255,255,0.04);
    transition: background 0.15s;
}
.synology-option:last-child { border-bottom: none; }
.synology-option:hover { background: rgba(59, 130, 246, 0.12); }
.synology-option.active { background: rgba(16, 185, 129, 0.12); }

@keyframes slideUp { from { opacity: 0; transform: translateY(12px); } to { opacity: 1; transform: translateY(0); } }
.animate-slide-up { animation: slideUp 0.3s ease-out forwards; }
</style>

<div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-10">

    {{-- Header --}}
    <div class="mb-10">
        <a href="{{ route('contracts.show', $contract) }}"
           class="inline-flex items-center gap-2 text-sm text-gray-500 hover:text-white mb-6 transition-colors group">
            <svg class="w-4 h-4 transition-transform group-hover:-translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Back to Contract
        </a>
        <h1 class="text-3xl font-extrabold text-white tracking-tight">Setup Review Workflow</h1>
        <p class="text-gray-400 mt-2">Define the approval sequence — drag and drop to reorder stages.</p>
    </div>

    {{-- Contract Summary --}}
    <div class="glass-card rounded-2xl p-6 mb-8 grid grid-cols-2 md:grid-cols-4 gap-6">
        <div><p class="text-gray-500 text-xs uppercase tracking-wider font-semibold mb-1">Title</p><p class="text-gray-200 font-medium truncate" title="{{ $contract->title }}">{{ $contract->title }}</p></div>
        <div><p class="text-gray-500 text-xs uppercase tracking-wider font-semibold mb-1">Counterparty</p><p class="text-gray-200 font-medium truncate">{{ $contract->counterparty_name }}</p></div>
        <div><p class="text-gray-500 text-xs uppercase tracking-wider font-semibold mb-1">Value</p><p class="text-gray-200 font-medium">{{ $contract->contract_value ? number_format($contract->contract_value, 0, '.', ',') . ' ' . ($contract->currency ?? 'IDR') : '—' }}</p></div>
        <div><p class="text-gray-500 text-xs uppercase tracking-wider font-semibold mb-1">Type</p><p class="text-gray-200 font-medium capitalize">{{ $contract->contract_type ?? '—' }}</p></div>
    </div>

    {{-- Legend --}}
    <div class="flex flex-wrap gap-6 mb-8 px-2">
        <div class="flex items-center gap-2.5 text-xs text-gray-400">
            <span class="w-2.5 h-2.5 rounded-full bg-blue-500"></span>
            Legal Stage (Sequential)
        </div>
        <div class="flex items-center gap-2.5 text-xs text-gray-400">
            <span class="w-2.5 h-2.5 rounded-full bg-purple-500"></span>
            Substantial Review (Parallel)
        </div>
    </div>

    <form id="wfSetupForm" method="POST" action="{{ route('contracts.process-start-review-dynamic', $contract) }}">
        @csrf
        <input type="hidden" name="workflow_items" id="workflowItemsInput">
        {{-- ✅ Simpan format: path||link --}}
        <input type="hidden" name="synology_folder_path" id="synologyHidden"
               value="{{ $synologyAutoFill['db_value'] ?? '' }}">

        {{-- Canvas --}}
        <div class="glass-card rounded-2xl p-6 mb-6">
            <div class="flex items-center justify-between mb-8">
                <div>
                    <h2 class="text-lg font-bold text-white">Workflow Steps</h2>
                    <p class="text-xs text-gray-500 mt-1"><span class="text-blue-400 font-semibold">User Submission</span> is always the first step.</p>
                </div>
                <div class="flex gap-3">
                    <button type="button" class="btn-secondary-action btn-add-seq" onclick="addLegalStage()">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                        Add Legal Stage
                    </button>
                    <button type="button" class="btn-secondary-action btn-add-par" id="addParBtn" onclick="addParallelBlock()">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                        Add Substantial Review
                    </button>
                </div>
            </div>

            <div class="wb-seq mb-3 border-gray-700/40 bg-gray-800/10 opacity-80">
                <div class="flex items-center gap-4">
                    <div class="seq-num bg-gray-700 text-gray-400">1</div>
                    <div>
                        <p class="text-sm font-bold text-gray-300">User Submission</p>
                        <p class="text-xs text-gray-500">{{ $contract->user?->nama_user ?? 'Document Owner' }} — System Generated</p>
                    </div>
                    <span class="ml-auto text-[10px] font-bold uppercase tracking-widest px-2.5 py-1 rounded-md bg-emerald-500/10 border border-emerald-500/20 text-emerald-500">Completed</span>
                </div>
            </div>

            <div id="wbCanvas" class="space-y-3"></div>
        </div>

        {{-- File Directory & Review Summary --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-10">

            {{-- ✅ File Directory — Auto-fill + Dropdown --}}
            <div class="glass-card rounded-2xl p-6">
                <h3 class="text-sm font-bold text-gray-300 mb-1 flex items-center gap-2">
                    <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/>
                    </svg>
                    File Directory — Synology
                </h3>

                {{-- Info department owner --}}
                @if($synologyAutoFill['dept_label'])
                @else
                <p class="text-xs text-amber-400/80 mb-4">
                    ⚠ Department user not found in mapping — select folder manually.
                </p>
                @endif

                {{-- ✅ Synology Selector --}}
                <div class="relative" id="synologySelector">

                    {{-- Display box — klik untuk buka dropdown --}}
                    <div id="synologyDisplay"
                         class="synology-box {{ $synologyAutoFill['path'] ? ($synologyAutoFill['dept_label'] ? 'auto-detected' : 'has-value') : '' }} cursor-pointer"
                         onclick="toggleSynologyDropdown()">

                        @if($synologyAutoFill['path'])
                        {{-- Ada nilai --}}
                        <div class="flex items-start justify-between gap-3">
                            <div class="flex-1 min-w-0">
                                {{-- PATH --}}
                                <div class="flex items-center gap-2 mb-2">
                                    <span class="text-[10px] uppercase tracking-widest font-bold text-gray-500 flex-shrink-0">Path</span>
                                    <span class="text-xs font-mono text-emerald-300 truncate"
                                          id="displayPath">{{ $synologyAutoFill['path'] }}</span>
                                </div>
                                {{-- LINK --}}
                                @if($synologyAutoFill['link'])
                                <div class="flex items-center gap-2">
                                    <span class="text-[10px] uppercase tracking-widest font-bold text-gray-500 flex-shrink-0">Link</span>
                                    <a href="{{ $synologyAutoFill['link'] }}" target="_blank"
                                       onclick="event.stopPropagation()"
                                       class="text-xs text-blue-400 hover:text-blue-300 truncate underline underline-offset-2"
                                       id="displayLink">{{ $synologyAutoFill['link'] }}</a>
                                </div>
                                @endif
                            </div>
                            {{-- Edit icon --}}
                            <div class="flex-shrink-0 p-1.5 rounded-lg bg-white/5 hover:bg-white/10 transition-colors mt-0.5">
                                <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                </svg>
                            </div>
                        </div>
                        @else
                        {{-- Belum ada nilai --}}
                        <div class="flex items-center gap-3 text-gray-500">
                            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/>
                            </svg>
                            <span class="text-sm">Click to select Synology folder...</span>
                            <svg class="w-4 h-4 ml-auto flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </div>
                        @endif
                    </div>

                    {{-- ✅ Dropdown pilihan folder --}}
                    <div id="synologyDropdown" class="synology-dropdown">
                        <div class="px-3 py-2 border-b border-white/5">
                            <p class="text-[10px] uppercase tracking-widest font-bold text-gray-500">Select Synology Folder</p>
                        </div>

                        {{-- Option: Kosongkan --}}
                        <div class="synology-option" onclick="selectSynologyFolder(null, null, null)">
                            <div>
                                <p class="text-xs font-medium text-gray-400 italic">— None / Fill in manually later</p>
                            </div>
                        </div>

                        {{-- Options dari SynologyMapper::allOptions() --}}
                        @foreach($synologyOptions as $opt)
                        <div class="synology-option {{ ($synologyAutoFill['path'] ?? '') === $opt['path'] ? 'active' : '' }}"
                             onclick="selectSynologyFolder('{{ $opt['path'] }}', '{{ $opt['link'] }}', '{{ $opt['value'] }}')">
                            <div class="flex-1 min-w-0">
                                <p class="text-xs font-semibold text-gray-200">{{ $opt['folder'] }}</p>
                                <p class="text-[10px] text-gray-500 truncate font-mono mt-0.5">{{ $opt['path'] }}</p>
                            </div>
                            <div class="flex-shrink-0 ml-3">
                                <svg class="w-3.5 h-3.5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                                </svg>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>

                {{-- Badge status --}}
                <div class="mt-3 flex items-center gap-2">
                    @if($synologyAutoFill['dept_label'])
                    <span class="text-[10px] px-2 py-0.5 rounded-full bg-blue-500/15 text-blue-400 border border-blue-500/20 font-medium">
                        🎯 Auto-detected · {{ $synologyAutoFill['dept_label'] }}
                    </span>
                    @endif
                    <span class="text-[10px] text-gray-600">Click the box above to change</span>
                </div>
            </div>

            {{-- Review Summary --}}
            <div class="glass-card rounded-2xl p-6">
                <h2 class="text-lg font-bold text-gray-300 mb-4">Review Summary</h2>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="bg-gray-800/30 rounded-lg p-3">
                        <div class="flex items-center gap-2 mb-2">
                            <div class="p-1.5 rounded-lg bg-gradient-to-br from-blue-500/10 to-blue-600/10">
                                <svg class="w-4 h-4 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                </svg>
                            </div>
                            <div>
                                <p class="text-xs text-gray-400">Legal Stages</p>
                                <p class="text-xl font-bold text-gray-300" id="stageCount">1</p>
                            </div>
                        </div>
                        <p class="text-[10px] text-gray-500">Sequential review stages</p>
                    </div>

                    <div class="bg-gray-800/30 rounded-lg p-3">
                        <div class="flex items-center gap-2 mb-2">
                            <div class="p-1.5 rounded-lg bg-gradient-to-br from-purple-500/10 to-pink-600/10">
                                <svg class="w-4 h-4 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                                </svg>
                            </div>
                            <div>
                                <p class="text-xs text-gray-400">Departments</p>
                                <p class="text-xl font-bold text-gray-300" id="deptCount">0</p>
                            </div>
                        </div>
                        <p class="text-[10px] text-gray-500">For parallel review</p>
                    </div>
                </div>

                <div class="mt-3 pt-3 border-t border-white/5 space-y-2">
                    {{-- Owner dept info --}}
                    <div class="flex items-center justify-between text-xs">
                        <span class="text-gray-500">Submitted by:</span>
                        <span class="text-gray-300 font-medium">
                            {{ $contract->user?->nama_user ?? '—' }}
                            @if($synologyAutoFill['dept_label'])
                                <span class="text-blue-400">({{ $synologyAutoFill['dept_label'] }})</span>
                            @endif
                        </span>
                    </div>
                    {{-- Synology status --}}
                    <div class="flex items-center justify-between text-xs">
                        <span class="text-gray-500">Synology Folder:</span>
                        <span class="font-mono text-[10px] truncate max-w-[160px]" id="synologyStatusText">
                            {{ $synologyAutoFill['path'] ? basename($synologyAutoFill['path']) : 'Not Set' }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Footer --}}
        <div class="flex flex-col sm:flex-row items-center justify-between gap-6 border-t border-white/5 pt-8">
            <div id="wfSummary" class="text-sm font-medium"></div>
            <div class="flex gap-4 w-full sm:w-auto">
                <a href="{{ route('contracts.show', $contract) }}"
                   class="flex-1 sm:flex-none px-8 py-3 bg-white/5 hover:bg-white/10 rounded-xl text-sm font-bold text-gray-300 transition-all text-center">
                    Cancel
                </a>
                <button type="submit" id="submitBtn" class="btn-submit flex-1 sm:flex-none min-w-[200px]">
                    <span class="flex items-center justify-center gap-2">
                        Start Workflow
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                    </span>
                </button>
            </div>
        </div>
    </form>
</div>

@php
$legalOfficersJson = $legalOfficers->map(fn($u) => [
    'id'   => $u->id_user,
    'name' => $u->nama_user,
])->values();
@endphp

<script>
const LEGAL_OFFICERS = @json($legalOfficersJson);
const DEPT_OPTIONS = [
    { code:'FIN',   label:'Finance',    icon:'💹' },
    { code:'ACC',   label:'Accounting', icon:'📊' },
    { code:'TAX',   label:'Tax',        icon:'🏛️' },
    { code:'LEGAL', label:'Legal',      icon:'⚖️'  },
];

let items        = [];
let parallelExists = false;
let dragSrc      = null;

document.addEventListener('DOMContentLoaded', () => {
    addLegalStage(false);
    render();
    document.getElementById('wfSetupForm').addEventListener('submit', onSubmit);

    // Tutup dropdown kalau klik di luar
    document.addEventListener('click', (e) => {
        const selector = document.getElementById('synologySelector');
        if (selector && !selector.contains(e.target)) {
            closeSynologyDropdown();
        }
    });
});

// ─────────────────────────────────────────────────────────────
// SYNOLOGY FOLDER SELECTOR
// ─────────────────────────────────────────────────────────────
function toggleSynologyDropdown() {
    const dd = document.getElementById('synologyDropdown');
    dd.classList.toggle('open');
}

function closeSynologyDropdown() {
    document.getElementById('synologyDropdown')?.classList.remove('open');
}

/**
 * Dipanggil saat user memilih folder dari dropdown.
 * path   : "/SHARE DATA LEGAL/LEGAL-FINANCE"
 * link   : "https://gofile.me/..."
 * dbValue: "path||link"  — null jika pilih "tidak ada"
 */
function selectSynologyFolder(path, link, dbValue) {
    // Simpan ke hidden input
    document.getElementById('synologyHidden').value = dbValue ?? '';

    // Update display box
    const display = document.getElementById('synologyDisplay');

    if (path) {
        display.className = 'synology-box has-value cursor-pointer';
        display.innerHTML = `
            <div class="flex items-start justify-between gap-3">
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2 mb-2">
                        <span class="text-[10px] uppercase tracking-widest font-bold text-gray-500 flex-shrink-0">Path</span>
                        <span class="text-xs font-mono text-emerald-300 truncate" id="displayPath">${escHtml(path)}</span>
                    </div>
                    ${link ? `
                    <div class="flex items-center gap-2">
                        <span class="text-[10px] uppercase tracking-widest font-bold text-gray-500 flex-shrink-0">Link</span>
                        <a href="${escHtml(link)}" target="_blank"
                           onclick="event.stopPropagation()"
                           class="text-xs text-blue-400 hover:text-blue-300 truncate underline underline-offset-2"
                           id="displayLink">${escHtml(link)}</a>
                    </div>` : ''}
                </div>
                <div class="flex-shrink-0 p-1.5 rounded-lg bg-white/5 hover:bg-white/10 transition-colors mt-0.5">
                    <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </div>
            </div>
        `;

        // Update summary text
        const folder = path.split('/').pop();
        document.getElementById('synologyStatusText').textContent = folder;
        document.getElementById('synologyStatusText').className = 'font-mono text-[10px] truncate max-w-[160px] text-emerald-400';
    } else {
        // Kosongkan
        display.className = 'synology-box cursor-pointer';
        display.innerHTML = `
            <div class="flex items-center gap-3 text-gray-500">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/>
                </svg>
                <span class="text-sm">Klik untuk pilih folder Synology...</span>
                <svg class="w-4 h-4 ml-auto flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </div>
        `;
        document.getElementById('synologyStatusText').textContent = 'Not Set';
        document.getElementById('synologyStatusText').className = 'font-mono text-[10px] truncate max-w-[160px] text-gray-500';
    }

    // Tandai active option di dropdown
    document.querySelectorAll('.synology-option').forEach(el => {
        el.classList.remove('active');
    });
    if (path) {
        document.querySelectorAll('.synology-option').forEach(el => {
            const elPath = el.querySelector('p.font-semibold')?.nextElementSibling?.textContent?.trim();
            if (elPath === path) el.classList.add('active');
        });
    }

    closeSynologyDropdown();
}

// ─────────────────────────────────────────────────────────────
// WORKFLOW BUILDER
// ─────────────────────────────────────────────────────────────
function updateSummaryStats() {
    const legalCount = items.filter(i => i.type === 'legal').length;
    const parallelBlock = items.find(i => i.type === 'parallel');
    const deptCount = parallelBlock ? parallelBlock.depts.length : 0;
    document.getElementById('stageCount').textContent = legalCount;
    document.getElementById('deptCount').textContent  = deptCount;
}

function render() {
    const canvas = document.getElementById('wbCanvas');
    canvas.innerHTML = '';
    let seq = 1;

    items.forEach((item, idx) => {
        seq++;
        const el = item.type === 'legal' ? buildLegalCard(item, idx, seq) : buildParCard(item, idx, seq);
        el.classList.add('animate-slide-up');
        canvas.appendChild(el);
        setupDrag(el, idx);
    });

    const parBtn = document.getElementById('addParBtn');
    if (parBtn) {
        parBtn.disabled      = parallelExists;
        parBtn.style.opacity = parallelExists ? '.3' : '1';
    }
    updateSummary();
    updateSummaryStats();
}

function buildLegalCard(item, idx, seq) {
    const div  = document.createElement('div');
    div.className = 'wb-seq';
    const opts = LEGAL_OFFICERS.map(u =>
        `<option value="${u.id}" ${u.id == item.user_id ? 'selected' : ''}>${escHtml(u.name)}</option>`
    ).join('');

    div.innerHTML = `
        <div class="flex items-center gap-4">
            <div class="drag-handle">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8h16M4 16h16"/></svg>
            </div>
            <div class="seq-num">${seq}</div>
            <div class="flex-1 grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="text-[10px] uppercase tracking-widest font-bold text-gray-500 mb-1.5 block">Legal Officer</label>
                    <select class="input-field select-field w-full rounded-lg py-2 px-3 text-sm"
                            onchange="items[${idx}].user_id=parseInt(this.value)||null; updateSummaryStats();" required>
                        <option value="">Select Officer...</option>
                        ${opts}
                    </select>
                </div>
                <div>
                    <label class="text-[10px] uppercase tracking-widest font-bold text-gray-500 mb-1.5 block">Stage Name</label>
                    <input type="text" class="input-field w-full rounded-lg py-2 px-3 text-sm stage-name-input"
                           value="${escHtml(item.stage_name)}"
                           oninput="items[${idx}].stage_name=this.value" required>
                </div>
            </div>
            <button type="button" class="btn-remove p-2" onclick="removeItem(${idx})">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
    `;
    return div;
}

function buildParCard(item, idx, seq) {
    const div = document.createElement('div');
    div.className = 'wb-par';
    const allSelected = item.depts.length === DEPT_OPTIONS.length;

    const deptCards = DEPT_OPTIONS.map(d => {
        const selected = item.depts.includes(d.code);
        return `
            <div class="dept-card ${selected ? 'selected' : ''}" onclick="toggleDept(${idx},'${d.code}')">
                <div class="flex items-center gap-2 mb-1">
                    <span class="text-lg">${d.icon}</span>
                    <span class="text-xs font-bold text-gray-200">${d.code}</span>
                    <div class="ml-auto ${selected ? '' : 'hidden'} text-purple-400">
                        <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><path d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"/></svg>
                    </div>
                </div>
                <p class="text-[10px] text-gray-500 font-medium">${d.label}</p>
            </div>
        `;
    }).join('');

    const legalOpts = LEGAL_OFFICERS.map(u =>
        `<option value="${u.id}" ${u.id == item.legal_reviewer_id ? 'selected' : ''}>${escHtml(u.name)}</option>`
    ).join('');

    div.innerHTML = `
        <div class="flex items-center justify-between mb-4">
            <div class="flex items-center gap-4">
                <div class="drag-handle text-purple-500/50">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8h16M4 16h16"/></svg>
                </div>
                <div class="w-8 h-8 rounded-lg bg-purple-500/20 border border-purple-500/30 flex items-center justify-center text-purple-400 font-bold text-sm">${seq}</div>
                <div>
                    <h4 class="text-sm font-bold text-purple-300">Substantial Review</h4>
                    <p class="text-[11px] text-gray-500">Parallel processing — all selected departments review simultaneously.</p>
                </div>
            </div>
            <div class="flex items-center gap-3">
                <label class="checkbox-wrapper">
                    <input type="checkbox" id="selectAllDepts_${idx}" ${allSelected ? 'checked' : ''}
                           onchange="toggleAllDepartments(${idx}, this.checked)">
                    <span>Select All</span>
                </label>
                <button type="button" class="text-xs font-bold text-red-400/60 hover:text-red-400 transition-colors"
                        onclick="removeParallel(${idx})">REMOVE</button>
            </div>
        </div>
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-4">${deptCards}</div>
        <div id="legalReviewerRow_${idx}" class="${item.depts.includes('LEGAL') ? '' : 'hidden'} animate-slide-up p-4 bg-purple-500/5 border border-purple-500/10 rounded-xl">
            <label class="text-[10px] font-bold text-purple-300 uppercase tracking-widest mb-2 block">Assign Legal Reviewer</label>
            <select class="input-field select-field w-full md:w-1/2 rounded-lg py-2 px-3 text-sm"
                    onchange="items[${idx}].legal_reviewer_id=parseInt(this.value)||null">
                <option value="">Select Officer...</option>
                ${legalOpts}
            </select>
        </div>
    `;
    return div;
}

function toggleAllDepartments(parIdx, isChecked) {
    const item = items[parIdx];
    if (!item || item.type !== 'parallel') return;
    item.depts = isChecked ? DEPT_OPTIONS.map(d => d.code) : [];
    if (!isChecked) item.legal_reviewer_id = null;
    render();
}

function addLegalStage(doRender = true) {
    const n = items.filter(i => i.type === 'legal').length + 1;
    items.push({ _id: Math.random().toString(36).slice(2), type: 'legal', user_id: null, stage_name: `Legal Review Stage ${n}` });
    if (doRender) render();
}

function addParallelBlock() {
    if (parallelExists) return;
    parallelExists = true;
    items.push({ _id: Math.random().toString(36).slice(2), type: 'parallel', depts: [], legal_reviewer_id: null });
    render();
}

function removeItem(idx) {
    if (confirm('Remove this stage?')) { items.splice(idx, 1); render(); }
}

function removeParallel(idx) {
    if (confirm('Remove the Substantial Review block?')) { parallelExists = false; items.splice(idx, 1); render(); }
}

function toggleDept(parIdx, code) {
    const item = items[parIdx];
    const pos  = item.depts.indexOf(code);
    if (pos === -1) item.depts.push(code);
    else { item.depts.splice(pos, 1); if (code === 'LEGAL') item.legal_reviewer_id = null; }
    render();
}

function updateSummary() {
    const el   = document.getElementById('wfSummary');
    const flow = items.map((it, i) =>
        it.type === 'legal' ? `L${items.filter((x,j) => x.type==='legal' && j<=i).length}` : '⚡'
    ).join(' → ');
    el.innerHTML = `<span class="text-gray-500 uppercase text-[10px] tracking-widest mr-2">Route:</span><span class="text-blue-400 font-mono">User → ${flow || '...'} → End</span>`;
}

function setupDrag(el, idx) {
    el.setAttribute('draggable', 'true');
    el.addEventListener('dragstart', e => {
        if (['INPUT','SELECT','BUTTON','A'].includes(e.target.tagName)) { e.preventDefault(); return; }
        dragSrc = idx; el.classList.add('dragging');
    });
    el.addEventListener('dragend',  () => { el.classList.remove('dragging'); render(); });
    el.addEventListener('dragover', e => { e.preventDefault(); el.classList.add('drag-over'); });
    el.addEventListener('dragleave',() => el.classList.remove('drag-over'));
    el.addEventListener('drop', e => {
        e.preventDefault();
        const moved = items.splice(dragSrc, 1)[0];
        items.splice(idx, 0, moved);
        render();
    });
}

function onSubmit(e) {
    // Sync stage names dari DOM
    document.querySelectorAll('.stage-name-input').forEach((input, i) => {
        const legalItems = items.filter(item => item.type === 'legal');
        if (legalItems[i]) legalItems[i].stage_name = input.value;
    });

    if (items.filter(i => i.type === 'legal').length < 1) {
        alert('Minimum 1 Legal Review Stage is required.');
        e.preventDefault(); return;
    }
    for (let i = 0; i < items.length; i++) {
        if (items[i].type === 'legal' && !items[i].user_id) {
            alert(`Please select a Legal Officer for "${items[i].stage_name}"`);
            e.preventDefault(); return;
        }
    }
    const par = items.find(i => i.type === 'parallel');
    if (par && par.depts.length === 0) {
        alert('Substantial Review must have at least one department selected!');
        e.preventDefault(); return;
    }
    if (par && par.depts.includes('LEGAL') && !par.legal_reviewer_id) {
        alert('Please assign a reviewer for the Legal department in Substantial Review!');
        e.preventDefault(); return;
    }

    document.getElementById('workflowItemsInput').value = JSON.stringify(items);

    const btn = document.getElementById('submitBtn');
    btn.disabled  = true;
    btn.innerHTML = `<svg class="animate-spin h-4 w-4 mr-2 inline" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg> Processing...`;
}

function escHtml(s) {
    return s ? String(s).replace(/[&<>"']/g, m => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":"&#39;"}[m])) : '';
}
</script>
</x-app-layout-dark>