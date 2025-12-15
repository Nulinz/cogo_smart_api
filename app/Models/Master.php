<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Master extends Model
{
    protected $connection = 'mysql';  // Use tenant DB

    protected $table = 'users';

    protected $fillable = [
        'name',
        'l_name',
        'type',
        'f_id',
        'db_name',
        'phone',
        'otp',
        'otp_verified',
    ];
}
