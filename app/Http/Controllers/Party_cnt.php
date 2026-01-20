<?php

namespace App\Http\Controllers;

use App\Services\Party_ser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class Party_cnt extends Controller
{
    // method to create or update party

    public function create_party(Request $request)
    {
        $rule = [
            'party_id' => 'nullable|string',
            'party_en' => 'required|string',
            'party_nick_en' => 'required|string',
            'com_name' => 'required|string',
            'com_add' => 'required|string',
            'party_location' => 'required|string',
            'party_ph_no' => 'required|string',
            'party_wp_no' => 'required|string',
            'party_open_type' => 'required|string',
            'party_open_bal' => 'required|string',
            'party_acc_type' => 'required|string',
            'party_b_name' => 'required|string',
            'party_acc_name' => 'required|string',
            'party_acc_no' => 'required|string',
            'party_ifsc' => 'required|string',
            'party_upi' => 'required|string',

        ];

        $validator = Validator::make($request->all(), $rule);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        try {

            $party = Party_ser::create_party($request->all());

            return response()->json([
                'success' => true,
                'message' => 'Party created/updated successfully',
                'data' => $party,
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Party created/updated failed: '.$e->getMessage(),
            ], 500);
        }
    }

    public function get_party_details(Request $request)
    {
        $rule = [
            'party_id' => 'required|string',
        ];

        $validator = Validator::make($request->all(), $rule);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        try {

            $party = Party_ser::get_party_details($request->party_id);

            return response()->json([
                'success' => true,
                'message' => 'Party details fetched successfully',
                'data' => $party,
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch party details: '.$e->getMessage(),
            ], 500);
        }
    }

    // function to fetch list of parties

    public function get_party_list(Request $request)
    {
        try {
            $parties = Party_ser::get_all_party();

            return response()->json([
                'success' => true,
                'data' => $parties,
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch party list: '.$e->getMessage(),
                'line'=>$e->getLine(),
            ], 500);
        }
    }

    // function for party profile

    public function party_profile(Request $request)
    {
        $rule = [
            'party_id' => 'required|string',
        ];

        $validator = Validator::make($request->all(), $rule);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $party = Party_ser::party_profile($request->all());

            return response()->json([
                'success' => true,
                'data' => $party,
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch party profile: '.$e->getMessage(),
            ], 500);
        }
    }

    // funciton for party pay In

    public function party_pay_in(Request $request)
    {
        $rule = [
            'party_id' => 'required|string',
            'amount' => 'required|string',
            'method' => 'required|string',
        ];

        $validator = Validator::make($request->all(), $rule);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        try{
            $party_cash = Party_ser::party_pay_in($request->all());

            return response()->json([
                'success' => true,
                'message' => 'Party pay in successful',
                'data' => $party_cash,
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Party pay in failed: '.$e->getMessage(),
            ], 500);
        }
    }

    // function for party pay Out

    public function party_pay_out(Request $request)
    {
        $rule = [
            'party_id' => 'required|string',
            'amount' => 'required|string',
            'method' => 'required|string',
        ];

        $validator = Validator::make($request->all(), $rule);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        try{
            $party_cash = Party_ser::party_pay_out($request->all());

            return response()->json([
                'success' => true,
                'message' => 'Party pay out successful',
                'data' => $party_cash,
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Party pay out failed: '.$e->getMessage(),
            ], 500);
        }
    }

    // function for party pay edit

    public function party_pay_edit(Request $request)
    {
        $rule = [
            'payment_id' => 'required|string',
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

        try{
            $party_cash = Party_ser::party_pay_edit($request->all());

            return response()->json([
                'success' => true,
                'message' => 'Party pay edit successful',
                'data' => $party_cash,
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Party pay edit failed: '.$e->getMessage(),
            ], 500);
        }
    }
}
