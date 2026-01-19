<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Prime_load;
use App\Models\E_invoice;

class M_invoice extends Model
{
    protected $connection = 'tenant';  // Use tenant DB

    protected $table = 'm_invoice';

    protected $fillable = [
        'load_id',
        'ext_piece',
        'ext_amount',
        'grace_per',
        'price',
        'charges',
        'description',
        'file',
        'product_profit',
        'shift_loss',
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

    public function load_data()
    {
        return $this->belongsTo(Prime_load::class, 'load_id');
    }

    public function invoice_items()
    {
        return $this->hasMany(E_invoice::class, 'inv_id');
    }

    public function getCreatedAtAttribute($value)
    {
        return date('d-m-Y H:i:s', strtotime($value));
    }
}
