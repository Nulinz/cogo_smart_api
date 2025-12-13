<?php

namespace App\Services;

use App\Models\Party;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class Party_ser
{
    public static function create_party(array $data)
    {

        return Party::updateOrCreate(
            ['id' => $data['party_id'] ?? null],
            [
                'party_seq' => 1,
                'party_en' => $data['party_en'],
                'party_kn' => $data['party_kn'],
                'party_nick_en' => $data['party_nick_en'],
                'party_nick_kn' => $data['party_nick_kn'],
                'com_name' => $data['com_name'],
                'com_add' => $data['com_add'],
                'party_location' => $data['party_location'],
                'party_ph_no' => $data['party_ph_no'],
                'party_wp_no' => $data['party_wp_no'],
                'party_open_type' => $data['party_open_type'],
                'party_open_bal' => $data['party_open_bal'],
                'party_acc_type' => $data['party_acc_type'],
                'party_b_name' => $data['party_b_name'],
                'party_acc_name' => $data['party_acc_name'],
                'party_acc_no' => $data['party_acc_no'],
                'party_ifsc' => $data['party_ifsc'],
                'party_upi' => $data['party_upi'],
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
