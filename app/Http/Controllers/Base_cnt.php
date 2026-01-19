<?php

namespace App\Http\Controllers;

use App\Services\Base_ser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use App\Models\Bank;
use Illuminate\Support\Facades\Auth;

class Base_cnt extends Controller
{
    // Common controller methods can be added here

    public function create_common(Request $request)
    {
        // Logic to create quality

        // Log::info("message", ['request' => $request->all()]);
      

            if( $request->filled('transport_id') || $request->filled('truck_id') || $request->filled('quality_id') || $request->filled('loss_id') || $request->filled('expense_id') ){

                $rulesMap = [
                        'quality' => [
                            'quality_id' => 'required|string',
                        ],

                        'transport' => [
                            'transport_id' => 'required|string',
                        ],

                        'truck' => [
                            'truck_id' => 'required|string',
                        ],
                        'loss' => [
                            'loss_id' => 'required|string',
                        ],
                        'expense' => [
                            'expense_id' => 'required|string',
                        ],
                    ];
            }else{
                $rulesMap = [
                    'quality' => [
                        'quality' => 'required|string',
                    ],

                    'transport' => [
                        'transport' => 'required|string',
                        'phone'     => 'required|string',
                    ],

                    'truck' => [
                        'capacity' => 'required|string',
                        'charge'   => 'required|string',
                    ],
                    'loss' => [
                        'loss' => 'required|string',
                    ],
                    'expense' => [
                        'cat' => 'required|string',
                    ],
                ];
            }
        


        $rules = $rulesMap[$request->type] ?? null;

        if (! $rules) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid type provided',
            ], 400);
        }

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            if ($request->type == 'transport') {
                $quality = Base_ser::create_transport($request->all());
            } elseif ($request->type == 'truck') {
                $quality = Base_ser::create_truck($request->all());
            } elseif ($request->type == 'loss') {
                $quality = Base_ser::create_loss($request->all());
            }else if( $request->type == 'expense') {
                $quality = Base_ser::create_expense_cat($request->all());
            }
            else {

                $quality = Base_ser::create_quality($request->all());
            }

            return response()->json([
                'success' => true,
                'message' => $request->type.' created/updated successfully',
                'data' => $quality,
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $request->type.' created/updated failed: '.$e->getMessage(),
            ], 500);
        }

    }

    // function to fetch list of qualities, transports, trucks

    public function get_common_list(Request $request)
    {
        $rules = [
            'type' => 'required|string|in:quality,transport,truck,loss,expense',
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $list = Base_ser::get_common_list($request->all());

            return response()->json([
                'success' => true,
                'data' => $list,
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch '.$request->type.' list: '.$e->getMessage(),
            ], 500);
        }
    }

    // function for edit common can be added here

    public function edit_common(Request $request)
    {
        // Logic to edit quality, transport, truck can be added here
    $rules = [
            'type' => 'required|string|in:quality,transport,truck,loss,expense',
            'id' => 'required|string',
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $list = Base_ser::edit_common_list($request->all());

            return response()->json([
                'success' => true,
                'data' => $list,
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch '.$request->type.' list: '.$e->getMessage(),
            ], 500);
        }
    }

    // function to add coconut availability

    public function add_coconut(Request $request)
    {
        $rules = [
            'farm_id'  => 'required|string',
            'coconut'  => 'required|string',
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $coconut = Base_ser::add_coconut($request->all());

            return response()->json([
                'success' => true,
                'message' => 'Coconut availability added successfully',
                'data' => $coconut,
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to add coconut availability: '.$e->getMessage(),
            ], 500);
        }
    }

    // function for get coconut availability

    public function get_coconut_emp(Request $request)
    {
        $rules = [
            'emp_id'  => 'required|string',
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $coconut = Base_ser::get_coconut_emp($request->all());

            return response()->json([
                'success' => true,
                'data' => $coconut,
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch coconut availability: '.$e->getMessage(),
            ], 500);
        }
    }
    // function for get coconut availability

    public function get_coconut_list(Request $request)
    {
        try {
            $coconut = Base_ser::get_coconut_list($request->all());

            return response()->json([
                'success' => true,
                'data' => $coconut,
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch coconut availability: '.$e->getMessage(),
            ], 500);
        }
    }

    // dashboard data

    public function dashboard(Request $request)
    {
        try {
            $dashboardData = Base_ser::dashboard_data($request->all());

            return response()->json([
                'success' => true,
                'data' => $dashboardData,
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch dashboard data: '.$e->getMessage(),
            ], 500);
        }
    }

     // function to add bank details

    public function add_bank_details(Request $request)
    {
        $rule = [
            'type' => 'required|string|in:farmer,party,emp',
            'prime_id' => 'nullable|string',
            'f_id' => 'required|string',
            'acc_type' => 'required|string',
            'b_name' => 'required|string',
            'acc_name' => 'required|string',
            'acc_no' => 'required|string',
            'ifsc' => 'nullable|string',
            'upi' => 'nullable|string',
            'method' => 'required|string|in:create,update',

        ];

        $validator = Validator::make($request->all(), $rule);

        if( $validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        try{
        if( $request->method == 'update' ) {
            $bank = Bank::where('type', $request->type)
                         ->where('id', $request->prime_id)
                         ->first();

            if( !$bank ) {
                return response()->json([
                    'success' => false,
                    'message' => 'Bank details not found for update',
                ], 404);
            }

            $bank->acc_type = $request->acc_type;
            $bank->b_name = $request->b_name;
            $bank->acc_name = $request->acc_name;
            $bank->acc_no = $request->acc_no;
            $bank->ifsc = $request->ifsc;
            $bank->upi = $request->upi;
            $bank->save();

            return response()->json([
                'success' => true,
                'message' => 'Bank details updated successfully',
                'data' => $bank,
            ], 200);
        }else{

        
            $bank = Bank::create([
                'type' => $request->type,
                'f_id' => $request->f_id,
                'acc_type' => $request->acc_type,
                'b_name' => $request->b_name,
                'acc_name' => $request->acc_name,
                'acc_no' => $request->acc_no,
                'ifsc' => $request->ifsc,
                'upi' => $request->upi,
                'status' => 'active',
                'c_by' => Auth::guard('tenant')->user()->id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Bank details added successfully',
                'data' => $bank,
            ], 200);
        }


        }catch(\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Bank details addition failed: '.$e->getMessage(),
            ], 500);
        }
    }

    // get bank details

    public function get_bank_details(Request $request)
    {
        $rule = [
            'type' => 'required|string|in:farmer,party,emp',
            'f_id' => 'required|string',
            'prime_id' => 'nullable|string',

        ];

        $validator = Validator::make($request->all(), $rule);

        if( $validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        try{

        $bank = Bank::where('type', $request->type)
                     ->where('id', $request->prime_id)
                     ->first();

            return response()->json([
                'success' => true,
                'data' => $bank,
            ], 200);

        }catch(\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch bank details: '.$e->getMessage(),
            ], 500);
        }
    }

    // function to list bank details

    public function list_bank_details(Request $request)
    {
        $rule = [
            'type' => 'required|string|in:farmer,party,emp',
            'emp_id' => 'nullable|string',

        ];

        $validator = Validator::make($request->all(), $rule);

        if( $validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        try{

        $bank = Bank::where('type', $request->type)->where('f_id', $request->emp_id)->get();

            return response()->json([
                'success' => true,
                'data' => $bank,
            ], 200);

        }catch(\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch bank details: '.$e->getMessage(),
            ], 500);
        }
    }
    
}
