{{--
======================================================================
resources/views/contracts/_show_revision_tasks.blade.php
======================================================================
CARA PAKAI di contracts/show.blade.php:
Tambahkan TEPAT SEBELUM section "REVIEW PROGRESS TIMELINE"
di right column:

    @include('contracts._show_revision_tasks', [
        'contract'   => $contract,
        'myReceived' => $myReceivedTasks,
        'mySent'     => $mySentTasks,
    ])

Variabel $myReceivedTasks dan $mySentTasks harus di-pass dari
ContractController::show() — lihat patch controller di bawah.
======================================================================
--}}

@if(!empty($myReceived) && $myReceived->isNotEmpty())
{{-- ── PANEL A: Revision tasks yang DITERIMA oleh user yang login ──
     Tampil untuk: Bunga, Sari, atau siapapun yang menerima revisi
     Mereka perlu kerjakan dan submit balik
--}}
<div class="glass-card rounded-xl p-5 border border-orange-500/30">
    <div class="flex items-center gap-3 mb-4">
        <div class="w-8 h-8 rounded-lg bg-orange-500/10 flex items-center justify-center flex-shrink-0">
            <svg class="w-4 h-4 text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16l-4-4m0 0l4-4m-4 4h18"/>
            </svg>
        </div>
        <div class="flex-1 min-w-0">
            <h3 class="text-sm font-semibold text-orange-400">Revisi Untukmu</h3>
            <p class="text-xs text-gray-500">Kamu diminta untuk melakukan revisi</p>
        </div>
        <span class="px-2 py-0.5 text-xs bg-orange-500/20 text-orange-400 rounded-full border border-orange-500/30">
            {{ $myReceived->count() }}
        </span>
    </div>

    <div class="space-y-3">
        @foreach($myReceived as $task)
        <div class="p-3 rounded-lg border border-orange-500/20 bg-orange-500/5">
            <div class="flex items-start justify-between gap-2 mb-2">
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-gray-300 truncate">
                        Dari: {{ $task->requester->nama_user ?? 'Unknown' }}
                    </p>
                    <p class="text-xs text-gray-500 mt-0.5">
                        {{ $task->fromStage->stage_name ?? '-' }}
                        @if($task->loop_count > 0)
                            · Loop ke-{{ $task->loop_count }}
                        @endif
                        · {{ $task->sent_at?->diffForHumans() ?? '-' }}
                    </p>
                </div>
                @php
                    $taskBadge = match($task->status) {
                        'pending'      => 'bg-yellow-500/20 text-yellow-400',
                        'in_progress'  => 'bg-blue-500/20 text-blue-400',
                        're_requested' => 'bg-orange-500/20 text-orange-400',
                        default        => 'bg-gray-500/20 text-gray-400',
                    };
                @endphp
                <span class="text-xs px-2 py-0.5 rounded-full {{ $taskBadge }} flex-shrink-0">
                    {{ $task->status_label }}
                </span>
            </div>

            {{-- Cuplikan catatan revisi --}}
            <p class="text-xs text-gray-400 line-clamp-2 mb-3 bg-gray-800/30 rounded p-2">
                {{ $task->revision_notes }}
            </p>

            <a href="{{ route('revision-tasks.show', $task) }}"
               class="block w-full text-center py-2 text-xs font-medium text-orange-400 bg-orange-500/10 rounded-lg border border-orange-500/20 hover:bg-orange-500/20 transition-all">
                Lihat & Kerjakan →
            </a>
        </div>
        @endforeach
    </div>
</div>
@endif

@if(!empty($mySent) && $mySent->isNotEmpty())
{{-- ── PANEL B: Revision tasks yang DIKIRIM oleh user yang login ──
     Tampil untuk: Bimo atau siapapun yang mengirim revisi
     Mereka bisa melihat status dan mengambil tindakan
--}}
@php
    $submittedCount = $mySent->where('status', 'submitted')->count();
    $openCount      = $mySent->whereIn('status', ['pending', 'in_progress', 're_requested'])->count();
