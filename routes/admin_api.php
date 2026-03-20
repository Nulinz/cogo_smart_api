<?php

use App\Http\Controllers\AdminController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('admin_login', [AdminController::class, 'index']);

Route::post('admin_login_post', [AdminController::class, 'login']);

Route::post('user_add', [AdminController::class, 'user_add']);

Route::post('farmer_create', [AdminController::class, 'farmers']);

Route::get('farmer_qr_code', [AdminController::class, 'farmer_qr_code']);
Route::get('farmer_list', [AdminController::class, 'farmer_list']);
Route::post('farmer_edit_store', [AdminController::class, 'farmer_edit_store']);
Route::get('farmer_edit_show', [AdminController::class, 'farmer_edit_show']);

Route::get('user_list', [AdminController::class, 'user_list']);
Route::get('user_edit_show', [AdminController::class, 'user_edit_show']);
Route::post('user_edit_store', [AdminController::class, 'user_edit_store']);
Route::post('user_status_update', [AdminController::class, 'user_status_update']);

Route::get('trader_list', [AdminController::class, 'trader_list']);

// Route::middleware(['tenant.db'])->group(function () {
//     Route::get('/users', [App\Http\Controllers\User_cnt::class, 'index']);
// });
