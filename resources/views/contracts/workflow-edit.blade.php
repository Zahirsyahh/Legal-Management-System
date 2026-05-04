<x-app-layout-dark title="Edit Workflow — {{ $contract->title }}">
<style>
.wf-card { background:rgba(15,23,42,.95); border:1px solid rgba(255,255,255,.1); border-radius:.75rem; backdrop-filter:blur(10px); transition:border-color .2s,box-shadow .2s; }
.wf-card:hover { border-color:rgba(14,165,233,.35); }
.seq-card { background:rgba(15,23,42,.95); border:1px solid rgba(255,255,255,.12); border-radius:.75rem; padding:1rem 1.25rem; position:relative; }
.seq-card.active-stage-card { border-color:rgba(14,165,233,.65)!important; box-shadow:0 0 18px rgba(14,165,233,.2); }
.seq-card.dragging,.par-card.dragging { opacity:.4; transform:scale(.98); }
.seq-card.drag-over { border-color:#0ea5e9; box-shadow:0 0 0 2px rgba(14,165,233,.3); }
.par-card { border:2px dashed rgba(168,85,247,.5); border-radius:.75rem; background:rgba(88,28,135,.07); padding:1rem; position:relative; }
.par-card.drag-over { border-color:#a855f7; box-shadow:0 0 0 2px rgba(168,85,247,.3); }
.dept-section { background:rgba(15,23,42,.7); border:1px solid rgba(168,85,247,.25); border-radius:.5rem; padding:.75rem 1rem; margin-bottom:.5rem; }
/* ✅ FIX: Dept section khusus untuk yang declined */
.dept-section.dept-declined { border-color:rgba(239,68,68,.35); background:rgba(127,29,29,.08); }
.dept-reviewer-row { background:rgba(30,41,59,.7); border:1px solid rgba(255,255,255,.08); border-radius:.4rem; padding:.5rem .75rem; margin-top:.4rem; display:flex; align-items:center; gap:.6rem; }
.dept-placeholder-notice { background:rgba(168,85,247,.05); border:1px dashed rgba(168,85,247,.3); border-radius:.35rem; padding:.4rem .75rem; margin-top:.4rem; font-size:.75rem; color:rgba(196,181,253,.8); display:flex; align-items:center; gap:.4rem; }
.drag-handle { cursor:grab; color:rgba(255,255,255,.35); padding:.2rem; flex-shrink:0; touch-action:none; }
.drag-handle:active { cursor:grabbing; }
.drag-handle:hover { color:#0ea5e9; }
.wf-input,.wf-select { background:rgba(30,41,59,.8); border:1px solid rgba(255,255,255,.15); border-radius:.4rem; color:#e2e8f0; padding:.45rem .75rem; font-size:.875rem; width:100%; transition:border-color .2s,box-shadow .2s; }
.wf-input:focus,.wf-select:focus { border-color:rgba(14,165,233,.6); box-shadow:0 0 0 3px rgba(14,165,233,.12); outline:none; }
.wf-select { appearance:none; background-image:url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%23ffffff' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e"); background-repeat:no-repeat; background-position:right .5rem center; background-size:1.25em; padding-right:2rem; }
.wf-select option { background:#0f172a; color:#e2e8f0; }
.status-pill { display:inline-flex; align-items:center; padding:.2rem .65rem; border-radius:9999px; font-size:.7rem; font-weight:600; letter-spacing:.04em; text-transform:uppercase; white-space:nowrap; }
.s-pending{background:rgba(245,158,11,.15);color:#fbbf24;border:1px solid rgba(245,158,11,.3)}
.s-assigned{background:rgba(59,130,246,.15);color:#60a5fa;border:1px solid rgba(59,130,246,.3)}
.s-in_progress{background:rgba(14,165,233,.15);color:#38bdf8;border:1px solid rgba(14,165,233,.3)}
.s-completed{background:rgba(34,197,94,.15);color:#4ade80;border:1px solid rgba(34,197,94,.3)}
.s-revision_requested{background:rgba(249,115,22,.15);color:#fb923c;border:1px solid rgba(249,115,22,.3)}
.s-declined{background:rgba(239,68,68,.15);color:#f87171;border:1px solid rgba(239,68,68,.3)}
.s-skipped{background:rgba(148,163,184,.15);color:#94a3b8;border:1px solid rgba(148,163,184,.3)}
.s-placeholder{background:rgba(168,85,247,.1);color:#c4b5fd;border:1px solid rgba(168,85,247,.3)}
.btn-blue{background:linear-gradient(135deg,#0ea5e9,#3b82f6);color:#fff;border:none;padding:.45rem .9rem;border-radius:.4rem;font-size:.8rem;font-weight:600;cursor:pointer;transition:opacity .2s,transform .15s}
.btn-blue:hover{opacity:.9;transform:translateY(-1px)}
.btn-purple{background:linear-gradient(135deg,#a855f7,#7c3aed);color:#fff;border:none;padding:.45rem .9rem;border-radius:.4rem;font-size:.8rem;font-weight:600;cursor:pointer;transition:opacity .2s,transform .15s}
.btn-purple:hover{opacity:.9;transform:translateY(-1px)}
.btn-red{background:rgba(239,68,68,.15);color:#f87171;border:1px solid rgba(239,68,68,.3);padding:.3rem .6rem;border-radius:.35rem;font-size:.75rem;cursor:pointer;transition:background .2s}
.btn-red:hover{background:rgba(239,68,68,.3)}
.btn-ghost{background:transparent;color:rgba(255,255,255,.5);border:1px solid rgba(255,255,255,.15);padding:.3rem .6rem;border-radius:.35rem;font-size:.75rem;cursor:pointer;transition:all .2s}
.btn-ghost:hover{color:#fff;border-color:rgba(255,255,255,.3)}
.btn-save{background:linear-gradient(135deg,#10b981,#059669);color:#fff;border:none;padding:.7rem 1.8rem;border-radius:.5rem;font-weight:600;font-size:.9rem;cursor:pointer;transition:opacity .2s,transform .15s}
.btn-save:hover{opacity:.92;transform:translateY(-1px)}
.btn-save:disabled{opacity:.6;cursor:not-allowed;transform:none}
.seq-num{width:32px;height:32px;border-radius:50%;background:linear-gradient(135deg,#0ea5e9,#3b82f6);color:#fff;font-weight:700;font-size:.8rem;display:flex;align-items:center;justify-content:center;flex-shrink:0}
.active-dot{width:8px;height:8px;border-radius:50%;background:#0ea5e9;animation:ping-dot 1.5s ease-in-out infinite}
@keyframes ping-dot{0%,100%{opacity:1;transform:scale(1)}50%{opacity:.6;transform:scale(1.3)}}
</style>

<div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    <div class="flex items-center justify-between mb-7">
        <div>
            <a href="{{ route('contracts.show', $contract) }}"
               class="inline-flex items-center gap-2 text-sm text-gray-400 hover:text-gray-200 mb-3 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Back to Document
            </a>
            <h1 class="text-2xl font-bold text-white">Edit Workflow</h1>
            <p class="text-sm text-gray-400 mt-1">{{ $contract->title }}</p>
        </div>
        <div class="wf-card px-4 py-2 text-sm text-gray-300">
            <span class="text-gray-500">Stages:</span>
            <span class="font-semibold text-white ml-1" id="stageCountLabel">{{ count($displayOrder) }}</span>
        </div>
    </div>

    @if(session('success'))
    <div class="mb-5 p-4 rounded-xl bg-green-500/10 border border-green-500/30 text-green-300 text-sm">✓ {{ session('success') }}</div>
    @endif
    @if(session('error'))
    <div class="mb-5 p-4 rounded-xl bg-red-500/10 border border-red-500/30 text-red-300 text-sm">{{ session('error') }}</div>
    @endif

    <div class="wf-card p-3 mb-5 flex flex-wrap gap-4 text-xs text-gray-400">
        <div class="flex items-center gap-2">
            <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8h16M4 16h16"/>
            </svg>
            Drag handle = change order
        </div>
        <div class="flex items-center gap-2">
            <span class="status-pill s-in_progress">In Progress</span>
            = active stage (reviewer locked)
        </div>
        <div class="flex items-center gap-2">
            <span class="status-pill s-placeholder">Waiting</span>
            = registered dept, no reviewer yet — <strong class="text-purple-400">still saved</strong>
        </div>
        {{-- ✅ TAMBAH: legenda untuk declined --}}
        <div class="flex items-center gap-2">
            <span class="status-pill s-declined">Declined</span>
            = department declined the invitation — use <strong class="text-yellow-400">Re-Invite</strong>
        </div>
    </div>

    <form id="wfForm" method="POST" action="{{ route('legal.workflow.update', $contract) }}">
        @csrf
        <input type="hidden" name="order" id="orderPayload">
        <input type="hidden" name="synology_folder_path" id="synologyHidden" value="{{ $contract->synology_folder_path }}">

        <div id="wfCanvas" class="space-y-3 mb-6"></div>

        <div class="flex flex-wrap gap-3 mb-8">
            <button type="button" onclick="addSequentialStage()" class="btn-blue flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Add new stage
            </button>
            <button type="button" onclick="addParallelGroup()" class="btn-purple flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                </svg>
                Add Substantial Review
            </button>
        </div>

        <div class="wf-card p-5 mb-6">
            <h3 class="text-sm font-semibold text-gray-300 mb-3">Synology Drive Link <span class="text-gray-600 font-normal">(opsional)</span></h3>
            <input type="text" id="synologyInput" value="{{ $contract->synology_folder_path }}"
                   placeholder="\\server\Legal\Contracts\2024" class="wf-input"
                   oninput="document.getElementById('synologyHidden').value=this.value">
        </div>

        <div class="flex items-center justify-between pt-4 border-t border-gray-800">
            <p class="text-xs text-gray-500 max-w-lg">
                ⚠ Departments without reviewers (FIN/ACC/TAX) remain listed in the substantial review and <strong>not deleted</strong>.
                Reviewers can be assigned later by the admin of each department.
            </p>
            <div class="flex gap-3 flex-shrink-0 ml-4">
                <a href="{{ route('contracts.show', $contract) }}" class="btn-ghost">Cancel</a>
                <button type="submit" class="btn-save" id="saveBtn">Save Changes</button>
            </div>
        </div>
    </form>

    <form method="POST" id="deleteForm" style="display:none;">
        @csrf
        @method('DELETE')
    </form>
</div>

@php
$allUsersFormatted = $users->map(fn($u) => [
    'id'      => $u->id_user,
    'name'    => $u->nama_user,
    'jabatan' => $u->jabatan ?? '',
])->values();

$staffByDeptFormatted = [];
foreach (['FIN', 'ACC', 'TAX', 'LEGAL'] as $code) {
    $staffByDeptFormatted[$code] = ($staffByDept[$code] ?? collect())->map(fn($u) => [
        'id'      => $u->id_user,
        'name'    => $u->nama_user,
        'jabatan' => $u->jabatan ?? '',
    ])->values();
}

// ✅ FIX: Format displayOrder agar contract_department_id ikut ke JS
// buildDisplayOrder() sudah mengembalikan array (bukan Eloquent), tapi kita
// pastikan versi JSON-nya benar untuk parallel stages
$displayOrderForJs = array_map(function($item) {
    if ($item['type'] === 'parallel') {
        // stages sudah berupa array dari buildDisplayOrder()
        return $item;
    }
    // sequential: konversi stage Eloquent ke array
    $s = $item['stage'];
    return [
        'type'  => 'sequential',
        'stage' => [
            'id'               => $s->id,
            'stage_name'       => $s->stage_name,
            'assigned_user_id' => $s->assigned_user_id,
            'status'           => $s->status,
            'sequence'         => $s->sequence,
        ],
    ];
}, $displayOrder);
@endphp

<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
const ALL_USERS     = @json($allUsersFormatted);
const STAFF_BY_DEPT = @json($staffByDeptFormatted);

const DEPT_LABELS = { FIN:'Finance', ACC:'Accounting', TAX:'Tax', LEGAL:'Legal' };
const DEPT_META   = {
    FIN:   { icon:'💹', cls:'text-emerald-400' },
    ACC:   { icon:'📊', cls:'text-sky-400' },
    TAX:   { icon:'🏛️', cls:'text-amber-400' },
    LEGAL: { icon:'⚖️',  cls:'text-blue-400'  },
};

// ✅ FIX: Gunakan $displayOrderForJs yang sudah di-format dengan benar
const INITIAL_DISPLAY_ORDER = @json($displayOrderForJs);

const DELETE_ROUTE_BASE   = "{{ route('legal.workflow.delete', ['contract' => $contract->id, 'stage' => '__ID__']) }}";
const REINVITE_ROUTE_BASE = "{{ route('legal.workflow.reinvite', ['contract' => $contract->id, 'contractDepartment' => '__ID__']) }}";
const LOCKED = ['in_progress','completed','revision_requested'];

let items = [];
let dragSrc = null;
let parallelGroupCounter = 10;

// ─────────────────────────────────────────────────────────────
// INIT
// ─────────────────────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', () => {
    items = INITIAL_DISPLAY_ORDER.map(item => {
        if (item.type === 'sequential') {
            const s = item.stage;
            return {
                _id: rnd(), type:'sequential',
                stage_id:   s.id,
                stage_name: s.stage_name,
                user_id:    s.assigned_user_id,
                status:     s.status,
                locked:     LOCKED.includes(s.status),
            };
        } else {
            // ✅ FIX: item.stages adalah array (bukan Eloquent), map langsung
            const depts = { FIN:[], ACC:[], TAX:[], LEGAL:[] };

            (item.stages || []).forEach(s => {
                const code = stageTypeToCode(s.stage_type);
                if (!depts[code]) depts[code] = [];
                depts[code].push({
                    _id:                    rnd(),
                    id:                     s.id,
                    user_id:                s.assigned_user_id,
                    stage_name:             s.stage_name,
                    status:                 s.status,
                    locked:                 LOCKED.includes(s.status),
                    // ✅ KUNCI FIX masalah 404: simpan contract_department_id
                    contract_department_id: s.contract_department_id,
                });
            });

            return {
                _id: rnd(), type:'parallel',
                group_id: item.group_id,
                depts,
            };
        }
    });

    render();
    document.getElementById('wfForm').addEventListener('submit', onFormSubmit);
});

function stageTypeToCode(t) {
    if (!t) return 'LEGAL';
    return { finance:'FIN', accounting:'ACC', tax:'TAX', legal:'LEGAL' }[t] || t.toUpperCase();
}
function rnd() { return Math.random().toString(36).slice(2); }

// ─────────────────────────────────────────────────────────────
// RENDER
// ─────────────────────────────────────────────────────────────
function render() {
    const canvas = document.getElementById('wfCanvas');
    canvas.innerHTML = '';
    let seq = 1;

    items.forEach((item, idx) => {
        seq++;
        const el = item.type === 'sequential'
            ? buildSeqCard(item, idx, seq)
            : buildParCard(item, idx, seq);
        canvas.appendChild(el);
        setupDragEvents(el, idx);
    });

    document.getElementById('stageCountLabel').textContent = items.length;
}

// ─── Sequential card ──────────────────────────────────────────
function buildSeqCard(item, idx, seq) {
    const div = document.createElement('div');
    div.className = 'seq-card' + (item.locked ? ' active-stage-card' : '');
    div.dataset.idx = idx;

    div.innerHTML = `
        <div class="flex items-center gap-3">
            <div class="drag-handle" title="Drag to change order">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8h16M4 16h16"/>
                </svg>
            </div>
            <div class="seq-num">${seq}</div>
            ${item.locked ? '<div class="active-dot" title="Stage sedang aktif"></div>' : ''}
            <div class="flex-1 grid grid-cols-1 md:grid-cols-2 gap-3">
                <div>
                    <label class="text-xs text-gray-500 mb-1 block">Stage Name</label>
                    <input class="wf-input stage-name-input" type="text" value="${escHtml(item.stage_name)}"
                           ${item.locked?'readonly style="opacity:.6;cursor:not-allowed"':''}
                           oninput="items[${idx}].stage_name=this.value">
                </div>
                <div>
                    <label class="text-xs text-gray-500 mb-1 block">Reviewer ${item.locked?'<span class="text-amber-400">(locked)</span>':''}</label>
                    <select class="wf-select reviewer-select-${idx}" ${item.locked?'disabled':''}
                            style="width:100%">
                        <option value="">-- Choose Reviewer --</option>
                    </select>
                </div>
            </div>
            <div class="flex flex-col items-end gap-2 flex-shrink-0">
                <span class="status-pill s-${item.status}">${item.status.replace(/_/g,' ')}</span>
                ${!item.locked?`<button type="button" class="btn-red" onclick="removeItem(${idx})">✕ Delete</button>`:''}
            </div>
        </div>
        ${item.stage_id && !item.locked ? `
        <div class="mt-2 text-right">
            <button type="button" class="text-xs text-red-400/60 hover:text-red-400 transition-colors"
                    onclick="deleteFromDb(${item.stage_id},'${escHtml(item.stage_name)}')">Hapus dari database</button>
        </div>` : ''}
    `;

    setTimeout(() => {
        const selectEl = div.querySelector(`.reviewer-select-${idx}`);
        if (selectEl && !item.locked) {
            if (item.user_id) {
                const selectedUser = ALL_USERS.find(u => u.id == item.user_id);
                if (selectedUser) {
                    const option = new Option(selectedUser.name, selectedUser.id, true, true);
                    $(selectEl).append(option).trigger('change');
                }
            }

            $(selectEl).select2({
                placeholder: 'Pilih Reviewer',
                allowClear: true,
                dropdownParent: div,
                ajax: {
                    url: '{{ route("legal.workflow.reviewer.candidates") }}',
                    dataType: 'json',
                    delay: 250,
                    data: function (params) {
                        return { q: params.term || '', contract_id: '{{ $contract->id }}' };
                    },
                    processResults: function (data) {
                        return data && data.results ? { results: data.results } : { results: [] };
                    },
                    cache: true
                }
            }).on('change', function() {
                items[idx].user_id = $(this).val() || null;
            });
        }
    }, 100);

    return div;
}

// ─── Parallel card ────────────────────────────────────────────
function buildParCard(item, idx, seq) {
    const div = document.createElement('div');
    div.className = 'par-card';
    div.dataset.idx = idx;

    const totalDepts    = ['FIN','ACC','TAX','LEGAL'].filter(c => (item.depts[c]||[]).length > 0).length;
    const assignedDepts = ['FIN','ACC','TAX','LEGAL'].filter(c => (item.depts[c]||[]).some(s => s.user_id)).length;
    // ✅ Hitung dept yang declined
    const declinedDepts = ['FIN','ACC','TAX','LEGAL'].filter(c => (item.depts[c]||[]).some(s => s.status === 'declined')).length;
    const waitingDepts  = totalDepts - assignedDepts - declinedDepts;

    let html = `
        <div class="flex items-center justify-between mb-4">
            <div class="flex items-center gap-3">
                <div class="drag-handle text-purple-400" title="Drag untuk memindahkan posisi substantial review">
                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8h16M4 16h16"/>
                    </svg>
                </div>
                <div class="w-8 h-8 rounded-full bg-purple-500/20 border border-purple-500/40 flex items-center justify-center text-purple-300 font-bold text-sm">${seq}</div>
                <div>
                    <p class="text-sm font-semibold text-purple-300">⚡ Substantial / Parallel Review</p>
                    <p class="text-xs text-gray-500 mt-0.5">
                        ${totalDepts} departments registered · ${assignedDepts} reviewers assigned
                        ${waitingDepts > 0 ? ` · <span class="text-amber-400">${waitingDepts} waiting</span>` : ''}
                        ${declinedDepts > 0 ? ` · <span class="text-red-400">${declinedDepts} declined</span>` : ''}
                    </p>
                </div>
            </div>
            <button type="button" class="btn-red" onclick="removeItem(${idx})">✕ Delete Group</button>
        </div>
        <div class="space-y-2">
    `;

    ['FIN','ACC','TAX','LEGAL'].forEach(code => {
        html += buildDeptSection(code, item.depts[code]||[], idx);
    });

    html += `</div>`;
    div.innerHTML = html;
    return div;
}

// ─────────────────────────────────────────────────────────────
// ✅ FIX UTAMA: buildDeptSection
// - Tampilkan status 'declined' dengan benar
// - Tombol Re-Invite menggunakan contract_department_id (bukan stage id)
// ─────────────────────────────────────────────────────────────
function buildDeptSection(code, deptStages, parIdx) {
    const label    = DEPT_LABELS[code] || code;
    const meta     = DEPT_META[code]  || { icon:'🏢', cls:'text-gray-400' };
    const staff    = STAFF_BY_DEPT[code] || [];
    const included = deptStages.length > 0;
    const hasAssigned = deptStages.some(ds => ds.user_id);

    // ✅ FIX: Cari stage yang declined — bisa punya atau tidak punya user_id
    const declinedStage = deptStages.find(ds => ds.status === 'declined');
    const isDeclinedDept = !!declinedStage;

    // ✅ FIX: class tambahan untuk dept yang declined
    const deptSectionClass = isDeclinedDept ? 'dept-section dept-declined' : 'dept-section';

    let html = `
        <div class="${deptSectionClass}">
            <div class="flex items-center justify-between mb-2">
                <div class="flex items-center gap-2">
                    <label class="flex items-center gap-2 cursor-pointer select-none">
                        <input type="checkbox"
                               data-par="${parIdx}" data-code="${code}"
                               ${included ? 'checked' : ''}
                               ${isDeclinedDept ? 'disabled title="This dept declined, use Re-Invite"' : ''}
                               onchange="toggleDept(${parIdx},'${code}',this.checked)"
                               class="w-3.5 h-3.5 accent-purple-500">
                        <span class="text-sm font-semibold ${meta.cls}">${meta.icon} ${label}</span>
                    </label>

                    ${isDeclinedDept
                        ? '<span class="text-xs text-red-400/80 bg-red-500/10 px-2 py-0.5 rounded-full border border-red-500/20">Declined</span>'
                        : included && !hasAssigned
                            ? '<span class="text-xs text-amber-400/80 bg-amber-500/10 px-2 py-0.5 rounded-full border border-amber-500/20">Waiting for reviewer</span>'
                            : included
                                ? `<span class="text-xs text-green-400/70 bg-green-500/5 px-2 py-0.5 rounded-full border border-green-500/20">${deptStages.filter(s=>s.user_id).length} reviewer</span>`
                                : ''
                    }
                </div>
                <div class="flex items-center gap-2">
                    <span class="text-xs text-gray-600">${staff.length} Active Staff(s)</span>
                    ${included && !isDeclinedDept ? `<button type="button" class="btn-ghost text-xs" onclick="addReviewerToDept(${parIdx},'${code}')">+ Reviewer</button>` : ''}
                </div>
            </div>
            <div id="deptRevs_${parIdx}_${code}">
    `;

    if (included) {
        deptStages.forEach((ds, ri) => {
            html += buildDeptReviewerRow(ds, parIdx, code, ri, staff);
        });

        // ✅ FIX: Tampilkan notice yang tepat berdasarkan status
        if (isDeclinedDept) {
            // Status declined: tidak perlu placeholder notice
        } else if (!hasAssigned) {
            html += `
                <div class="dept-placeholder-notice">
                    <svg class="w-3.5 h-3.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    This dept is registered but there are no reviewers yet.
                    ${label} need to assign reviewers before substantial review begins.
                </div>
            `;
        }
    } else {
        html += `<p class="text-xs text-gray-600 italic py-1">Department not included in parallel review.</p>`;
    }

    html += `</div>`;

    // ✅ FIX UTAMA: Tombol Re-Invite menggunakan contract_department_id
    // declinedStage.contract_department_id adalah ID dari tabel contract_departments
    // bukan ID dari tabel contract_review_stages
    if (isDeclinedDept && declinedStage.contract_department_id) {
        const reinviteUrl = REINVITE_ROUTE_BASE.replace('__ID__', declinedStage.contract_department_id);
        html += `
            <div class="mt-3 pt-2 border-t border-red-500/20">
                <div class="flex items-center gap-2 text-xs text-red-400/70 mb-2">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    ${label} declined the invitation. Resend the invitation to include them again.
                </div>
                <form method="POST"
                      action="${reinviteUrl}"
                      onsubmit="return confirm('Re-invite department ${label}?\\n\\nDepartment admin will receive a new invitation notification.')"
                      class="inline-block">
                    <input type="hidden" name="_token" value="{{ csrf_token() }}">
                    <button type="submit"
                            class="px-3 py-1.5 text-xs bg-yellow-500/20 hover:bg-yellow-500/30
                                   text-yellow-300 rounded-lg border border-yellow-500/30 transition-all
                                   flex items-center gap-1.5">
                        🔄 Re-Invite ${label} Department
                    </button>
                </form>
            </div>
        `;
    } else if (isDeclinedDept && !declinedStage.contract_department_id) {
        // ✅ Fallback: contract_department_id tidak ada (data lama), tampilkan pesan
        html += `
            <div class="mt-3 pt-2 border-t border-red-500/20">
                <p class="text-xs text-red-400/60 italic">
                    ⚠ Re-invite is not available. Department document data not found.
Resave this workflow to fix it.
                </p>
            </div>
        `;
    }

    html += `</div>`;
    return html;
}

function buildDeptReviewerRow(ds, parIdx, code, ri, staff) {
    const opts = staff.map(u =>
        `<option value="${u.id}" ${u.id == ds.user_id ? 'selected' : ''}>${escHtml(u.name)}${u.jabatan?' · '+escHtml(u.jabatan):''}</option>`
    ).join('');

    const isUnassigned = !ds.user_id;
    const isDeclined   = ds.status === 'declined';

    return `
        <div class="dept-reviewer-row ${isUnassigned?'opacity-60':''} ${isDeclined?'border-red-500/30 bg-red-500/5':''}">
            <select class="wf-select" style="max-width:260px"
                    ${ds.locked || isDeclined ? 'disabled' : ''}
                    onchange="updateParReviewer(${parIdx},'${code}',${ri},parseInt(this.value)||null)">
                <option value="">-- Pilih reviewer --</option>
                ${opts}
            </select>
            <input class="wf-input" type="text" style="max-width:190px"
                   value="${escHtml(ds.stage_name)}" placeholder="Nama stage"
                   ${ds.locked || isDeclined ? 'readonly style="opacity:.6;cursor:not-allowed"' : ''}
                   oninput="updateParStageName(${parIdx},'${code}',${ri},this.value)">
            <span class="status-pill ${isUnassigned && !isDeclined ? 's-placeholder' : 's-'+ds.status}">
                ${isDeclined ? 'Declined' : isUnassigned ? 'Waiting' : ds.status.replace(/_/g,' ')}
            </span>
            ${!ds.locked && !isDeclined
                ? `<button type="button" class="btn-red" onclick="removeDeptReviewer(${parIdx},'${code}',${ri})">✕</button>`
                : ''
            }
        </div>
    `;
}

// ─────────────────────────────────────────────────────────────
// STATE MUTATIONS
// ─────────────────────────────────────────────────────────────
function removeItem(idx) {
    if (!confirm('Remove this item from workflow?')) return;
    items.splice(idx, 1);
    render();
}

function addSequentialStage() {
    items.push({ _id:rnd(), type:'sequential', stage_id:null, stage_name:'Legal Review', user_id:null, status:'pending', locked:false });
    render();
    document.getElementById('wfCanvas').lastElementChild?.scrollIntoView({ behavior:'smooth', block:'center' });
}

function addParallelGroup() {
    parallelGroupCounter++;
    items.push({ _id:rnd(), type:'parallel', group_id:parallelGroupCounter, depts:{ FIN:[], ACC:[], TAX:[], LEGAL:[] } });
    render();
    document.getElementById('wfCanvas').lastElementChild?.scrollIntoView({ behavior:'smooth', block:'center' });
}

function toggleDept(parIdx, code, checked) {
    if (!items[parIdx] || items[parIdx].type !== 'parallel') return;
    if (checked) {
        items[parIdx].depts[code] = [{
            _id:rnd(), id:null, user_id:null,
            stage_name: (DEPT_LABELS[code]||code) + ' Review',
            status:'pending', locked:false,
            contract_department_id: null,
        }];
    } else {
        const hasLocked = (items[parIdx].depts[code]||[]).some(s => s.locked);
        if (hasLocked) {
            alert(`Dept ${DEPT_LABELS[code]||code} has active reviewers, cannot be removed from the group.`);
            setTimeout(() => {
                const cb = document.querySelector(`[data-par="${parIdx}"][data-code="${code}"]`);
                if (cb) cb.checked = true;
            }, 0);
            return;
        }
        items[parIdx].depts[code] = [];
    }
    render();
}

function addReviewerToDept(parIdx, code) {
    if (!items[parIdx]?.depts) return;
    if (!items[parIdx].depts[code]) items[parIdx].depts[code] = [];
    items[parIdx].depts[code].push({
        _id:rnd(), id:null, user_id:null,
        stage_name: (DEPT_LABELS[code]||code) + ' Review',
        status:'pending', locked:false,
        contract_department_id: null,
    });
    render();
}

function removeDeptReviewer(parIdx, code, ri) {
    const ds = items[parIdx]?.depts[code]?.[ri];
    if (!ds) return;
    if (ds.locked) { alert('Active reviewers cannot be deleted.'); return; }
    if (ds.status === 'declined') { alert('Declined stage cannot be deleted. Use Re-Invite.'); return; }
    if (!confirm('Remove this reviewer?')) return;
    items[parIdx].depts[code].splice(ri, 1);
    render();
}

function updateParReviewer(parIdx, code, ri, userId) {
    if (items[parIdx]?.depts[code]?.[ri] !== undefined) {
        items[parIdx].depts[code][ri].user_id = userId || null;
    }
}

function updateParStageName(parIdx, code, ri, name) {
    if (items[parIdx]?.depts[code]?.[ri] !== undefined) {
        items[parIdx].depts[code][ri].stage_name = name;
    }
}

function deleteFromDb(stageId, stageName) {
    if (!confirm(`Remove stage "${stageName}" from the database?\nThis action is permanent.`)) return;
    const form = document.getElementById('deleteForm');
    form.action = DELETE_ROUTE_BASE.replace('__ID__', stageId);
    form.submit();
}

// ─────────────────────────────────────────────────────────────
// DRAG AND DROP
// ─────────────────────────────────────────────────────────────
function setupDragEvents(el, idx) {
    el.setAttribute('draggable', 'true');

    el.addEventListener('dragstart', e => {
        if (['INPUT','SELECT','BUTTON','TEXTAREA'].includes(e.target.tagName)) { e.preventDefault(); return; }
        dragSrc = idx;
        el.classList.add('dragging');
        e.dataTransfer.effectAllowed = 'move';
    });
    el.addEventListener('dragend', () => {
        el.classList.remove('dragging');
        document.querySelectorAll('.drag-over').forEach(x => x.classList.remove('drag-over'));
        dragSrc = null;
    });
    el.addEventListener('dragover', e => {
        e.preventDefault();
        if (dragSrc !== null && dragSrc !== idx) el.classList.add('drag-over');
    });
    el.addEventListener('dragleave', () => el.classList.remove('drag-over'));
    el.addEventListener('drop', e => {
        e.preventDefault();
        el.classList.remove('drag-over');
        if (dragSrc === null || dragSrc === idx) return;
        const moved = items.splice(dragSrc, 1)[0];
        items.splice(idx > dragSrc ? idx - 1 : idx, 0, moved);
        dragSrc = null;
        render();
    });
}

// ─────────────────────────────────────────────────────────────
// FORM SUBMIT
// ─────────────────────────────────────────────────────────────
function onFormSubmit(e) {
    syncDomToState();

    for (let i = 0; i < items.length; i++) {
        const it = items[i];
        if (it.type === 'sequential' && !it.user_id && !it.locked) {
            alert(`Stage "${it.stage_name}" has no reviewer assigned!`);
            e.preventDefault(); return;
        }
    }

    const payload = items.map(item => {
        if (item.type === 'sequential') {
            return {
                type:       'sequential',
                stage_id:   item.stage_id || null,
                stage_name: item.stage_name,
                user_id:    item.user_id,
                status:     item.status,
            };
        } else {
            const departments = [];
            ['FIN','ACC','TAX','LEGAL'].forEach(code => {
                const slots = item.depts[code] || [];
                if (slots.length > 0) {
                    departments.push({
                        code,
                        stages: slots.map(ds => ({
                            id:                     ds.id       || null,
                            user_id:                ds.user_id  || null,
                            stage_name:             ds.stage_name,
                            status:                 ds.status,
                            contract_department_id: ds.contract_department_id || null,
                        })),
                    });
                }
            });
            return { type:'parallel', group_id:item.group_id, departments };
        }
    });

    document.getElementById('orderPayload').value = JSON.stringify(payload);

    const btn = document.getElementById('saveBtn');
    btn.disabled = true;
    btn.textContent = 'Saving...';
}

function syncDomToState() {
    Array.from(document.getElementById('wfCanvas').children).forEach((card, idx) => {
        const item = items[idx];
        if (!item || item.type !== 'sequential' || item.locked) return;
        const nameIn = card.querySelector('.stage-name-input');
        if (nameIn) item.stage_name = nameIn.value;
    });
}

function escHtml(s) {
    if (!s) return '';
    return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}
</script>

<style>
/* ── Select2 Dark Theme ─────────────────────────────────────── */
.select2-container--default .select2-selection--single {
    background: rgba(20, 28, 38, 0.95) !important;
    border: 1px solid rgba(14, 165, 233, 0.3) !important;
    border-radius: 0.5rem !important;
    height: 38px !important;
}
.select2-container--default .select2-selection--single:hover {
    border-color: rgba(14, 165, 233, 0.6) !important;
}
.select2-container--default.select2-container--open .select2-selection--single {
    border-color: #0ea5e9 !important;
    box-shadow: 0 0 0 3px rgba(14, 165, 233, 0.15) !important;
}
.select2-container--default .select2-selection--single .select2-selection__rendered {
    color: #e2e8f0 !important;
    line-height: 38px !important;
    padding-left: 14px !important;
    font-size: 0.875rem !important;
}
.select2-container--default .select2-selection--single .select2-selection__placeholder {
    color: #94a3b8 !important;
}
.select2-container--default .select2-selection--single .select2-selection__arrow {
    height: 36px !important;
    right: 8px !important;
}
.select2-container--default .select2-selection--single .select2-selection__clear {
    color: #f87171 !important;
    font-size: 18px !important;
    margin-right: 8px !important;
}
.select2-dropdown {
    background: rgba(15, 23, 42, 0.98) !important;
    border: 1px solid rgba(14, 165, 233, 0.25) !important;
    border-radius: 0.5rem !important;
    box-shadow: 0 10px 25px -5px rgba(0,0,0,0.5) !important;
    overflow: hidden !important;
}
.select2-container--default .select2-search--dropdown {
    padding: 8px !important;
    background: rgba(10, 15, 25, 0.6) !important;
    border-bottom: 1px solid rgba(14, 165, 233, 0.2) !important;
}
.select2-container--default .select2-search--dropdown .select2-search__field {
    background: rgba(30, 41, 59, 0.9) !important;
    border: 1px solid rgba(14, 165, 233, 0.3) !important;
    border-radius: 0.4rem !important;
    color: #f1f5f9 !important;
    padding: 8px 12px !important;
    font-size: 0.875rem !important;
    outline: none !important;
}
.select2-container--default .select2-search--dropdown .select2-search__field:focus {
    border-color: #0ea5e9 !important;
    box-shadow: 0 0 0 2px rgba(14, 165, 233, 0.2) !important;
}
.select2-container--default .select2-results__options {
    max-height: 280px !important;
    scrollbar-width: thin !important;
    scrollbar-color: #0ea5e9 #1e293b !important;
}
.select2-container--default .select2-results__options::-webkit-scrollbar { width: 6px !important; }
.select2-container--default .select2-results__options::-webkit-scrollbar-track { background: #1e293b !important; }
.select2-container--default .select2-results__options::-webkit-scrollbar-thumb { background: #0ea5e9 !important; border-radius: 3px !important; }
.select2-container--default .select2-results__option {
    background: transparent !important;
    color: #cbd5e1 !important;
    padding: 10px 14px !important;
    font-size: 0.875rem !important;
    border-bottom: 1px solid rgba(255,255,255,0.03) !important;
    transition: background 0.15s ease !important;
}
.select2-container--default .select2-results__option[aria-selected="true"] {
    background: rgba(14, 165, 233, 0.12) !important;
    color: #38bdf8 !important;
}
.select2-container--default .select2-results__option--highlighted[aria-selected="false"] {
    background: rgba(14, 165, 233, 0.18) !important;
    color: #e2e8f0 !important;
    border-left: 3px solid #0ea5e9 !important;
    padding-left: 11px !important;
}
.select2-container--default .select2-results__option--highlighted[aria-selected="true"] {
    background: rgba(14, 165, 233, 0.25) !important;
    color: #7dd3fc !important;
    border-left: 3px solid #0ea5e9 !important;
    padding-left: 11px !important;
}
.select2-results__message,
.select2-container--default .select2-results__option--loading {
    background: transparent !important;
    color: #94a3b8 !important;
    text-align: center !important;
    font-style: italic !important;
}
.select2-container--default.select2-container--disabled .select2-selection--single {
    background: rgba(30, 41, 59, 0.5) !important;
    border-color: rgba(255,255,255,0.1) !important;
    cursor: not-allowed !important;
}
.select2-container--default.select2-container--disabled .select2-selection--single .select2-selection__rendered {
    color: #64748b !important;
}
</style>
</x-app-layout-dark>