<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class FarmerController extends Controller
{
    public function farmer_list()
    {
        return view('farmer.index');
    }
}
