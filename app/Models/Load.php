<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Load extends Model
{
     protected $connection = 'tenant';  // Use tenant DB

    protected $table = 'e_load';

    protected $fillable = [
        'cat',
        'load_id',
        'farmer_id',
        'product_id',
        'total_piece',
        'grace_piece',
        'grace_per',
        'bill_piece',
        'price',
        'commission',
        'bill_amount',
        'adv',
        'quality',
        'total_amt',
        'status',
        'c_by',
    ];
        
}
