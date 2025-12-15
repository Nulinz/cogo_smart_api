<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transport extends Model
{
    protected $connection = 'tenant';  // Use tenant DB

    protected $table = 'transport';

    protected $fillable = [
        'transport',
        'phone',
        'status',
        'c_by',
    ];
}
