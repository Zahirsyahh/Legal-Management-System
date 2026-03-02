<?php

namespace App\Http\Controllers;

use App\Models\Contract;
use App\Models\TblUser;
use App\Models\ContractReviewStage;
use App\Models\ContractDepartment;
use App\Models\Department;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Dashboard utama
     * - USER, LEGAL, ADMIN → dashboard utama
     * - STAFF → redirect ke dashboard department
     * - ADMIN DEPARTMENT → redirect ke dashboard admin department
     */
    public function index()
{
    $user = Auth::user();

    Log::info('Dashboard access', [
        'user_id' => $user->id_user,
        'roles'   => $user->getRoleNames()
    ]);

    /**
     * =====================================
     * 1️⃣ ROLE → REDIRECT MAP
     * =====================================
     */
    $redirectMap = [
        'staff_fin' => 'finance-staff.dashboard',
        'staff_acc' => 'accounting-staff.dashboard',
        'staff_tax' => 'tax-staff.dashboard',

        'admin_fin' => 'finance-admin.dashboard',
        'admin_acc' => 'accounting-admin.dashboard',
        'admin_tax' => 'tax-admin.dashboard',
    ];

    foreach ($redirectMap as $role => $route) {
        if ($user->hasRole($role)) {
            Log::info("Redirect {$role} to {$route}");
            return redirect()->route($route);
        }
    }

    /**
     * =====================================
     * 2️⃣ DASHBOARD UTAMA
     * =====================================
     */
    if ($user->hasRole('user')) {
        return view('dashboard.dark', $this->getUserData($user));
    }

    if ($user->hasRole('legal')) {
        return view('dashboard.dark', $this->getLegalData($user));
    }

    if ($user->hasRole('admin')) {
        return view('dashboard.dark', $this->getAdminData());
    }

    /**
     * =====================================
     * 3️⃣ ROLE TIDAK VALID
     * =====================================
     */
    Log::warning('Unauthorized dashboard access', [
        'user_id' => $user->id_user,
        'roles'   => $user->getRoleNames()
    ]);

    abort(403, 'Unauthorized dashboard access. No valid role found.');
}

    
    /**
     * ============================================
     * DASHBOARD STAFF DEPARTMENT (TERPISAH)
     * ============================================
     */
    
    /**
     * Dashboard untuk Accounting Staff
     */
    public function accountingStaff()
    {
        $user = Auth::user();
        
        if (!$user->hasRole('staff_acc')) {
            Log::warning('Unauthorized access to accounting-staff dashboard', [
                'user_id' => $user->id_user,
                'roles' => $user->getRoleNames()
            ]);
            abort(403, 'Unauthorized. This dashboard is for Accounting Staff only.');
        }
        
        $data = $this->getStaffDashboardData($user, 'ACC');
        return view('departments.accounting.dashboard-staff', $data);
    }
    
    /**
     * Dashboard untuk Finance Staff
     */
    public function financeStaff()
    {
        $user = Auth::user();
        
        if (!$user->hasRole('staff_fin')) {
            Log::warning('Unauthorized access to finance-staff dashboard', [
                'user_id' => $user->id_user,
                'roles' => $user->getRoleNames()
            ]);
            abort(403, 'Unauthorized. This dashboard is for Finance Staff only.');
        }
        
        $data = $this->getStaffDashboardData($user, 'FIN');
        return view('departments.finance.dashboard-staff', $data);
    }
    
    /**
     * Dashboard untuk Tax Staff
     */
    public function taxStaff()
    {
        $user = Auth::user();
        
        if (!$user->hasRole('staff_tax')) {
            Log::warning('Unauthorized access to tax-staff dashboard', [
                'user_id' => $user->id_user,
                'roles' => $user->getRoleNames()
            ]);
            abort(403, 'Unauthorized. This dashboard is for Tax Staff only.');
        }
        
        $data = $this->getStaffDashboardData($user, 'TAX');
        return view('departments.tax.dashboard-staff', $data);
    }
    
    /**
     * Data untuk dashboard staff (SEMUA DEPARTMENT)
     * 🔥 FIXED: Added whereHas to ensure contract exists
     */
    private function getStaffDashboardData(TblUser $user, string $departmentCode): array
    {
        // 🔥 FIX: Gunakan id_user bukan id
        Log::info("Getting dashboard data for user {$user->id_user} in department {$departmentCode}");
        
        // Ambil department ID
        $department = Department::where('code', $departmentCode)->first();
        
        if (!$department) {
            Log::error("Department not found for code: {$departmentCode}");
            abort(404, "Department {$departmentCode} not found");
        }
        
        Log::info("Department found: {$department->id} - {$department->name}");
        
        // 🔥 FIX: Filter hanya stages yang contractnya masih ada dan tidak soft-deleted
        try {
            $assignedStages = ContractReviewStage::query()
                ->whereHas('contract', function($query) {
                    // Pastikan contract exists dan tidak soft deleted
                    $query->whereNull('deleted_at');
                })
                ->with([
                    'contract' => function($query) {
                        $query->with(['user', 'legalAssigned', 'financeAssigned', 'accountingAssigned', 'taxAssigned']);
                    },
                    'department',
                    'assignedUser'
                ])
                ->where('assigned_user_id', $user->id_user)
                ->where('department_id', $department->id)
                ->whereIn('status', [
                    'pending',
                    'assigned',
                    'in_progress',
                    'completed',
                    'revision_requested',
                    'rejected',
                ])
                ->orderBy('created_at', 'desc')
                ->get();

            Log::info("Found {$assignedStages->count()} assigned stages for user {$user->id_user}");
        } catch (\Exception $e) {
            Log::error("Error fetching assigned stages: " . $e->getMessage());
            $assignedStages = collect([]);
        }
        
        // =======================
        // STATISTICS
        // =======================
        $totalAssignedCount = $assignedStages->count();
        $activeReviewCount  = $assignedStages->where('status', 'in_progress')->count();
        $pendingReviewCount = $assignedStages->whereIn('status', ['pending', 'assigned'])->count();
        $completedReviewCount = $assignedStages->where('status', 'completed')->count();

        // =======================
        // URGENT / OVERDUE
        // =======================
        $urgentCount = 0;
        $overdueCount = 0;
        $highValueCount = 0;

        foreach ($assignedStages as $stage) {
            // 🔥 FIX: Always check if contract exists before accessing properties
            if (!$stage->contract) {
                continue;
            }

            if ($stage->contract->drafting_deadline) {
                try {
                    $deadline = \Carbon\Carbon::parse($stage->contract->drafting_deadline);

                    if ($deadline->isPast()) {
                        $overdueCount++;
                    } elseif (now()->diffInDays($deadline, false) <= 3) {
                        $urgentCount++;
                    }
                } catch (\Exception $e) {
                    Log::warning("Invalid deadline format for contract {$stage->contract->id}");
                }
            }

            if ($stage->contract->contract_value > 100000) {
                $highValueCount++;
            }
        }

        // =======================
        // RECENT
        // =======================
        $recentAssignedCount = $assignedStages
            ->where('created_at', '>=', now()->subDays(7))
            ->count();

        // =======================
        // AVG COMPLETION TIME
        // =======================
        $completedStages = $assignedStages->where('status', 'completed');
        
        $avgCompletionTime = 0;
        if ($completedStages->isNotEmpty()) {
            $totalHours = 0;
            $count = 0;
            
            foreach ($completedStages as $stage) {
                if ($stage->assigned_at && $stage->completed_at) {
                    try {
                        $hours = $stage->assigned_at->diffInHours($stage->completed_at);
                        $totalHours += $hours;
                        $count++;
                    } catch (\Exception $e) {
                        Log::warning("Invalid date for stage {$stage->id}");
                    }
                }
            }
            
            if ($count > 0) {
                $avgCompletionTime = round($totalHours / $count, 1);
            }
        }

        return [
            'assignedStages' => $assignedStages,

            'totalAssignedCount' => $totalAssignedCount,
            'activeReviewCount' => $activeReviewCount,
            'pendingReviewCount' => $pendingReviewCount,
            'completedReviewCount' => $completedReviewCount,

            'urgentCount' => $urgentCount,
            'overdueCount' => $overdueCount,
            'highValueCount' => $highValueCount,

            'recentAssignedCount' => $recentAssignedCount,

            // UI only
            'department' => $department,
            'departmentName' => $department->name,
            'departmentCode' => $department->code,
            'departmentColor' => match ($departmentCode) {
                'ACC' => 'blue',
                'FIN' => 'green',
                'TAX' => 'purple',
                default => 'gray',
            },

            'avgCompletionTime' => $avgCompletionTime,
            'unreadNotifications' => $user->unreadNotifications()->count(),
        ];
    }
    
    /**
     * ============================================
     * DASHBOARD UNTUK USER, LEGAL, ADMIN
     * ============================================
     */
    
    /**
     * Get data for User dashboard
     */
    private function getUserData($user)
    {
        try {
            $draftContracts = Contract::where('user_id', $user->id_user)
                ->where('status', 'draft')
                ->count();
                
            $submittedContracts = Contract::where('user_id', $user->id_user)
                ->where('status', 'submitted')
                ->count();
                
            $reviewingContracts = Contract::where('user_id', $user->id_user)
                ->whereIn('status', ['under_review', 'final_approved', 'number_issued'])
                ->count();
                
            $approvedContracts = Contract::where('user_id', $user->id_user)
                ->whereIn('status', ['final_approved', 'completed'])
                ->count();
                
            $recentContracts = Contract::where('user_id', $user->id_user)
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get();
            
            return compact(
                'draftContracts',
                'submittedContracts',
                'reviewingContracts',
                'approvedContracts',
                'recentContracts'
            );
            
        } catch (\Exception $e) {
            Log::error('Error loading user dashboard data', [
                'user_id' => $user->id_user,
                'error' => $e->getMessage()
            ]);
            
            return [
                'draftContracts' => 0,
                'submittedContracts' => 0,
                'reviewingContracts' => 0,
                'approvedContracts' => 0,
                'recentContracts' => collect([])
            ];
        }
    }
    
    /**
 * Get data for Legal dashboard with two tables
 */
