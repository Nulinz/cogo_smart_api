<?php

namespace App\Http\Controllers;

use App\Models\Admin_farmer;
use App\Models\Admin_user;
use App\Models\User;
use App\Services\Tenant_db;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Schema;

class AdminController extends Controller
{

    public function dashboard()
    {

    $farmer = Admin_farmer::all();

    $farmer_count = $farmer->count();
    $farmer_monthly_count = $farmer->where('created_at', '>=', now()->subDays(30))->count();

    $traders = DB::table('users')->where('type', 'reg')->get();

    $trader_count = $traders->count();
    $trader_monthly_count = $traders->where('created_at', '>=', now()->subDays(30))->count();

       
    $subscription_summary = [];
    $subscription_monthly = [];
    $total_subscription_amount = 0;
    $monthly_subscription_amount = 0;


    foreach ($traders as $trader) {

        try {

            Tenant_db::connect($trader->db_name);

            if (Schema::connection('tenant')->hasTable('subscription')) {

                $subscriptions = DB::connection('tenant')
                    ->table('subscription')
                    ->get();

                foreach ($subscriptions as $sub) {

                    $type = $sub->type;

                    // total count per type
                    if (!isset($subscription_summary[$type])) {
                        $subscription_summary[$type] = 0;
                    }

                    $subscription_summary[$type]++;


                    // monthly count
                    if (strtotime($sub->created_at) >= strtotime(now()->subDays(30))) {

                        if (!isset($subscription_monthly[$type])) {
                            $subscription_monthly[$type] = 0;
                        }

                        $subscription_monthly[$type]++;
                        $monthly_subscription_amount += $sub->amount ?? 0;
                    }

                    $total_subscription_amount += $sub->amount ?? 0;
                }
            }

        } catch (\Exception $e) {
            // optional log
        }
    }

       $trader_today = $traders->where('created_at', '>=', now()->startOfDay());

        $trader_today = $trader_today->map(function ($trader) {

        $subscription = null;

        try {

            Tenant_db::connect($trader->db_name);

            if (Schema::connection('tenant')->hasTable('subscription')) {

                $subscription = DB::connection('tenant')
                    ->table('subscription')
                    ->latest('id')
                    ->first();

                $trader->subscription_plan = $subscription?->type
                    ? $subscription->type . ' - ' . $subscription->duration . ' months'
                    : 'N/A';

                $trader->subscription_end = $subscription?->expiry_date
                    ? date('d-m-Y', strtotime($subscription->expiry_date))
                    : 'N/A';
            }

        } catch (\Exception $e) {
            // optional log
        }

        Log::info('Trader today data', ['trader' => $trader->name, 'subscription' => $subscription]);

    return $trader;
});


        return view('dashboard.index',[
                'farmer_count' => $farmer_count ?? 0,
                'farmer_monthly_count' => $farmer_monthly_count ?? 0,
                'trader_count' => $trader_count ?? 0,
                'trader_monthly_count' => $trader_monthly_count ?? 0,
                'subscription_summary' => $subscription_summary ?? [],
                'subscription_monthly' => $subscription_monthly ?? [],
                'total_subscription_amount' => $total_subscription_amount ?? 0,
                'monthly_subscription_amount' => $monthly_subscription_amount ?? 0,
                'trader_today' => $trader_today ?? [],
            ]);
    }

    public function index()
    {
        // dd('login');
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $rule = [
            'phone' => 'required',
            'password' => 'required',
        ];
        $validator = Validator::make($request->all(), $rule);

        if ($validator->fails()) {
             return redirect()->back()->withErrors($validator)->withInput();
            // return response()->json(['errors' => $validator->errors()], 422);
        }

        $admin_user = Admin_user::where('phone', $request->phone)->where('password', $request->password)->first();

        // \Log::info('Admin login attempt', ['phone' => $request->phone]);
// 
        if (! $admin_user) {
            return redirect()->back()->with('login_error', 'Invalid credentials')->withInput();

            // return response()->json(['error' => 'Invalid credentials'], 401);
        }

         // Manually login user
        Auth::guard('web')->login($admin_user);

        return redirect()->route('dashboard')->with('success', 'Login successful!');

        // return response()->json(['message' => 'Login successful', 'admin_user' => $admin_user]);

        // Handle login logic here
        // return redirect()->back()->with('success', 'Login successful!');
    }

