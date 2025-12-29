<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Stock_out extends Model
{
    protected $connection = 'tenant';  // Use tenant DB

    protected $table = 'stock_out';

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

    public function getCreatedAtAttribute($value)
    {
        return date('d-m-Y H:i:s', strtotime($value));
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id', 'id');
    }

    public function load_data()
    {
        return $this->belongsTo(Prime_load::class, 'load_id', 'id');
    }

    public function party()
    {
        return $this->belongsTo(Party::class, 'farm_id', 'id');
    }

}
