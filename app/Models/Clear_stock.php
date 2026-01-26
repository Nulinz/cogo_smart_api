<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Clear_stock extends Model
{
    protected $connection = 'tenant';  // Use tenant DB

    protected $table = 'clear_stock';

    protected $fillable = [
        'product_id',
        'bill_piece',
        'grace_piece',
        'avg_price',
        'total_amt',
        'status',
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

}
