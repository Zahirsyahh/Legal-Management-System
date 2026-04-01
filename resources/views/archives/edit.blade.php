<x-app-layout-dark title="Edit Archive">

    <style>
        /* ============================================================
           TWO COLUMN LAYOUT (Sama persis dengan Create)
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

        /* ============================================================
           RADIO & CHECKBOX GROUPS (Modern Style)
        ============================================================ */
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

        /* ============================================================
           CROSS REFERENCE SIDEBAR CARDS
        ============================================================ */
        .ref-card {
            background: rgba(15, 23, 42, 0.4);
            border: 1px solid rgba(255, 255, 255, 0.05);
            border-radius: 1rem;
            padding: 1rem;
            margin-bottom: 1rem;
            position: relative;
            transition: all 0.2s;
        }

        .ref-card:hover {
            border-color: rgba(14, 165, 233, 0.3);
            background: rgba(15, 23, 42, 0.6);
        }

        .remove-ref-btn {
            position: absolute;
            top: 0.5rem;
            right: 0.5rem;
            color: #6b7280;
            padding: 0.25rem;
            border-radius: 0.375rem;
            transition: all 0.2s;
        }

        .remove-ref-btn:hover {
            color: #ef4444;
            background: rgba(239, 68, 68, 0.1);
        }

        .add-ref-btn {
            width: 100%;
            padding: 0.75rem;
            background: rgba(14, 165, 233, 0.05);
            border: 1px dashed rgba(14, 165, 233, 0.3);
            border-radius: 1rem;
            color: #0ea5e9;
            font-size: 0.875rem;
            font-weight: 500;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            transition: all 0.2s;
        }

        .add-ref-btn:hover {
            background: rgba(14, 165, 233, 0.1);
            border-color: #0ea5e9;
        }

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
        }

        @media (max-width: 1024px) {
            .two-column-layout { grid-template-columns: 1fr; }
            .cross-ref-sidebar { position: static; }
        }
    </style>

    <div class="two-column-layout">
        <div class="main-form">
            <div class="mb-6">
                <h1 class="text-2xl font-bold text-white">Edit Archive</h1>
                <p class="text-gray-400 text-sm mt-1">Update existing document metadata and classification</p>
            </div>

            <form action="{{ route('archives.update', $archive->id) }}" method="POST" id="archiveForm">
                @csrf
                @method('PUT')

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
                            <input type="text" name="record_id" value="{{ old('record_id', $archive->record_id) }}" class="form-input" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">External Doc Number</label>
                            <input type="text" name="doc_number" value="{{ old('doc_number', $archive->doc_number) }}" class="form-input" placeholder="e.g. 001/GNI/LEGAL/2024">
                        </div>
                        <div class="form-group full-width">
                            <label class="form-label required">Document Name</label>
                            <input type="text" name="doc_name" value="{{ old('doc_name', $archive->doc_name) }}" class="form-input" required>
                        </div>
                    </div>
                </div>

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
                            <select name="company" class="form-select" required>
                                <option value="GNI" {{ old('company', $archive->company) == 'GNI' ? 'selected' : '' }}>GNI - Gunbuster Nickel Industry</option>
                                <option value="AMI" {{ old('company', $archive->company) == 'AMI' ? 'selected' : '' }}>AMI - Adhi Kartiko Pratama</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label required">Department</label>
                            <select name="department" class="form-select" required>
                                @foreach(['LG'=>'Legal','HR'=>'HRD','OP'=>'Operation','AC'=>'Accounting','FN'=>'Finance','TX'=>'Tax','EX'=>'Exim','CC'=>'CorCom','NP'=>'Nickel Ore','HE'=>'HSE','CP'=>'Coal','SL'=>'Sales','PC'=>'Purchasing','IT'=>'IT','GA'=>'GA','DK'=>'Direksi'] as $code => $name)
                                    <option value="{{ $code }}" {{ old('department', $archive->department) == $code ? 'selected' : '' }}>{{ $code }} - {{ $name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group full-width">
                            <label class="form-label required">Document Type</label>
                            <select name="doc_type" class="form-select" required>
                                @foreach($docTypes as $code => $label)
                                    <option value="{{ $code }}" {{ old('doc_type', $archive->doc_type) == $code ? 'selected' : '' }}>{{ $code }} — {{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

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
                            <input type="text" name="counterparty" value="{{ old('counterparty', $archive->counterparty) }}" class="form-input" placeholder="Vendor / Partner name">
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
                                        <input type="checkbox" name="doc_status[]" id="st_{{ $status }}" value="{{ $status }}" {{ in_array($status, $currentStatus) ? 'checked' : '' }}>
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
                                        <input type="radio" name="version_status" id="vs_{{ $val }}" value="{{ $val }}" {{ old('version_status', $archive->version_status) == $val ? 'checked' : '' }} required>
                                        <label for="vs_{{ $val }}" class="radio-label">{{ $lbl }}</label>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>

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
                            <input type="date" name="start_date" value="{{ old('start_date', $archive->start_date ? $archive->start_date->format('Y-m-d') : '') }}" class="form-input">
                        </div>
                        <div class="form-group">
                            <label class="form-label">End Date</label>
                            <input type="date" name="end_date" value="{{ old('end_date', $archive->end_date ? $archive->end_date->format('Y-m-d') : '') }}" class="form-input">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Physical Location</label>
                            <input type="text" name="doc_location" value="{{ old('doc_location', $archive->doc_location) }}" class="form-input" placeholder="e.g. Cabinet B, Shelf 4">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Digital Path</label>
                            <input type="text" name="synology_path" value="{{ old('synology_path', $archive->synology_path) }}" class="form-input" placeholder="/volume1/Legal/...">
                        </div>
                    </div>
                </div>

                <div class="form-actions">
                    <a href="{{ route('archives.index') }}" class="btn-secondary">Cancel</a>
                    <button type="submit" class="btn-primary">Update Archive</button>
                </div>
        </div>

        <div class="cross-ref-sidebar">
            <div class="form-section">
                <div class="section-header">
                    <div class="section-title">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/>
                        </svg>
                        Cross References
                    </div>
                </div>
                
                <div class="p-4" id="crossRefContainer">
                    @php
                        $refs = $archive->crossReferences ?? [];
                    @endphp
                    @foreach($refs as $index => $ref)
                    <div class="ref-card" id="ref_card_{{ $loop->index }}">
                        <button type="button" class="remove-ref-btn" onclick="this.parentElement.remove()">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M6 18L18 6M6 6l12 12" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        </button>
                        <div class="space-y-3">
                            <div>
                                <label class="text-[10px] uppercase font-bold text-gray-500 mb-1 block">Document Name</label>
                                <input type="text" name="ref_doc_name[]" value="{{ old('ref_doc_name.'.$index, $ref->ref_doc_name) }}" class="form-input" placeholder="Document Name">
                            </div>
                            <div class="grid grid-cols-2 gap-2">
                                <div>
                                    <label class="text-[10px] uppercase font-bold text-gray-500 mb-1 block">Record ID</label>
                                    <input type="text" name="ref_record_id[]" value="{{ old('ref_record_id.'.$index, $ref->ref_record_id) }}" class="form-input" placeholder="ID">
                                </div>
                                <div>
                                    <label class="text-[10px] uppercase font-bold text-gray-500 mb-1 block">Relation</label>
                                    <input type="text" name="ref_relation[]" value="{{ old('ref_relation.'.$index, $ref->ref_relation) }}" class="form-input" placeholder="e.g. Amendment">
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>

                <div class="p-4 pt-0">
                    <button type="button" class="add-ref-btn" onclick="addCrossRef()">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        Add Related Document
                    </button>
                </div>
            </div>

            @if($errors->any())
            <div class="bg-red-500/10 border border-red-500/20 rounded-2xl p-4 mt-4">
                <div class="flex gap-2 text-red-400 mb-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span class="font-bold text-sm">Validation Errors</span>
                </div>
                <ul class="text-xs text-red-400/80 list-disc list-inside space-y-1">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif
        </div>
        </form>
    </div>

    <script>
        function addCrossRef() {
            const container = document.getElementById('crossRefContainer');
            const newCard = document.createElement('div');
            newCard.className = 'ref-card';
            newCard.innerHTML = `
                <button type="button" class="remove-ref-btn" onclick="this.parentElement.remove()">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M6 18L18 6M6 6l12 12" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </button>
                <div class="space-y-3">
                    <div>
                        <label class="text-[10px] uppercase font-bold text-gray-500 mb-1 block">Document Name</label>
                        <input type="text" name="ref_doc_name[]" class="form-input" placeholder="Document Name">
                    </div>
                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="text-[10px] uppercase font-bold text-gray-500 mb-1 block">Record ID</label>
                            <input type="text" name="ref_record_id[]" class="form-input" placeholder="ID">
                        </div>
                        <div>
                            <label class="text-[10px] uppercase font-bold text-gray-500 mb-1 block">Relation</label>
                            <input type="text" name="ref_relation[]" class="form-input" placeholder="e.g. Amendment">
                        </div>
                    </div>
                </div>
            `;
            container.appendChild(newCard);
        }
    </script>
</x-app-layout-dark>