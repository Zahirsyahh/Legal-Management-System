@php
    $statusClasses = [
        'draft' => 'status-draft',
        'submitted' => 'status-submitted',
        'under_review' => 'status-under_review',
        'final_approved' => 'status-final_approved',
        'number_issued' => 'status-number_issued',
        'released' => 'status-released',
        'revision_needed' => 'status-revision_needed',
        'declined' => 'status-declined',
    ];
    
    $statusLabels = [
        'draft' => 'Draft',
        'submitted' => 'Submitted',
        'under_review' => 'Under Review',
        'final_approved' => 'Final Approved',
        'number_issued' => 'Number Issued',
        'released' => 'Released',
        'revision_needed' => 'Revision Needed',
        'declined' => 'Declined',
    ];
@endphp

@if(isset($status) && array_key_exists($status, $statusClasses))
    <span class="{{ $statusClasses[$status] }} px-3 py-1.5 rounded-full text-sm font-medium">
        {{ $statusLabels[$status] }}
    </span>
@else
    <span class="status-draft px-3 py-1.5 rounded-full text-sm font-medium">
        {{ $status ?? 'Unknown' }}
    </span>
@endif