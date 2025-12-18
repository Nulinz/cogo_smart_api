<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Prime_load extends Model
{
     protected $connection = 'tenant';  // Use tenant DB

    protected $table = 'm_load';

    protected $fillable = [
        'load_seq',
        'market',
        'party_id',
        'empty_weight',
        'load_date',
        'veh_no',
        'dr_no',
        'transporter',
        'quality_price',
        'fliter_price',
        'req_qty',
        'truck_capacity',
        'team',
        'status',
        'c_by',
    ];

    protected $casts = [
        'team' => 'array',   // or 'json'
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
                    

                $prefix = $seq->load_pref ?? 'CGSL';
                $next   = $seq->load_suf ?? 1;
                // $padding = 0;

            } else {
                // Subsequent inserts, get last inserted sequence
                // $prefix = 'CGSF'; // fallback prefix for subsequent inserts
                $lastSeq = self::whereNotNull('load_seq')
                    ->orderBy('id', 'desc')
                    ->value('load_seq');

                $prefix = substr($lastSeq, 0, strpos($lastSeq, '-'));

                $prefix = explode('-', $lastSeq)[0];
                $last  = explode('-', $lastSeq)[1];
                $next = $last + 1;
                 
        }

            // Generate sequence CGSL-001
            $model->load_seq = $prefix . '-' . str_pad($next,$padding, '0', STR_PAD_LEFT);
        });
    }

}
