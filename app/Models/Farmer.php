<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Farmer extends Model
{
    protected $connection = 'tenant';  // Use tenant DB

    protected $table = 'm_farmer';

    protected $fillable = [
        'farm_seq',
        'farm_en',
        'farm_kn',
        'farm_nick_en',
        'farm_nick_kn',
        'location',
        'ph_no',
        'wp_no',
        'open_type',
        'open_bal',
        'acc_type',
        'b_name',
        'acc_name',
        'acc_no',
        'ifsc',
        'upi',
        'c_by',
        'status',
    ];
}
