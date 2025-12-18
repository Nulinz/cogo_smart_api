<?php

use App\Http\Controllers\Base_cnt;
use App\Http\Controllers\Farmer_cnt;
use App\Http\Controllers\Party_cnt;
use App\Http\Controllers\Product_cnt;
use App\Http\Controllers\Register_cnt;
use App\Http\Controllers\Load_cnt;
use Illuminate\Support\Facades\Route;

// Route::post('/new', function () {
//     return response()->json(['message' => 'API is working']);
// });

// register
Route::post('/register', [Register_cnt::class, 'register']);
Route::post('/generate_otp', [Register_cnt::class, 'generate_otp']);

Route::post('/login_phone', [Register_cnt::class, 'login_phone']);
Route::post('/login', [Register_cnt::class, 'login']);

Route::post('/check_mobile_register', [Register_cnt::class, 'check_mobile']);

Route::middleware(['tenant.db', 'jwt.auth'])->group(function () {

    Route::post('/update_password', [Register_cnt::class, 'update_password']);

    Route::post('/check_mobile', [Register_cnt::class, 'check_mobile']);

    // methods related to Farmer
    Route::post('/create_farm', [Farmer_cnt::class, 'create_farm']);
    Route::post('/get_farm_details', [Farmer_cnt::class, 'get_farmer_details']);
    Route::post('/get_farm_list', [Farmer_cnt::class, 'get_farmer_list']);

    // methods related to Party
    Route::post('/create_party', [Party_cnt::class, 'create_party']);
    Route::post('/get_party_details', [Party_cnt::class, 'get_party_details']);
    Route::post('/get_party_list', [Party_cnt::class, 'get_party_list']);

    // methods for product
    Route::post('/create_product', [Product_cnt::class, 'create_product']);
    Route::post('/edit_product', [Product_cnt::class, 'edit_product']);
    Route::post('/active_product', [Product_cnt::class, 'active_product']);

      // mehtods for create quality, transport, truck
    Route::post('/create_common', [Base_cnt::class, 'create_common']);
    Route::post('/get_common_list', [Base_cnt::class, 'get_common_list']);
    Route::post('/edit_common_list', [Base_cnt::class, 'edit_common']);

    // method to create sequence
    Route::post('/create_sequence', [Register_cnt::class, 'create_seq']);

    // method to create load
    Route::post('/create_load', [Load_cnt::class, 'create_load']);
    Route::post('/add_load_item', [Load_cnt::class, 'add_load_item']);

    Route::post('/me', [Register_cnt::class, 'me']);
    Route::post('/logout', [Register_cnt::class, 'logout']);
});
