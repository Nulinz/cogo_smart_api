<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Kyc extends Model
{
     protected $connection = 'tenant';  // Use tenant DB

    protected $table = 'kyc';

    protected $fillable = [
        'user_id',
        'f_name',
        'phone',
        'email',
        'dob',
        'com_name',
        'com_address',
        'com_gst',
        'com_pan',
        'file',
        'status',
        'c_by',
    ];
}
