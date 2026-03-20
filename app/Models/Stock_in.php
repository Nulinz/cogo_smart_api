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
        'clear_status',
        'c_by',
    ];

     public function getCreatedAtAttribute($value)
    {
        return date('d-m-Y H:i:s', strtotime($value));
    }

    public function product_data()
    {
        return $this->belongsTo(Product::class, 'product_id', 'id');
    }

    public function load_data()
    {
        return $this->belongsTo(Prime_load::class, 'load_id', 'id');
    }

    public function farm_data()
    {
        return $this->belongsTo(Farmer::class, 'farm_id', 'id');
    }
    
}
