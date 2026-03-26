<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Subscription extends Model
{
     protected $connection = 'tenant';  // Use tenant DB

    protected $table = 'subscription';

    protected $fillable = [

        'type',
        'duration',
        'amount',
        't_id',
        'pay_status',  
        'status',
        'expiry_date',            
    ];
}
