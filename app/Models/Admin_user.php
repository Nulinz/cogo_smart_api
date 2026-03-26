<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Admin_user extends Authenticatable
{
    protected $connection = 'mysql';  // Use tenant DB

    protected $table = 'admin_users';

    protected $fillable = [
        'name',
        'role',
        'phone',
        'password',
        'status',
        'c_by',
    ];
}
