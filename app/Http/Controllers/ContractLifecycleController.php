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

            // ✅ 1. Update contract
            $contract->update([
                'status'      => Contract::STATUS_EXECUTED,
                'executed_at' => now(),
                'executed_by' => $user->id_user,
            ]);

            // ✅ 2. Cari executing stage (bukan dari current_stage_id!)
            $executingStage = $contract->reviewStages()
                ->where('stage_type', 'executing')
                ->where('assigned_user_id', $contract->user_id)
                ->whereIn('status', ['assigned', 'in_progress'])
                ->first();

            if (!$executingStage) {
                // Fallback: cari berdasarkan FK
                if ($contract->executing_stage_id) {
                    $executingStage = ContractReviewStage::find($contract->executing_stage_id);
                }
            }

            if ($executingStage) {
                // ✅ 3. Complete executing stage
                $executingStage->update([
                    'status'       => 'completed',
                    'completed_at' => now(),
                ]);

                // ✅ 4. Cari archiving stage
                $archivingStage = null;
                
                // Coba via FK dulu
                if ($contract->archiving_stage_id) {
                    $archivingStage = ContractReviewStage::find($contract->archiving_stage_id);
                    if ($archivingStage && $archivingStage->status !== 'pending') {
                        $archivingStage = null;
                    }
                }
                
                // Fallback: cari berdasarkan stage_type
                if (!$archivingStage) {
                    $archivingStage = $contract->reviewStages()
                        ->where('stage_type', 'archiving')
                        ->where('status', 'pending')
                        ->orderBy('sequence')
                        ->first();
                }

                if ($archivingStage) {
                    // ✅ 5. Aktifkan archiving stage
                    $archivingStage->update([
                        'status'      => 'assigned',  // atau 'in_progress' tergantung flow
                        'assigned_at' => now(),
                        'started_at'  => now(),      // langsung mulai jika mau auto
                        'notes'       => 'Contract executed. Ready for archiving.',
                    ]);

                    // ✅ 6. Set current stage ke archiving (pake sequence ya!)
                    $contract->update([
                        'current_stage' => $archivingStage->sequence,
                        // 'current_stage_id' => $archivingStage->id, // Kalau pake field ini juga
                    ]);

                    // Notify legal yang bertanggung jawab
                    if ($archivingStage->assignedUser) {
                        try {
                            $archivingStage->assignedUser->notify(
                                new StageAssignedNotification($contract, $archivingStage, $user)
                            );
                        } catch (\Exception $e) {
                            Log::error('Notify archiving failed: ' . $e->getMessage());
                        }
                    }
                } else {
                    // Archiving stage tidak ditemukan, log error
                    Log::error('Archiving stage not found after execution', [
                        'contract_id' => $contract->id,
                        'archiving_stage_id' => $contract->archiving_stage_id
                    ]);
                }
            } else {
                // Executing stage tidak ditemukan
                Log::error('Executing stage not found', [
                    'contract_id' => $contract->id,
                    'executing_stage_id' => $contract->executing_stage_id,
                    'user_id' => $user->id_user
                ]);
                
                return redirect()->route('contracts.show', $contract)
                    ->with('error', 'Executing stage not found. Please contact admin.');
            }

            // ✅ Log tetap
            ContractReviewLog::create([
                'contract_id' => $contract->id,
                'stage_id'    => $executingStage?->id,
                'user_id'     => $user->id_user,
                'action'      => 'contract_executed',
                'description' => 'Contract marked as executed by owner',
                'metadata'    => [
                    'executed_by' => $user->nama_user,
                    'executed_at' => now()->toDateTimeString(),
                    'archiving_activated' => isset($archivingStage),
                ]
            ]);

            DB::commit();

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('markAsExecuted failed: ' . $e->getMessage(), [
                'contract_id' => $contract->id,
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->route('contracts.show', $contract)
                ->with('error', 'Failed to execute contract: ' . $e->getMessage());
        }

        return redirect()->route('contracts.show', $contract)
            ->with('success', 'Contract executed & moved to archiving stage.');
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

            // ✅ 1. Update contract jadi archived
            $contract->update([
                'status'      => Contract::STATUS_ARCHIVED,
                'archived_at' => now(),
                'archived_by' => $user->id_user,
            ]);

            // ✅ 2. Ambil current stage (harusnya archiving stage)
            $currentStage = $contract->reviewStages()
                ->where('id', $contract->current_stage_id)
                ->first();

            if ($currentStage) {
                // ✅ 3. Complete archiving stage
                $currentStage->update([
                    'status'       => 'completed',
                    'completed_at' => now(),
                ]);
            }

            // ✅ 4. OPTIONAL: set tidak ada active stage lagi
            $contract->update([
                'current_stage_id' => null
            ]);

            // ✅ 5. Logging
            ContractReviewLog::create([
                'contract_id' => $contract->id,
                'stage_id'    => $currentStage?->id,
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
