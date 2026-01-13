<?php

namespace App\Http\Controllers;

use App\Models\Master;
use App\Models\Master_db;
use App\Models\User;
use App\Services\Otp;
use App\Services\Tenant_db;
use App\Services\Base_ser;
use App\Models\Petty_cash;
use App\Models\Farmer_cash;
use App\Services\Stock_ser;
use App\Models\Sequence;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;

class Register_cnt extends Controller
{
    // function to refresh token
    public function refresh_token()
    {
          // both error occur 401


            //{"error": "token_expired"},{"error": "session_expired"} -----------------------

        try {
            $newToken = JWTAuth::refresh(JWTAuth::getToken());

            return response()->json([
                'success' => true,
                'message' => 'Token refreshed successfully',
                'token' => $newToken,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Token refresh failed: '.$e->getMessage(),
            ], 500);
        }
    }

    // option to generate otp
    public function generate_otp(Request $request)
    {
        $rule = [
            'phone' => 'required|string',

        ];
        $validator = Validator::make($request->all(), $rule);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $otp = rand(100000, 999999);

            // app(Otp::class)->sendOtp($request->phone, $otp);

            \Log::info("OTP for ".$request->phone." is ".$otp);

            Tenant_db::main(); // switch to main DB
            $masterUser = DB::table('users')->where('phone', $request->phone)->first();
            // if ($masterUser) {
            //     DB::table('users')->where('phone', $request->phone)->update([
            //         'otp' => $otp,
            //         'otp_verified' => 'no',
            //     ]);
            // } else {
            //     $masterUser = DB::table('users')->insert([
            //         'phone' => $request->phone,
            //         'otp' => $otp,
            //         'otp_verified' => 'no',
            //         'created_at' => now(),
            //         'updated_at' => now(),
            //     ]);
            // }

            return response()->json([
                'success' => true,
                'message' => 'OTP generated successfully',
                'otp' => $otp,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
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
                'success' => false,
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
                'otp_verified' => 'yes',
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

                $product = ['Grade A','katki','Bombay katki'];

                foreach($product as $prod){

                    DB::connection('tenant')->table('m_product')->insert([
                        'name_en' => $prod,
                        // 'name_kn' => null,
                        'type' => 'auto',
                        'status' => 'active',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }

                DB::connection('tenant')->table('m_sequence')->insert([
                    'load_pref' => 'LOAD',
                    'load_suf' => 001,
                    'farmer_pref' => 'FARM',
                    'farmer_suf' => 001,
                    'party_pref' => 'PARTY',
                    'party_suf' => 001,
                    'status' => 'active',
                    'c_by' => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
               


                $token = JWTAuth::claims([
                    'db_name' => $master->db_name,
                ])->fromUser($user_create);

                Auth::guard('tenant')->setUser($user_create);

                Tenant_db::main(); // switch to main DB

                $master_fid = DB::connection('mysql')->table('users')->where('id', $master->id)->update([
                    'f_id' => $user_create->id,
                ]);

            } catch (\Exception $e) {
                \Log::error('Tenant DB creation failed: '.$e->getMessage());
                return response()->json([
                    'success' => false,
                    'message' => 'Database connection failed: '.$e->getMessage(),
                ], 500);
            }

            return response()->json([
                'success' => true,
                'message' => 'User registered successfully',
                'data' => $user_create,
                'token' => $token,
            ], 200);

        } catch (\Exception $e) {
            \Log::error('User registration failed: '.$e->getMessage());
            return response()->json([
                'success' => false,
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
                'success' => false,
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
                'success' => true,
                'message' => 'Password updated successfully',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Password update failed: '.$e->getMessage(),
            ], 500);
        }
    }

    // function for login mobile number

    public function login_phone(Request $request)
    {
        $rule = [
            'phone' => 'required|string',

        ];
        $validator = Validator::make($request->all(), $rule);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
             Tenant_db::main(); // switch to main DB

            $mainUser = DB::table('users')->where('phone', $request->phone)->first();

            // $user = User::where('phone', $request->phone)->first();

            if (! $mainUser) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not found',
                    
                ], 404);
            }

            // if($mainUser->otp_verified != 'yes') {

            //     Tenant_db::connect($mainUser->db_name); // switch to tenant DB

            //     $user_data = User::where('phone', $request->phone)->first();

            //     return response()->json([
            //         'success' => false,
            //         'message' => 'OTP not verified',
            //         'data'=>$user_data->role
                    
            //     ], 403);
            // }

            return response()->json([
                'success' => true,
                'message' => 'User found',
                'data'=>$mainUser

            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'User fetch failed: '.$e->getMessage(),
            ], 500);
        }

    }

