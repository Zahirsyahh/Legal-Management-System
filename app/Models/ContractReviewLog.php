<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use App\Events\ReviewLogCreated;

class ContractReviewLog extends Model
{
    protected $fillable = [
        'contract_id',
        'stage_id',
        'user_id',
        'action',
        'description',
        'notes', 
        'metadata',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        // Untuk longtext, kita perlu custom cast atau decode manual
    ];

    // ACCESSOR untuk metadata
    public function getMetadataAttribute($value)
    {
        if (empty($value)) {
            return [];
        }
        
        // Coba decode JSON
        if (is_string($value)) {
            $decoded = json_decode($value, true);
            return $decoded ?? [];
        }
        
        // Jika sudah array, return langsung
        return $value ?? [];
    }

    // MUTATOR untuk metadata
    public function setMetadataAttribute($value)
    {
        if (is_array($value)) {
            $this->attributes['metadata'] = json_encode($value, JSON_UNESCAPED_UNICODE);
        } elseif (is_null($value)) {
            $this->attributes['metadata'] = null;
        } else {
            $this->attributes['metadata'] = $value;
        }
    }

    public function contract()
    {
        return $this->belongsTo(Contract::class);
    }

    public function stage()
    {
        return $this->belongsTo(ContractReviewStage::class, 'stage_id');
    }

    public function user()
    {
        return $this->belongsTo(
            TblUser::class,
            'user_id',     // FK di contract_review_logs
            'id_user'      // PK di tbl_user (WAJIB)
        );
    }

        public static function logAction(
            Contract $contract,
            ?ContractReviewStage $stage,
            string $action,
            ?string $description = null,
            ?string $notes = null,
            ?array $metadata = null
        ) {
        $log = self::create([
            'contract_id' => $contract->id,
            'stage_id' => $stage?->id,
            'user_id' => Auth::id(),
            'action' => $action,
            'description' => $description,
            'notes' => $notes,
            'metadata' => $metadata,
        ]);

        // 🔥 INI WAJIB sebelum return
        event(new ReviewLogCreated($log));

        return $log;
    }
}