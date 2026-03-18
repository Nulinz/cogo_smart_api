<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Petty_cash extends Model
{
     protected $connection = 'tenant';  // Use tenant DB

    protected $table = 'petty_cash';

    protected $fillable = [
        'emp_id',
        'type',
        'amount',
        'method',
        'date',
        'status',
        'c_by',
    ];

    public $timestamps = false;

    public function getCreatedAtAttribute($value)
    {
        return date('d-m-Y H:i:s', strtotime($value));
    }
}
