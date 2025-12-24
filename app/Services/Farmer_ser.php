<?php

namespace App\Services;

use App\Models\Farmer;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class Farmer_ser
{
    public static function create_farm(array $data)
    {

        // return Farmer::updateOrCreate(
        //     ['id' => $data['farm_id'] ?? null],
        //     [
        //         // 'farm_seq' => 1,
        //         'farm_en' => $data['farm_en'],
        //         'farm_kn' => $data['farm_kn'] ?? null,
        //         'farm_nick_en' => $data['farm_nick_en'],
        //         'farm_nick_kn' => $data['farm_nick_kn'] ?? null,
        //         'location' => $data['location'],
        //         'ph_no' => $data['ph_no'],
        //         'wp_no' => $data['wp_no'],
        //         'open_type' => $data['open_type'],
        //         'open_bal' => $data['open_bal'],
        //         'acc_type' => $data['acc_type'],
        //         'b_name' => $data['b_name'],
        //         'acc_name' => $data['acc_name'],
        //         'acc_no' => $data['acc_no'],
        //         'ifsc' => $data['ifsc'],
        //         'upi' => $data['upi'],
        //         'c_by' => Auth::guard('tenant')->user()->id ?? null,
        //     ]
        // );

        $farmer = Farmer::find($data['farm_id'] ?? 0);
        if ($farmer) {
                // Fill the model with new data
            $farmer->fill([
                'farm_en' => $data['farm_en'],
                'farm_kn' => $data['farm_kn'] ?? null,
                'farm_nick_en' => $data['farm_nick_en'],
                'farm_nick_kn' => $data['farm_nick_kn'] ?? null,
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
            ]);

            // Save only if there are changes
            if ($farmer->isDirty()) {
                $farmer->save();
            }

        } else {
            // Create new record
            $farmer = Farmer::create([
                'farm_en' => $data['farm_en'],
                'farm_kn' => $data['farm_kn'] ?? null,
                'farm_nick_en' => $data['farm_nick_en'],
                'farm_nick_kn' => $data['farm_nick_kn'] ?? null,
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
            ]);
        }

        return $farmer;

    }

    public static function get_farmer_details($farm_id)
    {
        return Farmer::findOrFail($farm_id);
    }

    public static function get_all_farmers()
    {
        $data = Farmer::where('status', 'active')->orderBy('fav','DESC')->get();

       // 2. Use map to iterate over each farmer and format the array
            $farmers = $data->map(function ($farmer) {
                return [
                    'farm_id'      => $farmer->id,
                    'farm_en'      => $farmer->farm_en,
                    'farm_nick_en' => $farmer->farm_nick_en,
                    'location'     => $farmer->location,
                    'amount'       => 0,
                    'fav'          => $farmer->fav,
                ];
            });

        return $farmers;
    }

    // fucntion to get farmer profile details
    public static function farmer_profile(array $data){


        $farm_id = $data['farm_id'];

        $data = Farmer::select('id as farm_id','farm_en', 'farm_nick_en', 'location', 'ph_no','wp_no','fav')
                ->where('id', $farm_id)
                ->first();

        $data->amount = 0;
        $data->balance =0;

       return $data;
    }

    //function to get farmer advance pending

    public static function farmer_advance_pending(array $data){

        $farm_id = $data['farm_id'];

        $pending_advance = 0;

        // Logic to calculate pending advance can be added here

        return $pending_advance;

        // return [
        //     'farm_id' => $farm_id,
        //     'pending_advance' => $pending_advance,
        // ];
    }   
}
