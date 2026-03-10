<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Carbon\Carbon;

class Archive extends Model
{
    use HasFactory;

    protected $table = 'archives';

    protected $fillable = [
        'record_id',
        'doc_number',
        'doc_name',
        'doc_type',
        'department',
        'counterparty',
        'description',
        'doc_status',
        'version_status',
        'start_date',
        'end_date',
        'doc_location',
        'synology_path',
        'created_by',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    /*
    |--------------------------------------------------------------------------
    | DOCUMENT TYPE LIST
    |--------------------------------------------------------------------------
    */

    public const DOC_TYPES = [
        'CT' => 'Contract',
        'PM' => 'Payment',
        'LG' => 'Litigation',
        'DD' => 'Deeds',
        'SP' => 'Standard Procedure',
        'RF' => 'References',
        'CO' => 'Corporate Governance',
        'ID' => 'Identity',
        'RP' => 'Report',
        'LT' => 'Letter',
        'LS' => 'License',
    ];

    /*
    |--------------------------------------------------------------------------
    | DOCUMENT STATUS
    |--------------------------------------------------------------------------
    */

    public const DOC_STATUS = [
        'copy',
        'scancopy',
        'hardcopy',
        'born-digital'
    ];

    /*
    |--------------------------------------------------------------------------
    | VERSION STATUS
    |--------------------------------------------------------------------------
    */

    public const VERSION_STATUS = [
        'active',
        'obsolete',
        'superseded',
        'terminate'
    ];

    /*
    |--------------------------------------------------------------------------
    | ACCESSOR : DOCUMENT TYPE NAME
    |--------------------------------------------------------------------------
    */

    public function getDocTypeNameAttribute()
    {
        return self::DOC_TYPES[$this->doc_type] ?? $this->doc_type;
    }

    /*
    |--------------------------------------------------------------------------
    | ACCESSOR : VALIDITY STATUS
    |--------------------------------------------------------------------------
    | otomatis cek ongoing / ended
    */

    public function getValidityStatusAttribute()
    {
        if (!$this->end_date) {
            return 'ongoing';
        }

        return Carbon::now()->gt($this->end_date) ? 'ended' : 'ongoing';
    }

    /*
    |--------------------------------------------------------------------------
    | RELATION : CREATED BY USER
    |--------------------------------------------------------------------------
    */

    public function creator()
    {
        return $this->belongsTo(TblUser::class, 'created_by', 'id_user');
    }
}
