<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Expense extends Model
{
    protected $connection = 'tenant';  // Use tenant DB

    protected $table = 'm_expense';

    protected $fillable = [
        'title',
        'exp_cat',
        'amount',
        'notes',
        'status',
        'c_by',
    ];

    protected $hidden = [
        'updated_at',
    ];

    public function exp_category()
    {
        return $this->belongsTo(Expense_cat::class, 'exp_cat', 'id');
    }

    public function exp_cby()
    {
        return $this->belongsTo(User::class, 'c_by', 'id');
    }

    public function getCreatedAtAttribute($value)
    {
        return date('Y-m-d H:i:s', strtotime($value));
    }
}
