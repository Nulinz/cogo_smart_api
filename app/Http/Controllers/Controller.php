<?php

namespace App\Http\Controllers;

use App\Services\Tenant_db;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;
use App\Models\Sequence;

abstract class Controller
{
    // checking teh mobile number exists

    protected function check_mobile_exists(Request $request)
    {
        // Validate the request
        $validator = Validator::make($request->all(), [
            'mobile_no' => 'required|string',
            'type' => 'required|string|in:farmer,party,register,team',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $mobile_no = $request->mobile_no;
        $existing_mobile_numbers = [];

        if ($request->type == 'register') {

            Tenant_db::main(); // switch to main DB

            $exists = DB::table('users')->where('phone', $request->mobile_no)->exists();

        } else {

            $data = JWTAuth::parseToken()->getPayload();

            $db_name = $data->get('db_name');
            Tenant_db::connect($db_name); // switch to tenant DB

            $model = [
                'farmer' => 'App\Models\Farmer',
                'party' => 'App\Models\Party',
                'team' => 'App\Models\Team',
            ];

            $exists = $model[$request->type]::where('ph_no', $request->mobile_no)->exists();

        }

        if ($exists) {
            return response()->json([
                'success' => false,
                'value' => 1,
                'message' => 'Mobile number already exists',
            ], 422);
        } else {

            return response()->json([
                'success' => true,
                'value' => 0,
                'message' => 'Mobile number is available',
            ]);
        }

        // return in_array($mobile_no, $existing_mobile_numbers);
    }

    public function create_sequence(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'load_pref' => 'required|string',
            'load_suf' => 'required|string',
            'farmer_pref' => 'required|string',
            'farmer_suf' => 'required|string',
            'party_pref' => 'required|string',
            'party_suf' => 'required|string',
        ]);

        if( $validator->fails() ) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        try{
            $seq = Sequence::create(
               
                [
                    'load_pref' => $request->load_pref,
                    'load_suf' => $request->load_suf,
                    'farmer_pref' => $request->farmer_pref,
                    'farmer_suf' => $request->farmer_suf,
                    'party_pref' => $request->party_pref,
                    'party_suf' => $request->party_suf,
                    'status' => 'active',
                    'c_by' => Auth::guard('tenant')->user()->id,
                ]
            );

            return response()->json([
                'success' => true,
                'message' => 'Sequence created successfully',
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Sequence created failed: '.$e->getMessage(),
            ], 500);
        }
    }
}
