<?php

namespace App\Http\Controllers;

use App\Models\Contract;
use App\Models\ContractDepartment;
use App\Models\Department;
use App\Models\TblUser; // ✅ FIXED: Changed from User
use App\Models\ContractReviewStage;
use App\Models\ContractReviewLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Notifications\StaffAssignedNotification;

class DepartmentAdminController extends Controller
{
    /**
     * Show department admin dashboard
     * 🔥 FIXED: Added whereHas to filter only valid contracts
     */
    public function dashboard(Request $request)
    {
        $department = $this->requireDepartment();

        // 🔥 FIX: Filter hanya contract_departments yang contractnya masih ada
        $pendingCount = ContractDepartment::whereHas('contract', function($query) {
                $query->whereNull('deleted_at');
            })
            ->where('department_id', $department->id)
            ->where('status', 'pending_assignment')
            ->count();

        $activeCount = ContractDepartment::whereHas('contract', function($query) {
                $query->whereNull('deleted_at');
            })
            ->where('department_id', $department->id)
            ->whereIn('status', ['assigned', 'in_progress'])
            ->count();

        $completedCount = ContractDepartment::whereHas('contract', function($query) {
                $query->whereNull('deleted_at');
            })
            ->where('department_id', $department->id)
            ->where('status', 'completed')
            ->count();

        $pendingAssignments = ContractDepartment::whereHas('contract', function($query) {
                $query->whereNull('deleted_at');
            })
            ->with(['contract.user', 'contract.legalAssigned'])
            ->where('department_id', $department->id)
            ->where('status', 'pending_assignment')
            ->latest()
            ->limit(10) // ✅ CHANGED from 5 to 10 for better UX
            ->get();

        $activeReviews = ContractDepartment::whereHas('contract', function($query) {
                $query->whereNull('deleted_at');
            })
            ->with(['contract', 'assignedAdmin'])
            ->where('department_id', $department->id)
            ->whereIn('status', ['assigned', 'in_progress'])
            ->latest('updated_at')
            ->limit(10) // ✅ CHANGED from 5 to 10 for better UX
            ->get();

        return view(
            $this->getDepartmentViewPath($department),
            compact(
                'department',
                'pendingCount',
                'activeCount',
                'completedCount',
                'pendingAssignments',
                'activeReviews'
            )
        );
    }

    /**
     * Show pending assignments list
     * 🔥 FIXED: Added whereHas
     */
    public function pendingAssignments()
    {
        $department = $this->requireDepartment();

        $pendingAssignments = ContractDepartment::whereHas('contract', function($query) {
                $query->whereNull('deleted_at');
            })
            ->with(['contract.user', 'contract.legalAssigned', 'assignedAdmin'])
            ->where('department_id', $department->id)
            ->where('status', 'pending_assignment')
            ->latest()
            ->paginate(10);

        // Hitung statistik tambahan
        $dueThisWeek = $pendingAssignments->filter(function($assignment) {
            if (!$assignment->due_date) return false;
            $dueDate = \Carbon\Carbon::parse($assignment->due_date);
            return $dueDate->isFuture() && $dueDate->diffInDays(now()) <= 7;
        })->count();

        $overdueAssignments = $pendingAssignments->filter(function($assignment) {
            if (!$assignment->due_date) return false;
            return \Carbon\Carbon::parse($assignment->due_date)->isPast();
        })->count();

        $availableStaff = $department->activeStaff()->count();

        return view('departments.pending-reviews', compact(
            'pendingAssignments', 
            'department',
            'dueThisWeek',
            'overdueAssignments',
            'availableStaff'
        ));
    }

    /**
     * Show active reviews list
     * 🔥 FIXED: Added whereHas
     */
    public function activeReviews()
    {
        $department = $this->requireDepartment();

        $reviews = ContractDepartment::whereHas('contract', function($query) {
                $query->whereNull('deleted_at')
                    ->whereNull('final_approved_at')
                    ->whereNotIn('status', ['approved', 'declined']);
            })
            ->with(['contract.user', 'assignedAdmin'])
            ->where('department_id', $department->id)
            ->latest('updated_at')
            ->paginate(20);

        return view('departments.active-reviews', compact('reviews', 'department'));
    }

