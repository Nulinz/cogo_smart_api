<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class E_expense extends Model
{
   protected $connection = 'tenant';  // Use tenant DB

   protected $table = 'e_expense';

   protected $fillable = [
       'amount',
       'emp_id',
       'method',
       'status',
       'c_by',
   ];


}
