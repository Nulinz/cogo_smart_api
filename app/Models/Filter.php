<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Prime_load;
use App\Models\User;

class Filter extends Model
{
     protected $connection = 'tenant';  // Use tenant DB

    protected $table = 'filter';

    protected $fillable = [
        'load_id',
        'emp_id',
        'total',
        'status',
        'c_by'
    ];

    // public function load()
    // {
    //     return $this->belongsTo(Prime_load::class, 'load_id');
    // }

    public function emp_data()
    {
        return $this->belongsTo(User::class, 'emp_id');
    }
}
