<?php

namespace App\Http\Controllers;

use App\Models\Admin_farmer;
use App\Models\Admin_user;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AdminController extends Controller
{
    // public function index()
    // {
    //     return view('admin_login');
    // }

    public function login(Request $request)
    {
        $rule = [
            'phone' => 'required',
            'password' => 'required',
        ];
        $validator = Validator::make($request->all(), $rule);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $admin_user = Admin_user::where('phone', $request->phone)->where('password', $request->password)->first();

        if (! $admin_user) {
            return response()->json(['error' => 'Invalid credentials'], 401);
        }

        return response()->json(['message' => 'Login successful', 'admin_user' => $admin_user]);

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

            return response()->json(['message' => 'User added successfully']);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to add user', 'message' => $e->getMessage()], 500);
        }

    }

    // FUNCTION FOR USER LIST

    public function user_list()
    {
        $users = Admin_user::all();

        return response()->json(['users' => $users]);
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
        $rule = [
            'user_id' => 'required|exists:admin_users,id',
            'name' => 'required',
            'phone' => 'required',
            'password' => 'required',
        ];

        $validator = Validator::make($request->all(), $rule);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = Admin_user::find($request->user_id);

        if (! $user) {
            return response()->json(['error' => 'User not found'], 404);
        }

        $user->name = $request->name;
        $user->phone = $request->phone;
        $user->password = $request->password;

        try {

            $user->save();

            return response()->json(['message' => 'User updated successfully']);

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

        if (! $user) {
            return response()->json(['error' => 'User not found'], 404);
        }

        $user->status = $request->status;

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
        $rule = [
            'farmer_id' => 'required|exists:farmers,id',
            'name' => 'required',
            'nick' => 'required',
            'location' => 'required',
            'phone' => 'required',
            'whats_up' => 'required',
            'password' => 'required',
        ];

        $validator = Validator::make($request->all(), $rule);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = Admin_farmer::find($request->farmer_id);

        if (! $user) {
            return response()->json(['error' => 'Farmer not found'], 404);
        }

        $user->name = $request->name;
        $user->phone = $request->phone;
        $user->nick = $request->nick;
        $user->location = $request->location;
        $user->whats_up = $request->whats_up;
        $user->password = $request->password;

        try {

            $user->save();

            return response()->json(['message' => 'Farmer updated successfully']);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to update farmer', 'message' => $e->getMessage()], 500);
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

            return response()->json([
                'message' => 'Farmer added successfully',
                'qr_code_url' => $url,
            ]);

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

    // function for farmer list

    public function farmer_list()
    {
        $farmers = Admin_farmer::all();

        return response()->json(['farmers' => $farmers]);
    }

    // function for trader list

    public function trader_list()
    {
        // Assuming you have a Trader model and traders table
        $traders = User::where('type', 'reg')->get();

        return response()->json(['traders' => $traders]);
    }
}
