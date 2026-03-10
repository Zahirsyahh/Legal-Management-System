@props(['contract' => null, 'action' => '', 'method' => 'POST'])

<form action="{{ $action }}" method="POST" class="space-y-6" id="contract-form">
    @csrf
    @if($method !== 'POST')
        @method($method)
    @endif
    
    <!-- Success/Error Messages -->
    @if(session('success'))
        <div class="p-4 mb-6 bg-green-500/10 border border-green-500/30 rounded-xl">
            <p class="text-green-400">{{ session('success') }}</p>
        </div>
    @endif
    
    @if(session('error'))
        <div class="p-4 mb-6 bg-red-500/10 border border-red-500/30 rounded-xl">
            <p class="text-red-400">{{ session('error') }}</p>
        </div>
    @endif
    
    <!-- Form Grid - TETAP 2 KOLOM -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Left Column -->
        <div class="space-y-6">
            <!-- Contract Title -->
            <div>
                <label for="title" class="block text-sm font-medium text-gray-300 mb-2">
                    Document Title <span class="text-red-400">*</span>
                </label>
                <input type="text" 
                       name="title" 
                       id="title" 
                       value="{{ old('title', $contract->title ?? '') }}"
                       required
                       class="w-full px-4 py-3 bg-dark-800 border border-gray-700 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors"
                       placeholder="e.g., Service Agreement with ABC Corporation">
                @error('title')
                    <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                @enderror
            </div>
            
            <!-- Document Type -->
                    <div>
                        <label for="contract_type" class="block text-sm font-medium text-gray-300 mb-2">
                            Document Type <span class="text-red-400">*</span>
                        </label>
                        <select name="contract_type" 
                                id="contract_type" 
                                required
                                class="w-full px-4 py-3 bg-dark-800 border border-gray-700 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors">
                            <option value="">Select a type</option>
                            <option value="surat" {{ old('contract_type', $contract?->contract_type ?? '') == 'surat' ? 'selected' : '' }}>Surat (S)</option>
                            <option value="kontrak" {{ old('contract_type', $contract?->contract_type ?? '') == 'kontrak' ? 'selected' : '' }}>Kontrak (K)</option>
                        </select>
                        @error('contract_type')
                            <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                        @enderror
                    </div>
            
            <!-- Description -->
            <div>
                <label for="description" class="block text-sm font-medium text-gray-300 mb-2">
                    Description
                </label>
                <textarea name="description" 
                          id="description" 
                          rows="4"
                          class="w-full px-4 py-3 bg-dark-800 border border-gray-700 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors"
                          placeholder="Brief description of the document purpose and scope">{{ old('description', $contract->description ?? '') }}</textarea>
                @error('description')
                    <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                @enderror
            </div>
            
            <!-- Dates - TETAP di Left Column, tapi pakai grid-cols-3 -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label for="effective_date" class="block text-sm font-medium text-gray-300 mb-2">
                        Effective Date
                    </label>
                    <input type="date" 
                           name="effective_date" 
                           id="effective_date"
                           value="{{ old('effective_date', $contract?->effective_date?->format('Y-m-d') ?? '') }}"
                           class="w-full px-4 py-3 bg-dark-800 border border-gray-700 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors">
                    @error('effective_date')
                        <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                    @enderror
                </div>
                
                <div>
                    <label for="expiry_date" class="block text-sm font-medium text-gray-300 mb-2">
                        Expiry Date
                    </label>
                    <input type="date" 
                           name="expiry_date" 
                           id="expiry_date"
                           value="{{ old('expiry_date', $contract?->expiry_date?->format('Y-m-d') ?? '') }}"
                           class="w-full px-4 py-3 bg-dark-800 border border-gray-700 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors">
                    @error('expiry_date')
                        <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                    @enderror
                </div>
                
                <!-- NEW: Contract Drafting Deadline -->
                <div>
                    <label for="drafting_deadline" class="block text-sm font-medium text-gray-300 mb-2">
                        Drafting Deadline
                    </label>
                    <input type="date" 
                           name="drafting_deadline" 
                           id="drafting_deadline"
                           value="{{ old('drafting_deadline', $contract?->drafting_deadline?->format('Y-m-d') ?? '') }}"
                           class="w-full px-4 py-3 bg-dark-800 border border-gray-700 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors">
                    @error('drafting_deadline')
                        <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>
        
        <!-- Right Column - SEMUA KONTEN ASLI KEMBALI -->
        <div class="space-y-6">
            <!-- Counterparty Information -->
            <div class="bg-dark-800/50 p-5 rounded-xl border border-gray-700">
                <h3 class="text-lg font-semibold text-gray-300 mb-4">Counterparty Information</h3>
                
                <div class="space-y-4">
                    <div>
                        <label for="counterparty_name" class="block text-sm font-medium text-gray-300 mb-2">
                            Counterparty Name <span class="text-red-400">*</span>
                        </label>
                        <input type="text" 
                               name="counterparty_name" 
                               id="counterparty_name"
                               value="{{ old('counterparty_name', $contract->counterparty_name ?? '') }}"
                               required
                               class="w-full px-4 py-3 bg-dark-800 border border-gray-700 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors"
                               placeholder="Company or individual name">
                        @error('counterparty_name')
                            <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="counterparty_email" class="block text-sm font-medium text-gray-300 mb-2">
                                Email Address
                            </label>
                            <input type="email" 
                                   name="counterparty_email" 
                                   id="counterparty_email"
                                   value="{{ old('counterparty_email', $contract->counterparty_email ?? '') }}"
                                   class="w-full px-4 py-3 bg-dark-800 border border-gray-700 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors"
                                   placeholder="email@example.com">
                            @error('counterparty_email')
                                <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Financial Information -->
            <div class="bg-dark-800/50 p-5 rounded-xl border border-gray-700">
                <h3 class="text-lg font-semibold text-gray-300 mb-4">Financial Information</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="contract_value" class="block text-sm font-medium text-gray-300 mb-2">
                            Contract Value
                        </label>
                        <div class="relative">
                            <input type="number" 
                                   name="contract_value" 
                                   id="contract_value"
                                   step="0.01"
                                   min="0"
                                   value="{{ old('contract_value', $contract->contract_value ?? '') }}"
                                   class="w-full px-4 py-3 pl-12 bg-dark-800 border border-gray-700 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors"
                                   placeholder="0.00">
                            <div class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400">
                            </div>
                        </div>
                        @error('contract_value')
                            <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <div>
                        <label for="currency" class="block text-sm font-medium text-gray-300 mb-2">
                            Currency
                        </label>
                        <select name="currency" 
                                id="currency"
                                class="w-full px-4 py-3 bg-dark-800 border border-gray-700 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors">
                            <option value="IDR" {{ old('currency', $contract->currency ?? 'IDR') == 'IDR' ? 'selected' : '' }}>IDR (Indonesian Rupiah)</option>
                            <option value="USD" {{ old('currency', $contract->currency ?? '') == 'USD' ? 'selected' : '' }}>USD (US Dollar)</option>
                            <option value="EUR" {{ old('currency', $contract->currency ?? '') == 'EUR' ? 'selected' : '' }}>EUR (Euro)</option>
                            <option value="SGD" {{ old('currency', $contract->currency ?? '') == 'SGD' ? 'selected' : '' }}>SGD (Singapore Dollar)</option>
                        </select>
                        @error('currency')
                            <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>
            
            <!-- Additional Notes -->
            <div>
                <label for="additional_notes" class="block text-sm font-medium text-gray-300 mb-2">
                    Additional Notes
                </label>
                <textarea name="additional_notes" 
                          id="additional_notes" 
                          rows="3"
                          class="w-full px-4 py-3 bg-dark-800 border border-gray-700 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors"
                          placeholder="Any additional information or special requirements">{{ old('additional_notes', $contract->additional_notes ?? '') }}</textarea>
                @error('additional_notes')
                    <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                @enderror
            </div>
        </div>
    </div>
    
    <!-- Form Actions -->
    <div class="flex justify-between items-center pt-6 border-t border-gray-800">
        <a href="{{ route('contracts.index') }}" 
           class="px-6 py-3 border border-gray-700 rounded-lg hover:bg-gray-800 transition-colors">
            Cancel
        </a>
        
        <div class="flex space-x-3">
            @if(isset($contract) && $contract->status === 'draft')
                <button type="button"
                        onclick="confirmSaveDraft()"
                        class="px-6 py-3 bg-gray-700 hover:bg-gray-600 rounded-lg transition-colors">
                    Save Draft
                </button>
            @endif
            
            <button type="submit"
                    class="px-6 py-3 bg-gradient-to-r from-blue-600 to-cyan-600 hover:from-blue-700 hover:to-cyan-700 rounded-lg font-medium transition-colors">
                @if(isset($contract))
                    Update Document
                @else
                    Create Document Draft
                @endif
            </button>
        </div>
    </div>
</form>

@push('scripts')
<script>
    // Set minimum date to today for effective date and drafting deadline
    const today = new Date().toISOString().split('T')[0];
    document.getElementById('effective_date').min = today;
    document.getElementById('drafting_deadline').min = today;
    
    // Auto-set expiry date min based on effective date
    document.getElementById('effective_date').addEventListener('change', function() {
        document.getElementById('expiry_date').min = this.value;
    });
    
    function confirmSaveDraft() {
        if(confirm('Save as draft? You can submit for review later.')) {
            document.getElementById('contract-form').submit();
        }
    }
    
    // Format currency input
    document.getElementById('contract_value')?.addEventListener('blur', function(e) {
        if(this.value) {
            this.value = parseFloat(this.value).toFixed(2);
        }
    });
</script>
@endpush