private function getLegalData($user)
{
    try {
        // =====================================
        // STATISTICS CARDS DATA
        // =====================================
        
        // Total assigned to this legal user
        $myAssignedContracts = Contract::whereHas('reviewStages', function($q) use ($user) {
            $q->where('assigned_user_id', $user->id_user)
              ->whereIn('status', ['assigned', 'in_progress']);
        })->get();
        
        // Pending legal reviews (submitted)
        $pendingLegalContracts = Contract::where('status', 'submitted')
            ->where(function($q) {
                $q->where('contract_type', 'surat')
                  ->orWhereHas('reviewStages', function($sq) {
                      $sq->where('status', 'assigned');
                  });
            })
            ->count();
        
        // Completed today
        $completedToday = Contract::whereDate('updated_at', today())
            ->whereIn('status', ['approved', 'final_approved', 'number_issued', 'released'])
            ->count();
        
        // TABLE 1: SUBMITTED DOCUMENTS
        $submittedDocuments = Contract::where('status', 'submitted')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();
        
        // =====================================
        // TABLE 2: ONGOING DOCUMENTS (Sedang Diproses)
        // =====================================
        // ✅ YANG BENAR
        $ongoingDocuments = Contract::whereIn('status', ['under_review', 'revision_needed'])
            ->whereHas('reviewStages', function($sq) use ($user) {
                $sq->where('assigned_user_id', $user->id_user)
           ->whereIn('status', ['in_progress', 'assigned', 'revision_requested']);
        })
        ->orderBy('updated_at', 'desc')
        ->limit(5)
        ->get();
        
        // Log for debugging
        Log::info('Legal dashboard data loaded', [
            'user_id' => $user->id_user,
            'submitted_count' => $submittedDocuments->count(),
            'ongoing_count' => $ongoingDocuments->count(),
            'assigned_count' => $myAssignedContracts->count(),
            'pending_count' => $pendingLegalContracts,
            'completed_today' => $completedToday
        ]);
        
        return compact(
            'myAssignedContracts',
            'pendingLegalContracts',
            'completedToday',
            'submittedDocuments',
            'ongoingDocuments'
        );
        
    } catch (\Exception $e) {
        Log::error('Error loading legal dashboard data', [
            'user_id' => $user->id_user,
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
        
        // Return empty collections if error occurs
        return [
            'myAssignedContracts' => collect([]),
            'pendingLegalContracts' => 0,
            'completedToday' => 0,
            'submittedDocuments' => collect([]),
            'ongoingDocuments' => collect([])
        ];
    }
}
    
    /**
     * Get data for Admin dashboard
     */
    private function getAdminData()
    {
        try {
            $totalUsers = \App\Models\TblUser::count();
            $totalContracts = Contract::count();
            $pendingLegal = Contract::where('status', 'submitted')->count();
            
            return compact(
                'totalUsers',
                'totalContracts',
                'pendingLegal'
            );
            
        } catch (\Exception $e) {
            Log::error('Error loading admin dashboard data', [
                'error' => $e->getMessage()
            ]);
            
            return [
                'totalUsers' => 0,
                'totalContracts' => 0,
                'pendingLegal' => 0
            ];
        }
    }
    
    /**
     * ============================================
     * METHOD UNTUK ADMIN DEPARTMENT
     * ============================================
     */
    
    /**
     * Dashboard untuk Finance Admin
     * 🔥 FIXED: Added whereHas
     */
    public function financeAdmin()
    {
        $user = Auth::user();
        
        if (!$user->hasRole('admin_fin')) {
            Log::warning('Unauthorized access to finance-admin dashboard', [
                'user_id' => $user->id_user,
                'roles' => $user->getRoleNames()
            ]);
            abort(403, 'Unauthorized. This dashboard is for Finance Admin only.');
        }
        
        $department = Department::where('code', 'FIN')->first();
        
        if (!$department) {
            abort(404, 'Finance department not found');
        }
        
        $data = [
            'totalAssigned' => ContractReviewStage::whereHas('contract')
                ->where('department_id', $department->id)->count(),
            'pendingAssignment' => ContractReviewStage::whereHas('contract')
                ->where('department_id', $department->id)
                ->where('status', 'pending')->count(),
            'inProgress' => ContractReviewStage::whereHas('contract')
                ->where('department_id', $department->id)
                ->where('status', 'in_progress')->count(),
            'completedToday' => ContractReviewStage::whereHas('contract')
                ->where('department_id', $department->id)
                ->where('status', 'completed')
                ->whereDate('completed_at', today())->count(),
        ];
        
        return view('departments.finance.dashboard', $data);
    }
    
    /**
     * Dashboard untuk Accounting Admin
     * 🔥 FIXED: Added whereHas
     */
    public function accountingAdmin()
    {
        $user = Auth::user();
        
        if (!$user->hasRole('admin_acc')) {
            Log::warning('Unauthorized access to accounting-admin dashboard', [
                'user_id' => $user->id_user,
                'roles' => $user->getRoleNames()
            ]);
            abort(403, 'Unauthorized. This dashboard is for Accounting Admin only.');
        }
        
        $department = Department::where('code', 'ACC')->first();
        
        if (!$department) {
            abort(404, 'Accounting department not found');
        }
        
        $data = [
            'totalAssigned' => ContractReviewStage::whereHas('contract')
                ->where('department_id', $department->id)->count(),
            'pendingAssignment' => ContractReviewStage::whereHas('contract')
                ->where('department_id', $department->id)
                ->where('status', 'pending')->count(),
            'inProgress' => ContractReviewStage::whereHas('contract')
                ->where('department_id', $department->id)
                ->where('status', 'in_progress')->count(),
            'completedToday' => ContractReviewStage::whereHas('contract')
                ->where('department_id', $department->id)
                ->where('status', 'completed')
                ->whereDate('completed_at', today())->count(),
        ];
        
        return view('departments.accounting.dashboard', $data);
    }
    
    /**
     * Dashboard untuk Tax Admin
     * 🔥 FIXED: Added whereHas
     */
    public function taxAdmin()
    {
        $user = Auth::user();
        
        if (!$user->hasRole('admin_tax')) {
            Log::warning('Unauthorized access to tax-admin dashboard', [
                'user_id' => $user->id_user,
                'roles' => $user->getRoleNames()
            ]);
            abort(403, 'Unauthorized. This dashboard is for Tax Admin only.');
        }
        
        $department = Department::where('code', 'TAX')->first();
        
        if (!$department) {
            abort(404, 'Tax department not found');
        }
        
        $data = [
            'totalAssigned' => ContractReviewStage::whereHas('contract')
                ->where('department_id', $department->id)->count(),
            'pendingAssignment' => ContractReviewStage::whereHas('contract')
                ->where('department_id', $department->id)
                ->where('status', 'pending')->count(),
            'inProgress' => ContractReviewStage::whereHas('contract')
                ->where('department_id', $department->id)
                ->where('status', 'in_progress')->count(),
            'completedToday' => ContractReviewStage::whereHas('contract')
                ->where('department_id', $department->id)
                ->where('status', 'completed')
                ->whereDate('completed_at', today())->count(),
        ];
        
        return view('departments.tax.dashboard', $data);
    }
    
    /**
     * ============================================
     * HELPER METHODS
     * ============================================
     */
    
    /**
     * Helper: Get status color for CSS classes
     */
    private function getStatusColor($status)
    {
        $colors = [
            'draft' => 'bg-gray-500/20 text-gray-300',
            'submitted' => 'bg-yellow-500/20 text-yellow-300',
            'under_review' => 'bg-blue-500/20 text-blue-300',
            'reviewing' => 'bg-blue-500/20 text-blue-300',
            'legal_reviewing' => 'bg-blue-500/20 text-blue-300',
            'legal_approved' => 'bg-purple-500/20 text-purple-300',
            'finance_reviewing' => 'bg-indigo-500/20 text-indigo-300',
            'finance_approved' => 'bg-indigo-500/30 text-indigo-400',
            'accounting_reviewing' => 'bg-teal-500/20 text-teal-300',
            'accounting_approved' => 'bg-teal-500/30 text-teal-400',
            'tax_reviewing' => 'bg-pink-500/20 text-pink-300',
            'tax_approved' => 'bg-pink-500/30 text-pink-400',
            'final_approved' => 'bg-green-500/20 text-green-300',
            'approved' => 'bg-green-500/20 text-green-300',
            'rejected' => 'bg-red-500/20 text-red-300',
            'revision_needed' => 'bg-orange-500/20 text-orange-300',
            'completed' => 'bg-emerald-500/20 text-emerald-300',
        ];
        
        return $colors[$status] ?? 'bg-gray-500/20 text-gray-300';
    }
    
    /**
     * Helper: Get status label
     */
    private function getStatusLabel($status)
    {
        $labels = Contract::getStatuses();
        return $labels[$status] ?? ucfirst(str_replace('_', ' ', $status));
    }
}