{{-- resources/views/contracts/show.blade.php --}}
@php
    $activeStage = $contract->activeStage();
    $isInProgress = $activeStage && $activeStage->status === 'in_progress';
    $isAssignedToMe = $activeStage && $activeStage->assigned_user_id === auth()->id();
    
    $canEditWorkflow = $contract->isInReviewStageSystem() && 
                    !auth()->user()->hasRole('user') && 
                    (auth()->user()->hasAnyRole(['legal', 'acc', 'staff_fin', 'staff_tax', 'admin_legal', 'admin']));
    
    // Check if contract can have number generated
    $canGenerateNumber = $contract->status === \App\Models\Contract::STATUS_FINAL_APPROVED && 
                        empty($contract->contract_number);
    $allowedToGenerate = auth()->user()->hasAnyRole(['admin', 'legal']);
    
    // Legal internal discussion
    $isLegal = auth()->user()->hasAnyRole(['admin', 'legal']);
    $isUnderReview = $contract->status === \App\Models\Contract::STATUS_UNDER_REVIEW;
    
    // Ambil komentar internal legal
    $legalComments = \App\Models\LegalContractComment::where('contract_id', $contract->id)
        ->orderBy('created_at', 'asc')  // ASC = lama ke baru, terbaru di bawah
        ->get();
    
    // Status colors untuk badge
    $statusColors = [
        'draft' => 'bg-gray-500/10 text-gray-400 border-gray-500/30',
        'submitted' => 'bg-yellow-500/10 text-yellow-400 border-yellow-500/30',
        'under_review' => 'bg-blue-500/10 text-blue-400 border-blue-500/30',
        'revision_needed' => 'bg-orange-500/10 text-orange-400 border-orange-500/30',
        'final_approved' => 'bg-green-500/10 text-green-400 border-green-500/30',
        'executed' => 'bg-purple-500/10 text-purple-400 border-purple-500/30',
        'archived' => 'bg-gray-500/10 text-gray-400 border-gray-500/30',
    ];
    
    $contract->status_color = $statusColors[$contract->status] ?? 'bg-gray-500/10 text-gray-400 border-gray-500/30';
    $contract->status_label = ucwords(str_replace('_', ' ', $contract->status));
@endphp

<x-app-layout-dark title="Contract Details">
    <x-slot name="scripts">
<script>
    // Fungsi untuk toggle inline edit
    function toggleInlineEdit() {
        const displayPath = document.getElementById('displayPath');
        const inlineEdit = document.getElementById('inlineEdit');
        const editButton = document.getElementById('editButton');
        
        if (displayPath && inlineEdit) {
            displayPath.classList.toggle('hidden');
            inlineEdit.classList.toggle('hidden');
            
            // Ubah teks tombol
            if (editButton) {
                if (!displayPath.classList.contains('hidden')) {
                    editButton.innerHTML = `
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                        </svg>
                        Edit Path
                    `;
                } else {
                    editButton.innerHTML = `
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                        Cancel
                    `;
                }
            }
        }
    }

    // Copy to clipboard function
    function copyToClipboard(text) {
        navigator.clipboard.writeText(text).then(() => {
            // Show success toast
            const toast = document.createElement('div');
            toast.className = 'fixed top-4 right-4 px-4 py-3 bg-green-600 text-white rounded-lg shadow-lg z-50 flex items-center gap-2 animate-fade-in';
            toast.innerHTML = `
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
                <span>Link copied to clipboard!</span>
            `;
            document.body.appendChild(toast);
            
            setTimeout(() => {
                toast.remove();
            }, 3000);
        }).catch(err => {
            console.error('Failed to copy:', err);
            alert('Gagal menyalin link');
        });
    }

    // Close modal when clicking outside
    document.addEventListener('DOMContentLoaded', function() {
        const modal = document.getElementById('editPathModal');
        if (modal) {
            modal.addEventListener('click', function(e) {
                if (e.target === this) {
                    hideEditPathModal();
                }
            });
        }
        
        // Close modal with Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                hideEditPathModal();
            }
        });
    });

    // Fungsi switch tab dengan auto-scroll
    window.switchTab = function(tabName) {
        // Sembunyikan semua tab content
        document.querySelectorAll('.tab-content').forEach(el => {
            el.classList.add('hidden');
        });

        // Tampilkan tab content yang dipilih
        document.getElementById(tabName + '-tab').classList.remove('hidden');

        // Reset semua tab button
        document.querySelectorAll('.tab-btn').forEach(btn => {
            btn.classList.remove('border-blue-500', 'text-blue-600');
            btn.classList.add('border-transparent', 'text-gray-500');
        });

        // Aktifkan tab button yang dipilih
        document.querySelector('[data-tab="'+tabName+'"]')
            .classList.add('border-blue-500', 'text-blue-600');

        // Auto-scroll ke bawah untuk tab tertentu
        setTimeout(() => {
            if (tabName === 'discussion') {
                const chatContainer = document.getElementById('chat-messages');
                if (chatContainer) {
                    chatContainer.style.scrollBehavior = 'smooth';
                    chatContainer.scrollTop = chatContainer.scrollHeight;
                }
            } else if (tabName === 'history') {
                const historyTab = document.getElementById('history-tab');
                if (historyTab) {
                    const historyContainer = historyTab.querySelector('.overflow-y-auto');
                    if (historyContainer) {
                        historyContainer.style.scrollBehavior = 'smooth';
                        historyContainer.scrollTop = historyContainer.scrollHeight;
                    }
                }
            }
        }, 150);
    }

    // Observer untuk mendeteksi perubahan di review history
    function observeHistoryChanges() {
        const historyTab = document.getElementById('history-tab');
        if (!historyTab) return;
        
        const historyContainer = historyTab.querySelector('.overflow-y-auto');
        if (!historyContainer) return;
        
        // Buat MutationObserver untuk mendeteksi penambahan log baru
        const observer = new MutationObserver(function(mutations) {
            mutations.forEach(function(mutation) {
                if (mutation.type === 'childList' && mutation.addedNodes.length > 0) {
                    // Jika ada node baru ditambahkan, scroll ke bawah dengan smooth
                    historyContainer.style.scrollBehavior = 'smooth';
                    historyContainer.scrollTop = historyContainer.scrollHeight;
                    
                    // Reset scroll behavior setelah selesai
                    setTimeout(() => {
                        historyContainer.style.scrollBehavior = 'auto';
                    }, 500);
                }
            });
        });
        
        // Observasi perubahan pada container
        observer.observe(historyContainer, {
            childList: true,
            subtree: true
        });
    }

    // Inisialisasi tab saat halaman dimuat
    document.addEventListener('DOMContentLoaded', function() {
        // Set default tab ke history
        window.switchTab('history');
        
        // Auto-resize textarea
        const textareas = document.querySelectorAll('textarea');
        textareas.forEach(textarea => {
            textarea.addEventListener('input', function() {
                this.style.height = 'auto';
                this.style.height = (this.scrollHeight) + 'px';
            });
        });
        
        // Aktifkan observer untuk review history
        observeHistoryChanges();
        
        // Scroll ke bawah untuk pertama kali dengan sedikit delay
        setTimeout(() => {
            const historyTab = document.getElementById('history-tab');
            if (historyTab) {
                const historyContainer = historyTab.querySelector('.overflow-y-auto');
                if (historyContainer) {
                    historyContainer.style.scrollBehavior = 'smooth';
                    historyContainer.scrollTop = historyContainer.scrollHeight;
                    
                    setTimeout(() => {
                        historyContainer.style.scrollBehavior = 'auto';
                    }, 500);
                }
            }
        }, 200);
    });

    // Copy to clipboard function (global)
    window.copyToClipboard = function(text) {
        navigator.clipboard.writeText(text).then(() => {
            const toast = document.createElement('div');
            toast.className = 'fixed top-4 right-4 px-4 py-3 bg-green-600 text-white rounded-lg shadow-lg z-50 flex items-center gap-2 animate-fade-in';
            toast.innerHTML = `
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                </svg>
                <span>Link copied to clipboard!</span>
            `;
            document.body.appendChild(toast);
            
            setTimeout(() => {
                toast.remove();
            }, 3000);
        }).catch(err => {
            console.error('Failed to copy:', err);
            alert('Gagal menyalin link');
        });
    }
