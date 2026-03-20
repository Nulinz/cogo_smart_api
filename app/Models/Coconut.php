<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Coconut extends Model
{
     protected $connection = 'tenant';  // Use tenant DB

    protected $table = 'coconut_avail';

    protected $fillable = [
        'farm_id',
        'coconut',
        'status',
        'c_by',
    ];

    public function farmer_data()
    {
        return $this->belongsTo(Farmer::class, 'farm_id', 'id');
    }

    public function emp_data()
    {
        return $this->belongsTo(User::class, 'c_by', 'id');
    }
}
