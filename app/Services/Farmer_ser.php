<?php

namespace App\Services;

use App\Models\Farmer;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class Farmer_ser
{
    public static function create_farm(array $data)
    {

        return Farmer::updateOrCreate(
            ['id' => $data['farm_id'] ?? null],
            [
                'farm_seq' => 1,
                'farm_en' => $data['farm_en'],
                'farm_kn' => $data['farm_kn'],
                'farm_nick_en' => $data['farm_nick_en'],
                'farm_nick_kn' => $data['farm_nick_kn'],
                'location' => $data['location'],
                'ph_no' => $data['ph_no'],
                'wp_no' => $data['wp_no'],
                'open_type' => $data['open_type'],
                'open_bal' => $data['open_bal'],
                'acc_type' => $data['acc_type'],
                'b_name' => $data['b_name'],
                'acc_name' => $data['acc_name'],
                'acc_no' => $data['acc_no'],
                'ifsc' => $data['ifsc'],
                'upi' => $data['upi'],
                'c_by' => Auth::guard('tenant')->user()->id ?? null,
            ]
        );
    }

    // public static function main()
    // {
    //     // Reconnect to default MySQL DB
    //     DB::purge('mysql');
    //     DB::reconnect('mysql');
    // }
}
