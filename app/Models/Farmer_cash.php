<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Farmer;
use App\Models\Prime_load;

class Farmer_cash extends Model
{
     protected $connection = 'tenant';  // Use tenant DB

    protected $table = 'e_farmer';

    protected $fillable = [
        'farm_id',
        'load_id',
        'type',
        'amount',
        'method',
        'status',
        'c_by',
    ];

    protected $casts = [
        'created_at' => 'datetime:d-m-Y H:i:s',
    ];

    protected $appends = ['created_by_name'];

    // public function getCreatedAtAttribute($value)
    // {
    //     return date('d-m-Y H:i:s', strtotime($value));
    // }

    public function farm_data()
    {
        return $this->belongsTo(Farmer::class, 'farm_id', 'id');
    }

    public function load_data()
    {
        return $this->belongsTo(Prime_load::class, 'load_id', 'id');
    }

    public function created_by()
    {
        return $this->belongsTo(User::class, 'c_by', 'id');
    }

    public function getCreatedByNameAttribute()
    {
        return $this->created_by?->name ?? 'Unknown';
    }

}
