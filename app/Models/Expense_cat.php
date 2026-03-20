<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Expense_cat extends Model
{
     protected $connection = 'tenant';  // Use tenant DB

    protected $table = 'exp_cat';

    protected $fillable = [
        'cat',
        'status',
        'c_by',
    ];
}
