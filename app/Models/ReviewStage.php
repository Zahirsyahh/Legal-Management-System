<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReviewStage extends Model
{
    protected $fillable = [
        'contract_id',
        'stage_type',
        'stage_name',
        'sequence',
        'assigned_user_id',
        'status',
        'notes',
        'action_notes',
        'jump_to_stage_id',
        'action_taken_at',
        'action_taken_by',
        'action_type',
        'comments',
        'action', // approve, request_revision, etc.
        'completed_at'
    ];

    protected $dates = ['action_taken_at', 'assigned_at', 'started_at', 'completed_at'];
    
    protected $casts = ['completed_at' => 'datetime'];

    public function contract(){
        return $this->belongsTo(Contract::class);
    }

    public function assignedUser(){
        return $this->belongsTo(TblUser::class, 'assigned_user_id');
    }

    public function actionTakenBy(){
        return $this->belongsTo(TblUser::class, 'action_taken_by');
    }

    public function jumpToStage(){
        return $this->belongsTo(TblUser::class, 'jump_to_stage');
    }

    public function isUserStage(){
        return $this->belongsTo(ReviewStage::class, 'jump_to_stage_id');
    }

    public function isActive(){
        return in_array($this->status, ['assigned', 'in_progress']);
    }
}