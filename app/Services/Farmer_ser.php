<?php

namespace App\Services;

use App\Models\Farmer;
use App\Models\Load;
use App\Models\Stock_in;
use App\Models\Farmer_cash; 
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

        $give_bal = 0;
        $get_bal  = 0;

       $give_bal = $data->where('open_type', 'give')->sum('open_bal');
       $get_bal  = $data->where('open_type', 'get')->sum('open_bal');

        $load = Load::whereNotNull('farmer_id')->get();

        $stock_in = Stock_in::whereNotNull('farm_id')->get();

        $merge = $load->merge($stock_in)->sortByDesc('created_at')->values();

        $transactions = Farmer_cash::all();

         $purchase_pending = $merge->sum('bill_amount') -  ($transactions->where('type', 'purchase')->sum('amount')) + $give_bal;
       

        $adv_pending = ($transactions->where('type', 'advance')->sum('amount'))  - ($transactions->where('type', 'advance_deduct')->sum('amount')) + $get_bal;

        $head_card = [
            'adv_card'     => $adv_pending,
            'balance_card' => $purchase_pending,
        ];


       // 2. Use map to iterate over each farmer and format the array
            $farmers = $data->map(function ($farmer) use ($adv_pending, $purchase_pending) {
                return [
                    'farm_id'      => $farmer->id,
                    'farm_en'      => $farmer->farm_en,
                    'farm_nick_en' => $farmer->farm_nick_en,
                    'location'     => $farmer->location,
                    'amount'       => 0,
                    'fav'          => $farmer->fav,
                    'adv_card'     => $adv_pending,
                    'balance_card' => $purchase_pending,
                ];
            });

        return $value =[
            'head_card' => $head_card,
            'farmers'   => $farmers,
        ];
    }

    // fucntion to get farmer profile details
    public static function farmer_profile(array $data){


        $farm_id = $data['farm_id'];

        $data = Farmer::select('id as farm_id','farm_en', 'farm_nick_en', 'location', 'ph_no','wp_no','fav','open_type','open_bal')
                ->where('id', $farm_id)
                ->first();

        $give_bal = 0;
        $get_bal  = 0;

        if ($data->open_type === 'give') {
            $give_bal = $data->open_bal;
        } elseif ($data->open_type === 'get') {
            $get_bal = $data->open_bal;
        }
    


        $load = Load::with(['load_data:id,load_seq'])->where('farmer_id', $farm_id)->get()->map(function($item){
           $item->table = 'e_load';
           return $item;
        });

        $stock_in = Stock_in::where('farm_id', $farm_id)->get()->map(function($item){
           $item->table = 'stock_in';
           return $item;
        });

        $transactions = Farmer_cash::where('farm_id', $farm_id)->get()->map(function($item){
           $item->table = 'farmer_cash';

           return $item;
        });

        // merge load and stock in
        $merge = $load->merge($stock_in)->sortByDesc('created_at')->values();
        $purchase_pending = $merge->sum('bill_amount') -  ($transactions->where('type', 'purchase')->sum('amount')) + $give_bal;
       

        $adv_pending = ($transactions->where('type', 'advance')->sum('amount'))  - ($transactions->where('type', 'advance_deduct')->sum('amount')) + $get_bal;

        $data->balance_card = $purchase_pending;
        $data->adv_card = $adv_pending;

        $resp =[
            'profile' => $data,
            'loads'   => $merge,
            'transactions' => $transactions,
        ];

       return $resp;
    }

    //function to get farmer advance pending

    public static function farmer_advance_pending(array $data){

        $farm_id = $data['farm_id'];

        $pending_advance = Farmer_cash::where('farm_id', $farm_id)->where('type', 'advance')->sum('amount') 
                         - Farmer_cash::where('farm_id', $farm_id)->where('type', 'advance_deduct')->sum('amount');

        // Logic to calculate pending advance can be added here

        return $pending_advance;

    }   

    // function to process farmer pay out
    public static function farmer_pay_out(array $data){

        // Logic to process pay out can be added here

        $farm_cash = Farmer_cash::create([
            'farm_id' => $data['farm_id'],
            'type'    => $data['type'],
            'amount'  => $data['amount'],
            'method'  => $data['pay_method'],
            'c_by'    => Auth::guard('tenant')->user()->id ?? null,
        ]);
        

       return $farm_cash;
    }

    // function to process farmer pay in
    public static function farmer_pay_in(array $data){
        // Logic to process pay in can be added here

        $farm_cash = Farmer_cash::create([
            'farm_id' => $data['farm_id'],
            'type'    => $data['type'],
            'amount'  => $data['amount'],
            'method'  => $data['pay_method'],
            'c_by'    => Auth::guard('tenant')->user()->id ?? null,
        ]);
        

       return $farm_cash;
    }
}
