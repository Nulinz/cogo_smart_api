<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Master_db extends Model
{
    protected $connection = 'mysql';  // Use tenant DB

    protected $table = 'm_db';

    protected $fillable = [
        'db_name',
        'f_id',
        'status',
    ];
}
