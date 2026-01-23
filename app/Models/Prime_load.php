<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Party;
use App\Models\Transporter;
use App\Models\Truck_capacity;

class Prime_load extends Model
{
     protected $connection = 'tenant';  // Use tenant DB

    protected $table = 'm_load';

    // protected $appends = ['team_members']; // <<< important

    protected $fillable = [
        'load_seq',
        'market',
        'product_id',
        'party_id',
        'empty_weight',
        'load_date',
        'veh_no',
        'dr_no',
        'transporter',
        'quality_price',
        'filter_price',
        'req_qty',
        'truck_capacity',
        'team',
        'status',
        'load_status',
        'c_by',
    ];

    protected $casts = [
        'team' => 'array',   // or 'json'
    ];

    protected $appends = ['created_by'];

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


    // public function team_members()
    // {
    //     return $this->belongsToMany(User::class, 'load_team', 'load_id', 'user_id');
    // }

    public function getTeamMembersAttribute()
    {
        if (empty($this->team) || !is_array($this->team)) {
            return collect();
        }

        return User::whereIn('id', $this->team)->select('id','name')->get();
    }

    public function party_data()
    {
        return $this->belongsTo(Party::class, 'party_id', 'id');
    }

    // public function party_data()
    // {
    //     return $this->hasMany(Party::class, 'id' ,'party_id');
    // }

    public function transporter()
    {
        return $this->hasMany(Transport::class, 'id', 'transporter');
    }

    public function truck_capacity()
    {
        return $this->belongsTo(Truck_capacity::class, 'truck_capacity','id');
    }

    public function product_data()
    {
        return $this->belongsTo(Product::class, 'product_id', 'id');
    }

    public function load_list()
    {
        return $this->hasMany(Load::class, 'load_id', 'id');
    }

    public function shift_list()
    {
        return $this->hasMany(Shift::class, 'load_id', 'id');
    }

   public function getCreatedByAttribute()
    {
        if (!$this->c_by) {
            return 'Unknown';
        }

        return User::where('id', $this->c_by)->value('name') ?? 'Unknown';
    }
}
