<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class M_invoice extends Model
{
    protected $connection = 'tenant';  // Use tenant DB

    protected $table = 'm_invoice';

    protected $fillable = [
        'load_id',
        'ext_piece',
        'grace_per',
        'price',
        'charges',
        'description',
        'file',
        'product_profit',
        'loading',
        'commission',
        'final_loss',
        'profit_loss',
        'status',
        'c_by'
    ];

    protected $casts = [
        'final_loss' => 'array',
        'charges' => 'array',
    ];
}
