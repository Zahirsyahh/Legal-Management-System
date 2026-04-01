<x-app-layout-dark>
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

        /* 2. Tambahin grid column span */
        .two-column-layout > .col-span-2 {
            grid-column: span 2;
        }

        .main-content {
            min-width: 0;
        }

        /* 3. HAPUS margin-top: 5.5rem agar sejajar otomatis */
        .info-sidebar {
            position: relative;
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
            align-self: start; 
        }

        /* ============================================================
            DETAIL SECTIONS
        ============================================================ */
        .detail-section {
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

        .detail-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1.25rem;
            padding: 1.25rem 1.5rem;
        }

        .detail-item {
            display: flex;
            flex-direction: column;
            gap: 0.375rem;
        }

        .detail-item.full-width { 
            grid-column: span 2; 
        }

        .detail-label {
            font-size: 0.75rem;
            font-weight: 600;
            color: #9ca3af;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .detail-value {
            font-size: 0.9375rem;
            color: white;
            font-weight: 500;
            word-break: break-word;
        }

        .detail-value.empty {
            color: #6b7280;
            font-style: italic;
        }

        .badge {
            display: inline-flex;
            align-items: center;
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 500;
            width: fit-content;
        }

        .badge-status {
            background: rgba(14, 165, 233, 0.15);
            border: 1px solid rgba(14, 165, 233, 0.3);
            color: #7dd3fc;
        }

        .badge-version {
            background: rgba(139, 92, 246, 0.15);
            border: 1px solid rgba(139, 92, 246, 0.3);
            color: #c084fc;
        }

        /* Status Colors */
        .status-active { background: rgba(34, 197, 94, 0.15); border-color: rgba(34, 197, 94, 0.3); color: #4ade80; }
        .status-draft { background: rgba(251, 191, 36, 0.15); border-color: rgba(251, 191, 36, 0.3); color: #fbbf24; }
        .status-archived { background: rgba(107, 114, 128, 0.15); border-color: rgba(107, 114, 128, 0.3); color: #9ca3af; }
        .status-expired { background: rgba(239, 68, 68, 0.15); border-color: rgba(239, 68, 68, 0.3); color: #f87171; }

        /* Cross Reference Cards */
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

        .cross-ref-body {
            padding: 1.125rem;
        }

        .cross-ref-card {
            background: rgba(15, 23, 42, 0.5);
            border: 1px solid rgba(139, 92, 246, 0.15);
            border-radius: 1rem;
            padding: 1rem;
            margin-bottom: 1rem;
        }

        .cross-ref-card:last-child {
            margin-bottom: 0;
        }

        .cross-ref-card-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 0.75rem;
            padding-bottom: 0.5rem;
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

        .cross-ref-field {
            display: flex;
            flex-direction: column;
            gap: 0.25rem;
            margin-bottom: 0.75rem;
        }

        .cross-ref-field:last-child {
            margin-bottom: 0;
        }

        .cross-ref-label {
            font-size: 0.7rem;
            font-weight: 500;
            color: #9ca3af;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .cross-ref-value {
            font-size: 0.875rem;
            color: #e2e8f0;
            word-break: break-word;
        }

        .cross-ref-value.empty {
            color: #6b7280;
            font-style: italic;
        }

        /* Action Buttons */
        .action-buttons {
            display: flex;
            gap: 1rem;
            margin-top: 1.5rem;
            padding-top: 1.5rem;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
        }

        .btn-edit {
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
            text-decoration: none;
        }

        .btn-edit:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 14px rgba(14, 165, 233, 0.35);
        }

        .btn-back {
            background: rgba(255, 255, 255, 0.04);
            border: 1px solid rgba(255, 255, 255, 0.1);
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

        .btn-back:hover {
            background: rgba(255, 255, 255, 0.08);
            color: white;
        }

        /* Responsive */
        @media (max-width: 1024px) {
            .two-column-layout {
                grid-template-columns: 1fr;
            }
            .two-column-layout > .col-span-2 {
                grid-column: span 1;
            }
            .info-sidebar {
                position: static;
            }
        }

        @media (max-width: 640px) {
            .detail-grid {
                grid-template-columns: 1fr;
            }
            .detail-item.full-width {
                grid-column: span 1;
            }
        }
    </style>

    <div class="two-column-layout">
        
        <div class="col-span-2 mb-6 flex items-center gap-3">
            <div class="p-3 rounded-xl bg-gradient-to-br from-cyan-500/10 to-blue-600/10 border border-cyan-500/20">
                <svg class="w-6 h-6 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
            </div>
            <div>
                <h1 class="text-2xl font-bold bg-gradient-to-r from-cyan-400 via-blue-400 to-cyan-400 bg-clip-text text-transparent">
                    Archive Detail
                </h1>
                <p class="text-gray-400 text-sm mt-0.5">Complete information about this document</p>
            </div>
        </div>

        <div class="main-content">
            <div class="detail-section">
                <div class="section-header">
                    <div class="section-title">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14"/>
                        </svg>
                        Record Identification
                    </div>
                </div>
                <div class="detail-grid">
                    <div class="detail-item">
                        <div class="detail-label">Record ID</div>
                        <div class="detail-value">
                            <span class="badge badge-status">{{ $archive->record_id ?? '-' }}</span>
                        </div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Document Number</div>
                        <div class="detail-value">{{ $archive->doc_number ?? '-' }}</div>
                    </div>
                    <div class="detail-item full-width">
                        <div class="detail-label">Document Name</div>
                        <div class="detail-value">{{ $archive->doc_name ?? '-' }}</div>
                    </div>
                </div>
            </div>

            <div class="detail-section">
                <div class="section-header">
                    <div class="section-title">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                        </svg>
                        Document Classification
                    </div>
                </div>
                <div class="detail-grid">
                    <div class="detail-item">
                        <div class="detail-label">Company</div>
                        <div class="detail-value">{{ $archive->company ?? '-' }}</div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Year</div>
                        <div class="detail-value">{{ $archive->year ?? ($archive->created_at ? $archive->created_at->format('Y') : '-') }}</div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Department</div>
                        <div class="detail-value">{{ $archive->department_code ?? '-' }}</div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Document Type</div>
                        <div class="detail-value">{{ $archive->doc_type ?? '-' }}</div>
                    </div>
                </div>
            </div>

            <div class="detail-section">
                <div class="section-header">
                    <div class="section-title">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        Document Details
                    </div>
                </div>
                <div class="detail-grid">
                    <div class="detail-item">
                        <div class="detail-label">Counterparty</div>
                        <div class="detail-value {{ !$archive->counterparty ? 'empty' : '' }}">
                            {{ $archive->counterparty ?? 'Not specified' }}
                        </div>
                    </div>
                    <div class="detail-item full-width">
                        <div class="detail-label">Description</div>
                        <div class="detail-value {{ !$archive->description ? 'empty' : '' }}">
                            {{ $archive->description ?? 'No description provided' }}
                        </div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Document Status</div>
                        <div class="detail-value">
                            @php
                                $statusLabels = ['active' => 'Active', 'draft' => 'Draft', 'archived' => 'Archived', 'expired' => 'Expired'];
                                $docStatusValue = $archive->doc_status;
                                $statusText = '-';
                                $statusClass = 'badge-status';
                                if (is_array($docStatusValue)) {
                                    $statuses = [];
                                    foreach ($docStatusValue as $s) $statuses[] = $statusLabels[$s] ?? ucfirst($s);
                                    $statusText = implode(', ', $statuses);
                                } else if (is_string($docStatusValue)) {
                                    $statusText = $statusLabels[$docStatusValue] ?? ucfirst($docStatusValue);
                                    if ($docStatusValue == 'active') $statusClass = 'status-active';
                                    elseif ($docStatusValue == 'draft') $statusClass = 'status-draft';
                                    elseif ($docStatusValue == 'archived') $statusClass = 'status-archived';
                                    elseif ($docStatusValue == 'expired') $statusClass = 'status-expired';
                                }
                            @endphp
                            <span class="badge {{ $statusClass }}">{{ $statusText }}</span>
                        </div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Version Status</div>
                        <div class="detail-value">
                            @php
                                $versionLabels = ['latest' => 'Latest', 'previous' => 'Previous', 'draft' => 'Draft'];
                                $vValue = $archive->version_status;
                                $vText = is_string($vValue) ? ($versionLabels[$vValue] ?? ucfirst($vValue)) : '-';
                            @endphp
                            <span class="badge badge-version">{{ $vText }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="detail-section">
                <div class="section-header">
                    <div class="section-title">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                        Validity Period & Location
                    </div>
                </div>
                <div class="detail-grid">
                    <div class="detail-item">
                        <div class="detail-label">Start Date</div>
                        <div class="detail-value">
                            {{ $archive->start_date ? \Carbon\Carbon::parse($archive->start_date)->format('d M Y') : 'Not specified' }}
                        </div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">End Date</div>
                        <div class="detail-value">
                            {{ $archive->end_date ? \Carbon\Carbon::parse($archive->end_date)->format('d M Y') : 'Not specified' }}
                        </div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Physical Location</div>
                        <div class="detail-value">{{ $archive->doc_location ?? 'Not specified' }}</div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Digital Path</div>
                        <div class="detail-value">{{ $archive->synology_path ?? 'Not specified' }}</div>
                    </div>
                </div>
            </div>

            <div class="action-buttons">
                <a href="{{ route('archives.edit', $archive->id) }}" class="btn-edit">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                    </svg>
                    Edit Archive
                </a>
                <a href="{{ route('archives.index') }}" class="btn-back">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    Back to List
                </a>
            </div>
        </div>

                <!-- RIGHT COLUMN — CROSS REFERENCES -->
        <div class="info-sidebar">
            <div class="cross-ref-section">
                <div class="cross-ref-header">
                    <div class="cross-ref-title">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/>
                        </svg>
                        Cross References
                    </div>
                    <span class="badge badge-version">
                        {{ $archive->crossReferences ? $archive->crossReferences->count() : 0 }} Reference(s)
                    </span>
                </div>
                    <div class="cross-ref-body">
                        @if($archive->crossReferences && $archive->crossReferences->count() > 0)
                            @foreach($archive->crossReferences as $index => $ref)
                                <div class="cross-ref-card">
                                    <div class="cross-ref-card-header">
                                        <span class="cross-ref-badge-num">{{ $index + 1 }}</span>
                                    </div>

                                    <div class="cross-ref-field">
                                        <div class="cross-ref-label">Document Name</div>
                                        <div class="cross-ref-value">{{ $ref->ref_doc_name ?? '-' }}</div>
                                    </div>

                                    <div class="cross-ref-field">
                                        <div class="cross-ref-label">Record ID</div>
                                        <div class="cross-ref-value {{ !$ref->ref_record_id ? 'empty' : '' }}">{{ $ref->ref_record_id ?? 'Not specified' }}</div>
                                    </div>

                                    <div class="cross-ref-field">
                                        <div class="cross-ref-label">Location</div>
                                        <div class="cross-ref-value {{ !$ref->ref_location ? 'empty' : '' }}">{{ $ref->ref_location ?? 'Not specified' }}</div>
                                    </div>

                                    <div class="cross-ref-field">
                                        <div class="cross-ref-label">Description / Notes</div>
                                        <div class="cross-ref-value {{ !$ref->ref_relation ? 'empty' : '' }}">{{ $ref->ref_relation ?? 'No additional notes' }}</div>
                                    </div>
                                </div>
                            @endforeach
                        @else
                            <div class="text-center py-8">
                                <svg class="w-12 h-12 mx-auto text-gray-600 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/>
                                </svg>
                                <p class="text-gray-500 text-sm">No cross references found</p>
                                <p class="text-gray-600 text-xs mt-1">This document has no linked references</p>
                            </div>
                        @endif
                    </div>
            </div>

            <!-- Additional Info Card -->
            <div class="mt-4 cross-ref-section">
                <div class="cross-ref-header">
                    <div class="cross-ref-title">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        System Information
                    </div>
                </div>
                <div class="cross-ref-body">
                    <div class="cross-ref-field">
                        <div class="cross-ref-label">Created At</div>
                        <div class="cross-ref-value">{{ $archive->created_at ? \Carbon\Carbon::parse($archive->created_at)->format('d M Y, H:i') : '-' }}</div>
                    </div>
                    <div class="cross-ref-field">
                        <div class="cross-ref-label">Last Updated</div>
                        <div class="cross-ref-value">{{ $archive->updated_at ? \Carbon\Carbon::parse($archive->updated_at)->format('d M Y, H:i') : '-' }}</div>
                    </div>
                    <div class="cross-ref-field">
                        <div class="cross-ref-label">Created By</div>
                        <div class="cross-ref-value">{{ $archive->createdBy->name ?? 'System' }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout-dark>