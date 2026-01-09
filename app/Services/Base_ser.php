<?php

namespace App\Services;

use App\Models\Quality;
use App\Models\Transport;
use App\Models\Truck_capacity;
use App\Models\Loss;
use App\Models\Coconut;
use App\Models\User;
use App\Services\Farmer_ser;
use App\Models\Farmer_cash;
use App\Models\Petty_cash;
use App\Services\Party_ser;
use App\Models\Prime_load;
use App\Models\Filter;
use App\Models\Expense_cat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class Base_ser
{
    public static function create_quality(array $data)
    {

        // return Quality::updateOrCreate(
        //     ['id' => $data['quality_id'] ?? null],
        //     [
        //         'quality' => $data['quality'],
        //         'status' => $data['status'] ?? 'active',
        //         'c_by' => $data['c_by'],
        //     ]
        // );

        if (isset($data['quality_id'])) {
            // UPDATE: only status
            if (isset($data['status'])) {
                return Quality::where('id', $data['quality_id'])->update([
                    'status' => $data['status'] ?? 'active',
                ]);
            } else {
                return Quality::where('id', $data['quality_id'])->update([
                    'quality' => $data['quality'],
                ]);
            }

        } else {
            // CREATE: full insert
            Log::info("Creating quality", ['data' => $data]);
            return Quality::create([
                'quality' => $data['quality'],
                'status' => $data['status'] ?? 'active',
                'c_by' => Auth::guard('tenant')->user()->id ?? null,
            ]);
        }
    }

    public static function create_transport(array $data)
    {
        if (isset($data['transport_id'])) {
            // UPDATE: only status
            if (isset($data['status'])) {
                return Transport::where('id', $data['transport_id'])->update([
                    'status' => $data['status'] ?? 'active',
                ]);
            } else {
                return Transport::where('id', $data['transport_id'])->update([
                    'transport' => $data['transport'],
                    'phone' => $data['phone'],
                ]);
            }

        } else {
            // CREATE: full insert
            return Transport::create([
                'transport' => $data['transport'],
                'phone' => $data['phone'],
                'status' => $data['status'] ?? 'active',
                'c_by' => Auth::guard('tenant')->user()->id ?? null,
            ]
            );
        }
    }

    // Add other common service methods here

    public static function create_truck(array $data)
    {
        if (isset($data['truck_id'])) {
            // UPDATE: only status
            if (isset($data['status'])) {
                return Truck_capacity::where('id', $data['truck_id'])->update([
                    'status' => $data['status'] ?? 'active',
                ]);
            } else {
                return Truck_capacity::where('id', $data['truck_id'])->update([
                    'capacity' => $data['capacity'],
                    'charge' => $data['charge'],
                ]);
            }

        } else {
            // CREATE: full insert
            return Truck_capacity::create([
                'capacity' => $data['capacity'],
                'charge' => $data['charge'],
                'status' => $data['status'] ?? 'active',
                'c_by' => Auth::guard('tenant')->user()->id ?? null,
            ]);
        }

    }

    // add function for expense category

    public static function create_expense_cat(array $data)
    {
        if (isset($data['expense_id'])) {
            // UPDATE: only status
            if (isset($data['status'])) {
                return Expense_cat::where('id', $data['expense_id'])->update([            
                    'status' => $data['status'] ?? 'active',
                ]);
            } else {
                return Expense_cat::where('id', $data['expense_id'])->update([
                    'cat' => $data['cat'],
                ]);
            }
        } else {
            // CREATE: full insert
            return Expense_cat::create([
                'cat' => $data['cat'],
                'status' => $data['status'] ?? 'active',
                'c_by' => Auth::guard('tenant')->user()->id ?? null,
            ]);
        }
    }

    // function to create loss category

    public static function create_loss(array $data)
    {
        if (isset($data['loss_id'])) {
            // UPDATE: only status
            if (isset($data['status'])) {
                return Loss::where('id', $data['loss_id'])->update([            
                    'status' => $data['status'] ?? 'active',
                ]);
            } else {
                return Loss::where('id', $data['loss_id'])->update([
                    'loss' => $data['loss'],
                ]);
            }
        } else {
            // CREATE: full insert
            return Loss::create([
                'loss' => $data['loss'],
                'status' => $data['status'] ?? 'active',
                'c_by' => Auth::guard('tenant')->user()->id ?? null,
            ]);
        }
    }

     // fetch list of qulaities, transports, trucks
        public static function get_common_list(array $data)
        {
            switch ($data['type']) {
                case 'quality':
                    if($data['status']=='all'){
                        return Quality::all();
                    }else{
                        return Quality::where('status', $data['status'])->get();
                    }
                case 'transport':
                    if($data['status']=='all'){
                        return Transport::all();
                    }else{
                        return Transport::where('status', $data['status'])->get();
                    }
                case 'truck':
                    if($data['status']=='all'){
                        return Truck_capacity::all();
                    }else{
                        return Truck_capacity::where('status', $data['status'])->get();
                    }
                case 'loss':
                    if($data['status']=='all'){
                        return Loss::all();
                    }else{
                        return Loss::where('status', $data['status'])->get();
                    }

                case 'expense':
                    if($data['status']=='all'){
                        return Expense_cat::all();
                    }else{
                        return Expense_cat::where('status', $data['status'])->get();
                    }
                default:
                    return null;
                   
                    // throw new \InvalidArgumentException("Invalid type: $type");
            }
        }

        // function to edit common entries

        public static function edit_common_list(array $data)
        {
            // similar to create_common but only updates

            switch ($data['type']) {
                case 'quality':
                    return Quality::where('id', $data['id'])->first();
                case 'transport':
                    return Transport::where('id', $data['id'])->first();
                case 'truck':
                    return Truck_capacity::where('id', $data['id'])->first();
                case 'loss':
                    return Loss::where('id', $data['id'])->first();
                case 'expense':
                    return Expense_cat::where('id', $data['id'])->first();
                default:
                    return null;
                    // throw new \InvalidArgumentException("Invalid type: $type");
            }
        }

        // function to add coconut availability 

        public static function add_coconut(array $data)
        {
            return Coconut::create([
                'farm_id' => $data['farm_id'],
                'coconut' => $data['coconut'],
                'c_by'    => Auth::guard('tenant')->user()->id ?? null,
            ]);
        }

    // funciton to get coconut availability

    public static function get_coconut_emp(array $data)
    {
        $today = date('Y-m-d');

        $coconut =  Coconut::with(['farmer_data:id,farm_en,location','emp_data:id,name'])->where('c_by', $data['emp_id'])->whereDate('created_at', $today)->get();

        $coconut_sum = $coconut->sum('coconut');

        $farm_group = $coconut->groupBy('farm_id')->map(function ($item) {
            return [
                'farm_en'  => $item->first()->farmer_data->farm_en,
                'location' => $item->first()->farmer_data->location,
                'total_coconut' => $item->sum('coconut'),
            ];
        })->values();

        return [
            'coconut_sum'  => $coconut_sum,
            'farm_group'   => $farm_group,
        ];
    }

    // function to get coconut availability

    public static function get_coconut_list(array $data)
    {
        $today = date('Y-m-d');

        $coconut =  Coconut::with(['emp_data:id,name,location'])->whereDate('created_at', $today)->get();

        $coconut_sum = $coconut->sum('coconut');

        $emp_group = $coconut->groupBy('c_by')->map(function ($item) {
           $emp = $item->first()->emp_data;

                return [
                    'emp_name'       => $emp->name ?? null,
                    'location'       => $emp->location ?? null,
                    'farmer_count'   => $item->groupBy('farm_id')->count(),
                    'total_coconut'  => $item->sum('coconut'),
                ];
        })->values();


         return [
            'coconut_sum'  => $coconut_sum,
            'emp_group'   => $emp_group,
        ];
    }


    // finction to get dashboard data

    public static function dashboard_data(array $data)
    {

        $dash_farmer = Farmer_ser::get_all_farmers();

        $farmer_card = $dash_farmer['head_card'];

        $dash_party = Party_ser::get_all_party();

        $party_card = $dash_party['head_card'];



        $prime_load = Prime_load::with(['party_data:id,party_en,party_location','load_list:id,load_id,bill_piece,grace_piece,total_piece,farmer_id',
                                          'shift_list:id,load_id,bill_piece,grace_piece,total_piece',])->where('status', 'active');

        if(Auth::guard('tenant')->user()->role != 'admin'){

            $prime_load->WhereJsonContains('team', [Auth::guard('tenant')->user()->id]);
        }
                                          
                                          
           $prime_load = $prime_load->orderBy('id', 'desc')->get()->map(function($item){

            $item->filter = Filter::where('load_id', $item->id)->sum('total');

           $add_piece   = (optional($item->load_list)->sum('total_piece')) ?? 0;
           $shift_piece = (optional($item->shift_list)->sum('total_piece')) ?? 0;

            $item->loaded = $add_piece - $shift_piece;
            $item->remain  =$item->bill_piece - $item->loaded;

            

            $item->last_farmer = $item->load_list
                                ?->sortByDesc('id')
                                ->take(2)
                                ->map(function ($load) {
                                    return $load->farmer_data->farm_en ?? null;
                                });

            unset($item->load_list, $item->shift_list);

            return $item;
        });

        if(Auth::guard('tenant')->user()->role != 'admin'){

            $petty_cash = 0;
        }else{
            $petty_cash_sum = Petty_cash::where('emp_id', Auth::guard('tenant')->user()->id)->sum('amount');

            $farmer_cash = Farmer_cash::where('c_by', Auth::guard('tenant')->user()->id)->sum('amount');

            $petty_cash = $petty_cash_sum - $farmer_cash;
        }


         return [
            'farmer_card' => $farmer_card,
            'party_card' => $party_card,
            'load_data' => $prime_load,
            'petty_cash' => $petty_cash ?? 0,
        ];

      
    }

    // function to get the employee details

    public static function get_employee_details(array $data)
    {
        $user = User::where('id', $data['user_id'])->first();

        $user_paid_list = Farmer_cash::with(['farm_data:id,farm_en,location'])->where('c_by', $data['user_id'])->orderBy('id','desc')->limit(100)->get();

        $petty_cash = Petty_cash::where('emp_id', $data['user_id'])->sum('amount');


        $user_spent = $user_paid_list->sum('amount');
        


        if(!$user) {
            throw new \Exception("User not found");
        }

        return ['user' => $user, 'user_paid' => $user_paid_list,'cash_given' => $petty_cash, 'cash_spent' => $user_spent];
    }
}
