<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $connection = 'tenant';  // Use tenant DB

    protected $table = 'm_product';

    protected $fillable = [
        'name_en',
        'name_kn',
        'type',
        'c_by',
        'status',
    ];
}
