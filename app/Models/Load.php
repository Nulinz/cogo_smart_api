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
        'shift_id',
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

    protected $appends = ['created_by_name'];
    // relationships
    public function farmer_data()
    {
        return $this->belongsTo(Farmer::class, 'farmer_id', 'id');
    }

    public function product_data()
    {
        return $this->belongsTo(Product::class, 'product_id', 'id');
    }

    public function load_data()
    {
        return $this->belongsTo(Prime_load::class, 'load_id', 'id');
    }

     public function shift_load_data()
    {
        return $this->belongsTo(Shift::class, 'shift_id', 'id');
    }

    public function getCreatedAtAttribute($value)
    {
        return date('d-m-Y H:i:s', strtotime($value));
    }

    public function quality_details()
    {
        return $this->belongsTo(Quality::class, 'quality', 'id');
    }

    public function created_by()
    {
        return $this->belongsTo(User::class, 'c_by', 'id');
    }

    public function getCreatedByNameAttribute()
    {
        return $this->created_by?->name ?? 'Unknown';
    }

    

    
}
