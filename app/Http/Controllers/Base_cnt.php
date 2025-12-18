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
      

            if( $request->filled('transport_id') || $request->filled('truck_id') || $request->filled('quality_id') ) {

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
            'type' => 'required|string|in:quality,transport,truck',
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $list = Base_ser::get_common_list($request->type);

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
            'type' => 'required|string|in:quality,transport,truck',
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
}
