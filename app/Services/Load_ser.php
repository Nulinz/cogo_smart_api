<?php

namespace App\Services;

use App\Models\Prime_load;
use App\Models\Load;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class Load_ser
{
    public static function create_load(array $data)
    {

        return Prime_load::create(
           
            [
                // 'farm_seq' => 1,
                'market' => $data['market'],
                'party_id' => $data['party_id'],
                'empty_weight' => $data['empty_weight'],
                'load_date' => $data['load_date'],
                'veh_no' => $data['veh_no'],
                'dr_no' => $data['dr_no'],
                'transporter' => $data['transporter'],
                'quality_price' => $data['quality_price'],
                'fliter_price' => $data['fliter_price'],
                'req_qty' => $data['req_qty'],
                'truck_capacity' => $data['truck_capacity'],
                'team' => ($data['team']),
                'status' => $data['status'] ?? 'active',
                'c_by' => Auth::guard('tenant')->user()->id ?? null,
            ]
        );
    }

    public static function add_load_item(array $data)
    {
        return Load::create(
            [
                'cat'=>'add',
                'load_id' => $data['load_id'],
                'farmer_id' => $data['farmer_id'],
                'product_id' => $data['product_id'],
                'total_piece' => $data['total_piece'],
                'grace_piece' => $data['grace_piece'],
                'grace_per' => $data['grace_per'],
                'bill_piece' => $data['bill_piece'],
                'price' => $data['price'],
                'commission' => $data['commission'],    
                'bill_amount' => $data['bill_amount'],
                'adv' => $data['adv'],
                'quality' => $data['quality'],
                'total_amt' => $data['total_amt'],
                'status' => $data['status'] ?? 'active',
                'c_by' => Auth::guard('tenant')->user()->id ?? null,
            ]
        );
    }

   
}
