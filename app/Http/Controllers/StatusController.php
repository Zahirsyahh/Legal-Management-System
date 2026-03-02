<?php
//UDAH GA KEPAKE
namespace App\Http\Controllers;

use App\Models\Contract;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StatusController extends Controller
{
    /**
     * User starts reviewing document in Synology
     */
    public function userStartReview(Contract $contract)
    {
        if ($contract->user_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }
        
        if (!Auth::user()->can('contract_track_status')) {
            abort(403, 'Unauthorized action.');
        }
        
        if ($contract->status !== Contract::STATUS_DOCUMENT_UPLOADED) {
            return back()->with('error', 'Document not ready for review.');
        }
        
        $contract->update([
            'status' => Contract::STATUS_USER_REVIEWING,
            'user_review_started_at' => now(),
        ]);
        
        return redirect()->route('contracts.show', $contract)
            ->with('info', 'You have started reviewing the document. Please access it in Synology.');
    }

    /**
     * User completes review
     */
    public function userCompleteReview(Request $request, Contract $contract)
    {
        if ($contract->user_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }
        
        if (!Auth::user()->can('contract_track_status')) {
            abort(403, 'Unauthorized action.');
        }
        
        $validated = $request->validate([
            'feedback' => 'nullable|string',
        ]);
        
        $contract->update([
            'status' => Contract::STATUS_USER_REVIEW_COMPLETE,
            'user_review_completed_at' => now(),
            'user_feedback' => $validated['feedback'] ?? null,
        ]);
        
        // TODO: Send notification to legal
        
        return redirect()->route('contracts.show', $contract)
            ->with('success', 'Review completed. Legal team has been notified.');
    }

    /**
     * Final release to Synology
     */
    public function finalRelease(Request $request, Contract $contract)
    {
        if (!Auth::user()->can('status_mark_released')) {
            abort(403, 'Unauthorized action.');
        }
        
        $validated = $request->validate([
            'final_notes' => 'nullable|string',
        ]);
        
        $contract->update([
            'status' => Contract::STATUS_RELEASED,
        ]);
        
        // TODO: Send notification to user
        
        return redirect()->route('contracts.show', $contract)
            ->with('success', 'Contract final released. User can now download from Synology.');
    }
}