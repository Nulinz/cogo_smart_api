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
            'party_kn' => 'required|string',
            'party_nick_en' => 'required|string',
            'party_nick_kn' => 'required|string',
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
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Party created/updated failed: '.$e->getMessage(),
            ], 500);
        }
    }
}
