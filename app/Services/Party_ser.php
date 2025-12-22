<?php

namespace App\Services;

use App\Models\Party;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class Party_ser
{
    public static function create_party(array $data)
    {

           $party = Party::find($data['party_id'] ?? 0);

           if($party) {
                // Fill the model with new data
            $party->fill([
                'party_en' => $data['party_en'],
                'party_nick_en' => $data['party_nick_en'],
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
            ]);

            // Save only if there are changes
            if ($party->isDirty()) {
                $party->save();
            }

           
           }else{

            $party = Party::create([
                'party_en' => $data['party_en'],
                'party_kn' => $data['party_kn'] ?? null,
                'party_nick_en' => $data['party_nick_en'],
                'party_nick_kn' => $data['party_nick_kn'] ?? null,
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
            ]);
           }

           return $party;

    }

    public static function get_party_details($party_id)
    {
        return Party::findOrFail($party_id);
    }

    // fetch party list

    public static function get_all_party()
    {
        $data =  Party::where('status','active')->orderBy('fav','DESC')->get();

        // dd($data);

        $party = $data->map(function ($party) {
                return [
                    'party_id'      => $party->id,
                    'party_en'      => $party->party_en,
                    'party_nick_en' => $party->party_nick_en,
                    'party_location'  => $party->party_location,
                    'phone'         => $party->party_ph_no,
                    'amount'       => 0,
                    'fav'          => $party->fav,
                ];
            });

        return $party;

    }
}
