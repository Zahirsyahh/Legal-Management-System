<?php

namespace App\Http\Controllers;

use App\Models\Contract;
use App\Models\ContractRevision;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Models\ContractReviewStage;
use App\Models\ContractReviewLog;
use Illuminate\Support\Facades\DB;
use App\Services\ContractNumberService;
use App\Models\TblUser;
use App\Notifications\DocumentSubmittedNotification;
use App\Notifications\DocumentReturnedToDraftNotification;
use Illuminate\Support\Facades\Storage;
use App\Models\LegalContractComment;

class ContractController extends Controller
{
    public function index(Request $request)
{
    $user = Auth::user();

    /*
    |--------------------------------------------------------------------------
    | DETERMINE DEFAULT TAB
    |--------------------------------------------------------------------------
    */
    $activeTab = $request->get('tab');

    if (!$activeTab) {
        $activeTab = $user->can('contract_view_all')
            ? 'all'
            : 'requests';
    }

    /*
    |--------------------------------------------------------------------------
    | VALIDATE TAB ACCESS
    |--------------------------------------------------------------------------
    */
    $allowedTabs = ['requests', 'reviews'];

    if ($user->can('contract_view_all')) {
        $allowedTabs[] = 'all';
    }

    if (!in_array($activeTab, $allowedTabs)) {
        abort(403);
    }

    /*
    |--------------------------------------------------------------------------
    | BASE QUERY
    |--------------------------------------------------------------------------
    */
    $query = Contract::with([
        'user',
        'legalAssigned',
        'financeAssigned',
        'accountingAssigned',
        'taxAssigned',
        'reviewStages'
    ]);

    /*
    |--------------------------------------------------------------------------
    | TAB VISIBILITY LOGIC
    |--------------------------------------------------------------------------
    */

    if ($activeTab === 'requests') {

        // 📝 My Requests
        $query->where('user_id', $user->id_user);

    } elseif ($activeTab === 'reviews') {

        // 🔍 My Reviews
        $query->whereHas('reviewStages', function ($q) use ($user) {
            $q->where('assigned_user_id', $user->id_user);
        });

    } elseif ($activeTab === 'all') {

        if (!$user->can('contract_view_all')) {
            abort(403);
        }

        // Admin & Legal → full access
        if (!$user->hasRole('admin') && !$user->hasRole('legal')) {

            $stageMap = [
                'admin_fin'  => 'finance',
                'staff_fin'  => 'finance',
                'admin_acc'  => 'accounting',
                'staff_acc'  => 'accounting',
                'admin_tax'  => 'tax',
                'staff_tax'  => 'tax',
            ];

            foreach ($stageMap as $role => $stageType) {
                if ($user->hasRole($role)) {
                    $query->whereHas('reviewStages', function ($q) use ($stageType) {
                        $q->where('stage_type', $stageType);
                    });
                    break;
                }
            }
        }
    }

    /*
    |--------------------------------------------------------------------------
    | FILTER STATUS (MULTIPLE)
    |--------------------------------------------------------------------------
    */
    if ($request->filled('status')) {
        $statuses = $request->input('status');
        if (!is_array($statuses)) {
            $statuses = [$statuses];
        }
        $query->whereIn('status', $statuses);
    }

    /*
    |--------------------------------------------------------------------------
    | FILTER CONTRACT TYPE (MULTIPLE)
    |--------------------------------------------------------------------------
    */
    if ($request->filled('contract_type')) {
        $types = $request->input('contract_type');
        if (!is_array($types)) {
            $types = [$types];
        }
        $query->whereIn('contract_type', $types);
    }

    /*
    |--------------------------------------------------------------------------
    | DATE RANGE FILTER
    |--------------------------------------------------------------------------
    */
    if ($request->filled('date_range')) {
        $range = $request->date_range;
        $now = now();

        switch ($range) {
            case 'today':
                $query->whereDate('created_at', $now->toDateString());
                break;

            case 'week':
                $query->whereBetween('created_at', [
                    $now->startOfWeek(),
                    $now->copy()->endOfWeek()
                ]);
                break;

            case 'month':
                $query->whereMonth('created_at', $now->month)
                      ->whereYear('created_at', $now->year);
                break;

            case 'quarter':
                $query->whereBetween('created_at', [
                    $now->copy()->subMonths(3),
                    now()
                ]);
                break;

            case 'year':
                $query->whereYear('created_at', $now->year);
                break;
        }
    }

    /*
    |--------------------------------------------------------------------------
    | SEARCH FILTER
    |--------------------------------------------------------------------------
    */
    if ($request->filled('search')) {
        $search = $request->search;

        $query->where(function ($q) use ($search) {
            $q->where('title', 'like', "%{$search}%")
              ->orWhere('contract_number', 'like', "%{$search}%")
              ->orWhere('counterparty_name', 'like', "%{$search}%")
              ->orWhere('description', 'like', "%{$search}%");
        });
    }

    /*
    |--------------------------------------------------------------------------
    | SORTING
    |--------------------------------------------------------------------------
    */
    if ($request->filled('sort')) {

        $sortField = $request->sort;
        $direction = 'asc';

        if (str_contains($sortField, '_desc')) {
            $sortField = str_replace('_desc', '', $sortField);
            $direction = 'desc';
        } elseif (str_contains($sortField, '_asc')) {
            $sortField = str_replace('_asc', '', $sortField);
            $direction = 'asc';
        } else {
            $direction = $request->get('direction', 'desc');
        }

        $query->orderBy($sortField, $direction);

    } else {
        $query->latest();
    }

    /*
    |--------------------------------------------------------------------------
    | PAGINATION
    |--------------------------------------------------------------------------
    */
    $contracts = $query->paginate(10)->withQueryString();

    /*
    |--------------------------------------------------------------------------
    | AJAX REQUEST HANDLER
    |--------------------------------------------------------------------------
    */
    if ($request->ajax()) {
        return view('contracts.partials.table', compact('contracts'))->render();
    }

    return view('contracts.index', compact('contracts', 'activeTab'));
}



    public function scopeVisibleTo($query, $user)
    {
        if ($user->hasAnyRole(['admin', 'legal'])) {
            return $query;
        }

        return $query->where(function ($q) use ($user) {
            $q->where('user_id', $user->id)
            ->orWhereHas('reviewStages', function ($q2) use ($user) {
                $q2->where('assigned_user_id', $user->id);
            });
        });
    }

