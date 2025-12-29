<?php

namespace App\Services;

use App\Models\Party;
use App\Models\Prime_load;
use App\Models\E_invoice;
use App\Models\Stock_out;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Party_cash;

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
        $data =  Party::where('status','active')->orderBy('fav','DESC')->get()->map(function($item){

             $give_bal = 0;
             $get_bal  = 0;

            $party_cash_ind = Party_cash::where('party_id', $item->id)->select('id','type','amount','method','created_at')->get();

            $party_load_ind = Prime_load::where('party_id', $item->id)->pluck('id');

            $inv_data_ind = E_invoice::whereIn('load_id', $party_load_ind)->select('id','bill_amt')->get();

            $inv_amount_ind = $inv_data_ind->sum('bill_amt');

            $party_sales_ind = Stock_out::where('cat','sales')->where('farm_id', $item->id)->sum('bill_amount');

            $in_cash_ind = $party_cash_ind->where('type','pay_in')->sum('amount');
            $out_cash_ind = $party_cash_ind->where('type','pay_out')->sum('amount');
            $pt_bal = ($inv_amount_ind + $party_sales_ind +  $out_cash_ind) - $in_cash_ind;


             if ($data->open_type === 'give') {
                $give_bal = $data->open_bal;
                $pt_bal = $pt_bal - $give_bal;
            } elseif ($data->open_type === 'get') {
                $get_bal = $data->open_bal;
                $pt_bal = $pt_bal + $get_bal;
            }


            $item->party_bal = $pt_bal;
            

            return $item;

        });

        // dd($data->toArray());

        
        $party_cash = Party_cash::select('id','type','amount','method','created_at')->get();

        $total_party = Party::where('status','active')->count();

        $party_load = Prime_load::pluck('id');

        $inv_data = E_invoice::select('id','bill_amt')->get();

        $inv_amount = $inv_data->sum('bill_amt');

        $party_sales = Stock_out::where('cat','sales')->sum('bill_amount');

        $in_cash = $party_cash->where('type','pay_in')->sum('amount');
        $out_cash = $party_cash->where('type','pay_out')->sum('amount');

        $party_give_get = Party::where('status','active')->get();

        $give_total = $party_give_get->where('open_type', 'give')->sum('open_bal');
        $get_total  = $party_give_get->where('open_type', 'get')->sum('open_bal');


        $bal = ($inv_amount + $party_sales +  $out_cash) - $in_cash + ($get_total - $give_total);

       



        $party_card = [
            'balance'=> $bal,
            'total'=>$total_party
        ];
        // dd($data);

        $party = $data->map(function ($party) {
                return [
                    'party_id'      => $party->id ?? null,
                    'party_en'      => $party->party_en ?? null,
                    'party_nick_en' => $party->party_nick_en ?? null,
                    'party_location'  => $party->party_location ?? null,
                    'phone'         => $party->party_ph_no ?? null,
                    'amount'       => $party->party_bal ?? null,
                    'fav'          => $party->fav ?? null,
                ];
            });

        return ['party'=>$party,'head_card'=>$party_card];

    }

    // function to get party profile details

    public static function party_profile(array $data){

        $party_id = $data['party_id'];

        $data = Party::select('id as party_id','party_en', 'party_nick_en', 'party_location', 'party_ph_no','party_wp_no','fav','open_type','open_bal')
                ->where('id', $party_id)
                ->first();

        $give_bal = 0;
        $get_bal  = 0;



        $party_cash = Party_cash::where('party_id', $party_id)->select('id','type','amount','method','created_at')->get();

        $party_load = Prime_load::where('party_id', $party_id)->pluck('id');

        $inv_data = E_invoice::whereIn('load_id', $party_load)->select('id','bill_amt')->get();

        $inv_amount = $inv_data->sum('bill_amt');

        $party_sales = Stock_out::where('cat','sales')->where('farm_id', $party_id)->sum('bill_amount');

        $in_cash = $party_cash->where('type','pay_in')->sum('amount');
        $out_cash = $party_cash->where('type','pay_out')->sum('amount');

        $bal = ($inv_amount + $party_sales +  $out_cash) - $in_cash;

        if ($data->open_type === 'give') {
            $give_bal = $data->open_bal;
            $bal = $bal - $give_bal;
        } elseif ($data->open_type === 'get') {
            $get_bal = $data->open_bal;
            $bal = $bal + $get_bal;
        }

        
        // $data->amount = 0;
        $data->balance = $bal;
       return ['data' => $data,'party_cash' => $party_cash];
    }

    // function to party pay In

    public static function party_pay_in(array $data){

        $party = Party::findOrFail($data['party_id']);

        if(! $party) {
            throw new \Exception('Party not found');
        }
       
        $party_cash =  Party_cash::create([
            'party_id' => $data['party_id'],
            'type'     => 'pay_in',
            'amount'   => $data['amount'],
            'method'   => $data['method'],
            'c_by'     => Auth::guard('tenant')->user()->id ?? null,
        ]);
        
        return $party_cash;

    }

    // funtion to party pay Out

    public static function party_pay_out(array $data){

        $party = Party::findOrFail($data['party_id']);

        if(! $party) {
            throw new \Exception('Party not found');
        }
       
        $party_cash =  Party_cash::create([
            'party_id' => $data['party_id'],
            'type'     => 'pay_out',
            'amount'   => $data['amount'],
            'method'   => $data['method'],
            'c_by'     => Auth::guard('tenant')->user()->id ?? null,
        ]);
        
        return $party_cash;

    }
}
