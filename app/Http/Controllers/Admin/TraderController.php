<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class TraderController extends Controller
{
    public function trader_list()
    {
        return view('trader.index');
    }
}
