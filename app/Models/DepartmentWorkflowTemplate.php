<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class DepartmentWorkflowTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'department_id',
        'name',
        'stages_config',
        'is_default',
        'is_active',
    ];

    protected $casts = [
        'stages_config' => 'array',
        'is_default' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function getStageCountAttribute()
    {
        return count($this->stages_config ?? []);
    }

    public function getStageNamesAttribute()
    {
        return collect($this->stages_config)->pluck('stage_name')->toArray();
    }
}