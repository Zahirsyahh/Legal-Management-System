<x-app-layout-dark title="Edit Archive">

    <style>
        /* ============================================================
           TWO COLUMN LAYOUT
        ============================================================ */
        .two-column-layout {
            display: grid;
            grid-template-columns: 1fr 420px;
            gap: 1.5rem;
            align-items: start;
            max-width: 1600px;
            margin: 0 auto;
            padding: 2rem 1.5rem;
        }

        /* Form menggunakan display: contents agar tidak merusak grid layout */
        #archiveForm {
            display: contents;
        }

        .main-form {
            min-width: 0;
        }

        /* Sidebar di kanan, digeser turun agar sejajar dengan Record Identity */
        .info-sidebar {
            position: relative;
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
            align-self: start;
            margin-top: 4.5rem;
        }

        /* ============================================================
           FORM SECTIONS
        ============================================================ */
        .form-section {
            background: linear-gradient(145deg, rgba(30, 41, 59, 0.6), rgba(15, 23, 42, 0.8));
            backdrop-filter: blur(12px);
            border: 1px solid rgba(14, 165, 233, 0.15);
            border-radius: 1.5rem;
            margin-bottom: 1.5rem;
            overflow: hidden;
        }

        .section-header {
            background: linear-gradient(135deg, rgba(14, 165, 233, 0.08), rgba(59, 130, 246, 0.04));
            padding: 0.875rem 1.5rem;
            border-bottom: 1px solid rgba(14, 165, 233, 0.15);
        }

        .section-title {
            font-size: 0.9375rem;
            font-weight: 600;
            color: white;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .section-title svg { 
            width: 1.1rem; 
            height: 1.1rem; 
            color: #0ea5e9; 
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1.25rem;
            padding: 1.25rem 1.5rem;
        }

        .form-group {
            display: flex;
            flex-direction: column;
            gap: 0.375rem;
        }

        .form-group.full-width { 
            grid-column: span 2; 
        }

        .form-label {
            font-size: 0.8125rem;
            font-weight: 500;
            color: #9ca3af;
        }

        .form-label.required::after {
            content: ' *';
            color: #ef4444;
        }

        .form-input, .form-select, .form-textarea {
            background: rgba(15, 23, 42, 0.6);
            border: 1px solid rgba(255, 255, 255, 0.08);
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
            box-shadow: 0 0 0 3px rgba(14, 165, 233, 0.1);
        }

        .form-select option { 
            background: #0f172a; 
            color: white; 
        }

        .radio-group {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
            background: rgba(15, 23, 42, 0.4);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 0.75rem;
            padding: 0.5rem;
        }

        .radio-option {
            flex: 1;
            min-width: 100px;
            position: relative;
        }

        .radio-option input {
            position: absolute;
            opacity: 0;
            width: 0;
            height: 0;
        }

        .radio-label {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 0.5rem;
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid rgba(255, 255, 255, 0.05);
            border-radius: 0.5rem;
            color: #9ca3af;
            font-size: 0.75rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s;
            text-align: center;
            height: 100%;
        }

        .radio-option input:checked + .radio-label {
            background: rgba(14, 165, 233, 0.15);
            border-color: #0ea5e9;
            color: #0ea5e9;
        }

        .date-range {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 0.75rem;
        }

        .help-text { 
            font-size: 0.7rem; 
            color: #6b7280; 
        }

        .record-id-wrap {
            display: flex;
            gap: 0.625rem;
        }

        .record-id-wrap .form-input { 
            flex: 1; 
        }

        .generate-btn {
            background: linear-gradient(135deg, #0ea5e9, #3b82f6);
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

        .generate-btn:hover { 
            transform: translateY(-1px); 
            box-shadow: 0 4px 12px rgba(14,165,233,0.3); 
        }

        .generate-btn:disabled { 
            opacity: 0.6; 
            cursor: not-allowed; 
            transform: none; 
        }

        /* ============================================================
           CROSS REFERENCE STYLING
        ============================================================ */
        .cross-ref-section {
            background: linear-gradient(145deg, rgba(30, 41, 59, 0.6), rgba(15, 23, 42, 0.85));
            backdrop-filter: blur(12px);
            border: 1px solid rgba(139, 92, 246, 0.25);
            border-radius: 1.5rem;
            overflow: hidden;
        }

        .cross-ref-header {
            background: linear-gradient(135deg, rgba(139, 92, 246, 0.1), rgba(99, 102, 241, 0.05));
            padding: 0.875rem 1.25rem;
            border-bottom: 1px solid rgba(139, 92, 246, 0.2);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .cross-ref-title {
            font-size: 0.9375rem;
            font-weight: 600;
            color: white;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .cross-ref-title svg { 
            width: 1.1rem; 
            height: 1.1rem; 
            color: #a78bfa; 
        }

        .badge-optional {
            font-size: 0.65rem;
            font-weight: 500;
            padding: 0.15rem 0.55rem;
            background: rgba(139, 92, 246, 0.15);
            border: 1px solid rgba(139, 92, 246, 0.3);
            color: #c084fc;
            border-radius: 9999px;
        }

        .cross-ref-body {
            padding: 1.125rem;
        }

        .cross-ref-card {
            background: rgba(15, 23, 42, 0.5);
            border: 1px solid rgba(139, 92, 246, 0.15);
            border-radius: 1rem;
            padding: 1rem;
            margin-bottom: 1rem;
            transition: border-color 0.2s ease;
        }

        .cross-ref-card:hover { 
            border-color: rgba(139, 92, 246, 0.3); 
        }

        .cross-ref-card:last-child {
            margin-bottom: 0;
        }

        .cross-ref-card-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 1rem;
            padding-bottom: 0.75rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
        }

        .cross-ref-badge-num {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 1.75rem;
            height: 1.75rem;
            background: rgba(139, 92, 246, 0.2);
            border: 1px solid rgba(139, 92, 246, 0.3);
            border-radius: 50%;
            font-size: 0.75rem;
            font-weight: 700;
            color: #c084fc;
        }

        .btn-remove-card {
            background: rgba(239, 68, 68, 0.08);
            border: 1px solid rgba(239, 68, 68, 0.2);
            border-radius: 0.5rem;
            padding: 0.35rem 0.75rem;
            color: #f87171;
            cursor: pointer;
            font-size: 0.7rem;
            font-weight: 500;
            transition: all 0.2s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.3rem;
        }

        .btn-remove-card:hover { 
            background: rgba(239, 68, 68, 0.2); 
            border-color: rgba(239, 68, 68, 0.4); 
        }

        .cross-ref-fields {
            display: flex;
            flex-direction: column;
            gap: 0.875rem;
        }

        .cross-ref-field {
            display: flex;
            flex-direction: column;
            gap: 0.35rem;
        }

        .cross-ref-label {
            font-size: 0.7rem;
            font-weight: 500;
            color: #9ca3af;
        }

        .cross-ref-label.required::after { 
            content: ' *'; 
            color: #ef4444; 
        }

        .cross-ref-input,
        .cross-ref-textarea {
            background: rgba(15, 23, 42, 0.8);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 0.5rem;
            padding: 0.55rem 0.75rem;
            color: white;
            font-size: 0.8rem;
            width: 100%;
            transition: all 0.2s ease;
        }

        .cross-ref-input:focus,
        .cross-ref-textarea:focus {
            outline: none;
            border-color: #a78bfa;
            box-shadow: 0 0 0 2px rgba(139, 92, 246, 0.1);
        }

        .cross-ref-textarea {
            resize: vertical;
            min-height: 60px;
        }

        .btn-add-ref {
            background: rgba(139, 92, 246, 0.08);
            border: 1px dashed rgba(139, 92, 246, 0.4);
            border-radius: 0.75rem;
            padding: 0.7rem 1rem;
            color: #a78bfa;
            font-size: 0.8rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            width: 100%;
            margin-top: 0.5rem;
        }

        .btn-add-ref:hover { 
            background: rgba(139, 92, 246, 0.15); 
            border-color: rgba(139, 92, 246, 0.6); 
        }

        /* Form Actions */
        .form-actions {
            display: flex;
            justify-content: flex-end;
            gap: 1rem;
            margin-top: 2rem;
            padding-top: 1.5rem;
            border-top: 1px solid rgba(255, 255, 255, 0.05);
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
        }

        .btn-primary:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(14, 165, 233, 0.3);
        }

        .btn-secondary {
            background: rgba(255, 255, 255, 0.05);
            color: #9ca3af;
            padding: 0.75rem 1.5rem;
            border-radius: 0.75rem;
            font-weight: 500;
            transition: all 0.2s;
            text-decoration: none;
        }

        .btn-secondary:hover {
            background: rgba(255, 255, 255, 0.1);
            color: white;
        }

        /* Alert Error */
        .alert-error {
            background: rgba(239, 68, 68, 0.1);
            border: 1px solid rgba(239, 68, 68, 0.3);
            border-radius: 1rem;
            padding: 1rem;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: flex-start;
            gap: 0.75rem;
        }

        .loading-spinner {
            display: inline-block;
            width: 0.875rem;
            height: 0.875rem;
            border: 2px solid rgba(255,255,255,0.3);
            border-top-color: white;
            border-radius: 50%;
            animation: spin 0.6s linear infinite;
        }

        @keyframes spin { 
            to { transform: rotate(360deg); } 
        }

        /* Responsive */
        @media (max-width: 1024px) {
            .two-column-layout {
                grid-template-columns: 1fr;
            }
            .info-sidebar {
                position: static;
                margin-top: 0;
            }
        }

        @media (max-width: 640px) {
            .two-column-layout {
                padding: 1rem;
            }
            .form-grid { 
                grid-template-columns: 1fr; 
            }
            .form-group.full-width { 
                grid-column: span 1; 
            }
            .date-range { 
                grid-template-columns: 1fr; 
            }
            .radio-option {
                min-width: auto;
            }
        }
    </style>

    <div class="two-column-layout">
        
        <form action="{{ route('archives.update', $archive->id) }}" method="POST" id="archiveForm">
            @csrf
            @method('PUT')
        </form>

        <!-- KOLOM KIRI - MAIN FORM -->
        <div class="main-form">
            <div class="mb-6 flex items-center gap-3">
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

            <!-- SECTION 1: RECORD IDENTITY -->
            <div class="form-section">
                <div class="section-header">
                    <div class="section-title">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14"/>
                        </svg>
                        Record Identity
                    </div>
                </div>
                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label required">Record ID</label>
                        <input type="text" name="record_id" form="archiveForm" value="{{ old('record_id', $archive->record_id) }}" class="form-input" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">External Doc Number</label>
                        <input type="text" name="doc_number" form="archiveForm" value="{{ old('doc_number', $archive->doc_number) }}" class="form-input" placeholder="e.g. 001/GNI/LEGAL/2024">
                    </div>
                    <div class="form-group full-width">
                        <label class="form-label required">Document Name</label>
                        <input type="text" name="doc_name" form="archiveForm" value="{{ old('doc_name', $archive->doc_name) }}" class="form-input" required>
                    </div>
                </div>
            </div>

            <!-- SECTION 2: CLASSIFICATION -->
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
                    <div class="form-group">
                        <label class="form-label required">Company</label>
                        <select name="company" form="archiveForm" class="form-select" required>
                            <option value="GNI" {{ old('company', $archive->company) == 'GNI' ? 'selected' : '' }}>GNI - Gunbuster Nickel Industry</option>
                            <option value="AMI" {{ old('company', $archive->company) == 'AMI' ? 'selected' : '' }}>AMI - Adhi Kartiko Pratama</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label required">Department</label>
                        <select name="department" form="archiveForm" class="form-select" required>
                            @foreach(['LG'=>'Legal','HR'=>'HRD','OP'=>'Operation','AC'=>'Accounting','FN'=>'Finance','TX'=>'Tax','EX'=>'Exim','CC'=>'CorCom','NP'=>'Nickel Ore','HE'=>'HSE','CP'=>'Coal','SL'=>'Sales','PC'=>'Purchasing','IT'=>'IT','GA'=>'GA','DK'=>'Direksi'] as $code => $name)
                                <option value="{{ $code }}" {{ old('department', $archive->department_code) == $code ? 'selected' : '' }}>{{ $code }} - {{ $name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group full-width">
                        <label class="form-label required">Document Type</label>
                        <select name="doc_type" form="archiveForm" class="form-select" required>
                            @foreach($docTypes as $code => $label)
                                <option value="{{ $code }}" {{ old('doc_type', $archive->doc_type) == $code ? 'selected' : '' }}>{{ $code }} — {{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <!-- SECTION 3: DETAILS & STATUS -->
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
                        <label class="form-label">Counterparty</label>
                        <input type="text" name="counterparty" form="archiveForm" value="{{ old('counterparty', $archive->counterparty) }}" class="form-input" placeholder="Vendor / Partner name">
                    </div>
                    <div class="form-group full-width">
                        <label class="form-label">Description</label>
                        <textarea name="description" form="archiveForm" class="form-textarea" rows="3" placeholder="Brief description of the document">{{ old('description', $archive->description) }}</textarea>
                    </div>
                    <div class="form-group full-width">
                        <label class="form-label text-xs uppercase tracking-wider text-cyan-500 font-bold mt-2">Document Status</label>
                        <div class="radio-group">
                            @php
                                $currentStatus = old('doc_status', $archive->doc_status ?? []);
                                if (is_string($currentStatus)) {
                                    $currentStatus = json_decode($currentStatus, true) ?? [];
                                }
                            @endphp
                            @foreach(\App\Models\Archive::DOC_STATUS as $status)
                                <div class="radio-option">
                                    <input type="checkbox" name="doc_status[]" form="archiveForm" id="st_{{ $status }}" value="{{ $status }}" {{ in_array($status, $currentStatus) ? 'checked' : '' }}>
                                    <label for="st_{{ $status }}" class="radio-label">
                                        {{ \App\Models\Archive::DOC_STATUS_LABEL[$status] }}
                                    </label>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    <div class="form-group full-width">
                        <label class="form-label text-xs uppercase tracking-wider text-cyan-500 font-bold mt-2">Version Control</label>
                        <div class="radio-group">
                            @foreach(['latest' => 'Latest', 'obsolete' => 'Obsolete', 'superseded' => 'Superseded'] as $val => $lbl)
                                <div class="radio-option">
                                    <input type="radio" name="version_status" form="archiveForm" id="vs_{{ $val }}" value="{{ $val }}" {{ old('version_status', $archive->version_status) == $val ? 'checked' : '' }} required>
                                    <label for="vs_{{ $val }}" class="radio-label">{{ $lbl }}</label>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            <!-- SECTION 4: VALIDITY & STORAGE -->
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
                        <input type="date" name="start_date" form="archiveForm" value="{{ old('start_date', $archive->start_date ? $archive->start_date->format('Y-m-d') : '') }}" class="form-input">
                    </div>
                    <div class="form-group">
                        <label class="form-label">End Date</label>
                        <input type="date" name="end_date" form="archiveForm" value="{{ old('end_date', $archive->end_date ? $archive->end_date->format('Y-m-d') : '') }}" class="form-input">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Physical Location</label>
                        <input type="text" name="doc_location" form="archiveForm" value="{{ old('doc_location', $archive->doc_location) }}" class="form-input" placeholder="e.g. Cabinet B, Shelf 4">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Digital Path</label>
                        <input type="text" name="synology_path" form="archiveForm" value="{{ old('synology_path', $archive->synology_path) }}" class="form-input" placeholder="/volume1/Legal/...">
                    </div>
                </div>
            </div>

            <!-- FORM ACTIONS -->
            <div class="form-actions">
                <a href="{{ route('archives.index') }}" class="btn-secondary">Cancel</a>
                <button type="submit" form="archiveForm" class="btn-primary">Update Archive</button>
            </div>
        </div>

        <!-- KOLOM KANAN - CROSS REFERENCES (STATIC, DIGESER TURUN) -->
        <div class="info-sidebar">
            <div class="cross-ref-section">
                <div class="cross-ref-header">
                    <div class="cross-ref-title">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/>
                        </svg>
                        Cross References
                    </div>
                    <span class="badge-optional">Optional</span>
                </div>

                <div class="cross-ref-body" id="crossRefContainer">
                    @php
                        $hasOldData = old('ref_doc_name') !== null;
                        $oldRefDocNames = old('ref_doc_name', []);
                        $oldRefRecordIds = old('ref_record_id', []);
                        $oldRefLocations = old('ref_location', []);
                        $oldRefRelations = old('ref_relation', []);
                        
                        if ($hasOldData) {
                            $refs = [];
                            foreach ($oldRefDocNames as $i => $docName) {
                                if (!empty(trim($docName))) {
                                    $refs[] = (object)[
                                        'ref_doc_name' => $docName,
                                        'ref_record_id' => $oldRefRecordIds[$i] ?? '',
                                        'ref_location' => $oldRefLocations[$i] ?? '',
                                        'ref_relation' => $oldRefRelations[$i] ?? '',
                                    ];
                                }
                            }
                        } else {
                            $refs = $archive->crossReferences ?? collect();
                        }
                    @endphp

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
                                <input type="text" form="archiveForm" 
                                       name="ref_doc_name[]" 
                                       class="cross-ref-input"
                                       value="{{ $ref->ref_doc_name ?? '' }}"
                                       placeholder="e.g., Amendment Agreement No. 3">
                            </div>
                            <div class="cross-ref-field">
                                <label class="cross-ref-label">Record ID</label>
                                <input type="text" form="archiveForm" 
                                       name="ref_record_id[]" 
                                       class="cross-ref-input"
                                       value="{{ $ref->ref_record_id ?? '' }}"
                                       placeholder="e.g., 24LG-CT-001">
                            </div>
                            <div class="cross-ref-field">
                                <label class="cross-ref-label">Location</label>
                                <input type="text" form="archiveForm" 
                                       name="ref_location[]" 
                                       class="cross-ref-input"
                                       value="{{ $ref->ref_location ?? '' }}"
                                       placeholder="e.g., Cabinet B2">
                            </div>
                            <div class="cross-ref-field">
                                <label class="cross-ref-label">Description / Notes</label>
                                <textarea form="archiveForm" 
                                          name="ref_relation[]" 
                                          class="cross-ref-textarea"
                                          placeholder="Additional notes about this reference...">{{ $ref->ref_relation ?? '' }}</textarea>
                            </div>
                        </div>
                    </div>
                    @empty
                    <div class="cross-ref-card" data-card>
                        <div class="cross-ref-card-header">
                            <span class="cross-ref-badge-num">1</span>
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
                                <input type="text" form="archiveForm" name="ref_doc_name[]" class="cross-ref-input" placeholder="e.g., Amendment Agreement No. 3">
                            </div>
                            <div class="cross-ref-field">
                                <label class="cross-ref-label">Record ID</label>
                                <input type="text" form="archiveForm" name="ref_record_id[]" class="cross-ref-input" placeholder="e.g., 24LG-CT-001">
                            </div>
                            <div class="cross-ref-field">
                                <label class="cross-ref-label">Location</label>
                                <input type="text" form="archiveForm" name="ref_location[]" class="cross-ref-input" placeholder="e.g., Cabinet B2">
                            </div>
                            <div class="cross-ref-field">
                                <label class="cross-ref-label">Description / Notes</label>
                                <textarea form="archiveForm" name="ref_relation[]" class="cross-ref-textarea" placeholder="Additional notes about this reference..."></textarea>
                            </div>
                        </div>
                    </div>
                    @endforelse
                </div>

                <div style="padding: 0 1.125rem 1.125rem 1.125rem;">
                    <button type="button" id="addCrossRefBtn" class="btn-add-ref">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        Add Another Reference
                    </button>
                </div>
            </div>
        </div>

    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Cross References - Dynamic Cards
            const container = document.getElementById('crossRefContainer');
            const addBtn = document.getElementById('addCrossRefBtn');

            function reNumberCards() {
                const cards = container.querySelectorAll('.cross-ref-card');
                cards.forEach((card, idx) => {
                    const badge = card.querySelector('.cross-ref-badge-num');
                    if (badge) badge.textContent = idx + 1;
                });
            }

            function attachRemoveHandler(card) {
                const removeBtn = card.querySelector('.remove-cross-ref');
                if (removeBtn) {
                    removeBtn.addEventListener('click', function() {
                        const cards = container.querySelectorAll('.cross-ref-card');
                        if (cards.length === 1) {
                            // Clear all inputs instead of removing last card
                            card.querySelectorAll('input, textarea').forEach(field => {
                                field.value = '';
                            });
                        } else {
                            card.remove();
                            reNumberCards();
                        }
                    });
                }
            }

            // Attach handlers to existing cards
            document.querySelectorAll('.cross-ref-card').forEach(card => {
                attachRemoveHandler(card);
            });

            // Add new card function
            function addNewCard() {
                const cardCount = container.querySelectorAll('.cross-ref-card').length;
                const newCard = document.createElement('div');
                newCard.className = 'cross-ref-card';
                newCard.setAttribute('data-card', '');
                newCard.innerHTML = `
                    <div class="cross-ref-card-header">
                        <span class="cross-ref-badge-num">${cardCount + 1}</span>
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
                            <input type="text" form="archiveForm" name="ref_doc_name[]" class="cross-ref-input" placeholder="e.g., Amendment Agreement No. 3">
                        </div>
                        <div class="cross-ref-field">
                            <label class="cross-ref-label">Record ID</label>
                            <input type="text" form="archiveForm" name="ref_record_id[]" class="cross-ref-input" placeholder="e.g., 24LG-CT-001">
                        </div>
                        <div class="cross-ref-field">
                            <label class="cross-ref-label">Location</label>
                            <input type="text" form="archiveForm" name="ref_location[]" class="cross-ref-input" placeholder="e.g., Cabinet B2">
                        </div>
                        <div class="cross-ref-field">
                            <label class="cross-ref-label">Description / Notes</label>
                            <textarea form="archiveForm" name="ref_relation[]" class="cross-ref-textarea" placeholder="Additional notes about this reference..."></textarea>
                        </div>
                    </div>
                `;
                container.appendChild(newCard);
                attachRemoveHandler(newCard);
                newCard.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            }

            if (addBtn) {
                addBtn.addEventListener('click', addNewCard);
            }
        });
    </script>
</x-app-layout-dark>