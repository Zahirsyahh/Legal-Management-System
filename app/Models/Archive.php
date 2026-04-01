<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\ArchiveCrossReference;
use Carbon\Carbon;
use App\Models\TblUser;

class Archive extends Model
{
    use HasFactory;

    protected $table = 'archives';

    protected $fillable = [
        'record_id',
        'doc_number',
        'doc_name',
        'company',
        'doc_type',
        'department_code',
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
        'doc_status' => 'array'
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
        'scan',
        'copy',
        'original_hardcopy'
    ];

    public const DOC_STATUS_LABEL = [
        'scan' => 'Scan',
        'copy' => 'Copy',
        'original_hardcopy' => 'Original Hardcopy',
    ];

    /*
    |--------------------------------------------------------------------------
    | VERSION STATUS
    |--------------------------------------------------------------------------
    */

    public const VERSION_STATUS = [
        'latest',
        'obsolete',
        'superseded'
    ];

    public const VERSION_STATUS_LABEL = [
        'latest' => 'Latest',
        'obsolete' => 'Obsolete',
        'superseded' => 'Superseded',
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

    /*
    |--------------------------------------------------------------------------
    | RELATION : CROSS REFERENCES
    |--------------------------------------------------------------------------
    */

    // dia punya banyak cross reference
    public function crossReferences()
    {
        return $this->hasMany(ArchiveCrossReference::class, 'archive_id');
    }
}