    /**
     * Legal INDEX - IMPROVED VERSION
     */
    public function indexForLegal(Request $request)
    {
        $user = Auth::user();

        $query = Contract::whereIn('status', [
            'submitted',
            'under_review',
            'legal_reviewing',
            'legal_approved',
            'revision_needed',
            'final_approved',
        ])->with(['user', 'financeAssigned', 'accountingAssigned', 'taxAssigned']);

        if ($request->filled('assigned_to_me') && $request->assigned_to_me == '1') {
            $query->where('legal_assigned_id', $user->id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('contract_number', 'like', "%{$search}%")
                  ->orWhere('counterparty_name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($request->filled('sort')) {
            $direction = $request->get('direction', 'desc');
            $query->orderBy($request->sort, $direction);
        } else {
            $query->latest();
        }

        $contracts = $query->paginate(10)->withQueryString();

        return view('contracts.index', compact('contracts'));
    }

    /**
     * Finance INDEX - IMPROVED VERSION
     */
    public function indexForFinance(Request $request)
    {
        $user = Auth::user();

        $query = Contract::whereIn('status', [
            'legal_approved',
            'finance_reviewing',
            'finance_approved',
            'under_review',
            'revision_needed',
        ])->with(['user', 'legalAssigned', 'financeAssigned']);

        if ($request->filled('assigned_to_me') && $request->assigned_to_me == '1') {
            $query->where(function($q) use ($user) {
                $q->where('finance_assigned_id', $user->id)
                  ->orWhereHas('reviewStages', function($q2) use ($user) {
                      $q2->where('stage_type', 'finance')
                         ->where('assigned_user_id', $user->id);
                  });
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('contract_number', 'like', "%{$search}%")
                  ->orWhere('counterparty_name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($request->filled('sort')) {
            $direction = $request->get('direction', 'desc');
            $query->orderBy($request->sort, $direction);
        } else {
            $query->latest();
        }

        $contracts = $query->paginate(10)->withQueryString();

        return view('contracts.finance-index', compact('contracts'));
    }

    /**
     * Accounting INDEX - IMPROVED VERSION
     */
    public function indexForAccounting(Request $request)
    {
        $user = Auth::user();

        $query = Contract::whereIn('status', [
            'finance_approved',
            'accounting_reviewing',
            'accounting_approved',
            'under_review',
            'revision_needed',
        ])->with(['user', 'financeAssigned', 'accountingAssigned']);

        if ($request->filled('assigned_to_me') && $request->assigned_to_me == '1') {
            $query->where(function($q) use ($user) {
                $q->where('accounting_assigned_id', $user->id)
                  ->orWhereHas('reviewStages', function($q2) use ($user) {
                      $q2->where('stage_type', 'accounting')
                         ->where('assigned_user_id', $user->id);
                  });
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('contract_number', 'like', "%{$search}%")
                  ->orWhere('counterparty_name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($request->filled('sort')) {
            $direction = $request->get('direction', 'desc');
            $query->orderBy($request->sort, $direction);
        } else {
            $query->latest();
        }

        $contracts = $query->paginate(10)->withQueryString();

        return view('contracts.accounting-index', compact('contracts'));
    }

    /**
     * Tax INDEX - IMPROVED VERSION
     */
    public function indexForTax(Request $request)
    {
        $user = Auth::user();

        $query = Contract::whereIn('status', [
            'accounting_approved',
            'tax_reviewing',
            'tax_approved',
            'under_review',
            'revision_needed',
        ])->with(['user', 'accountingAssigned', 'taxAssigned']);

        if ($request->filled('assigned_to_me') && $request->assigned_to_me == '1') {
            $query->where(function($q) use ($user) {
                $q->where('tax_assigned_id', $user->id)
                  ->orWhereHas('reviewStages', function($q2) use ($user) {
                      $q2->where('stage_type', 'tax')
                         ->where('assigned_user_id', $user->id);
                  });
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('contract_number', 'like', "%{$search}%")
                  ->orWhere('counterparty_name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($request->filled('sort')) {
            $direction = $request->get('direction', 'desc');
            $query->orderBy($request->sort, $direction);
        } else {
            $query->latest();
        }

        $contracts = $query->paginate(10)->withQueryString();

        return view('contracts.tax-index', compact('contracts'));
    }

    /**
     * Admin INDEX - IMPROVED VERSION
     */
    public function indexForAdmin(Request $request)
    {
        $query = Contract::with(['user', 'legalAssigned', 'financeAssigned', 'accountingAssigned', 'taxAssigned']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('contract_number', 'like', "%{$search}%")
                  ->orWhere('counterparty_name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($request->filled('sort')) {
            $direction = $request->get('direction', 'desc');
            $query->orderBy($request->sort, $direction);
        } else {
            $query->latest();
        }

        $contracts = $query->paginate(10)->withQueryString();

        return view('contracts.admin-index', compact('contracts'));
    }

    /**
     * My Reviews - For staff (Accounting, Finance, Tax)
     */
    public function myReviews(Request $request)
    {
        $user = Auth::user();
        
        $query = ContractReviewStage::where('assigned_user_id', $user->id)
            ->with(['contract.user', 'contract.financeAssigned', 'contract.accountingAssigned', 'contract.taxAssigned'])
            ->whereHas('contract');
        
        // Filter by stage type
        if ($request->filled('stage_type')) {
            $query->where('stage_type', $request->stage_type);
        }
        
        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('contract', function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('contract_number', 'like', "%{$search}%");
            });
        }
        
        // Sort
        if ($request->filled('sort')) {
            $direction = $request->get('direction', 'desc');
            
            if ($request->sort === 'contract_title') {
                $query->join('contracts', 'contract_review_stages.contract_id', '=', 'contracts.id')
                      ->orderBy('contracts.title', $direction);
            } elseif ($request->sort === 'contract_number') {
                $query->join('contracts', 'contract_review_stages.contract_id', '=', 'contracts.id')
                      ->orderBy('contracts.contract_number', $direction);
            } else {
                $query->orderBy($request->sort, $direction);
            }
        } else {
            $query->orderBy('created_at', 'desc');
        }
        
        $stages = $query->paginate(10)->withQueryString();
        
        return view('reviews.my-reviews', compact('stages'));
    }

    /**
     * Department Dashboard Data
     */
    public function getDashboardData()
    {
        $user = Auth::user();
        
        if ($user->hasRole('staff_acc') || $user->hasRole('admin_acc')) {
            // Accounting Dashboard Data
            $totalAssigned = ContractReviewStage::where('assigned_user_id', $user->id)
                ->where('stage_type', 'accounting')
                ->count();
            
            $activeReviews = ContractReviewStage::where('assigned_user_id', $user->id)
                ->where('stage_type', 'accounting')
                ->where('status', 'in_progress')
                ->count();
                
            $pendingReviews = ContractReviewStage::where('assigned_user_id', $user->id)
                ->where('stage_type', 'accounting')
                ->where('status', 'pending')
                ->count();
                
            $assignedStages = ContractReviewStage::where('assigned_user_id', $user->id)
                ->where('stage_type', 'accounting')
                ->whereIn('status', ['pending', 'in_progress'])
                ->with(['contract' => function($query) {
                    $query->select('id', 'title', 'contract_number', 'contract_type', 'contract_value', 'currency', 'drafting_deadline', 'user_id')
                          ->with(['user:id,name']);
                }])
                ->orderBy('created_at', 'desc')
                ->get();
                
            return [
                'totalAssignedCount' => $totalAssigned,
                'activeReviewCount' => $activeReviews,
                'pendingReviewCount' => $pendingReviews,
                'assignedStages' => $assignedStages,
                'urgentCount' => $assignedStages->where('contract.drafting_deadline', '<=', now()->addDays(3))->count(),
                'highValueCount' => $assignedStages->where('contract.contract_value', '>=', 1000000000)->count(),
            ];
            
        } elseif ($user->hasRole('staff_fin') || $user->hasRole('admin_fin')) {
            // Finance Dashboard Data
            $totalAssigned = ContractReviewStage::where('assigned_user_id', $user->id)
                ->where('stage_type', 'finance')
                ->count();
            
            $activeReviews = ContractReviewStage::where('assigned_user_id', $user->id)
                ->where('stage_type', 'finance')
                ->where('status', 'in_progress')
                ->count();
                
            $pendingReviews = ContractReviewStage::where('assigned_user_id', $user->id)
                ->where('stage_type', 'finance')
                ->where('status', 'pending')
                ->count();
                
            $assignedStages = ContractReviewStage::where('assigned_user_id', $user->id)
                ->where('stage_type', 'finance')
                ->whereIn('status', ['pending', 'in_progress'])
                ->with(['contract' => function($query) {
                    $query->select('id', 'title', 'contract_number', 'contract_type', 'contract_value', 'currency', 'drafting_deadline', 'user_id')
                          ->with(['user:id,name']);
                }])
                ->orderBy('created_at', 'desc')
                ->get();
                
            return [
                'totalAssignedCount' => $totalAssigned,
                'activeReviewCount' => $activeReviews,
                'pendingReviewCount' => $pendingReviews,
                'assignedStages' => $assignedStages,
                'urgentCount' => $assignedStages->where('contract.drafting_deadline', '<=', now()->addDays(3))->count(),
                'highValueCount' => $assignedStages->where('contract.contract_value', '>=', 1000000000)->count(),
            ];
            
        } elseif ($user->hasRole('staff_tax') || $user->hasRole('admin_tax')) {
            // Tax Dashboard Data
            $totalAssigned = ContractReviewStage::where('assigned_user_id', $user->id)
                ->where('stage_type', 'tax')
                ->count();
            
            $activeReviews = ContractReviewStage::where('assigned_user_id', $user->id)
                ->where('stage_type', 'tax')
                ->where('status', 'in_progress')
                ->count();
                
            $pendingReviews = ContractReviewStage::where('assigned_user_id', $user->id)
                ->where('stage_type', 'tax')
                ->where('status', 'pending')
                ->count();
                
            $assignedStages = ContractReviewStage::where('assigned_user_id', $user->id)
                ->where('stage_type', 'tax')
                ->whereIn('status', ['pending', 'in_progress'])
                ->with(['contract' => function($query) {
                    $query->select('id', 'title', 'contract_number', 'contract_type', 'contract_value', 'currency', 'drafting_deadline', 'user_id')
                          ->with(['user:id,name']);
                }])
                ->orderBy('created_at', 'desc')
                ->get();
                
            return [
                'totalAssignedCount' => $totalAssigned,
                'activeReviewCount' => $activeReviews,
                'pendingReviewCount' => $pendingReviews,
                'assignedStages' => $assignedStages,
                'urgentCount' => $assignedStages->where('contract.drafting_deadline', '<=', now()->addDays(3))->count(),
                'highValueCount' => $assignedStages->where('contract.contract_value', '>=', 1000000000)->count(),
            ];
        }
        
        return [];
    }

   public function store(Request $request)
    {
        // ========================================
        // STEP 1: LOG RAW REQUEST
        // ========================================
        Log::info('========== CONTRACT STORE START ==========');
        Log::info('Step 1: Raw Request Data', [
            'all_data' => $request->all(),
            'contract_type' => $request->input('contract_type'),
            'user_id' => Auth::id(),
            'user_email' => Auth::user()->email,
        ]);
        
        // ========================================
        // STEP 2: VALIDATION (TANPA department_code)
        // ========================================
        try {
            $validated = $request->validate([
                'title' => 'required|string|max:255',
                'description' => 'nullable|string|max:1000',
                'contract_type' => 'required|in:surat,kontrak',
                // ❌ REMOVED: 'department_code' => 'required|string|max:10',
                'counterparty_name' => 'required|string|max:255',
                'counterparty_email' => 'nullable|email|max:255',
                'counterparty_phone' => 'nullable|string|max:20',
                'drafting_deadline' => 'nullable|date|after_or_equal:today',
                'effective_date' => 'nullable|date|after_or_equal:today',
                'expiry_date' => 'nullable|date|after_or_equal:effective_date',
                'contract_value' => 'nullable|numeric|min:0',
                'currency' => 'nullable|string|size:3',
                'additional_notes' => 'nullable|string|max:500',
            ]);
            
            Log::info('Step 2: Validation Success', [
                'validated_data' => $validated,
            ]);
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('❌ VALIDATION FAILED', [
                'errors' => $e->errors(),
                'request_all' => $request->all(),
            ]);
            throw $e;
        }
        
        // ========================================
        // STEP 3: AUTO-RESOLVE DEPARTMENT CODE
        // ========================================
        try {
            $user = Auth::user();
            
            // Query tbl_user untuk ambil kode_department
            $hrmsUser = DB::table('tbl_user')
                ->where('email', $user->email)
                ->first(['kode_department', 'nama_user']);
            
            if (!$hrmsUser) {
                Log::error('❌ USER NOT FOUND IN HRMS', [
                    'user_email' => $user->email,
                    'user_id' => $user->id,
                ]);
                
                return back()
                    ->withInput()
                    ->withErrors(['email' => 'Your email not found in HRMS system. Please contact admin.'])
                    ->with('error', '🔴 Your account is not linked to HRMS. Please contact HR department.');
            }
            
            if (empty($hrmsUser->kode_department)) {
                Log::error('❌ DEPARTMENT CODE NOT SET IN HRMS', [
                    'user_email' => $user->email,
                    'hrms_nama' => $hrmsUser->nama_user,
                ]);
                
                return back()
                    ->withInput()
                    ->withErrors(['department' => 'Your department code is not set in HRMS. Please contact admin.'])
                    ->with('error', '🔴 Your department code is missing. Please contact HR department to set your department.');
            }
            
            // ✅ SET DEPARTMENT CODE
            $departmentCode = strtoupper($hrmsUser->kode_department);
            $validated['department_code'] = $departmentCode;
            
            Log::info('Step 3: Department Code Resolved', [
                'user_email' => $user->email,
                'hrms_nama' => $hrmsUser->nama_user,
                'kode_department' => $departmentCode,
            ]);
            
        } catch (\Exception $e) {
            Log::error('❌ FAILED TO RESOLVE DEPARTMENT', [
                'error' => $e->getMessage(),
                'user_email' => Auth::user()->email,
            ]);
            
            return back()
                ->withInput()
                ->with('error', 'Failed to resolve department code: ' . $e->getMessage());
        }
        
        // ========================================
        // STEP 4: PREPARE DATA
        // ========================================
        $validated['user_id'] = Auth::id();
        $validated['status'] = Contract::STATUS_DRAFT;
        $validated['currency'] = $validated['currency'] ?? 'IDR';
        
        // ✅ TAMBAHKAN INI - Wajib dynamic karena lewat ContractController
        // Hanya SuratController yang set 'static'
        $validated['workflow_type'] = 'dynamic';

        Log::info('Step 4: Final Prepared Data', [
            'prepared_data' => $validated,
            'department_code' => $validated['department_code'],
            'contract_type' => $validated['contract_type'],
        ]);
        
        // ========================================
        // STEP 5: CREATE CONTRACT
        // ========================================
        try {
            DB::beginTransaction();
            
            Log::info('Step 5: Creating contract...');
            
            $contract = Contract::create($validated);
            
            Log::info('Step 6: Contract created!', [
                'contract_id' => $contract->id,
                'title' => $contract->title,
                'department_code' => $contract->department_code,
                'contract_type' => $contract->contract_type,
            ]);
            
            // ✅ VERIFY dari database
            $fresh = Contract::find($contract->id);
            
            Log::info('Step 7: Database Verification', [
                'id' => $fresh->id,
                'department_code_from_db' => $fresh->department_code,
                'contract_type_from_db' => $fresh->contract_type,
                'is_null_dept' => is_null($fresh->department_code),
                'is_null_type' => is_null($fresh->contract_type),
            ]);
            
            // ❌ JIKA NULL, ROLLBACK DAN REPORT
            if (is_null($fresh->department_code)) {
                DB::rollBack();
                
                Log::error('❌❌❌ DEPARTMENT CODE IS NULL IN DATABASE!', [
                    'contract_id' => $fresh->id,
                    'validated_data' => $validated,
                    'contract_attributes' => $contract->getAttributes(),
                    'fresh_attributes' => $fresh->getAttributes(),
                ]);
                
                return back()
                    ->withInput()
                    ->with('error', '🔴 CRITICAL: Department code became NULL in database! Check logs.');
            }
            
            DB::commit();
            
            Log::info('========== CONTRACT STORE SUCCESS ==========');
            
            return redirect()
                ->route('contracts.show', $contract)
                ->with('success', "✅ Contract created successfully! Department: {$fresh->department_code}, Type: {$fresh->contract_type}");
                
        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('❌ CONTRACT CREATION FAILED', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            return back()
                ->withInput()
                ->with('error', 'Failed to create contract: ' . $e->getMessage());
        }
    }

    /**
     * 🔥 NEW: Preview official number untuk info user
     */
    public function previewOfficialNumber(Request $request, ContractNumberService $service)
        {
            $request->validate([
                'contract_type' => 'required|string|in:surat,kontrak',
            ]);
            
            try {
                $user = Auth::user();
                
                // ✅ RESOLVE DEPARTMENT dari HRMS
                $hrmsUser = DB::table('tbl_user')
                    ->where('email', $user->email)
                    ->first(['kode_department', 'nama_user']);
                
                if (!$hrmsUser || empty($hrmsUser->kode_department)) {
                    return response()->json([
                        'success' => false,
                        'error' => 'Your department code is not set in HRMS. Please contact HR department.',
                        'missing_data' => [
                            'user_email' => $user->email,
                            'hrms_found' => !is_null($hrmsUser),
                            'has_department' => !empty($hrmsUser->kode_department ?? null),
                        ]
                    ], 400);
                }
                
                $departmentCode = strtoupper($hrmsUser->kode_department);
                
                // Buat contract dummy untuk preview
                $dummyContract = new Contract([
                    'department_code' => $departmentCode,
                    'contract_type' => $request->contract_type,
                    'user_id' => Auth::id(),
                ]);
                
                $preview = $service->previewNumber($dummyContract);
                $components = $service->debugNumberComponents($dummyContract);
                
                // Get department name
                $department = DB::table('tbl_department')
                    ->where('kode_pendek', $departmentCode)
                    ->first(['nama_departemen']);
                
                return response()->json([
                    'success' => true,
                    'preview' => $preview,
                    'components' => $components,
                    'user_info' => [
                        'email' => $user->email,
                        'name' => $user->name,
                        'hrms_name' => $hrmsUser->nama_user,
                        'department_code' => $departmentCode,
                        'department_name' => $department->nama_departemen ?? 'Unknown Department',
                    ],
                    'format' => 'sequence/department-GNI/type/romanMonth/year',
                    'explanation' => [
                        'sequence' => '3-digit sequence number (auto increment per department per year)',
                        'department' => 'Your department code: ' . $departmentCode,
                        'GNI' => 'Company code',
                        'type' => 'S (Surat) or K (Kontrak)',
                        'romanMonth' => 'Roman numeral month (I-XII)',
                        'year' => '4-digit year',
                    ],
                    'note' => 'This is a preview. Actual number will be generated after final approval.'
                ]);
                
            } catch (\Exception $e) {
                Log::error('Failed to preview official number', [
                    'error' => $e->getMessage(),
                    'user_email' => Auth::user()->email,
                    'data' => $request->all(),
                ]);
                
                return response()->json([
                    'success' => false,
                    'error' => 'Failed to generate preview: ' . $e->getMessage(),
                ], 500);
            }
        }

public function returnToDraft(Contract $contract)
{
    if ($contract->status !== 'submitted') {
        return back()->with('error', 'Only submitted documents can be returned to draft.');
    }

    DB::transaction(function () use ($contract) {

        // Update status
        $contract->update([
            'status' => 'draft'
        ]);

        // Log siapa yang melakukan action
        ContractReviewLog::create([
            'contract_id' => $contract->id,
            'stage_id'    => null,
            'user_id'     => auth()->id(),
            'action'      => 'returned_to_draft_by_admin',
        ]);

        /*
        |--------------------------------------------------------------------------
        | SEND NOTIFICATION TO DOCUMENT OWNER
        |--------------------------------------------------------------------------
        */
        $owner = $contract->user; // pastikan relasi user() ada

        if ($owner) {
            $owner->notify(
                new DocumentReturnedToDraftNotification(
                    $contract,
                    auth()->user() // admin yg melakukan
                )
            );
        }
    });

    return back()->with('success', 'Document has been returned to draft.');
}

    /**
     * Show upload revision form
     */
    public function showUploadRevisionForm(Contract $contract)
    {
        $user = Auth::user();
        
        if (!$contract->canBeRevisedBy($user)) {
            abort(403, 'You cannot revise this contract.');
        }
        
        return view('contracts.upload-revision', compact('contract'));
    }

    /**
     * Submit revision
     */
    public function submitRevision(Request $request, Contract $contract)
    {
        $user = Auth::user();
        
        if (!$contract->canBeRevisedBy($user)) {
            abort(403, 'You cannot revise this contract.');
        }
        
        $validated = $request->validate([
            'revision_file' => 'required|file|mimes:pdf,doc,docx|max:20480',
            'revision_notes' => 'nullable|string|max:1000',
        ]);
        
        if ($request->hasFile('revision_file')) {
            $file = $request->file('revision_file');
            $path = $file->store('contracts/revisions');
            
            $contract->update([
                'status' => Contract::STATUS_SUBMITTED,
                'review_flow_status' => 'in_review',
                'document_uploaded_at' => now(),
                'document_uploaded_by' => $user->id,
                'revision_count' => ($contract->revision_count ?? 0) + 1,
                'last_revision_at' => now(),
                'revision_notes' => $request->revision_notes,
            ]);
            
            ContractRevision::create([
                'contract_id' => $contract->id,
                'user_id' => $user->id,
                'file_path' => $path,
                'file_name' => $file->getClientOriginalName(),
                'notes' => $request->revision_notes,
            ]);
            
            return redirect()->route('contracts.show', $contract)
                ->with('success', 'Revision submitted successfully!');
        }
        
        return back()->with('error', 'Failed to upload revision.');
    }


        /**
         * Show contract details
         */
        public function show(Contract $contract)
    {
        $user = Auth::user();

        if (!$this->canUserAccessContract($contract, $user)) {
            abort(403, 'You do not have permission to access this contract.');
        }

        // ========================================
        // 🔥 BARIS BARU: REDIRECT JIKA SURAT REQUEST
        // ========================================
        if ($contract->isSuratRequest()) {
            return redirect()->route('surat.show', $contract);
        }

        // ========================================
        // ✅ PERBAIKAN 1: LOAD CONTRACT RELATIONS
        // ========================================
        $contract->load([
            'reviewStages' => function($q) {
                $q->with(['assignedUser'])->orderBy('sequence');
            },
            'user',
            'legalAssigned',
            'financeAssigned',
            'accountingAssigned',
            'taxAssigned',
        ]);

        // ========================================
        // ✅ PERBAIKAN 2: LOAD REVIEW LOGS DENGAN RELASI LENGKAP
        // ========================================
        $reviewLogs = $contract->reviewLogs()
            ->with([
                // USER: gunakan id_user, nama_user, email, jabatan
                'user:id_user,nama_user,email,jabatan',
                
                // STAGE: basic stage info
                'stage:id,contract_id,stage_name,stage_type,sequence,assigned_user_id,status,is_user_stage',
                
                // ASSIGNED USER di stage: WAJIB untuk nama reviewer
                'stage.assignedUser:id_user,nama_user,email,jabatan'
            ])
            ->orderBy('created_at', 'desc')
            ->get();

        // ========================================
        // ✅ PERBAIKAN 3: DEBUG - CEK APAKAH DATA MASUK
        // ========================================
        Log::info('=== REVIEW LOGS DEBUG (FIXED) ===', [
            'contract_id' => $contract->id,
            'total_logs' => $reviewLogs->count(),
            'sample_log' => $reviewLogs->first() ? [
                'log_id' => $reviewLogs->first()->id,
                'user_id' => $reviewLogs->first()->user_id,
                'user_name' => $reviewLogs->first()->user->nama_user ?? 'NULL',
                'user_loaded' => !is_null($reviewLogs->first()->user),
                'stage_assigned_user' => $reviewLogs->first()->stage?->assignedUser?->nama_user ?? 'NULL',
            ] : 'No logs',
        ]);

        return view('contracts.show', compact('contract', 'reviewLogs'));
    }



    /**
     * Check if user can access the contract
     */
    private function canUserAccessContract(Contract $contract, $user): bool
    {
        // Admin selalu bisa akses
        if ($user->hasRole('admin')) {
            return true;
        }
        
        // User yang membuat kontrak
        if ($contract->user_id === $user->id) {
            return true;
        }
        
        // Untuk revision_needed
        if ($contract->status === Contract::STATUS_REVISION_NEEDED) {
            return $this->canAccessRevisionNeededContract($contract, $user);
        }
        
        $userRoles = $user->getRoleNames();
        
        // ✅ Legal
        if ($userRoles->contains('legal')) {
            $isInLegalStages = $contract->reviewStages()
                ->where('stage_type', 'legal')
                ->where('assigned_user_id', $user->id)
                ->exists();
                
            if ($contract->legal_assigned_id === $user->id || 
                $isInLegalStages || 
                $user->can('contract_view_all')) {
                return true;
            }
        }
        
        // ✅ Finance
        if ($userRoles->contains('staff_fin') || $userRoles->contains('admin_fin')) {
            $isInFinanceStages = $contract->reviewStages()
                ->where('stage_type', 'finance')
                ->where('assigned_user_id', $user->id)
                ->exists();
                
            if ($contract->finance_assigned_id === $user->id || 
                $isInFinanceStages || 
                ($userRoles->contains('admin_fin') && $this->isFinanceDepartmentContract($contract))) {
                return true;
            }
        }
        
        // ✅ Accounting
        if ($userRoles->contains('staff_acc') || $userRoles->contains('admin_acc')) {
            $isInAccountingStages = $contract->reviewStages()
                ->where('stage_type', 'accounting')
                ->where('assigned_user_id', $user->id)
                ->exists();
                
            if ($contract->accounting_assigned_id === $user->id || 
                $isInAccountingStages || 
                ($userRoles->contains('admin_acc') && $this->isAccountingDepartmentContract($contract))) {
                return true;
            }
        }
        
        // ✅ Tax
        if ($userRoles->contains('staff_tax') || $userRoles->contains('admin_tax')) {
            $isInTaxStages = $contract->reviewStages()
                ->where('stage_type', 'tax')
                ->where('assigned_user_id', $user->id)
                ->exists();
                
            if ($contract->tax_assigned_id === $user->id || 
                $isInTaxStages || 
                ($userRoles->contains('admin_tax') && $this->isTaxDepartmentContract($contract))) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * Special access logic for 'revision_needed' contracts
     */
    private function canAccessRevisionNeededContract(Contract $contract, $user): bool
    {
        Log::info('Checking access for revision_needed contract', [
            'contract_id' => $contract->id,
            'user_id' => $user->id,
            'contract_status' => $contract->status,
        ]);

        // 1. User pembuat kontrak
        if ($contract->user_id === $user->id) {
            return true;
        }
        
        // 2. Admin atau user dengan permission view_all
        if ($user->hasRole('admin') || $user->can('contract_view_all')) {
            return true;
        }
        
        // 3. User yang di-assign sebagai legal/finance/accounting/tax
        if ($contract->legal_assigned_id === $user->id || 
            $contract->finance_assigned_id === $user->id ||
            $contract->accounting_assigned_id === $user->id ||
            $contract->tax_assigned_id === $user->id) {
            return true;
        }
        
        // 4. User yang ada di review stages
        $userStages = $contract->reviewStages()
            ->where('assigned_user_id', $user->id)
            ->get();
        
        if ($userStages->count() > 0) {
            return true;
        }
        
        // 5. Department admin access DAN staff access
        $userRoles = $user->getRoleNames();
        
        if (($userRoles->contains('admin_fin') || $userRoles->contains('staff_fin')) && $this->isFinanceDepartmentContract($contract)) {
            return true;
        }
        
        if (($userRoles->contains('admin_acc') || $userRoles->contains('staff_acc')) && $this->isAccountingDepartmentContract($contract)) {
            return true;
        }
        
        if (($userRoles->contains('admin_tax') || $userRoles->contains('staff_tax')) && $this->isTaxDepartmentContract($contract)) {
            return true;
        }
        
        // 6. Jika tidak ada kriteria di atas, coba cari berdasarkan review logs
        $hasUserInLogs = $contract->reviewLogs()
            ->where('user_id', $user->id)
            ->exists();
        
        if ($hasUserInLogs) {
            return true;
        }
        
        Log::warning('Access denied for revision_needed contract', [
            'contract_id' => $contract->id,
            'user_id' => $user->id,
            'reason' => 'User not involved in contract',
        ]);
        
        return false;
    }

    /**
     * Check if contract belongs to finance department
     */
    private function isFinanceDepartmentContract(Contract $contract): bool
    {
        return $contract->hasDepartmentSelected('FIN') ||
               $contract->reviewStages()->where('stage_type', 'finance')->exists() ||
               !is_null($contract->finance_assigned_id);
    }

    private function isAccountingDepartmentContract(Contract $contract): bool
    {
        return $contract->hasDepartmentSelected('ACC') ||
               $contract->reviewStages()->where('stage_type', 'accounting')->exists() ||
               !is_null($contract->accounting_assigned_id);
    }

    private function isTaxDepartmentContract(Contract $contract): bool
    {
        return $contract->hasDepartmentSelected('TAX') ||
               $contract->reviewStages()->where('stage_type', 'tax')->exists() ||
               !is_null($contract->tax_assigned_id);
    }

    /**
     * Show full review logs for a contract
     */
    public function showReviewLogs(Contract $contract)
    {
        $user = Auth::user();
        
        if (!$this->canUserAccessContract($contract, $user)) {
            abort(403, 'You do not have permission to access this contract.');
        }

        $reviewLogs = $contract->reviewLogs()
            ->with([
                'user:id,name,email',
                'stage:id,stage_name,stage_type,is_user_stage,sequence'
            ])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('contracts.review-logs', compact('contract', 'reviewLogs'));
    }

    /**
     * Show chat/discussion page
     */
    public function chat(Contract $contract)
    {
        $user = Auth::user();

        if (!$this->canUserAccessContract($contract, $user)) {
            abort(403, 'You do not have permission to access this contract.');
        }

        // Get all review logs
        $reviewLogs = $contract->reviewLogs()
            ->with(['user:id,name,email', 'stage:id,stage_name,stage_type,is_user_stage,sequence'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('contracts.partials.chat', compact('contract', 'reviewLogs'));
    }


    public function create()
{
    if (!Auth::user()->can('contract_request_create')) {
        abort(403);
    }

    $user = Auth::user();
    $userDepartment = null;
    $departmentCode = null;
    $departmentWarning = null;

    try {
        $hrmsUser = DB::table('tbl_user')
            ->where('email', $user->email)
            ->first(['kode_department', 'nama_user']);

        if ($hrmsUser && !empty($hrmsUser->kode_department)) {
            $departmentCode = strtoupper($hrmsUser->kode_department);

            $userDepartment = DB::table('tbl_department')
                ->where('kode_pendek', $departmentCode)
                ->first(['nama_departemen', 'kode_pendek']);
        } else {
            $departmentWarning = 'Your department is not set in HRMS. Please contact HR department.';
        }
    } catch (\Exception $e) {
        $departmentWarning = 'Unable to load department information.';
    }

    // ✅ WAJIB ADA
    $contract = new Contract();

    return view('contracts.create', compact(
        'contract',
        'userDepartment',
        'departmentCode',
        'departmentWarning'
    ));
}


/**
 * 🔥 FIXED: Edit method - DEPARTMENT READ-ONLY
 */
public function edit(Contract $contract)
{
    if ($contract->user_id !== Auth::id() ||
        $contract->status !== Contract::STATUS_DRAFT ||
        !Auth::user()->can('contract_request_edit')) {
        abort(403);
    }

    // ✅ GET USER DEPARTMENT INFO (untuk display saja, tidak bisa edit)
    $userDepartment = null;
    $departmentCode = $contract->department_code; // Pakai yang sudah ada di contract
    
    try {
        if ($departmentCode) {
            // Get full department info dari tbl_department
            $userDepartment = DB::table('tbl_department')
                ->where('kode_pendek', $departmentCode)
                ->first(['nama_departemen', 'kode_pendek']);
        }
    } catch (\Exception $e) {
        Log::error('Failed to load department info in edit', [
            'error' => $e->getMessage(),
            'contract_id' => $contract->id,
        ]);
    }

    // ❌ REMOVED: $departments query - tidak butuh lagi
    
    return view('contracts.edit', compact(
        'contract',
        'userDepartment',
        'departmentCode'
    ));
}

    /**
     * Update
     */
    /**
     * 🔥 FIXED: Update method - DEPARTMENT TIDAK BISA DIUBAH
     */
    public function update(Request $request, Contract $contract)
    {
        if ($contract->user_id !== Auth::id()) abort(403);
        
        if ($contract->status !== Contract::STATUS_DRAFT) {
            return redirect()->route('contracts.show', $contract)
                ->with('error', 'Only draft contracts can be edited.');
        }

        // ❌ REMOVED: department_code dari validation - tidak bisa diubah
        $validated = $request->validate([
            'title' => 'required|max:255',
            'description' => 'nullable|max:1000',
            'contract_type' => 'required|in:surat,kontrak', // ✅ FIXED validation
            'counterparty_name' => 'required|max:255',
            'counterparty_email' => 'nullable|email|max:255',
            'counterparty_phone' => 'nullable|max:20',
            'effective_date' => 'nullable|date|after_or_equal:today',
            'expiry_date' => 'nullable|date|after_or_equal:effective_date',
            'contract_value' => 'nullable|numeric|min:0|max:999999999999.99',
            'currency' => 'nullable|string|size:3',
            'additional_notes' => 'nullable|max:500',
        ]);

        // ✅ ENSURE department_code TIDAK IKUT UPDATE (preserve original)
        unset($validated['department_code']);

        $contract->update($validated);
        
        Log::info('Contract updated (department preserved)', [
            'contract_id' => $contract->id,
            'department_code' => $contract->department_code,
            'updated_fields' => array_keys($validated),
        ]);

        return redirect()->route('contracts.show', $contract)
            ->with('success', 'Contract updated successfully!');
    }

    /**
     * Destroy
     */
    public function destroy(Contract $contract)
    {
        $user = Auth::user();
        
        if (!$user->hasRole('admin')) {
            if ($user->id !== $contract->user_id) {
                abort(403, 'Not the contract owner.');
            }
            if ($contract->status !== Contract::STATUS_DRAFT) {
                return redirect()->back()
                    ->with('error', 'Only draft contracts can be deleted.');
            }
        }
        
        try {
            $contract->delete();
            
            $message = $user->hasRole('admin') 
                ? "Contract '{$contract->title}' has been deleted by admin." 
                : 'Contract deleted successfully.';
                
            return redirect()->route('contracts.index')
                ->with('success', $message);
                
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to delete contract.');
        }
    }

    /**
     * Submit contract
     */
    public function submit(Contract $contract)
    {
        if ($contract->user_id !== Auth::id() || !Auth::user()->can('contract_request_submit')) {
            abort(403);
        }

        if (!$contract->canBeSubmitted()) {
            return back()->with('error', 'Document cannot be submitted.');
        }

        $contract->update([
            'status' => Contract::STATUS_SUBMITTED,
            'submitted_at' => now(),
            'review_flow_status' => Contract::REVIEW_FLOW_PENDING_ASSIGNMENT,
        ]);

        /*
        |--------------------------------------------------------------------------
        | SEND NOTIFICATION TO LEGAL
        |--------------------------------------------------------------------------
        */
        $legalUsers = TblUser::role('legal')->get();

        foreach ($legalUsers as $legal) {
            $legal->notify(
                new DocumentSubmittedNotification($contract, Auth::user())
            );
        }

        return redirect()->route('contracts.show', $contract)
            ->with('success', 'Document submitted for legal review.');
    }

    /**
     * Legal upload document
     */
    public function markDocumentUploaded(Request $request, Contract $contract)
    {
        if (!Auth::user()->can('contract_status_update')) abort(403);

        if (!$contract->canLegalUploadDocument()) {
            return back()->with('error', 'Cannot mark document as uploaded.');
        }

        $validated = $request->validate([
            'synology_folder_path' => 'required|string',
        ]);

        $contract->update([
            'status' => Contract::STATUS_DOCUMENT_UPLOADED,
            'synology_folder_path' => $validated['synology_folder_path'],
            'document_uploaded_at' => now(),
            'document_uploaded_by' => Auth::id(),
        ]);

        return redirect()->route('contracts.show', $contract)
            ->with('success', 'Document marked as uploaded.');
    }

    /**
     * Update Synology folder path (Admin only)
     * Untuk memperbaiki path yang salah
     */
    public function updateSynologyPath(Request $request, Contract $contract){
        // Hanya admin yang bisa mengedit path
        if (!auth()->user()->hasRole('admin')) {
            abort(403, 'Only admin can edit Synology folder path.');
        }

        $validated = $request->validate([
            'synology_folder_path' => 'required|string|max:500',
            'reason' => 'nullable|string|max:500',
        ]);

        DB::beginTransaction();
        
        try {
            // Simpan path lama untuk log
            $oldPath = $contract->synology_folder_path;
            
            // Update path
            $contract->update([
                'synology_folder_path' => $validated['synology_folder_path'],
            ]);

            // Catat perubahan di log review
            ContractReviewLog::create([
                'contract_id' => $contract->id,
                'user_id' => auth()->id(),
                'action' => 'synology_path_updated',
                'description' => 'Synology folder path updated by admin',
                'metadata' => [
                    'old_path' => $oldPath,
                    'new_path' => $validated['synology_folder_path'],
                    'reason' => $request->reason,
                    'updated_by' => auth()->user()->name,
                    'updated_by_email' => auth()->user()->email,
                ]
            ]);

            Log::info('Synology folder path updated by admin', [
                'contract_id' => $contract->id,
                'old_path' => $oldPath,
                'new_path' => $validated['synology_folder_path'],
                'updated_by' => auth()->user()->name,
                'reason' => $request->reason,
            ]);

            DB::commit();

            return redirect()
                ->route('contracts.show', $contract)
                ->with('success', 'Synology folder path has been updated successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Failed to update Synology folder path', [
                'contract_id' => $contract->id,
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
            ]);

            return redirect()
                ->back()
                ->with('error', 'Failed to update Synology folder path: ' . $e->getMessage());
        }
    }

    /**
     * Legal Start Review
     */
    public function legalStartReview(Contract $contract)
    {
        if (!Auth::user()->hasRole('legal') && !Auth::user()->hasRole('admin')) {
            abort(403);
        }

        // Update contract
        $contract->update([
            'status' => Contract::STATUS_UNDER_REVIEW,
            'legal_review_started_at' => now(),
        ]);

        // LOCK ORIGINAL SEQUENCE
        ContractReviewStage::where('contract_id', $contract->id)
            ->whereNull('original_sequence')
            ->update([
                'original_sequence' => DB::raw('sequence')
            ]);

        // START FIRST STAGE
        $firstStage = ContractReviewStage::where('contract_id', $contract->id)
            ->orderBy('sequence')
            ->first();

        if ($firstStage) {
            $firstStage->update([
                'status' => 'in_progress',
                'started_at' => now(),
            ]);
        }

        return redirect()->route('legal.contracts.show', $contract)
            ->with('success', 'Legal review started.');
    }
    

    /*
    |--------------------------------------------------------------------------
    | LEGAL INTERNAL COMMENT (Hanya Legal - Status Under Review)
    |--------------------------------------------------------------------------
    */
    public function storeLegalComment(Request $request, $id)
    {
        $request->validate([
            'notes' => 'required|string'
        ]);

        $contract = Contract::findOrFail($id);

        $user = auth()->user();

        // hanya legal dan admin
        if (!$user->hasAnyRole(['legal', 'admin'])) {
            abort(403, 'Unauthorized action.');
        }

        // hanya boleh saat under_review
        if ($contract->status !== 'under_review') {
            return back()->with('error', 'Comment hanya bisa saat status Under Review.');
        }

        LegalContractComment::create([
            'contract_id'   => $contract->id,
            'legal_user_id' => $user->id_user,
            'notes'         => $request->notes,
        ]);

        return back()->with('success', 'Comment berhasil ditambahkan.');
    }



    /**
     * Finance Start Review
     */
    public function financeStartReview(Contract $contract)
    {
        if (!Auth::user()->hasRole('staff_fin') && !Auth::user()->hasRole('admin')) abort(403);

        $contract->update([
            'status' => Contract::STATUS_FINANCE_REVIEWING,
            'finance_assigned_id' => Auth::id(),
            'finance_review_started_at' => now(),
        ]);

        return redirect()->route('finance.contracts.show', $contract)
            ->with('success', 'Finance review started.');
    }

    /**
     * Accounting Start Review
     */
    public function accountingStartReview(Contract $contract)
    {
        if (!Auth::user()->hasRole('staff_acc') && !Auth::user()->hasRole('admin')) abort(403);

        $contract->update([
            'status' => Contract::STATUS_ACCOUNTING_REVIEWING,
            'accounting_assigned_id' => Auth::id(),
            'accounting_review_started_at' => now(),
        ]);

        return redirect()->route('accounting.contracts.show', $contract)
            ->with('success', 'Accounting review started.');
    }

    /**
     * Tax Start Review
     */
    public function taxStartReview(Contract $contract)
    {
        if (!Auth::user()->hasRole('staff_tax') && !Auth::user()->hasRole('admin')) abort(403);

        $contract->update([
            'status' => Contract::STATUS_TAX_REVIEWING,
            'tax_assigned_id' => Auth::id(),
            'tax_review_started_at' => now(),
        ]);

        return redirect()->route('tax.contracts.show', $contract)
            ->with('success', 'Tax review started.');
    }

    /**
     * Export contracts to CSV
     */
    public function export(Request $request)
    {
        $user = Auth::user();
        
        // Filter based on role
        if ($user->hasRole('admin')) {
            $query = Contract::query();
        } else {
            $query = Contract::where('user_id', $user->id);
        }
        
        // Apply filters if present
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('contract_number', 'like', "%{$search}%")
                  ->orWhere('counterparty_name', 'like', "%{$search}%");
            });
        }
        
        $contracts = $query->with(['user'])->get();
        
        $fileName = 'contracts_' . date('Y-m-d_H-i-s') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
        ];
        
        $callback = function() use ($contracts) {
            $file = fopen('php://output', 'w');
            
            // Add BOM for UTF-8
            fwrite($file, "\xEF\xBB\xBF");
            
            // Headers
            fputcsv($file, [
                'Contract Number',
                'Title',
                'Type',
                'Status',
                'Counterparty',
                'Value',
                'Currency',
                'Created By',
                'Created Date',
                'Last Updated'
            ]);
            
            // Data
            foreach ($contracts as $contract) {
                fputcsv($file, [
                    $contract->contract_number ?? 'N/A',
                    $contract->title,
                    $contract->contract_type,
                    $contract->status,
                    $contract->counterparty_name,
                    $contract->contract_value ? number_format($contract->contract_value, 2) : '0.00',
                    $contract->currency ?? 'IDR',
                    $contract->user->name,
                    $contract->created_at->format('Y-m-d'),
                    $contract->updated_at->format('Y-m-d H:i:s')
                ]);
            }
            
            fclose($file);
        };
        
        return response()->stream($callback, 200, $headers);
    }
}
