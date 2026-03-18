<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Loss extends Model
{
    protected $connection = 'tenant';  // Use tenant DB

    protected $table = 'loss_category';

    protected $fillable = [
        'loss',
        'status',
        'c_by',
    ];
}
