<?php

namespace App\Services;

use App\Models\Prime_load;
use App\Models\Load;
use App\Models\Stock_in;  
use App\Models\Stock_out;  
use App\Models\Filter;
use App\Models\Shift;
use App\Models\Truck_capacity;
use App\Models\Summary;
use App\Models\M_invoice;
use App\Services\Farmer_ser;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class Load_ser
{
    public static function create_load(array $data)
    {

         $prime_load = Prime_load::find($data['prime_load'] ?? 0);

         if($prime_load) {
              // Fill the model with new data
            $prime_load->fill([
                'market' => $data['market'],
                'party_id' => $data['party_id'],
                // 'product_id' => $data['product_id'],
                'empty_weight' => $data['empty_weight'],
                'load_date' => $data['load_date'],
                'veh_no' => $data['veh_no'],
                'dr_no' => $data['dr_no'],
                'transporter' => $data['transporter'],
                'quality_price' => $data['quality_price'],    
                'filter_price' => $data['filter_price'],
                'req_qty' => $data['req_qty'],
                'truck_capacity' => $data['truck_capacity'],
                'team' => ($data['team']),
                // 'status' => $data['status'] ?? 'active',
                // 'c_by' => Auth::guard('tenant')->user()->id ?? null,
            ]);

                // Save only if there are changes
                if ($prime_load->isDirty()) {
                    $prime_load->save();
                }


        }else{

                 Prime_load::create(
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
                        'filter_price' => $data['filter_price'],
                        'req_qty' => $data['req_qty'],
                        'truck_capacity' => $data['truck_capacity'],
                        'team' => ($data['team']),
                        'status' => $data['status'] ?? 'active',
                        'c_by' => Auth::guard('tenant')->user()->id ?? null,
                    ]
                );
            }
    }

    public static function add_load_item(array $data)
    {
        $load_create =  Load::create(
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

        if($data['adv'] > 0){
            // update farmer advance

            $farmer = Farmer_ser::farmer_pay_in(['farm_id' => $data['farmer_id'],'amount' => $data['adv'], 'type' => 'advance_deduct','load_id' => $data['load_id']]);

        }

        return $load_create;
    }

    public static function get_load_list()
    {
        $query = Prime_load::with(['party_data:id,party_en', 'transporter:id,transport', 'truck_capacity:id,capacity']);
        
        if(Auth::guard('tenant')->user()->role != 'admin'){
            $query->WhereJsonContains('team', [Auth::guard('tenant')->user()->id]);
        }
        
        $query = $query->orderBy('id', 'desc')->get();

        // \Log::info('Load List Query: ', $query->toArray());

        $query->map(function($item){
            // $item->load_piece = 0; // Access the appended attribute to load team members

                   $load_data = Self::ind_load_list(['load_id'=>$item->id]);

            $item->load_piece = $load_data['summary']['card_billing_piece'] + $load_data['summary']['card_grace'];

             $item->team_members = $item->getTeamMembersAttribute();

            return $item;
        });

       $ongoing = $query->where('load_status','!=','inv_completed');

       $completed = $query->where('load_status','inv_completed');

        //  \Log::info('Final Load List: ', $query->toArray());

        return ['ongoing'=>$ongoing->values(),'completed'=>$completed->values()];
    }

    // function to get individual load list

    public static function ind_load_list(array $data)
    {
        $load_id = $data['load_id'];
    
        $query = Load::with(['farmer_data:id,farm_en,location', 'product_data:id,name_en','load_data:id,load_seq,veh_no,team','shift_load_data.load_data:id,load_seq', 'quality_details:id,quality'])->where('load_id', $load_id)->orderBy('id', 'desc')->get();

        $query->map(function($item){
            // $item->load_piece = 0; // Access the appended attribute to load team members

              $item->team_members = $item->load_data->getTeamMembersAttribute();
              $item->table_name = 'e_load';

              if($item->shift_id != null){
                $item->shift_load_seq = $item->shift_load_data ? $item->shift_load_data->load_data->load_seq : null;
              }
              else{
                $item->shift_load_seq = null;
              }


            // $item->card_billing_piece = $item->bill_piece;
            // $item->card_grace = $item->grace_piece;
            // $item->card_billing_amount = $item->bill_amount;            

               unset($item->shift_load_data);

            return $item;
        });

        $grouped = $query->groupBy(function ($item) {
            return ($item->cat === 'add' || $item->cat === 'stock') ? 'add' : 'load';
        });

        //  $get_load = $query->groupBy(function ($item) {
        //     return $item->shift_id != null ? 'shift_from' : 'direct_add';
        // });

       

        $shift = Shift::with(['load_data:id,load_seq', 'to_load:id,load_seq', 'party_data:id,party_en,party_location'])->where('load_id', $load_id)->get()
                ->map(function ($item) {
                        $item->table_name = 'e_shift';
                        return $item;
                    });

        $total_bill_piece  = ($query->sum('bill_piece'))-($shift->sum('bill_piece'));
        $total_grace       = ($query->sum('grace_piece'))-($shift->sum('grace_piece'));
        $total_bill_amount = ($query->sum('bill_amount'))-($shift->sum('bill_amount'));

         $shift_from = $grouped->get('load', collect());

        $finalShift = $shift_from->concat($shift);

        $load_data = Prime_load::with(['party_data:id,party_en,party_location'])->where('id', $load_id)->first();

        $load_data->load_summary =  Summary::where('load_id', $load_id)->count();

        $final_loss = M_invoice::where('load_id', $load_id)->value('final_loss');

        $load_data->load_invoice = is_null($final_loss) ? 0 : 1;


        $summary = [
            'card_billing_piece'  => $total_bill_piece,
            'card_grace'          => $total_grace,
            'card_billing_amount' => $total_bill_amount,
            ];

            $items =  [
                'load_data' => $load_data,
                'add'   => $grouped->get('add', collect()),
                'shift' => $finalShift,
                'summary' => $summary,
            ];

        return $items;
    }

    // funcition to get individual load details
    public static function ind_load_details(array $data)
    {
        $load_item_id = $data['load_item_id'];

        $type = $data['type'];

        if($type=='e_shift'){

            $query = Shift::with(['load_data:id,load_seq,team,party_id','to_load:id,load_seq','party_data:id,party_en'])->where('id', $load_item_id)->first();

            $query->team_members = $query->load_data->getTeamMembersAttribute();

        }else{
            $query = Load::with(['farmer_data:id,farm_en,location','load_data:id,load_seq,team,party_id','load_data.party_data:id,party_en'])->where('id', $load_item_id)->first();

            $query->team_members = $query->load_data->getTeamMembersAttribute();

        }
    

        return $query;
    }
   
    // fucntion to add stock in entry

    public static function add_purchase(array $data)
    {
        $purchase =  Stock_in::create(
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

        if($data['adv'] > 0){
            // update farmer advance

            $farmer = Farmer_ser::farmer_pay_in(['farm_id' => $data['farmer_id'],'amount' => $data['adv'], 'type' => 'advance_deduct','load_id' => $data['load_id']]);

        }

        return $purchase;
    }

    // function to add stock out entry

    public static function add_sales(array $data)
    {
        return Stock_out::create(
            [
                'cat' => $data['cat'],
                'load_id' => $data['load_id'],
                'farm_id' => $data['party_id'],
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
                'emp_id' => Auth::guard('tenant')->user()->id ?? null,
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

    // function to store shift load item


    public static function add_shift_item(array $data)
    {
        $cat = $data['cat'];

        if($cat=='load'){

            $shift =  Shift::create(
                [
                    'cat'=>$cat,
                    'load_id' => $data['load_id'],
                    'to_load' => $data['to_load'],
                    'product_id' => $data['product_id'],
                    'total_piece' => $data['total_piece'],
                    'grace_piece' => $data['grace_piece'],
                    'grace_per' => $data['grace_per'],
                    'bill_piece' => $data['bill_piece'],
                    'price' => $data['price'],
                    'bill_amount' => $data['bill_amount'],
                    'status' => $data['status'] ?? 'active',
                    'c_by' => Auth::guard('tenant')->user()->id ?? null,
                ]
            );

            $add_load = Load::create(
                [
                    'cat'=>$cat,
                    'load_id' => $data['to_load'],
                    'shift_id' => $shift->id,
                    'product_id' => $data['product_id'],
                    'total_piece' => $data['total_piece'],
                    'grace_piece' => $data['grace_piece'],
                    'grace_per' => $data['grace_per'],
                    'bill_piece' => $data['bill_piece'],
                    'price' => $data['price'],
                    'bill_amount' => $data['bill_amount'],
                    'total_amt' => $data['bill_amount'],
                    'status' => $data['status'] ?? 'active',
                    'c_by' => Auth::guard('tenant')->user()->id ?? null,
                ]
            );
        }

        elseif($cat=='others'){

            $shift =  Shift::create(
                [
                    'cat'=>$cat,
                    'load_id' => $data['load_id'],
                    'party_id' => $data['party_id'],
                    'product_id' => $data['product_id'],
                    'total_piece' => $data['total_piece'],
                    'grace_piece' => $data['grace_piece'],
                    'grace_per' => $data['grace_per'],
                    'bill_piece' => $data['bill_piece'],
                    'price' => $data['price'],
                    'bill_amount' => $data['bill_amount'],
                    'status' => $data['status'] ?? 'active',
                    'c_by' => Auth::guard('tenant')->user()->id ?? null,
                ]
            );

        }else{

            $shift = Shift::create(
                [
                    'cat'=>'stock',
                    'load_id' => $data['load_id'],
                    'product_id' => $data['product_id'],
                    'total_piece' => $data['total_piece'],
                    'grace_piece' => $data['grace_piece'],
                    'grace_per' => $data['grace_per'],
                    'bill_piece' => $data['bill_piece'],
                    'price' => $data['price'],
                    'bill_amount' => $data['bill_amount'],
                    'status' => $data['status'] ?? 'active',
                    'c_by' => Auth::guard('tenant')->user()->id ?? null,
                ]
            );

            $add_load = Stock_in::create(
                [
                    'cat'=>'load',
                    'load_id' => $data['load_id'],
                    'product_id' => $data['product_id'],
                    'total_piece' => $data['total_piece'],
                    'grace_piece' => $data['grace_piece'],
                    'grace_per' => $data['grace_per'],
                    'bill_piece' => $data['bill_piece'],
                    'price' => $data['price'],
                    'bill_amount' => $data['bill_amount'],
                    'total_amt' => $data['bill_amount'],
                    'status' => $data['status'] ?? 'active',
                    'c_by' => Auth::guard('tenant')->user()->id ?? null,
                ]
            );
        }
    }

    // function to get load self list

    public static function load_self_list(array $data)
    {
    
        $query = Prime_load::find($data['load_id']);

        $load_list = Prime_load::where('product_id', $query->product_id)->where('id', '!=', $data['load_id'])->select('id','load_seq')->get();

        return $load_list;
    }

    // function to stock shift

    public static function stock_shift(array $data)
    {

       $load =  Prime_load::where('id',$data['load_id'])->first();

        $stock_create = Stock_out::create(
            [
                'cat'=>'load',
                'load_id' => $data['load_id'],
                'product_id' => $load->product_id,
                'total_piece' => $data['total_piece'],
                'grace_piece' => $data['grace_piece'],
                'grace_per' => $data['grace_per'],
                'bill_piece' => $data['bill_piece'],
                'price' => $data['price'],
                'bill_amount' => $data['bill_amount'],
                'total_amt' => $data['bill_amount'],
                'status' => $data['status'] ?? 'active',
                'c_by' => Auth::guard('tenant')->user()->id ?? null,
            ]
        );

        $load_create = Load::create(
            [
                'cat'=>'stock',
                'load_id' => $data['load_id'],
                'product_id' => $load->product_id,
                'total_piece' => $data['total_piece'],
                'grace_piece' => $data['grace_piece'],
                'grace_per' => $data['grace_per'],
                'bill_piece' => $data['bill_piece'],
                'price' => $data['price'],
                'bill_amount' => $data['bill_amount'],
                'total_amt' => $data['bill_amount'],
                'status' => $data['status'] ?? 'active',
                'c_by' => Auth::guard('tenant')->user()->id ?? null,
            ]
        );

        return ['stock'=>$stock_create,'load'=> $load_create];
    }   

    // function to fetch edit load data

    public static function edit_load_fetch(array $data)
    {
        $load_id  = $data['load_id'];

        if(!$load_id){
            throw new \Exception('Load ID is required');
        }

        $load_data = Prime_load::with(['party_data:id,party_en,party_location', 'transporter:id,transport', 'truck_capacity:id,capacity'])->where('id', $load_id)->first();

        $load_data->tk = $load_data->truck_capacity;

        // $load_t_cap = Truck_capacity::find($load_data->truck_capacity);

          // Remove original key from response
            // unset($load_data->truck_capacity);


        return $load_data;
    }

    // function to edit load item

    public static function edit_load_item(array $data)
    {
        $load_item = Load::find($data['load_item_id']);

        if (!$load_item) {
            throw new \Exception('Load item not found');
        }

        $load_item->total_piece = $data['total_piece'];
        $load_item->grace_piece = $data['grace_piece'];
        $load_item->grace_per = $data['grace_per'];
        $load_item->bill_piece = $data['bill_piece'];
        $load_item->price = $data['price'];
        $load_item->commission = $data['commission'];
        $load_item->bill_amount = $data['bill_amount'];
        $load_item->adv = $data['adv'];
        $load_item->quality = $data['quality'];
        $load_item->total_amt = $data['total_amt'];
        $load_item->save();

        return $load_item;
    }

    // function to fetch edit load item data

    public static function edit_load_item_fetch(array $data)
    {
        $load_item_id  = $data['load_item_id'];

        if(!$load_item_id){
            throw new \Exception('Load Item ID is required');
        }

        $load_item_data = Load::with(['farmer_data:id,farm_en,location', 'product_data:id,name_en'])->where('id', $load_item_id)->first();

        return $load_item_data;
    }
}