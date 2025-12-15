<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\Tenant_db;
use Illuminate\Support\Facades\DB;

class User_cnt extends Controller
{
    public function index()
    {
        // $db = 'cogo_smart_api'; // Example tenant database name
        // // 2. Switch to tenant DB
        // Tenant_db::main();

        // $user = DB::table('users')->get();

        // return $user;

        return User::all();  // Automatically uses tenant DB
    }
}
