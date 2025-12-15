<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Route::middleware(['tenant.db'])->group(function () {
//     Route::get('/users', [App\Http\Controllers\User_cnt::class, 'index']);
// });
