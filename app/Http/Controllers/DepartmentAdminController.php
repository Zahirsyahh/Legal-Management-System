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
use App\Notifications\InvitationDeclinedNotification;

class DepartmentAdminController extends Controller
{
    // ============================================================
    // DASHBOARD
    // ============================================================

    public function dashboard(Request $request)
    {
        $department = $this->requireDepartment();

        $pendingCount = ContractDepartment::whereHas('contract', fn($q) => $q->whereNull('deleted_at'))
            ->where('department_id', $department->id)
            ->where('status', 'pending_assignment')
            ->count();

        $activeCount = ContractDepartment::whereHas('contract', fn($q) => $q->whereNull('deleted_at'))
            ->where('department_id', $department->id)
            ->whereIn('status', ['assigned', 'in_progress'])
            ->count();

        $completedCount = ContractDepartment::whereHas('contract', fn($q) => $q->whereNull('deleted_at'))
            ->where('department_id', $department->id)
            ->where('status', 'completed')
            ->count();

        $pendingAssignments = ContractDepartment::whereHas('contract', fn($q) => $q->whereNull('deleted_at'))
            ->with(['contract.user', 'contract.legalAssigned'])
            ->where('department_id', $department->id)
            ->where('status', 'pending_assignment')
            ->latest()
            ->limit(10)
            ->get();

        $activeReviews = ContractDepartment::whereHas('contract', fn($q) => $q->whereNull('deleted_at'))
            ->with(['contract', 'assignedAdmin'])
            ->where('department_id', $department->id)
            ->whereIn('status', ['assigned', 'in_progress'])
            ->latest('updated_at')
            ->limit(10)
            ->get();

        return view(
            $this->getDepartmentViewPath($department),
            compact('department', 'pendingCount', 'activeCount', 'completedCount', 'pendingAssignments', 'activeReviews')
        );
    }

    // ============================================================
    // PENDING ASSIGNMENTS (REVIEW INVITATIONS LIST PAGE)
    // ============================================================

    public function pendingAssignments(Request $request)
    {
        $department = $this->requireDepartment();

        $search = trim($request->input('search', ''));
        $status = $request->input('status', 'all');
        $sort   = $request->input('sort', 'due_date');

        $allPendingAssignments = ContractDepartment::whereHas('contract', fn($q) => $q->whereNull('deleted_at'))
            ->with(['contract.user', 'contract.legalAssigned', 'contract.reviewStages.assignedUser', 'assignedAdmin'])
            ->where('department_id', $department->id)
            ->where('status', 'pending_assignment')
            ->get();

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

        $query = ContractDepartment::whereHas('contract', function ($q) use ($search) {
                $q->whereNull('deleted_at');
                if ($search !== '') {
                    $q->where(fn($sq) => $sq->where('title', 'like', "%{$search}%")
                        ->orWhere('contract_number', 'like', "%{$search}%"));
                }
            })
            ->with(['contract.user', 'contract.legalAssigned', 'contract.reviewStages.assignedUser', 'assignedAdmin'])
            ->where('department_id', $department->id)
            ->where('status', 'pending_assignment');

        if (in_array($status, ['overdue', 'due_soon', 'pending'])) {
            $allFiltered = $query->get()->filter(function ($a) use ($status, $now) {
                $d       = $a->contract?->drafting_deadline ?? ($a->due_date ?? null);
                $dueDate = $d ? \Carbon\Carbon::parse($d) : null;
                $isOverdue = $dueDate && $dueDate->isPast();
                $isDueSoon = $dueDate && !$isOverdue && $dueDate->diffInDays($now) <= 7;
                return match ($status) {
                    'overdue'  => $isOverdue,
                    'due_soon' => $isDueSoon,
                    'pending'  => !$isOverdue && !$isDueSoon,
                    default    => true,
                };
            });
            $allFiltered        = $this->sortCollection($allFiltered, $sort);
            $pendingAssignments = $this->paginateCollection($allFiltered, 10, $request);
        } else {
            $query              = $this->applySortToQuery($query, $sort);
            $pendingAssignments = $query->paginate(10)->withQueryString();
        }

        $availableStaff = $department->activeStaff()->count();

        return view('departments.pending-reviews', compact(
            'pendingAssignments', 'allPendingAssignments', 'department',
            'dueThisWeek', 'overdueAssignments', 'availableStaff'
        ));
    }

    // ============================================================
    // ACCEPT INVITATION
    // Admin joins workflow automatically. Redirects to contracts.show
    // with a success popup message.
    // ============================================================

