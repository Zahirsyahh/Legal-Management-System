<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LegalContractComment extends Model
{
    protected $table = 'legal_contract_comments';

    protected $fillable = [
        'contract_id',
        'legal_user_id',
        'notes',
    ];

    /*
    |--------------------------------------------------------------------------
    | RELATIONS
    |--------------------------------------------------------------------------
    */

    public function contract()
    {
        return $this->belongsTo(Contract::class, 'contract_id');
    }

    public function legalUser()
    {
        return $this->belongsTo(TblUser::class, 'legal_user_id', 'id_user');
    }
}