    /**
     * Show completed reviews list
     * 🔥 FIXED: Added whereHas
     */
    public function completedReviews(){
        $department = $this->requireDepartment();

        $contracts = Contract::whereHas('departments', function ($q) use ($department) {
                $q->where('department_id', $department->id)
                ->where('status', 'completed');
            })
            ->with(['reviewStages.assignedUser'])
            ->whereNotNull('final_approved_at')
            ->latest('final_approved_at')
            ->paginate(20);

        $counterparties = Contract::whereHas('departments', function ($q) use ($department) {
                $q->where('department_id', $department->id)
                ->where('status', 'completed');
            })
            ->distinct()
            ->pluck('counterparty_name');

        return view('departments.completed', compact('contracts', 'counterparties'));
    }


    /**
     * Show assign staff form
     * 🔥 FIXED: Added contract existence check & TblUser
     */
    public function showAssignForm(ContractDepartment $contractDepartment)
    {
        $department = $this->requireDepartment();

        // 🔥 Check if contract still exists
        if (!$contractDepartment->contract) {
            return redirect()->route($this->getDepartmentRouteName($department))
                ->with('error', 'Contract no longer exists or has been deleted.');
        }

        // Authorization
        if ($contractDepartment->department_id !== $department->id) {
            abort(403, 'You can only assign staff to your own department contracts.');
        }

        if ($contractDepartment->status !== 'pending_assignment') {
            return redirect()->route($this->getDepartmentRouteName($department))
                ->with('error', 'This contract has already been assigned.');
        }

        // Get staff members
        $staffRole = $this->getStaffRoleName($department);
        
        // ✅ FIXED: Use TblUser instead of User
        $staffMembers = TblUser::whereHas('roles', function($q) use ($staffRole) {
            $q->where('name', $staffRole);
        })
        ->where('status_karyawan', 'AKTIF') // ✅ FIXED: Changed from is_active
        ->orderBy('nama_user') // ✅ FIXED: Changed from name
        ->get();

        return view('departments.assign-staff', compact(
            'contractDepartment',
            'staffMembers',
            'department'
        ));
    }

