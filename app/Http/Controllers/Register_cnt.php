<?php

namespace App\Http\Controllers;

use App\Models\Master;
use App\Models\Master_db;
use App\Models\User;
use App\Services\Otp;
use App\Services\Tenant_db;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;

class Register_cnt extends Controller
{
    // option to generate otp
    public function generate_otp(Request $request)
    {
        $rule = [
            'phone' => 'required|string',

        ];
        $validator = Validator::make($request->all(), $rule);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $otp = rand(1000, 9999);

            // app(Otp::class)->sendOtp($request->phone, $otp);

            return response()->json([
                'status' => true,
                'message' => 'OTP generated successfully',
                'otp' => $otp,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'OTP generation failed: '.$e->getMessage(),
            ], 500);
        }
    }

    public function register(Request $request)
    {
        Tenant_db::main(); // switch to main DB

        $rule = [
            'name' => 'required|string|unique:users,name',
            'l_name' => 'required|string',
            'phone' => 'required|string|unique:users,phone',
            'otp' => 'required|string',

        ];

        $validator = Validator::make($request->all(), $rule);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors(),
            ], 422);
        }
        try {
            $master = Master::create([
                'name' => $request->name,
                'l_name' => $request->l_name,
                'type' => 'reg',
                'phone' => $request->phone,
                'otp' => $request->otp,
                'otp_verified' => 1,
            ]);

            $db_name = 'cogo_smart_'.Str::substr($master->name, 0, 4).'_'.$master->id;
            $master->db_name = $db_name;
            $master->save();

            $master_db = Master_db::create([
                'db_name' => $db_name,
                'f_id' => $master->id,

            ]);

            try {

                // Create tenant database connection
                Tenant_db::create_tenant_db($master->db_name);

                $user_create = User::create([
                    'name' => $request->name,
                    'l_name' => $request->l_name,
                    'phone' => $request->phone,

                ]);

                $token = JWTAuth::claims([
                    'db_name' => $master->db_name,
                ])->fromUser($user_create);

                Auth::guard('tenant')->setUser($user_create);

            } catch (\Exception $e) {
                return response()->json([
                    'status' => false,
                    'message' => 'Database connection failed: '.$e->getMessage(),
                ], 500);
            }

            return response()->json([
                'status' => true,
                'message' => 'User registered successfully',
                'data' => $user_create,
                'token' => $token,
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'User registration failed: '.$e->getMessage(),
            ], 500);
        }
    }

    // function for update passowrd

    public function update_password(Request $request)
    {

        $rule = [
            'password' => 'required|string',
        ];

        $validator = Validator::make($request->all(), $rule);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        try {

            $user = Auth::guard('tenant')->user(); // ✅ Works now

            $user = User::where('id', $user->id)->update([
                'password' => $request->password,
            ]);
            // $user->save();

            return response()->json([
                'status' => true,
                'message' => 'Password updated successfully',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Password update failed: '.$e->getMessage(),
            ], 500);
        }
    }

    public function login(Request $request)
    {

        Tenant_db::main(); // switch to main DB

        $mainUser = DB::table('users')->where('name', $request->name)->first();

        // dd($mainUser);

        if (! $mainUser) {
            return response()->json(['error' => 'User not found in main DB'], 404);
        }
        Tenant_db::connect($mainUser->db_name); // switch to tenant DB
        // dd($request->all());
        $credentials = $request->only('name', 'password');

        // User::create([
        //     'name' => 'tenant_user',
        //     'email' => 'tenant_user@example.com',
        //     'password' => Hash::make('password'),
        // ]);

        $user = User::where('name', $request->name)->first();

        // IMPORTANT: when TenantDB middleware made 'tenant' default, you can call Auth::attempt()
        // If not default, use Auth::guard('tenant')->attempt($credentials)
        // if (! $token = Auth::guard('tenant')->attempt($credentials)) {
        //     return response()->json(['error' => 'Invalid credentials'], 401);
        // }

        // if (! $user || ! Hash::check($request->password, $user->password)) {
        //     return response()->json(['error' => 'Invalid credentials'], 401);
        // }

        // Manually verify password
        // if (! Hash::check($request->password, $user->password)) {
        //     return response()->json(['error' => 'Invalid credentials'], 401);
        // }

        // Manually generate JWT token for this user
        // Generate token with tenant DB inside it
        $token = JWTAuth::claims([
            'db_name' => $mainUser->db_name,
        ])->fromUser($user);

        Auth::guard('tenant')->setUser($user);

        // return response()->json([
        //     // 'token' => $token,
        //     // 'user' => $user,
        // ]);

        return response()->json([
            'token' => $token,
            'user' => Auth::guard('tenant')->user(),
        ]);
    }

    // Get logged-in user
    public function me()
    {
        $user = Auth::guard('tenant')->user(); // ✅ Works now

        $payload = JWTAuth::parseToken()->getPayload();

        // $dbName = $payload->get('db_name');
        $data = $payload->toArray();

        return response()->json([
            'user' => $data,
        ]);
    }

    // Logout
    public function logout()
    {
        JWTAuth::invalidate(JWTAuth::getToken());

        return response()->json(['message' => 'Logged out successfully']);
    }

    // function to check mobile number exists

    public function check_mobile(Request $request)
    {
        return parent::check_mobile_exists($request);
    }
}
