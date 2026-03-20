<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Admin_farmer extends Model
{
    protected $connection = 'mysql';  // Use tenant DB

    protected $table = 'farmers';

    protected $fillable = [
        'name',
        'nick',
        'phone',
        'whats_up',
        'location',
        'status',
        'c_by',

    ];
}
