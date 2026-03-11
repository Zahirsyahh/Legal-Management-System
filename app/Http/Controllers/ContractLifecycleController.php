<?php

namespace App\Http\Controllers;

use App\Models\Contract; 
use Illuminate\Http\Request;
use App\Models\TblUser;
use App\Models\ContractReviewLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ContractLifecycleController extends Controller
{
    /**
     * User menandai kontrak sudah di-TTD (executed)
     */
    public function markAsExecuted(Contract $contract)
    {
        $user = TblUser::find(Auth::id());

        if (!$contract->canBeExecuted($user)) {
            return redirect()->route('contracts.show', $contract)
                ->with('error', 'You are not authorized to execute this contract, or contract is not in the correct status.');
        }

        try {
            DB::beginTransaction();

            $contract->update([
                'status'      => Contract::STATUS_EXECUTED,
                'executed_at' => now(),
                'executed_by' => $user->id_user,
            ]);

            ContractReviewLog::create([
                'contract_id' => $contract->id,
                'stage_id'    => null,
                'user_id'     => $user->id_user,
                'action'      => 'contract_executed',
                'description' => 'Contract marked as executed by owner',
                'metadata'    => [
                    'executed_by'    => $user->nama_user,
                    'executed_by_email' => $user->email,
                    'executed_at'    => now()->toDateTimeString(),
                ]
            ]);

            DB::commit();

            Log::info('Contract marked as executed', [
                'contract_id' => $contract->id,
                'user_id'     => $user->id_user,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to mark contract as executed: ' . $e->getMessage());

            return redirect()->route('contracts.show', $contract)
                ->with('error', 'Failed to execute contract: ' . $e->getMessage());
        }

        return redirect()->route('contracts.show', $contract)
            ->with('success', 'Contract has been marked as executed. Legal will archive it shortly.');
    }

    /**
     * Legal menutup/mengarsipkan kontrak (archived)
     */
    public function markAsArchived(Contract $contract)
    {
        $user = TblUser::find(Auth::id());

        if (!$contract->canBeArchived($user)) {
            return redirect()->route('contracts.show', $contract)
                ->with('error', 'You are not authorized to archive this contract, or contract is not in the correct status.');
        }

        try {
            DB::beginTransaction();

            $contract->update([
                'status'      => Contract::STATUS_ARCHIVED,
                'archived_at' => now(),
                'archived_by' => $user->id_user,
            ]);

            ContractReviewLog::create([
                'contract_id' => $contract->id,
                'stage_id'    => null,
                'user_id'     => $user->id_user,
                'action'      => 'contract_archived',
                'description' => 'Contract archived and closed by legal',
                'metadata'    => [
                    'archived_by'       => $user->nama_user,
                    'archived_by_email' => $user->email,
                    'archived_at'       => now()->toDateTimeString(),
                ]
            ]);

            DB::commit();

            Log::info('Contract archived', [
                'contract_id' => $contract->id,
                'user_id'     => $user->id_user,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to archive contract: ' . $e->getMessage());

            return redirect()->route('contracts.show', $contract)
                ->with('error', 'Failed to archive contract: ' . $e->getMessage());
        }

        return redirect()->route('contracts.show', $contract)
            ->with('success', '✅ Contract has been archived. Review process is complete.');
    }
}
