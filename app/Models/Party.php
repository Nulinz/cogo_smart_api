<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Party extends Model
{
    protected $connection = 'tenant';  // Use tenant DB

    protected $table = 'm_party';

    protected $fillable = [
        'party_seq',
        'party_en',
        'party_kn',
        'party_nick_en',
        'party_nick_kn',
        'com_name',
        'com_add',
        'party_location',
        'party_ph_no',
        'party_wp_no',
        'party_open_type',
        'party_open_bal',
        'party_acc_type',
        'party_b_name',
        'party_acc_name',
        'party_acc_no',
        'party_ifsc',
        'party_upi',
        'c_by',
        'status',
    ];
}
