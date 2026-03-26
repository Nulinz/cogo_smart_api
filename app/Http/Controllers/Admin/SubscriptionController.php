<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class SubscriptionController extends Controller
{
    public function subscription_list()
    {
        return view('subscription.index');
    }

    public function subscription_profile()
    {
        return view('subscription.profile');
    }
}
