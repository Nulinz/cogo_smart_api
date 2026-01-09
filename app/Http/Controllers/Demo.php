<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class Demo extends Controller
{
    // function for demo

    public function demo_function(Request $request)
    {
        return response()->json([
            'success' => true,
            'message' => 'Demo function executed successfully',
        ], 200);
    }
}
