<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Party;
use App\Models\User;
use App\Models\Bank;

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

     protected $appends = ['created_by_name'];

    public function party()
    {
        return $this->belongsTo(Party::class, 'party_id', 'id');
    }

    public function created_by()
    {
        return $this->belongsTo(User::class, 'c_by', 'id');
    }

     public function getCreatedByNameAttribute()
    {
        return $this->created_by?->name ?? 'Unknown';
    }

    public function party_bank_detail()
    {
        return $this->belongsTo(Bank::class, 'method');
    }

    // public function getCreatedAtAttribute($value)
    // {
    //     return date('d-m-Y H:i:s', strtotime($value));
    // }
}
