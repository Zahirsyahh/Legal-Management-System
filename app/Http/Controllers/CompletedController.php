<?php

namespace App\Http\Controllers;

use App\Models\Contract;
use App\Models\ContractReviewStage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CompletedController extends Controller
{
    public function index(Request $request)
    {
        // Determine department based on user role/route
        $department = $this->getDepartmentFromRoute();
        
        // Base query for contracts with final_approved status
        $query = Contract::where('status', 'final_approved')
            ->whereHas('reviewStages', function ($q) use ($department) {
                $q->where('stage_type', strtolower($department));
            })
            ->with(['reviewStages' => function ($q) use ($department) {
                $q->where('stage_type', strtolower($department))
                  ->with('assignedUser');
            }])
            ->with('contractDepartments.department');
        
        // Search filter
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('contract_number', 'like', "%{$search}%")
                  ->orWhere('counterparty_name', 'like', "%{$search}%");
            });
        }
        
        // Contract type filter
        if ($request->has('type') && $request->type) {
            $query->where('contract_type', $request->type);
        }
        
        // Counterparty filter
        if ($request->has('counterparty') && $request->counterparty) {
            $query->where('counterparty_name', $request->counterparty);
        }
        
        // Date filter
        if ($request->has('date_filter')) {
            $dateFilter = $request->date_filter;
            $now = now();
            
            switch ($dateFilter) {
                case 'today':
                    $query->whereDate('final_approved_at', $now->toDateString());
                    break;
                case 'week':
                    $query->whereBetween('final_approved_at', [
                        $now->startOfWeek(),
                        $now->endOfWeek()
                    ]);
                    break;
                case 'month':
                    $query->whereBetween('final_approved_at', [
                        $now->startOfMonth(),
                        $now->endOfMonth()
                    ]);
                    break;
                case 'quarter':
                    $query->whereBetween('final_approved_at', [
                        $now->startOfQuarter(),
                        $now->endOfQuarter()
                    ]);
                    break;
                case 'year':
                    $query->whereBetween('final_approved_at', [
                        $now->startOfYear(),
                        $now->endOfYear()
                    ]);
                    break;
            }
        }
        
        // Sort order
        $sortField = 'final_approved_at';
        $sortOrder = 'desc';
        
        if ($request->has('sort')) {
            switch ($request->sort) {
                case 'oldest':
                    $sortField = 'final_approved_at';
                    $sortOrder = 'asc';
                    break;
                case 'value_high':
                    $sortField = 'contract_value';
                    $sortOrder = 'desc';
                    break;
                case 'value_low':
                    $sortField = 'contract_value';
                    $sortOrder = 'asc';
                    break;
                case 'newest':
                default:
                    $sortField = 'final_approved_at';
                    $sortOrder = 'desc';
                    break;
            }
        }
        
        $query->orderBy($sortField, $sortOrder);
        
        // Get contracts with pagination
        $contracts = $query->paginate(15);
        
        // Get unique counterparties for filter
        $counterparties = Contract::where('status', 'final_approved')
            ->whereHas('reviewStages', function ($q) use ($department) {
                $q->where('stage_type', strtolower($department));
            })
            ->distinct()
            ->pluck('counterparty_name')
            ->filter()
            ->sort()
            ->values();
        
        // Calculate stats
        $monthlyCount = Contract::where('status', 'final_approved')
            ->whereHas('reviewStages', function ($q) use ($department) {
                $q->where('stage_type', strtolower($department));
            })
            ->whereMonth('final_approved_at', now()->month)
            ->whereYear('final_approved_at', now()->year)
            ->count();
        
        $totalValue = Contract::where('status', 'final_approved')
            ->whereHas('reviewStages', function ($q) use ($department) {
                $q->where('stage_type', strtolower($department));
            })
            ->sum('contract_value') ?? 0;
        
        $totalContracts = Contract::whereHas('reviewStages', function ($q) use ($department) {
                $q->where('stage_type', strtolower($department));
            })->count();
        
        // Calculate average duration from creation to final approval
        $avgDuration = Contract::where('status', 'final_approved')
            ->whereHas('reviewStages', function ($q) use ($department) {
                $q->where('stage_type', strtolower($department));
            })
            ->whereNotNull('final_approved_at')
            ->whereNotNull('created_at')
            ->select(DB::raw('AVG(DATEDIFF(final_approved_at, created_at)) as avg_days'))
            ->first()
            ->avg_days ?? 0;
        
        $avgDuration = round($avgDuration, 1);
        
        // Count active reviewers from this department
        $activeReviewers = ContractReviewStage::where('stage_type', strtolower($department))
            ->whereIn('status', ['assigned', 'in_progress'])
            ->whereNotNull('assigned_user_id')
            ->distinct()
            ->count('assigned_user_id');
        
        return view('departments.completed', compact(
            'contracts',
            'department',
            'counterparties',
            'monthlyCount',
            'totalValue',
            'totalContracts',
            'avgDuration',
            'activeReviewers'
        ));
    }
    
    private function getDepartmentFromRoute()
    {
        $routePrefix = request()->route()->getPrefix();
        
        if (str_contains($routePrefix, 'finance')) {
            return 'FIN';
        } elseif (str_contains($routePrefix, 'accounting')) {
            return 'ACC';
        } elseif (str_contains($routePrefix, 'tax')) {
            return 'TAX';
        }
        
        return 'FIN';
    }
    
    public function history($id)
    {
        $contract = Contract::with(['reviewStages' => function ($q) {
            $q->with('assignedUser')
              ->orderBy('sequence');
        }])->findOrFail($id);
        
        return response()->json([
            'contract_number' => $contract->contract_number,
            'title' => $contract->title,
            'review_stages' => $contract->reviewStages->map(function ($stage) {
                return [
                    'stage_name' => $stage->stage_name,
                    'status' => $stage->status,
                    'assigned_user_name' => $stage->assignedUser->name ?? null,
                    'completed_at' => $stage->completed_at ? $stage->completed_at->format('M d, Y H:i') : null,
                ];
            })
        ]);
    }
}