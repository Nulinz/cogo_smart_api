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
        'signature',
        'aadhar_front',
        'aadhar_back',
        'pan_front',
        'pan_back',
        'apmc_front',
        'apmc_back',
        'status',
        'c_by',
    ];
}
