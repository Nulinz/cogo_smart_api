<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Shift extends Model
{
    protected $connection = 'tenant';  // Use tenant DB

    protected $table = 'e_shift';

    protected $fillable = [
        'cat',
        'load_id',
        'to_load',
        'party_id',
        'product_id',
        'total_piece',
        'grace_piece',
        'grace_per',
        'bill_piece',
        'price',
        'bill_amount',
        'inv_no',
        'status',
        'c_by',
    ];

    protected $appends = ['created_by_name'];

    protected static function booted()
    {
        static::creating(function ($model) {

            // Run only when category is sales
            if ($model->cat !== 'others') {
                return;
            }

            // Prefix (can be static or from config/db)
            $padding = 3; // Default padding

            // Check if any farmer exists
            $count = self::where('cat', 'others')->count();

            // dd($count);

            if ($count === 0) {
                // First insert, fetch prefix & start number from sequence table
                $seq = DB::connection('tenant')->table('m_sequence')->where('status', 'active')->first();
                // ->where('module', 'farmer')
                // ->where('status', 'active')

                $prefix = $seq->stock_others_pref ?? 'PI';
                $next = $seq->stock_others_suf ?? 1;
                // $padding = 0;

            } else {

                $lastSeq = self::where('cat', 'others')
                    ->whereNotNull('inv_no')
                    ->orderBy('id', 'desc')
                    ->value('inv_no');

                if (! $lastSeq) {
                    $prefix = 'PI';
                    $next = 1;
                } else {

                    $parts = explode('-', $lastSeq);

                    $prefix = $parts[0] ?? 'PI';
                    $last = $parts[1] ?? 0;

                    $next = $last + 1;
                }

            }

            // Generate sequence PI-001
            $model->inv_no = $prefix.'-'.str_pad($next, $padding, '0', STR_PAD_LEFT);
        });
    }

    public function load_data()
    {
        return $this->belongsTo(Prime_load::class, 'load_id');
    }

    public function to_load()
    {
        return $this->belongsTo(Prime_load::class, 'to_load');
    }

    public function party_data()
    {
        return $this->belongsTo(Party::class, 'party_id');
    }

    public function product_data()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function getCreatedAtAttribute($value)
    {
        return date('d-m-Y H:i:s', strtotime($value));
    }

    public function created_by()
    {
        return $this->belongsTo(User::class, 'c_by', 'id');
    }

    public function getCreatedByNameAttribute()
    {
        return $this->created_by?->name ?? 'Unknown';
    }
}
