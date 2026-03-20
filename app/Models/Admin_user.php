<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Admin_user extends Model
{
    protected $connection = 'mysql';  // Use tenant DB

    protected $table = 'admin_users';

    protected $fillable = [
        'name',
        'phone',
        'password',
        'status',
        'c_by',
    ];
}
