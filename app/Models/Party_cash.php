<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Party_cash extends Model
{
     protected $connection = 'tenant';  // Use tenant DB

    protected $table = 'e_party';

    protected $fillable = [
        'party_id',
        'type',
        'amount',
        'method',
        'c_by',
        'status'
    ];

     protected $casts = [
        'created_at' => 'datetime:d-m-Y H:i:s',
    ];

    public function party()
    {
        return $this->belongsTo(Party::class, 'party_id', 'id');
    }

    // public function getCreatedAtAttribute($value)
    // {
    //     return date('d-m-Y H:i:s', strtotime($value));
    // }
}
