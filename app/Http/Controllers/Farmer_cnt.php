<?php

namespace App\Http\Controllers;

use App\Services\Farmer_ser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class Farmer_cnt extends Controller
{
    // Controller methods here

    public function create_farm(Request $request)
    {
        // Method implementation here

        $rule = [
            'farm_id' => 'nullable|string',
            'farm_en' => 'required|string',
            // 'farm_kn' => 'nulllable|string',
            'farm_nick_en' => 'required|string',
            // 'farm_nick_kn' => 'nulllable|string',
            'location' => 'required|string',
            'ph_no' => 'required|string',
            'wp_no' => 'required|string',
            'open_type' => 'required|string',
            'open_bal' => 'required|string',
            'acc_type' => 'required|string',
            'b_name' => 'required|string',
            'acc_name' => 'required|string',
            'acc_no' => 'required|string',
            'ifsc' => 'required|string',
            'upi' => 'required|string',

        ];

        $validator = Validator::make($request->all(), $rule);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        try {

            $farmer = Farmer_ser::create_farm($request->all());

            return response()->json([
                'success' => true,
                'message' => 'Farmer created/updated successfully',
                'data' => $farmer,
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Farmer created/updated failed: '.$e->getMessage(),
            ], 500);
        }
    }

    /// fucntion form individual farmer details

    public function get_farmer_details(Request $request)
    {
        $rule = [
            'farm_id' => 'required|string',
        ];

        $validator = Validator::make($request->all(), $rule);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }
        try {
            $farmer = Farmer_ser::get_farmer_details($request->farm_id);

            return response()->json([
                'success' => true,
                'data' => $farmer,
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch farmer details: '.$e->getMessage(),
            ], 500);
        }
    }

    // function for list of farmers

    public function get_farmer_list(Request $request)
    {
        try {
            $farmers = Farmer_ser::get_all_farmers();

            return response()->json([
                'success' => true,
                'data' => $farmers,
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch farmers list: '.$e->getMessage(),
            ], 500);
        }
    }
}
