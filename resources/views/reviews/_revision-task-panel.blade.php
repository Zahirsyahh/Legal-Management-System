{{--
=======================================================================
_revision-task-panel.blade.php  — COMPACT REDESIGN
Ganti seluruh isi file dengan ini.
=======================================================================
--}}

<style>
.rtask-card {
    background: rgba(15,23,42,.5);
    border: 1px solid rgba(255,255,255,.07);
    border-radius: .625rem;
    transition: border-color .2s;
}
.rtask-card:hover { border-color: rgba(255,255,255,.13); }
.rtask-pill {
    display:inline-flex; align-items:center;
    padding:.15rem .55rem; border-radius:9999px;
    font-size:.65rem; font-weight:600; border:1px solid;
}
.rtp-pending   { background:rgba(245,158,11,.1);  color:#fbbf24; border-color:rgba(245,158,11,.3); }
.rtp-progress  { background:rgba(59,130,246,.1);  color:#60a5fa; border-color:rgba(59,130,246,.3); }
.rtp-submitted { background:rgba(168,85,247,.1);  color:#c084fc; border-color:rgba(168,85,247,.3); }
.rtp-approved  { background:rgba(34,197,94,.1);   color:#4ade80; border-color:rgba(34,197,94,.3);  }
.rtp-rerequested{ background:rgba(249,115,22,.1); color:#fb923c; border-color:rgba(249,115,22,.3); }
.rtp-cancelled { background:rgba(107,114,128,.1); color:#9ca3af; border-color:rgba(107,114,128,.3);}
</style>

{{-- ── A. SENT REVISION TASKS ──────────────────────────────────── --}}
@if($sentRevisionTasks->isNotEmpty())
<div class="glass-card rounded-xl overflow-hidden mb-4">
    {{-- Header --}}
    <div class="flex items-center justify-between px-4 py-3 border-b border-white/[.06]">
        <div class="flex items-center gap-2">
            <svg class="w-3.5 h-3.5 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
            </svg>
            <span class="text-sm font-semibold text-amber-400">Sent Revisions</span>
        </div>
        <div class="flex items-center gap-1.5">
            @php $sub = $sentRevisionTasks->where('status','submitted')->count(); $open = $sentRevisionTasks->whereIn('status',['pending','in_progress','re_requested'])->count(); @endphp
            @if($sub)  <span class="rtask-pill rtp-submitted">{{ $sub }} need review</span> @endif
            @if($open) <span class="rtask-pill rtp-pending">{{ $open }} ongoing</span> @endif
        </div>
    </div>

    {{-- Task list --}}
    <div class="divide-y divide-white/[.04]">
        @foreach($sentRevisionTasks as $t)
        @php
            $pillClass = match($t->status) {
                'pending'      => 'rtp-pending',
                'in_progress'  => 'rtp-progress',
                'submitted'    => 'rtp-submitted',
                'approved'     => 'rtp-approved',
                're_requested' => 'rtp-rerequested',
                default        => 'rtp-cancelled',
            };
        @endphp
        <div class="px-4 py-3">
            <div class="flex items-center justify-between mb-1.5">
                <div class="flex items-center gap-2 min-w-0">
                    <div class="w-6 h-6 rounded-full bg-gray-700/70 flex items-center justify-center text-xs font-bold text-gray-300 flex-shrink-0">
                        {{ substr($t->assignee?->nama_user ?? 'X', 0, 1) }}
                    </div>
                    <span class="text-sm font-medium text-gray-300 truncate">{{ $t->assignee?->nama_user ?? '?' }}</span>
                    @if($t->loop_count > 0)
                        <span class="text-xs text-gray-600 flex-shrink-0">Loop {{ $t->loop_count }}</span>
                    @endif
                </div>
                <span class="rtask-pill {{ $pillClass }} flex-shrink-0 ml-2">{{ $t->status_label }}</span>
            </div>

            {{-- Notes (collapsible after 2 lines) --}}
            <p class="text-xs text-gray-500 line-clamp-2 mb-2">{{ $t->revision_notes }}</p>

            {{-- Response --}}
            @if($t->response_notes)
            <div class="mb-2 px-2.5 py-1.5 rounded-lg bg-purple-500/8 border border-purple-500/20">
                <p class="text-xs text-purple-400 mb-0.5">Response:</p>
                <p class="text-xs text-gray-300 line-clamp-2">{{ $t->response_notes }}</p>
            </div>
            @endif

            {{-- Actions --}}
            @if($canAct && $t->status === 'submitted')
            <div class="flex gap-1.5 mt-2">
                <form action="{{ route('revision-tasks.approve', $t) }}" method="POST" class="flex-1">
                    @csrf
                    <button type="submit" onclick="return confirm('Approve this revision?')"
                            class="w-full py-1.5 text-xs font-medium bg-green-600/15 hover:bg-green-600/25 text-green-400 border border-green-500/25 rounded-lg transition-all">
                        ✓ Approve
                    </button>
                </form>
                <button type="button" onclick="toggleRR('rr-{{ $t->id }}')"
                        class="flex-1 py-1.5 text-xs font-medium bg-orange-600/15 hover:bg-orange-600/25 text-orange-400 border border-orange-500/25 rounded-lg transition-all">
                    ↩ Re-request
                </button>
                <a href="{{ route('revision-tasks.show', $t) }}"
                   class="px-2.5 py-1.5 text-xs text-gray-400 border border-gray-700/40 rounded-lg hover:bg-gray-700/40 transition-all">⤢</a>
            </div>
            {{-- Re-request form --}}
            <div id="rr-{{ $t->id }}" class="hidden mt-2">
                <form action="{{ route('revision-tasks.re-request', $t) }}" method="POST">
                    @csrf
                    <textarea name="revision_notes" rows="2" required minlength="10" maxlength="2000"
                              class="w-full bg-gray-800/50 border border-orange-500/25 rounded-lg px-3 py-2 text-gray-300 text-xs outline-none mb-1.5"
                              placeholder="What still needs to be revised? (min 10 chars)"></textarea>
                    <div class="flex gap-1.5">
                        <button type="submit" class="flex-1 py-1.5 text-xs bg-orange-600/25 text-orange-300 rounded-lg border border-orange-500/25">Send Again</button>
                        <button type="button" onclick="toggleRR('rr-{{ $t->id }}')" class="px-3 py-1.5 text-xs text-gray-400 border border-gray-700/40 rounded-lg">Cancel</button>
                    </div>
                </form>
            </div>
            @elseif(!in_array($t->status, ['approved','cancelled']))
            <a href="{{ route('revision-tasks.show', $t) }}"
               class="mt-1.5 block text-center text-xs text-gray-600 hover:text-gray-400 transition-colors">View detail →</a>
            @endif
        </div>
        @endforeach
    </div>
</div>
@endif

{{-- ── B. RECEIVED REVISION TASKS ──────────────────────────────── --}}
@if($receivedRevisionTasks->isNotEmpty())
<div class="glass-card rounded-xl overflow-hidden mb-4">
    <div class="flex items-center gap-2 px-4 py-3 border-b border-white/[.06]">
        <svg class="w-3.5 h-3.5 text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16l-4-4m0 0l4-4m-4 4h18"/>
        </svg>
        <span class="text-sm font-semibold text-orange-400">Incoming Revisions</span>
        <span class="rtask-pill rtp-rerequested ml-auto">{{ $receivedRevisionTasks->count() }}</span>
    </div>
    <div class="divide-y divide-white/[.04]">
        @foreach($receivedRevisionTasks as $t)
        <div class="px-4 py-3 flex items-center gap-3">
            <div class="flex-1 min-w-0">
                <p class="text-xs font-medium text-gray-300 truncate">
                    From: <span class="text-orange-300">{{ $t->requester?->nama_user ?? '?' }}</span>
                </p>
                <p class="text-xs text-gray-500 line-clamp-1 mt-0.5">{{ $t->revision_notes }}</p>
            </div>
            <a href="{{ route('revision-tasks.show', $t) }}"
               class="flex-shrink-0 px-3 py-1.5 text-xs font-medium text-orange-400 bg-orange-500/10 rounded-lg border border-orange-500/20 hover:bg-orange-500/20 transition-all whitespace-nowrap">
                Work on →
            </a>
        </div>
        @endforeach
    </div>
</div>
@endif

{{-- ── C. SEND NEW REVISION FORM ────────────────────────────────── --}}
@if($canAct && $stage->status === 'in_progress' && !$isRevisionTarget)
<div class="glass-card rounded-xl overflow-hidden border-l-2 border-amber-500/60">
    {{-- Header toggle --}}
    <button type="button" onclick="togglePanel('revFormBody')"
            class="w-full flex items-center justify-between px-4 py-3 text-left hover:bg-white/[.02] transition-all group">
        <div class="flex items-center gap-2">
            <svg class="w-3.5 h-3.5 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
            </svg>
            <span class="text-sm font-semibold text-amber-400">Send Revision Request</span>
        </div>
        <svg id="revFormChevron" class="w-4 h-4 text-gray-500 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
        </svg>
    </button>

    {{-- Collapsible body --}}
    <div id="revFormBody" class="hidden border-t border-white/[.06]">
        <form action="{{ route('revision-tasks.send', [$contract, $stage]) }}" method="POST" id="sendRevisionForm" class="p-4 space-y-3">
            @csrf

            {{-- Recipients --}}
            <div>
                <div class="flex items-center justify-between mb-2">
                    <label class="text-xs font-medium text-gray-400">Recipients <span class="text-red-400">*</span></label>
                    @if($revisionTargetStages->isNotEmpty())
                    <label class="flex items-center gap-1.5 cursor-pointer text-xs text-gray-500 hover:text-gray-300 transition-colors">
                        <input type="checkbox" id="selAll" class="w-3 h-3 accent-amber-500"> All
                    </label>
                    @endif
                </div>
                <div class="space-y-1 max-h-40 overflow-y-auto pr-1">
                    @forelse($revisionTargetStages as $ts)
                    <label class="flex items-center gap-2.5 p-2 rounded-lg cursor-pointer hover:bg-amber-500/5 border border-transparent hover:border-amber-500/20 transition-all">
                        <input type="checkbox" name="to_stage_ids[]" value="{{ $ts->id }}" class="rcb w-3.5 h-3.5 accent-amber-500 flex-shrink-0">
                        <div class="flex-1 min-w-0">
                            <span class="text-xs font-medium text-gray-300">{{ $ts->assignedUser?->nama_user ?? 'Unassigned' }}</span>
                            <span class="text-xs text-gray-600 ml-1">· Stage {{ $ts->sequence }}</span>
                        </div>
                        @if($ts->status === 'completed')
                            <span class="rtask-pill rtp-approved flex-shrink-0">Done</span>
                        @endif
                    </label>
                    @empty
                    <p class="text-xs text-gray-600 text-center py-3">No reviewers available</p>
                    @endforelse
                </div>
                @error('to_stage_ids') <p class="text-xs text-red-400 mt-1">{{ $message }}</p> @enderror
            </div>

            {{-- Notes --}}
            <div>
                <div class="flex items-center justify-between mb-1.5">
                    <label class="text-xs font-medium text-gray-400">Notes <span class="text-red-400">*</span></label>
                    <span class="text-xs text-gray-600" id="revCharCnt">0/2000</span>
                </div>
                <textarea name="revision_notes" rows="3" required minlength="10" maxlength="2000" id="revNotesTA"
                          class="w-full bg-gray-800/50 border border-gray-700/40 rounded-lg px-3 py-2 text-gray-300 text-xs outline-none focus:border-amber-500/40 focus:ring-1 focus:ring-amber-500/20 transition-all resize-none"
                          placeholder="Explain what needs to be revised (min 10 characters)..."></textarea>
                @error('revision_notes') <p class="text-xs text-red-400 mt-1">{{ $message }}</p> @enderror
            </div>

            {{-- Info --}}
            <p class="text-xs text-gray-600 flex items-start gap-1.5">
                <svg class="w-3 h-3 text-blue-500 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                Your stage stays active. Recipients work in parallel.
            </p>

            <button type="submit" id="sendRevBtn"
                    class="w-full py-2.5 bg-gradient-to-r from-amber-500/80 to-orange-600/80 hover:from-amber-500 hover:to-orange-600 rounded-lg text-sm font-semibold text-white transition-all">
                📨 Send Revision
            </button>
        </form>
    </div>
</div>
@endif

{{-- ── D. OPEN REVISION NOTICE ──────────────────────────────────── --}}
@if($stage->has_open_revision && $stage->status === 'in_progress')
@php $openRev = $stage->openRevisionCount(); $subRev = $stage->pendingDecisionCount(); @endphp
@if($openRev > 0 || $subRev > 0)
<div class="mt-3 px-3 py-2.5 rounded-lg bg-blue-500/5 border border-blue-500/15 flex items-start gap-2">
    <svg class="w-3.5 h-3.5 text-blue-400 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
    </svg>
    <div class="text-xs text-gray-500 space-y-0.5">
        @if($openRev)  <p>⏳ <span class="text-yellow-400">{{ $openRev }}</span> reviewer(s) still working</p> @endif
        @if($subRev)   <p>📬 <span class="text-purple-400">{{ $subRev }}</span> awaiting your decision</p> @endif
        <p class="text-gray-600 mt-1">Approving will auto-cancel pending tasks.</p>
    </div>
</div>
@endif
@endif

<script>
function toggleRR(id) {
    const el = document.getElementById(id);
    if (el) el.classList.toggle('hidden');
}
function togglePanel(id) {
    const el = document.getElementById(id);
    const ch = document.getElementById('revFormChevron');
    if (!el) return;
    el.classList.toggle('hidden');
    if (ch) ch.style.transform = el.classList.contains('hidden') ? '' : 'rotate(180deg)';
}
document.addEventListener('DOMContentLoaded', () => {
    // Char counter
    const ta = document.getElementById('revNotesTA');
    const ct = document.getElementById('revCharCnt');
    if (ta && ct) ta.addEventListener('input', () => ct.textContent = ta.value.length + '/2000');

    // Select all
    const selAll = document.getElementById('selAll');
    const rcbs   = document.querySelectorAll('.rcb');
    if (selAll) {
        selAll.addEventListener('change', () => rcbs.forEach(cb => cb.checked = selAll.checked));
        rcbs.forEach(cb => cb.addEventListener('change', () => {
            selAll.checked       = [...rcbs].every(c => c.checked);
            selAll.indeterminate = !selAll.checked && [...rcbs].some(c => c.checked);
        }));
    }

    // Form validation
    const form = document.getElementById('sendRevisionForm');
    if (form) {
        form.addEventListener('submit', e => {
            if (![...rcbs].some(c => c.checked)) {
                e.preventDefault(); return alert('Select at least 1 recipient.');
            }
            const n = document.querySelector('[name="revision_notes"]');
            if (!n?.value.trim() || n.value.trim().length < 10) {
                e.preventDefault(); n?.focus(); return alert('Notes must be at least 10 characters.');
            }
            const btn = document.getElementById('sendRevBtn');
            if (btn) { btn.disabled = true; btn.textContent = 'Sending...'; }
        });
    }
});
</script>