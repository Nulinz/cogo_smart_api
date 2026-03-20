<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class M_invoice extends Model
{
    protected $connection = 'tenant';  // Use tenant DB

    protected $table = 'm_invoice';

    protected $fillable = [
        'load_id',
        'inv_no',
        'ext_piece',
        'ext_amount',
        'grace_per',
        'price',
        'charges',
        'description',
        'file',
        'product_profit',
        'shift_loss',
        'loading',
        'commission',
        'final_loss',
        'profit_loss',
        'status',
        'c_by',
    ];

    protected static function booted()
    {
        static::creating(function ($model) {

            // Prefix (can be static or from config/db)
            $padding = 3; // Default padding

            // Check if any farmer exists
            $count = self::count();

            // dd($count);

            if ($count === 0) {
                // First insert, fetch prefix & start number from sequence table
                $seq = DB::connection('tenant')->table('m_sequence')->where('status', 'active')->first();
                // ->where('module', 'farmer')
                // ->where('status', 'active')

                $prefix = $seq->inv_pref ?? 'INV';
                $next = $seq->inv_suf ?? 1;
                // $padding = 0;

            } else {

                $lastSeq = self::whereNotNull('inv_no')
                    ->orderBy('id', 'desc')
                    ->value('inv_no');

                if (! $lastSeq) {
                    $prefix = 'INV';
                    $next = 1;
                } else {

                    $parts = explode('-', $lastSeq);

                    $prefix = $parts[0] ?? 'INV';
                    $last = $parts[1] ?? 0;

                    $next = $last + 1;
                }

            }

            // Generate sequence INV-001
            $model->inv_no = $prefix.'-'.str_pad($next, $padding, '0', STR_PAD_LEFT);
        });
    }

    protected $casts = [
        'final_loss' => 'array',
        'charges' => 'array',
    ];

    public function load_data()
    {
        return $this->belongsTo(Prime_load::class, 'load_id');
    }

    public function invoice_items()
    {
        return $this->hasMany(E_invoice::class, 'inv_id');
    }

    public function getCreatedAtAttribute($value)
    {
        return date('d-m-Y H:i:s', strtotime($value));
    }
}
