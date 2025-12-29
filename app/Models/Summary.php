<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Summary extends Model
{
     protected $connection = 'tenant';  // Use tenant DB

    protected $table = 'load_summary';

    protected $fillable = [
        'load_id',
        'filter_total',
        'filter_price',
        'filter_amount',
        'product_id',
        'exp_loading',  
        'exp_misc',
        'exp_rmc',
        'total',
        'grace',
        'grace_per',
        'billing_amt',
        'avg_price',
        'total_weight',
        'empty_weight',
        'net_weight',
        'avg_per_weight',
        'shift_loss',
        'status',
        'c_by'
    ];

    public function load_data()
    {
        return $this->belongsTo(Prime_load::class, 'load_id', 'id');
    }

}