    public function user_add(Request $request)
    {
        // Handle user addition logic here
        // return redirect()->back()->with('success', 'User added successfully!');
        $rule = [
            'name' => 'required',
            'password' => 'required',
            'phone' => 'required',
        ];

        $validator = Validator::make($request->all(), $rule);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = [
            'name' => $request->name,
            'password' => $request->password,
            'phone' => $request->phone,
        ];

        try {

            Admin_user::create($data);

                return redirect()->back()->with('message', 'User added successfully!');

            // return response()->json(['message' => 'User added successfully']);

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to add user: ' . $e->getMessage());

        }

    }

    // FUNCTION FOR USER LIST

    public function user_list()
    {
        $users = Admin_user::all();

        return view('user.index', ['users' => $users]);

        // return response()->json(['users' => $users]);
    }

    // function for user edit show

    public function user_edit_show(Request $request)
    {
        $rule = [
            'user_id' => 'required|exists:admin_users,id',
        ];

        $validator = Validator::make($request->all(), $rule);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = Admin_user::find($request->user_id);

        if (! $user) {
            return response()->json(['error' => 'User not found'], 404);
        }

        return response()->json(['user' => $user]);
    }

    // function for user edit store

    public function user_edit_store(Request $request)
    {
        // Log::info('User edit store request', ['request' => $request->all()]);
        $rule = [
            'user_id' => 'required|exists:admin_users,id',
            'name' => 'required',
            'phone' => 'required',
            'password' => 'required',
        ];

        $validator = Validator::make($request->all(), $rule);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
            // return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = Admin_user::find($request->user_id);

        // if (! $user) {
        //     return response()->json(['error' => 'User not found'], 404);
        // }

        $user->name = $request->name;
        $user->phone = $request->phone;
        $user->password = $request->password;

        try {

             $user->save();

            return back()->with('message', 'User updated successfully!');

            // return response()->json(['message' => 'User updated successfully']);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to update user', 'message' => $e->getMessage()], 500);
        }
    }

    // function for user status update

    public function user_status_update(Request $request)
    {
        $rule = [
            'user_id' => 'required|exists:admin_users,id',
            'status' => 'required|in:active,inactive',
        ];

        $validator = Validator::make($request->all(), $rule);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = Admin_user::find($request->user_id);

        \Log::info('User status update request', ['user_id' => $request->user_id, 'status' => $request->status]);
        if (! $user) {
            return response()->json(['error' => 'User not found'], 404);
        }

        $user->status = ($request->status === 'active') ? 'inactive' : 'active';

        try {

            $user->save();

            return response()->json(['message' => 'User status updated successfully']);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to update user status', 'message' => $e->getMessage()], 500);
        }
    }

    // function for farmer edit store

    public function farmer_edit_store(Request $request)
    {
         Log::info('Farmer edit store request', ['request' => $request->all()]);
        $rule = [
            'farmer_id' => 'required|exists:farmers,id',
            'name' => 'required',
            'nick_name' => 'required',
            'location' => 'required',
            'phone' => 'required',
            'whatsapp_number' => 'required',
        ];

        $validator = Validator::make($request->all(), $rule);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
            Log::error('Farmer edit store validation failed', ['errors' => $validator->errors()]);
            // return response()->json(['errors' => $validator->errors()], 422);
        }


        $user = Admin_farmer::find($request->farmer_id);

        if (! $user) {
            return back()->with('error', 'Farmer not found')->withInput();
            // return response()->json(['error' => 'Farmer not found'], 404);
        }

        $user->name = $request->name;
        $user->phone = $request->phone;
        $user->nick = $request->nick_name;
        $user->location = $request->location;
        $user->whats_up = $request->whatsapp_number;
        // $user->password = $request->password;    

