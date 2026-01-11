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

    public function getCreatedAtAttribute($value)
    {
        return date('d-m-Y H:i:s', strtotime($value));
    }

    public function farm_data()
    {
        return $this->belongsTo(Farmer::class, 'farm_id', 'id');
    }

    public function load_data()
    {
        return $this->belongsTo(Prime_load::class, 'load_id', 'id');
    }

}
