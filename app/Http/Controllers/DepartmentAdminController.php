<?php

namespace App\Http\Controllers;

use App\Models\Contract;
use App\Models\ContractDepartment;
use App\Models\Department;
use App\Models\TblUser;
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
     */
    public function dashboard(Request $request)
    {
        $department = $this->requireDepartment();

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
            ->limit(10)
            ->get();

        $activeReviews = ContractDepartment::whereHas('contract', function($query) {
                $query->whereNull('deleted_at');
            })
            ->with(['contract', 'assignedAdmin'])
            ->where('department_id', $department->id)
            ->whereIn('status', ['assigned', 'in_progress'])
            ->latest('updated_at')
            ->limit(10)
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
     */
    public function completedReviews()
    {
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
     *
     * ✅ PERUBAHAN: Admin department sekarang juga muncul di daftar pilihan,
     *    sehingga admin bisa menugaskan dirinya sendiri untuk ikut review.
     */
    public function showAssignForm(ContractDepartment $contractDepartment)
    {
        $department = $this->requireDepartment();

        if (!$contractDepartment->contract) {
            return redirect()->route($this->getDepartmentRouteName($department))
                ->with('error', 'Contract no longer exists or has been deleted.');
        }

        if ($contractDepartment->department_id !== $department->id) {
            abort(403, 'You can only assign staff to your own department contracts.');
        }

        if ($contractDepartment->status !== 'pending_assignment') {
            return redirect()->route($this->getDepartmentRouteName($department))
                ->with('error', 'This contract has already been assigned.');
        }

        $staffRole = $this->getStaffRoleName($department);
        $adminRole = $this->getAdminRoleName($department); // ✅ BARU

        // ✅ PERUBAHAN: Sertakan role admin department agar admin bisa pilih dirinya sendiri
        $staffMembers = TblUser::whereHas('roles', function($q) use ($staffRole, $adminRole) {
            $q->whereIn('name', [$staffRole, $adminRole]);
        })
        ->where('status_karyawan', 'AKTIF')
        ->orderBy('nama_user')
        ->get();

        return view('departments.assign-staff', compact(
            'contractDepartment',
            'staffMembers',
            'department'
        ));
    }

    /**
     * Process staff assignment
     *
     * ✅ PERUBAHAN: Validasi role sekarang menerima admin department,
     *    bukan hanya staff. Sehingga admin bisa menugaskan dirinya sendiri.
     */
    public function assignStaff(Request $request, ContractDepartment $contractDepartment)
    {
        $user = Auth::user();
        $department = $this->requireDepartment();

        if (!$contractDepartment->contract) {
            return back()->with('error', 'Contract no longer exists or has been deleted.');
        }

        if ($contractDepartment->department_id !== $department->id) {
            abort(403, 'Unauthorized action.');
        }

        $request->validate([
            'staff_user_id' => 'required|exists:tbl_user,id_user',
            'notes' => 'nullable|string|max:1000',
        ]);

        $staffUser = TblUser::where('id_user', $request->staff_user_id)->first();

        if (!$staffUser) {
            return back()->with('error', 'Staff user not found.');
        }

        $staffRole = $this->getStaffRoleName($department);
        $adminRole = $this->getAdminRoleName($department); // ✅ BARU

        // ✅ PERUBAHAN: Izinkan juga admin department, bukan hanya staff
        if (!$staffUser->hasRole($staffRole) && !$staffUser->hasRole($adminRole)) {
            return back()->with('error', 'Selected user is not a member of this department.');
        }

        DB::beginTransaction();

        try {
            // 1. Update contract_department
            $contractDepartment->update([
                'status' => 'assigned',
                'assigned_admin_id' => $user->id_user,
                'assigned_at' => now(),
            ]);

            // 2. Create review stage for the assigned user (staff or admin)
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
                'created_by' => $user->id_user,
            ]);

            // 3. Send notification (skip jika user menugaskan dirinya sendiri)
            if ((int) $staffUser->id_user !== (int) $user->id_user) {
                $this->sendStaffAssignmentNotification(
                    $staffUser,
                    $contractDepartment->contract,
                    $department,
                    $user,
                    $request->notes
                );
            }

            // 4. Log the assignment
            $isSelfAssign = (int) $staffUser->id_user === (int) $user->id_user;

            ContractReviewLog::create([
                'contract_id' => $contractDepartment->contract_id,
                'stage_id' => $stage->id,
                'user_id' => $user->id_user,
                'action' => 'staff_assigned',
                'description' => $isSelfAssign
                    ? "Admin assigned themselves for {$department->name} review"
                    : "Staff assigned for {$department->name} review",
                'metadata' => [
                    'staff_user_id' => $request->staff_user_id,
                    'staff_name' => $staffUser->nama_user,
                    'is_self_assign' => $isSelfAssign, // ✅ BARU: catat jika self-assign
                    'department_id' => $department->id,
                    'notes' => $request->notes,
                ]
            ]);

            DB::commit();

            $successMessage = $isSelfAssign
                ? 'You have successfully assigned yourself to review this contract.'
                : 'Staff assigned successfully. Notification sent to staff member.';

            return redirect()->route($this->getDepartmentRouteName($department))
                ->with('success', $successMessage);

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

    /**
     * ✅ BARU: Helper untuk mendapatkan nama role admin department
     */
    private function getAdminRoleName(Department $department): string
    {
        $roleMapping = [
            'FIN' => 'admin_fin',
            'ACC' => 'admin_acc',
            'TAX' => 'admin_tax'
        ];

        return $roleMapping[$department->code] ?? 'admin_' . strtolower($department->code);
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
     */
    private function sendStaffAssignmentNotification($staffUser, $contract, $department, $admin, $notes = null)
    {
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