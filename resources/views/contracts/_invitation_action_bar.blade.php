@php
    // Detect if the current user is a dept admin with a pending invitation for this contract
    $pendingInvitation = null;
    $currentUser = Auth::user();

    if ($currentUser->hasAnyRole(['admin_fin', 'admin_acc', 'admin_tax'])) {
        // Resolve their department
        $myDept = null;
        if ($currentUser->hasRole('admin_fin'))      $myDept = \App\Models\Department::where('code', 'FIN')->first();
        elseif ($currentUser->hasRole('admin_acc'))  $myDept = \App\Models\Department::where('code', 'ACC')->first();
        elseif ($currentUser->hasRole('admin_tax'))  $myDept = \App\Models\Department::where('code', 'TAX')->first();

        if ($myDept) {
            $pendingInvitation = \App\Models\ContractDepartment::where('contract_id', $contract->id)
                ->where('department_id', $myDept->id)
                ->where('status', 'pending_assignment')
                ->first();
        }
    }

    // Route prefix based on role
    $invRoutePrefix = match(true) {
        $currentUser->hasRole('admin_fin') => 'finance',
        $currentUser->hasRole('admin_acc') => 'accounting',
        $currentUser->hasRole('admin_tax') => 'tax',
        default => null,
    };

    // Department color mapping
    $deptColorMap = [
        'finance'    => ['#10b981', '#059669', 'emerald'],
        'accounting' => ['#06b6d4', '#0284c7', 'cyan'],
        'tax'        => ['#a855f7', '#d946ef', 'purple'],
    ];
    $deptHex    = $deptColorMap[$invRoutePrefix][0] ?? '#10b981';
    $deptHex2   = $deptColorMap[$invRoutePrefix][1] ?? '#059669';
    $deptOrb    = $deptColorMap[$invRoutePrefix][2] ?? 'emerald';
