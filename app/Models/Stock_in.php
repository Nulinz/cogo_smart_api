<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Stock_in extends Model
{
    protected $connection = 'tenant';  // Use tenant DB

    protected $table = 'stock_in';

    protected $fillable = [
        'cat',
        'load_id',
        'farm_id',
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
