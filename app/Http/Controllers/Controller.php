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

        if ($request->type == 'register' || $request->type == 'team') {

            Tenant_db::main(); // switch to main DB

            $exists = DB::table('users')->where('phone', $request->mobile_no)->exists();

        } else {

            $token = JWTAuth::getToken();
            $data =  $payload = JWTAuth::manager()
                    ->getJWTProvider()
                    ->decode($token);
             // $data = JWTAuth::parseToken()->getPayload();

            $db_name = $data['db_name'] ?? null;
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
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Sequence created failed: '.$e->getMessage(),
            ], 500);
        }
    }

    // functiopn for toggle  o and 1  for favorite

     public function toggle_fav(Request $request)
     {
        $validator = Validator::make($request->all(), [
            'type' => 'required|string|in:farmer,party',
            'id' => 'required|integer',
        ]);

        if( $validator->fails() ) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $model_map = [
            'farmer' => 'App\Models\Farmer',
            'party' => 'App\Models\Party',
        ];

        $model_class = $model_map[$request->type];

        $record = $model_class::find($request->id);

        if (!$record) {
            return response()->json([
                'success' => false,
                'message' => ucfirst($request->model).' not found',
            ], 404);
        }

        // Toggle fav value
       $record->fav = $record->fav == 1 ? 0 : 1;
        $record->save();

        return response()->json([
            'success' => true,
            'message' => ucfirst($request->model).' favorite status updated',
            'fav' => $record->fav,
        ]);
     }
}
