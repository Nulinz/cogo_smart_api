<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function login()
    {
        // dd('login');
        return view('auth.login');
    }

    public function otp()
    {
        return view('auth.otp');
    }
    public function change_password()
    {
        return view('auth.change_pass');
    }
}
