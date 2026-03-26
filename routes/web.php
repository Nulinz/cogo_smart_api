<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\FarmerController;
use App\Http\Controllers\Admin\SubscriptionController;
use App\Http\Controllers\Admin\TraderController;
use App\Http\Controllers\Admin\UserController;

// Auth Routes
Route::get('admin_login', [AdminController::class, 'index'])->name('login');
Route::get('admin_logout', [AdminController::class, 'logout'])->name('admin.logout');
Route::post('admin_login_post', [AdminController::class, 'login'])->name('admin.login.post');

Route::get('otp', [AuthController::class, 'otp'])->name('otp');
Route::get('change-password', [AuthController::class, 'change_password'])->name('change.password');

// Dashboard    
Route::get('dashboard', [AdminController::class, 'dashboard'])->name('dashboard');

// trader
Route::get('trader-list', [AdminController::class, 'trader_list'])->name('trader.list');

// farmer
Route::get('farmer-list', [AdminController::class, 'farmer_list'])->name('farmer.list');
Route::post('farmer_create', [AdminController::class, 'farmers'])->name('farmer.store');
Route::get('farmer_qr_code', [AdminController::class, 'farmer_qr_code'])->name('farmer.qr_code');
Route::post('farmer_edit_store', [AdminController::class, 'farmer_edit_store'])->name('farmer.edit.store');

Route::post('farmer_check_phone', [AdminController::class, 'farmer_check_phone'])->name('farmer.check.phone');

// subscripton
Route::get('subscription-list', [AdminController::class, 'subscription_list'])->name('subscription.list');
Route::get('subscription-profile/{type}', [AdminController::class, 'subscription_profile'])->name('subscription.profile');
Route::post('subscription-store', [AdminController::class, 'subscription_store'])->name('subscription.store');

// user
// Route::get('user-list', [UserController::class, 'user_list'])->name('user.list');
Route::post('user_add', [AdminController::class, 'user_add'])->name('user.add');
Route::get('user_list', [AdminController::class, 'user_list'])->name('user.list');
Route::get('user_edit_show', [AdminController::class, 'user_edit_show'])->name('user.edit.show');
Route::post('user_edit_store', [AdminController::class, 'user_edit_store'])->name('user.edit.store');
Route::post('user_status_update', [AdminController::class, 'user_status_update'])->name('user.status.update');
