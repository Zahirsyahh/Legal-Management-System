<?php

namespace App\Http\Controllers;

use App\Models\Contract;
use App\Models\ContractDepartment;
use App\Models\ContractReviewStage;
use App\Models\Department;
use App\Models\TblUser;
use App\Models\ContractReviewLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB; // ✅ TAMBAHKAN INI
use Illuminate\Support\Facades\Log; // ✅ TAMBAHKAN INI
use App\Notifications\StaffAssignedNotification;

class ContractDepartmentController extends Controller
{
    /**
     * LEGAL: Pilih department yang ikut review
     */
    public function store(Request $request, Contract $contract)
    {
        if (!Auth::user()->hasRole('legal')) {
            abort(403);
        }

        $request->validate([
            'department_ids' => 'required|array',
        ]);

        foreach ($request->department_ids as $departmentId) {
            ContractDepartment::firstOrCreate([
                'contract_id' => $contract->id,
                'department_id' => $departmentId,
                'status' => 'pending_assignment', // ✅ TAMBAHKAN STATUS
            ]);
        }

        return back()->with('success', 'Departments successfully added.');
    }

    /**
     * LEGAL: Tambah reviewer legal
     */
    public function addLegalReviewer(Request $request, Contract $contract)
    {
        if ($contract->status !== 'draft') {
            abort(403, 'Reviewer hanya bisa ditambahkan sebelum review dimulai.');
        }

        $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        // ✅ FIX: Gunakan fully qualified class name
        $user = \App\Models\TblUser::findOrFail($request->id_user);

        if (!$user->hasRole('legal')) {
            abort(403, 'Reviewer harus dari role LEGAL.');
        }

        $exists = $contract->reviewStages()
            ->where('assigned_user_id', $user->id) // ✅ FIX: gunakan assigned_user_id
            ->exists();

        if ($exists) {
            return back()->withErrors('Reviewer ini sudah ditambahkan.');
        }

        $nextOrder = $contract->reviewStages()->max('sequence') + 1; // ✅ FIX: gunakan sequence

        $contract->reviewStages()->create([
            'stage_name' => 'Legal Review',
            'assigned_user_id' => $user->id, // ✅ FIX: gunakan assigned_user_id
            'stage_type' => 'legal',
            'sequence' => $nextOrder, // ✅ FIX: gunakan sequence
            'status' => 'pending',
        ]);

        return back()->with('success', 'Reviewer legal berhasil ditambahkan.');
    }

    /**
     * DEPARTMENT ADMIN: Assign staff reviewer
     * ✅ FIX: Parameter harus ContractDepartment, bukan Contract
     */
    public function assignStaffReviewer(Request $request, ContractDepartment $contractDepartment)
    {
        $user = Auth::user();
        
        // Authorization: must be department admin
        $departmentCode = $user->getDepartmentCode();
        if (!$user->isDepartmentAdmin() && !$user->hasRole('admin')) {
            abort(403, 'Only department admin can assign reviewers.');
        }
        
        // ✅ FIX: Verify this contract belongs to user's department
        if ($contractDepartment->department->code !== $departmentCode) {
            abort(403, 'You can only assign staff to your own department contracts.');
        }
        
        $request->validate([
            'staff_user_id' => 'required|exists:users,id',
            'notes' => 'nullable|string|max:1000',
        ]);
        
        // Check if already assigned
        if ($contractDepartment->status !== 'pending_assignment') {
            return back()->with('error', 'This department review has already been assigned.');
        }
        
        // ✅ FIX: Verify staff user belongs to correct department
        $staffUser = User::find($request->staff_user_id);
        $staffRole = 'staff_' . strtolower($departmentCode);
        
        if (!$staffUser->hasRole($staffRole)) {
            return back()->with('error', 'Selected user is not a staff member of this department.');
        }
        
        try {
            DB::beginTransaction();
            
            // Update contract_department
            $contractDepartment->update([
                'status' => 'assigned',
                'assigned_admin_id' => $user->id,
                'assigned_at' => now(),
            ]);
            
            // Create review stage for staff
            $lastSequence = ContractReviewStage::where('contract_id', $contractDepartment->contract_id)
                ->max('sequence') ?? 0;
            
            $stage = ContractReviewStage::create([
                'contract_id' => $contractDepartment->contract_id,
                'department_id' => $contractDepartment->department_id,
                'stage_name' => $contractDepartment->department->name . ' Review',
                'stage_type' => strtolower($contractDepartment->department->code),
                'assigned_user_id' => $request->staff_user_id,
                'sequence' => $lastSequence + 1,
                'status' => 'pending', // ✅ FIX: status harus 'pending' untuk staff mulai review
                'notes' => $request->notes,
                'created_by' => $user->id,
            ]);
            
            // Notify the assigned staff
            if ($staffUser) {
                $staffUser->notify(new \App\Notifications\StaffAssignedNotification(
                    $contractDepartment->contract,
                    $contractDepartment->department,
                    $user,
                    $request->notes
                ));
            }
            
            // Log the assignment
            ContractReviewLog::create([
                'contract_id' => $contractDepartment->contract_id,
                'stage_id' => $stage->id,
                'user_id' => $user->id,
                'action' => 'staff_assigned',
                'description' => 'Staff assigned for department review: ' . $contractDepartment->department->name,
                'metadata' => [
                    'staff_user_id' => $request->staff_user_id,
                    'staff_name' => $staffUser->name,
                    'department_id' => $contractDepartment->department_id,
                    'department_name' => $contractDepartment->department->name,
                    'notes' => $request->notes,
                ]
            ]);
            
            DB::commit();
            
            return back()->with('success', 'Staff assigned successfully. Notification sent.');
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to assign staff: ' . $e->getMessage(), [
                'contract_department_id' => $contractDepartment->id,
                'user_id' => $user->id,
                'staff_user_id' => $request->staff_user_id,
            ]);
            
            return back()->with('error', 'Failed to assign staff: ' . $e->getMessage());
        }
    }

    /**
     * Show form to assign staff reviewer
     * ✅ FIX: Parameter harus ContractDepartment
     */
    public function showAssignForm(ContractDepartment $contractDepartment)
    {
        $user = Auth::user();
        $departmentCode = $user->getDepartmentCode();
        
        // Authorization
        if (!$user->isDepartmentAdmin() && !$user->hasRole('admin')) {
            abort(403);
        }
        
        // ✅ FIX: Verify this contract belongs to user's department
        if ($contractDepartment->department->code !== $departmentCode) {
            abort(403, 'You can only assign staff to your own department contracts.');
        }
        
        // Check if already assigned
        if ($contractDepartment->status !== 'pending_assignment') {
            return redirect()->route($departmentCode . '-admin.dashboard')
                ->with('error', 'Contract already assigned.');
        }
        
        // Get staff members from the same department
        $staffRole = 'staff_' . strtolower($departmentCode);
        $staffMembers = User::whereHas('roles', function($query) use ($staffRole) {
                $query->where('name', $staffRole);
            })
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
        
        return view('departments.assign-staff', compact(
            'contractDepartment',
            'staffMembers',
            'departmentCode'
        ));
    }
}