<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Stock_out extends Model
{
    protected $connection = 'tenant';  // Use tenant DB

    protected $table = 'stock_out';

    protected $fillable = [
        'cat',
        'load_id',
        'farm_id',
        'product_id',
        'total_piece',
        'grace_piece',
        'grace_per',
        'bill_piece',
        'price',
        'commission',
        'bill_amount',
        'adv',
        'quality',
        'total_amt',
        'status',
        'inv_no',
        'clear_status',
        'c_by',
    ];

    protected static function booted()
    {
        static::creating(function ($model) {

            // Run only when category is sales
            if ($model->cat !== 'sales') {
                return;
            }

            // Prefix (can be static or from config/db)
            $padding = 3; // Default padding

            // Check if any farmer exists
            $count = self::where('cat', 'sales')->count();

            // dd($count);

            if ($count === 0) {
                // First insert, fetch prefix & start number from sequence table
                $seq = DB::connection('tenant')->table('m_sequence')->where('status', 'active')->first();
                // ->where('module', 'farmer')
                // ->where('status', 'active')

                $prefix = $seq->stock_out_pref ?? 'SI';
                $next = $seq->stock_out_suf ?? 1;
                // $padding = 0;

            } else {

                $lastSeq = self::where('cat', 'sales')
                    ->whereNotNull('inv_no')
                    ->orderBy('id', 'desc')
                    ->value('inv_no');

                if (! $lastSeq) {
                    $prefix = 'SI';
                    $next = 1;
                } else {

                    $parts = explode('-', $lastSeq);

                    $prefix = $parts[0] ?? 'SI';
                    // $last = $parts[1] ?? 0;
                    $last = (int) ($parts[1] ?? 0);

                    $next = $last + 1;

                }

            }

            // Generate sequence CGS-SI-001
            $model->inv_no = $prefix.'-'.str_pad($next, $padding, '0', STR_PAD_LEFT);
        });
    }

    public function getCreatedAtAttribute($value)
    {
        return date('d-m-Y H:i:s', strtotime($value));
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id', 'id');
    }

    public function load_data()
    {
        return $this->belongsTo(Prime_load::class, 'load_id', 'id');
    }

    public function party()
    {
        return $this->belongsTo(Party::class, 'farm_id', 'id');
    }
}
