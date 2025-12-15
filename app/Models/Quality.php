<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Quality extends Model
{
    protected $connection = 'tenant';  // Use tenant DB

    protected $table = 'quality';

    protected $fillable = [
        'quality',
        'status',
        'c_by',
    ];
}
