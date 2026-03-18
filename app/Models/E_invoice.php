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

    public function invoice()
    {
        return $this->belongsTo(M_invoice::class, 'inv_id');
    }

    public function load_data()
    {
        return $this->belongsTo(Prime_load::class, 'load_id');
    }

    public function product_data()
    {
        return $this->belongsTo(Product::class, 'product');
    }

    public function getCreatedAtAttribute($value)
    {
        return date('Y-m-d H:i:s', strtotime($value));
    }   
}
