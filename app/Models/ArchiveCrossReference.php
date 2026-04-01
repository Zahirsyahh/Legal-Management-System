<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ArchiveCrossReference extends Model
{
    use HasFactory;

    protected $table = 'archive_cross_references';

    protected $fillable = [
        'archive_id',
        'ref_doc_name',
        'ref_record_id',
        'ref_location',
        'ref_relation',
    ];

    /*
    |--------------------------------------------------------------------------
    | RELATION
    |--------------------------------------------------------------------------
    */

    // parent archive
    public function archive()
    {
        return $this->belongsTo(Archive::class, 'archive_id');
    }
}
