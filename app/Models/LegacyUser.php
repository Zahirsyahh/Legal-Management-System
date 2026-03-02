<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LegacyUser extends Model
{
    protected $table = 'tbl_user';
    protected $primaryKey = 'id_user';
    public $timestamps = false;
    
    protected $fillable = [
        'id_user',
        'kode_department',
        // tambahkan field lain jika perlu
    ];
    
    /**
     * Relationship to Laravel User
     */
    public function user()
    {
        return $this->belongsTo(TblUser::class, 'id_user', 'id');
    }
}