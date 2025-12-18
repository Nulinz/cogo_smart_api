<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sequence extends Model
{
    //
     protected $connection = 'tenant';  // Use tenant DB

     protected $table = 'm_sequence';

     protected $fillable = [
         'load_pref',
         'load_suf',
         'farmer_pref',
         'farmer_suf',
         'party_pref',
         'party_suf',
         'status',
         'c_by',
     ];
}
