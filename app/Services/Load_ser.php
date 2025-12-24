<?php

namespace App\Services;

use App\Models\Prime_load;
use App\Models\Load;
use App\Models\Stock_in;  
use App\Models\Stock_out;  
use App\Models\Filter;
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
                'product_id' => $data['product_id'],
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

    public static function get_load_list()
    {
        $query = Prime_load::with(['party_data:id,party_en', 'transporter:id,transport', 'truck_capacity:id,capacity'])->orderBy('id', 'desc')->get();

        $query->map(function($item){
            $item->load_piece = 0; // Access the appended attribute to load team members

             $item->team_members = $item->getTeamMembersAttribute();

            return $item;
        });

        return $query;
    }

    // function to get individual load list

    public static function ind_load_list(array $data)
    {
        $load_id = $data['load_id'];
    
        $query = Load::with(['farmer_data:id,farm_en', 'product_data:id,name_en','load_data:id,load_seq,team'])->where('load_id', $load_id)->orderBy('id', 'desc')->get();

        $query->map(function($item){
            // $item->load_piece = 0; // Access the appended attribute to load team members

              $item->team_members = $item->load_data->getTeamMembersAttribute();

            // $item->card_billing_piece = $item->bill_piece;
            // $item->card_grace = $item->grace_piece;
            // $item->card_billing_amount = $item->bill_amount;            

            //   unset()

            return $item;
        });

        $total_bill_piece  = $query->sum('bill_piece');
        $total_grace       = $query->sum('grace_piece');
        $total_bill_amount = $query->sum('bill_amount');

        $summary = [
            'card_billing_piece'  => $total_bill_piece,
            'card_grace'          => $total_grace,
            'card_billing_amount' => $total_bill_amount,
            ];

        return [
            'items'   => $query,
            'summary' => $summary
        ];
    }

    // funcition to get individual load details
    public static function ind_load_details(array $data)
    {
        $load_item_id = $data['load_item_id'];
    
        $query = Load::with(['farmer_data:id,farm_en','load_data:id,load_seq,team,party_id','load_data.party_data:id,party_en'])->where('id', $load_item_id)->first();

        return $query;
    }
   
    // fucntion to add stock in entry

    public static function add_purchase(array $data)
    {
        return Stock_in::create(
            [
                'cat' => $data['cat'],
                'load_id' => $data['load_id'],
                'farm_id' => $data['farmer_id'],
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

    // function to add stock out entry

    public static function add_sales(array $data)
    {
        return Stock_out::create(
            [
                'cat' => $data['cat'],
                'load_id' => $data['load_id'],
                'farm_id' => $data['farmer_id'],
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

    // logic to add filter data

    public static function add_filter(array $data)
    {
        return Filter::create(
            [
                'load_id' => $data['load_id'],
                'emp_id' => $data['emp_id'],
                'total' => $data['total'],
                'status' => $data['status'] ?? 'active',
                'c_by' => Auth::guard('tenant')->user()->id ?? null,
            ]
        );
    }

    // function to get filter list

    public static function get_filter_list(array $data)
    {
        $load_id = $data['load_id'];
    
        $query = Filter::with(['emp_data:id,name'])->where('load_id', $load_id)->orderBy('id', 'desc')->get();

        return $query;
    }

    // function to edit filter data

    public static function edit_filter(array $data)
    {
        $filter = Filter::find($data['filter_id']);

        if (!$filter) {
            throw new \Exception('Filter data not found');
        }

        $filter->total = $data['total'];
        $filter->save();

        return $filter;
    }
}