    public function login(Request $request)
    {

        // dd('here');

        // Tenant_db::main(); // switch to main DB

        // $mainUser = DB::table('users')->where('phone', $request->phone)->first();

        // // dd($mainUser);

        // if (! $mainUser) {
        //     return response()->json(['error' => 'User not found in main DB'], 404);
        // }

       

            $rule = [
                'phone' => 'required|string',
                'password' => 'required|string',
                'db_name' => 'required|string',
            ];

        $validator = Validator::make($request->all(), $rule);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        try{

            Tenant_db::connect($request->db_name); // switch to tenant DB

            

            $user = User::where('phone', $request->phone)->where('password', $request->password)->first();

            if(! $user){
                return response()->json(['success'=>false,'error' => 'Invalid credentials'], 401);
            }

            $token = JWTAuth::claims([
                        'db_name' => $request->db_name,
                    ])->fromUser($user);

            Auth::guard('tenant')->setUser($user);

           // 👉 Get payload FROM the token you just created
            $payload = JWTAuth::setToken($token)->getPayload();

            // 👉 Extract expiry (exp)
            $expiresAt = $payload->get('exp'); // UNIX timestamp

            $exp = date('Y-m-d H:i:s', $expiresAt);


            // return response()->json([
            //     // 'token' => $token,
            //     // 'user' => $user,
            // ]);

            return response()->json([
                'success' => true,
                'message' => 'Login successful',
                'token' => $token,
                'token_expires_at' => $exp,
                'user' => Auth::guard('tenant')->user(),
            ]);

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
        

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Login failed: '.$e->getMessage(),
            ], 500);    
        }
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

    // function for insert the sequence

    public function create_seq(Request $request)
    {
        return parent::create_sequence($request);
    }

    // toggle favorite for farmer and party

    public function toggle_fav(Request $request)
    {
        return parent::toggle_fav($request);
    }

    // create a employee user

    public function create_employee(Request $request)
    {

        // $payload = JWTAuth::parseToken()->getPayload();

         $token = JWTAuth::getToken();
            $payload =  $payload = JWTAuth::manager()
                    ->getJWTProvider()
                    ->decode($token);

        // $dbName = $payload->get('db_name');

        $db = $payload['db_name'];

        $rule = [
            'name' => 'required|string',
            'phone' => 'required|string',
            'role' => 'required|string',
            'location' => 'required|string',

        ];

        $validator = Validator::make($request->all(), $rule);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

         DB::beginTransaction();

        try {
            $user = User::create([
                'name' => $request->name,
                'phone' => $request->phone,
                'role' => $request->role,
                'location' => $request->location,
                'password' =>'123456',
                'status' => 'active',
            ]);

            if($user){

                Tenant_db::main(); // switch to main DB
                $master_user = DB::table('users')->insert([
                    'name' => $request->name,
                    'type' => 'emp',
                    'f_id' => $user->id,
                    'phone' => $request->phone,
                    'db_name' => $db,
                    'otp' => 0,
                    'otp_verified' => 'yes',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                Tenant_db::connect($db); // switch back to tenant DB
                // $masterUser = DB::table('users')->where('phone', $request->phone)->first();
            }

            return response()->json([
                'success' => true,
                'message' => 'Employee created successfully',
                'data' => $user,
            ], 200);
        } catch (\Exception $e) {
             DB::rollBack();
             Tenant_db::connect($db); // ensure DB reset

            return response()->json([
                'success' => false,
                'message' => 'Employee creation failed: '.$e->getMessage(),
            ], 500);
        }
    }

    // fucntion to get employee list

    public function get_employee_list(Request $request)
    {
        // $rule = [
        //      'role' => 'required|string',
        // ];
        // $validator = Validator::make($request->all(), $rule);
        // if ($validator->fails()) {
        //     return response()->json([
        //         'success' => false,
        //         'errors' => $validator->errors(),
        //     ], 422);
        // }
        try{
             $users = User::query()
                    ->where('status', 'active')
                    ->select('id', 'name', 'role','location')
                    ->get()
                    ->map(function ($user) {

                        $data =  Stock_ser::petty_cash_ind(['emp_id'=>$user->id]);

                        $user->balance = $data['balance'];

    
                        return $user;
                    });

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Database connection failed: '.$e->getMessage(),
            ], 500);
        }
       

        return response()->json([
            'success' => true,
            'data' => $users,
        ], 200);
    }

    // function to get employee details

    public function get_employee_details(Request $request)
    {
        $rule = [
             'user_id' => 'required|string',
        ];
        $validator = Validator::make($request->all(), $rule);
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }
        try{

            $user = Base_ser::get_employee_details($validator->validated());

            //  $user = User::where('id', $request->user_id)->first();
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Database connection failed: '.$e->getMessage(),
            ], 500);
        }
       

        return response()->json([
            'success' => true,
            'data' => $user,
        ], 200);
    }


    // function tp update employee OTP verified status

    public function update_emp_otp_status(Request $request)
    {
        Tenant_db::main(); // switch to main DB
        $rule = [
            // 'user_id' => 'required|string',
            'phone' => 'required|string|unique:users,phone',
            'otp' => 'required|string',

        ];
        $validator = Validator::make($request->all(), $rule);
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        try{
             $user = DB::table('users')->where('phone', $request->phone)->update([
                'otp' => $request->otp,
                'otp_verified' => 'yes',
             ]);

             $main_data = DB::table('users')->where('phone', $request->phone)->first();

             Tenant_db::connect($main_data->db_name); // switch to tenant DB

             $user_data = User::where('phone', $request->phone)->first();


            $token = JWTAuth::claims([
                'db_name' => $main_data->db_name,
            ])->fromUser($user_data);

            Auth::guard('tenant')->setUser($user_data);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Database connection failed: '.$e->getMessage(),
            ], 500);
        }
       

        return response()->json([
            'success' => true,
            'message' => 'OTP status updated successfully',
            'data' => $user_data,
            'token' => $token,  
        ], 200);
    }



    // function to edit employee

    public function edit_employee(Request $request)
    {
        $rule = [
            'emp_id' => 'required|string',
            'name' => 'required|string',
            'role' => 'required|string|in:admin,emp,manager',
            'location' => 'required|string',

        ];

        $validator = Validator::make($request->all(), $rule);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $user = User::where('id', $request->emp_id)->update([
                'name' => $request->name,
                'role' => $request->role,
                'location' => $request->location,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Employee updated successfully',
            ], 200);
        } catch (\Exception $e) {

            return response()->json([
                'success' => false,
                'message' => 'Employee update failed: '.$e->getMessage(),
            ], 500);
        }
    }

    // function to edit employee details

    public function edit_employee_details(Request $request)
    {
        $rule = [
            'emp_id' => 'required|string',
        ];

        $validator = Validator::make($request->all(), $rule);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $user = User::where('id', $request->emp_id)->select('id','name','location','role')->first();
            return response()->json([
                'success' => true,
                'message' => 'Employee details updated successfully',
                'data' => $user,
            ], 200);
        } catch (\Exception $e) {

            return response()->json([
                'success' => false,
                'message' => 'Employee details update failed: '.$e->getMessage(),
            ], 500);
        }
    }

    // function to change password

    public function change_password(Request $request)
    {
        if($request->has('type')){
            $user = User::where('id', $request->user_id)->select('id','name','phone','password')->first();
            return response()->json([
                'success' => true,
                'data' => $user,
            ], 200);    
        }

        $rule = [
            'new_password' => 'required|string',

        ];

        $validator = Validator::make($request->all(), $rule);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $user = Auth::guard('tenant')->user(); // ✅ Works now

            // if($user->password != $request->old_password){
            //     return response()->json([
            //         'success' => false,
            //         'message' => 'Old password does not match',
            //     ], 400);
            // }

            $user = User::where('id', $user->id)->update([
                'password' => $request->new_password,
            ]);
            // $user->save();

            return response()->json([
                'success' => true,
                'message' => 'Password changed successfully',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Password change failed: '.$e->getMessage(),
            ], 500);
        }
    }
   
    // function for get sequence count

    public function get_sequence_count(Request $request)
    {
        return response()->json(['count' => Sequence::count()]);
    }
}
