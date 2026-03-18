<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Bank extends Model
{
     protected $connection = 'tenant';  // Use tenant DB

    protected $table = 'bank_details';

    protected $fillable = [
        'type',
        'f_id',
        'acc_type',
        'b_name',
        'acc_name',
        'acc_no',
        'ifsc',
        'upi',
        'status',
        'c_by',
    ];
}