</script>
    </x-slot>

    <div class="pb-8 px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto">
        
        <!-- Header -->
        <div class="mb-8 animate-fade-in">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div class="flex-1">
                    <h1 class="text-3xl md:text-4xl font-bold mb-2">{{ $contract->title }}</h1>
                    <div class="flex items-center gap-4 text-gray-400 flex-wrap">
                        <span>
                            Document Number #: 
                            <span class="font-medium text-gray-300">
                                {{ $contract->contractNumber }}
                            </span>
                        </span>
                        <span>•</span>
                        <span class="px-2 py-1 text-xs rounded-full {{ $contract->status_color }}">
                            {{ $contract->status_label }}
                        </span>
                        <span>•</span>
                        <span>Created: {{ $contract->created_at->format('M d, Y') }}</span>
                        
                        <!-- Status indicator glow for in_progress -->
                        @if($isInProgress)
                        <span class="flex items-center gap-1">
                            <span class="relative flex h-2 w-2">
                                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-blue-400 opacity-75"></span>
                                <span class="relative inline-flex rounded-full h-2 w-2 bg-blue-500"></span>
                            </span>
                            <span class="text-blue-400 text-xs font-medium">In Progress</span>
                        </span>
                        @endif
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
                    @if(auth()->check() && auth()->user()->hasAnyRole(['legal', 'admin']))
                        <a href="{{ route('legal.workflow.edit', $contract->id) }}"
                           class="group relative inline-flex items-center justify-center px-5 py-3 bg-gradient-to-r from-purple-300 via-purple-500 to-indigo-600 hover:from-purple-700 hover:via-purple-600 hover:to-indigo-700 text-white font-medium rounded-xl transition-all duration-300 transform hover:-translate-y-0.5 active:scale-95 shadow-lg shadow-purple-500/30 hover:shadow-purple-500/50">
                            <div class="absolute inset-0 rounded-xl bg-gradient-to-r from-white/0 via-white/10 to-white/0 opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                      d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                      d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                            <span class="relative">Edit Workflow</span>
                        </a>
                    @endif
                    
                    <a href="{{ route('contracts.index') }}" 
                        class="flex items-center text-gray-400 hover:text-gray-300 transition-colors">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                            </svg>
                            Back to List
                        </a>
                    
                    @if($contract->status === 'draft' && $contract->user_id === auth()->id())
                        <a href="{{ route('contracts.edit', $contract) }}" 
                           class="group relative inline-flex items-center justify-center px-5 py-3 bg-gradient-to-r from-blue-600 via-blue-500 to-cyan-600 hover:from-blue-700 hover:via-blue-600 hover:to-cyan-700 text-white font-medium rounded-xl transition-all duration-300 transform hover:-translate-y-0.5 active:scale-95 shadow-lg shadow-blue-500/30 hover:shadow-blue-500/50">
                            <div class="absolute inset-0 rounded-xl bg-gradient-to-r from-white/0 via-white/10 to-white/0 opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                      d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                            </svg>
                            <span class="relative">Edit Draft</span>
                        </a>
                    @endif
                </div>
            </div>
        </div>
        
        <!-- Flash Messages -->
        @if(session('success'))
            <div class="mb-6 p-4 bg-green-500/10 border border-green-500/30 rounded-xl">
                <p class="text-green-400">{!! session('success') !!}</p>
            </div>
        @endif
        
        @if(session('error'))
            <div class="mb-6 p-4 bg-red-500/10 border border-red-500/30 rounded-xl">
                <p class="text-red-400">{{ session('error') }}</p>
            </div>
        @endif

        {{-- ============================= --}}
        {{-- CONTRACT NUMBER GENERATION SECTION --}}
        {{-- ============================= --}}
        @if($canGenerateNumber && $allowedToGenerate)
            <div class="mb-6 p-6 bg-gradient-to-r from-green-900/30 to-emerald-900/20 border border-green-500/30 rounded-xl shadow-lg shadow-green-500/10 animate-fade-in">
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div class="flex-1">
                        <div class="flex items-center gap-3 mb-2">
                            <div class="w-10 h-10 rounded-full bg-gradient-to-br from-green-500 to-emerald-500 flex items-center justify-center">
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-lg font-semibold text-green-300">Generate Contract Number</h3>
                                <p class="text-green-200/70 text-sm mt-1">
                                    This contract is <span class="font-medium text-green-300">FINAL APPROVED</span> and ready for official numbering.
                                </p>
                            </div>
                        </div>
                        
                        <div class="mt-3 ml-13">
                            <p class="text-sm text-green-200/70">
                                Format: <code class="bg-green-900/50 px-2 py-1 rounded text-green-300 font-mono text-xs">001/ITE-GNI/K/IX/2024</code>
                            </p>
                            <p class="text-xs text-green-200/50 mt-1">
                                Sequence/Department-GNI/K/RomanMonth/Year
                            </p>
                        </div>
                    </div>
                    
                    <div class="flex-shrink-0">
                        <form action="{{ route('contracts.generate-number', $contract) }}" method="POST">
                            @csrf
                            <button type="submit" 
                                    onclick="return confirm('Generate official contract number?\n\nThis action will: \n1. Create official contract number\n2. Cannot be undone\n3. Will notify relevant parties\n\nProceed?')"
                                    class="group relative inline-flex items-center justify-center px-6 py-3 bg-gradient-to-r from-green-600 via-emerald-500 to-teal-600 hover:from-green-700 hover:via-emerald-600 hover:to-teal-700 
                                        text-white font-semibold rounded-xl transition-all duration-300 transform hover:-translate-y-0.5 active:scale-95 shadow-lg shadow-green-500/30 hover:shadow-green-500/50">
                                <div class="absolute inset-0 rounded-xl bg-gradient-to-r from-white/0 via-white/10 to-white/0 opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                        d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                </svg>
                                <span class="relative">Generate Contract Number</span>
                            </button>
                        </form>
                        <p class="text-xs text-green-300/50 text-center mt-2">
                            Only Admin/Legal can generate
                        </p>
                    </div>
                </div>
            </div>
        @endif

        @if($contract->contract_number)
            <div class="mb-6 p-6 bg-gradient-to-r from-blue-900/30 to-cyan-900/20 border border-blue-500/30 rounded-xl shadow-lg shadow-blue-500/10">
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div class="flex-1">
                        <div class="flex items-center gap-3 mb-2">
                            <div class="w-10 h-10 rounded-full bg-gradient-to-br from-blue-500 to-cyan-500 flex items-center justify-center">
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-lg font-semibold text-blue-300">Contract Number Generated</h3>
                                <p class="text-blue-200/70 text-sm mt-1">
                                    Official contract number has been assigned
                                </p>
                            </div>
                        </div>
                        
                        <div class="mt-3 ml-13">
                            <div class="inline-block px-4 py-3 bg-blue-900/40 border border-blue-500/30 rounded-lg">
                                <p class="text-2xl font-bold text-blue-300 font-mono tracking-wider">
                                    {{ $contract->contract_number }}
                                </p>
                            </div>
                            <p class="text-xs text-blue-300/50 mt-2">
                                Generated on: {{ $contract->updated_at->format('d M Y, H:i') }} 
                                @if($contract->final_approved_at)
                                    • Approved on: {{ $contract->final_approved_at->format('d M Y') }}
                                @endif
                            </p>
                        </div>
                    </div>
                    
                    <div class="flex-shrink-0">
                        <button type="button" 
                                class="inline-flex items-center px-4 py-2 bg-blue-600/30 text-blue-300 
                                    rounded-lg cursor-default border border-blue-500/30">
                            <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                            </svg>
                            Already Generated
                        </button>
                    </div>
                </div>
            </div>
        @endif

        {{-- ============================= --}}
        {{-- PROGRESS BAR (NEW SYSTEM) --}}
        {{-- ============================= --}}
        @if($contract->isInReviewStageSystem())
            <!-- Progress Bar with glow effect for in_progress -->
            <div class="mb-6 relative">
                @if($isInProgress)
                <div class="absolute -inset-1 bg-gradient-to-r from-blue-600 to-cyan-600 rounded-xl blur opacity-20 animate-pulse-slow"></div>
                @endif
                <div class="relative">
                    @include('components.progress-bar', ['contract' => $contract])
                </div>
            </div>
        @endif

        <!-- Content Layout -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            <!-- LEFT COLUMN -->
            <div class="lg:col-span-2 space-y-6">

                {{-- ============================= --}}
                {{-- CONTRACT INFORMATION --}}
                {{-- ============================= --}}
                <div class="glass-card rounded-xl p-6 @if($isInProgress) border border-blue-500/30 shadow-lg shadow-blue-500/10 @endif">
                    <h3 class="text-lg font-semibold text-gray-300 mb-6">Document Information</h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        <!-- Column 1 -->
                        <div class="space-y-4">
                            <div>
                                <p class="text-sm text-gray-400 mb-1">Document Type</p>
                                <p class="text-gray-300 font-medium">
                                    {{ $contract->contract_type ?? 'Not specified' }}
                                </p>
                            </div>
                            
                            <div>
                                <p class="text-sm text-gray-400 mb-1">Effective Date</p>
                                <p class="text-gray-300">
                                    @if($contract->effective_date)
                                        {{ \Carbon\Carbon::parse($contract->effective_date)->format('M d, Y') }}
                                    @else
                                        <span class="text-gray-500">Not set</span>
                                    @endif
                                </p>
                            </div>
                        </div>
                        
                        <!-- Column 2 -->
                        <div class="space-y-4">
                            <div>
                                <p class="text-sm text-gray-400 mb-1">Counterparty Name</p>
                                <p class="text-gray-300 font-medium">
                                    {{ $contract->counterparty_name ?? 'Not specified' }}
                                </p>
                            </div>
                            
                            <div>
                                <p class="text-sm text-gray-400 mb-1">Expiry Date</p>
                                <p class="text-gray-300">
                                    @if($contract->expiry_date)
                                        {{ \Carbon\Carbon::parse($contract->expiry_date)->format('M d, Y') }}
                                    @else
                                        <span class="text-gray-500">Not set</span>
                                    @endif
                                </p>
                            </div>
                        </div>
                        
                        <!-- Column 3 -->
                        <div class="space-y-4">
                            <div>
                                <p class="text-sm text-gray-400 mb-1">Expected Deadline</p>
                                <p class="text-gray-300">
                                    @if($contract->drafting_deadline)
                                        <span class="font-medium {{ \Carbon\Carbon::parse($contract->drafting_deadline)->isPast() ? 'text-red-400' : 'text-green-400' }}">
                                            {{ \Carbon\Carbon::parse($contract->drafting_deadline)->format('M d, Y') }}
                                        </span>
                                        @if(\Carbon\Carbon::parse($contract->drafting_deadline)->isPast())
                                            <span class="text-xs text-red-400 ml-2">(Overdue)</span>
                                        @elseif(\Carbon\Carbon::parse($contract->drafting_deadline)->diffInDays(now()) <= 3)
                                            <span class="text-xs text-yellow-400 ml-2">(Due soon)</span>
                                        @endif
                                    @else
                                        <span class="text-gray-500">Not set</span>
                                    @endif
                                </p>
                            </div>
                            
                            <div>
                                <p class="text-sm text-gray-400 mb-1">Description</p>
                                <p class="text-gray-300">
                                    {{ $contract->description ?? 'No description provided' }}
                                </p>
                            </div>
                        </div>
                    </div>
                    
                    {{-- SYNOLOGY DRIVE LINK SECTION --}}
                    @if($contract->synology_folder_path)
                    <div class="mt-6 pt-6 border-t border-gray-700/50">
                        <div class="flex items-start gap-3">
                            <div class="w-10 h-10 rounded-lg bg-gradient-to-br from-orange-500 to-amber-500 flex items-center justify-center flex-shrink-0">
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
                                </svg>
                            </div>
                            <div class="flex-1">
                                <div class="flex items-center justify-between mb-1">
                                    <h4 class="font-medium text-gray-300">Synology Drive Link</h4>
                                    
                                    {{-- TOMBOL EDIT UNTUK ADMIN --}}
                                    @if(auth()->user()->hasRole('admin'))
                                    <div class="flex items-center gap-2">
                                        <button onclick="toggleInlineEdit()" 
                                                class="text-xs text-blue-400 hover:text-blue-300 flex items-center gap-1 px-2 py-1 rounded-lg hover:bg-blue-500/10 transition-colors"
                                                id="editButton">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                            </svg>
                                            Edit Path
                                        </button>
                                    </div>
                                    @endif
                                </div>
                                
                                <!-- Display Mode -->
                                <div id="displayPath" class="flex items-center gap-2">
                                    <span class="text-gray-300 break-all text-sm font-mono bg-gray-800 px-3 py-2 rounded flex-1">
                                        {{ $contract->synology_folder_path }}
                                    </span>
                                </div>
                                
                                <!-- Inline Edit Mode (hidden by default) -->
                                <div id="inlineEdit" class="hidden mt-3">
                                    <form action="{{ route('contracts.update-synology-path', $contract) }}" method="POST" class="space-y-3">
                                        @csrf
                                        <div>
                                            <label class="block text-xs text-gray-400 mb-1">Folder Path</label>
                                            <input type="text" 
                                                   name="synology_folder_path" 
                                                   value="{{ $contract->synology_folder_path }}"
                                                   class="w-full px-3 py-2 bg-gray-900 border border-gray-700 rounded-lg text-white text-sm font-mono focus:border-blue-500 focus:ring-1 focus:ring-blue-500/30 outline-none transition-all"
                                                   required>
                                        </div>
                                        
                                        <div>
                                            <label class="block text-xs text-gray-400 mb-1">Reason for change (optional)</label>
                                            <textarea name="reason" 
                                                      rows="2"
                                                      class="w-full px-3 py-2 bg-gray-900 border border-gray-700 rounded-lg text-gray-300 text-sm placeholder-gray-600 focus:border-blue-500 focus:ring-1 focus:ring-blue-500/30 outline-none transition-all"
                                                      placeholder="Why are you changing this path?"></textarea>
                                        </div>
                                        
                                        <div class="flex gap-2">
                                            <button type="submit" 
                                                    class="px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white text-sm rounded-lg transition-colors flex items-center gap-1">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                                </svg>
                                                Save Changes
                                            </button>
                                            <button type="button" 
                                                    onclick="toggleInlineEdit()"
                                                    class="px-3 py-1.5 bg-gray-700 hover:bg-gray-600 text-white text-sm rounded-lg transition-colors">
                                                Cancel
                                            </button>
                                        </div>
                                    </form>
                                </div>
                                
                                <p class="text-xs text-gray-500 mt-2">
                                    Shared by Legal Department for contract document access
                                </p>
                            </div>
                            
                            <!-- Copy and Open buttons -->
                            <div class="flex gap-2">
                                <button onclick="copyToClipboard('{{ $contract->synology_folder_path }}')"
                                        class="p-2 text-gray-400 hover:text-gray-300 hover:bg-gray-800 rounded-lg transition-colors"
                                        title="Copy link">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                    </svg>
                                </button>
                                <a href="{{ $contract->synology_folder_path }}" 
                                   target="_blank"
                                   class="p-2 text-gray-400 hover:text-gray-300 hover:bg-gray-800 rounded-lg transition-colors"
                                   title="Open in new tab">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                                    </svg>
                                </a>
                            </div>
                        </div>
                    </div>
                    @endif
                </div>

                {{-- ============================= --}}
                {{-- FINANCIAL INFO --}}
                {{-- ============================= --}}
                <div class="glass-card rounded-xl p-6 @if($isInProgress) border border-blue-500/20 shadow-md shadow-blue-500/10 @endif">
                    <h3 class="text-lg font-semibold text-gray-300 mb-6">Financial Information</h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Contract Value -->
                        <div class="space-y-2">
                            <div class="flex items-center justify-between">
                                <p class="text-sm font-medium text-gray-400">Contract Value</p>
                            </div>
                            <div class="min-h-[48px] flex items-center">
                                <p class="text-2xl font-bold text-gray-300">
                                    @if($contract->contract_value)
                                        {{ number_format($contract->contract_value, 2) }} {{ $contract->currency ?? 'IDR' }}
                                    @else
                                        <span class="text-lg font-medium text-gray-500">Not specified</span>
                                    @endif
                                </p>
                            </div>
                        </div>
                        
                        <!-- Additional Notes -->
                        <div class="space-y-2">
                            <div class="flex items-center justify-between">
                                <p class="text-sm font-medium text-gray-400">Additional Notes</p>
                            </div>
                            <div class="min-h-[48px]">
                                <p class="text-gray-300 leading-relaxed whitespace-pre-line">
                                    {{ $contract->additional_notes ?? 'No additional notes' }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ============================= --}}
                {{-- TABBED HISTORY & DISCUSSION --}}
                {{-- ============================= --}}
                <div class="glass-card rounded-xl p-6 mt-6" id="history-discussion-container">
                    
                    {{-- TAB NAVIGATION --}}
                    <div class="flex items-center border-b border-gray-700/50 mb-4">
                        {{-- Tab: Review History --}}
                        <button type="button"
                                onclick="switchTab('history')"
                                class="tab-btn px-4 py-2 text-sm font-medium border-b-2 transition-all duration-200 -mb-px
                                       border-blue-500 text-blue-600"
                                data-tab="history">
                            <span>Review History</span>
                            <span class="ml-2 px-2 py-0.5 bg-blue-500/20 text-blue-600 rounded-full text-xs">
                                {{ $reviewLogs->count() ?? 0 }}
                            </span>
                        </button>
                        
                        {{-- Tab: Legal Discussion (hanya untuk Legal/Admin) --}}
                        @if($isLegal)
                        <button type="button"
                                onclick="switchTab('discussion')"
                                class="tab-btn px-4 py-2 text-sm font-medium border-b-2 transition-all duration-200 -mb-px
                                       border-transparent text-gray-500 hover:text-gray-300"
                                data-tab="discussion">
                            <span>Legal Discussion</span>
                            @if($legalComments->count() > 0)
                                <span class="ml-2 px-2 py-0.5 bg-indigo-500/20 text-indigo-600 rounded-full text-xs">
                                    {{ $legalComments->count() }}
                                </span>
                            @endif
                        </button>
                        @endif
                    </div>

                    {{-- TAB CONTENT: REVIEW HISTORY --}}
                    <div id="history-tab" class="tab-content">
                        @if(empty($reviewLogs) || $reviewLogs->isEmpty())
                            <div class="text-center py-8">
                                <div class="w-12 h-12 mx-auto mb-3 text-gray-500">
                                    <svg class="w-full h-full" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" 
                                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                </div>
                                <p class="text-gray-500 text-sm">No review history yet.</p>
                                <p class="text-gray-400 text-xs mt-1">Review actions will appear here</p>
                            </div>
                        @else
                            <div class="space-y-3 max-h-[400px] overflow-y-auto pr-2 scrollbar-thin">
                                @foreach ($reviewLogs as $log)
                                    @php
                                        $metadata = is_array($log->metadata ?? null) ? $log->metadata : [];

                                        $displayNotes =
                                            !empty(trim($log->notes ?? '')) ? $log->notes
                                            : (!empty(trim($metadata['notes'] ?? '')) ? $metadata['notes']
                                            : (!empty(trim($metadata['revision_notes'] ?? '')) ? $metadata['revision_notes']
                                            : (!empty(trim($metadata['rejection_reason'] ?? '')) ? $metadata['rejection_reason']
                                            : (!empty(trim($metadata['response'] ?? '')) ? $metadata['response']
                                            : (!empty(trim($metadata['feedback'] ?? '')) ? $metadata['feedback']
                                            : null)))));

                                        $isJumpAction = in_array($log->action, [
                                            'approve_jump',
                                            'request_revision',
                                            'revision_requested',
                                        ]);

                                        $jumpToStage    = null;
                                        $jumpToReviewer = null;

                                        if ($isJumpAction) {
                                            $jumpToStage    = $metadata['to_stage']['stage_name']  ?? $metadata['to_stage_name']    ?? null;
                                            $jumpToReviewer = $metadata['to_reviewer']['name']     ?? $metadata['to_reviewer_name'] ?? $metadata['target_reviewer'] ?? null;
                                        }

                                        $actionColors = [
                                            'approve'          => 'bg-green-500/10 text-green-400 border-green-500/30',
                                            'approve_jump'     => 'bg-blue-500/10 text-blue-400 border-blue-500/30',
                                            'request_revision' => 'bg-yellow-500/10 text-yellow-400 border-yellow-500/30',
                                            'revision_requested'=> 'bg-yellow-500/10 text-yellow-400 border-yellow-500/30',
                                            'revision'         => 'bg-yellow-500/10 text-yellow-400 border-yellow-500/30',
                                            'user_response'    => 'bg-purple-500/10 text-purple-400 border-purple-500/30',
                                            'reject'           => 'bg-red-500/10 text-red-400 border-red-500/30',
                                            'final_approve'    => 'bg-emerald-500/10 text-emerald-400 border-emerald-500/30',
                                            'stage_started'    => 'bg-cyan-500/10 text-cyan-400 border-cyan-500/30',
                                            'stage_completed'  => 'bg-green-500/10 text-green-400 border-green-500/30',
                                            'stage_added'      => 'bg-indigo-500/10 text-indigo-400 border-indigo-500/30',
                                            'stage_deleted'    => 'bg-red-500/10 text-red-400 border-red-500/30',
                                            'stage_removed'    => 'bg-red-500/10 text-red-400 border-red-500/30',
                                            'workflow_updated' => 'bg-indigo-500/10 text-indigo-400 border-indigo-500/30',
                                        ];

                                        $actionColor = $actionColors[$log->action] ?? 'bg-gray-500/10 text-gray-400 border-gray-500/30';

                                        $actionLabels = [
                                            'approve'          => 'Approved',
                                            'approve_jump'     => 'Approved & Jump',
                                            'request_revision' => 'Revision Requested',
                                            'revision_requested'=> 'Revision Requested',
                                            'revision'         => 'Revision Submitted',
                                            'user_response'    => 'User Response',
                                            'reject'           => 'Rejected',
                                            'final_approve'    => 'Final Approved',
                                            'stage_started'    => 'Stage Started',
                                            'stage_completed'  => 'Stage Completed',
                                            'stage_added'      => 'Stage Added',
                                            'stage_deleted'    => 'Stage Deleted',
                                            'stage_removed'    => 'Stage Removed',
                                            'workflow_updated' => 'Workflow Updated',
                                            'sequence_changed' => 'Sequence Changed',
                                            'reviewer_changed' => 'Reviewer Changed',
                                        ];

                                        $actionLabel = $actionLabels[$log->action] ?? ucfirst(str_replace('_', ' ', $log->action));
                                    @endphp

                                    {{-- Review Log Card --}}
                                    <div class="glass-card rounded-lg p-4 bg-gray-900/30 hover:bg-gray-900/50 transition-colors border border-gray-700/30">
                                        {{-- Header: Avatar + Name + Badge --}}
                                        <div class="flex items-start justify-between mb-3">
                                            <div class="flex items-center gap-3">
                                                <div class="w-8 h-8 rounded-full bg-gradient-to-br from-blue-500/30 to-indigo-500/30 flex items-center justify-center flex-shrink-0 border border-blue-500/30">
                                                    <span class="text-sm font-semibold text-blue-400">
                                                        {{ substr($log->user->nama_user ?? 'S', 0, 1) }}
                                                    </span>
                                                </div>

                                                <div class="flex flex-col">
                                                    <div class="flex items-center gap-2 flex-wrap">
                                                        <span class="text-sm font-semibold text-gray-200">
                                                            {{ $log->user->nama_user ?? 'System' }}
                                                        </span>

                                                        @if($log->user && $log->user->jabatan)
                                                            @php
                                                                $roleDisplay = match($log->user->jabatan) {
                                                                    'ADMIN'     => 'Admin',
                                                                    'LEGAL'     => 'Legal',
                                                                    'USER'      => 'User',
                                                                    'ADMIN_FIN' => 'Admin Finance',
                                                                    'ADMIN_ACC' => 'Admin Accounting',
                                                                    'ADMIN_TAX' => 'Admin Tax',
                                                                    'STAFF_FIN' => 'Staff Finance',
                                                                    'STAFF_ACC' => 'Staff Accounting',
                                                                    'STAFF_TAX' => 'Staff Tax',
                                                                    default     => $log->user->jabatan
                                                                };
                                                            @endphp
                                                            <span class="px-1.5 py-0.5 rounded bg-gray-800 text-xs text-gray-300">
                                                                {{ $roleDisplay }}
                                                            </span>
                                                        @endif
                                                    </div>

                                                    @if($log->user && $log->user->email)
                                                        <div class="text-xs text-gray-500 mt-0.5">
                                                            {{ $log->user->email }}
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>

                                            <span class="text-xs px-3 py-1.5 rounded-full border font-medium {{ $actionColor }} shadow-sm">
                                                {{ $actionLabel }}
                                            </span>
                                        </div>

                                        {{-- Stage Info Box (kecuali stage_added & stage_deleted) --}}
                                        @if($log->stage_id && $log->stage && !in_array($log->action, ['stage_added', 'stage_deleted']))
                                            <div class="mb-3 p-3 bg-gray-800/40 rounded-lg border border-gray-700/50 space-y-3">
                                                {{-- Stage name --}}
                                                <div class="flex items-center gap-2 text-sm">
                                                    <svg class="w-4 h-4 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                                    </svg>
                                                    <span class="text-gray-300 font-medium">
                                                        Stage {{ $log->stage->sequence }}: {{ $log->stage->stage_name }}
                                                    </span>
                                                    <span class="text-xs px-2 py-0.5 rounded-full bg-gray-700 text-gray-300">
                                                        {{ $log->stage->is_user_stage ? 'USER' : strtoupper($log->stage->stage_type) }}
                                                    </span>
                                                </div>

                                                {{-- Jump / Revision target --}}
                                                @if($isJumpAction && ($jumpToStage || $jumpToReviewer))
                                                    <div class="ml-6 space-y-1">
                                                        <span class="text-xs font-medium text-blue-400 flex items-center gap-1">
                                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                                                            </svg>
                                                            {{ $log->action === 'approve_jump' ? 'Jumped to:' : 'Requested revision to:' }}
                                                        </span>
                                                        @if($jumpToStage)
                                                            <div class="flex items-center gap-2 text-xs">
                                                                <span class="text-gray-400">Stage:</span>
                                                                <span class="font-medium text-blue-300">{{ $jumpToStage }}</span>
                                                            </div>
                                                        @endif
                                                        @if($jumpToReviewer)
                                                            <div class="flex items-center gap-2 text-xs">
                                                                <span class="text-gray-400">Reviewer:</span>
                                                                <span class="font-medium text-blue-300">{{ $jumpToReviewer }}</span>
                                                            </div>
                                                        @endif
                                                    </div>
                                                @endif

                                                {{-- Timestamps --}}
                                                @if($log->stage->assigned_at || $log->stage->started_at || $log->stage->completed_at)
                                                    <div class="flex items-center gap-4 ml-6 text-xs text-gray-500">
                                                        @if($log->stage->assigned_at)
                                                            <span class="flex items-center gap-1">
                                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                                </svg>
                                                                Assigned: {{ $log->stage->assigned_at->format('M d, H:i') }}
                                                            </span>
                                                        @endif
                                                        @if($log->stage->started_at)
                                                            <span class="flex items-center gap-1">
                                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
                                                                </svg>
                                                                Started: {{ $log->stage->started_at->format('M d, H:i') }}
                                                            </span>
                                                        @endif
                                                        @if($log->stage->completed_at)
                                                            <span class="flex items-center gap-1 text-green-400">
                                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                                                </svg>
                                                                Completed: {{ $log->stage->completed_at->format('M d, H:i') }}
                                                            </span>
                                                        @endif
                                                    </div>
                                                @endif

                                                {{-- Notes --}}
                                                @if($displayNotes)
                                                    <div class="pt-3 border-t border-gray-700/50">
                                                        <div class="flex items-center gap-2 mb-1">
                                                            <svg class="w-4 h-4 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z" />
                                                            </svg>
                                                            <span class="text-sm font-medium text-gray-300">Notes:</span>
                                                        </div>
                                                        <p class="ml-6 text-gray-300 text-sm leading-relaxed whitespace-pre-line">{{ $displayNotes }}</p>

                                                        @if($log->action === 'request_revision' && isset($metadata['feedback_for_user']))
                                                            <div class="mt-3 pt-3 border-t border-gray-700/50 ml-6">
                                                                <div class="flex items-center gap-2 mb-1">
                                                                    <svg class="w-4 h-4 text-yellow-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.998-.833-2.732 0L4.732 16.5c-.77.833.192 2.5 1.732 2.5z" />
                                                                    </svg>
                                                                    <span class="text-sm font-medium text-yellow-300">Feedback for User:</span>
                                                                </div>
                                                                <p class="text-yellow-200 text-sm leading-relaxed whitespace-pre-line">{{ $metadata['feedback_for_user'] }}</p>
                                                            </div>
                                                        @endif
                                                    </div>
                                                @endif
                                            </div>
                                        @endif

                                        {{-- Stage Added Box --}}
                                        @if($log->action === 'stage_added' && isset($metadata['reviewer_name']))
                                            <div class="mb-3 p-3 bg-indigo-500/10 border border-indigo-500/20 rounded-lg">
                                                <div class="flex items-center gap-2 text-sm">
                                                    <svg class="w-4 h-4 text-indigo-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                                                    </svg>
                                                    <span class="text-indigo-300 font-medium">New Stage Added</span>
                                                </div>
                                                <div class="mt-2 ml-6 space-y-1">
                                                    <div class="flex items-center gap-2 text-sm">
                                                        <span class="text-gray-400">Stage:</span>
                                                        <span class="font-medium text-gray-300">{{ $metadata['stage_name'] ?? 'Unknown' }}</span>
                                                        @if(isset($metadata['sequence']))
                                                            <span class="text-xs px-2 py-0.5 rounded-full bg-indigo-900/30 text-indigo-300">
                                                                Sequence {{ $metadata['sequence'] }}
                                                            </span>
                                                        @endif
                                                    </div>
                                                    <div class="flex items-center gap-2 text-sm">
                                                        <span class="text-gray-400">Assigned to:</span>
                                                        <span class="font-medium text-indigo-300">{{ $metadata['reviewer_name'] }}</span>
                                                        @if(isset($metadata['reviewer_role']))
                                                            <span class="text-xs px-1.5 py-0.5 rounded bg-indigo-900/30 text-indigo-300">
                                                                {{ $metadata['reviewer_role'] }}
                                                            </span>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        @endif

                                        {{-- Stage Deleted Box --}}
                                        @if($log->action === 'stage_deleted' && isset($metadata['stage_name']))
                                            <div class="mb-3 p-3 bg-red-500/10 border border-red-500/20 rounded-lg">
                                                <div class="flex items-center gap-2 text-sm">
                                                    <svg class="w-4 h-4 text-red-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                    </svg>
                                                    <span class="text-red-300 font-medium">Stage Deleted</span>
                                                </div>
                                                <div class="mt-2 ml-6 space-y-1">
                                                    <div class="flex items-center gap-2 text-sm">
                                                        <span class="text-gray-400">Stage:</span>
                                                        <span class="font-medium text-gray-300">{{ $metadata['stage_name'] }}</span>
                                                        @if(isset($metadata['sequence']))
                                                            <span class="text-xs px-2 py-0.5 rounded-full bg-red-900/30 text-red-300">
                                                                Sequence {{ $metadata['sequence'] }}
                                                            </span>
                                                        @endif
                                                    </div>
                                                    @if(isset($metadata['reviewer']))
                                                        <div class="flex items-center gap-2 text-sm">
                                                            <span class="text-gray-400">Reviewer:</span>
                                                            <span class="font-medium text-red-300">{{ $metadata['reviewer'] }}</span>
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                        @endif

                                        {{-- Workflow Updated Box --}}
                                        @if($log->action === 'workflow_updated' && !empty($log->metadata['changes']))
                                            <div class="mt-2 p-3 bg-yellow-500/10 border border-yellow-500/20 rounded-lg">
                                                <div class="flex items-center gap-2 text-sm font-medium text-yellow-400 mb-2">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                    </svg>
                                                    Workflow Changes:
                                                </div>
                                                <div class="text-sm text-yellow-300 ml-6 space-y-1">
                                                    @foreach($log->metadata['changes'] as $change)
                                                        <div class="flex items-start gap-2">
                                                            <span class="text-yellow-400">•</span>
                                                            <span>{{ is_array($change) ? json_encode($change) : $change }}</span>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        @endif

                                        {{-- Footer: Timestamp + Label --}}
                                        <div class="mt-3 pt-3 border-t border-gray-700/30 flex justify-between items-center">
                                            <div class="flex items-center gap-2 text-xs text-gray-500">
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                </svg>
                                                <span>{{ $log->created_at->format('M d, Y H:i:s') }}</span>
                                            </div>

                                            @if($log->created_at->isToday())
                                                <span class="px-2 py-0.5 bg-blue-500/10 text-blue-400 rounded-full text-xs">Today</span>
                                            @elseif($log->created_at->isYesterday())
                                                <span class="px-2 py-0.5 bg-gray-500/10 text-gray-400 rounded-full text-xs">Yesterday</span>
                                            @elseif($log->created_at->gt(now()->subDays(7)))
                                                <span class="px-2 py-0.5 bg-green-500/10 text-green-400 rounded-full text-xs">This week</span>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>

                    {{-- TAB CONTENT: LEGAL DISCUSSION (hanya untuk Legal/Admin) --}}
                    @if($isLegal)
                    <div id="discussion-tab" class="tab-content hidden">
                        
                        {{-- LIST KOMENTAR - CHAT BUBBLE STYLE (DI ATAS, SCROLL KE BAWAH) --}}
                        <div id="chat-messages" class="max-h-[350px] overflow-y-auto pr-2 scrollbar-thin mb-4 flex flex-col">
                            <div class="flex flex-col mt-auto">
                                @forelse($legalComments as $comment)
                                    @php
                                        $isOwn = $comment->user_id === auth()->id();
                                        $metadata = is_array($comment->metadata) ? $comment->metadata : [];
                                        $isEdited = isset($metadata['edited_at']);
                                        $showAvatar = !$isOwn;
                                    @endphp
                                    
                                    <div class="flex {{ $isOwn ? 'justify-end' : 'justify-start' }} mb-3">
                                        {{-- Avatar untuk pesan orang lain --}}
                                        @if(!$isOwn)
                                        <div class="flex-shrink-0 mr-2">
                                            <div class="w-6 h-6 rounded-full bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center mt-1">
                                                <span class="text-[10px] font-bold text-white">
                                                    {{ substr($comment->legalUser->nama_user ?? 'L', 0, 1) }}
                                                </span>
                                            </div>
                                        </div>
                                        @endif
                                        
                                        {{-- Bubble chat --}}
                                        <div class="max-w-[80%]">
                                            <div class="{{ $isOwn 
                                                ? 'bg-indigo-600/20 text-indigo-100 border-indigo-500/30' 
                                                : 'bg-gray-800/60 text-gray-200 border-gray-700/50' }} 
                                                rounded-2xl px-3 py-2 border shadow-sm">
                                                
                                                {{-- Header dengan nama dan waktu --}}
                                                <div class="flex items-center gap-2 mb-1 text-xs">
                                                    <span class="font-medium {{ $isOwn ? 'text-indigo-300' : 'text-gray-300' }}">
                                                        {{ $comment->legalUser->nama_user ?? 'Legal Team' }}
                                                    </span>
                                                    @if($comment->legalUser && $comment->legalUser->jabatan)
                                                        <span class="px-1.5 py-0.5 rounded-full {{ $isOwn ? 'bg-indigo-800/30 text-indigo-300' : 'bg-gray-700 text-gray-300' }} text-[10px]">
                                                            {{ $comment->legalUser->jabatan }}
                                                        </span>
                                                    @endif
                                                    <span class="text-gray-500 text-[10px]">
                                                        {{ $comment->created_at->format('H:i') }}
                                                    </span>
                                                    @if($isEdited)
                                                        <span class="text-gray-600 text-[10px] italic">
                                                            (edited)
                                                        </span>
                                                    @endif
                                                </div>
                                                
                                                {{-- Isi pesan --}}
                                                <div class="text-sm leading-relaxed whitespace-pre-line break-words">
                                                    {{ $comment->notes }}
                                                </div>
                                                
                                                {{-- Metadata footer (edited time) --}}
                                                @if($isEdited && isset($metadata['edited_at']))
                                                <div class="mt-1 text-[10px] text-gray-500 italic">
                                                    Edited {{ \Carbon\Carbon::parse($metadata['edited_at'])->format('H:i') }}
                                                </div>
                                                @endif
                                            </div>
                                        </div>
                                        
                                        {{-- Avatar untuk pesan sendiri --}}
                                        @if($isOwn)
                                        <div class="flex-shrink-0 ml-2">
                                            <div class="w-6 h-6 rounded-full bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center mt-1">
                                                <span class="text-[10px] font-bold text-white">
                                                    {{ substr(auth()->user()->nama_user ?? 'Y', 0, 1) }}
                                                </span>
                                            </div>
                                        </div>
                                        @endif
                                    </div>
                                @empty
                                    {{-- Empty state --}}
                                    <div class="text-center py-6">
                                        <div class="w-12 h-12 mx-auto mb-2 text-gray-600">
                                            <svg class="w-full h-full" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                                            </svg>
                                        </div>
                                        <p class="text-gray-500 text-sm">Belum ada komentar legal</p>
                                        <p class="text-gray-600 text-xs mt-1">Komentar akan muncul di sini</p>
                                    </div>
                                @endforelse
                            </div>
                        </div>
                        
                        {{-- FORM KOMENTAR LEGAL (DI BAWAH) --}}
                        <div class="mt-2">
                            <form action="{{ route('contracts.legal-comment', $contract->id) }}" method="POST">
                                @csrf
                                <div class="flex gap-2">
                                    <textarea name="notes" 
                                            rows="1"
                                            class="flex-1 px-3 py-2 bg-gray-800/60 border border-gray-700 focus:border-indigo-500/50 focus:ring-1 focus:ring-indigo-500/30 rounded-lg text-gray-200 text-sm placeholder-gray-500 resize-none transition-all duration-200 outline-none"
                                            placeholder="Tulis komentar legal..."></textarea>
                                    <button type="submit"
                                            class="px-3 py-2 bg-indigo-600/20 hover:bg-indigo-600/30 text-indigo-400 rounded-lg border border-indigo-500/30 transition-all duration-200 flex items-center gap-1 text-sm">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                                        </svg>
                                        <span>Send</span>
                                    </button>
                                </div>
                            </form>
                        </div>
                        
                        {{-- Footer info --}}
                        @if($legalComments->count() > 0)
                        <div class="mt-3 pt-2 border-t border-gray-700/50 flex items-center justify-between text-xs text-gray-500">
                            <div class="flex items-center gap-2">
                                <svg class="w-3.5 h-3.5 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                </svg>
                                <span>Internal discussion</span>
                            </div>
                            
                            <span class="bg-gray-800 px-2 py-0.5 rounded-full">
                                {{ $legalComments->count() }} {{ Str::plural('comment', $legalComments->count()) }}
                            </span>
                        </div>
                        @endif
                    </div>
                    @endif
                </div>
            </div> <!-- END LEFT COLUMN -->

            <!-- RIGHT COLUMN -->
            <div class="space-y-6">

                {{-- ============================= --}}
                {{-- COUNTERPARTY --}}
                {{-- ============================= --}}
                <div class="glass-card rounded-xl p-6 @if($isInProgress) border border-blue-500/20 shadow-md shadow-blue-500/10 @endif">
                    <h3 class="text-lg font-semibold text-gray-300 mb-4">Counterparty</h3>
                    
                    <div class="space-y-3">
                        <div>
                            <p class="text-sm text-gray-400">Name</p>
                            <p class="text-gray-300 font-medium">{{ $contract->counterparty_name }}</p>
                        </div>
                        
                        @if($contract->counterparty_email)
                        <div>
                            <p class="text-sm text-gray-400">Email</p>
                            <p class="text-gray-300">{{ $contract->counterparty_email }}</p>
                        </div>
                        @endif
                        
                        @if($contract->counterparty_phone)
                        <div>
                            <p class="text-sm text-gray-400">Phone</p>
                            <p class="text-gray-300">{{ $contract->counterparty_phone }}</p>
                        </div>
                        @endif
                    </div>
                </div>

                {{-- ============================= --}}
                {{-- ACTIONS --}}
                {{-- ============================= --}}
                <div class="glass-card rounded-xl p-6 @if($isInProgress && $isAssignedToMe) border-2 border-blue-500/40 shadow-xl shadow-blue-500/20 @elseif($isInProgress) border border-blue-500/20 shadow-md shadow-blue-500/10 @endif">
                    <h3 class="text-lg font-semibold text-gray-300 mb-4">Actions</h3>
                    
                    <div class="space-y-3">
                        @if($contract->status === 'draft' && $contract->user_id === auth()->id())
                            <form action="{{ route('contracts.submit', $contract) }}" method="POST">
                                @csrf
                                <button type="submit" 
                                        onclick="return confirm('Submit this contract for legal review?')"
                                        class="group relative w-full px-4 py-3 bg-gradient-to-r from-blue-600 via-blue-500 to-cyan-600 hover:from-blue-700 hover:via-blue-600 hover:to-cyan-700 text-white font-medium rounded-xl transition-all duration-300 transform hover:-translate-y-0.5 active:scale-95 shadow-lg shadow-blue-500/30 hover:shadow-blue-500/50">
                                    <div class="absolute inset-0 rounded-xl bg-gradient-to-r from-white/0 via-white/10 to-white/0 opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                                    <span class="relative flex items-center justify-center">
                                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3V10" />
                                        </svg>
                                        Submit for Legal Review
                                    </span>
                                </button>
                            </form>
                        @endif

                        {{-- EXECUTED: hanya contract owner, saat status final_approved --}}
                        @if($contract->canBeExecuted(auth()->user()))
                            <button onclick="document.getElementById('modal-execute').showModal()"
                                    class="group relative w-full px-4 py-3 bg-gradient-to-r from-green-600 via-emerald-500 to-teal-600 hover:from-green-700 hover:via-emerald-600 hover:to-teal-700 text-white font-medium rounded-xl transition-all duration-300 transform hover:-translate-y-0.5 active:scale-95 shadow-lg shadow-green-500/30 hover:shadow-green-500/50">
                                <div class="absolute inset-0 rounded-xl bg-gradient-to-r from-white/0 via-white/10 to-white/0 opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                                <span class="relative flex items-center justify-center">
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    Mark as Executed
                                </span>
                            </button>
                        @endif

                        {{-- ARCHIVED: hanya legal, saat status executed --}}
                        @if($contract->canBeArchived(auth()->user()))
                            <button onclick="document.getElementById('modal-archive').showModal()"
                                    class="group relative w-full px-4 py-3 bg-gradient-to-r from-amber-600 via-orange-500 to-red-600 hover:from-amber-700 hover:via-orange-600 hover:to-red-700 text-white font-medium rounded-xl transition-all duration-300 transform hover:-translate-y-0.5 active:scale-95 shadow-lg shadow-orange-500/30 hover:shadow-orange-500/50">
                                <div class="absolute inset-0 rounded-xl bg-gradient-to-r from-white/0 via-white/10 to-white/0 opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                                <span class="relative flex items-center justify-center">
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4" />
                                    </svg>
                                    Archive Contract
                                </span>
                            </button>
                        @endif

                        {{-- NEW REVIEW STAGE SYSTEM --}}
                        @if($contract->isInReviewStageSystem())
                            <!-- Stage-specific actions -->
                            @if($activeStage && $isAssignedToMe)
                                <div class="relative">
                                    @if($isInProgress)
                                    <div class="absolute -inset-1 bg-gradient-to-r from-blue-500 to-cyan-500 rounded-xl blur opacity-20 animate-pulse"></div>
                                    @endif
                                    <a href="{{ route('review-stages.show', [$contract, $activeStage]) }}" 
                                    class="group relative block w-full text-center px-4 py-3 
                                        @if($isInProgress)
                                        bg-gradient-to-r from-blue-600 via-blue-500 to-cyan-600 hover:from-blue-700 hover:via-blue-600 hover:to-cyan-700
                                        @else
                                        bg-gradient-to-r from-green-600 via-emerald-500 to-teal-600 hover:from-green-700 hover:via-emerald-600 hover:to-teal-700
                                        @endif
                                        text-white font-medium rounded-xl transition-all duration-300 transform hover:-translate-y-0.5 active:scale-95 shadow-lg hover:shadow-xl">
                                        <div class="absolute inset-0 rounded-xl bg-gradient-to-r from-white/0 via-white/10 to-white/0 opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                                        <span class="relative flex items-center justify-center">
                                            @if($activeStage->status === 'assigned')
                                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
                                                </svg>
                                                Start Review (Stage {{ $activeStage->sequence  }})
                                            @elseif($activeStage->status === 'in_progress')
                                                <svg class="w-5 h-5 mr-2 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
                                                </svg>
                                                Continue Review (Stage {{ $activeStage->sequence }})
                                            @endif
                                        </span>
                                    </a>
                                </div>
                            @endif
                        
                        {{-- OLD SYSTEM - FOR BACKWARD COMPATIBILITY --}}
                        @else
                            @if(auth()->user()->hasAnyRole(['legal', 'admin']))
                                @if($contract->canStartReview())
                                    <a href="{{ route('contracts.start-review-dynamic', $contract) }}" 
                                    class="group relative block text-center px-4 py-3 bg-gradient-to-r from-green-600 via-emerald-500 to-teal-600 hover:from-green-700 hover:via-emerald-600 hover:to-teal-700 text-white font-medium rounded-xl transition-all duration-300 transform hover:-translate-y-0.5 active:scale-95 shadow-lg shadow-green-500/30 hover:shadow-green-500/50">
                                        <div class="absolute inset-0 rounded-xl bg-gradient-to-r from-white/0 via-white/10 to-white/0 opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                                        <span class="relative flex items-center justify-center">
                                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                            </svg>
                                            Start Legal Review
                                        </span>
                                    </a>
                                @elseif($contract->status === 'submitted')
                                    <div class="group relative block text-center px-4 py-3 bg-gradient-to-r from-gray-700 via-gray-600 to-gray-700 text-gray-400 font-medium rounded-xl cursor-not-allowed">
                                        <div class="absolute inset-0 rounded-xl bg-gradient-to-r from-white/0 via-white/5 to-white/0 opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                                        <span class="relative flex items-center justify-center">
                                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                            </svg>
                                            Start Legal Review (Not Ready)
                                        </span>
                                    </div>
                                @endif
                            @endif

                            @if($contract->status === 'revision_needed' && $contract->user_id === auth()->id())
                                <div class="mt-4 pt-4 border-t border-gray-700">
                                    <div class="mb-3 p-3 bg-gradient-to-r from-yellow-500/10 via-amber-500/10 to-orange-500/10 border border-yellow-500/30 rounded-lg">
                                        <p class="text-yellow-400 text-sm font-medium flex items-center gap-1">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.998-.833-2.732 0L4.732 16.5c-.77.833.192 2.5 1.732 2.5z" />
                                            </svg>
                                            Revision Required
                                        </p>
                                        <p class="text-gray-400 text-xs mt-1">
                                            Please submit a revised version of this contract
                                        </p>
                                    </div>
                                    
                                    @if($activeStage && $activeStage->is_user_stage)
                                        <div class="relative">
                                            @if($activeStage->status === 'in_progress')
                                            <div class="absolute -inset-1 bg-gradient-to-r from-yellow-500 to-orange-500 rounded-xl blur opacity-20 animate-pulse"></div>
                                            @endif
                                            
                                            <a href="{{ route('review-stages.show', [$contract, $activeStage]) }}" 
                                            class="group relative block w-full text-center px-4 py-3 
                                                    @if($activeStage->status === 'in_progress')
                                                    bg-gradient-to-r from-yellow-600 via-amber-500 to-orange-600 hover:from-yellow-700 hover:via-amber-600 hover:to-orange-700
                                                    @else
                                                    bg-gradient-to-r from-yellow-600 via-amber-500 to-orange-600 hover:from-yellow-700 hover:via-amber-600 hover:to-orange-700
                                                    @endif
                                                    text-white font-medium rounded-xl transition-all duration-300 transform hover:-translate-y-0.5 active:scale-95 shadow-lg hover:shadow-xl">
                                                <div class="absolute inset-0 rounded-xl bg-gradient-to-r from-white/0 via-white/10 to-white/0 opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                                                <span class="relative flex items-center justify-center">
                                                    @if($activeStage->status === 'assigned')
                                                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                        </svg>
                                                        Start Revision (Stage {{ $activeStage->sequence }})
                                                    @elseif($activeStage->status === 'in_progress')
                                                        <svg class="w-5 h-5 mr-2 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
                                                        </svg>
                                                        Continue Revision (Stage {{ $activeStage->sequence }})
                                                    @else
                                                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3V10" />
                                                        </svg>
                                                        Submit Revision (Stage {{ $activeStage->sequence }})
                                                    @endif
                                                </span>
                                            </a>
                                        </div>
                                    @else
                                        @if($activeStage)
                                            <a href="{{ route('review-stages.show', ['contract' => $contract, 'stage' => $activeStage]) }}" 
                                            class="group relative block w-full text-center px-4 py-3 bg-gradient-to-r from-yellow-600 via-amber-500 to-orange-600 hover:from-yellow-700 hover:via-amber-600 hover:to-orange-700 text-white font-medium rounded-xl transition-all duration-300 transform hover:-translate-y-0.5 active:scale-95 shadow-lg hover:shadow-xl">
                                                <div class="absolute inset-0 rounded-xl bg-gradient-to-r from-white/0 via-white/10 to-white/0 opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                                                <span class="relative flex items-center justify-center">
                                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 5l7 7-7 7M5 5l7 7-7 7" />
                                                    </svg>
                                                    Go to Stage {{ $activeStage->sequence }}
                                                </span>
                                            </a>
                                        @else
                                            <a href="{{ route('contracts.show', $contract) }}" 
                                            class="group relative block w-full text-center px-4 py-3 bg-gradient-to-r from-gray-600 via-gray-500 to-gray-600 hover:from-gray-700 hover:via-gray-600 hover:to-gray-700 text-white font-medium rounded-xl transition-all duration-300 transform hover:-translate-y-0.5 active:scale-95 shadow-lg hover:shadow-xl">
                                                <div class="absolute inset-0 rounded-xl bg-gradient-to-r from-white/0 via-white/5 to-white/0 opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                                                <span class="relative flex items-center justify-center">
                                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                                    </svg>
                                                    Refresh Page
                                                </span>
                                            </a>
                                        @endif
                                    @endif
                                    
                                    @if($contract->legal_feedback)
                                    <button onclick="alert('Legal Feedback:\n\n{{ addslashes($contract->legal_feedback) }}')" 
                                            class="group relative w-full mt-2 px-4 py-2 bg-gradient-to-r from-gray-800 via-gray-700 to-gray-800 hover:from-gray-700 hover:via-gray-600 hover:to-gray-700 text-gray-300 text-sm rounded-xl transition-all duration-300 transform hover:-translate-y-0.5 active:scale-95 shadow-lg shadow-gray-800/30 hover:shadow-gray-700/50">
                                        <div class="absolute inset-0 rounded-xl bg-gradient-to-r from-white/0 via-white/5 to-white/0 opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                                        <span class="relative flex items-center justify-center">
                                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                            </svg>
                                            View Feedback
                                        </span>
                                    </button>
                                    @endif
                                </div>
                            @endif
                        @endif
                    </div>
                </div>

                {{-- ============================= --}}
                {{-- REVIEW PROGRESS TIMELINE --}}
                {{-- ============================= --}}
                <div class="glass-card rounded-xl p-6">
                    <h3 class="text-lg font-semibold text-gray-300 mb-4">Review Progress</h3>

                    <div class="space-y-4">
                        @if($contract->isInReviewStageSystem())
                            <!-- Timeline untuk semua stage review -->
                            @foreach($contract->reviewStages->sortBy('sequence') as $reviewStage)
                                <div class="flex items-start gap-3">
                                    <!-- Status indicator dengan glow untuk in_progress -->
                                    <div class="flex-shrink-0">
                                        @if($reviewStage->status === 'completed')
                                            <div class="w-8 h-8 rounded-full bg-green-500/20 flex items-center justify-center">
                                                <svg class="w-4 h-4 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                                </svg>
                                            </div>
                                        @elseif($reviewStage->status === 'in_progress')
                                            <!-- Glowing indicator untuk in_progress -->
                                            <div class="relative">
                                                <div class="absolute inset-0 w-8 h-8 rounded-full bg-blue-500 animate-ping opacity-30"></div>
                                                <div class="w-8 h-8 rounded-full bg-blue-500/30 flex items-center justify-center shadow-lg shadow-blue-500/50">
                                                    <div class="w-2 h-2 rounded-full bg-blue-400 animate-pulse"></div>
                                                </div>
                                            </div>
                                        @elseif($reviewStage->status === 'assigned')
                                            <div class="w-8 h-8 rounded-full bg-yellow-500/20 flex items-center justify-center">
                                                <div class="w-2 h-2 rounded-full bg-yellow-400"></div>
                                            </div>
                                        @elseif($reviewStage->status === 'revision_requested')
                                            <div class="w-8 h-8 rounded-full bg-orange-500/20 flex items-center justify-center">
                                                <div class="w-2 h-2 rounded-full bg-orange-400"></div>
                                            </div>
                                        @else
                                            <div class="w-8 h-8 rounded-full bg-gray-700 flex items-center justify-center">
                                                <span class="text-xs text-gray-400">{{ $reviewStage->sequence }}</span>
                                            </div>
                                        @endif
                                    </div>
                                    
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center justify-between mb-1">
                                            <div class="font-medium text-gray-300">
                                                @if($reviewStage->is_user_stage)
                                                    User Review
                                                @else
                                                    {{ ucfirst(str_replace('_', ' ', $reviewStage->stage_name)) }}
                                                    @if($reviewStage->stage_name === 'legal' && $reviewStage->sequence)
                                                        {{ $reviewStage->sequence }}
                                                    @endif
                                                @endif
                                            </div>
                                            <div class="text-sm">
                                                @if($reviewStage->status === 'completed')
                                                    <span class="text-green-400">Completed</span>
                                                @elseif($reviewStage->status === 'in_progress')
                                                    <span class="text-blue-400 font-semibold animate-pulse">
                                                        <span class="flex items-center gap-1">
                                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
                                                            </svg>
                                                            In Progress
                                                        </span>
                                                    </span>
                                                @elseif($reviewStage->status === 'assigned')
                                                    <span class="text-yellow-400">Assigned</span>
                                                @elseif($reviewStage->status === 'revision_requested')
                                                    <span class="text-orange-400">Revision</span>
                                                @else
                                                    <span class="text-gray-500">Pending</span>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="text-sm text-gray-400">
                                            {{ $reviewStage->assignedUser->name ?? 'Unassigned' }}
                                        </div>
                                        
                                        @if($reviewStage->status === 'in_progress' && $reviewStage->started_at)
                                            <div class="text-xs text-blue-400 mt-1">
                                                Started: {{ $reviewStage->started_at->format('M d, H:i') }}
                                            </div>
                                        @endif
                                        
                                        @if($reviewStage->status === 'completed' && $reviewStage->completed_at)
                                            <div class="text-xs text-green-400 mt-1">
                                                Completed: {{ $reviewStage->completed_at->format('M d, H:i') }}
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        @else
                            <!-- Timeline sederhana (backward compatibility) -->
                            <div class="flex items-start">
                                <div class="w-2 h-2 mt-1.5 bg-green-500 rounded-full mr-3"></div>
                                <div>
                                    <p class="text-sm text-gray-300">Created</p>
                                    <p class="text-xs text-gray-500">{{ $contract->created_at->format('M d, Y H:i') }}</p>
                                </div>
                            </div>

                            @if($contract->status !== 'draft')
                                <div class="flex items-start">
                                    <div class="w-2 h-2 mt-1.5 bg-yellow-500 rounded-full mr-3"></div>
                                    <div>
                                        <p class="text-sm text-gray-300">Submitted</p>
                                        <p class="text-xs text-gray-500">{{ $contract->updated_at->format('M d, Y H:i') }}</p>
                                    </div>
                                </div>
                            @endif
                        @endif
                    </div>
                </div>

            </div> <!-- END RIGHT COLUMN -->
        </div> <!-- END Content Layout grid -->

        {{-- ================================ --}}
        {{-- MODALS - di luar glass-card      --}}
        {{-- ================================ --}}

        {{-- Modal Execute --}}
        @if($contract->canBeExecuted(auth()->user()))
        <dialog id="modal-execute" class="modal p-0 bg-transparent" style="border: none; padding: 0; background: transparent;">
            <div class="fixed inset-0 bg-black/70 backdrop-blur-sm" style="position: fixed; top: 0; left: 0; right: 0; bottom: 0; z-index: -1;" onclick="document.getElementById('modal-execute').close()"></div>
            <div class="glass-card rounded-xl p-0 max-w-md w-full mx-4 overflow-hidden border border-green-500/30 shadow-2xl shadow-green-500/20 relative z-50">
                <div class="bg-gradient-to-r from-green-600/20 via-emerald-500/20 to-teal-600/20 px-6 py-4 border-b border-green-500/30">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full bg-gradient-to-br from-green-500 to-emerald-500 flex items-center justify-center shadow-lg shadow-green-500/30">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <h3 class="text-xl font-bold text-green-300">Mark Contract as Executed</h3>
                    </div>
                </div>
                
                <div class="p-6">
                    <div class="mb-6">
                        <div class="flex items-start gap-3 p-4 bg-green-500/5 border border-green-500/20 rounded-lg">
                            <svg class="w-5 h-5 text-green-400 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <div>
                                <p class="text-gray-300 text-sm leading-relaxed">
                                    This confirms that the contract document has been <span class="text-green-400 font-semibold">signed and executed</span>.
                                </p>
                                <p class="text-gray-400 text-sm mt-2">
                                    This action will:
                                </p>
                                <ul class="text-gray-400 text-sm mt-1 space-y-1 list-disc list-inside">
                                    <li>Mark the contract as <span class="text-green-400">Executed</span></li>
                                    <li>Record the execution date</li>
                                    <li>Move contract to archival queue</li>
                                    <li><span class="text-yellow-400">This action cannot be undone</span></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    
                    <div class="flex justify-end gap-3">
                        <button type="button" 
                                onclick="document.getElementById('modal-execute').close()"
                                class="px-4 py-2 bg-gray-800/80 hover:bg-gray-700 text-gray-300 font-medium rounded-lg transition-all duration-200 border border-gray-700 hover:border-gray-600">
                            Cancel
                        </button>
                        
                        <form method="POST" action="{{ route('contracts.execute', $contract) }}">
                            @csrf
                            <button type="submit" 
                                    class="group relative inline-flex items-center justify-center px-5 py-2 bg-gradient-to-r from-green-600 via-emerald-500 to-teal-600 hover:from-green-700 hover:via-emerald-600 hover:to-teal-700 text-white font-medium rounded-lg transition-all duration-300 transform hover:-translate-y-0.5 active:scale-95 shadow-lg shadow-green-500/30 hover:shadow-green-500/50">
                                <div class="absolute inset-0 rounded-lg bg-gradient-to-r from-white/0 via-white/10 to-white/0 opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                                <svg class="w-4 h-4 mr-2 relative" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                                <span class="relative">Yes, Mark as Executed</span>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </dialog>
        @endif

        {{-- Modal Archive --}}
        @if($contract->canBeArchived(auth()->user()))
        <dialog id="modal-archive" class="modal p-0 bg-transparent" style="border: none; padding: 0; background: transparent;">
            <div class="fixed inset-0 bg-black/70 backdrop-blur-sm" style="position: fixed; top: 0; left: 0; right: 0; bottom: 0; z-index: -1;" onclick="document.getElementById('modal-archive').close()"></div>
            <div class="glass-card rounded-xl p-0 max-w-md w-full mx-4 overflow-hidden border border-orange-500/30 shadow-2xl shadow-orange-500/20 relative z-50">
                <div class="bg-gradient-to-r from-amber-600/20 via-orange-500/20 to-red-600/20 px-6 py-4 border-b border-orange-500/30">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full bg-gradient-to-br from-amber-500 to-orange-500 flex items-center justify-center shadow-lg shadow-orange-500/30">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4" />
                            </svg>
                        </div>
                        <h3 class="text-xl font-bold text-orange-300">Archive Contract</h3>
                    </div>
                </div>
                
                <div class="p-6">
                    <div class="mb-6">
                        <div class="flex items-start gap-3 p-4 bg-orange-500/5 border border-orange-500/20 rounded-lg">
                            <svg class="w-5 h-5 text-orange-400 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.998-.833-2.732 0L4.732 16.5c-.77.833.192 2.5 1.732 2.5z" />
                            </svg>
                            <div>
                                <p class="text-gray-300 text-sm leading-relaxed">
                                    This will permanently close the review process and mark the contract as 
                                    <span class="text-orange-400 font-semibold">Archived</span>.
                                </p>
                                <p class="text-gray-400 text-sm mt-2">
                                    Archiving a contract means:
                                </p>
                                <ul class="text-gray-400 text-sm mt-1 space-y-1 list-disc list-inside">
                                    <li>Contract moves to read-only mode</li>
                                    <li>No further actions can be taken</li>
                                    <li>Document is preserved for records</li>
                                    <li><span class="text-yellow-400">This action is permanent and cannot be undone</span></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    
                    <div class="flex justify-end gap-3">
                        <button type="button" 
                                onclick="document.getElementById('modal-archive').close()"
                                class="px-4 py-2 bg-gray-800/80 hover:bg-gray-700 text-gray-300 font-medium rounded-lg transition-all duration-200 border border-gray-700 hover:border-gray-600">
                            Cancel
                        </button>
                        
                        <form method="POST" action="{{ route('contracts.archive', $contract) }}">
                            @csrf
                            <button type="submit" 
                                    class="group relative inline-flex items-center justify-center px-5 py-2 bg-gradient-to-r from-amber-600 via-orange-500 to-red-600 hover:from-amber-700 hover:via-orange-600 hover:to-red-700 text-white font-medium rounded-lg transition-all duration-300 transform hover:-translate-y-0.5 active:scale-95 shadow-lg shadow-orange-500/30 hover:shadow-orange-500/50">
                                <div class="absolute inset-0 rounded-lg bg-gradient-to-r from-white/0 via-white/10 to-white/0 opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                                <svg class="w-4 h-4 mr-2 relative" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                                <span class="relative">Yes, Archive Contract</span>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </dialog>
        @endif

        <style>
            /* Scrollbar styling */
            .scrollbar-thin::-webkit-scrollbar {
                width: 4px;
            }
            .scrollbar-thin::-webkit-scrollbar-track {
                background: transparent;
            }
            .scrollbar-thin::-webkit-scrollbar-thumb {
                background: #4b5563;
                border-radius: 20px;
            }
            .scrollbar-thin::-webkit-scrollbar-thumb:hover {
                background: #6b7280;
            }
            
            /* Animations */
            @keyframes fade-in {
                from {
                    opacity: 0;
                    transform: translateY(-10px);
                }
                to {
                    opacity: 1;
                    transform: translateY(0);
                }
            }
            .animate-fade-in {
                animation: fade-in 0.3s ease-out;
            }
            
            /* Glass card effect */
            .glass-card {
                background: rgba(17, 25, 40, 0.75);
                backdrop-filter: blur(8px);
                border: 1px solid rgba(255, 255, 255, 0.05);
            }
            .glass-card:hover {
                background: rgba(17, 25, 40, 0.85);
                border-color: rgba(255, 255, 255, 0.1);
            }
            
            /* Pulse animation */
            @keyframes pulse-slow {
                0%, 100% { opacity: 0.2; }
                50% { opacity: 0.4; }
            }
            .animate-pulse-slow {
                animation: pulse-slow 3s ease-in-out infinite;
            }
            
            /* Auto-resize textarea */
            textarea {
                min-height: 38px;
                max-height: 150px;
            }

            /* Modal styles */
            dialog.modal {
                display: none;
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background-color: transparent;
                border: none;
                padding: 0;
                margin: 0;
                max-width: 100%;
                max-height: 100%;
                z-index: 9999;
            }

            dialog.modal[open] {
                display: flex;
                align-items: center;
                justify-content: center;
            }

            dialog.modal::backdrop {
                background-color: rgba(0, 0, 0, 0.7);
                backdrop-filter: blur(4px);
            }
        </style>

    </div>
</x-app-layout-dark>