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
     *
     * Mendukung server-side search, filter status, dan sort melalui query params:
     *   ?search=Mobil Racing   → cari di title & contract_number
     *   ?status=overdue        → filter: all | pending | due_soon | overdue
     *   ?sort=title            → urut: due_date | title | contract_number
     *
     * Stats cards (Total, Due This Week, Overdue) selalu dihitung dari
     * $allPendingAssignments (semua data tanpa filter search/status)
     * agar angkanya tidak berubah saat user mengetik di search box.
     */
    public function pendingAssignments(Request $request)
    {
        $department = $this->requireDepartment();

        $search = trim($request->input('search', ''));
        $status = $request->input('status', 'all');
        $sort   = $request->input('sort', 'due_date');

        // ── Base scope (tanpa filter search/status) — untuk stats global ──
        $baseScope = ContractDepartment::whereHas('contract', function ($q) {
                $q->whereNull('deleted_at');
            })
            ->with([
                'contract.user',
                'contract.legalAssigned',
                'contract.reviewStages.assignedUser',
                'assignedAdmin',
            ])
            ->where('department_id', $department->id)
            ->where('status', 'pending_assignment');

        // ── Koleksi penuh untuk stats (tidak terpaginate, tidak terfilter) ──
        $allPendingAssignments = $baseScope->get();

        $now = \Carbon\Carbon::now();

        $dueThisWeek = $allPendingAssignments->filter(function ($a) use ($now) {
            $d = $a->contract?->drafting_deadline ?? ($a->due_date ?? null);
            if (!$d) return false;
            $p = \Carbon\Carbon::parse($d);
            return $p->isFuture() && $p->diffInDays($now) <= 7;
        })->count();

        $overdueAssignments = $allPendingAssignments->filter(function ($a) use ($now) {
            $d = $a->contract?->drafting_deadline ?? ($a->due_date ?? null);
            return $d && \Carbon\Carbon::parse($d)->isPast();
        })->count();

        // ── Query untuk tabel (dengan filter search/status/sort + paginate) ──
        $query = ContractDepartment::whereHas('contract', function ($q) use ($search) {
                $q->whereNull('deleted_at');

                // Filter search: cari di title dan contract_number contract
                if ($search !== '') {
                    $q->where(function ($sq) use ($search) {
                        $sq->where('title', 'like', "%{$search}%")
                           ->orWhere('contract_number', 'like', "%{$search}%");
                    });
                }
            })
            ->with([
                'contract.user',
                'contract.legalAssigned',
                'contract.reviewStages.assignedUser',
                'assignedAdmin',
            ])
            ->where('department_id', $department->id)
            ->where('status', 'pending_assignment');

        // Filter status (overdue / due_soon / pending) — berbasis tanggal
        // Dilakukan di PHP setelah query karena kolom deadline ada di tabel contracts,
        // dan join/subquery bisa kompleks. Untuk dataset besar bisa dioptimasi ke whereHas.
        if (in_array($status, ['overdue', 'due_soon', 'pending'])) {
            $allFiltered = $query->get();

            $allFiltered = $allFiltered->filter(function ($a) use ($status, $now) {
                $d = $a->contract?->drafting_deadline ?? ($a->due_date ?? null);
                $dueDate = $d ? \Carbon\Carbon::parse($d) : null;
                $isOverdue = $dueDate && $dueDate->isPast();
                $isDueSoon = $dueDate && !$isOverdue && $dueDate->diffInDays($now) <= 7;

                return match($status) {
                    'overdue'  => $isOverdue,
                    'due_soon' => $isDueSoon,
                    'pending'  => !$isOverdue && !$isDueSoon,
                    default    => true,
                };
            });

            // Sort koleksi
            $allFiltered = $this->sortCollection($allFiltered, $sort);

            // Manual paginate dari koleksi
            $pendingAssignments = $this->paginateCollection($allFiltered, 10, $request);

        } else {
            // Status 'all' — sort & paginate langsung via query builder
            $query = $this->applySortToQuery($query, $sort);
            $pendingAssignments = $query->paginate(10)->withQueryString();
        }

        $availableStaff = $department->activeStaff()->count();

        return view('departments.pending-reviews', compact(
            'pendingAssignments',       // paginated — untuk tabel
            'allPendingAssignments',    // full collection — untuk stats cards
            'department',
            'dueThisWeek',
            'overdueAssignments',
            'availableStaff'
        ));
    }

    /**
     * Terapkan urutan pada query builder
     */
    private function applySortToQuery($query, string $sort)
    {
        return match($sort) {
            'title'           => $query->join('contracts', 'contract_departments.contract_id', '=', 'contracts.id')
                                       ->orderBy('contracts.title')
                                       ->select('contract_departments.*'),
            'contract_number' => $query->join('contracts', 'contract_departments.contract_id', '=', 'contracts.id')
                                       ->orderBy('contracts.contract_number')
                                       ->select('contract_departments.*'),
            default           => $query->latest('contract_departments.created_at'), // due_date fallback
        };
    }

    /**
     * Terapkan urutan pada Collection
     */
    private function sortCollection($collection, string $sort)
    {
        return match($sort) {
            'title'           => $collection->sortBy(fn($a) => $a->contract?->title ?? ''),
            'contract_number' => $collection->sortBy(fn($a) => $a->contract?->contract_number ?? ''),
            default           => $collection->sortBy(fn($a) => $a->contract?->drafting_deadline
                                                                   ?? ($a->due_date ?? '9999-12-31')),
        };
    }

    /**
     * Manual paginate untuk Illuminate\Support\Collection
     */
    private function paginateCollection($collection, int $perPage, Request $request)
    {
        $page  = $request->input('page', 1);
        $total = $collection->count();
        $items = $collection->values()->forPage($page, $perPage);

        return new \Illuminate\Pagination\LengthAwarePaginator(
            $items,
            $total,
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );
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
        $adminRole = $this->getAdminRoleName($department);

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

    public function assignStaff(Request $request, ContractDepartment $contractDepartment)
    {
        $user       = Auth::user();
        $department = $this->requireDepartment();

        if (!$contractDepartment->contract) {
            return back()->with('error', 'Contract no longer exists or has been deleted.');
        }

        if ($contractDepartment->department_id !== $department->id) {
            abort(403, 'Unauthorized action.');
        }

        if ($this->getDepartmentSlug($department) === 'legal') {
            return back()->with('info', 'Legal reviewer sudah di-assign otomatis saat review dimulai.');
        }

        $request->validate([
            'staff_user_id' => 'required|exists:tbl_user,id_user',
            'notes'         => 'nullable|string|max:1000',
        ]);

        $staffUser = TblUser::where('id_user', $request->staff_user_id)->first();

        if (!$staffUser) {
            return back()->with('error', 'Staff user not found.');
        }

        $staffRole = $this->getStaffRoleName($department);
        $adminRole = $this->getAdminRoleName($department);

        if (!$staffUser->hasRole($staffRole) && !$staffUser->hasRole($adminRole)) {
            return back()->with('error', 'Selected user is not a member of this department.');
        }

        DB::beginTransaction();

        try {
            $contractDepartment->update([
                'status'            => 'assigned',
                'assigned_admin_id' => $user->id_user,
                'assigned_at'       => now(),
            ]);

            $existingStage = ContractReviewStage::where('contract_id', $contractDepartment->contract_id)
                ->where('department_id', $department->id)
                ->where('stage_type', $this->getDepartmentSlug($department))
                ->whereNull('assigned_user_id')
                ->first();

            if ($existingStage) {
                $existingStage->update([
                    'assigned_user_id' => $request->staff_user_id,
                    'status'           => 'pending',
                    'notes'            => $request->notes,
                    'created_by'       => $user->id_user,
                ]);

                if ($existingStage->parallel_group !== null) {
                    $parallelAlreadyActivated = ContractReviewStage::where('contract_id', $existingStage->contract_id)
                        ->where('parallel_group', $existingStage->parallel_group)
                        ->where('id', '!=', $existingStage->id)
                        ->whereIn('status', ['in_progress', 'completed'])
                        ->exists();

                    if ($parallelAlreadyActivated) {
                        $existingStage->update(['status' => 'assigned']);
                        Log::info('[BUG FIX] Placeholder stage langsung Assigned karena parallel group sudah aktif', [
                            'stage_id'       => $existingStage->id,
                            'department'     => $department->code,
                            'staff_user_id'  => $request->staff_user_id,
                            'parallel_group' => $existingStage->parallel_group,
                        ]);
                    }
                }

                $stage = $existingStage->fresh();

                Log::info('✅ Placeholder stage updated with staff assignment', [
                    'stage_id'       => $stage->id,
                    'department'     => $department->code,
                    'staff_user_id'  => $request->staff_user_id,
                    'final_status'   => $stage->status,
                    'parallel_group' => $stage->parallel_group,
                ]);

            } else {
                Log::warning('⚠️ No placeholder found, creating new stage (backward compat)', [
                    'contract_id' => $contractDepartment->contract_id,
                    'department'  => $department->code,
                ]);

                $selectedDepts = json_decode(
                    $contractDepartment->contract->selected_departments ?? '[]', true
                );
                $parallelGroup = count($selectedDepts) >= 2 ? 1 : null;

                $lastSequence = ContractReviewStage::where('contract_id', $contractDepartment->contract_id)
                    ->max('sequence') ?? 0;

                $stage = ContractReviewStage::create([
                    'contract_id'      => $contractDepartment->contract_id,
                    'department_id'    => $department->id,
                    'stage_name'       => $department->name . ' Review',
                    'stage_type'       => $this->getDepartmentSlug($department),
                    'assigned_user_id' => $request->staff_user_id,
                    'sequence'         => $lastSequence + 1,
                    'parallel_group'   => $parallelGroup,
                    'status'           => 'pending',
                    'notes'            => $request->notes,
                    'created_by'       => $user->id_user,
                ]);
            }

            $isSelfAssign = (int) $staffUser->id_user === (int) $user->id_user;
            if (!$isSelfAssign) {
                $this->sendStaffAssignmentNotification(
                    $staffUser,
                    $contractDepartment->contract,
                    $department,
                    $user,
                    $request->notes
                );
            }

            ContractReviewLog::create([
                'contract_id' => $contractDepartment->contract_id,
                'stage_id'    => $stage->id,
                'user_id'     => $user->id_user,
                'action'      => 'staff_assigned',
                'description' => $isSelfAssign
                    ? "Admin assigned themselves for {$department->name} review"
                    : "Staff assigned for {$department->name} review",
                'metadata' => [
                    'staff_user_id'  => $request->staff_user_id,
                    'staff_name'     => $staffUser->nama_user,
                    'is_self_assign' => $isSelfAssign,
                    'department_id'  => $department->id,
                    'parallel_group' => $stage->parallel_group,
                    'stage_status'   => $stage->status,
                    'notes'          => $request->notes,
                ],
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
                'user_id'                => $user->id_user,
                'contract_department_id' => $contractDepartment->id,
                'staff_user_id'          => $request->staff_user_id,
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