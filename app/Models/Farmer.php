<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Farmer extends Model
{
    protected $connection = 'tenant';  // Use tenant DB

    protected $table = 'm_farmer';

    protected $fillable = [
        'farm_seq',
        'farm_en',
        'farm_kn',
        'farm_nick_en',
        'farm_nick_kn',
        'location',
        'ph_no',
        'wp_no',
        'open_type',
        'open_bal',
        'acc_type',
        'b_name',
        'acc_name',
        'acc_no',
        'ifsc',
        'upi',
        'c_by',
        'status',
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
                    

                $prefix = $seq->farmer_pref ?? 'CGSF';
                $next   = $seq->farmer_suf ?? 1;
                // $padding = 0;

            } else {
                // Subsequent inserts, get last inserted sequence
                // $prefix = 'CGSF'; // fallback prefix for subsequent inserts
                $lastSeq = self::whereNotNull('farm_seq')
                    ->orderBy('id', 'desc')
                    ->value('farm_seq');

                $prefix = substr($lastSeq, 0, strpos($lastSeq, '-'));

                $prefix = explode('-', $lastSeq)[0];
                $last  = explode('-', $lastSeq)[1];
                $next = $last + 1;
                 
        }

            // Generate sequence CGSL-001
            $model->farm_seq = $prefix . '-' . str_pad($next,$padding, '0', STR_PAD_LEFT);
        });
    }
}
