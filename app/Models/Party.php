<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Party extends Model
{
    protected $connection = 'tenant';  // Use tenant DB

    protected $table = 'm_party';

    protected $fillable = [
        'party_seq',
        'party_en',
        'party_nick_en',
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
        'fav'
    ];

    protected static function booted()
    {
        static::creating(function ($model) {

            // Prefix (can be static or from config/db)
           $padding = 0; // Default padding

            // Check if any farmer exists
            $count = self::count();

            // dd($count);

            if ($count === 0) {
                // First insert, fetch prefix & start number from sequence table
                $seq = DB::connection('tenant')->table('m_sequence')->where('status', 'active')->first();
                    // ->where('module', 'farmer')
                    // ->where('status', 'active')
                    

                $prefix = $seq->party_pref ?? 'CGSP';
                $next   = $seq->party_suf ?? 1;
                // $padding = 0;

            } else {
                // Subsequent inserts, get last inserted sequence
                // $prefix = 'CGSF'; // fallback prefix for subsequent inserts
                $lastSeq = self::whereNotNull('party_seq')
                    ->orderBy('id', 'desc')
                    ->value('party_seq');

                $prefix = substr($lastSeq, 0, strpos($lastSeq, '-'));

                $prefix = explode('-', $lastSeq)[0];
                $last  = explode('-', $lastSeq)[1];
                $next = $last + 1;
                 
        }

            // Generate sequence CGSL-001
            $model->party_seq = $prefix . '-' . str_pad($next,$padding, '0', STR_PAD_LEFT);
        });
    }

    // function for party cash relation

    public function party_cash()
    {
        return $this->hasMany(Party_cash::class, 'party_id', 'id');
    }
}