    public function acceptInvitation(ContractDepartment $contractDepartment)
    {
        $user       = Auth::user();
        $department = $this->requireDepartment();

        if (!$contractDepartment->contract) {
            return back()->with('error', 'Contract no longer exists or has been deleted.');
        }

        if ($contractDepartment->department_id !== $department->id) {
            abort(403, 'You can only accept invitations for your own department.');
        }

        if ($contractDepartment->status !== 'pending_assignment') {
            return back()->with('error', 'This invitation has already been responded to.');
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
                $newStatus = 'pending';

                if ($existingStage->parallel_group !== null) {
                    $parallelAlreadyActivated = ContractReviewStage::where('contract_id', $existingStage->contract_id)
                        ->where('parallel_group', $existingStage->parallel_group)
                        ->where('id', '!=', $existingStage->id)
                        ->whereIn('status', ['in_progress', 'assigned', 'completed'])
                        ->exists();

                    if ($parallelAlreadyActivated) {
                        $newStatus = 'assigned';
                    }
                }

                $existingStage->update([
                    'assigned_user_id' => $user->id_user,
                    'status'           => $newStatus,
                    'notes'            => 'Accepted by department admin: ' . $user->nama_user,
                ]);

                $stage = $existingStage->fresh();
            } else {
                Log::warning('[acceptInvitation] No placeholder stage found, creating new stage', [
                    'contract_id' => $contractDepartment->contract_id,
                    'department'  => $department->code,
                ]);

                $selectedDepts = json_decode($contractDepartment->contract->selected_departments ?? '[]', true);
                $parallelGroup = count($selectedDepts) >= 2 ? 1 : null;
                $lastSequence  = ContractReviewStage::where('contract_id', $contractDepartment->contract_id)
                    ->max('sequence') ?? 0;

                $stage = ContractReviewStage::create([
                    'contract_id'      => $contractDepartment->contract_id,
                    'department_id'    => $department->id,
                    'stage_name'       => $department->name . ' Review',
                    'stage_type'       => $this->getDepartmentSlug($department),
                    'assigned_user_id' => $user->id_user,
                    'sequence'         => $lastSequence + 1,
                    'parallel_group'   => $parallelGroup,
                    'status'           => 'pending',
                    'notes'            => 'Accepted by department admin: ' . $user->nama_user,
                    'created_by'       => $user->id_user,
                ]);
            }

            ContractReviewLog::create([
                'contract_id' => $contractDepartment->contract_id,
                'stage_id'    => $stage->id,
                'user_id'     => $user->id_user,
                'action'      => 'invitation_accepted',
                'description' => $user->nama_user . ' accepted the review invitation for ' . $department->name,
                'metadata'    => [
                    'department_id' => $department->id,
                    'stage_id'      => $stage->id,
                    'stage_status'  => $stage->status,
                ],
            ]);

            DB::commit();

            // Redirect ke contracts.show dengan popup "You've joined" 
            return redirect()
                ->route('contracts.show', $contractDepartment->contract)
                ->with('invitation_accepted', "You've joined the <strong>{$contractDepartment->contract->title}</strong> review workflow!");

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('acceptInvitation failed: ' . $e->getMessage(), [
                'user_id'                => $user->id_user,
                'contract_department_id' => $contractDepartment->id,
            ]);
            return back()->with('error', 'Failed to accept invitation: ' . $e->getMessage());
        }
    }

    // ============================================================
    // DECLINE INVITATION
    // Marks department as declined, skips placeholder stage,
    // sends notification to all Legal users, and redirects back
    // with a descriptive flash message.
    // ============================================================

    public function declineInvitation(Request $request, ContractDepartment $contractDepartment)
    {
        $request->validate([
            'decline_reason' => 'nullable|string|max:500',
        ]);

        $user       = Auth::user();
        $department = $this->requireDepartment();

        if (!$contractDepartment->contract) {
            return back()->with('error', 'Contract no longer exists or has been deleted.');
        }

        if ($contractDepartment->department_id !== $department->id) {
            abort(403, 'You can only decline invitations for your own department.');
        }

        if ($contractDepartment->status !== 'pending_assignment') {
            return back()->with('error', 'This invitation has already been responded to.');
        }

        DB::beginTransaction();
        try {
            $declineReason = $request->decline_reason;

            // 1. Update ContractDepartment → declined
            $contractDepartment->update([
                'status'      => 'declined',
                'declined_at' => now(),
                'declined_by' => $user->id_user,
                'notes'       => $declineReason ?? 'Declined by department admin.',
            ]);

            // 2. Mark placeholder stage as skipped
            $placeholderStage = ContractReviewStage::where('contract_id', $contractDepartment->contract_id)
                ->where('department_id', $department->id)
                ->where('stage_type', $this->getDepartmentSlug($department))
                ->whereNull('assigned_user_id')
                ->first();

            if ($placeholderStage) {
                $placeholderStage->update([
                    'status' => 'declined',
                    'notes'  => 'Invitation declined by ' . $user->nama_user
                                . ($declineReason ? ': ' . $declineReason : '.'),
                ]);
            }

            // 3. Log the action
            ContractReviewLog::create([
                'contract_id' => $contractDepartment->contract_id,
                'stage_id'    => $placeholderStage?->id,
                'user_id'     => $user->id_user,
                'action'      => 'invitation_declined',
                'description' => $user->nama_user . ' declined the review invitation for ' . $department->name,
                'metadata'    => [
                    'department_id'  => $department->id,
                    'decline_reason' => $declineReason,
                ],
            ]);

            DB::commit();

            // 4. Notify all active Legal users (non-blocking)
            try {
                $legalUsers = TblUser::role('legal')
                    ->where('status_karyawan', 'AKTIF')
                    ->get();

                foreach ($legalUsers as $legalUser) {
                    $legalUser->notify(new InvitationDeclinedNotification(
                        $contractDepartment->contract,
                        $department,
                        $user,
                        $declineReason
                    ));
                }

                // Also notify admin
                $admins = TblUser::role('admin')
                    ->where('status_karyawan', 'AKTIF')
                    ->get();

                foreach ($admins as $admin) {
                    $admin->notify(new InvitationDeclinedNotification(
                        $contractDepartment->contract,
                        $department,
                        $user,
                        $declineReason
                    ));
                }
            } catch (\Exception $e) {
                Log::error('InvitationDeclinedNotification failed: ' . $e->getMessage());
            }

            $contractTitle = $contractDepartment->contract->title;

            return redirect()
                ->route($this->getDepartmentRouteName($department))
                ->with('invitation_declined', "Invitation to <strong>{$contractTitle}</strong> review has been declined.");

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('declineInvitation failed: ' . $e->getMessage(), [
                'user_id'                => $user->id_user,
                'contract_department_id' => $contractDepartment->id,
            ]);
            return back()->with('error', 'Failed to decline invitation: ' . $e->getMessage());
        }
    }

    // ============================================================
    // ACTIVE REVIEWS
    // ============================================================

    public function activeReviews()
    {
        $department = $this->requireDepartment();

        $reviews = ContractDepartment::whereHas('contract', function ($query) {
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

    // ============================================================
    // COMPLETED REVIEWS
    // ============================================================

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

    // ============================================================
    // PRIVATE HELPERS
    // ============================================================

    private function applySortToQuery($query, string $sort)
    {
        return match ($sort) {
            'title'           => $query->join('contracts', 'contract_departments.contract_id', '=', 'contracts.id')
                                       ->orderBy('contracts.title')
                                       ->select('contract_departments.*'),
            'contract_number' => $query->join('contracts', 'contract_departments.contract_id', '=', 'contracts.id')
                                       ->orderBy('contracts.contract_number')
                                       ->select('contract_departments.*'),
            default           => $query->latest('contract_departments.created_at'),
        };
    }

    private function sortCollection($collection, string $sort)
    {
        return match ($sort) {
            'title'           => $collection->sortBy(fn($a) => $a->contract?->title ?? ''),
            'contract_number' => $collection->sortBy(fn($a) => $a->contract?->contract_number ?? ''),
            default           => $collection->sortBy(fn($a) => $a->contract?->drafting_deadline
                                                                   ?? ($a->due_date ?? '9999-12-31')),
        };
    }

    private function paginateCollection($collection, int $perPage, Request $request)
    {
        $page  = $request->input('page', 1);
        $total = $collection->count();
        $items = $collection->values()->forPage($page, $perPage);

        return new \Illuminate\Pagination\LengthAwarePaginator(
            $items, $total, $perPage, $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );
    }

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

    public function getDepartmentSlug(Department $department): string
    {
        return match ($department->code) {
            'FIN'   => 'finance',
            'ACC'   => 'accounting',
            'TAX'   => 'tax',
            default => abort(403, 'Invalid department code.'),
        };
    }

    private function getStaffRoleName(Department $department): string
    {
        return match ($department->code) {
            'FIN'   => 'staff_fin',
            'ACC'   => 'staff_acc',
            'TAX'   => 'staff_tax',
            default => 'staff_' . strtolower($department->code),
        };
    }

    private function getAdminRoleName(Department $department): string
    {
        return match ($department->code) {
            'FIN'   => 'admin_fin',
            'ACC'   => 'admin_acc',
            'TAX'   => 'admin_tax',
            default => 'admin_' . strtolower($department->code),
        };
    }

    private function getUserDepartment($user)
    {
        if ($user->hasRole('admin_fin')) return Department::where('code', 'FIN')->first();
        if ($user->hasRole('admin_acc')) return Department::where('code', 'ACC')->first();
        if ($user->hasRole('admin_tax')) return Department::where('code', 'TAX')->first();
        return null;
    }

    private function sendStaffAssignmentNotification($staffUser, $contract, $department, $admin, $notes = null)
    {
        try {
            $staffUser->notify(new StaffAssignedNotification($contract, $department, $admin, $notes));
        } catch (\Throwable $e) {
            Log::error('Failed to send staff assignment notification', [
                'error'       => $e->getMessage(),
                'contract_id' => $contract->id ?? null,
                'staff_id'    => $staffUser->id_user ?? null,
            ]);
        }
    }
}