    /**
     * Process staff assignment
     * 🔥 FIXED: Added contract existence check & TblUser
     */
    public function assignStaff(Request $request, ContractDepartment $contractDepartment)
    {
        $user = Auth::user();
        $department = $this->requireDepartment();

        // 🔥 Check if contract still exists
        if (!$contractDepartment->contract) {
            return back()->with('error', 'Contract no longer exists or has been deleted.');
        }

        // Authorization
        if ($contractDepartment->department_id !== $department->id) {
            abort(403, 'Unauthorized action.');
        }

        $request->validate([
            'staff_user_id' => 'required|exists:tbl_user,id_user', // ✅ FIXED: table and field
            'notes' => 'nullable|string|max:1000',
        ]);

        // ✅ FIXED: Use TblUser and id_user
        $staffUser = TblUser::where('id_user', $request->staff_user_id)->first();
        
        if (!$staffUser) {
            return back()->with('error', 'Staff user not found.');
        }
        
        $staffRole = $this->getStaffRoleName($department);
        
        if (!$staffUser->hasRole($staffRole)) {
            return back()->with('error', 'Selected user is not a member of this department.');
        }

        DB::beginTransaction();

        try {
            // 1. Update contract_department
            $contractDepartment->update([
                'status' => 'assigned',
                'assigned_admin_id' => $user->id_user, // ✅ FIXED: Use id_user
                'assigned_at' => now(),
            ]);

            // 2. Create review stage for staff
            $sequence = ContractReviewStage::where('contract_id', $contractDepartment->contract_id)
                ->max('sequence') ?? 0;

            $stage = ContractReviewStage::create([
                'contract_id' => $contractDepartment->contract_id,
                'department_id' => $department->id,
                'stage_name' => $department->name . ' Review',
                'stage_type' => $this->getDepartmentSlug($department),
                'assigned_user_id' => $request->staff_user_id,
                'sequence' => $sequence + 1,
                'status' => 'pending',
                'notes' => $request->notes,
                'created_by' => $user->id_user, // ✅ FIXED: Use id_user
            ]);

            // 3. Send notification to staff
            $this->sendStaffAssignmentNotification(
                $staffUser,
                $contractDepartment->contract,
                $department,
                $user,
                $request->notes
            );

            // 4. Log the assignment
            ContractReviewLog::create([
                'contract_id' => $contractDepartment->contract_id,
                'stage_id' => $stage->id,
                'user_id' => $user->id_user, // ✅ FIXED: Use id_user
                'action' => 'staff_assigned',
                'description' => "Staff assigned for {$department->name} review",
                'metadata' => [
                    'staff_user_id' => $request->staff_user_id,
                    'staff_name' => $staffUser->nama_user, // ✅ FIXED: Use nama_user
                    'department_id' => $department->id,
                    'notes' => $request->notes,
                ]
            ]);

            DB::commit();

            return redirect()->route($this->getDepartmentRouteName($department))
                ->with('success', 'Staff assigned successfully. Notification sent to staff member.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('DepartmentAdminController::assignStaff - Error: ' . $e->getMessage(), [
                'user_id' => $user->id_user,
                'contract_department_id' => $contractDepartment->id,
                'staff_user_id' => $request->staff_user_id,
            ]);
            
            return back()->with('error', 'Failed to assign staff: ' . $e->getMessage());
        }
    }

    /* ===========================
       HELPER METHODS
       =========================== */

    private function requireDepartment()
    {
        $department = $this->getUserDepartment(Auth::user());
        if (!$department) {
            abort(403, 'You are not a department admin.');
        }
        return $department;
    }

    private function getDepartmentViewPath(Department $department): string
    {
        return 'departments.' . $this->getDepartmentSlug($department) . '.dashboard';
    }

    private function getDepartmentRouteName(Department $department): string
    {
        return $this->getDepartmentSlug($department) . '-admin.dashboard';
    }

    private function getDepartmentSlug(Department $department): string
    {
        return match ($department->code) {
            'FIN' => 'finance',
            'ACC' => 'accounting',
            'TAX' => 'tax',
            default => abort(403, 'Invalid department code.'),
        };
    }

    private function getStaffRoleName(Department $department): string
    {
        $roleMapping = [
            'FIN' => 'staff_fin',
            'ACC' => 'staff_acc',
            'TAX' => 'staff_tax'
        ];
        
        return $roleMapping[$department->code] ?? 'staff_' . strtolower($department->code);
    }

    private function getUserDepartment($user)
    {
        if ($user->hasRole('admin_fin')) {
            return Department::where('code', 'FIN')->first();
        }
        if ($user->hasRole('admin_acc')) {
            return Department::where('code', 'ACC')->first();
        }
        if ($user->hasRole('admin_tax')) {
            return Department::where('code', 'TAX')->first();
        }
        return null;
    }

    /**
     * Send notification to assigned staff
     * ✅ FIXED: Support both TblUser fields
     */
    private function sendStaffAssignmentNotification($staffUser, $contract, $department, $admin, $notes = null){
        try {
            $staffUser->notify(new StaffAssignedNotification(
                $contract,
                $department,
                $admin,
                $notes
            ));

            Log::info('Staff assignment notification sent', [
                'staff_id'      => $staffUser->id_user ?? $staffUser->id,
                'contract_id'   => $contract->id,
                'department_id' => $department->id,
                'assigned_by'   => $admin->id_user ?? $admin->id,
            ]);

        } catch (\Throwable $e) {
            Log::error('Failed to send staff assignment notification', [
                'error' => $e->getMessage(),
                'contract_id' => $contract->id ?? null,
                'staff_id' => $staffUser->id_user ?? null,
            ]);
        }
    }
}