<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class E_invoice extends Model
{
     protected $connection = 'tenant';  // Use tenant DB

    protected $table = 'e_invoice';

    protected $fillable = [
        'inv_id',
        'load_id',
        'product',
        'total',
        'grace',
        'price',
        'bill_amt',
        'status',
        'c_by'
    ];
}
