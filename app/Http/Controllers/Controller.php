<?php

namespace App\Http\Controllers;

use App\Services\Tenant_db;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;

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
}