@endphp

    {{-- ========================================================
        FLASH: INVITATION ACCEPTED
        ======================================================== --}}
    @if(session('invitation_accepted'))
    <div id="invitation-accepted-toast"
        class="fixed top-5 left-1/2 -translate-x-1/2 z-[9999] w-full max-w-sm px-4"
        style="animation: liquidSlideDown 0.5s cubic-bezier(0.34, 1.56, 0.64, 1) both;">
        <div style="
            display: flex;
            align-items: flex-start;
            gap: 0.75rem;
            padding: 0.875rem 1rem;
            border-radius: 0.875rem;
            background: rgba(15, 23, 42, 0.92);
            backdrop-filter: blur(20px) saturate(180%);
            -webkit-backdrop-filter: blur(20px) saturate(180%);
            border: 1px solid rgba(16, 185, 129, 0.35);
            box-shadow: 0 8px 32px rgba(0,0,0,0.35), 0 0 60px rgba(16,185,129,0.12), inset 0 1px 0 rgba(255,255,255,0.04);
            position: relative;
            overflow: hidden;
        ">
            {{-- Green glow orb background --}}
            <div style="
                position: absolute; top: 50%; left: 50%;
                transform: translate(-50%, -50%);
                width: 200px; height: 80px;
                border-radius: 50%;
                background: radial-gradient(circle, rgba(16,185,129,0.08), transparent 70%);
                filter: blur(20px);
                pointer-events: none;
            "></div>

            {{-- Icon circle --}}
            <div style="
                width: 38px; height: 38px;
                border-radius: 50%;
                background: rgba(16, 185, 129, 0.15);
                border: 1px solid rgba(16, 185, 129, 0.35);
                display: flex; align-items: center; justify-content: center;
                flex-shrink: 0;
                position: relative; z-index: 1;
            ">
                <svg width="18" height="18" fill="none" stroke="#6ee7b7" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                </svg>
            </div>

            {{-- Text --}}
            <div style="flex: 1; min-width: 0; position: relative; z-index: 1;">
                <p style="margin: 0; font-weight: 600; font-size: 0.875rem; color: #f1f5f9; line-height: 1.3;">
                    You've Joined the Workflow!
                </p>
                <p style="margin: 0.2rem 0 0 0; font-size: 0.75rem; color: #94a3b8; line-height: 1.4;">
                    {!! session('invitation_accepted') !!}
                </p>
            </div>

            {{-- Close button --}}
            <button onclick="document.getElementById('invitation-accepted-toast').remove()"
                    style="
                        background: none; border: none; cursor: pointer;
                        color: rgba(148,163,184,0.5); padding: 0.2rem;
                        border-radius: 0.4rem; flex-shrink: 0; line-height: 1;
                        transition: all 0.2s; position: relative; z-index: 1;
                    "
                    onmouseover="this.style.color='#e2e8f0'; this.style.background='rgba(255,255,255,0.08)'"
                    onmouseout="this.style.color='rgba(148,163,184,0.5)'; this.style.background='none'">
                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
    </div>
    <script>setTimeout(() => document.getElementById('invitation-accepted-toast')?.remove(), 6000);</script>
    @endif

    {{-- ========================================================
     FLASH: INVITATION DECLINED
     ======================================================== --}}
    @if(session('invitation_declined'))
    <div id="invitation-declined-toast"
        class="fixed top-5 left-1/2 -translate-x-1/2 z-[9999] w-full max-w-lg px-4"
        style="animation: liquidSlideDown 0.5s cubic-bezier(0.34, 1.56, 0.64, 1) both;">
        <div class="liquid-toast liquid-toast-error">
            <div class="liquid-toast-orb">
                <div class="liquid-orb-core error"></div>
            </div>
            <div class="liquid-toast-content">
                <p class="liquid-toast-title">Invitation Declined</p>
                <p class="liquid-toast-message">{!! session('invitation_declined') !!}</p>
            </div>
            <button onclick="document.getElementById('invitation-declined-toast').remove()"
                    class="liquid-toast-close">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
    </div>
    <script>setTimeout(() => document.getElementById('invitation-declined-toast')?.remove(), 6000);</script>
    @endif

    {{-- ========================================================
     COMPACT FLOATING GLASS CARD
     ======================================================== --}}
    @if($pendingInvitation && $invRoutePrefix)

    {{-- Hidden decline form --}}
    <form id="inv-decline-form"
        method="POST"
        action="{{ route($invRoutePrefix . '-admin.invitation.decline', $pendingInvitation) }}"
        class="hidden">
        @csrf
        <input type="hidden" name="decline_reason" id="inv-decline-reason-input">
    </form>

    {{-- Compact floating glass card --}}
    <div id="invitation-floating-card"
        class="glass-float-card"
        style="animation: floatIn 0.5s cubic-bezier(0.34, 1.56, 0.64, 1) both;">
        
        {{-- Ambient glow orb --}}
        <div class="glass-float-orb"></div>

        {{-- Content --}}
        <div class="glass-float-content">
            {{-- Icon + Text --}}
            <div class="glass-float-info">
                <div class="glass-float-icon">
                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                            d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                    </svg>
                </div>
                <div class="glass-float-text">
                    <span class="glass-float-label">Review Invitation</span>
                    <span class="glass-float-dot">•</span>
                    <span class="glass-float-contract">{{ Str::limit($contract->title, 24) }}</span>
                </div>
            </div>

            {{-- Action buttons --}}
            <div class="glass-float-actions">
                {{-- Accept --}}
                <form method="POST"
                    action="{{ route($invRoutePrefix . '-admin.invitation.accept', $pendingInvitation) }}"
                    onsubmit="return confirm('Accept review invitation?')">
                    @csrf
                    <button type="submit" class="glass-btn glass-btn-accept" title="Accept">
                        <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                        </svg>
                    </button>
                </form>

                {{-- Decline --}}
                <button type="button"
                        class="glass-btn glass-btn-decline"
                        title="Decline"
                        onclick="openInvDeclineModal()">
                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>

                {{-- Dismiss --}}
                <button type="button"
                        class="glass-btn-dismiss"
                        title="Dismiss"
                        onclick="document.getElementById('invitation-floating-card').style.display='none'">
                    <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </div>
    </div>

