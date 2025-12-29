<?php

namespace App\Http\Controllers;

use App\Services\Base_ser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class Base_cnt extends Controller
{
    // Common controller methods can be added here

    public function create_common(Request $request)
    {
        // Logic to create quality

        Log::info("message", ['request' => $request->all()]);
      

            if( $request->filled('transport_id') || $request->filled('truck_id') || $request->filled('quality_id') || $request->filled('loss_id') ){

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
            } else {

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
            'type' => 'required|string|in:quality,transport,truck,loss',
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
            'type' => 'required|string|in:quality,transport,truck,loss',
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
    
}
