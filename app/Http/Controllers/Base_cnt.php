<?php

namespace App\Http\Controllers;

use App\Services\Base_ser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class Base_cnt extends Controller
{
    // Common controller methods can be added here

    public function create_common(Request $request)
    {
        // Logic to create quality

      

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
                'status' => false,
                'message' => 'Invalid type provided',
            ], 400);
        }

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
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
                'status' => true,
                'message' => $request->type.' created/updated successfully',
                'data' => $quality,
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $request->type.' created/updated failed: '.$e->getMessage(),
            ], 500);
        }

    }
}
