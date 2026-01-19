<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Shift extends Model
{
     protected $connection = 'tenant';  // Use tenant DB

    protected $table = 'e_shift';

    protected $fillable = [
        'cat',
        'load_id',
        'to_load',
        'party_id',
        'product_id',
        'total_piece',
        'grace_piece',
        'grace_per',
        'bill_piece',
        'price',
        'bill_amount',
        'status',
        'c_by',
    ];

    public function load_data()
    {
        return $this->belongsTo(Prime_load::class, 'load_id');
    }


     public function to_load()
    {
        return $this->belongsTo(Prime_load::class, 'to_load');
    }

    public function party_data()
    {
        return $this->belongsTo(Party::class, 'party_id');
    }

    public function product_data()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
}