{{-- ========================================================
     DECLINE MODAL — Glass
     ======================================================== --}}
<div id="inv-decline-modal" class="glass-modal-overlay">
    <div id="inv-decline-modal-inner" class="glass-modal-inner">
        <div class="glass-modal-card">
            <div class="glass-modal-orb glass-modal-orb-1"></div>
            <div class="glass-modal-orb glass-modal-orb-2"></div>

            <div class="glass-modal-header">
                <div>
                    <h3 class="glass-modal-title">Decline Invitation</h3>
                    <p class="glass-modal-desc">Legal will be notified of your decision.</p>
                </div>
                <button onclick="closeInvDeclineModal()" class="glass-modal-close">
                    <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <div class="glass-modal-info">
                <p class="glass-modal-info-label">Document</p>
                <p class="glass-modal-info-title">{{ Str::limit($contract->title, 60) }}</p>
            </div>

            <textarea id="inv-decline-reason"
                      rows="2"
                      placeholder="Reason (optional)…"
                      class="glass-modal-textarea"></textarea>

            <div class="glass-modal-actions">
                <button onclick="closeInvDeclineModal()" class="glass-modal-btn-cancel">Cancel</button>
                <button onclick="submitInvDecline()" class="glass-modal-btn-confirm">
                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                    Decline
                </button>
            </div>
        </div>
    </div>
</div>

<style>
/* ------------------------------------------------------------------ */
/* KEYFRAMES                                                            */
/* ------------------------------------------------------------------ */
@keyframes liquidSlideDown {
    from { opacity: 0; transform: translateX(-50%) translateY(-30px) scale(0.95); }
    to   { opacity: 1; transform: translateX(-50%) translateY(0) scale(1); }
}
@keyframes liquidOrbPulse {
    0%, 100% { transform: scale(1); opacity: 0.6; }
    50%      { transform: scale(1.2); opacity: 1; }
}
@keyframes floatIn {
    from { opacity: 0; transform: translateY(12px) scale(0.92); }
    to   { opacity: 1; transform: translateY(0) scale(1); }
}
@keyframes orbDrift {
    0%, 100% { transform: translate(-50%, -50%) scale(1); }
    50%      { transform: translate(-50%, -50%) scale(1.3); }
}

