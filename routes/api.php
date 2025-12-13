<?php

use App\Http\Controllers\Farmer_cnt;
use App\Http\Controllers\Party_cnt;
use App\Http\Controllers\Product_cnt;
use App\Http\Controllers\Register_cnt;
use App\Http\Controllers\User_cnt;
use Illuminate\Support\Facades\Route;

// Route::post('/new', function () {
//     return response()->json(['message' => 'API is working']);
// });

// register
Route::post('/register', [Register_cnt::class, 'register']);

Route::post('/login', [Register_cnt::class, 'login']);

Route::post('/check_mobile_register', [Register_cnt::class, 'check_mobile']);

// ->middleware('tenant.db');

// Route::middleware(['api'])->group(function () {
//     Route::get('/me', [User_cnt::class, 'me']);
//     Route::post('/logout', [User_cnt::class, 'logout']);
// });

Route::middleware(['tenant.db', 'jwt.auth'])->group(function () {

    Route::post('/check_mobile', [Register_cnt::class, 'check_mobile']);

    // methods related to Farmer
    Route::post('/create_farm', [Farmer_cnt::class, 'create_farm']);

    // methods related to Party
    Route::post('/create_party', [Party_cnt::class, 'create_party']);

    // methods for product
    Route::post('/create_product', [Product_cnt::class, 'create_product']);
    Route::post('/edit_product', [Product_cnt::class, 'edit_product']);
    Route::post('/active_product', [Product_cnt::class, 'active_product']);

    Route::post('/me', [Register_cnt::class, 'me']);
    Route::post('/logout', [Register_cnt::class, 'logout']);
});
