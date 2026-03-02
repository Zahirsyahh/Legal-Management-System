<?php

namespace App\Policies;

use App\Models\Contract;
use App\Models\TblUser;

class ContractPolicy
{
    /**
     * Lihat contract
     */
    public function view(TblUser $user, Contract $contract): bool
    {
        return $user->id_user === $contract->user_id
            || $user->hasRole(['admin', 'legal']);
    }


    public function editWorkflow(User $user, Contract $contract)
    {
        // Only legal and admin can edit workflow
        return $user->hasRole(['legal', 'admin']);
    }
    
    /**
     * Generate nomor contract
     */
    public function generateNumber(TblUser $user, Contract $contract): bool
    {
        return $user->hasRole(['admin', 'legal']);
    }

    /**
     * Boleh create contract
     */
    public function create(TblUser $user): bool
    {
        return $user->hasRole(['user', 'admin', 'legal']);
    }

    /**
     * Update contract (hanya owner & masih draft)
     */
    public function update(TblUser $user, Contract $contract): bool
    {
        return $user->id_user === $contract->user_id
            && $contract->status === 'draft';
    }

    /**
     * Delete contract
     */
    public function delete(TblUser $user, Contract $contract): bool
    {
        if ($user->hasRole('admin')) {
            return true;
        }

        return $user->id_user === $contract->user_id
            && $contract->status === 'draft';
    }

    /**
     * Force delete (admin only)
     */
    public function forceDelete(TblUser $user, Contract $contract): bool
    {
        return $user->hasRole('admin');
    }

    /**
     * Submit contract
     */
    public function submit(TblUser $user, Contract $contract): bool
    {
        return $user->id_user === $contract->user_id
            && $contract->status === 'draft';
    }

    /**
     * Cancel contract
     */
    public function cancel(TblUser $user, Contract $contract): bool
    {
        return $user->id_user === $contract->user_id
            && in_array($contract->status, [
                'draft',
                'submitted',
                'awaiting_document_upload',
            ]);
    }

    /**
     * Mulai review (legal / admin)
     */
    public function startReview(TblUser $user, Contract $contract): bool
    {
        return $user->hasRole(['legal', 'admin'])
            && method_exists($contract, 'canStartReview')
            && $contract->canStartReview();
    }

    /**
     * Akses stage review
     */
    public function accessStage(TblUser $user, Contract $contract, $stage): bool
    {
        return $stage->assigned_user_id === $user->id_user
            || $user->hasRole('admin')
            || $contract->user_id === $user->id_user;
    }
}