/* ------------------------------------------------------------------ */
/* TOAST                                                                */
/* ------------------------------------------------------------------ */
.liquid-toast {
    display: flex;
    align-items: flex-start;
    gap: 0.75rem;
    padding: 1rem 1.25rem;
    border-radius: 1rem;
    position: relative;
    overflow: hidden;
    background: rgba(15, 23, 42, 0.85);
    backdrop-filter: blur(20px) saturate(180%);
    -webkit-backdrop-filter: blur(20px) saturate(180%);
    border: 1px solid rgba(255, 255, 255, 0.12);
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3), inset 0 1px 0 rgba(255, 255, 255, 0.05);
}
.liquid-toast-success { border-color: rgba(16, 185, 129, 0.4); box-shadow: 0 8px 32px rgba(0,0,0,0.3), 0 0 60px rgba(16,185,129,0.15), inset 0 1px 0 rgba(255,255,255,0.05); }
.liquid-toast-error   { border-color: rgba(239, 68, 68, 0.4); box-shadow: 0 8px 32px rgba(0,0,0,0.3), 0 0 60px rgba(239,68,68,0.15), inset 0 1px 0 rgba(255,255,255,0.05); }
.liquid-toast-orb { width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center; flex-shrink: 0; background: rgba(255,255,255,0.06); border: 1px solid rgba(255,255,255,0.15); }
.liquid-orb-core { width: 20px; height: 20px; border-radius: 50%; animation: liquidOrbPulse 2s ease-in-out infinite; }
.liquid-orb-core.success { background: radial-gradient(circle, rgba(16,185,129,0.8), rgba(16,185,129,0.3)); box-shadow: 0 0 20px rgba(16,185,129,0.5); }
.liquid-orb-core.error   { background: radial-gradient(circle, rgba(239,68,68,0.8), rgba(239,68,68,0.3)); box-shadow: 0 0 20px rgba(239,68,68,0.5); }
.liquid-toast-content { flex: 1; min-width: 0; }
.liquid-toast-title { font-weight: 600; font-size: 0.875rem; margin: 0; color: #f1f5f9; }
.liquid-toast-message { font-size: 0.75rem; margin: 0.15rem 0 0 0; color: #94a3b8; line-height: 1.4; }
.liquid-toast-close { color: rgba(148,163,184,0.5); background: none; border: none; cursor: pointer; padding: 0.25rem; border-radius: 0.5rem; flex-shrink: 0; transition: all 0.2s; margin-top: 0.125rem; }
.liquid-toast-close:hover { color: #e2e8f0; background: rgba(255,255,255,0.08); }

/* ------------------------------------------------------------------ */
/* COMPACT FLOATING GLASS CARD                                          */
/* ------------------------------------------------------------------ */
.glass-float-card {
    position: fixed;
    bottom: 24px;
    right: 24px;
    z-index: 9990;
    padding: 0.5rem 0.75rem;
    border-radius: 1rem;
    background: rgba(15, 23, 42, 0.65);
    backdrop-filter: blur(20px) saturate(180%);
    -webkit-backdrop-filter: blur(20px) saturate(180%);
    border: 1px solid rgba(255, 255, 255, 0.12);
    box-shadow:
        0 8px 32px rgba(0, 0, 0, 0.35),
        0 0 40px rgba({{ $deptOrb === 'emerald' ? '16,185,129' : ($deptOrb === 'cyan' ? '6,182,212' : '168,85,247') }}, 0.08),
        inset 0 1px 0 rgba(255, 255, 255, 0.04);
    overflow: hidden;
    max-width: calc(100vw - 48px);
}
@media (max-width: 640px) {
    .glass-float-card {
        right: 12px;
        bottom: 12px;
        left: 12px;
        max-width: none;
    }
}

/* Ambient orb */
.glass-float-orb {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    width: 120px;
    height: 120px;
    border-radius: 50%;
    background: radial-gradient(circle, {{ $deptHex }}22, transparent 70%);
    filter: blur(30px);
    pointer-events: none;
    animation: orbDrift 4s ease-in-out infinite;
}

/* Content */
.glass-float-content {
    display: flex;
    align-items: center;
    gap: 0.625rem;
    position: relative;
    z-index: 1;
    flex-wrap: wrap;
}
@media (max-width: 640px) {
    .glass-float-content {
        justify-content: space-between;
    }
}

/* Info */
.glass-float-info {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    min-width: 0;
}
.glass-float-icon {
    width: 28px;
    height: 28px;
    border-radius: 0.5rem;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    background: rgba(255, 255, 255, 0.06);
    border: 1px solid rgba(255, 255, 255, 0.1);
    color: #94a3b8;
}
.glass-float-text {
    display: flex;
    align-items: center;
    gap: 0.3rem;
    font-size: 0.7rem;
    min-width: 0;
}
.glass-float-label {
    color: #94a3b8;
    font-weight: 500;
    white-space: nowrap;
}
.glass-float-dot {
    color: #334155;
    font-size: 0.5rem;
}
.glass-float-contract {
    color: #cbd5e1;
    font-weight: 500;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    max-width: 180px;
}
@media (max-width: 640px) {
    .glass-float-contract {
        max-width: 120px;
    }
}

/* Action buttons */
.glass-float-actions {
    display: flex;
    align-items: center;
    gap: 0.3rem;
    flex-shrink: 0;
}

/* Icon buttons */
.glass-btn {
    width: 30px;
    height: 30px;
    border-radius: 0.5rem;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border: 1px solid;
    cursor: pointer;
    transition: all 0.2s ease;
    flex-shrink: 0;
}
.glass-btn-accept {
    background: rgba(16, 185, 129, 0.15);
    border-color: rgba(16, 185, 129, 0.3);
    color: #6ee7b7;
}
.glass-btn-accept:hover {
    background: rgba(16, 185, 129, 0.25);
    border-color: rgba(16, 185, 129, 0.5);
    color: #bbf7d0;
    box-shadow: 0 0 16px rgba(16, 185, 129, 0.3);
}

.glass-btn-decline {
    background: rgba(239, 68, 68, 0.1);
    border-color: rgba(239, 68, 68, 0.25);
    color: #fca5a5;
}
.glass-btn-decline:hover {
    background: rgba(239, 68, 68, 0.2);
    border-color: rgba(239, 68, 68, 0.45);
    color: #fecaca;
    box-shadow: 0 0 16px rgba(239, 68, 68, 0.25);
}

.glass-btn-dismiss {
    width: 22px;
    height: 22px;
    border-radius: 50%;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    background: rgba(255, 255, 255, 0.03);
    border: 1px solid rgba(255, 255, 255, 0.06);
    color: rgba(148, 163, 184, 0.35);
    cursor: pointer;
    transition: all 0.2s;
    flex-shrink: 0;
    margin-left: 0.15rem;
}
.glass-btn-dismiss:hover {
    background: rgba(255, 255, 255, 0.08);
    color: #94a3b8;
    border-color: rgba(255, 255, 255, 0.15);
}

/* ------------------------------------------------------------------ */
/* MODAL — GLASS                                                        */
/* ------------------------------------------------------------------ */
.glass-modal-overlay {
    position: fixed; inset: 0; z-index: 9999;
    display: flex; align-items: center; justify-content: center;
    background: rgba(2,6,23,0.7);
    backdrop-filter: blur(6px);
    -webkit-backdrop-filter: blur(6px);
    opacity: 0; pointer-events: none;
    transition: opacity 0.25s ease;
}
.glass-modal-overlay.open { opacity: 1; pointer-events: all; }

.glass-modal-inner {
    transform: scale(0.92) translateY(16px);
    transition: transform 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
    width: 100%; max-width: 420px; margin: 0 1rem;
}
.glass-modal-overlay.open .glass-modal-inner { transform: scale(1) translateY(0); }

.glass-modal-card {
    background: rgba(15,23,42,0.9);
    backdrop-filter: blur(30px) saturate(180%);
    -webkit-backdrop-filter: blur(30px) saturate(180%);
    border: 1px solid rgba(255,255,255,0.1);
    border-radius: 1.25rem;
    padding: 1.25rem;
    position: relative;
    overflow: hidden;
    box-shadow: 0 20px 50px rgba(0,0,0,0.5), 0 0 60px rgba({{ $deptOrb === 'emerald' ? '16,185,129' : ($deptOrb === 'cyan' ? '6,182,212' : '168,85,247') }},0.06), inset 0 1px 0 rgba(255,255,255,0.04);
}
.glass-modal-card::before {
    content: ''; position: absolute; inset: 0; border-radius: 1.25rem;
    background: radial-gradient(ellipse at top, rgba(255,255,255,0.03), transparent 50%);
    pointer-events: none;
}
.glass-modal-orb { position: absolute; border-radius: 50%; filter: blur(50px); pointer-events: none; opacity: 0.12; }
.glass-modal-orb-1 { width: 150px; height: 150px; top: -50px; right: -40px; background: {{ $deptHex }}; }
.glass-modal-orb-2 { width: 100px; height: 100px; bottom: -30px; left: -20px; background: {{ $deptHex2 }}; opacity: 0.08; }

.glass-modal-header { display: flex; align-items: flex-start; justify-content: space-between; margin-bottom: 0.875rem; position: relative; z-index: 1; }
.glass-modal-title { color: #f1f5f9; font-weight: 600; font-size: 1rem; margin: 0; }
.glass-modal-desc { color: #64748b; font-size: 0.75rem; margin: 0.15rem 0 0 0; }
.glass-modal-close { color: #475569; background: none; border: none; cursor: pointer; padding: 0.25rem; border-radius: 0.5rem; transition: all 0.2s; flex-shrink: 0; }
.glass-modal-close:hover { color: #e2e8f0; background: rgba(255,255,255,0.08); }

.glass-modal-info { padding: 0.6rem 0.875rem; border-radius: 0.65rem; background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.06); margin-bottom: 0.75rem; position: relative; z-index: 1; }
.glass-modal-info-label { color: #475569; font-size: 0.65rem; text-transform: uppercase; letter-spacing: 0.04em; margin: 0 0 0.15rem 0; font-weight: 600; }
.glass-modal-info-title { color: #e2e8f0; font-size: 0.8rem; font-weight: 500; margin: 0; }

.glass-modal-textarea {
    background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.08);
    color: #e2e8f0; border-radius: 0.65rem; padding: 0.6rem 0.875rem;
    font-size: 0.8rem; resize: none; width: 100%; outline: none;
    box-sizing: border-box; transition: all 0.2s; font-family: inherit;
    position: relative; z-index: 1;
}
.glass-modal-textarea::placeholder { color: #475569; }
.glass-modal-textarea:focus { border-color: rgba(239,68,68,0.5); box-shadow: 0 0 0 3px rgba(239,68,68,0.08); background: rgba(255,255,255,0.05); }

.glass-modal-actions { display: flex; justify-content: flex-end; gap: 0.5rem; margin-top: 0.875rem; position: relative; z-index: 1; }
.glass-modal-btn-cancel {
    padding: 0.45rem 1rem; font-size: 0.8rem; font-weight: 500;
    color: #94a3b8; background: rgba(255,255,255,0.04);
    border: 1px solid rgba(255,255,255,0.1); border-radius: 0.65rem;
    cursor: pointer; transition: all 0.2s;
}
.glass-modal-btn-cancel:hover { background: rgba(255,255,255,0.08); color: #e2e8f0; border-color: rgba(255,255,255,0.2); }
.glass-modal-btn-confirm {
    display: inline-flex; align-items: center; gap: 0.35rem;
    padding: 0.45rem 1rem; font-size: 0.8rem; font-weight: 600;
    color: #fca5a5; background: rgba(239,68,68,0.12);
    border: 1px solid rgba(239,68,68,0.3); border-radius: 0.65rem;
    cursor: pointer; transition: all 0.2s;
}
.glass-modal-btn-confirm:hover { background: rgba(239,68,68,0.22); border-color: rgba(239,68,68,0.5); color: #fecaca; box-shadow: 0 4px 16px rgba(239,68,68,0.2); }
</style>

<script>
function openInvDeclineModal() {
    document.getElementById('inv-decline-reason').value = '';
    const overlay = document.getElementById('inv-decline-modal');
    overlay.classList.add('open');
    setTimeout(() => document.getElementById('inv-decline-reason')?.focus(), 200);
}
function closeInvDeclineModal() {
    document.getElementById('inv-decline-modal').classList.remove('open');
}
function submitInvDecline() {
    const reason = document.getElementById('inv-decline-reason').value.trim();
    document.getElementById('inv-decline-reason-input').value = reason;
    document.getElementById('inv-decline-form').submit();
}
document.getElementById('inv-decline-modal')?.addEventListener('click', function(e) {
    if (e.target === this) closeInvDeclineModal();
});
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') closeInvDeclineModal();
});
</script>

@endif