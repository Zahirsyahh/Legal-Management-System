<x-app-layout-dark title="Edit Archive">

<style>
    /* ============================================================
       WRAPPER — page header & grid share the same max-width/padding
    ============================================================ */
    .page-outer {
        max-width: 1600px;
        margin: 0 auto;
        padding: 2rem 1.5rem 0;
    }

    /* ============================================================
       TWO-COLUMN GRID
       default  → 2 kolom (cross-ref visible)
       .hide-cr → 1 kolom (cross-ref hidden)
    ============================================================ */
    .two-column-layout {
        display: grid;
        grid-template-columns: 1fr 420px;
        gap: 1.5rem;
        align-items: start;
        max-width: 1600px;
        margin: 0 auto;
        padding: 1.25rem 1.5rem 2rem;
        transition: grid-template-columns 0.3s ease;
    }

    .two-column-layout.hide-cr {
        grid-template-columns: 1fr;
    }

    .two-column-layout.hide-cr .cross-ref-sidebar {
        display: none;
    }

    .main-form { min-width: 0; }

    /* Cross-ref sidebar — sejajar dengan card Classification */
    .cross-ref-sidebar {
        position: relative;
        align-self: start;
        margin-top: 0.01rem;
    }

    /* ============================================================
       BACK BUTTON
    ============================================================ */
    .btn-back {
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        padding: 0.45rem 1rem;
        background: rgba(255,255,255,0.04);
        border: 1px solid rgba(255,255,255,0.1);
        border-radius: 0.625rem;
        color: #9ca3af;
        font-size: 0.8125rem;
        font-weight: 500;
        text-decoration: none;
        transition: all 0.2s;
    }

    .btn-back:hover {
        background: rgba(255,255,255,0.08);
        color: white;
        border-color: rgba(255,255,255,0.2);
    }

    .btn-back svg { width: 0.875rem; height: 0.875rem; }

    /* ============================================================
       ADD CROSS REFERENCE TOGGLE BUTTON (di page header)
    ============================================================ */
    .btn-toggle-crossref {
        display: inline-flex; align-items: center; gap: 0.5rem;
        padding: 0.55rem 1.1rem;
        background: rgba(139,92,246,0.08);
        border: 1px solid rgba(139,92,246,0.35);
        border-radius: 0.75rem;
        color: #c084fc; font-size: 0.8125rem; font-weight: 500;
        cursor: pointer; transition: all 0.2s; white-space: nowrap;
    }

    .btn-toggle-crossref:hover {
        background: rgba(139,92,246,0.15);
        border-color: rgba(139,92,246,0.6);
        color: #d8b4fe;
    }

    .btn-toggle-crossref.active {
        background: rgba(139,92,246,0.18);
        border-color: rgba(139,92,246,0.6);
        color: #d8b4fe;
    }

    .btn-toggle-crossref svg { width: 0.9rem; height: 0.9rem; }

    /* ============================================================
       FORM SECTIONS
    ============================================================ */
    .form-section {
        background: linear-gradient(145deg, rgba(30,41,59,0.6), rgba(15,23,42,0.8));
        backdrop-filter: blur(12px);
        border: 1px solid rgba(14,165,233,0.15);
        border-radius: 1.5rem;
        margin-bottom: 1.5rem;
        overflow: hidden;
    }

    .section-header {
        background: linear-gradient(135deg, rgba(14,165,233,0.08), rgba(59,130,246,0.04));
        padding: 0.875rem 1.5rem;
        border-bottom: 1px solid rgba(14,165,233,0.15);
    }

    .section-title {
        font-size: 0.9375rem;
        font-weight: 600;
        color: white;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .section-title svg { width: 1.1rem; height: 1.1rem; color: #0ea5e9; }

    .form-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 1.25rem;
        padding: 1.25rem 1.5rem;
    }

    .form-group             { display: flex; flex-direction: column; gap: 0.375rem; }
    .form-group.full-width  { grid-column: span 2; }

    .form-label             { font-size: 0.8125rem; font-weight: 500; color: #9ca3af; }
    .form-label.required::after { content: ' *'; color: #ef4444; }

    .form-input, .form-select, .form-textarea {
        background: rgba(15,23,42,0.6);
        border: 1px solid rgba(255,255,255,0.08);
        border-radius: 0.625rem;
        padding: 0.625rem 0.875rem;
        color: white;
        font-size: 0.875rem;
        transition: border-color 0.2s, box-shadow 0.2s;
        width: 100%;
    }

    .form-input:focus, .form-select:focus, .form-textarea:focus {
        outline: none;
        border-color: #0ea5e9;
        box-shadow: 0 0 0 3px rgba(14,165,233,0.1);
    }

    .form-select option { background: #0f172a; color: white; }

    .help-text  { font-size: 0.7rem; color: #6b7280; }

    .record-id-wrap         { display: flex; gap: 0.625rem; }
    .record-id-wrap .form-input { flex: 1; }

    /* ============================================================
       TOOLTIP INFO ICON (pengganti note-box)
    ============================================================ */
    .info-tooltip-wrap {
    position: relative;
    display: inline-flex;
    align-items: center;
    margin-top: 0.35rem;
}

.info-tooltip-icon {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 1.25rem;
    height: 1.25rem;
    border-radius: 50%;
    background: rgba(245,158,11,0.12);
    border: 1px solid rgba(245,158,11,0.3);
    color: #f59e0b;
    cursor: default;
    flex-shrink: 0;
    transition: background 0.2s, border-color 0.2s;
}

.info-tooltip-icon:hover {
    background: rgba(245,158,11,0.22);
    border-color: rgba(245,158,11,0.55);
}

.info-tooltip-icon svg { width: 0.7rem; height: 0.7rem; }

.info-tooltip-bubble {
    display: none;
    position: absolute;
    bottom: calc(100% + 8px);
    left: 0;
    /* Hapus transform: translateX(-50%) */
    min-width: 260px;
    max-width: 300px;
    background: rgba(15,23,42,0.97);
    border: 1px solid rgba(245,158,11,0.35);
    border-radius: 0.625rem;
    padding: 0.65rem 0.875rem;
    font-size: 0.7rem;
    color: #fcd34d;
    line-height: 1.5;
    z-index: 50;
    pointer-events: none;
    box-shadow: 0 8px 24px rgba(0,0,0,0.4);
    /* Tambahkan white-space agar teks tidak keluar dari container */
    white-space: normal;
    word-wrap: break-word;
}

.info-tooltip-bubble::after {
    content: '';
    position: absolute;
    top: 100%;
    left: 1rem; /* Ubah dari 50% ke 1rem agar arrow sejajar dengan icon */
    transform: translateX(0); /* Hapus translateX(-50%) */
    border: 6px solid transparent;
    border-top-color: rgba(245,158,11,0.35);
}

/* Optional: Jika ingin arrow tetap di tengah icon, gunakan pendekatan berbeda */
.info-tooltip-wrap:hover .info-tooltip-bubble { display: block; }

    /* ============================================================
       REGENERATE BUTTON
    ============================================================ */
    .regenerate-btn {
        background: linear-gradient(135deg, #f59e0b, #d97706);
        color: white;
        border: none;
        border-radius: 0.625rem;
        padding: 0.625rem 1rem;
        font-size: 0.8125rem;
        cursor: pointer;
        white-space: nowrap;
        transition: all 0.2s;
        display: inline-flex;
        align-items: center;
        gap: 0.375rem;
    }

    .regenerate-btn:hover    { transform: translateY(-1px); box-shadow: 0 4px 12px rgba(245,158,11,0.35); }
    .regenerate-btn:disabled { opacity: 0.6; cursor: not-allowed; transform: none; }

    /* ============================================================
       VERSION STATUS — pill radio
    ============================================================ */
    .version-radio-group {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
        background: rgba(15,23,42,0.4);
        border: 1px solid rgba(255,255,255,0.08);
        border-radius: 0.75rem;
        padding: 0.5rem;
    }

    .version-radio-item { flex: 1; min-width: 80px; position: relative; }

    .version-radio-item input[type="radio"] {
        position: absolute; opacity: 0; width: 0; height: 0;
    }

    .version-radio-label {
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 0.5rem 0.75rem;
        background: rgba(255,255,255,0.03);
        border: 1px solid rgba(255,255,255,0.06);
        border-radius: 0.5rem;
        color: #9ca3af;
        font-size: 0.775rem;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.2s;
        text-align: center;
        user-select: none;
    }

    .version-radio-item input:checked + .version-radio-label {
        background: rgba(14,165,233,0.15);
        border-color: #0ea5e9;
        color: #38bdf8;
    }

    /* ============================================================
       DOCUMENT STATUS — custom pill checkbox
    ============================================================ */
    .doc-status-group {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
        padding: 0.625rem 0;
    }

    .doc-status-item { position: relative; }

    .doc-status-item input[type="checkbox"] {
        position: absolute; opacity: 0; width: 0; height: 0; pointer-events: none;
    }

    .doc-status-label {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.45rem 0.875rem 0.45rem 0.625rem;
        border-radius: 9999px;
        border: 1px solid rgba(255,255,255,0.1);
        background: rgba(255,255,255,0.03);
        color: #9ca3af;
        font-size: 0.8rem;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.2s ease;
        user-select: none;
        white-space: nowrap;
    }

    .doc-status-label .check-box {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 1rem;
        height: 1rem;
        border-radius: 4px;
        border: 1.5px solid rgba(255,255,255,0.2);
        background: transparent;
        flex-shrink: 0;
        transition: all 0.2s ease;
    }

    .doc-status-label .check-box svg {
        width: 0.6rem; height: 0.6rem;
        opacity: 0; transition: opacity 0.15s ease; stroke: white;
    }

    .doc-status-item input:checked + .doc-status-label {
        background: rgba(14,165,233,0.12);
        border-color: rgba(14,165,233,0.5);
        color: #38bdf8;
        box-shadow: 0 0 0 2px rgba(14,165,233,0.08);
    }

    .doc-status-item input:checked + .doc-status-label .check-box {
        background: #0ea5e9; border-color: #0ea5e9;
    }

    .doc-status-item input:checked + .doc-status-label .check-box svg { opacity: 1; }
    .doc-status-label:hover { border-color: rgba(14,165,233,0.3); color: #cbd5e1; }

    /* ============================================================
       FORM ACTIONS
    ============================================================ */
    .form-actions {
        display: flex;
        justify-content: flex-end;
        gap: 1rem;
        margin-top: 2rem;
        padding-top: 1.5rem;
        border-top: 1px solid rgba(255,255,255,0.05);
    }

    .btn-primary {
        background: linear-gradient(135deg, #0ea5e9, #3b82f6);
        color: white;
        padding: 0.75rem 2rem;
        border-radius: 0.75rem;
        font-weight: 600;
        transition: all 0.2s;
        border: none;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
    }

    .btn-primary:hover { transform: translateY(-1px); box-shadow: 0 4px 12px rgba(14,165,233,0.3); }

    .btn-secondary {
        background: rgba(255,255,255,0.05);
        color: #9ca3af;
        padding: 0.75rem 1.5rem;
        border-radius: 0.75rem;
        font-weight: 500;
        transition: all 0.2s;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        border: 1px solid rgba(255,255,255,0.08);
    }

    .btn-secondary:hover { background: rgba(255,255,255,0.1); color: white; }

    .alert-error {
        background: rgba(239,68,68,0.1);
        border: 1px solid rgba(239,68,68,0.3);
        border-radius: 1rem;
        padding: 1rem;
        margin-bottom: 1.5rem;
        display: flex;
        align-items: flex-start;
        gap: 0.75rem;
        color: #f87171;
    }

    .loading-spinner {
        display: inline-block;
        width: 0.875rem; height: 0.875rem;
        border: 2px solid rgba(255,255,255,0.3);
        border-top-color: white;
        border-radius: 50%;
        animation: spin 0.6s linear infinite;
    }

    @keyframes spin { to { transform: rotate(360deg); } }

    /* ============================================================
       CROSS REFERENCE — sidebar
    ============================================================ */
    .cross-ref-section {
        background: linear-gradient(145deg, rgba(30,41,59,0.6), rgba(15,23,42,0.85));
        backdrop-filter: blur(12px);
        border: 1px solid rgba(139,92,246,0.25);
        border-radius: 1.5rem;
        overflow: hidden;
    }

    .cross-ref-header {
        background: linear-gradient(135deg, rgba(139,92,246,0.1), rgba(99,102,241,0.05));
        padding: 0.875rem 1.25rem;
        border-bottom: 1px solid rgba(139,92,246,0.2);
        display: flex; align-items: center; justify-content: space-between;
    }

    .cross-ref-title {
        font-size: 0.9375rem; font-weight: 600; color: white;
        display: flex; align-items: center; gap: 0.5rem;
    }

    .cross-ref-title svg { width: 1.1rem; height: 1.1rem; color: #a78bfa; }

    .badge-optional {
        font-size: 0.65rem; font-weight: 500;
        padding: 0.15rem 0.55rem;
        background: rgba(139,92,246,0.15);
        border: 1px solid rgba(139,92,246,0.3);
        color: #c084fc; border-radius: 9999px;
    }

    .btn-close-crossref {
        display: inline-flex; align-items: center; justify-content: center;
        width: 1.75rem; height: 1.75rem;
        background: rgba(239,68,68,0.08);
        border: 1px solid rgba(239,68,68,0.2);
        border-radius: 0.5rem; color: #f87171;
        cursor: pointer; transition: all 0.2s;
    }

    .btn-close-crossref:hover { background: rgba(239,68,68,0.18); border-color: rgba(239,68,68,0.4); }
    .btn-close-crossref svg   { width: 0.8rem; height: 0.8rem; }

    .cross-ref-body { padding: 1.125rem; }

    .cross-ref-card {
        background: rgba(15,23,42,0.5);
        border: 1px solid rgba(139,92,246,0.15);
        border-radius: 1rem; padding: 1rem; margin-bottom: 1rem;
        transition: border-color 0.2s ease;
    }

    .cross-ref-card:hover      { border-color: rgba(139,92,246,0.3); }
    .cross-ref-card:last-child { margin-bottom: 0; }

    .cross-ref-card-header {
        display: flex; align-items: center; justify-content: space-between;
        margin-bottom: 1rem; padding-bottom: 0.75rem;
        border-bottom: 1px solid rgba(255,255,255,0.05);
    }

    .cross-ref-badge-num {
        display: inline-flex; align-items: center; justify-content: center;
        width: 1.75rem; height: 1.75rem;
        background: rgba(139,92,246,0.2); border: 1px solid rgba(139,92,246,0.3);
        border-radius: 50%; font-size: 0.75rem; font-weight: 700; color: #c084fc;
    }

    .btn-remove-card {
        background: rgba(239,68,68,0.08); border: 1px solid rgba(239,68,68,0.2);
        border-radius: 0.5rem; padding: 0.35rem 0.75rem;
        color: #f87171; cursor: pointer; font-size: 0.7rem; font-weight: 500;
        transition: all 0.2s ease;
        display: inline-flex; align-items: center; gap: 0.3rem;
    }

    .btn-remove-card:hover { background: rgba(239,68,68,0.2); border-color: rgba(239,68,68,0.4); }

    .cross-ref-fields { display: flex; flex-direction: column; gap: 0.875rem; }
    .cross-ref-field  { display: flex; flex-direction: column; gap: 0.35rem; }

    .cross-ref-label              { font-size: 0.7rem; font-weight: 500; color: #9ca3af; }
    .cross-ref-label.required::after { content: ' *'; color: #ef4444; }

    .cross-ref-input, .cross-ref-textarea {
        background: rgba(15,23,42,0.8);
        border: 1px solid rgba(255,255,255,0.08);
        border-radius: 0.5rem; padding: 0.55rem 0.75rem;
        color: white; font-size: 0.8rem; width: 100%;
        transition: all 0.2s ease;
    }

    .cross-ref-input:focus, .cross-ref-textarea:focus {
        outline: none; border-color: #a78bfa;
        box-shadow: 0 0 0 2px rgba(139,92,246,0.1);
    }

    .cross-ref-textarea { resize: vertical; min-height: 60px; }

    .btn-add-ref {
        background: rgba(139,92,246,0.08);
        border: 1px dashed rgba(139,92,246,0.4);
        border-radius: 0.75rem; padding: 0.7rem 1rem;
        color: #a78bfa; font-size: 0.8rem; font-weight: 500;
        cursor: pointer; transition: all 0.2s ease;
        display: flex; align-items: center; justify-content: center;
        gap: 0.5rem; width: 100%; margin-top: 0.5rem;
    }

    .btn-add-ref:hover { background: rgba(139,92,246,0.15); border-color: rgba(139,92,246,0.6); }

    /* ============================================================
       RESPONSIVE
    ============================================================ */
    @media (max-width: 1024px) {
        .two-column-layout,
        .two-column-layout.hide-cr { grid-template-columns: 1fr !important; }
        .cross-ref-sidebar  { position: static; display: block !important; }
        .btn-toggle-crossref { display: none; }
    }

    @media (max-width: 640px) {
        .page-outer        { padding: 1rem 1rem 0; }
        .two-column-layout { padding: 1rem; }
        .form-grid         { grid-template-columns: 1fr; }
        .form-group.full-width { grid-column: span 1; }
        .version-radio-item { min-width: auto; }
    }
</style>

{{-- ================================================================
     PAGE HEADER — di luar grid agar sidebar sejajar dgn Classification
================================================================ --}}
<div class="page-outer">
    {{-- Back button --}}
    <div class="mb-4">
        <a href="{{ route('archives.index') }}" class="btn-back">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            Back to List
        </a>
    </div>

    {{-- Title row + toggle button --}}
    <div class="flex items-center justify-between gap-3 mb-5">
        <div class="flex items-center gap-3">
            <div class="p-3 rounded-xl bg-gradient-to-br from-cyan-500/10 to-blue-600/10 border border-cyan-500/20">
                <svg class="w-6 h-6 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
            </div>
            <div>
                <h1 class="text-2xl font-bold bg-gradient-to-r from-cyan-400 via-blue-400 to-cyan-400 bg-clip-text text-transparent">
                    Edit Archive
                </h1>
                <p class="text-gray-400 text-sm mt-0.5">Update existing document metadata and classification</p>
            </div>
        </div>

        <button type="button" id="toggleCrossRefBtn" class="btn-toggle-crossref">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/>
            </svg>
            Add Cross Reference
        </button>
    </div>
</div>

{{-- Hidden form untuk PUT --}}
<form action="{{ route('archives.update', $archive->id) }}" method="POST" id="archiveForm" style="display:none">
    @csrf
    @method('PUT')
</form>

@php
    $hasOldData      = old('ref_doc_name') !== null;
    $oldRefDocNames  = old('ref_doc_name', []);
    $oldRefRecordIds = old('ref_record_id', []);
    $oldRefLocations = old('ref_location', []);
    $oldRefRelations = old('ref_relation', []);

    if ($hasOldData) {
        $refs = [];
        foreach ($oldRefDocNames as $i => $docName) {
            if (!empty(trim($docName))) {
                $refs[] = (object)[
                    'ref_doc_name'  => $docName,
                    'ref_record_id' => $oldRefRecordIds[$i] ?? '',
                    'ref_location'  => $oldRefLocations[$i] ?? '',
                    'ref_relation'  => $oldRefRelations[$i] ?? '',
                ];
            }
        }
    } else {
        $refs = $archive->crossReferences ?? collect();
    }

    $hasRefs = count($refs) > 0;
@endphp

<div class="two-column-layout {{ $hasRefs ? '' : 'hide-cr' }}" id="mainLayout">

    {{-- LEFT COLUMN --}}
    <div class="main-form">

        @if($errors->any())
        <div class="alert-error">
            <svg class="w-5 h-5 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <div>
                <strong>Please fix the following errors:</strong>
                <ul class="mt-1 list-disc list-inside text-sm">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
        @endif

        {{-- SECTION 1: CLASSIFICATION --}}
        <div class="form-section">
            <div class="section-header">
                <div class="section-title">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                    </svg>
                    Classification
                </div>
            </div>
            <div class="form-grid">

                <div class="form-group full-width">
                    <label class="form-label required">Record ID</label>
                    <div class="record-id-wrap">
                        <input type="text"
                               name="record_id"
                               id="record_id"
                               form="archiveForm"
                               value="{{ old('record_id', $archive->record_id) }}"
                               class="form-input"
                               required>
                        <button type="button" id="regenerateRecordIdBtn" class="regenerate-btn">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                            </svg>
                            Regenerate
                        </button>
                    </div>
                    <div class="info-tooltip-wrap">
                        <span class="info-tooltip-icon">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </span>
                        <div class="info-tooltip-bubble">
                            Use <strong>Regenerate</strong> to reformat the number using the latest numbering format. Select <strong>Year</strong> below before regenerating.
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label required">Company</label>
                    <select name="company" id="companySelect" form="archiveForm" class="form-select" required>
                        <option value="GNI" {{ old('company', $archive->company) == 'GNI' ? 'selected' : '' }}>GNI - Gunbuster Nickel Industry</option>
                        <option value="AMI" {{ old('company', $archive->company) == 'AMI' ? 'selected' : '' }}>AMI - Adhi Kartiko Pratama</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label required">Year (for Record ID)</label>
                    <select id="yearSelect" class="form-select">
                    @php $currentYear = substr($archive->record_year, 2); @endphp
                        @for($year = date('Y'); $year >= date('Y') - 20; $year--)
                            @php $yy = substr($year, 2, 2); @endphp
                            <option value="{{ $yy }}" {{ $currentYear == $yy ? 'selected' : '' }}>
                                {{ $year }} ({{ $yy }})
                            </option>
                        @endfor
                    </select>
                    <p class="help-text">Used when regenerating Record ID</p>
                </div>

                <div class="form-group">
                    <label class="form-label required">Department</label>
                    <select name="department" id="departmentSelect" form="archiveForm" class="form-select" required>
                        @foreach(['LG'=>'Legal','HR'=>'HRD','OP'=>'Operation','AC'=>'Accounting','FN'=>'Finance','TX'=>'Tax','EX'=>'Exim','CC'=>'CorCom','NP'=>'Nickel Ore','HE'=>'HSE','CP'=>'Coal','SL'=>'Sales','PC'=>'Purchasing','IT'=>'IT','GA'=>'GA','DK'=>'Direksi'] as $code => $name)
                            <option value="{{ $code }}" {{ old('department', $archive->department_code) == $code ? 'selected' : '' }}>
                                {{ $code }} - {{ $name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label required">Document Type</label>
                    <select name="doc_type" id="docTypeSelect" form="archiveForm" class="form-select" required>
                        @foreach($docTypes as $code => $label)
                            <option value="{{ $code }}" {{ old('doc_type', $archive->doc_type) == $code ? 'selected' : '' }}>
                                {{ $code }} — {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>

            </div>
        </div>

        {{-- SECTION 2: DETAILS & STATUS --}}
        <div class="form-section">
            <div class="section-header">
                <div class="section-title">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    Details & Status
                </div>
            </div>
            <div class="form-grid">

                <div class="form-group full-width">
                    <label class="form-label required">Document Name</label>
                    <input type="text" name="doc_name" form="archiveForm"
                           value="{{ old('doc_name', $archive->doc_name) }}"
                           class="form-input" placeholder="Full document title / name" required>
                </div>

                <div class="form-group">
                    <label class="form-label">External Doc Number</label>
                    <input type="text" name="doc_number" form="archiveForm"
                           value="{{ old('doc_number', $archive->doc_number) }}"
                           class="form-input" placeholder="e.g. 001/GNI/LEGAL/2024">
                </div>

                <div class="form-group">
                    <label class="form-label">Counterparty</label>
                    <input type="text" name="counterparty" form="archiveForm"
                           value="{{ old('counterparty', $archive->counterparty) }}"
                           class="form-input" placeholder="Vendor / Partner name">
                </div>

                <div class="form-group full-width">
                    <label class="form-label">Description</label>
                    <textarea name="description" form="archiveForm" class="form-textarea" rows="3"
                              placeholder="Brief description of the document">{{ old('description', $archive->description) }}</textarea>
                </div>

                <div class="form-group full-width">
                    <label class="form-label required">Document Status</label>
                    <div class="doc-status-group">
                        @php
                            $currentStatus = old('doc_status', $archive->doc_status ?? []);
                            if (is_string($currentStatus)) { $currentStatus = json_decode($currentStatus, true) ?? []; }
                        @endphp
                        @foreach(\App\Models\Archive::DOC_STATUS as $status)
                        <div class="doc-status-item">
                            <input type="checkbox" name="doc_status[]" form="archiveForm"
                                   id="st_{{ $status }}" value="{{ $status }}"
                                   {{ in_array($status, $currentStatus) ? 'checked' : '' }}>
                            <label for="st_{{ $status }}" class="doc-status-label">
                                <span class="check-box">
                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                                    </svg>
                                </span>
                                {{ \App\Models\Archive::DOC_STATUS_LABEL[$status] }}
                            </label>
                        </div>
                        @endforeach
                    </div>
                </div>

                <div class="form-group full-width">
                    <label class="form-label required">Version Control</label>
                    <div class="version-radio-group">
                        @foreach(['latest' => 'Latest', 'obsolete' => 'Obsolete', 'superseded' => 'Superseded'] as $val => $lbl)
                        <div class="version-radio-item">
                            <input type="radio" name="version_status" form="archiveForm"
                                   id="vs_{{ $val }}" value="{{ $val }}"
                                   {{ old('version_status', $archive->version_status) == $val ? 'checked' : '' }} required>
                            <label for="vs_{{ $val }}" class="version-radio-label">{{ $lbl }}</label>
                        </div>
                        @endforeach
                    </div>
                </div>

            </div>
        </div>

        {{-- SECTION 3: VALIDITY & STORAGE --}}
        <div class="form-section">
            <div class="section-header">
                <div class="section-title">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    Validity & Storage
                </div>
            </div>
            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label">Start Date</label>
                    <input type="date" name="start_date" form="archiveForm"
                           value="{{ old('start_date', $archive->start_date ? $archive->start_date->format('Y-m-d') : '') }}"
                           class="form-input">
                </div>
                <div class="form-group">
                    <label class="form-label">End Date</label>
                    <input type="date" name="end_date" form="archiveForm"
                           value="{{ old('end_date', $archive->end_date ? $archive->end_date->format('Y-m-d') : '') }}"
                           class="form-input">
                </div>
                <div class="form-group">
                    <label class="form-label">Physical Location</label>
                    <input type="text" name="doc_location" form="archiveForm"
                           value="{{ old('doc_location', $archive->doc_location) }}"
                           class="form-input" placeholder="e.g. Cabinet B, Shelf 4">
                </div>
                <div class="form-group">
                    <label class="form-label">Digital Path (Synology)</label>
                    <input type="text" name="synology_path" form="archiveForm"
                           value="{{ old('synology_path', $archive->synology_path) }}"
                           class="form-input" placeholder="/volume1/Legal/...">
                </div>
            </div>
        </div>

        {{-- FORM ACTIONS --}}
        <div class="form-actions">
            <a href="{{ route('archives.index') }}" class="btn-secondary">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
                Cancel
            </a>
            <button type="submit" form="archiveForm" class="btn-primary">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                Update Archive
            </button>
        </div>

    </div>{{-- end main-form --}}

    {{-- RIGHT COLUMN — CROSS REFERENCES --}}
    <div class="cross-ref-sidebar" id="crossRefSidebar">
        <div class="cross-ref-section">
            <div class="cross-ref-header">
                <div class="cross-ref-title">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/>
                    </svg>
                    Cross References
                </div>
                <div style="display:flex;align-items:center;gap:.5rem;">
                    <span class="badge-optional">Optional</span>
                    <button type="button" id="closeCrossRefBtn" class="btn-close-crossref" title="Hide">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
            </div>

            <div class="cross-ref-body">
                <p class="text-sm text-gray-500 mb-4">Link this archive to related external documents.</p>

                <div id="crossRefContainer">
                    @forelse($refs as $index => $ref)
                    <div class="cross-ref-card" data-card>
                        <div class="cross-ref-card-header">
                            <span class="cross-ref-badge-num">{{ $loop->iteration }}</span>
                            <button type="button" class="btn-remove-card remove-cross-ref">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                                Remove
                            </button>
                        </div>
                        <div class="cross-ref-fields">
                            <div class="cross-ref-field">
                                <label class="cross-ref-label required">Document Name</label>
                                <input type="text" form="archiveForm" name="ref_doc_name[]" class="cross-ref-input"
                                       value="{{ $ref->ref_doc_name ?? '' }}" placeholder="e.g., Amendment Agreement No. 3">
                            </div>
                            <div class="cross-ref-field">
                                <label class="cross-ref-label">Record ID</label>
                                <input type="text" form="archiveForm" name="ref_record_id[]" class="cross-ref-input"
                                       value="{{ $ref->ref_record_id ?? '' }}" placeholder="e.g., 24LG-CT-001">
                            </div>
                            <div class="cross-ref-field">
                                <label class="cross-ref-label">Location</label>
                                <input type="text" form="archiveForm" name="ref_location[]" class="cross-ref-input"
                                       value="{{ $ref->ref_location ?? '' }}" placeholder="e.g., Cabinet B2">
                            </div>
                            <div class="cross-ref-field">
                                <label class="cross-ref-label">Description / Notes</label>
                                <textarea form="archiveForm" name="ref_relation[]" class="cross-ref-textarea"
                                          placeholder="Additional notes about this reference...">{{ $ref->ref_relation ?? '' }}</textarea>
                            </div>
                        </div>
                    </div>
                    @empty
                    {{-- Kartu kosong ditambahkan via JS saat sidebar dibuka --}}
                    @endforelse
                </div>

                <button type="button" id="addCrossRefBtn" class="btn-add-ref">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Add Another Reference
                </button>
            </div>
        </div>
    </div>

</div>{{-- end two-column-layout --}}

<script>
document.addEventListener('DOMContentLoaded', function () {

    // ====================================================
    // CROSS REF TOGGLE
    // ====================================================
    const layout    = document.getElementById('mainLayout');
    const toggleBtn = document.getElementById('toggleCrossRefBtn');
    const closeBtn  = document.getElementById('closeCrossRefBtn');
    const container = document.getElementById('crossRefContainer');

    const SVG_LINK = `<svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width:.9rem;height:.9rem">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/>
    </svg>`;

    function showCrossRef() {
        layout.classList.remove('hide-cr');
        toggleBtn.classList.add('active');
        toggleBtn.innerHTML = SVG_LINK + ' Cross Reference Added';

        // Jika belum ada kartu, tambahkan 1 kartu kosong
        if (container.querySelectorAll('[data-card]').length === 0) {
            const card = buildCard(1);
            attachRemove(card);
            container.appendChild(card);
        }
    }

    function hideCrossRef() {
        layout.classList.add('hide-cr');
        toggleBtn.classList.remove('active');
        toggleBtn.innerHTML = SVG_LINK + ' Add Cross Reference';
    }

    toggleBtn?.addEventListener('click', function () {
        layout.classList.contains('hide-cr') ? showCrossRef() : hideCrossRef();
    });

    closeBtn?.addEventListener('click', hideCrossRef);

    // Sync label toggle saat load (jika sidebar sudah tampil karena ada refs)
    if (!layout.classList.contains('hide-cr')) {
        toggleBtn.classList.add('active');
        toggleBtn.innerHTML = SVG_LINK + ' Cross Reference Added';
    }

    // ====================================================
    // REGENERATE RECORD ID
    // ====================================================
    const regenBtn         = document.getElementById('regenerateRecordIdBtn');
    const recordIdInput    = document.getElementById('record_id');
    const companySelect    = document.getElementById('companySelect');
    const yearSelect       = document.getElementById('yearSelect');
    const departmentSelect = document.getElementById('departmentSelect');
    const docTypeSelect    = document.getElementById('docTypeSelect');

    regenBtn?.addEventListener('click', async function () {
        const company    = companySelect?.value;
        const year       = yearSelect?.value;
        const department = departmentSelect?.value;
        const docType    = docTypeSelect?.value;

        if (!company || !year || !department || !docType) {
            alert('Please ensure Company, Year, Department, and Document Type are all filled in.');
            return;
        }

        const confirmed = confirm(
            'This will regenerate the Record ID using the new numbering format.\n\n' +
            'Current ID: ' + recordIdInput.value + '\n\nContinue?'
        );
        if (!confirmed) return;

        const originalHTML = regenBtn.innerHTML;
        regenBtn.innerHTML = '<span class="loading-spinner"></span> Regenerating...';
        regenBtn.disabled  = true;

        try {
            const response = await fetch('{{ route("archives.generate-record-id") }}', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                body: JSON.stringify({ company, year, department, doc_type: docType })
            });
            const data = await response.json();
            if (data.record_id) {
                recordIdInput.value = data.record_id;
                recordIdInput.style.borderColor = '#22c55e';
                recordIdInput.style.boxShadow   = '0 0 0 3px rgba(34,197,94,0.15)';
                setTimeout(() => {
                    recordIdInput.style.borderColor = '';
                    recordIdInput.style.boxShadow   = '';
                }, 2000);
            } else {
                alert(data.message || 'Failed to regenerate Record ID. Please try again.');
            }
        } catch (err) {
            console.error(err);
            alert('An error occurred. Please try again.');
        } finally {
            regenBtn.innerHTML = originalHTML;
            regenBtn.disabled  = false;
        }
    });

    // ====================================================
    // DATE VALIDATION
    // ====================================================
    const startDate = document.querySelector('input[name="start_date"]');
    const endDate   = document.querySelector('input[name="end_date"]');

    function validateDates() {
        if (startDate?.value && endDate?.value && new Date(endDate.value) < new Date(startDate.value)) {
            endDate.setCustomValidity('End date must be after start date');
        } else {
            endDate?.setCustomValidity('');
        }
    }

    startDate?.addEventListener('change', validateDates);
    endDate?.addEventListener('change',   validateDates);

    // ====================================================
    // CROSS REFERENCES — dynamic cards
    // ====================================================
    function reNumber() {
        container.querySelectorAll('[data-card] .cross-ref-badge-num').forEach(function (badge, i) {
            badge.textContent = i + 1;
        });
    }

    function attachRemove(card) {
        card.querySelector('.remove-cross-ref')?.addEventListener('click', function () {
            const cards = container.querySelectorAll('[data-card]');
            if (cards.length <= 1) {
                card.remove();
                hideCrossRef();
                return;
            }
            card.remove();
            reNumber();
        });
    }

    function buildCard(num) {
        const div = document.createElement('div');
        div.className = 'cross-ref-card';
        div.setAttribute('data-card', '');
        div.innerHTML = `
            <div class="cross-ref-card-header">
                <span class="cross-ref-badge-num">${num}</span>
                <button type="button" class="btn-remove-card remove-cross-ref">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                    Remove
                </button>
            </div>
            <div class="cross-ref-fields">
                <div class="cross-ref-field">
                    <label class="cross-ref-label required">Document Name</label>
                    <input type="text" form="archiveForm" name="ref_doc_name[]" class="cross-ref-input"
                           placeholder="e.g., Amendment Agreement No. 3">
                </div>
                <div class="cross-ref-field">
                    <label class="cross-ref-label">Record ID</label>
                    <input type="text" form="archiveForm" name="ref_record_id[]" class="cross-ref-input"
                           placeholder="e.g., 24LG-CT-001">
                </div>
                <div class="cross-ref-field">
                    <label class="cross-ref-label">Location</label>
                    <input type="text" form="archiveForm" name="ref_location[]" class="cross-ref-input"
                           placeholder="e.g., Cabinet B2">
                </div>
                <div class="cross-ref-field">
                    <label class="cross-ref-label">Description / Notes</label>
                    <textarea form="archiveForm" name="ref_relation[]" class="cross-ref-textarea"
                              placeholder="Additional notes about this reference..."></textarea>
                </div>
            </div>`;
        return div;
    }

    // Attach remove ke kartu yang sudah ada (dari DB)
    container.querySelectorAll('[data-card]').forEach(attachRemove);

    document.getElementById('addCrossRefBtn')?.addEventListener('click', function () {
        const num  = container.querySelectorAll('[data-card]').length + 1;
        const card = buildCard(num);
        attachRemove(card);
        container.appendChild(card);
        card.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    });

});
</script>

</x-app-layout-dark>