        try {

            $user->save();

            return back()->with('message', 'Farmer updated successfully!');

            // return response()->json(['message' => 'Farmer updated successfully']);

        } catch (\Exception $e) {
            return back()->with('error', 'Failed to update farmer')->withInput();
            // return response()->json(['error' => 'Failed to update farmer', 'message' => $e->getMessage()], 500);
        }
    }

    // function for farmer edit show

    public function farmer_edit_show(Request $request)
    {
        $rule = [
            'farmer_id' => 'required|exists:farmers,id',
        ];

        $validator = Validator::make($request->all(), $rule);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $farmer = Admin_farmer::find($request->farmer_id);

        if (! $farmer) {
            return response()->json(['error' => 'Farmer not found'], 404);
        }

        return response()->json(['farmer' => $farmer]);
    }

    public function farmers(Request $request)
    {
        $rule = [
            'name' => 'required',
            'nick' => 'required',
            'phone' => 'required',
            'whats_up' => 'required',
            'location' => 'required',
        ];

        $validator = Validator::make($request->all(), $rule);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = [
            'name' => $request->name,
            'phone' => $request->phone,
            'nick' => $request->nick,
            'whats_up' => $request->whats_up,
            'location' => $request->location,
        ];

        try {

            $farmer = Admin_farmer::create($data);

            // QR will open farmer details page
            $qrData = url('farmer/'.$farmer->id);

            $fileName = 'farmer_'.$farmer->id.'.png';
            $path = public_path('farmer_qr/'.$fileName);

            $qrCode = new QrCode($qrData);
            // $qrCode->setSize(300);
            // $qrCode->setMargin(10);

            $writer = new PngWriter;

            $result = $writer->write($qrCode);

            $result->saveToFile($path);

            $url = url('api/farmer_qr_code').'?farmer_id='.$farmer->id;

            // $farmer->qr_code_url = $url;
            // $farmer->save();

            return redirect()->route('farmer.list')->with('success', 'Farmer added successfully!');

            // return response()->json([
            //     'message' => 'Farmer added successfully',
            //     'qr_code_url' => $url,
            // ]);

        } catch (\Exception $e) {

            return response()->json([
                'error' => 'Failed to add farmer',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    // function to generate qr code for farmer

    public function farmer_qr_code(Request $request)
    {
        $rule = [
            'farmer_id' => 'required|exists:farmers,id',
        ];

        $validator = Validator::make($request->all(), $rule);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $farmer = Admin_farmer::find($request->farmer_id);

        if (! $farmer) {
            return response()->json(['error' => 'Farmer not found'], 404);
        }

        return view('farmer_qr_code', ['farmer' => $farmer]);
    }

    // function for farmer qr data

    public function farmer_qr_data(Request $request)
    {
        // $rule = [
        //     'farmer_id' => 'required|exists:farmers,id',
        // ];

        // $validator = Validator::make($request->all(), $rule);

        // if ($validator->fails()) {
        //     return response()->json(['errors' => $validator->errors()], 422);
        // }

            $file = $request->file;

            $name = explode('_', $file);        // ["farmer", "9.png"]
            $last = explode('.', $name[1])[0];  // ["9","png"]

        $farmer = Admin_farmer::find($last);

        if (! $farmer) {
            return response()->json(['error' => 'Farmer not found'], 404);
        }

        return response()->json(['farmer' => $farmer]);
    }

    // function for farmer list

    public function farmer_list()
    {
        $farmers = Admin_farmer::all();

        return view('farmer.index',['farmers' => $farmers]);
    }

    // function for trader list

    public function trader_list()
    {
        // Assuming you have a Trader model and traders table
        $traders = DB::table('users')->where('type', 'reg')->get();

        $traders = $traders->map(function ($trader) {

            $subscription = null;

            try {

                $database = $trader->db_name;

                // switch tenant database
                Tenant_db::connect($database);

                // check table exists
                if (Schema::connection('tenant')->hasTable('subscription')) {

                    $subscription = DB::connection('tenant')
                        ->table('subscription')
                        ->latest('id')
                        ->first();
                }

            } catch (\Exception $e) {
                // optional: log error
                // \Log::error($e->getMessage());
            }

            $trader->subscription_plan = $subscription?->type ? $subscription->type.' - '.$subscription->duration.' months' : 'N/A';
            $trader->subscription_end = $subscription?->expiry_date ? date('d-m-Y', strtotime($subscription->expiry_date)): 'N/A';

            return $trader;
        });
        
        return view('trader.index', ['traders' => $traders]);

        // return response()->json(['traders' => $traders]);
    }

    // function for subscription list

    public function subscription_list()
    {
        // Assuming you have a Subscription model and subscriptions table
        $subscriptions = DB::table('subscription_admin')->get();
        
        return view('subscription.index', ['subscriptions' => $subscriptions]);

        // return response()->json(['subscriptions' => $subscriptions]);
    }

    // function for subscription profile

    public function subscription_profile(Request $request, $type)
    {
         // Assuming you have a Trader model and traders table
        $traders = DB::table('users')->where('type', 'reg')->get();

       
        $traders = $traders->map(function ($trader) use ($type) {

           $subscription_data = null;

            try {

                $database = $trader->db_name;

                // switch tenant database
                Tenant_db::connect($database);

                

                // check table exists
                if (Schema::connection('tenant')->hasTable('subscription')) {

                    $subscription_data = DB::connection('tenant')
                        ->table('subscription')
                        ->where('type', $type)
                        ->latest('id')
                        ->first();

                    // Log::info('Subscription data found for trader', ['sub' => $subscription_data, 'trader' => $trader->name]);
                }
                else{
                    // Log::warning('Subscription table not found in tenant database', ['database' => $database]);
                }

            } catch (\Exception $e) {
                // $subscription = null;
                // optional: log error
                // \Log::error($e->getMessage());
            }

           
            if ($subscription_data) {
                //  Log::info('Subscription data for trader', ['sub' => $subscription_data, 'trader' => $trader->name]);
                $trader->subscription_plan = $subscription_data->type . ' - ' . $subscription_data->duration . ' months';
                $trader->subscription_end = date('d-m-Y', strtotime($subscription_data->expiry_date));

                 return $trader; // keep trader
            }

             return null; // remove trader if no subscription

                // return $subscription ? $trader : null;

        })->filter();

        //  dd($type);

        
        return view('subscription.profile',[
            'traders' => $traders,
        ]);

        // return response()->json(['subscriptions' => $subscriptions]);
    }
   
    // function for subscription store

    public function subscription_store(Request $request)
    {
        $rule = [
            'sub_id' => 'sometimes|exists:subscription_admin,id',
            'name' => 'required',
            'amount' => 'required|numeric',
            'duration' => 'required|integer',
        ];

        $validator = Validator::make($request->all(), $rule);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $data = [
            'type' => $request->name,
            'amount' => $request->amount,
            'duration' => $request->duration,
            // 'desc' => $request->desc,
        ];

        try {

            if ($request->has('sub_id')) {
                DB::table('subscription_admin')->where('id', $request->sub_id)->update($data);
            } 
           

            return redirect()->back()->with('message', 'Subscription added successfully!');

            // return response()->json(['message' => 'Subscription added successfully']);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to add subscription', 'message' => $e->getMessage()], 500);
        }
    }

      // function for logout

      public function logout()
      {
          Auth::guard('web')->logout();
  
          return redirect()->route('login')->with('success', 'Logged out successfully!');
      }


      // function for farmer phone check

      public function farmer_check_phone(Request $request)
      {
          $rule = [
              'phone' => 'required',
            //   'farmer_id' => 'sometimes|exists:farmers,id',
          ];

          $validator = Validator::make($request->all(), $rule);

          if ($validator->fails()) {
              return response()->json(['errors' => $validator->errors()], 422);
          }

          $query = Admin_farmer::where('phone', $request->phone)->exists();

        //   if ($request->has('farmer_id')) {
        //       $query->where('id', '!=', $request->farmer_id);
        //   }

        //   $exists = $query->exists();

          return response()->json(['exists' => $query]);
      }
}
