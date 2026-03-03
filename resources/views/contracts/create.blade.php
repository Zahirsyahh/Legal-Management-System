<x-app-layout-dark title="Create New Contract">
    <div class="pb-8 px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto">
        <!-- Header -->
        <div class="mb-8 animate-fade-in">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl md:text-4xl font-bold mb-2">Create New Document Review</h1>
                    <p class="text-gray-400">Fill in the document details below. All fields marked with <span class="text-red-400">*</span> are required.</p>
                </div>
                <a href="{{ route('contracts.index') }}" class="flex items-center text-blue-400 hover:text-blue-300">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Back to Contracts
                </a>
            </div>
        </div>

        <!-- Form Card -->
        <div class="glass-card rounded-2xl p-6 animate-slide-up">
            @include('contracts.form', [
                'action' => route('contracts.store'),
                'method' => 'POST',
                'contract' => null
            ])
        </div>
        
        <!-- Help Text -->
        <div class="mt-6 p-4 bg-blue-500/10 border border-blue-500/30 rounded-xl">
            <div class="flex items-start">
                <svg class="w-5 h-5 text-blue-400 mt-0.5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <div>
                    <h4 class="text-sm font-medium text-blue-300 mb-1">About Draft Contracts</h4>
                    <p class="text-sm text-gray-400">
                        Your contract will be saved as a draft. You can edit it later and submit for review when ready. 
                        Once submitted, the Legal team will review your contract.
                    </p>
                </div>
            </div>
        </div>
    </div>
</x-app-layout-dark>