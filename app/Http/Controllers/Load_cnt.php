<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Services\Load_ser;

class Load_cnt extends Controller
{
    //function for create load

    public function create_load(Request $request)
    {
        $rule = [
            'market' => 'required|string',
            'party_id' => 'required|string',
            'empty_weight' => 'required|string',
            'load_date' => 'required|string',
            'veh_no' => 'required|string',
            'dr_no' => 'required|string',
            'transporter' => 'required|string',
            'quality_price' => 'required|string',
            'fliter_price' => 'required|string',
            'req_qty' => 'required|string',
            'truck_capacity' => 'required|string',
            'team' => 'required',
        ];

        $validator = Validator::make($request->all(), $rule);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            // Load creation logic here

            // Assuming Load_ser is a service class that handles load creation
            $load = Load_ser::create_load($request->all());

            return response()->json([
                'success' => true,
                'message' => 'Load created successfully',
                // 'data' => $load,
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Load creation failed: '.$e->getMessage(),
            ], 500);
        }
    }

    // Other controller methods can be added here

    public function add_load_item(Request $request)
    {
        // Method implementation here

        $rule = [
            'load_id' => 'required|string',
            'farmer_id' => 'required|string',
            'product_id' => 'required|string',
            'total_piece' => 'required|string',
            'grace_piece' => 'required|string',
            'grace_per' => 'required|string',
            'bill_piece' => 'required|string',
            'price' => 'required|string',
            'commission' => 'required|string',
            'bill_amount' => 'required|string',
            'adv' => 'required|string',
            'quality' => 'required|string',
            'total_amt' => 'required|string',
        ];

        $validator = Validator::make($request->all(), $rule);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        try {

            $load_item = Load_ser::add_load_item($request->all());

            return response()->json([
                'success' => true,
                'message' => 'Load item added successfully',
                'data' => $load_item,
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Adding load item failed: '.$e->getMessage(),
            ], 500);
        }

    }
}
