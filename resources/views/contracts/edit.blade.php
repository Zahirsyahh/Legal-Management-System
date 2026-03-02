<x-app-layout-dark title="Edit Contract">
    <div class="pb-8 px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto">
        <!-- Header -->
        <div class="mb-8 animate-fade-in">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl md:text-4xl font-bold mb-2">Edit Contract</h1>
                    <p class="text-gray-400">
                        Editing: <span class="font-medium text-gray-300">{{ $contract->title }}</span>
                        <span class="mx-2">•</span>
                        Status: <span class="px-2 py-1 text-xs rounded-full bg-gray-700 text-gray-300">{{ $contract->status_label }}</span>
                    </p>
                </div>
                <a href="{{ route('contracts.show', $contract) }}" class="flex items-center text-blue-400 hover:text-blue-300">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Back to Contract
                </a>
            </div>
        </div>

        @if($contract->status !== 'draft')
            <div class="mb-6 p-4 bg-yellow-500/10 border border-yellow-500/30 rounded-xl">
                <div class="flex items-center">
                    <svg class="w-5 h-5 text-yellow-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.998-.833-2.732 0L4.732 16.5c-.77.833.192 2.5 1.732 2.5z" />
                    </svg>
                    <div>
                        <p class="text-yellow-300 font-medium">Contract cannot be edited</p>
                        <p class="text-sm text-gray-400 mt-1">
                            This contract is currently in <span class="font-medium">{{ $contract->status_label }}</span> status and cannot be edited. 
                            Only draft contracts can be modified.
                        </p>
                    </div>
                </div>
            </div>
        @endif

        <!-- Form Card -->
        <div class="glass-card rounded-2xl p-6 animate-slide-up">
            @if($contract->status === 'draft')
                @include('contracts.form', [
                    'action' => route('contracts.update', $contract),
                    'method' => 'PUT',
                    'contract' => $contract
                ])
            @else
                <div class="text-center py-10">
                    <svg class="mx-auto h-16 w-16 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                    </svg>
                    <h3 class="mt-6 text-lg font-medium text-gray-300">Editing Restricted</h3>
                    <p class="mt-2 text-gray-500 max-w-md mx-auto">
                        This contract is no longer in draft status and cannot be edited. 
                        If you need to make changes, please contact the Legal team.
                    </p>
                    <div class="mt-6">
                        <a href="{{ route('contracts.show', $contract) }}" class="inline-flex items-center px-5 py-2.5 bg-blue-600 hover:bg-blue-700 rounded-lg font-medium transition-colors">
                            View Contract Details
                        </a>
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-app-layout-dark>