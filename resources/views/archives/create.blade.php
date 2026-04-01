<x-app-layout-dark title="Create New Archive">

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

        .main-form {
            min-width: 0;
        }

        .cross-ref-sidebar {
            position: sticky;
            top: 1.5rem;
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

        .section-title svg { width: 1.1rem; height: 1.1rem; color: #0ea5e9; }

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

        .form-group.full-width { grid-column: span 2; }

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

        .form-select option { background: #0f172a; color: white; }

        .radio-group {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
            background: rgba(15, 23, 42, 0.4);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 0.625rem;
            padding: 0.625rem 0.875rem;
        }

        .radio-option {
            display: flex;
            align-items: center;
            gap: 0.4rem;
            cursor: pointer;
        }

        .radio-option input {
            width: 0.9rem;
            height: 0.9rem;
            accent-color: #0ea5e9;
            cursor: pointer;
        }

        .radio-option span { color: #d1d5db; font-size: 0.8125rem; }

        .help-text { font-size: 0.7rem; color: #6b7280; }

        .date-range {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 0.75rem;
        }

        .record-id-wrap {
            display: flex;
            gap: 0.625rem;
        }

        .record-id-wrap .form-input { flex: 1; }

        /* ============================================================
           FORM ACTIONS BAR
        ============================================================ */
        .form-actions {
            display: flex;
            align-items: center;
            justify-content: flex-end;
            gap: 1rem;
            padding: 1.125rem 1.5rem;
            border-top: 1px solid rgba(255,255,255,0.05);
        }

        .btn-submit {
            background: linear-gradient(135deg, #0ea5e9, #3b82f6);
            color: white;
            border: none;
            border-radius: 0.75rem;
            padding: 0.7rem 1.75rem;
            font-weight: 600;
            font-size: 0.875rem;
            cursor: pointer;
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .btn-submit:hover { transform: translateY(-1px); box-shadow: 0 4px 14px rgba(14,165,233,0.35); }

        .btn-cancel {
            background: rgba(255,255,255,0.04);
            border: 1px solid rgba(255,255,255,0.1);
            border-radius: 0.75rem;
            padding: 0.7rem 1.5rem;
            color: #9ca3af;
            font-size: 0.875rem;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.2s;
        }

        .btn-cancel:hover { background: rgba(255,255,255,0.08); color: white; }

        /* Generate buttons */
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

        .generate-btn:hover { transform: translateY(-1px); box-shadow: 0 4px 12px rgba(14,165,233,0.3); }
        .generate-btn:disabled { opacity: 0.6; cursor: not-allowed; transform: none; }

        /* Alert */
        .alert {
            padding: 0.875rem 1.25rem;
            border-radius: 0.875rem;
            margin-bottom: 1.25rem;
            display: flex;
            align-items: flex-start;
            gap: 0.75rem;
        }

        .alert-error {
            background: rgba(239,68,68,0.1);
            border: 1px solid rgba(239,68,68,0.3);
            color: #f87171;
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

        @keyframes spin { to { transform: rotate(360deg); } }

        /* ============================================================
           CROSS REFERENCE — SIDEBAR STYLE
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

        .cross-ref-title svg { width: 1.1rem; height: 1.1rem; color: #a78bfa; }

        .badge-optional {
            font-size: 0.65rem;
            font-weight: 500;
            padding: 0.15rem 0.55rem;
            background: rgba(139, 92, 246, 0.15);
            border: 1px solid rgba(139, 92, 246, 0.3);
            color: #c084fc;
            border-radius: 9999px;
        }

        .cross-ref-body { padding: 1.125rem; }

        /* Cross reference card */
        .cross-ref-card {
            background: rgba(15, 23, 42, 0.5);
            border: 1px solid rgba(139, 92, 246, 0.15);
            border-radius: 1rem;
            padding: 1rem;
            margin-bottom: 1rem;
            transition: border-color 0.2s ease;
        }

        .cross-ref-card:hover { border-color: rgba(139, 92, 246, 0.3); }

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

        .btn-remove-card:hover { background: rgba(239, 68, 68, 0.2); border-color: rgba(239, 68, 68, 0.4); }

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

        .cross-ref-label.required::after { content: ' *'; color: #ef4444; }

        .cross-ref-input,
        .cross-ref-textarea,
        .cross-ref-select {
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
        .cross-ref-textarea:focus,
        .cross-ref-select:focus {
            outline: none;
            border-color: #a78bfa;
            box-shadow: 0 0 0 2px rgba(139, 92, 246, 0.1);
        }

        .cross-ref-textarea {
            resize: vertical;
            min-height: 60px;
        }

        .cross-ref-select option { background: #0f172a; color: white; }

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

        .btn-add-ref:hover { background: rgba(139, 92, 246, 0.15); border-color: rgba(139, 92, 246, 0.6); }

        /* Responsive */
        @media (max-width: 1024px) {
            .two-column-layout {
                grid-template-columns: 1fr;
            }
            .cross-ref-sidebar {
                position: static;
            }
        }

        @media (max-width: 640px) {
            .form-grid { grid-template-columns: 1fr; }
            .form-group.full-width { grid-column: span 1; }
            .date-range { grid-template-columns: 1fr; }
        }
    </style>

    <div class="two-column-layout">

        <!-- LEFT COLUMN — MAIN FORM -->
        <div class="main-form">
            <!-- Page Header -->
            <div class="mb-6 flex items-center gap-3">
                <div class="p-3 rounded-xl bg-gradient-to-br from-cyan-500/10 to-blue-600/10 border border-cyan-500/20">
                    <svg class="w-6 h-6 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
                <div>
                    <h1 class="text-2xl font-bold bg-gradient-to-r from-cyan-400 via-blue-400 to-cyan-400 bg-clip-text text-transparent">
                        Create New Archive
                    </h1>
                    <p class="text-gray-400 text-sm mt-0.5">Register a new legal document in the archive system</p>
                </div>
            </div>

            <!-- Validation Errors -->
            @if($errors->any())
            <div class="alert alert-error">
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

            <form action="{{ route('archives.store') }}" method="POST" id="archiveForm">
                @csrf

                <!-- SECTION 1: RECORD IDENTIFICATION -->
                <div class="form-section">
                    <div class="section-header">
                        <div class="section-title">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14"/>
                            </svg>
                            Record Identification
                        </div>
                    </div>
                    <div class="form-grid">
                        <div class="form-group">
                            <label class="form-label required">Record ID</label>
                            <div class="record-id-wrap">
                                <input type="text"
                                       name="record_id"
                                       id="record_id"
                                       class="form-input"
                                       value="{{ old('record_id') }}"
                                       placeholder="Auto-generated"
                                       required>
                                <button type="button" id="generateRecordIdBtn" class="generate-btn">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                    </svg>
                                    Generate
                                </button>
                            </div>
                            <p class="help-text">Select Company, Year, Department & Type first</p>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Document Number</label>
                            <input type="text"
                                   name="doc_number"
                                   class="form-input"
                                   value="{{ old('doc_number') }}"
                                   placeholder="External document number">
                        </div>

                        <div class="form-group full-width">
                            <label class="form-label required">Document Name</label>
                            <input type="text"
                                   name="doc_name"
                                   class="form-input"
                                   value="{{ old('doc_name') }}"
                                   placeholder="Full document title / name"
                                   required>
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
                            Document Classification
                        </div>
                    </div>
                    <div class="form-grid">
                        <div class="form-group">
                            <label class="form-label required">Company</label>
                            <select name="company" id="companySelect" class="form-select" required>
                                <option value="">Select Company</option>
                                <option value="GNI" {{ old('company') == 'GNI' ? 'selected' : '' }}>GNI</option>
                                <option value="AMI" {{ old('company') == 'AMI' ? 'selected' : '' }}>AMI</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label class="form-label required">Year (for Record ID)</label>
                            <select id="yearSelect" class="form-select">
                                <option value="">Select Year</option>
                                @for($year = date('Y'); $year >= date('Y') - 20; $year--)
                                    <option value="{{ $year }}" {{ date('Y') == $year ? 'selected' : '' }}>
                                        {{ $year }}
                                    </option>
                                @endfor
                            </select>
                        </div>

                        <div class="form-group">
                            <label class="form-label required">Department</label>
                            <select name="department" id="departmentSelect" class="form-select" required>
                                <option value="">Select Department</option>
                                @foreach($departments as $code => $name)
                                    <option value="{{ $code }}" {{ old('department') == $code ? 'selected' : '' }}>
                                        {{ $code }} — {{ $name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group">
                            <label class="form-label required">Document Type</label>
                            <select name="doc_type" id="docTypeSelect" class="form-select" required>
                                <option value="">Select Document Type</option>
                                @foreach($docTypes as $code => $label)
                                    <option value="{{ $code }}" {{ old('doc_type') == $code ? 'selected' : '' }}>
                                        {{ $code }} — {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                <!-- SECTION 3: DOCUMENT DETAILS -->
                <div class="form-section">
                    <div class="section-header">
                        <div class="section-title">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            Document Details
                        </div>
                    </div>
                    <div class="form-grid">
                        <div class="form-group">
                            <label class="form-label">Counterparty</label>
                            <input type="text"
                                   name="counterparty"
                                   class="form-input"
                                   value="{{ old('counterparty') }}"
                                   placeholder="Other party involved">
                        </div>

                        <div class="form-group">
                            <label class="form-label">Description</label>
                            <textarea name="description"
                                      class="form-textarea"
                                      rows="3"
                                      placeholder="Brief description of the document">{{ old('description') }}</textarea>
                        </div>

                        <div class="form-group">
                            <label class="form-label required">Document Status</label>
                            <div class="radio-group">
                            @foreach($docStatus as $statusValue)
                            <label class="radio-option">
                            <input type="checkbox"
                                name="doc_status[]"
                                value="{{ $statusValue }}"
                                {{ is_array(old('doc_status')) && in_array($statusValue, old('doc_status', [])) ? 'checked' : '' }}>
                            <span>{{ \App\Models\Archive::DOC_STATUS_LABEL[$statusValue] }}</span>
                            </label>
                            @endforeach
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="form-label required">Version Status</label>
                            <div class="radio-group">
                                @foreach($versionStatus as $versionValue)
                                <label class="radio-option">
                                    <input type="radio"
                                           name="version_status"
                                           value="{{ $versionValue }}"
                                           {{ old('version_status', 'latest') == $versionValue ? 'checked' : '' }}
                                           required>
                                    <span>{{ \App\Models\Archive::VERSION_STATUS_LABEL[$versionValue] ?? ucfirst($versionValue) }}</span>
                                </label>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>

                <!-- SECTION 4: VALIDITY & LOCATION -->
                <div class="form-section">
                    <div class="section-header">
                        <div class="section-title">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                            Validity Period & Location
                        </div>
                    </div>
                    <div class="form-grid">
                        <div class="form-group full-width">
                            <label class="form-label">Validity Period</label>
                            <div class="date-range">
                                <div>
                                    <p class="help-text mb-1">Start Date</p>
                                    <input type="date" name="start_date" class="form-input" value="{{ old('start_date') }}">
                                </div>
                                <div>
                                    <p class="help-text mb-1">End Date</p>
                                    <input type="date" name="end_date" class="form-input" value="{{ old('end_date') }}">
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Physical Location</label>
                            <input type="text"
                                   name="doc_location"
                                   class="form-input"
                                   value="{{ old('doc_location') }}"
                                   placeholder="e.g., Cabinet A1, Shelf 3">
                        </div>

                        <div class="form-group">
                            <label class="form-label">Digital Path (Synology)</label>
                            <input type="text"
                                   name="synology_path"
                                   class="form-input"
                                   value="{{ old('synology_path') }}"
                                   placeholder="/volume1/legal/2024/contracts/">
                        </div>
                    </div>
                </div>

                <!-- FORM ACTIONS -->
                <div class="form-section">
                    <div class="form-actions">
                        <a href="{{ route('archives.index') }}" class="btn-cancel">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                            Cancel
                        </a>
                        <button type="submit" class="btn-submit">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            Create Archive
                        </button>
                    </div>
                </div>
            </div>

            <!-- RIGHT COLUMN — CROSS REFERENCES (OPTIONAL) -->
            <div class="cross-ref-sidebar">
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

                    <div class="cross-ref-body">
                        <p class="text-sm text-gray-500 mb-4">
                            Link this archive to related external documents.
                        </p>

                        <!-- Container untuk cross reference cards -->
                        <div id="crossRefContainer">
                            @if(old('ref_doc_name'))
                                @foreach(old('ref_doc_name') as $i => $val)
                                <div class="cross-ref-card" data-card>
                                    <div class="cross-ref-card-header">
                                        <span class="cross-ref-badge-num">{{ $i + 1 }}</span>
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
                                            <input type="text" name="ref_doc_name[]" class="cross-ref-input"
                                                value="{{ $val }}" placeholder="e.g., Amendment Agreement No. 3">
                                        </div>
                                        <div class="cross-ref-field">
                                            <label class="cross-ref-label">Record ID</label>
                                            <input type="text" name="ref_record_id[]" class="cross-ref-input"
                                                value="{{ old('ref_record_id')[$i] ?? '' }}" placeholder="e.g., 24LG-CT-001">
                                        </div>
                                        <div class="cross-ref-field">
                                            <label class="cross-ref-label">Location</label>
                                            <input type="text" name="ref_location[]" class="cross-ref-input"
                                                value="{{ old('ref_location')[$i] ?? '' }}" placeholder="e.g., Cabinet B2, /volume1/legal/">
                                        </div>
                                        <div class="cross-ref-field">
                                            <label class="cross-ref-label">Description / Notes</label>
                                            <textarea name="ref_relation[]" class="cross-ref-textarea"
                                                    placeholder="Additional notes about this reference...">{{ old('ref_relation.0') ?? '' }}</textarea>
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            @else
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
                                            <input type="text" name="ref_doc_name[]" class="cross-ref-input"
                                                placeholder="e.g., Amendment Agreement No. 3">
                                        </div>
                                        <div class="cross-ref-field">
                                            <label class="cross-ref-label">Record ID</label>
                                            <input type="text" name="ref_record_id[]" class="cross-ref-input"
                                                placeholder="e.g., 24LG-CT-001">
                                        </div>
                                        <div class="cross-ref-field">
                                            <label class="cross-ref-label">Location</label>
                                            <input type="text" name="ref_location[]" class="cross-ref-input"
                                                placeholder="e.g., Cabinet B2">
                                        </div>
                                        <div class="cross-ref-field">
                                            <label class="cross-ref-label">Description / Notes</label>
                                            <textarea name="ref_relation[]" class="cross-ref-textarea"
                                                    placeholder="...">{{ old('ref_relation.0') ?? '' }}</textarea>
                                        </div>
                                    </div>
                                </div>
                            @endif
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
        </form>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function () {

        // ==========================================
        // RECORD ID GENERATOR
        // ==========================================
        const generateBtn      = document.getElementById('generateRecordIdBtn');
        const recordIdInput    = document.getElementById('record_id');
        const companySelect    = document.getElementById('companySelect');
        const yearSelect       = document.getElementById('yearSelect');
        const departmentSelect = document.getElementById('departmentSelect');
        const docTypeSelect    = document.getElementById('docTypeSelect');

        generateBtn?.addEventListener('click', async function () {
            const company    = companySelect?.value;
            const year       = yearSelect?.value;
            const department = departmentSelect?.value;
            const docType    = docTypeSelect?.value;

            if (!company || !year || !department || !docType) {
                alert('Please select Company, Year, Department, and Document Type first.');
                return;
            }

            const originalHTML    = generateBtn.innerHTML;
            generateBtn.innerHTML = '<span class="loading-spinner"></span> Generating...';
            generateBtn.disabled  = true;

            try {
                const response = await fetch('{{ route("archives.generate-record-id") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ company, year, department, doc_type: docType })
                });

                const data = await response.json();

                if (data.record_id) {
                    recordIdInput.value = data.record_id;
                } else {
                    alert(data.message || 'Failed to generate Record ID. Please try again.');
                }
            } catch (err) {
                console.error(err);
                alert('An error occurred. Please try again.');
            } finally {
                generateBtn.innerHTML = originalHTML;
                generateBtn.disabled  = false;
            }
        });

        // ==========================================
        // DATE RANGE VALIDATION
        // ==========================================
        const startDate = document.querySelector('input[name="start_date"]');
        const endDate   = document.querySelector('input[name="end_date"]');

        function validateDates() {
            if (startDate.value && endDate.value && new Date(endDate.value) < new Date(startDate.value)) {
                endDate.setCustomValidity('End date must be after start date');
            } else {
                endDate.setCustomValidity('');
            }
        }

        startDate?.addEventListener('change', validateDates);
        endDate?.addEventListener('change',   validateDates);

        // ==========================================
        // FORM SUBMIT VALIDATION
        // ==========================================
        document.getElementById('archiveForm')?.addEventListener('submit', function (e) {
            if (!recordIdInput?.value.trim()) {
                e.preventDefault();
                alert('Please generate or enter a Record ID first.');
                return;
            }
            if (!document.querySelector('input[name="doc_status[]"]:checked')) {
                e.preventDefault();
                alert('Please select a Document Status.');
                return;
            }
            if (!document.querySelector('input[name="version_status"]:checked')) {
                e.preventDefault();
                alert('Please select a Version Status.');
                return;
            }
        });

        // ==========================================
        // CROSS REFERENCES — Dynamic Cards
        // ==========================================
        const container = document.getElementById('crossRefContainer');
        const addBtn    = document.getElementById('addCrossRefBtn');

        function reNumber() {
            container.querySelectorAll('[data-card] .cross-ref-badge-num').forEach(function (badge, i) {
                badge.textContent = i + 1;
            });
        }

        function attachRemove(card) {
            card.querySelector('.remove-cross-ref')?.addEventListener('click', function () {
                const cards = container.querySelectorAll('[data-card]');
                if (cards.length <= 1) {
                    // Kosongkan isi card
                    card.querySelectorAll('input').forEach(function (inp) { inp.value = ''; });
                    card.querySelectorAll('textarea').forEach(function (ta) { ta.value = ''; });
                    return;
                }
                card.remove();
                reNumber();
            });
        }

        // Attach ke card yang sudah ada
        container.querySelectorAll('[data-card]').forEach(attachRemove);

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
                        <input type="text" name="ref_doc_name[]" class="cross-ref-input"
                               placeholder="e.g., Amendment Agreement No. 3">
                    </div>
                    <div class="cross-ref-field">
                        <label class="cross-ref-label">Record ID</label>
                        <input type="text" name="ref_record_id[]" class="cross-ref-input"
                               placeholder="e.g., 24LG-CT-001">
                    </div>
                    <div class="cross-ref-field">
                        <label class="cross-ref-label">Location</label>
                        <input type="text" name="ref_location[]" class="cross-ref-input"
                               placeholder="e.g., Cabinet B2">
                    </div>
                    <div class="cross-ref-field">
                        <label class="cross-ref-label">Description / Notes</label>
                        <textarea name="ref_relation[]" class="cross-ref-textarea"
                                  placeholder="Additional notes about this reference..."></textarea>
                    </div>
                </div>
            `;
            return div;
        }

        addBtn?.addEventListener('click', function () {
            const num  = container.querySelectorAll('[data-card]').length + 1;
            const card = buildCard(num);
            attachRemove(card);
            container.appendChild(card);
            card.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        });

    });
    </script>

</x-app-layout-dark>