<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Truck_capacity extends Model
{
    protected $connection = 'tenant';  // Use tenant DB

    protected $table = 'truck_cap';

    protected $fillable = [
        'capacity',
        'charge',
        'status',
        'c_by',
    ];
}
