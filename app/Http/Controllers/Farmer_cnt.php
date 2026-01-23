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
            'farm_nick_en' => 'required|string',
            'location' => 'required|string',
            'ph_no' => 'required|string',
            'wp_no' => 'required|string',
            'open_type' => 'required|string',
            'open_bal' => 'required|string',
            'acc_type' => 'nullable|string',
            'b_name' => 'nullable|string',
            'acc_name' => 'nullable|string',
            'acc_no' => 'nullable|string',
            'ifsc' => 'nullable|string',
            'upi' => 'nullable|string',
            'adv' => 'nullable|string',

        ];

        $validator = Validator::make($request->all(), $rule);
        
        //  \Log::info('Create farm request data: ', $request->all());

        if ($validator->fails()) {

            // \Log::error('Validation failed in create_farm: ', $validator->errors()->toArray());

            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        try {

            $farmer = Farmer_ser::create_farm($validator->validated());

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
    // function for farmer profile

    public function farmer_profile(Request $request)
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
            $profile = Farmer_ser::farmer_profile($request->all());

            return response()->json([
                'success' => true,
                'data' => $profile,
            ], 200);

        } catch (\Exception $e) {
            \Log::error('Error fetching farmer profile: '.$e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch farmer profile: '.$e->getMessage(),
            ], 500);
        }
    }

    // function for farmer advance pending

    public function farmer_advance_pending(Request $request)
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
            $advance = Farmer_ser::farmer_advance_pending($request->all());

            return response()->json([
                'success' => true,
                'data' => $advance,
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch farmer advance pending: '.$e->getMessage(),
            ], 500);
        }
    }

    // function for farmer pay out

    public function farmer_pay_out(Request $request)
    {
        $rule = [
            'farm_id' => 'required|string',
            'type' => 'required|string|in:advance,purchase',
            'amount' => 'required|string',
            'pay_method' => 'required|string',
        ];

        $validator = Validator::make($request->all(), $rule);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }
        try {
            $payout = Farmer_ser::farmer_pay_out($request->all());

            return response()->json([
                'success' => true,
                'message' => 'Farmer pay out recorded successfully',
                'data' => $payout,
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to record farmer pay out: '.$e->getMessage(),
            ], 500);
        }
    }

    // function for farmer pay in   

    public function farmer_pay_in(Request $request)
    {
        $rule = [
            'farm_id' => 'required|string',
            'type' => 'required|string|in:advance_deduct',
            'amount' => 'required|string',
            'pay_method' => 'required|string',
        ];

        $validator = Validator::make($request->all(), $rule);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }
        try {
            $payin = Farmer_ser::farmer_pay_in($request->all());

            return response()->json([
                'success' => true,
                'message' => 'Farmer pay in recorded successfully',
                'data' => $payin,
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to record farmer pay in: '.$e->getMessage(),
            ], 500);
        }
    }

    // function for farmer pay edit

    public function farmer_pay_edit(Request $request)
    {
        $rule = [
            'payment_id' => 'required|string',
            // 'farm_id' => 'required|string',
            // 'type' => 'required|string|in:advance,advance_deduct,purchase',
            'amount' => 'required|string',
            'pay_method' => 'required|string',
        ];

        $validator = Validator::make($request->all(), $rule);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }
        try {
            $payedit = Farmer_ser::farmer_pay_edit($request->all());

            return response()->json([
                'success' => true,
                'message' => 'Farmer payment edited successfully',
                'data' => $payedit,
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to edit farmer payment: '.$e->getMessage(),
            ], 500);
        }
    }
}
