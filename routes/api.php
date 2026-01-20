<?php

use App\Http\Controllers\Base_cnt;
use App\Http\Controllers\Farmer_cnt;
use App\Http\Controllers\Party_cnt;
use App\Http\Controllers\Product_cnt;
use App\Http\Controllers\Register_cnt;
use App\Http\Controllers\Load_cnt;
use App\Http\Controllers\Stock_cnt;
use App\Http\Controllers\Exp_cnt;
use Illuminate\Support\Facades\Route;

// Route::post('/new', function () {
//     return response()->json(['message' => 'API is working']);
// });

// update popup

Route::post('/update_popup',function () {
    return response()->json(['version' => '0.0.1']);
});


// auth refrehh token
Route::post('/refresh', [Register_cnt::class, 'refresh_token']);

// register........
Route::post('/register', [Register_cnt::class, 'register']);
Route::post('/generate_otp', [Register_cnt::class, 'generate_otp']);

Route::post('/login_phone', [Register_cnt::class, 'login_phone']); //--> step-1
Route::post('/login', [Register_cnt::class, 'login']); //--> step-2

Route::post('/check_mobile_register', [Register_cnt::class, 'check_mobile']);

Route::post('/forgot_password', [Register_cnt::class, 'forgot_password']);

Route::middleware(['tenant.db','jwt.auth'])->group(function () {

  // dashboard data

    Route::post('/dashboard', [Base_cnt::class, 'dashboard']);

    Route::post('/update_password', [Register_cnt::class, 'update_password']);

    Route::post('/check_mobile', [Register_cnt::class, 'check_mobile']);

    // toggle favorite for farmer and party
    Route::post('/toggle_fav', [Register_cnt::class, 'toggle_fav']);

    // methods related to Farmer
    Route::post('/create_farm', [Farmer_cnt::class, 'create_farm']);
    Route::post('/get_farm_details', [Farmer_cnt::class, 'get_farmer_details']);
    Route::post('/get_farm_list', [Farmer_cnt::class, 'get_farmer_list']);
    Route::post('/farmer_profile', [Farmer_cnt::class, 'farmer_profile']);

    // methods related to Party
    Route::post('/create_party', [Party_cnt::class, 'create_party']);
    Route::post('/get_party_details', [Party_cnt::class, 'get_party_details']);
    Route::post('/get_party_list', [Party_cnt::class, 'get_party_list']);
    Route::post('/party_profile', [Party_cnt::class, 'party_profile']);

    // methods for product
    Route::post('/create_product', [Product_cnt::class, 'create_product']);
    Route::post('/edit_product', [Product_cnt::class, 'edit_product']);
    Route::post('/active_product', [Product_cnt::class, 'active_product']);
    Route::post('/get_product_list', [Product_cnt::class, 'get_product_list']);
    Route::post('/get_product_details', [Product_cnt::class, 'get_product_details']);

      // mehtods for create quality, transport, truck
    Route::post('/create_common', [Base_cnt::class, 'create_common']);
    Route::post('/get_common_list', [Base_cnt::class, 'get_common_list']);
    Route::post('/edit_common_list', [Base_cnt::class, 'edit_common']);

    // method to create sequence
    Route::post('/create_sequence', [Register_cnt::class, 'create_seq']);
    Route::post('/get_sequence', [Register_cnt::class, 'get_sequence_count']);

    // method to create load
    Route::post('/create_load', [Load_cnt::class, 'create_load']);
    Route::post('/add_load_item', [Load_cnt::class, 'add_load_item']);
    Route::post('/get_load_list', [Load_cnt::class, 'get_load_list']);
    Route::post('/ind_load_list', [Load_cnt::class, 'ind_load_list']);
    Route::post('/ind_load_details', [Load_cnt::class, 'ind_load_details']);
    Route::post('/edit_load_fetch', [Load_cnt::class, 'edit_load_fetch']);
     Route::post('/edit_load_item', [Load_cnt::class, 'edit_load_item']);
     Route::post('/edit_load_item_fetch', [Load_cnt::class, 'edit_load_item_fetch']);

    // load self list
    Route::post('/load_self_list', [Load_cnt::class, 'load_self_list']);

    // shift load items
    Route::post('/add_shift_item', [Load_cnt::class, 'add_shift_item']);
    // Route::post('/shift_load_items', [Load_cnt::class, 'shift_load_items']);

    Route::post('/me', [Register_cnt::class, 'me']);
    Route::post('/logout', [Register_cnt::class, 'logout']);

    // create a employee user
    Route::post('/create_employee', [Register_cnt::class, 'create_employee']);
    Route::post('/get_employee_list', [Register_cnt::class, 'get_employee_list']);
    Route::post('/get_employee_details', [Register_cnt::class, 'get_employee_details']);
    Route::post('/edit_employee', [Register_cnt::class, 'edit_employee']);
     Route::post('/edit_employee_details', [Register_cnt::class, 'edit_employee_details']);
     Route::post('/change_password', [Register_cnt::class, 'change_password']);

     //bank details
    Route::post('/add_bank_details', [Base_cnt::class, 'add_bank_details']);
    Route::post('/get_bank_details', [Base_cnt::class, 'get_bank_details']);
    Route::post('/list_bank_details', [Base_cnt::class, 'list_bank_details']);


    // advance related routes
    // Route::post('/add_advance', [Base_cnt::class, 'add_advance']);
    // Route::post('/get_advance_list', [Base_cnt::class, 'get_advance_list']);
    Route::post('/farmer_advance_pending', [Farmer_cnt::class, 'farmer_advance_pending']);


    // create teh stock in entry
    Route::post('/add_purchase', [Load_cnt::class, 'add_purchase']);

    // create the stock out entry
    Route::post('/add_sales', [Load_cnt::class, 'add_sales']);

    // create teh shift to load from stock entry
    Route::post('/stock_shift', [Load_cnt::class, 'stock_shift']);
    Route::post('/stock_home', [Stock_cnt::class, 'stock_home']);
    Route::post('/stock_transaction_list', [Stock_cnt::class, 'stock_transaction_list']);
    Route::post('/get_stock_product', [Stock_cnt::class, 'get_stock_product']);
    // create the filter data

    Route::post('/add_filter', [Load_cnt::class, 'add_filter']);
    Route::post('/get_filter_list', [Load_cnt::class, 'get_filter_list']);
    Route::post('/edit_filter', [Load_cnt::class, 'edit_filter']);

    // farmer transaction

    Route::post('/farmer_pay_out', [Farmer_cnt::class, 'farmer_pay_out']);
    Route::post('/farmer_pay_in', [Farmer_cnt::class, 'farmer_pay_in']);
    Route::post('/farmer_pay_edit', [Farmer_cnt::class, 'farmer_pay_edit']);

    // party transaction
    Route::post('/party_pay_out', [Party_cnt::class, 'party_pay_out']);
    Route::post('/party_pay_in', [Party_cnt::class, 'party_pay_in']);
    Route::post('/party_pay_edit', [Party_cnt::class, 'party_pay_edit']);

    // coconut availabilty

    Route::post('/add_coconut', [Base_cnt::class, 'add_coconut']);
    Route::post('/get_coconut_emp', [Base_cnt::class, 'get_coconut_emp']);
    Route::post('/get_coconut_list', [Base_cnt::class, 'get_coconut_list']);

    //load summary
    Route::post('/add_load_summary', [Stock_cnt::class, 'add_load_summary']);
    Route::post('/get_load_summary', [Stock_cnt::class, 'get_load_summary']);
    Route::post('/edit_load_summary', [Stock_cnt::class, 'edit_load_summary']);
    Route::post('/summary_new', [Stock_cnt::class, 'summary_new']);

    // add invoice
    Route::post('/add_invoice', [Stock_cnt::class, 'add_invoice']);
    Route::post('/get_invoice', [Stock_cnt::class, 'get_invoice']);
    Route::post('/update_loss_invoice', [Stock_cnt::class, 'update_loss_invoice']);

    // invoices for party..

     Route::post('/invoice_pdf', [Stock_cnt::class, 'invoice_pdf']);

    // add petty cash

    Route::post('/add_petty',[Stock_cnt::class, 'add_petty']);
    Route::post('/petty_cash_ind',[Stock_cnt::class, 'petty_cash_ind']);
    Route::post('/petty_cash_ind_transaction',[Stock_cnt::class, 'petty_cash_ind_transaction']);
    Route::post('/petty_cash_ind_view_all',[Stock_cnt::class, 'petty_cash_ind_view_all']);

    // Route to expenses
    Route::post('/create_expense', [Exp_cnt::class, 'create_expense']);
    Route::post('/expense_created_list', [Exp_cnt::class, 'expense_created_list']);
    Route::post('/expense_pay_out', [Exp_cnt::class, 'expense_pay_out']);
    Route::post('/expense_status_update', [Exp_cnt::class, 'expense_status_update']); 
    Route::post('/expense_home', [Exp_cnt::class, 'expense_home']);
    Route::post('/expense_week', [Exp_cnt::class, 'expense_week']);
    Route::post('/expense_emp_profile', [Exp_cnt::class, 'expense_emp_profile']);
    Route::post('/expense_overall_list', [Exp_cnt::class, 'expense_overall_list']);

    // expense list

     Route::post('/get_exp_list', [Exp_cnt::class, 'get_exp_list']);

     // kyc and company name insert

     Route::post('/add_kyc', [Register_cnt::class, 'add_kyc']);
     Route::post('/get_kyc', [Register_cnt::class, 'get_kyc']);

});