@endphp
<div class="glass-card rounded-xl p-5 border {{ $submittedCount > 0 ? 'border-purple-500/40' : 'border-amber-500/30' }}">
    <div class="flex items-center gap-3 mb-4">
        <div class="w-8 h-8 rounded-lg {{ $submittedCount > 0 ? 'bg-purple-500/10' : 'bg-amber-500/10' }} flex items-center justify-center flex-shrink-0">
            <svg class="w-4 h-4 {{ $submittedCount > 0 ? 'text-purple-400' : 'text-amber-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
            </svg>
        </div>
        <div class="flex-1 min-w-0">
            <h3 class="text-sm font-semibold {{ $submittedCount > 0 ? 'text-purple-400' : 'text-amber-400' }}">
                Revisi yang Kamu Kirim
            </h3>
            <p class="text-xs text-gray-500">
                @if($submittedCount > 0)
                    {{ $submittedCount }} hasil menunggu keputusanmu
                @else
                    Sedang dikerjakan oleh reviewer
                @endif
            </p>
        </div>
        <div class="flex items-center gap-1 flex-shrink-0">
            @if($submittedCount > 0)
            <span class="px-2 py-0.5 text-xs bg-purple-500/20 text-purple-400 rounded-full border border-purple-500/30 animate-pulse">
                {{ $submittedCount }} perlu ditinjau
            </span>
            @endif
            @if($openCount > 0)
            <span class="px-2 py-0.5 text-xs bg-amber-500/20 text-amber-400 rounded-full border border-amber-500/30">
                {{ $openCount }} berjalan
            </span>
            @endif
        </div>
    </div>

    <div class="space-y-3">
        @foreach($mySent as $task)
        @php
            $isSubmitted = $task->status === 'submitted';
            $cardBorder  = $isSubmitted ? 'border-purple-500/30 bg-purple-500/5' : 'border-gray-700/40 bg-gray-800/20';
        @endphp
        <div class="p-3 rounded-lg border {{ $cardBorder }}">
            <div class="flex items-start justify-between gap-2 mb-2">
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-gray-300 truncate">
                        Ke: {{ $task->assignee->nama_user ?? 'Unknown' }}
                    </p>
                    <p class="text-xs text-gray-500 mt-0.5">
                        {{ $task->toStage->stage_name ?? '-' }}
                        · {{ $task->sent_at?->diffForHumans() ?? '-' }}
                    </p>
                </div>
                @php
                    $sentBadge = match($task->status) {
                        'pending'      => 'bg-yellow-500/20 text-yellow-400',
                        'in_progress'  => 'bg-blue-500/20 text-blue-400',
                        'submitted'    => 'bg-purple-500/20 text-purple-400',
                        're_requested' => 'bg-orange-500/20 text-orange-400',
                        default        => 'bg-gray-500/20 text-gray-400',
                    };
                @endphp
                <span class="text-xs px-2 py-0.5 rounded-full {{ $sentBadge }} flex-shrink-0">
                    {{ $task->status_label }}
                </span>
            </div>

            {{-- Jika sudah submitted: tampilkan cuplikan respons --}}
            @if($task->response_notes)
            <div class="mb-2 p-2 bg-purple-500/5 border border-purple-500/20 rounded text-xs text-gray-300 line-clamp-2">
                <span class="text-purple-400 font-medium">Hasil: </span>{{ $task->response_notes }}
            </div>
            @endif

            {{-- Tombol aksi inline untuk submitted task --}}
            @if($isSubmitted)
            <div class="flex gap-2">
                <form action="{{ route('revision-tasks.approve', $task) }}" method="POST" class="flex-1">
                    @csrf
                    <button type="submit"
                            class="w-full py-1.5 text-xs font-medium bg-green-600/20 hover:bg-green-600/30 text-green-400 border border-green-500/30 rounded-lg transition-all"
                            onclick="return confirm('Setujui hasil revisi dari {{ addslashes($task->assignee->nama_user ?? '-') }}?')">
                        ✓ Approve
                    </button>
                </form>
                <a href="{{ route('revision-tasks.show', $task) }}"
                   class="px-3 py-1.5 text-xs text-gray-400 border border-gray-700/50 rounded-lg hover:bg-gray-700/50 transition-all flex items-center">
                    Detail
                </a>
            </div>
            @else
            <a href="{{ route('revision-tasks.show', $task) }}"
               class="block w-full text-center py-1.5 text-xs text-gray-400 border border-gray-700/50 rounded-lg hover:bg-gray-700/50 transition-all">
                Lihat Detail →
            </a>
            @endif
        </div>
        @endforeach
    </div>
</div>
@endif